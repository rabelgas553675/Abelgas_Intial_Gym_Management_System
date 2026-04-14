<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQrToken extends Model
{
    protected $fillable = ['user_id', 'role', 'name', 'qr_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}