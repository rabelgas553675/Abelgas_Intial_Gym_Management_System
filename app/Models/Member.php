<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Member extends Model
{
    protected $fillable = [
        'user_id', 
        'instructor_id',
        'name', 
        'first_name', 
        'last_name',
        'email', 
        'phone', 
        'gender', 
        'birthdate', 
        'address',
        'membership_type', 
        'fitness_plan',
        'start_date', 
        'end_date', 
        'fee', 
        'status', 
        'photo', 
        'qr_code',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'birthdate'  => 'date',
    ];

    /**
     * Accessors
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name
            ? trim($this->first_name . ' ' . $this->last_name)
            : $this->name;
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function workoutPlans()
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    /**
     * Business Logic Methods
     */
    public function isDueWithinDays(int $days = 7): bool
    {
        if (!$this->end_date) return false;
        return $this->end_date->isFuture()
            && $this->end_date->diffInDays(now()) <= $days;
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }
}