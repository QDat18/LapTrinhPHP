<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'date_of_birth',
        'gender',
        'city',
        'district',
        'address',
        'user_type',
        'avatar_url',
        'is_verified',
        'is_active',        
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function volunteerProfile():HasOne {
        return $this->hasOne(VolunteerProfile::class, 'user_id', 'user_id');
    }

    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class, 'user_id', 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'volunteer_id', 'user_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(VolunteerActivity::class, 'volunteer_id', 'user_id');
    }

    public function sentReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id', 'user_id');
    }
    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id', 'user_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'created_by', 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'user_id', 'user_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive($query){
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeVolunteers($query)
    {
        return $query->where('user_type', 'Volunteer');
    }

    public function scopeOrganizations($query)
    {
        return $query->where('user_type', 'Organization');
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'Admin');
    }
    
    public function isVolunteer():bool{
        return $this->user_type == 'Volunteer';
    }

    public function isOrganization(): bool{
        return $this->user_type == 'Organization';
    }

    public function isAdmin():bool{
        return $this->user_type == 'Admin';
    }

    public function markAsLoggedIn(){
        $this->update(['last_login_at' => now()]);
    }

    public function verify(): void
    {
        $this->update(['is_verified' => true]);
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