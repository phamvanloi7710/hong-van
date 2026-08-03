<?php

namespace App\Domain\PageBuilder;

final readonly class LayoutPreviewFixture
{
    public function __construct(private BlockRegistry $registry) {}

    /** @return array<string, mixed> */
    public function document(): array
    {
        $document = PageDocumentSchema::emptyDocument();
        $document['blocks'] = [
            $this->block('layout.section', 'layout-section-fixture', [
                $this->block('layout.container', 'layout-container-fixture', [
                    $this->block('layout.stack', 'layout-stack-fixture', [
                        $this->block('foundation.placeholder', 'layout-heading-fixture'),
                        $this->block('layout.divider', 'layout-divider-fixture'),
                    ]),
                    $this->block('layout.columns', 'layout-columns-fixture', [
                        $this->block('layout.container', 'layout-column-one-fixture', [$this->block('foundation.placeholder', 'layout-copy-one-fixture')]),
                        $this->block('layout.container', 'layout-column-two-fixture', [$this->block('foundation.placeholder', 'layout-copy-two-fixture')]),
                    ]),
                    $this->block('layout.grid', 'layout-grid-fixture', [
                        $this->block('foundation.placeholder', 'layout-card-one-fixture'),
                        $this->block('foundation.placeholder', 'layout-card-two-fixture'),
                        $this->block('foundation.placeholder', 'layout-card-three-fixture'),
                    ]),
                    $this->block('layout.spacer', 'layout-spacer-fixture'),
                ]),
            ]),
        ];

        return $document;
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    private function block(string $type, string $id, array $children = []): array
    {
        $definition = $this->registry->get($type);
        $block = ['id' => $id, 'type' => $type, 'version' => $definition->version, ...$definition->defaults];
        $block['children'] = $children;
        if ($type === 'foundation.placeholder') {
            $block['props']['label'] = $id;
        }

        return $block;
    }
}
