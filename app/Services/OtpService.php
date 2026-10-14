<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function generate(string $identifier, string $purpose): OtpCode
    {
        OtpCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $code = $this->demoCode() ?? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpCode::create([
            'identifier' => $identifier,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes((int) config('otp.expires_minutes', 2)),
        ]);

        Log::info('OTP generated', [
            'identifier' => $identifier,
            'purpose' => $purpose,
            'code' => config('otp.use_demo_code') ? $code : '[redacted]',
        ]);

        return $otp;
    }

    public function verify(string $identifier, string $code, string $purpose): bool
    {
        $demoCode = $this->demoCode();
        if ($demoCode && hash_equals($demoCode, $code)) {
            OtpCode::query()
                ->where('identifier', $identifier)
                ->where('purpose', $purpose)
                ->where('is_used', false)
                ->update(['is_used' => true]);

            return true;
        }

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

    private function demoCode(): ?string
    {
        if (! config('otp.use_demo_code')) {
            return null;
        }

        $code = (string) config('otp.demo_code', '123456');

        return $code !== '' ? $code : null;
    }
}
