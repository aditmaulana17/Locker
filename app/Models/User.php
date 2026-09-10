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

    /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PIMPINAN = 'pimpinan';

    public const ROLE_STAFF = 'staff';

    /**
     * Role resmi aplikasi.
     */
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_PIMPINAN,
        self::ROLE_STAFF,
    ];

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | HIDDEN
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE NORMALIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Menormalisasi role menjadi standar aplikasi.
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

        return match ($role) {
            'staf' => self::ROLE_STAFF,
            'staff' => self::ROLE_STAFF,
            'pimpinan' => self::ROLE_PIMPINAN,
            'admin' => self::ROLE_ADMIN,
            default => $role,
        };
    }

    /**
     * Role user yang sudah dinormalisasi.
     */
    public function normalizedRole(): string
    {
        return self::normalizeRole(
            $this->role
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE CHECK
    |--------------------------------------------------------------------------
    */

    /**
     * Mengecek apakah user adalah Admin.
     */
    public function isAdmin(): bool
    {
        return $this->normalizedRole()
            === self::ROLE_ADMIN;
    }

    /**
     * Mengecek apakah user adalah Pimpinan.
     */
    public function isPimpinan(): bool
    {
        return $this->normalizedRole()
            === self::ROLE_PIMPINAN;
    }

    /**
     * Mengecek apakah user adalah Staff.
     */
    public function isStaff(): bool
    {
        return $this->normalizedRole()
            === self::ROLE_STAFF;
    }

    /**
     * Alias Staff dalam Bahasa Indonesia.
     */
    public function isStaf(): bool
    {
        return $this->isStaff();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCOUNT STATUS
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | PERMISSION HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Hanya Admin yang dapat mengelola master data.
     */
    public function canManageMasterData(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Admin dan Pimpinan dapat mengelola surat.
     */
    public function canManageSurat(): bool
    {
        return $this->isAdmin()
            || $this->isPimpinan();
    }

    /**
     * Admin dan Pimpinan dapat mengelola disposisi.
     */
    public function canManageDisposisi(): bool
    {
        return $this->isAdmin()
            || $this->isPimpinan();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Disposisi yang diterima user.
     */
    public function disposisiMasuk(): HasMany
    {
        return $this->hasMany(
            Disposisi::class,
            'kepada_user_id'
        );
    }

    /**
     * Disposisi yang dibuat user.
     */
    public function disposisiKeluar(): HasMany
    {
        return $this->hasMany(
            Disposisi::class,
            'dari_user_id'
        );
    }

    /**
     * Surat masuk yang dicatat user.
     */
    public function suratMasuk(): HasMany
    {
        return $this->hasMany(
            SuratMasuk::class,
            'diterima_oleh'
        );
    }

    /**
     * Surat keluar yang dibuat user.
     */
    public function suratKeluar(): HasMany
    {
        return $this->hasMany(
            SuratKeluar::class,
            'dibuat_oleh'
        );
    }

    /**
     * Surat keluar yang ditandatangani user.
     */
    public function suratKeluarDitandatangani(): HasMany
    {
        return $this->hasMany(
            SuratKeluar::class,
            'ditandatangani_oleh'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * User aktif.
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
     * User Staff.
     *
     * Mendukung data lama:
     * staff / staf.
     */
    public function scopeStaff(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(role)) IN (?, ?)',
            [
                self::ROLE_STAFF,
                'staf',
            ]
        );
    }

    /**
     * User Admin.
     */
    public function scopeAdmin(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(role)) = ?',
            [
                self::ROLE_ADMIN,
            ]
        );
    }

    /**
     * User Pimpinan.
     */
    public function scopePimpinan(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(role)) = ?',
            [
                self::ROLE_PIMPINAN,
            ]
        );
    }

    /**
     * Filter berdasarkan role.
     */
    public function scopeRole(
        Builder $query,
        ?string $role
    ): Builder {
        if (
            $role === null
            || trim($role) === ''
        ) {
            return $query;
        }

        $role = self::normalizeRole(
            $role
        );

        if (
            !in_array(
                $role,
                self::ROLES,
                true
            )
        ) {
            return $query;
        }

        if (
            $role === self::ROLE_STAFF
        ) {
            return $query->whereRaw(
                'LOWER(TRIM(role)) IN (?, ?)',
                [
                    self::ROLE_STAFF,
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
     * Pencarian user.
     */
    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim(
            (string) $search
        );

        if ($search === '') {
            return $query;
        }

        $keyword = "%{$search}%";

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

    /*
    |--------------------------------------------------------------------------
    | LABEL
    |--------------------------------------------------------------------------
    */

    /**
     * Label role untuk tampilan.
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
     * Label status akun.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->isActive()
            ? 'Aktif'
            : 'Nonaktif';
    }
}