<?php

namespace App\Domain\PageBuilder;

final class PageDocumentSchema
{
    public const VERSION = 1;

    public const MAX_BYTES = 524288;

    public const MAX_DEPTH = 12;

    public const MAX_BLOCKS = 300;

    /** @return array<string, mixed> */
    public static function emptyDocument(): array
    {
        return [
            'schemaVersion' => self::VERSION,
            'themeVersionId' => null,
            'pageSettings' => [
                'container' => 'default',
                'background' => 'surface',
                'hideHeader' => false,
                'hideFooter' => false,
            ],
            'blocks' => [],
        ];
    }

    /** @return array<string, mixed> */
    public static function metadata(): array
    {
        return [
            'schemaVersion' => self::VERSION,
            'limits' => ['maxBytes' => self::MAX_BYTES, 'maxDepth' => self::MAX_DEPTH, 'maxBlocks' => self::MAX_BLOCKS],
            'blockFields' => ['id', 'type', 'version', 'props', 'style', 'visibility', 'bindings', 'children'],
            'pageSettings' => [
                'container' => ['default', 'wide', 'full'],
                'background' => ['surface', 'muted', 'brand'],
            ],
        ];
    }
}
