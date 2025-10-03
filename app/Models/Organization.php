<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model{
    use HasFactory;

    protected $primaryKey = 'org_id';
    protected $fillable = [
        'user_id',
        'organization_name',
        'organization_type',
        'description',
        'mission_statement',
        'website',
        'contact_person',
        'registration_number',
        'verification_status',
        'founded_year',
        'volunteer_count',
        'rating',
        'total_opportunities',        
    ];

    protected $casts = [
        'verification_status' => 'string',
        'founded_year' => 'integer',
        'volunteer_count' => 'integer',
        'rating' => 'decimal:2',
        'total_opportunities' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user() : BelongsTo{
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function opportunities() : HasMany{
        return $this->hasMany(VolunteerOpportunity::class, 'org_id', 'org_id');
    }

    public function activities():HasMany{
        return $this->hasMany(VolunteerActivity::class, 'org_id', 'org_id');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'Verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'Pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'Rejected');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('organization_type', $type);
    }

    public function verify(): void
    {
        $this->update(['verification_status' => 'Verified']);
    }

    public function reject(): void
    {
        $this->update(['verification_status' => 'Rejected']);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'Verified';
    }

    public function incrementVolunteerCount(): void
    {
        $this->increment('volunteer_count');
    }

    public function decrementVolunteerCount(): void
    {
        if ($this->volunteer_count > 0) {
            $this->decrement('volunteer_count');
        }
    }

    public function incrementOpportunities(): void
    {
        $this->increment('total_opportunities');
    }

    public function updateRating(float $newRating, int $reviewCount): void
    {
        $currentTotal = (float) $this->rating * ($reviewCount - 1);
        $this->rating = ($currentTotal + $newRating) / $reviewCount;
        $this->save();
    }    
}
