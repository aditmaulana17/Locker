<?php

namespace Database\Seeders;

use App\Models\Instansi;
use App\Models\KategoriSurat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. User Admin
        User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@arsipsurat.test',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'jabatan'   => 'Kepala IT / Admin Sistem',
            'is_active' => true,
        ]);

        // 2. User Pimpinan
        User::create([
            'name'      => 'Kepala Instansi',
            'email'     => 'pimpinan@arsipsurat.test',
            'password'  => Hash::make('password'),
            'role'      => 'pimpinan',
            'jabatan'   => 'Kepala Dinas / Direktur',
            'is_active' => true,
        ]);

        // 3. User Staf
        User::create([
            'name'      => 'Staf Arsip',
            'email'     => 'staf@arsipsurat.test',
            'password'  => Hash::make('password'),
            'role'      => 'staf',
            'jabatan'   => 'Staf Administrasi & Agenda',
            'is_active' => true,
        ]);

        // Seed Data Kategori Surat
        $kategoris = [
            ['nama_kategori' => 'Surat Undangan', 'kode' => 'UND', 'sifat' => 'biasa'],
            ['nama_kategori' => 'Surat Keputusan', 'kode' => 'SK', 'sifat' => 'penting'],
            ['nama_kategori' => 'Surat Edaran', 'kode' => 'SE', 'sifat' => 'biasa'],
            ['nama_kategori' => 'Surat Perjanjian Kerjasama', 'kode' => 'MOU', 'sifat' => 'rahasia'],
            ['nama_kategori' => 'Surat Permohonan', 'kode' => 'PER', 'sifat' => 'biasa'],
        ];
        foreach ($kategoris as $k) {
            KategoriSurat::create($k);
        }
    }
}