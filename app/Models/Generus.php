<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Generus extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name', 'nik', 'place_of_birth', 'date_of_birth', 'gender',
        'blood_type', 'photo_path', // Tambahkan ini
        'father_name', 'mother_name',
        'address', 'kelompok_id',
        'jenjang', 'education_status', 'notes',
        'created_by_user_id', 'updated_by_user_id',
    ];


    // Pastikan 'date_of_birth' ada di $fillable atau $guarded
    // dan juga di $casts jika kamu ingin Carbon otomatis memparsingnya
    protected $casts = [
        'date_of_birth' => 'date', // Penting agar Carbon bisa langsung bekerja
    ];

    /**
     * Get the age of the generus in years, months, or days.
     */
    public function getAgeAttribute(): ?string
    {
        // Jika tanggal lahir tidak ada, kembalikan null atau string default
        if (empty($this->date_of_birth)) {
            return null; // Atau 'N/A'
        }

        // Pastikan date_of_birth sudah dalam format Carbon
        // Jika kamu menggunakan $casts di atas, ini tidak terlalu krusial,
        // tapi tetap baik untuk penanganan error.
        try {
            $birthDate = Carbon::parse($this->date_of_birth);
        } catch (\Exception $e) {
            return null; // Tanggal tidak valid
        }

        $now = Carbon::now();

        // Hitung perbedaan waktu
        $diff = $birthDate->diff($now);

        // Logika: Tahun > Bulan > Hari
        if ($diff->y > 0) {
            // Jika ada perbedaan tahun
            return $diff->y . ' tahun';
        } elseif ($diff->m > 0) {
            // Jika tidak ada tahun, tapi ada perbedaan bulan
            return $diff->m . ' bulan';
        } else {
            // Jika tidak ada tahun dan tidak ada bulan, gunakan hari
            return $diff->d . ' hari';
        }
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class);
    }

    // public function createdBy(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'created_by_user_id');
    // }

    // public function updatedBy(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'updated_by_user_id');
    // }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
