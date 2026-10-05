<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class CoachRequest extends Model
{
    use Auditable;

    protected string $auditModule = 'Coach Requests';

    public function auditLabel(): string
    {
        return '#' . $this->getKey() . ' (' . $this->status . ')';
    }

    protected $fillable = [
        'member_id',
        'instructor_id',
        'status',
        'message',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}