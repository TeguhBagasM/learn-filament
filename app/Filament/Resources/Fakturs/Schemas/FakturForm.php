<?php

namespace App\Filament\Resources\Fakturs\Schemas;

use App\Models\Barang;
use App\Models\CustomerModel;
use App\Models\Faktur;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;

class FakturForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_faktur')
                    ->required(),

                DatePicker::make('tanggal_faktur')
                    ->required(),

                Select::make('customer_id')
                    ->reactive()
                    ->relationship('customer', 'nama_customer')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $customer = CustomerModel::find($state);
                            if ($customer) {
                                $set('kode_customer', $customer->kode_customer);
                            }
                        } else {
                            $set('kode_customer', null);
                        }
                    }),

                TextInput::make('kode_customer')
                    ->disabled()
                    ->dehydrated(), // PENTING: Pastikan field ini tersimpan ke database

                Repeater::make('detail')
                    ->relationship()
                    ->reactive()
                    ->schema([
                        Select::make('barang_id')
                            ->relationship('barang', 'nama_barang')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $barang = Barang::find($state);
                                    if ($barang) {
                                        // Auto-fill harga dari master barang
                                        $set('harga_barang', $barang->harga_jual ?? $barang->harga_barang ?? 0);

                                        // Recalculate subtotal setelah harga berubah
                                        self::calculateSubtotal($set, $get);
                                    }
                                }
                            }),

                        TextInput::make('qty')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                self::calculateSubtotal($set, $get);
                                self::calculateHasilQty($set, $get);
                            }),

                        TextInput::make('harga_barang')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                self::calculateSubtotal($set, $get);
                            }),

                        TextInput::make('diskon')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                self::calculateSubtotal($set, $get);
                            }),

                        TextInput::make('subtotal')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('Rp')
                            ->default(0),

                        TextInput::make('hasil_qty')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->helperText('Hasil setelah konversi/proses'),
                    ])
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        self::calculateGrandTotal($set, $get);
                    })
                    ->addActionLabel('Tambah Item')
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {
                        if (!empty($state['barang_id'])) {
                            $barang = Barang::find($state['barang_id']);
                            $namaBarang = $barang?->nama_barang ?? 'Unknown';
                            $qty = $state['qty'] ?? 0;
                            $subtotal = $state['subtotal'] ?? 0;
                            return "{$namaBarang} - Qty: {$qty} - Rp " . number_format($subtotal, 0, ',', '.');
                        }
                        return 'Item Baru';
                    })
                    ->columnSpanFull(),

                Textarea::make('ket_faktur')
                    ->label('Keterangan')
                    ->placeholder('Catatan tambahan untuk faktur...')
                    ->columnSpanFull(),

                TextInput::make('total')
                    ->label('Total Barang')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('Rp')
                    ->default(0),

                TextInput::make('charge')
                    ->label('Charge (%)')
                    ->numeric()
                    ->default(0)
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100)
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        self::calculateChargeAmount($set, $get);
                        self::calculateFinalTotal($set, $get);
                    }),

                TextInput::make('nominal_charge')
                    ->label('Nominal Charge')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('Rp')
                    ->default(0),

                TextInput::make('total_final')
                    ->label('TOTAL AKHIR')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('Rp')
                    ->default(0)
                    ->extraAttributes(['class' => 'font-bold text-lg']),
            ]);
    }

    protected static function calculateSubtotal(callable $set, callable $get): void
    {
        $qty = (float) ($get('qty') ?? 0);
        $harga_barang = (float) ($get('harga_barang') ?? 0);
        $diskon = (float) ($get('diskon') ?? 0);

        // Hitung subtotal sebelum diskon
        $subtotalBeforeDiskon = $qty * $harga_barang;

        // Hitung diskon amount
        $diskonAmount = $subtotalBeforeDiskon * ($diskon / 100);

        // Subtotal final setelah diskon
        $subtotal = $subtotalBeforeDiskon - $diskonAmount;

        $set('subtotal', max(0, $subtotal)); // Pastikan tidak minus

        // Trigger recalculate grand total
        self::triggerGrandTotalRecalculation($set, $get);
    }

    protected static function calculateHasilQty(callable $set, callable $get): void
    {
        $qty = (float) ($get('qty') ?? 0);
        $hasilQty = $qty; // Sesuaikan dengan kebutuhan bisnis
        $set('hasil_qty', $hasilQty);
    }

    protected static function calculateGrandTotal(callable $set, callable $get): void
    {
        // Untuk akses dari level repeater ke parent form
        $details = $get('../../detail') ?? [];
        $total = 0;

        foreach ($details as $detail) {
            if (is_array($detail) && isset($detail['subtotal'])) {
                $total += (float) $detail['subtotal'];
            }
        }

        $set('../../total', $total);

        // Recalculate charge and final total
        $chargePercent = (float) ($get('../../charge') ?? 0);
        $nominalCharge = $total * ($chargePercent / 100);
        $set('../../nominal_charge', $nominalCharge);

        $totalFinal = $total + $nominalCharge;
        $set('../../total_final', $totalFinal);
    }

    protected static function triggerGrandTotalRecalculation(callable $set, callable $get): void
    {
        // Method helper untuk trigger grand total calculation dari dalam item repeater
        $details = $get('../../detail') ?? [];
        $total = 0;

        foreach ($details as $detail) {
            if (is_array($detail) && isset($detail['subtotal'])) {
                $total += (float) $detail['subtotal'];
            }
        }

        $set('../../total', $total);

        // Recalculate charge and final total
        $chargePercent = (float) ($get('../../charge') ?? 0);
        $nominalCharge = $total * ($chargePercent / 100);
        $set('../../nominal_charge', $nominalCharge);

        $totalFinal = $total + $nominalCharge;
        $set('../../total_final', $totalFinal);
    }

    protected static function calculateChargeAmount(callable $set, callable $get): void
    {
        $total = (float) ($get('total') ?? 0);
        $chargePercent = (float) ($get('charge') ?? 0);

        $nominalCharge = $total * ($chargePercent / 100);
        $set('nominal_charge', $nominalCharge);

        // Also update final total
        self::calculateFinalTotal($set, $get);
    }

    protected static function calculateFinalTotal(callable $set, callable $get): void
    {
        $total = (float) ($get('total') ?? 0);
        $nominalCharge = (float) ($get('nominal_charge') ?? 0);

        $totalFinal = $total + $nominalCharge;
        $set('total_final', $totalFinal);
    }

    public static function generateKodeFaktur(): string
    {
        $tanggal = now()->format('Ymd');
        $lastFaktur = Faktur::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastFaktur ?
            (int) substr($lastFaktur->kode_faktur, -4) + 1 : 1;

        return "INV-{$tanggal}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
