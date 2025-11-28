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
        return 'mocktest-' . $this->id . '-' . uniqid();
    }

    public function canStart()
    {
        if ($this->status !== 'accepted') {
        return false;
    }
    
    $startTime = $this->scheduled_time;
    $endTime = $this->scheduled_time->copy()->addMinutes($this->duration_minutes);
    $now = now();
    
    // Session can start 15 minutes before scheduled time and until it ends
    return $now >= $startTime->subMinutes(15) && $now <= $endTime;
    }
}
