<?php

namespace App\Domain\PageBuilder;

use App\Domain\PageBuilder\Contracts\BlockRenderer;
use App\Domain\PageBuilder\Rendering\BadgeBlockRenderer;
use App\Domain\PageBuilder\Rendering\ButtonBlockRenderer;
use App\Domain\PageBuilder\Rendering\CardBlockRenderer;
use App\Domain\PageBuilder\Rendering\FaqBlockRenderer;
use App\Domain\PageBuilder\Rendering\GalleryBlockRenderer;
use App\Domain\PageBuilder\Rendering\HeadingBlockRenderer;
use App\Domain\PageBuilder\Rendering\IconBlockRenderer;
use App\Domain\PageBuilder\Rendering\ImageBlockRenderer;
use App\Domain\PageBuilder\Rendering\ImageTextBlockRenderer;
use App\Domain\PageBuilder\Rendering\ListBlockRenderer;
use App\Domain\PageBuilder\Rendering\LogoCloudBlockRenderer;
use App\Domain\PageBuilder\Rendering\QuoteBlockRenderer;
use App\Domain\PageBuilder\Rendering\RichTextBlockRenderer;
use App\Domain\PageBuilder\Rendering\TableBlockRenderer;
use App\Domain\PageBuilder\Rendering\VideoEmbedBlockRenderer;
use App\Domain\PageBuilder\Sanitization\ContentMediaBlockSanitizer;

final class ContentMediaBlockDefinitions
{
    public const TYPES = [
        'content.heading', 'content.rich-text', 'content.button', 'content.icon', 'content.list', 'content.quote',
        'content.table', 'content.badge', 'content.card', 'media.image', 'media.image-text', 'media.gallery',
        'media.video-embed', 'media.logo-cloud', 'content.faq',
    ];

    private const PARENTS = ['layout.section', 'layout.container', 'layout.stack', 'layout.grid', 'layout.columns'];

    /** @return list<BlockDefinition> */
    public static function definitions(): array
    {
        return [
            self::heading(), self::richText(), self::button(), self::icon(), self::list(), self::quote(),
            self::table(), self::badge(), self::card(), self::image(), self::imageText(), self::gallery(),
            self::videoEmbed(), self::logoCloud(), self::faq(),
        ];
    }

    private static function heading(): BlockDefinition
    {
        return self::definition('content.heading', ['vi' => 'Tiêu đề', 'en' => 'Heading', 'zh' => '标题'], 'fa-solid fa-heading', [
            'text' => self::string(200), 'level' => ['type' => 'integer', 'enum' => [1, 2, 3, 4, 5, 6]],
            'anchorId' => ['type' => 'string', 'maxLength' => 80, 'pattern' => '^(?:|[a-z][a-z0-9-]*)$'],
        ], ['text', 'level', 'anchorId'], ['text' => 'Heading', 'level' => 2, 'anchorId' => ''], HeadingBlockRenderer::class);
    }

    private static function richText(): BlockDefinition
    {
        return self::definition('content.rich-text', ['vi' => 'Văn bản', 'en' => 'Rich text', 'zh' => '富文本'], 'fa-solid fa-align-left', [
            'html' => self::string(30000),
        ], ['html'], ['html' => '<p>Content</p>'], RichTextBlockRenderer::class);
    }

    private static function button(): BlockDefinition
    {
        return self::definition('content.button', ['vi' => 'Nút liên kết', 'en' => 'Button', 'zh' => '按钮'], 'fa-solid fa-arrow-pointer', [
            'label' => self::string(120), 'url' => self::string(2048), 'target' => self::enum(['_self', '_blank']),
            'variant' => self::enum(['primary', 'secondary', 'outline', 'link']),
        ], ['label', 'url', 'target', 'variant'], ['label' => 'Learn more', 'url' => '#', 'target' => '_self', 'variant' => 'primary'], ButtonBlockRenderer::class);
    }

