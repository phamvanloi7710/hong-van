import { expect, Page, test } from '@playwright/test';

const permissions = [
  'dashboard.view',
  'products.view',
  'posts.view',
  'leads.view',
  'leads.export',
];

test.beforeEach(async ({ page }) => {
  await mockAdminApi(page);
});

test('admin shell supports keyboard navigation and semantic hierarchy', async ({ page }) => {
  await page.goto('dashboard');
  await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);
  await expect(page.getByRole('heading', { name: 'Tổng quan quản trị', level: 2 })).toBeVisible();
  await expect(page.getByRole('navigation', { name: 'Điều hướng chính' })).toBeVisible();

  await page.keyboard.press('Tab');
  await expect(page.getByRole('link', { name: 'Chuyển đến nội dung chính' })).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(page.locator('#admin-main')).toBeFocused();

  const catalogButton = page.getByRole('button', { name: 'Danh mục' });
  await expect(catalogButton).toHaveAttribute('aria-expanded', 'false');
  await catalogButton.click();
  await expect(catalogButton).toHaveAttribute('aria-expanded', 'true');
});

for (const viewport of [
  { name: 'mobile', width: 390, height: 844 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'desktop', width: 1440, height: 900 },
]) {
  test(`admin dashboard has no page-level horizontal overflow on ${viewport.name}`, async ({
    page,
  }) => {
    await page.setViewportSize({ width: viewport.width, height: viewport.height });
    await page.goto('dashboard');
    await expect(page.getByRole('heading', { name: 'Tổng quan quản trị' })).toBeVisible();

    const overflow = await page.locator('html').evaluate((root) => ({
      root: root.scrollWidth > root.clientWidth,
      body: document.body.scrollWidth > document.body.clientWidth,
    }));

    expect(overflow).toEqual({ root: false, body: false });
  });
}

async function mockAdminApi(page: Page): Promise<void> {
  await page.route('**/api/admin/v1/**', async (route) => {
    await route.fulfill({ json: envelope([]) });
  });
  await page.route('**/api/admin/v1/auth/me', async (route) => {
    await route.fulfill({
      json: envelope({
        public_id: '01K1USER000000000000000001',
        name: 'Quản trị Hồng Vân',
        email: 'admin@example.test',
        email_verified_at: '2026-08-03T00:00:00.000Z',
        is_active: true,
        locked_at: null,
        roles: ['super_admin'],
        permissions,
      }),
    });
  });
  await page.route('**/api/admin/v1/preferences', async (route) => {
    await route.fulfill({
      json: envelope({
        theme: {
          fixed_header: true,
          fixed_sidenav: true,
          fixed_footer: false,
          sidenav_opened: true,
          sidenav_pinned: true,
          menu_orientation: 'vertical',
          menu_density: 'default',
          skin: 'indigo-light',
          rtl: false,
        },
        locale: 'vi',
        favorite_menu_ids: [],
      }),
    });
  });
  await page.route('**/api/admin/v1/dashboard**', async (route) => {
    await route.fulfill({
      json: envelope({
        range: { from: '2026-07-05', to: '2026-08-03', timezone: 'Asia/Ho_Chi_Minh' },
        capabilities: {
          products: true,
          content: true,
          leads: true,
          activity: true,
          analytics: false,
          pages: false,
          top_viewed: false,
        },
        cards: {
          products: { total: 3, published: 2 },
          content: { drafts: 1, scheduled: 0, pages: null },
          leads: {
            total: 1,
            new_in_range: 1,
            overdue_follow_up: 0,
            by_type: { contact: 1 },
            by_status: { new: 1 },
          },
        },
        charts: {
          leads: [{ date: '2026-08-03', value: 1 }],
          published_products: [{ date: '2026-08-03', value: 2 }],
        },
        recent_activity: [],
        analytics: { enabled: false, top_search_terms: [], top_viewed: [] },
        generated_at: '2026-08-03T00:00:00Z',
        cache_ttl_seconds: 60,
      }),
    });
  });
}

function envelope(data: unknown): {
  success: true;
  data: unknown;
  meta: { request_id: string };
  message: null;
} {
  return {
    success: true,
    data,
    meta: { request_id: '01K1REQUEST000000000000001' },
    message: null,
  };
}
