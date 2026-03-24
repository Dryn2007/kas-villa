<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah ENUM menjadi VARCHAR(20) menggunakan RAW SQL agar langsung kompatibel di TiDB / MySQL
        DB::statement("ALTER TABLE pembayarans MODIFY COLUMN status VARCHAR(20) DEFAULT 'belum'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pembayarans MODIFY COLUMN status ENUM('belum', 'proses', 'lunas') DEFAULT 'belum'");
    }
};
