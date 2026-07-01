<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpService
{
    public function generate(string $identifier, string $purpose): OtpCode
    {
        OtpCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpCode::create([
            'identifier' => $identifier,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(2),
        ]);

        Log::info('OTP generated', [
            'identifier' => $identifier,
            'purpose' => $purpose,
            'code' => app()->environment('local') ? $code : '[redacted]',
        ]);

        return $otp;
    }

    public function verify(string $identifier, string $code, string $purpose): bool
    {
        $otp = OtpCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (! $otp?->isValid($code)) {
            return false;
        }

        $otp->update(['is_used' => true]);

        return true;
    }
}
