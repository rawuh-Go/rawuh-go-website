<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'gender',
        'address',
        'phone_number',
        'country',
        'password',
        'image',
        'job_position',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getImageAttribute($value)
    {
        if (!$value) {
            return null; // Return null akan memicu default image
        }
        return $value;
    }

    // method helper untuk mendapatkan URL gambar
    public function getImageUrl()
    {
        if (!$this->image) {
            return url('/images/default-avatar.jpg');
        }

        return Storage::disk('public')->url($this->image);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function assignments()
    {
        return $this->belongsToMany(Assignment::class)->withPivot('laporan', 'file_laporan')->withTimestamps();
    }

    public function createdAssignments()
    {
        return $this->hasMany(Assignment::class, 'created_by');
    }

}
