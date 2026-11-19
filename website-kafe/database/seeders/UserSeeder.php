<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $passwordManajer = env('PASSWORD_MANAJER');
        $passwordKasir = env('PASSWORD_KASIR');

        if (!$passwordManajer || !$passwordKasir) {
            throw new RuntimeException(
                'Password awal Manajer dan Kasir belum diatur di .env'
            );
        }

        User::updateOrCreate(
            ['username' => 'manajer'],
            [
                'nama' => 'Manajer Kedai',
                'role' => 'Manajer',
                'nomor_hp' => null,
                'password' => $passwordManajer,
                'wajib_ganti_password' => false,
            ]
        );

        User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'nama' => 'Kasir Kedai',
                'role' => 'Kasir',
                'nomor_hp' => null,
                'password' => $passwordKasir,
                'wajib_ganti_password' => false,
            ]
        );
    }
}
