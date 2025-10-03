<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoCall extends Model
{
    use HasFactory;

    protected $primaryKey = 'call_id';

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'initiated_by',
        'call_type',
        'call_status',
        'room_id',
        'started_at',
        'ended_at',
        'duration',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'conversation_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by', 'user_id');
    }

    // Accessors
    public function getDurationMinutesAttribute(): int
    {
        return (int) ($this->duration / 60);
    }

    // Methods
    public function start(): void
    {
        $this->update([
            'call_status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function end(): void
    {
        $endTime = now();
        $duration = $this->started_at ? $this->started_at->diffInSeconds($endTime) : 0;
        
        $this->update([
            'call_status' => 'ended',
            'ended_at' => $endTime,
            'duration' => $duration,
        ]);
    }

    public function markMissed(): void
    {
        $this->update(['call_status' => 'missed']);
    }

    public function decline(): void
    {
        $this->update(['call_status' => 'declined']);
    }
}