<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockTestSession extends Model
{
    /** @use HasFactory<\Database\Factories\MockTestSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'title',
        'description',
        'proposed_time',
        'scheduled_time',
        'status',
        'jitsi_room_name',
        'rejection_reason',
        'teacher_notes',
        'duration_minutes',
        'recording_url',
        'screen_sharing_data',
    ];

    protected $casts = [
        'proposed_time' => 'datetime',
        'scheduled_time' => 'datetime',
        'screen_sharing_data' => 'array',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_time', '>', now());
    }

    // Methods
    public function generateRoomName()
    {
        return 'mocktest-' . $this->id . '-' . time() . '-' . rand(1000, 9999);
    }

    public function canStart()
{
    if ($this->status !== 'accepted') {
        return false;
    }
    
    if (!$this->scheduled_time) {
        return false;
    }
    
    // Pastikan menggunakan timezone yang sama
    $startTime = $this->scheduled_time->copy()->timezone(config('app.timezone'));
    $endTime = $this->scheduled_time->copy()->addMinutes($this->duration_minutes)->timezone(config('app.timezone'));
    $now = now()->timezone(config('app.timezone'));
    
    // Session bisa mulai 3 menit sebelum scheduled_time
    $earliestStart = $startTime->subMinutes(3);
    
    return $now >= $earliestStart && $now <= $endTime;
}

// Tambahkan method untuk auto-timezone
protected static function boot()
{
    parent::boot();
    
    static::retrieved(function ($model) {
        // Otomatis convert ke timezone aplikasi saat retrieve
        if ($model->proposed_time) {
            $model->proposed_time->setTimezone(config('app.timezone'));
        }
        if ($model->scheduled_time) {
            $model->scheduled_time->setTimezone(config('app.timezone'));
        }
    });
}
}
