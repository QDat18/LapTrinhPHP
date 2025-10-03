<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use app\Models\VolunteerOpportunity;

class Favorite extends Model
{
    use HasFactory;

    protected $primaryKey = 'favorite_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'opportunity_id',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id', 'opportunity_id');
    }

    // Methods
    public function updateNotes(string $notes): void
    {
        $this->update(['notes' => $notes]);
    }
}