    private static function icon(): BlockDefinition
    {
        return self::definition('content.icon', ['vi' => 'Biểu tượng', 'en' => 'Icon', 'zh' => '图标'], 'fa-regular fa-star', [
            'name' => self::enum(['leaf', 'truck', 'warehouse', 'phone', 'envelope', 'check', 'star', 'quote']),
            'label' => self::string(120), 'decorative' => ['type' => 'boolean'], 'tone' => self::enum(['brand', 'neutral', 'positive']),
        ], ['name', 'label', 'decorative', 'tone'], ['name' => 'leaf', 'label' => '', 'decorative' => true, 'tone' => 'brand'], IconBlockRenderer::class);
    }

    private static function list(): BlockDefinition
    {
        return self::definition('content.list', ['vi' => 'Danh sách', 'en' => 'List', 'zh' => '列表'], 'fa-solid fa-list', [
            'ordered' => ['type' => 'boolean'], 'items' => self::array(self::string(500), 1, 50),
        ], ['ordered', 'items'], ['ordered' => false, 'items' => ['List item']], ListBlockRenderer::class);
    }

    private static function quote(): BlockDefinition
    {
        return self::definition('content.quote', ['vi' => 'Trích dẫn', 'en' => 'Quote', 'zh' => '引用'], 'fa-solid fa-quote-left', [
            'text' => self::string(2000), 'attribution' => self::string(200), 'citeUrl' => self::string(2048),
        ], ['text', 'attribution', 'citeUrl'], ['text' => 'Quote', 'attribution' => '', 'citeUrl' => ''], QuoteBlockRenderer::class);
    }

    private static function table(): BlockDefinition
    {
        return self::definition('content.table', ['vi' => 'Bảng dữ liệu', 'en' => 'Table', 'zh' => '表格'], 'fa-solid fa-table', [
            'caption' => self::string(200),
            'headers' => self::array(self::string(200), 1, 8),
            'rows' => self::array(self::array(self::string(500), 1, 8), 1, 50),
        ], ['caption', 'headers', 'rows'], ['caption' => 'Data table', 'headers' => ['Column'], 'rows' => [['Value']]], TableBlockRenderer::class);
    }

    private static function badge(): BlockDefinition
    {
        return self::definition('content.badge', ['vi' => 'Nhãn', 'en' => 'Badge', 'zh' => '徽章'], 'fa-solid fa-certificate', [
            'text' => self::string(100), 'tone' => self::enum(['brand', 'neutral', 'positive', 'warning']),
        ], ['text', 'tone'], ['text' => 'Badge', 'tone' => 'brand'], BadgeBlockRenderer::class);
    }

    private static function card(): BlockDefinition
    {
        return self::definition('content.card', ['vi' => 'Thẻ nội dung', 'en' => 'Card', 'zh' => '内容卡片'], 'fa-regular fa-rectangle-list', [
            'title' => self::string(200), 'body' => self::string(2000), 'linkLabel' => self::string(120),
            'linkUrl' => self::string(2048), 'target' => self::enum(['_self', '_blank']), 'tone' => self::enum(['surface', 'muted', 'brand-soft']),
        ], ['title', 'body', 'linkLabel', 'linkUrl', 'target', 'tone'], [
            'title' => 'Card title', 'body' => 'Card content', 'linkLabel' => '', 'linkUrl' => '', 'target' => '_self', 'tone' => 'surface',
        ], CardBlockRenderer::class);
    }

    private static function image(): BlockDefinition
    {
        return self::definition('media.image', ['vi' => 'Hình ảnh', 'en' => 'Image', 'zh' => '图片'], 'fa-regular fa-image', [
            'mediaId' => self::mediaId(), 'alt' => self::string(300), 'decorative' => ['type' => 'boolean'],
            'caption' => self::string(500), 'loading' => self::enum(['lazy', 'eager']), 'width' => self::enum(['intrinsic', 'full']),
        ], ['mediaId', 'alt', 'decorative', 'caption', 'loading', 'width'], [
            'mediaId' => '01K00000000000000000000000', 'alt' => 'Image description', 'decorative' => false,
            'caption' => '', 'loading' => 'lazy', 'width' => 'intrinsic',
        ], ImageBlockRenderer::class, 'media');
    }

