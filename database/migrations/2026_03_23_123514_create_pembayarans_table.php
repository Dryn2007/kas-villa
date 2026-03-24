<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke KK
            $table->integer('bulan_ke'); // Angka 1 sampai 14 (Sesuai update Mei 2027)
            $table->integer('nominal')->default(65000);
            $table->enum('status', ['belum', 'proses', 'lunas'])->default('belum');
            $table->string('order_id')->nullable(); // Kode unik untuk Payment Gateway
            $table->string('payment_url')->nullable(); // Link bayar dari Payment Gateway
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
