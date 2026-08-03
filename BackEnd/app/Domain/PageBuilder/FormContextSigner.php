<?php

namespace App\Domain\PageBuilder;

final class FormContextSigner
{
    public function sign(string $formType, string $blockId, string $contextType, string $contextPublicId, int $ttlSeconds = 7200): string
    {
        $payload = $this->encode(json_encode([
            'form' => $formType,
            'block' => $blockId,
            'context_type' => $contextType,
            'context_public_id' => $contextPublicId,
            'expires_at' => now('UTC')->addSeconds(max(60, $ttlSeconds))->getTimestamp(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        return $payload.'.'.hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    /** @return array{form:string,block:string,context_type:string,context_public_id:string,expires_at:int}|null */
    public function verify(string $token, string $expectedForm): ?array
    {
        if (strlen($token) > 4096 || substr_count($token, '.') !== 1) {
            return null;
        }
        [$payload, $signature] = explode('.', $token, 2);
        if (! hash_equals(hash_hmac('sha256', $payload, (string) config('app.key')), $signature)) {
            return null;
        }
        $json = $this->decode($payload);
        $data = is_string($json) ? json_decode($json, true) : null;
        if (! is_array($data)
            || ($data['form'] ?? null) !== $expectedForm
            || ($data['context_type'] ?? null) !== 'product'
            || ! is_string($data['block'] ?? null)
            || ! is_string($data['context_public_id'] ?? null)
            || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $data['context_public_id']) !== 1
            || ! is_int($data['expires_at'] ?? null)
            || $data['expires_at'] < now('UTC')->getTimestamp()) {
            return null;
        }

        return [
            'form' => $data['form'], 'block' => $data['block'], 'context_type' => $data['context_type'],
            'context_public_id' => $data['context_public_id'], 'expires_at' => $data['expires_at'],
        ];
    }

    private function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function decode(string $value): ?string
    {
        $remainder = strlen($value) % 4;
        if ($remainder !== 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return is_string($decoded) ? $decoded : null;
    }
}
