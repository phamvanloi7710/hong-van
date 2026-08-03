<?php

namespace App\Domain\Search;

use Illuminate\Support\Str;
use Normalizer;

final class SearchTermNormalizer
{
    public function normalize(string $term): string
    {
        $term = strip_tags($term);
        $term = preg_replace('/[\p{Cc}\p{Cf}]+/u', ' ', $term) ?? '';
        if (class_exists(Normalizer::class)) {
            $term = Normalizer::normalize($term, Normalizer::FORM_C) ?: $term;
        }

        return Str::limit(Str::squish($term), (int) config('search.max_query_length', 100), '');
    }

    public function booleanQuery(string $normalizedTerm): string
    {
        preg_match_all('/[\p{L}\p{N}]+/u', $this->fold($normalizedTerm), $matches);
        $tokens = array_values(array_unique(array_filter(
            $matches[0],
            static fn (string $token): bool => mb_strlen($token) >= 2,
        )));

        return implode(' ', array_map(static fn (string $token): string => '+'.$token.'*', $tokens));
    }

    public function fold(string $value): string
    {
        $decomposed = class_exists(Normalizer::class) ? (Normalizer::normalize($value, Normalizer::FORM_D) ?: $value) : $value;
        $withoutMarks = preg_replace('/\p{Mn}+/u', '', $decomposed) ?? $decomposed;

        return mb_strtolower(str_replace(['đ', 'Đ'], ['d', 'D'], $withoutMarks));
    }

    public function redactPersonalData(string $normalizedTerm): string
    {
        $redacted = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/iu', '[email]', $normalizedTerm) ?? $normalizedTerm;

        return preg_replace('/(?<!\p{N})(?:\+?\d[\d\s().-]{6,}\d)(?!\p{N})/u', '[phone]', $redacted) ?? $redacted;
    }
}
