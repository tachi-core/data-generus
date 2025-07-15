<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Ini adalah namespace yang benar
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser; // <--- TAMBAHKAN ATAU KOREKSI BARIS INI
use Filament\Panel; // <--- PASTIKAN BARIS INI JUGA ADA UNTUK canAccessPanel

class User extends Authenticatable implements FilamentUser // Implement FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Definisi roles
    const ROLE_ADMIN = 'admin';
    const ROLE_KELOMPOK = 'kelompok';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'kelompok_id', // Relasi ke kelompok
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke Kelompok
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    // Implementasi FilamentUser untuk otorisasi akses panel
    public function canAccessPanel(Panel $panel): bool
    {
        // Izinkan semua user yang memiliki 'admin' atau 'kelompok' role untuk mengakses panel
        return $this->hasRole(self::ROLE_ADMIN) || $this->hasRole(self::ROLE_KELOMPOK);
    }

    // Helper untuk memeriksa role
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
