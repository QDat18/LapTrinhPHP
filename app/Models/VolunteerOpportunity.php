<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use app\Models\VolunteerActivity;
use app\Models\Organization;
use app\Models\Category;
use app\Models\Favorite;
use app\Models\Application;

class VolunteerOpportunity extends Model{
    use HasFactory;
    protected $primaryKey = 'opportunity_id';

    protected $fillable = [
        'org_id',
        'category_id',
        'title',
        'description',
        'requirements',
        'benefits',
        'location',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'time_commitment',
        'schedule_type',
        'volunteers_needed',
        'volunteers_registered',
        'min_age',
        'required_skills',
        'experience_needed',
        'status',
        'application_deadline',
        'view_count',
        'application_count',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'start_date' => 'date',
        'end_date' => 'date',
        'application_deadline' => 'date',
        'volunteers_needed' => 'integer',
        'volunteers_registered' => 'integer',
        'min_age' => 'integer',
        'required_skills' => 'array',
        'view_count' => 'integer',
        'application_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id', 'org_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'opportunity_id', 'opportunity_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(VolunteerActivity::class, 'opportunity_id', 'opportunity_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'opportunity_id', 'opportunity_id');
    }    
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
                     ->where('status', 'Active');
    }

    public function scopeNotFull($query)
    {
        return $query->whereColumn('volunteers_registered', '<', 'volunteers_needed');
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByLocation($query, string $city)
    {
        return $query->where('location', 'like', "%{$city}%");
    }

    public function getFillPercentageAttribute(): int
    {
        if ($this->volunteers_needed == 0) return 0;
        return (int) (($this->volunteers_registered / $this->volunteers_needed) * 100);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->volunteers_registered >= $this->volunteers_needed;
    }

    public function getIsDeadlinePassedAttribute(): bool
    {
        if (!$this->application_deadline) return false;
        return Carbon::today()->gt($this->application_deadline);
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function addApplication(): void
    {
        $this->increment('application_count');
    }

    public function registerVolunteer(): bool
    {
        if ($this->volunteers_registered < $this->volunteers_needed) {
            $this->increment('volunteers_registered');
            return true;
        }
        return false;
    }

    public function unregisterVolunteer(): bool
    {
        if ($this->volunteers_registered > 0) {
            $this->decrement('volunteers_registered');
            return true;
        }
        return false;
    }

    public function pause(): void
    {
        $this->update(['status' => 'Paused']);
    }

    public function resume(): void
    {
        $this->update(['status' => 'Active']);
    }

    public function complete(): void
    {
        $this->update(['status' => 'Completed']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'Cancelled']);
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }    
}