<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserQrToken extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'role',
        'qr_token',
        'qr_code_path'
    ];

    public static function createForUser(User $user): self
    {
        // Shortened from 16 bytes (32 hex chars) to 8 bytes (16 hex chars)
        $token = 'STF-' . strtoupper(bin2hex(random_bytes(8)));

        $record = self::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name'     => $user->name,
                'role'     => $user->role,
                'qr_token' => $token,
            ]
        );

        self::generateStaffQrCode($record);

        return $record;
    }

    public static function generateStaffQrCode(self $record): void
    {
        $fileName = 'STAFF-' . $record->user_id . '-' . time() . '.svg';
        $folder = storage_path('app/public/qrcodes/staff');
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
        $path = 'qrcodes/staff/' . $fileName;

        // Just encode the token — role/id can be looked up server-side after scan
        $qrContent = $record->qr_token;

        QrCode::format('svg')
            ->size(300)
            ->errorCorrection('M') // was 'H' — 'M' still recovers ~15% damage, much smaller QR
            ->generate($qrContent, storage_path('app/public/' . $path));

        $record->update(['qr_code_path' => $path]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}