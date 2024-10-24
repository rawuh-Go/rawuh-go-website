<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Leave;
use Auth;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Presensi extends Component
{
    use WithFileUploads;

    public $latitude;
    public $longitude;
    public $insideRadius = false;
    public $showPhotoUploadPage = false;
    public $photo;
    public $photoPreview;
    public $photoTaken = false;

    public $logbook = '';
    public $showLogbookForm = false;
    public $isClockOut = false;



    protected $rules = [
        'photo' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
        'logbook' => 'required_if:isClockOut,true',
    ];


    public function render()
    {
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->whereDate('created_at', date('Y-m-d'))->first();

        // Set isClockOut based on attendance status
        if ($attendance && !$attendance->waktu_pulang) {
            $this->isClockOut = true;
        }

        return view('livewire.presensi', [
            'schedule' => $schedule,
            'insideRadius' => $this->insideRadius,
            'attendance' => $attendance,
            'showPhotoUploadPage' => $this->showPhotoUploadPage,
            'photoPreview' => $this->photoPreview,
            'isClockOut' => $this->isClockOut,
        ]);
    }

    public function setPhoto($photoData)
    {
        $this->photo = $photoData;
        $this->photoPreview = $photoData;
        $this->photoTaken = true;

        // Automatically show logbook form after taking photo during clock out
        if ($this->isClockOut) {
            $this->showLogbookForm = true;
        }
    }

    public function showPhotoUpload()
    {
        $this->showPhotoUploadPage = true;
    }

    public function submitPresensi()
    {
        // Validate basic requirements
        $this->validate([
            'photo' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        // Additional validation for clock out
        if ($this->isClockOut && empty($this->logbook)) {
            $this->showLogbookForm = true;
            session()->flash('error', 'Mohon isi logbook terlebih dahulu.');
            return;
        }

        $schedule = Schedule::where('user_id', Auth::user()->id)->first();

        if ($schedule) {
            $attendance = Attendance::where('user_id', Auth::user()->id)
                ->whereDate('created_at', date('Y-m-d'))->first();

            $user = Auth::user();
            $fileName = time() . '.png';
            $folderPath = 'attendance/' . $user->name;

            Storage::disk('public')->makeDirectory($folderPath);

            $image = $this->decodeBase64Image($this->photo);
            $photoPath = $folderPath . '/' . $fileName;
            Storage::disk('public')->put($photoPath, $image);

            if (!$attendance) {
                // Clock in
                Attendance::create([
                    'user_id' => $user->id,
                    'schedule_latitude' => $schedule->office->latitude,
                    'schedule_longitude' => $schedule->office->longitude,
                    'schedule_waktu_datang' => $schedule->shift->waktu_datang,
                    'schedule_waktu_pulang' => $schedule->shift->waktu_pulang,
                    'datang_latitude' => $this->latitude,
                    'datang_longitude' => $this->longitude,
                    'waktu_datang' => Carbon::now()->toTimeString(),
                    'foto_absen_datang' => $photoPath,
                ]);
                session()->flash('message', 'Presensi masuk berhasil.');
            } else {
                // Clock out
                if ($attendance->waktu_pulang) {
                    session()->flash('error', 'Anda sudah melakukan presensi pulang hari ini.');
                    return;
                }

                $attendance->update([
                    'pulang_latitude' => $this->latitude,
                    'pulang_longitude' => $this->longitude,
                    'waktu_pulang' => Carbon::now()->toTimeString(),
                    'foto_absen_pulang' => $photoPath,
                    'logbook' => $this->logbook,
                ]);
                session()->flash('message', 'Presensi pulang berhasil.');
            }

            $this->reset(['photo', 'photoPreview', 'showPhotoUploadPage', 'logbook', 'showLogbookForm', 'isClockOut', 'photoTaken']);
            return redirect('admin/attendances');
        }
    }


    public function initiateAttendance()
    {
        $this->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($this->insideRadius) {
            $this->showPhotoUploadPage = true;
        } else {
            session()->flash('error', 'Anda berada di luar radius yang diizinkan.');
        }
    }

    public function backToMap()
    {
        $this->showPhotoUploadPage = false;
        $this->reset(['photo', 'photoPreview']);
    }

    private function decodeBase64Image($base64String)
    {
        // Remove data URI scheme if present
        $base64String = preg_replace('#^data:image/\w+;base64,#i', '', $base64String);
        return base64_decode($base64String);
    }
}