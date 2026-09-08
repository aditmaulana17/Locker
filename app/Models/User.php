<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'is_active',
    ];

    /**
     * Kolom yang disembunyikan.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Mengecek apakah user adalah admin.
     */
    public function isAdmin(): bool
    {
        return strtolower(
            trim(
                (string) $this->role
            )
        ) === 'admin';
    }

    /**
     * Mengecek apakah user adalah pimpinan.
     */
    public function isPimpinan(): bool
    {
        return strtolower(
            trim(
                (string) $this->role
            )
        ) === 'pimpinan';
    }

    /**
     * Mengecek apakah user adalah staf.
     *
     * Database menggunakan "staff".
     * "staf" juga diterima sebagai alias.
     */
    public function isStaf(): bool
    {
        return in_array(
            strtolower(
                trim(
                    (string) $this->role
                )
            ),
            [
                'staff',
                'staf',
            ],
            true
        );
    }

    /**
     * Mengecek role staf.
     *
     * Alias alternatif dari isStaf().
     */
    public function isStaff(): bool
    {
        return $this->isStaf();
    }

    /**
     * Mengambil role yang sudah dinormalisasi
     * untuk kebutuhan logika aplikasi.
     *
     * Hasil:
     * admin
     * pimpinan
     * staf
     */
    public function normalizedRole(): string
    {
        $role = strtolower(
            trim(
                (string) $this->role
            )
        );

        if ($role === 'staff') {
            return 'staf';
        }

        return $role;
    }

    /**
     * Relasi disposisi masuk.
     *
     * Disposisi yang ditujukan kepada user ini.
     */
    public function disposisiMasuk()
    {
        return $this->hasMany(
            Disposisi::class,
            'kepada_user_id'
        );
    }

    /**
     * Relasi disposisi keluar.
     *
     * Disposisi yang dibuat oleh user ini.
     */
    public function disposisiKeluar()
    {
        return $this->hasMany(
            Disposisi::class,
            'dari_user_id'
        );
    }
}