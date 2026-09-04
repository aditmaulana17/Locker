<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instansi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model (opsional jika sesuai konvensi plural).
     *
     * @var string
     */
    protected $table = 'instansis'; // Sesuaikan jika nama tabel Anda 'instansi' atau 'instansis'

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_instansi', 
        'jenis', 
        'alamat', 
        'telepon', 
        'email', 
        'kontak_person',
    ];

    /**
     * Relasi ke model SuratMasuk.
     * Sebuah instansi dapat memiliki banyak surat masuk.
     */
    public function suratMasuk(): HasMany
    {
        // Parameter kedua dan ketiga opsional jika menggunakan konvensi standar (instansi_id)
        return $this->hasMany(SuratMasuk::class, 'instansi_id', 'id');
    }

    /**
     * Relasi ke model SuratKeluar.
     * Sebuah instansi dapat memiliki banyak surat keluar.
     */
    public function suratKeluar(): HasMany
    {
        return $this->hasMany(SuratKeluar::class, 'instansi_id', 'id');
    }
}