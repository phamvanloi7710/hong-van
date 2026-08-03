<?php

namespace App\Domain\Analytics;

final class AnalyticsEventPayload
{
    /** @return array{name: string, parameters: array{lead_type: string}} */
    public function leadSubmitted(string $leadType): array
    {
        return ['name' => 'lead_submit', 'parameters' => ['lead_type' => mb_substr(strip_tags($leadType), 0, 64)]];
    }

    /** @return array{name: string, parameters: array{product_public_id: string, locale: string}} */
    public function productViewed(string $productPublicId, string $locale): array
    {
        return [
            'name' => 'product_view',
            'parameters' => [
                'product_public_id' => preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $productPublicId) === 1 ? $productPublicId : '',
                'locale' => in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'vi',
            ],
        ];
    }
}
