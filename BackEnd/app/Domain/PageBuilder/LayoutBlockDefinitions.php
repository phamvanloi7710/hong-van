<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use App\Domain\PageBuilder\Rendering\ColumnsBlockRenderer;
use App\Domain\PageBuilder\Rendering\ContainerBlockRenderer;
use App\Domain\PageBuilder\Rendering\DividerBlockRenderer;
use App\Domain\PageBuilder\Rendering\GridBlockRenderer;
use App\Domain\PageBuilder\Rendering\SectionBlockRenderer;
use App\Domain\PageBuilder\Rendering\SpacerBlockRenderer;
use App\Domain\PageBuilder\Rendering\StackBlockRenderer;
use App\Domain\PageBuilder\Sanitization\LayoutBlockSanitizer;

final class LayoutBlockDefinitions
{
    private const SPACING = ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'];

    private const ALIGN = ['start', 'center', 'end', 'stretch'];

    private const LAYOUT_PARENTS = ['layout.section', 'layout.container', 'layout.stack', 'layout.grid', 'layout.columns'];

    private const CONTENT_CHILDREN = [
        'layout.container', 'layout.stack', 'layout.grid', 'layout.columns', 'layout.spacer', 'layout.divider',
        'foundation.placeholder',
        'content.heading', 'content.rich-text', 'content.button', 'content.icon', 'content.list', 'content.quote',
        'content.table', 'content.badge', 'content.card', 'media.image', 'media.image-text', 'media.gallery',
        'media.video-embed', 'media.logo-cloud', 'content.faq',
        'business.hero', 'business.product-grid', 'business.category-grid', 'business.crop-grid',
        'business.service-grid', 'business.fleet', 'business.route-list', 'business.warehouse-cards',
        'business.stats', 'business.partner-logos', 'business.certificate-list', 'business.project-list',
        'business.post-list', 'business.cta', 'business.breadcrumb',
        'form.contact', 'form.product-quote', 'form.transport-request', 'form.warehouse-request',
    ];

    /** @return list<BlockDefinition> */
    public static function definitions(): array
    {
        return [
            self::section(),
            self::container(),
            self::stack(),
            self::grid(),
            self::columns(),
            self::spacer(),
            self::divider(),
        ];
    }

    private static function section(): BlockDefinition
    {
        $defaults = [
            'props' => ['background' => 'transparent', 'ariaLabel' => ''],
            'style' => self::responsiveDefaults(['paddingY' => 'xl', 'paddingX' => 'none', 'align' => 'stretch']),
            'visibility' => self::visibilityDefaults(), 'bindings' => [], 'children' => [],
        ];

        return self::definition(
            type: 'layout.section', labels: ['vi' => 'Khu vực', 'en' => 'Section', 'zh' => '区段'], icon: 'fa-regular fa-square',
            props: self::object([
                'background' => self::enum(['transparent', 'surface', 'surface-muted', 'brand', 'brand-soft', 'gradient-brand', 'media']),
                'backgroundMediaId' => ['type' => 'string', 'pattern' => '^[0-9A-HJKMNP-TV-Z]{26}$'],
                'ariaLabel' => ['type' => 'string', 'maxLength' => 160],
            ], ['background', 'ariaLabel']),
            style: self::responsiveSchema([
                'paddingY' => self::enum(self::SPACING), 'paddingX' => self::enum(self::SPACING), 'align' => self::enum(self::ALIGN),
            ], ['paddingY', 'paddingX', 'align']),
            defaults: $defaults, allowRoot: true, parents: [], children: self::CONTENT_CHILDREN,
            maxDepth: 1, minChildren: 0, maxChildren: 100, renderer: SectionBlockRenderer::class,
        );
    }

    private static function container(): BlockDefinition
    {
        $defaults = [
            'props' => ['width' => 'default'],
            'style' => self::responsiveDefaults(['paddingX' => 'none']),
            'visibility' => self::visibilityDefaults(), 'bindings' => [], 'children' => [],
        ];

        return self::definition(
            type: 'layout.container', labels: ['vi' => 'Khung chứa', 'en' => 'Container', 'zh' => '容器'], icon: 'fa-regular fa-window-maximize',
            props: self::object(['width' => self::enum(['narrow', 'default', 'wide', 'full'])], ['width']),
            style: self::responsiveSchema(['paddingX' => self::enum(self::SPACING)], ['paddingX']),
            defaults: $defaults, allowRoot: false, parents: ['layout.section', 'layout.stack', 'layout.grid', 'layout.columns'],
            children: array_values(array_diff(self::CONTENT_CHILDREN, ['layout.container'])), maxDepth: 8, minChildren: 0, maxChildren: 100,
            renderer: ContainerBlockRenderer::class,
        );
    }

