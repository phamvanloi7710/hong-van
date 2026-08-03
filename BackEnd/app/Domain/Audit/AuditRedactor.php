<?php

namespace App\Domain\Audit;

final class AuditRedactor
{
    private const REDACTED = '[REDACTED]';

    /** @var list<string> */
    private const SENSITIVE_KEY_PARTS = [
        'password',
        'passwd',
        'secret',
        'token',
        'cookie',
        'authorization',
        'api_key',
        'apikey',
        'private_key',
        'file',
        'upload',
        'contents',
        'email',
        'phone',
        'message',
        'payload',
        'address',
        'location',
        'description',
        'note',
        'contact_name',
        'company',
    ];

    /** @param array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    public function redact(array $data): array
    {
        $redacted = [];

        foreach ($data as $key => $value) {
            $redacted[$key] = is_string($key) && $this->isSensitiveKey($key)
                ? self::REDACTED
                : $this->redactValue($value);
        }

        return $redacted;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SENSITIVE_KEY_PARTS as $part) {
            if (str_contains($normalized, $part)) {
                return true;
            }
        }

        return false;
    }

    private function redactValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->redact($value);
        }

        if (is_object($value)) {
            return self::REDACTED;
        }

        if (is_string($value)) {
            return mb_substr($value, 0, max(1, (int) config('security.audit.max_string_length', 2000)));
        }

        return is_scalar($value) || $value === null ? $value : self::REDACTED;
    }
}
