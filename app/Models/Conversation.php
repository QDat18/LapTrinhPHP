<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use app\Models\VolunteerOpportunity;

class Conversation extends Model
{
    use HasFactory;

    protected $primaryKey = 'conversation_id';

    public $timestamps = false;

    protected $fillable = [
        'conversation_type',
        'title',
        'opportunity_id',
        'created_by',
        'last_message_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id', 'opportunity_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id', 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'conversation_id');
    }

    public function videoCalls(): HasMany
    {
        return $this->hasMany(VideoCall::class, 'conversation_id', 'conversation_id');
    }

    // Methods
    public function updateLastMessage(): void
    {
        $this->update(['last_message_at' => now()]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }
}
