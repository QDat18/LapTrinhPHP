<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use app\Models\User;

class VolunteerProfile extends Model
{
    use HasFactory;
    protected $table = 'volunteer_profiles';
    protected $primaryKey = 'profile_id';

    protected $fillable = [
        'user_id',
        'occupation',
        'education_level',
        'university',
        'bio',
        'skills',
        'interests',
        'availability',
        'volunteer_experience',
        'total_volunteer_hours',
        'volunteer_rating',
        'preferred_location',
        'transportation',
    ];

    protected $casts = [
        'skills' => 'array',
        'total_volunteer_hours' => 'integer',
        'volunteer_rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    public function getCompletionPercentageAttribute(): int
    {
        $fields = [
            'occupation', 'education_level', 'university', 'bio',
            'skills', 'interests', 'availability', 'volunteer_experience',
            'preferred_location', 'transportation'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return (int) (($completed / count($fields)) * 100);
    }

    public function addHours(float $hours): void
    {
        $this->increment('total_volunteer_hours', $hours);
    }

    public function updateRating(float $newRating, int $reviewCount): void
    {
        $currentTotal = (float) $this->volunteer_rating * ($reviewCount - 1);
        $this->volunteer_rating = ($currentTotal + $newRating) / $reviewCount;
        $this->save();
    }

    public function hasSkill(string $skill): bool
    {
        return in_array($skill, $this->skills ?? []);
    }

    public function addSkill(string $skill): void
    {
        $skills = $this->skills ?? [];
        if (!in_array($skill, $skills)) {
            $skills[] = $skill;
            $this->skills = $skills;
            $this->save();
        }
    }

    public function removeSkill(string $skill): void
    {
        $skills = $this->skills ?? [];
        if (($key = array_search($skill, $skills)) !== false) {
            unset($skills[$key]);
            $this->skills = array_values($skills);
            $this->save();
        }
    }    
}
