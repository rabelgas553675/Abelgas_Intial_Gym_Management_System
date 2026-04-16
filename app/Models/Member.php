<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        'qr_id',           // From QR logic
        'qr_code_path',    // From QR logic
        'qr_token',        // From QR logic
        'qr_code',         // From your original fillable
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'birthdate'  => 'date',
    ];

    /**
     * Generate a unique QR code for the member
     */
    public static function generateQrCode(self $member): void
    {
        // Unique ID format: IFG-MEM-000001
        $qrId = 'IFG-MEM-' . str_pad($member->id, 6, '0', STR_PAD_LEFT);
        
        // Ensure folder exists in storage/app/public/qrcodes
        $folder = storage_path('app/public/qrcodes');
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        $path = 'qrcodes/' . $qrId . '.svg';

        // Generate the SVG file
        QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrId, storage_path('app/public/' . $path));

        // Generate a random token for the attendance scanner logic
        $token = 'MBR-' . strtoupper(bin2hex(random_bytes(16)));

        // Update the member record
        $member->update([
            'qr_id'        => $qrId,
            'qr_code_path' => $path,
            'qr_token'     => $token
        ]);
    }

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