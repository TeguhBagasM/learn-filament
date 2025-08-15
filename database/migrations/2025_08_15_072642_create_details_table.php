<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
/*************  ✨ Windsurf Command 🌟  *************/
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')
                ->constrained('barangs', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('customer_id')
                ->constrained('customers', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->integer('diskon')->nullable();
            $table->string('nama_barang')->nullable();
            $table->bigInteger('harga')->nullable();
            $table->bigInteger('subtotal')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('hasil_qty')->nullable();
            $table->timestamps();
        });
    }
/*******  d3e74daf-5205-4519-97ce-47ca9cc827f3  *******/

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
