<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * User yang sedang login.
     */
    protected function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User
            ? $user
            : null;
    }

    /**
     * Role user yang sudah dinormalisasi.
     *
     * staf -> staff
     */
    protected function currentUserRole(): string
    {
        $user = $this->currentUser();

        if (!$user) {
            return '';
        }

        return User::normalizeRole(
            $user->role
        );
    }

    /**
     * Mengecek apakah user admin.
     */
    protected function isAdmin(): bool
    {
        return $this->currentUserRole()
            === User::ROLE_ADMIN;
    }

    /**
     * Mengecek apakah user pimpinan.
     */
    protected function isPimpinan(): bool
    {
        return $this->currentUserRole()
            === User::ROLE_PIMPINAN;
    }

    /**
     * Mengecek apakah user staff.
     */
    protected function isStaff(): bool
    {
        return $this->currentUserRole()
            === User::ROLE_STAFF;
    }

    /**
     * Memastikan user sudah login.
     */
    protected function ensureAuthenticated(): void
    {
        abort_unless(
            Auth::check(),
            403,
            'Anda harus login untuk mengakses halaman ini.'
        );
    }

    /**
     * Memastikan user adalah admin.
     */
    protected function ensureAdmin(): void
    {
        $this->ensureAuthenticated();

        abort_unless(
            $this->isAdmin(),
            403,
            'Anda tidak memiliki izin untuk mengakses fitur ini.'
        );
    }

    /**
     * Memastikan user boleh mengelola surat.
     *
     * Admin dan pimpinan.
     */
    protected function ensureCanManageSurat(): void
    {
        $this->ensureAuthenticated();

        abort_unless(
            $this->isAdmin()
                || $this->isPimpinan(),
            403,
            'Anda tidak memiliki izin untuk mengelola surat.'
        );
    }

    /**
     * Memastikan user boleh mengelola disposisi.
     *
     * Admin dan pimpinan.
     */
    protected function ensureCanManageDisposisi(): void
    {
        $this->ensureAuthenticated();

        abort_unless(
            $this->isAdmin()
                || $this->isPimpinan(),
            403,
            'Anda tidak memiliki izin untuk mengelola disposisi.'
        );
    }
}