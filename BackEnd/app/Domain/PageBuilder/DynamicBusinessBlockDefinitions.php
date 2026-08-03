<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Rendering\DynamicBusinessBlockRenderer;
use App\Domain\PageBuilder\Sanitization\DynamicBusinessBlockSanitizer;

final class DynamicBusinessBlockDefinitions
{
    public const TYPES = [
        'business.hero', 'business.product-grid', 'business.category-grid', 'business.crop-grid',
        'business.service-grid', 'business.fleet', 'business.route-list', 'business.warehouse-cards',
        'business.stats', 'business.partner-logos', 'business.certificate-list', 'business.project-list',
        'business.post-list', 'business.cta', 'business.breadcrumb',
    ];

    private const PARENTS = ['layout.section', 'layout.container', 'layout.stack', 'layout.grid', 'layout.columns'];

    /** @return list<BlockDefinition> */
    public static function definitions(): array
    {
        return [
            self::dynamic('business.hero', 'Hero', 'products', ['featured' => self::boolean()], ['featured' => true], 1, 'fa-regular fa-image'),
            self::dynamic('business.product-grid', 'Product grid', 'products', ['featured' => self::boolean(), 'categoryId' => self::string(26)], ['featured' => false, 'categoryId' => ''], 8, 'fa-solid fa-seedling'),
            self::dynamic('business.category-grid', 'Category grid', 'product_categories', ['featured' => self::boolean(), 'rootOnly' => self::boolean()], ['featured' => false, 'rootOnly' => true], 8, 'fa-solid fa-layer-group'),
            self::dynamic('business.crop-grid', 'Crop solution grid', 'crop_solutions', ['cropId' => self::string(26)], ['cropId' => ''], 8, 'fa-solid fa-leaf'),
            self::dynamic('business.service-grid', 'Service grid', 'services', ['featured' => self::boolean(), 'categoryId' => self::string(26)], ['featured' => false, 'categoryId' => ''], 8, 'fa-solid fa-handshake'),
            self::dynamic('business.fleet', 'Fleet', 'fleet', [], [], 12, 'fa-solid fa-truck'),
            self::dynamic('business.route-list', 'Route list', 'routes', [], [], 12, 'fa-solid fa-route'),
            self::dynamic('business.warehouse-cards', 'Warehouse cards', 'warehouses', [], [], 8, 'fa-solid fa-warehouse'),
            self::dynamic('business.stats', 'Statistics', 'stats', [], [], 8, 'fa-solid fa-chart-column'),
            self::dynamic('business.partner-logos', 'Partner logos', 'partners', ['featured' => self::boolean()], ['featured' => false], 12, 'fa-solid fa-building'),
            self::dynamic('business.certificate-list', 'Certificate list', 'certifications', ['featured' => self::boolean()], ['featured' => false], 8, 'fa-solid fa-certificate'),
            self::dynamic('business.project-list', 'Project list', 'projects', ['featured' => self::boolean()], ['featured' => false], 8, 'fa-solid fa-diagram-project'),
            self::dynamic('business.post-list', 'Post list', 'posts', ['featured' => self::boolean(), 'categorySlug' => self::string(160), 'tagSlug' => self::string(160)], ['featured' => false, 'categorySlug' => '', 'tagSlug' => ''], 8, 'fa-regular fa-newspaper'),
            self::static('business.cta', 'Call to action', 'fa-solid fa-bullhorn'),
            self::static('business.breadcrumb', 'Breadcrumb', 'fa-solid fa-chevron-right'),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $filterProperties
     * @param  array<string, mixed>  $filterDefaults
     */
    private static function dynamic(string $type, string $label, string $source, array $filterProperties, array $filterDefaults, int $limit, string $icon): BlockDefinition
    {
        $binding = ['source' => $source, 'filters' => $filterDefaults, 'sort' => 'default', 'limit' => $limit, 'preset' => 'default'];
        $bindingsSchema = self::object([
            'source' => ['type' => 'string', 'enum' => [$source]],
            'filters' => self::object($filterProperties, array_keys($filterProperties)),
            'sort' => ['type' => 'string', 'enum' => $source === 'products' ? ['default', 'newest', 'oldest'] : ($source === 'posts' ? ['default', 'newest'] : ['default'])],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => DataSourceRegistry::MAX_LIMIT],
            'preset' => ['type' => 'string', 'enum' => in_array($source, ['products', 'product_categories', 'services', 'partners', 'certifications', 'projects', 'posts'], true) ? ['default', 'featured'] : ['default']],
        ], ['source', 'filters', 'sort', 'limit', 'preset']);

        return self::definition($type, $label, $icon, $bindingsSchema, $binding, ['page-builder', 'data:'.$source], [$source]);
    }

    private static function static(string $type, string $label, string $icon): BlockDefinition
    {
        return self::definition($type, $label, $icon, self::object([], []), [], ['page-builder'], []);
    }

    /**
     * @param  array<string, mixed>  $bindingsSchema
     * @param  array<string, mixed>  $bindingDefaults
     * @param  list<string>  $cacheTags
     * @param  list<string>  $dependencies
     */
    private static function definition(string $type, string $label, string $icon, array $bindingsSchema, array $bindingDefaults, array $cacheTags, array $dependencies): BlockDefinition
    {
        $props = ['title' => '', 'description' => '', 'emptyMessage' => '', 'ctaLabel' => '', 'ctaUrl' => ''];
        $defaults = ['props' => $props, 'style' => self::responsiveDefaults(), 'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true], 'bindings' => $bindingDefaults, 'children' => []];

        return new BlockDefinition(
            type: $type, version: 1,
            labels: self::labels($label), category: 'business', icon: $icon, thumbnail: null,
            propsSchema: self::object(['title' => self::string(200), 'description' => self::string(1000), 'emptyMessage' => self::string(300), 'ctaLabel' => self::string(120), 'ctaUrl' => self::string(2048)], array_keys($props)),
            styleSchema: self::responsiveStyleSchema(), visibilitySchema: self::object(['desktop' => self::boolean(), 'tablet' => self::boolean(), 'mobile' => self::boolean()], ['desktop', 'tablet', 'mobile']),
            bindingsSchema: $bindingsSchema, defaults: $defaults, allowRoot: false, allowedParents: self::PARENTS, allowedChildren: [], maxDepth: 8, minChildren: 0, maxChildren: 0,
            dataDependencies: $dependencies, permissions: [], cacheTags: $cacheTags,
            renderer: DynamicBusinessBlockRenderer::class, sanitizer: DynamicBusinessBlockSanitizer::class, migrations: [],
            testFixture: ['id' => str_replace('.', '-', $type).'-fixture', 'type' => $type, 'version' => 1, ...$defaults],
        );
    }

    /** @return array<string,mixed> */
    private static function responsiveStyleSchema(): array
    {
        $point = self::object(['textAlign' => ['type' => 'string', 'enum' => ['start', 'center', 'end']], 'spacing' => ['type' => 'string', 'enum' => ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl']]], ['textAlign', 'spacing']);

        return self::object(['desktop' => $point, 'tablet' => $point, 'mobile' => $point], ['desktop', 'tablet', 'mobile']);
    }

    /** @return array<string,array<string,string>> */
    private static function responsiveDefaults(): array
    {
        $value = ['textAlign' => 'start', 'spacing' => 'none'];

        return ['desktop' => $value, 'tablet' => $value, 'mobile' => $value];
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

    /** @return array<string,mixed> */
    private static function string(int $max): array
    {
        return ['type' => 'string', 'maxLength' => $max];
    }

    /** @return array<string,mixed> */
    private static function boolean(): array
    {
        return ['type' => 'boolean'];
    }

    /** @return array{vi: string, en: string, zh: string} */
    private static function labels(string $english): array
    {
        return match ($english) {
            'Hero' => ['vi' => 'Hero giới thiệu', 'en' => $english, 'zh' => '主视觉'],
            'Product grid' => ['vi' => 'Lưới sản phẩm', 'en' => $english, 'zh' => '产品网格'],
            'Category grid' => ['vi' => 'Lưới danh mục', 'en' => $english, 'zh' => '分类网格'],
            'Crop solution grid' => ['vi' => 'Lưới giải pháp cây trồng', 'en' => $english, 'zh' => '作物解决方案网格'],
            'Service grid' => ['vi' => 'Lưới dịch vụ', 'en' => $english, 'zh' => '服务网格'],
            'Fleet' => ['vi' => 'Đội xe', 'en' => $english, 'zh' => '车队'],
            'Route list' => ['vi' => 'Danh sách tuyến', 'en' => $english, 'zh' => '路线列表'],
            'Warehouse cards' => ['vi' => 'Thẻ kho bãi', 'en' => $english, 'zh' => '仓库卡片'],
            'Statistics' => ['vi' => 'Thống kê', 'en' => $english, 'zh' => '统计'],
            'Partner logos' => ['vi' => 'Logo đối tác', 'en' => $english, 'zh' => '合作伙伴标志'],
            'Certificate list' => ['vi' => 'Danh sách chứng nhận', 'en' => $english, 'zh' => '认证列表'],
            'Project list' => ['vi' => 'Danh sách dự án', 'en' => $english, 'zh' => '项目列表'],
            'Post list' => ['vi' => 'Danh sách bài viết', 'en' => $english, 'zh' => '文章列表'],
            'Call to action' => ['vi' => 'Kêu gọi hành động', 'en' => $english, 'zh' => '行动号召'],
            'Breadcrumb' => ['vi' => 'Điều hướng phân cấp', 'en' => $english, 'zh' => '面包屑导航'],
            default => ['vi' => $english, 'en' => $english, 'zh' => $english],
        };
    }
}
