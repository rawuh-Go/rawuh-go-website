<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_latitude',
        'schedule_longitude',
        'schedule_waktu_datang',
        'schedule_waktu_pulang',
        'datang_latitude',
        'datang_longitude',
        'pulang_latitude',
        'pulang_longitude',
        'waktu_datang',
        'waktu_pulang',
        'foto_absen_datang',
        'foto_absen_pulang',
        'logbook',
        'status',
    ];

    protected $casts = [
        'foto_absen_pulang' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // fungsi untuk menghandle keterlambatan pegawai
    public function isLate()
    {
        $scheduleStartTime = Carbon::parse($this->schedule_waktu_datang);
        $start_time = Carbon::parse($this->waktu_datang);

        return $start_time->greaterThan($scheduleStartTime);
    }

    // buatkan fungsi untuk menghitung lama waktu berkerja
    public function calculateWorkDuration()
    {
        $waktuDatang = Carbon::parse($this->waktu_datang);
        $waktuPulang = Carbon::parse($this->waktu_pulang);

        $duration = $waktuDatang->diff($waktuPulang);

        $hours = $duration->h;
        $minutes = $duration->i;

        return "{$hours} Jam, {$minutes} Menit.";
    }

    public function getFotoAbsenDatangUrlAttribute()
    {
        return $this->foto_absen_datang ? Storage::disk('public')->url($this->foto_absen_datang) : null;
    }

    public function getFotoAbsenPulangUrlAttribute()
    {
        return $this->foto_absen_pulang ? Storage::disk('public')->url($this->foto_absen_pulang) : null;
    }
}
