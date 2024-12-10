<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'created_by',
        'jenis_project',
        'deskripsi',
        'tanggal_deadline',
        'status',
        'feedback',
    ];

    protected $casts = [
        'tanggal_deadline' => 'date',
    ];

    // Default values
    protected $attributes = [
        'status' => 'pending',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE = 'done';
    const STATUS_REJECTED = 'rejected';

    // Project type constants
    const TYPE_TIM = 'tim';
    const TYPE_PERSONAL = 'personal';

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('laporan', 'file_laporan', 'link_laporan')
            ->withTimestamps();
    }

}