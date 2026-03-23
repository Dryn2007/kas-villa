<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $guarded = []; // Mengizinkan semua kolom diisi

    // Tambahkan relasi ini untuk fitur History
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
