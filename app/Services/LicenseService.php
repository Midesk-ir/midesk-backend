<?php

namespace App\Services;

use App\Models\License;

class LicenseInfo
{
    public function __construct(
        public bool $valid,
        public int $maxUsers = 25,
        public array $features = [],
        public ?string $message = null,
    ) {}
}

class LicenseService
{
    public function validate(string $key): LicenseInfo
    {
        $license = License::withoutGlobalScopes()->where('license_key', $key)->first();

        if (! $license) {
            return $this->validateOfflineSignature($key);
        }

        if (! $license->isValid()) {
            return new LicenseInfo(false, message: 'License expired or inactive');
        }

        return new LicenseInfo(true, $license->max_users, $license->features ?? []);
    }

    public function isFeatureEnabled(string $feature): bool
    {
        if (! config('license.enabled')) {
            return true;
        }

        $key = config('app.license_key');
        if (! $key) {
            return false;
        }

        $info = $this->validate($key);

        return $info->valid && (empty($info->features) || in_array($feature, $info->features, true));
    }

    public function maxUsers(): int
    {
        if (! config('license.enabled')) {
            return PHP_INT_MAX;
        }

        $key = config('app.license_key');
        if (! $key) {
            return 25;
        }

        return $this->validate($key)->maxUsers;
    }

    private function validateOfflineSignature(string $key): LicenseInfo
    {
        $publicKey = config('license.public_key');
        if (! $publicKey) {
            return new LicenseInfo(true, 25, [], 'No license validation configured');
        }

        $parts = explode('.', $key, 2);
        if (count($parts) !== 2) {
            return new LicenseInfo(false, message: 'Invalid license format');
        }

        [$payloadB64, $signatureB64] = $parts;
        $payload = json_decode(base64_decode($payloadB64), true);
        if (! is_array($payload)) {
            return new LicenseInfo(false, message: 'Invalid license payload');
        }

        $valid = openssl_verify(
            $payloadB64,
            base64_decode($signatureB64),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ) === 1;

        if (! $valid) {
            return new LicenseInfo(false, message: 'Invalid license signature');
        }

        if (isset($payload['valid_until']) && now()->gt($payload['valid_until'])) {
            return new LicenseInfo(false, message: 'License expired');
        }

        return new LicenseInfo(
            true,
            $payload['max_users'] ?? 25,
            $payload['features'] ?? []
        );
    }
}
