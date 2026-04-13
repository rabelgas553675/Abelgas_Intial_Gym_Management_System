<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'phone', 'gender', 'birthdate', 'address',
        'photo', 'specialization', 'experience_years',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_login_at'     => 'datetime',
            'birthdate'         => 'date',
        ];
    }

    // ── Role Checks ────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function canManageMembers(): bool
    {
        return in_array($this->role, ['admin', 'staff']);
    }

    // ── Relationships ──────────────────────────────────────────

    /**
     * Get the member profile associated with the user (if role is member).
     */
    public function memberProfile()
    {
        return $this->hasOne(Member::class, 'user_id');
    }

    /**
     * Get the members assigned to this user (if role is instructor).
     */
    public function assignedMembers()
    {
        return $this->hasMany(Member::class, 'instructor_id');
    }

    /**
     * Get the workout plans created by this user (instructor).
     */
    public function createdWorkoutPlans()
    {
        return $this->hasMany(WorkoutPlan::class, 'instructor_id');
    }
}