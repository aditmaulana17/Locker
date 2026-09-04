<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'jabatan', 
        'is_active',
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // --- HELPER METHOD ROLE ---
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isStaf(): bool
    {
        return $this->role === 'staf';
    }

    // --- RELASI DISPOSISI ---
    public function disposisiMasuk()
    {
        return $this->hasMany(Disposisi::class, 'kepada_user_id');
    }

    public function disposisiKeluar()
    {
        return $this->hasMany(Disposisi::class, 'dari_user_id');
    }
}