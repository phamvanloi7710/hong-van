<?php

namespace Database\Seeders;

use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageManager;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

final class PageBuilderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()->where('email', 'phamloi7710@gmail.com')->first();

        if (! $actor instanceof User) {
            throw new RuntimeException('The default super admin account must exist before seeding Page Builder demos.');
        }

        $manager = app(PageManager::class);

        foreach ($this->pages() as $index => $definition) {
            $page = $manager->saveMetadata(
                $actor,
                Page::query()->where('code', $definition['code'])->first(),
                [
                    'code' => $definition['code'],
                    'type' => $index === 0 ? 'landing' : 'standard',
                    'is_home' => false,
                    'translations' => $definition['translations'],
                ],
            );

            $document = PageDocumentSchema::emptyDocument();
            $document['blocks'] = [
                $this->placeholder($index + 1, 1, $definition['translations'][0]['title']),
                $this->placeholder($index + 1, 2, 'Nội dung thử nghiệm Page Builder #'.($index + 1)),
            ];

            $manager->saveDraft($actor, $page, $document);
        }
    }

    /** @return array<string, mixed> */
    private function placeholder(int $pageNumber, int $blockNumber, string $label): array
    {
        return [
            'id' => sprintf('demo-page-%02d-block-%02d', $pageNumber, $blockNumber),
            'type' => 'foundation.placeholder',
            'version' => 1,
            'props' => ['label' => $label],
            'style' => ['desktop' => [], 'tablet' => [], 'mobile' => []],
            'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
            'bindings' => [],
            'children' => [],
        ];
    }

    /** @return list<array{code: string, translations: list<array{locale: string, title: string, navigation_label: string, slug: string}>}> */
    private function pages(): array
    {
        return [
            $this->page('company-overview', 'Giới thiệu Hồng Vân', 'Company Overview', '鸿云公司简介', 'gioi-thieu-hong-van', 'company-overview', 'hong-van-company'),
            $this->page('fertilizer-products', 'Sản phẩm phân bón', 'Fertilizer Products', '肥料产品', 'san-pham-phan-bon', 'fertilizer-products', 'fertilizer-products-zh'),
            $this->page('transport-services', 'Dịch vụ vận chuyển', 'Transport Services', '运输服务', 'dich-vu-van-chuyen', 'transport-services', 'transport-services-zh'),
            $this->page('warehouse-services', 'Dịch vụ kho bãi', 'Warehouse Services', '仓储服务', 'dich-vu-kho-bai', 'warehouse-services', 'warehouse-services-zh'),
            $this->page('projects-partners', 'Dự án và đối tác', 'Projects and Partners', '项目与合作伙伴', 'du-an-doi-tac', 'projects-partners', 'projects-partners-zh'),
            $this->page('contact-quote', 'Liên hệ và yêu cầu báo giá', 'Contact and Quote Request', '联系与询价', 'lien-he-bao-gia', 'contact-quote', 'contact-quote-zh'),
        ];
    }

    /** @return array{code: string, translations: list<array{locale: string, title: string, navigation_label: string, slug: string}>} */
    private function page(string $code, string $vi, string $en, string $zh, string $viSlug, string $enSlug, string $zhSlug): array
    {
        return [
            'code' => $code,
            'translations' => [
                ['locale' => 'vi', 'title' => $vi, 'navigation_label' => $vi, 'slug' => $viSlug],
                ['locale' => 'en', 'title' => $en, 'navigation_label' => $en, 'slug' => $enSlug],
                ['locale' => 'zh', 'title' => $zh, 'navigation_label' => $zh, 'slug' => $zhSlug],
            ],
        ];
    }
}
