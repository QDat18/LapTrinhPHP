<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model{
    use HasFactory;
    protected $primaryKey = 'category_id';

    public $timestamps = false;
    protected $fillable = [
        'category_name',
        'description',
        'icon',
        'color',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order'=> 'integer',
        'created_at' => 'datetime',
    ];

    public function opportunities():HasMany{
        return $this->hasMany(VolunteerOpportunity::class, 'category_id', 'category_id');
    }

    public function scopeActive($query){
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query){
        return $query->orderBy('display_order');
    }

    public function activate(){
        $this->update(['is_active' => true]);
    }

    public function deactive()
    {
        $this->update(['is_acative' => false]);
    }
}