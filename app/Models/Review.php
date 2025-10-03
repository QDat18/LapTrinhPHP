<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model{
    use HasFactory;
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'reviewer_id',
        'reviewee_id',
        'opportunity_id',
        'rating',
        'review_title',
        'review_text',
        'review_type',
        'is_approved',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
    ];
    
        public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'user_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id', 'user_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id');
    }

        public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('reviewee_id', $userId);
    }

    public function scopeByRating($query, $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function approve(){
        $this->is_approved = true;
        $this->save();
        $this->updateRevieweeRating();
    }

    public function reject(){
        $this->delete();
    }

    public function incrementHelpful(){
        $this->increment('helpful_count');
    }

    private function updateRevieweeRating()
    {
        $reviewee = User::find($this->reviewee_id);
        
        if ($reviewee->user_type == 'Volunteer') {
            $avgRating = Review::where('reviewee_id', $this->reviewee_id)
                ->where('is_approved', true)
                ->avg('rating');
            
            $profile = $reviewee->volunteerProfile;
            if ($profile) {
                $profile->volunteer_rating = round($avgRating, 2);
                $profile->save();
            }
        } elseif ($reviewee->user_type == 'Organization') {
            $avgRating = Review::where('reviewee_id', $this->reviewee_id)
                ->where('is_approved', true)
                ->avg('rating');
            
            $org = $reviewee->organization;
            if ($org) {
                $org->rating = round($avgRating, 2);
                $org->total_reviews = Review::where('reviewee_id', $this->reviewee_id)
                    ->where('is_approved', true)
                    ->count();
                $org->save();
            }
        }
    }
}