    private static function imageText(): BlockDefinition
    {
        return self::definition('media.image-text', ['vi' => 'Ảnh và nội dung', 'en' => 'Image and text', 'zh' => '图文'], 'fa-solid fa-table-columns', [
            'mediaId' => self::mediaId(), 'alt' => self::string(300), 'decorative' => ['type' => 'boolean'],
            'heading' => self::string(200), 'text' => self::string(3000), 'imagePosition' => self::enum(['left', 'right']),
            'linkLabel' => self::string(120), 'linkUrl' => self::string(2048), 'target' => self::enum(['_self', '_blank']),
        ], ['mediaId', 'alt', 'decorative', 'heading', 'text', 'imagePosition', 'linkLabel', 'linkUrl', 'target'], [
            'mediaId' => '01K00000000000000000000000', 'alt' => 'Image description', 'decorative' => false,
            'heading' => 'Image and text', 'text' => 'Content', 'imagePosition' => 'left', 'linkLabel' => '', 'linkUrl' => '', 'target' => '_self',
        ], ImageTextBlockRenderer::class, 'media');
    }

    private static function gallery(): BlockDefinition
    {
        $item = self::object([
            'mediaId' => self::mediaId(), 'alt' => self::string(300), 'decorative' => ['type' => 'boolean'], 'caption' => self::string(500),
        ], ['mediaId', 'alt', 'decorative', 'caption']);

        return self::definition('media.gallery', ['vi' => 'Thư viện ảnh', 'en' => 'Gallery', 'zh' => '图库'], 'fa-regular fa-images', [
            'label' => self::string(160), 'columns' => ['type' => 'integer', 'enum' => [2, 3, 4]], 'items' => self::array($item, 1, 24),
        ], ['label', 'columns', 'items'], ['label' => 'Image gallery', 'columns' => 3, 'items' => [[
            'mediaId' => '01K00000000000000000000000', 'alt' => 'Gallery image', 'decorative' => false, 'caption' => '',
        ]]], GalleryBlockRenderer::class, 'media');
    }

    private static function videoEmbed(): BlockDefinition
    {
        return self::definition('media.video-embed', ['vi' => 'Video nhúng', 'en' => 'Video embed', 'zh' => '嵌入视频'], 'fa-solid fa-circle-play', [
            'provider' => self::enum(['youtube-nocookie', 'vimeo']), 'videoId' => ['type' => 'string', 'maxLength' => 32, 'pattern' => '^[A-Za-z0-9_-]{6,32}$'],
            'title' => self::string(200), 'loading' => self::enum(['lazy', 'eager']),
        ], ['provider', 'videoId', 'title', 'loading'], [
            'provider' => 'youtube-nocookie', 'videoId' => 'dQw4w9WgXcQ', 'title' => 'Video', 'loading' => 'lazy',
        ], VideoEmbedBlockRenderer::class, 'media');
    }

    private static function logoCloud(): BlockDefinition
    {
        $item = self::object([
            'mediaId' => self::mediaId(), 'alt' => self::string(300), 'linkUrl' => self::string(2048), 'target' => self::enum(['_self', '_blank']),
        ], ['mediaId', 'alt', 'linkUrl', 'target']);

        return self::definition('media.logo-cloud', ['vi' => 'Danh sách logo', 'en' => 'Logo cloud', 'zh' => '标志墙'], 'fa-solid fa-building', [
            'label' => self::string(160), 'items' => self::array($item, 1, 24),
        ], ['label', 'items'], ['label' => 'Logos', 'items' => [[
            'mediaId' => '01K00000000000000000000000', 'alt' => 'Logo', 'linkUrl' => '', 'target' => '_self',
        ]]], LogoCloudBlockRenderer::class, 'media');
    }