    private static function stack(): BlockDefinition
    {
        $defaults = [
            'props' => ['wrap' => false],
            'style' => self::responsiveDefaults(['gap' => 'md', 'direction' => 'vertical', 'align' => 'stretch', 'justify' => 'start']),
            'visibility' => self::visibilityDefaults(), 'bindings' => [], 'children' => [],
        ];

        return self::definition(
            type: 'layout.stack', labels: ['vi' => 'Xếp chồng', 'en' => 'Stack', 'zh' => '堆叠'], icon: 'fa-solid fa-bars-staggered',
            props: self::object(['wrap' => ['type' => 'boolean']], ['wrap']),
            style: self::responsiveSchema([
                'gap' => self::enum(self::SPACING), 'direction' => self::enum(['vertical', 'horizontal']),
                'align' => self::enum(self::ALIGN), 'justify' => self::enum(['start', 'center', 'end', 'between']),
            ], ['gap', 'direction', 'align', 'justify']),
            defaults: $defaults, allowRoot: false, parents: ['layout.section', 'layout.container', 'layout.grid', 'layout.columns'],
            children: array_values(array_diff(self::CONTENT_CHILDREN, ['layout.stack'])), maxDepth: 8, minChildren: 0, maxChildren: 100,
            renderer: StackBlockRenderer::class,
        );
    }

    private static function grid(): BlockDefinition
    {
        $defaults = [
            'props' => [],
            'style' => [
                'desktop' => ['columns' => '3', 'gap' => 'lg', 'align' => 'stretch'],
                'tablet' => ['columns' => '2', 'gap' => 'md', 'align' => 'stretch'],
                'mobile' => ['columns' => '1', 'gap' => 'md', 'align' => 'stretch'],
            ],
            'visibility' => self::visibilityDefaults(), 'bindings' => [], 'children' => [],
        ];

        return self::definition(
            type: 'layout.grid', labels: ['vi' => 'Lưới', 'en' => 'Grid', 'zh' => '网格'], icon: 'fa-solid fa-grip',
            props: self::object([], []),
            style: self::responsiveSchema([
                'columns' => self::enum(['1', '2', '3', '4']), 'gap' => self::enum(self::SPACING), 'align' => self::enum(self::ALIGN),
            ], ['columns', 'gap', 'align']),
            defaults: $defaults, allowRoot: false, parents: ['layout.section', 'layout.container', 'layout.stack'],
            children: array_values(array_diff(self::CONTENT_CHILDREN, ['layout.grid', 'layout.columns'])),
            maxDepth: 8, minChildren: 1, maxChildren: 12, renderer: GridBlockRenderer::class,
        );
    }

    private static function columns(): BlockDefinition
    {
        $defaults = [
            'props' => ['desktopPreset' => 'equal-2', 'tabletPreset' => 'equal-2', 'mobilePreset' => 'stack'],
            'style' => self::responsiveDefaults(['gap' => 'lg', 'align' => 'stretch']),
            'visibility' => self::visibilityDefaults(), 'bindings' => [], 'children' => [],
        ];

        return self::definition(
            type: 'layout.columns', labels: ['vi' => 'Cột', 'en' => 'Columns', 'zh' => '分栏'], icon: 'fa-solid fa-table-columns',
            props: self::object([
                'desktopPreset' => self::enum(['equal-2', 'equal-3', 'equal-4', 'sidebar-left', 'sidebar-right']),
                'tabletPreset' => self::enum(['equal-2', 'stack']), 'mobilePreset' => self::enum(['stack']),
            ], ['desktopPreset', 'tabletPreset', 'mobilePreset']),
            style: self::responsiveSchema(['gap' => self::enum(self::SPACING), 'align' => self::enum(self::ALIGN)], ['gap', 'align']),
            defaults: $defaults, allowRoot: false, parents: ['layout.section', 'layout.container', 'layout.stack'],
            children: array_values(array_diff(self::CONTENT_CHILDREN, ['layout.grid', 'layout.columns'])),
            maxDepth: 8, minChildren: 2, maxChildren: 4, renderer: ColumnsBlockRenderer::class,
        );
    }

