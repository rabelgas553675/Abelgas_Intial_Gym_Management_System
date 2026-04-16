<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserQrToken extends Model
{
    // Tell Laravel not to look for created_at and updated_at columns
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'role',
        'qr_token',
        'qr_code_path'
    ];

    /**
     * Generate and save a QR code image for staff members
     */
    public static function generateStaffQrCode(self $record): void
    {
        $fileName = 'STAFF-' . $record->user_id . '-' . time() . '.svg';

        // Ensure folder exists in storage/app/public/qrcodes/staff
        $folder = storage_path('app/public/qrcodes/staff');
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        $path = 'qrcodes/staff/' . $fileName;

        // Use actual role in QR content: ADMIN, STAFF, or INSTRUCTOR
        $roleCode = strtoupper($record->role); // ADMIN | STAFF | INSTRUCTOR
        $qrContent = "IRONFORGE|{$roleCode}|{$record->user_id}|{$record->qr_token}";

        QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrContent, storage_path('app/public/' . $path));

        // Update the path in the database
        $record->update(['qr_code_path' => $path]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}