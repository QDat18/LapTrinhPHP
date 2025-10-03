<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $primaryKey = 'participant_id';

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'joined_at',
        'last_read_at',
        'unread_count',
        'is_active',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
        'unread_count' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Methods
    public function markAsRead(): void
    {
        $this->update([
            'last_read_at' => now(),
            'unread_count' => 0,
        ]);
    }

    public function incrementUnread(): void
    {
        $this->increment('unread_count');
    }

    public function leave(): void
    {
        $this->update(['is_active' => false]);
    }

    public function rejoin(): void
    {
        $this->update(['is_active' => true]);
    }
}