    private static function faq(): BlockDefinition
    {
        $item = self::object(['question' => self::string(500), 'answer' => self::string(5000)], ['question', 'answer']);

        return self::definition('content.faq', ['vi' => 'Câu hỏi thường gặp', 'en' => 'FAQ', 'zh' => '常见问题'], 'fa-regular fa-circle-question', [
            'heading' => self::string(200), 'verified' => ['type' => 'boolean'], 'items' => self::array($item, 1, 30),
        ], ['heading', 'verified', 'items'], ['heading' => 'Frequently asked questions', 'verified' => false, 'items' => [[
            'question' => 'Question?', 'answer' => '<p>Answer.</p>',
        ]]], FaqBlockRenderer::class);
    }

    /**
     * @param  array<string, string>  $labels
     * @param  array<string, mixed>  $props
     * @param  list<string>  $required
     * @param  array<string, mixed>  $defaults
     * @param  class-string<BlockRenderer>  $renderer
     */
    private static function definition(string $type, array $labels, string $icon, array $props, array $required, array $defaults, string $renderer, string $category = 'content'): BlockDefinition
    {
        $empty = self::object([], []);
        $blockDefaults = [
            'props' => $defaults,
            'style' => self::responsiveDefaults(),
            'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
            'bindings' => [], 'children' => [],
        ];

        return new BlockDefinition(
            type: $type, version: 1, labels: $labels, category: $category, icon: $icon, thumbnail: null,
            propsSchema: self::object($props, $required), styleSchema: self::responsiveStyleSchema(),
            visibilitySchema: self::visibilitySchema(), bindingsSchema: $empty, defaults: $blockDefaults,
            allowRoot: false, allowedParents: self::PARENTS, allowedChildren: [], maxDepth: 8, minChildren: 0, maxChildren: 0,
            dataDependencies: [], permissions: [], cacheTags: ['page-builder', 'page-builder:content'],
            renderer: $renderer, sanitizer: ContentMediaBlockSanitizer::class, migrations: [],
            testFixture: ['id' => str_replace('.', '-', $type).'-fixture', 'type' => $type, 'version' => 1, ...$blockDefaults],
        );
    }

    /** @return array<string, mixed> */
    private static function responsiveStyleSchema(): array
    {
        $breakpoint = self::object([
            'textAlign' => self::enum(['start', 'center', 'end']),
            'spacing' => self::enum(['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl']),
        ], ['textAlign', 'spacing']);

        return self::object(['desktop' => $breakpoint, 'tablet' => $breakpoint, 'mobile' => $breakpoint], ['desktop', 'tablet', 'mobile']);
    }

    /** @return array<string, array<string, string>> */
    private static function responsiveDefaults(): array
    {
        $value = ['textAlign' => 'start', 'spacing' => 'none'];

        return ['desktop' => $value, 'tablet' => $value, 'mobile' => $value];
    }

    /** @return array<string, mixed> */
    private static function visibilitySchema(): array
    {
        return self::object([
            'desktop' => ['type' => 'boolean'], 'tablet' => ['type' => 'boolean'], 'mobile' => ['type' => 'boolean'],
        ], ['desktop', 'tablet', 'mobile']);
    }

    /** @return array<string, mixed> */
    private static function mediaId(): array
    {
        return ['type' => 'string', 'pattern' => '^[0-9A-HJKMNP-TV-Z]{26}$'];
    }

    /** @return array<string, mixed> */
    private static function string(int $maxLength): array
    {
        return ['type' => 'string', 'maxLength' => $maxLength];
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
     * @param  array<string, mixed>  $items
     * @return array<string, mixed>
     */
    private static function array(array $items, int $minItems, int $maxItems): array
    {
        return ['type' => 'array', 'items' => $items, 'minItems' => $minItems, 'maxItems' => $maxItems];
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
}
