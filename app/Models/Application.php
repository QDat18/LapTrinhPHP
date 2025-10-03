<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\VolunteerOpportunity;

class Application extends Model{
    use HasFactory;
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'opportunity_id',
        'volunteer_id',
        'motivation_letter',
        'relevant_experience',
        'availability_note',
        'status',
        'applied_date',
        'reviewed_date',
        'organization_notes',
        'interview_scheduled',
    ];

    protected $casts = [
        'applied_date' => 'datetime',
        'reviewed_date' => 'datetime',
        'interview_scheduled' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id', 'opportunity_id');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id', 'user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'Accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'Under Review');
    }

    // Methods
    public function accept(?string $notes = null): void
    {
        $this->update([
            'status' => 'Accepted',
            'reviewed_date' => now(),
            'organization_notes' => $notes,
        ]);
    }

    public function reject(?string $notes = null): void
    {
        $this->update([
            'status' => 'Rejected',
            'reviewed_date' => now(),
            'organization_notes' => $notes,
        ]);
    }

    public function withdraw(): void
    {
        $this->update(['status' => 'Withdrawn']);
    }

    public function markUnderReview(): void
    {
        $this->update(['status' => 'Under Review']);
    }

    public function scheduleInterview(\DateTime $interviewTime): void
    {
        $this->update([
            'interview_scheduled' => $interviewTime,
            'status' => 'Under Review',
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'Accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }
}