<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Pembayaran;

#[Signature('app:add-month14')]
#[Description('Add month 14 for all users')]
class AddMonth14 extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            Pembayaran::firstOrCreate(
                ['user_id' => $user->id, 'bulan_ke' => 14],
                ['nominal' => 65000, 'status' => 'belum']
            );
        }
        $this->info('Added month 14 to ' . $users->count() . ' users.');
    }
}
