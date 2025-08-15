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
        Schema::create('fakturs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_faktur')->unique();
            $table->date('tanggal_faktur');
            $table->string('kode_customer');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('ket_faktur')->nullable();
            $table->integer('total')->default(0);
            $table->integer('nominal_charge')->default(0);
            $table->integer('charge')->default(0);
            $table->integer('total_final')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }
/*******  f94b7619-ec53-4b85-8d68-a3bd71465e04  *******/

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fakturs');
    }
};
