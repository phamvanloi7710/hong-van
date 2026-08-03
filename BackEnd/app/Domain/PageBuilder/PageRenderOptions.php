<?php

namespace App\Domain\PageBuilder;

final readonly class PageRenderOptions
{
    public function __construct(
        public string $locale,
        public bool $preview = false,
        public bool $sampleData = false,
        public ?string $contextType = null,
        public ?string $contextPublicId = null,
    ) {}

    public static function published(?string $locale = null): self
    {
        return new self($locale ?? app()->getLocale());
    }

    public function mayUseSampleData(): bool
    {
        return $this->preview && $this->sampleData;
    }
}
