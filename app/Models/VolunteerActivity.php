<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use app\Models\VolunteerOpportunity;
class VolunteerActivity extends Model
{
    use HasFactory;

    protected $primaryKey = 'activity_id';

    public $timestamps = false;

    protected $fillable = [
        'volunteer_id',
        'opportunity_id',
        'org_id',
        'activity_date',
        'hours_worked',
        'activity_description',
        'status',
        'verified_by',
        'verified_date',
        'impact_notes',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'hours_worked' => 'decimal:2',
        'verified_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id', 'user_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id', 'opportunity_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id', 'org_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('status', 'Verified');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'Disputed');
    }

    // Methods
    public function verify(int $verifiedBy): void
    {
        $this->update([
            'status' => 'Verified',
            'verified_by' => $verifiedBy,
            'verified_date' => now(),
        ]);
    }

    public function dispute(): void
    {
        $this->update(['status' => 'Disputed']);
    }

    public function isVerified(): bool
    {
        return $this->status === 'Verified';
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function canBeLogged(): bool
    {
        $daysDiff = Carbon::today()->diffInDays($this->activity_date, false);
        return $daysDiff >= -7 && $daysDiff <= 0;
    }
}