    private static function spacer(): BlockDefinition
    {
        $defaults = [
            'props' => [], 'style' => self::responsiveDefaults(['size' => 'lg']),
            'visibility' => self::visibilityDefaults(), 'bindings' => [], 'children' => [],
        ];

        return self::definition(
            type: 'layout.spacer', labels: ['vi' => 'Khoảng cách', 'en' => 'Spacer', 'zh' => '间距'], icon: 'fa-solid fa-arrows-up-down',
            props: self::object([], []), style: self::responsiveSchema(['size' => self::enum(self::SPACING)], ['size']),
            defaults: $defaults, allowRoot: false, parents: self::LAYOUT_PARENTS, children: [], maxDepth: 8, minChildren: 0, maxChildren: 0,
            renderer: SpacerBlockRenderer::class,
        );
    }

    private static function divider(): BlockDefinition
    {
        $defaults = [
            'props' => ['variant' => 'solid', 'color' => 'border'], 'style' => self::responsiveDefaults(['marginY' => 'md']),
            'visibility' => self::visibilityDefaults(), 'bindings' => [], 'children' => [],
        ];

        return self::definition(
            type: 'layout.divider', labels: ['vi' => 'Đường phân cách', 'en' => 'Divider', 'zh' => '分隔线'], icon: 'fa-solid fa-minus',
            props: self::object(['variant' => self::enum(['solid', 'dashed']), 'color' => self::enum(['border', 'brand', 'muted'])], ['variant', 'color']),
            style: self::responsiveSchema(['marginY' => self::enum(self::SPACING)], ['marginY']),
            defaults: $defaults, allowRoot: false, parents: self::LAYOUT_PARENTS, children: [], maxDepth: 8, minChildren: 0, maxChildren: 0,
            renderer: DividerBlockRenderer::class,
        );
    }

    /**
     * @param  array<string, string>  $labels
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $style
     * @param  array<string, mixed>  $defaults
     * @param  list<string>  $parents
     * @param  list<string>  $children
     * @param  class-string<BlockRenderer>  $renderer
     */
    private static function definition(string $type, array $labels, string $icon, array $props, array $style, array $defaults, bool $allowRoot, array $parents, array $children, int $maxDepth, int $minChildren, int $maxChildren, string $renderer): BlockDefinition
    {
        $empty = self::object([], []);

        return new BlockDefinition(
            type: $type, version: 1, labels: $labels, category: 'layout', icon: $icon, thumbnail: null,
            propsSchema: $props, styleSchema: $style, visibilitySchema: self::visibilitySchema(), bindingsSchema: $empty,
            defaults: $defaults, allowRoot: $allowRoot, allowedParents: $parents, allowedChildren: $children,
            maxDepth: $maxDepth, minChildren: $minChildren, maxChildren: $maxChildren,
            dataDependencies: [], permissions: [], cacheTags: ['page-builder', 'page-builder:layout'],
            renderer: $renderer, sanitizer: LayoutBlockSanitizer::class, migrations: [],
            testFixture: ['id' => str_replace('.', '-', $type).'-fixture', 'type' => $type, 'version' => 1, ...$defaults],
        );
    }

    /**
     * @param  array<string, mixed>  $properties
     * @param  list<string>  $required
     * @return array<string, mixed>
     */
    private static function object(array $properties, array $required): array
    {
        return ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false];
    }

    /**
     * @param  list<string>  $values
     * @return array<string, mixed>
     */
    private static function enum(array $values): array
    {
        return ['type' => 'string', 'enum' => $values];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @param  list<string>  $required
     * @return array<string, mixed>
     */
    private static function responsiveSchema(array $properties, array $required): array
    {
        $breakpoint = self::object($properties, $required);

        return self::object(['desktop' => $breakpoint, 'tablet' => $breakpoint, 'mobile' => $breakpoint], ['desktop', 'tablet', 'mobile']);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function responsiveDefaults(array $values): array
    {
        return ['desktop' => $values, 'tablet' => $values, 'mobile' => $values];
    }

    /** @return array<string, mixed> */
    private static function visibilitySchema(): array
    {
        return self::object([
            'desktop' => ['type' => 'boolean'], 'tablet' => ['type' => 'boolean'], 'mobile' => ['type' => 'boolean'],
        ], ['desktop', 'tablet', 'mobile']);
    }

    /** @return array<string, bool> */
    private static function visibilityDefaults(): array
    {
        return ['desktop' => true, 'tablet' => true, 'mobile' => true];
    }
}
