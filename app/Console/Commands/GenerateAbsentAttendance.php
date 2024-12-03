<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;

class GenerateAbsentAttendance extends Command
{
    protected $signature = 'attendance:generate-absent';
    protected $description = 'Generate absent records for users who did not check in/out';

    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');

        // Ambil semua jadwal user
        $schedules = Schedule::with(['user', 'shift'])
            ->where('is_banned', false)
            ->get();

        foreach ($schedules as $schedule) {
            // Skip jika user sedang cuti
            if ($this->isUserOnLeave($schedule->user_id, $today)) {
                continue;
            }

            // Cek kehadiran hari ini
            $attendance = Attendance::where('user_id', $schedule->user_id)
                ->whereDate('created_at', $today)
                ->first();

            $shiftEndTime = Carbon::parse($today . ' ' . $schedule->shift->waktu_pulang);

            // Jika sudah lewat waktu shift
            if (Carbon::now()->gt($shiftEndTime)) {
                if (!$attendance) {
                    // Jika tidak ada presensi sama sekali
                    $this->createAbsentRecord($schedule, 'Tidak Hadir');
                } elseif (!$attendance->waktu_pulang) {
                    // Jika hanya presensi masuk tapi tidak pulang
                    $attendance->update([
                        'waktu_pulang' => $schedule->shift->waktu_pulang,
                        'status' => 'Tidak Checkout',
                    ]);
                }
            }
        }

        $this->info('Absent attendance records have been generated successfully.');
    }

    private function isUserOnLeave($userId, $date)
    {
        return \App\Models\Leave::where('user_id', $userId)
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date)
            ->where('status', 'approved')
            ->exists();
    }

    private function createAbsentRecord($schedule, $status)
    {
        Attendance::create([
            'user_id' => $schedule->user_id,
            'schedule_latitude' => $schedule->office->latitude,
            'schedule_longitude' => $schedule->office->longitude,
            'schedule_waktu_datang' => $schedule->shift->waktu_datang,
            'schedule_waktu_pulang' => $schedule->shift->waktu_pulang,
            'waktu_datang' => $schedule->shift->waktu_datang,
            'waktu_pulang' => $schedule->shift->waktu_pulang,
            'status' => $status,
        ]);
    }
}