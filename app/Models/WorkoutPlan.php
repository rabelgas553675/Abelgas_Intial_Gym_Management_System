<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class WorkoutPlan extends Model
{
    use Auditable;

    protected string $auditModule = 'Workout Plans';

    public function auditLabel(): string
    {
        return $this->title ?? '#' . $this->getKey();
    }

    protected $fillable = [
        'instructor_id', 'member_id', 'title', 'description',
        'scheduled_date', 'category', 'intensity', 'exercises', 'is_completed',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'exercises'      => 'array',
        'is_completed'   => 'boolean',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}