<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        return static::where('key', $key)->value('value', $default);
    }
}
