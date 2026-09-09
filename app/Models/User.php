<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Konstanta role aplikasi.
     */
    public const ROLE_ADMIN = 'admin';

    public const ROLE_PIMPINAN = 'pimpinan';

    public const ROLE_STAFF = 'staff';

    /**
     * Daftar role resmi.
     */
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_PIMPINAN,
        self::ROLE_STAFF,
    ];

    /**
     * Kolom yang boleh diisi.
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
     * Casting.
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
     * Normalisasi role.
     *
     * staf -> staff
     */
    public static function normalizeRole(
        ?string $role
    ): string {
        $role = strtolower(
            trim(
                (string) $role
            )
        );

        return $role === 'staf'
            ? self::ROLE_STAFF
            : $role;
    }

    /**
     * Role yang sudah dinormalisasi.
     */
    public function normalizedRole(): string
    {
        return self::normalizeRole(
            $this->role
        );
    }

    /**
     * Mengecek admin.
     */
    public function isAdmin(): bool
    {
        return $this->normalizedRole()
            === self::ROLE_ADMIN;
    }

    /**
     * Mengecek pimpinan.
     */
    public function isPimpinan(): bool
    {
        return $this->normalizedRole()
            === self::ROLE_PIMPINAN;
    }

    /**
     * Mengecek staff.
     */
    public function isStaff(): bool
    {
        return $this->normalizedRole()
            === self::ROLE_STAFF;
    }

    /**
     * Alias staff dalam bahasa Indonesia.
     */
    public function isStaf(): bool
    {
        return $this->isStaff();
    }

    /**
     * Mengecek akun aktif.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Mengecek akun nonaktif.
     */
    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    /**
     * Admin dapat mengelola master data.
     */
    public function canManageMasterData(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Admin dan pimpinan dapat mengelola surat.
     */
    public function canManageSurat(): bool
    {
        return $this->isAdmin()
            || $this->isPimpinan();
    }

    /**
     * Admin dan pimpinan dapat mengelola disposisi.
     */
    public function canManageDisposisi(): bool
    {
        return $this->isAdmin()
            || $this->isPimpinan();
    }

    /**
     * Relasi disposisi yang diterima.
     */
    public function disposisiMasuk(): HasMany
    {
        return $this->hasMany(
            Disposisi::class,
            'kepada_user_id'
        );
    }

    /**
     * Relasi disposisi yang dibuat.
     */
    public function disposisiKeluar(): HasMany
    {
        return $this->hasMany(
            Disposisi::class,
            'dari_user_id'
        );
    }

    /**
     * Relasi surat masuk yang dicatat.
     */
    public function suratMasuk(): HasMany
    {
        return $this->hasMany(
            SuratMasuk::class,
            'diterima_oleh'
        );
    }

    /**
     * Relasi surat keluar yang dibuat.
     */
    public function suratKeluar(): HasMany
    {
        return $this->hasMany(
            SuratKeluar::class,
            'dibuat_oleh'
        );
    }

    /**
     * Relasi surat keluar yang ditandatangani.
     */
    public function suratKeluarDitandatangani(): HasMany
    {
        return $this->hasMany(
            SuratKeluar::class,
            'ditandatangani_oleh'
        );
    }

    /**
     * Scope user aktif.
     */
    public function scopeAktif(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }

    /**
     * Scope staff.
     */
    public function scopeStaff(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(role)) IN (?, ?)',
            [
                'staff',
                'staf',
            ]
        );
    }

    /**
     * Scope admin.
     */
    public function scopeAdmin(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(role)) = ?',
            [
                'admin',
            ]
        );
    }

    /**
     * Scope pimpinan.
     */
    public function scopePimpinan(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(role)) = ?',
            [
                'pimpinan',
            ]
        );
    }

    /**
     * Scope berdasarkan role.
     */
    public function scopeRole(
        Builder $query,
        ?string $role
    ): Builder {
        if (
            $role === null ||
            trim($role) === ''
        ) {
            return $query;
        }

        $role =
            self::normalizeRole($role);

        if (!in_array(
            $role,
            self::ROLES,
            true
        )) {
            return $query;
        }

        if ($role === self::ROLE_STAFF) {
            return $query->whereRaw(
                'LOWER(TRIM(role)) IN (?, ?)',
                [
                    'staff',
                    'staf',
                ]
            );
        }

        return $query->whereRaw(
            'LOWER(TRIM(role)) = ?',
            [$role]
        );
    }

    /**
     * Scope pencarian user.
     */
    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search =
            trim(
                (string) $search
            );

        if ($search === '') {
            return $query;
        }

        $keyword =
            "%{$search}%";

        return $query->where(
            function (
                Builder $q
            ) use ($keyword): void {
                $q->where(
                    'name',
                    'like',
                    $keyword
                )
                ->orWhere(
                    'email',
                    'like',
                    $keyword
                )
                ->orWhere(
                    'jabatan',
                    'like',
                    $keyword
                );
            }
        );
    }

    /**
     * Label role.
     */
    public function getRoleLabelAttribute(): string
    {
        return match (
            $this->normalizedRole()
        ) {
            self::ROLE_ADMIN =>
                'Admin',

            self::ROLE_PIMPINAN =>
                'Pimpinan',

            self::ROLE_STAFF =>
                'Staff',

            default =>
                ucfirst(
                    $this->normalizedRole()
                ),
        };
    }

    /**
     * Label status.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->isActive()
            ? 'Aktif'
            : 'Nonaktif';
    }
}