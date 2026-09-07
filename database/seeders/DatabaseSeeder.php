<?php

namespace Database\Seeders;

use App\Models\KategoriSurat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan database seeder.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER ADMIN
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'admin@arsipsurat.test',
            ],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'jabatan' => 'Kepala IT / Admin Sistem',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | USER PIMPINAN
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'pimpinan@arsipsurat.test',
            ],
            [
                'name' => 'Kepala Instansi',
                'password' => Hash::make('password'),
                'role' => 'pimpinan',
                'jabatan' => 'Kepala Dinas / Direktur',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | USER STAFF
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'staff@arsipsurat.test',
            ],
            [
                'name' => 'Staff Arsip',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'jabatan' => 'Staff Administrasi & Agenda',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | KATEGORI SURAT
        |--------------------------------------------------------------------------
        */

        $kategoris = [
            [
                'nama_kategori' => 'Surat Undangan',
                'kode' => 'UND',
                'sifat' => 'biasa',
            ],
            [
                'nama_kategori' => 'Surat Keputusan',
                'kode' => 'SK',
                'sifat' => 'penting',
            ],
            [
                'nama_kategori' => 'Surat Edaran',
                'kode' => 'SE',
                'sifat' => 'biasa',
            ],
            [
                'nama_kategori' => 'Surat Perjanjian Kerjasama',
                'kode' => 'MOU',
                'sifat' => 'rahasia',
            ],
            [
                'nama_kategori' => 'Surat Permohonan',
                'kode' => 'PER',
                'sifat' => 'biasa',
            ],
        ];

        foreach ($kategoris as $kategori) {
            KategoriSurat::updateOrCreate(
                [
                    'kode' => $kategori['kode'],
                ],
                [
                    'nama_kategori' => $kategori['nama_kategori'],
                    'sifat' => $kategori['sifat'],
                ]
            );
        }
    }
}