<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'aktivitas', 'modul', 'deskripsi'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function catat(string $aktivitas, string $modul, ?string $deskripsi = null): void
    {
        self::create([
            'user_id' => auth()->id(),
            'aktivitas' => $aktivitas,
            'modul' => $modul,
            'deskripsi' => $deskripsi,
        ]);
    }
}
