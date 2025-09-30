<?php

namespace App\Services\Mfa;

class TotpGenerator
{
    public function __construct(
        private readonly int $digits = 6,
        private readonly int $period = 30,
        private readonly string $hashAlgorithm = 'sha1'
    ) {
    }

    public function generateSecret(int $bytes = 20): string
    {
        return rtrim($this->base32Encode(random_bytes($bytes)), '=');
    }

    public function getCurrentOtp(string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();

        return $this->at($secret, (int) floor($timestamp / $this->period));
    }

    public function verify(string $secret, string $otp, int $window = 1): bool
    {
        $normalizedOtp = preg_replace('/\s+/', '', $otp ?? '');
        if ($normalizedOtp === null || !preg_match('/^\d{' . $this->digits . '}$/', $normalizedOtp)) {
            return false;
        }

        $timeSlice = (int) floor(time() / $this->period);
        $binarySecret = $this->base32Decode($secret);

        for ($i = -$window; $i <= $window; $i++) {
            $candidate = $this->hotp($binarySecret, $timeSlice + $i);
            if (hash_equals($candidate, $normalizedOtp)) {
                return true;
            }
        }

        return false;
    }

    public function getProvisioningUri(string $label, string $issuer, string $secret): string
    {
        $encodedLabel = rawurlencode($label);
        $encodedIssuer = rawurlencode($issuer);

        return sprintf('otpauth://totp/%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d',
            $encodedIssuer . ':' . $encodedLabel,
            $secret,
            $encodedIssuer,
            strtoupper($this->hashAlgorithm),
            $this->digits,
            $this->period
        );
    }

    private function at(string $secret, int $counter): string
    {
        $binarySecret = $this->base32Decode($secret);

        return $this->hotp($binarySecret, $counter);
    }

    private function hotp(string $binarySecret, int $counter): string
    {
        $counterBytes = pack('N*', 0, $counter);
        $hash = hash_hmac($this->hashAlgorithm, $counterBytes, $binarySecret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $segment = substr($hash, $offset, 4);
        $value = unpack('N', $segment)[1] & 0x7FFFFFFF;
        $modulo = 10 ** $this->digits;

        return str_pad((string) ($value % $modulo), $this->digits, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($data) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($bits, 5);
        $encoded = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $encoded .= $alphabet[bindec($chunk)];
        }

        return $encoded;
    }

    private function base32Decode(string $secret): string
    {
        $cleanSecret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $alphabetMap = array_flip(str_split($alphabet));

        $bits = '';
        foreach (str_split($cleanSecret) as $char) {
            if (!isset($alphabetMap[$char])) {
                continue;
            }
            $bits .= str_pad(decbin($alphabetMap[$char]), 5, '0', STR_PAD_LEFT);
        }

        $bytes = str_split($bits, 8);
        $decoded = '';
        foreach ($bytes as $byte) {
            if (strlen($byte) === 8) {
                $decoded .= chr(bindec($byte));
            }
        }

        return $decoded;
    }
}
