import { Page } from '@playwright/test';
import { expect, test } from './support/qa-test';

const userId = '01K1USER000000000000000001';
const productId = '01K1PRODUCT000000000000001';
const leadId = '01K1LEAD000000000000000001';
const seoId = '01K1SEOCONTENT0000000000001';
const mediaId = '01K1MEDIA000000000000000001';

test('login and logout use an isolated mocked session', async ({ page }) => {
  let authenticated = false;
  const user = adminUser(['dashboard.view']);

  await mockApiFallback(page);
  await mockPreferences(page);
  await mockDashboard(page);
  await page.route('**/sanctum/csrf-cookie', (route) => route.fulfill({ status: 204 }));
  await page.route('**/api/admin/v1/auth/me', async (route) => {
    await route.fulfill(authenticated
      ? { json: envelope(user) }
      : { status: 401, json: { message: 'Unauthenticated.' } });
  });
  await page.route('**/api/admin/v1/auth/login', async (route) => {
    authenticated = true;
    await route.fulfill({ json: envelope(user) });
  });
  await page.route('**/api/admin/v1/auth/logout', async (route) => {
    authenticated = false;
    await route.fulfill({ json: envelope(null) });
  });

  await page.goto('login');
  await page.locator('input[formControlName=email]').fill('admin@example.test');
  await page.locator('input[formControlName=password]').fill('safe-test-password');
  await page.locator('button[type=submit]').click();
  await expect(page).toHaveURL(/\/admin\/dashboard/);

  await page.locator('button.user-button').click();
  await page.locator('button.mat-mdc-menu-item').filter({ has: page.locator('mat-icon', { hasText: 'logout' }) }).click();
  await expect(page).toHaveURL(/\/admin\/login/);
});

test('RBAC redirects denied routes and applies the current user theme', async ({ page }) => {
  await mockAuthenticatedAdmin(page, ['dashboard.view'], 'green-dark');
  await page.goto('products');

  await expect(page).toHaveURL(/\/admin\/dashboard\?denied=%2Fproducts/);
  await expect(page.locator('hv-admin-shell > .admin-shell')).toHaveClass(/skin-green-dark/);
  await expect(page.locator('a[href="/products"]')).toHaveCount(0);
});

test('product CRUD includes the shared media picker', async ({ page }) => {
  const permissions = ['products.view', 'products.create', 'products.update', 'products.delete', 'products.publish', 'media.view'];
  let product: ReturnType<typeof productItem> | null = null;

  await mockAuthenticatedAdmin(page, permissions);
  await page.route('**/api/admin/v1/products**', async (route) => {
    const request = route.request();
    const url = new URL(request.url());
    const path = url.pathname;

    if (['categories', 'brands', 'tags', 'attributes'].some((segment) => path.endsWith(`/products/${segment}`))) {
      await route.fulfill({ json: envelope([]) });
      return;
    }
    if (path.endsWith(`/products/${productId}/publish`)) {
      product = { ...(product ?? productItem()), status: 'published' };
      await route.fulfill({ json: envelope(product) });
      return;
    }
    if (path.endsWith('/products') && request.method() === 'POST') {
      const payload = request.postDataJSON() as { sku: string; translations: readonly { locale: string; name: string; slug: string }[] };
      product = productItem(payload.sku, payload.translations);
      await route.fulfill({ json: envelope(product) });
      return;
    }
    await route.fulfill({ json: paginated(product ? [product] : []) });
  });
  await page.route(`**/api/admin/v1/products/${productId}`, async (route) => {
    if (route.request().method() === 'DELETE') {
      const removed = product ?? productItem();
      product = null;
      await route.fulfill({ json: envelope(removed) });
      return;
    }
    await route.fulfill({ json: envelope(product ?? productItem()) });
  });
  await page.route('**/api/admin/v1/media**', async (route) => route.fulfill({ json: paginated([mediaItem()]) }));

  await page.goto('products');
  await page.locator('header.page-header button').filter({ has: page.locator('mat-icon', { hasText: 'add' }) }).click();
  const dialog = page.locator('hv-product-editor-dialog');
  await dialog.locator('input[formControlName=sku]').fill('QA-NPK-001');
  await dialog.locator('.mat-mdc-tab').nth(1).click();
  const translations = dialog.locator('.translation-tabs section');
  for (const [index, value] of ['phan-bon-qa', 'qa-fertilizer', 'qa-fertilizer-zh'].entries()) {
    await translations.nth(index).locator('input').nth(0).fill(['Phân bón QA', 'QA fertilizer', 'QA 肥料'][index]);
    await translations.nth(index).locator('input').nth(1).fill(value);
  }
  await dialog.locator('.mat-mdc-tab').nth(3).click();
  await dialog.locator('button').filter({ has: page.locator('mat-icon', { hasText: 'add_photo_alternate' }) }).click();
  const picker = page.locator('hv-media-picker-dialog');
  await picker.locator('.picker-item').click();
  await picker.locator('mat-dialog-actions button').last().click();
  await expect(dialog.locator('.media-list article')).toHaveCount(1);
  await dialog.locator('mat-dialog-actions button').last().click();

  await expect(page.locator('tbody')).toContainText('QA-NPK-001');
  const actions = page.locator('tbody .row-actions');
  await actions.locator('button').nth(1).click();
  await expect(page.locator('tbody')).toContainText('QA-NPK-001');
  page.once('dialog', (confirmation) => confirmation.accept());
  const deleteRequest = page.waitForRequest((request) => request.method() === 'DELETE' && new URL(request.url()).pathname.endsWith(`/${productId}`));
  await actions.locator('button').filter({ has: page.locator('mat-icon', { hasText: 'delete' }) }).click();
  await deleteRequest;
  await expect.poll(() => product).toBeNull();
  await page.reload();
  await expect(page.locator('tbody tr')).toHaveCount(0);
});

test('lead status and note workflow persists through its typed endpoints', async ({ page }) => {
  let lead = leadItem();
  await mockAuthenticatedAdmin(page, ['leads.view', 'leads.update']);
  await page.route('**/api/admin/v1/leads**', (route) => route.fulfill({ json: envelope({ items: [lead], meta: { current_page: 1, last_page: 1, total: 1 } }) }));
  await page.route('**/api/admin/v1/leads/metrics**', (route) => route.fulfill({ json: envelope({ total: 1, unassigned: 1, new_today: 1, by_status: { new: 1 }, by_type: { contact: 1 } }) }));
  await page.route('**/api/admin/v1/leads/assignees**', (route) => route.fulfill({ json: envelope([]) }));
  await page.route(`**/api/admin/v1/leads/${leadId}`, (route) => route.fulfill({ json: envelope(lead) }));
  await page.route(`**/api/admin/v1/leads/${leadId}/status`, async (route) => {
    lead = { ...lead, status: 'contacted', allowed_transitions: ['qualified'] };
    await route.fulfill({ json: envelope(lead) });
  });
  await page.route(`**/api/admin/v1/leads/${leadId}/notes`, async (route) => {
    const body = (route.request().postDataJSON() as { body: string }).body;
    lead = { ...lead, notes: [{ public_id: '01K1NOTE000000000000000001', body, author: 'QA Admin', created_at: '2026-08-03T01:00:00Z' }] };
    await route.fulfill({ json: envelope(lead) });
  });

  await page.goto('leads');
  await page.locator('.lead-row').click();
  await page.locator('.workflow mat-select').first().click();
  await page.locator('mat-option').filter({ hasText: /liên hệ/i }).click();
  await page.locator('.workflow button').first().click();
  await expect(page.locator('.detail-head')).toContainText(/liên hệ/i);

  await page.locator('.note-form textarea').fill('Đã xác minh nhu cầu khách hàng.');
  await page.locator('.note-form button').click();
  await expect(page.locator('.history-grid')).toContainText('Đã xác minh nhu cầu khách hàng.');
});

test('SEO metadata edit sends the localized payload', async ({ page }) => {
  let savedTitle = '';
  const record = seoRecord();
  await mockAuthenticatedAdmin(page, ['seo.view', 'seo.update']);
  await page.route('**/api/admin/v1/seo-meta/entities**', async (route) => route.fulfill({ json: envelope([{ public_id: seoId, label: 'Phân bón QA', code: 'QA-NPK-001', status: 'published' }]) }));
  await page.route(`**/api/admin/v1/seo-meta/product/${seoId}**`, async (route) => {
    if (route.request().method() === 'PUT') {
      savedTitle = (route.request().postDataJSON() as { meta_title: string }).meta_title;
      await route.fulfill({ json: envelope({ ...record, meta_title: savedTitle }) });
      return;
    }
    await route.fulfill({ json: envelope(record) });
  });

  await page.goto('seo');
  await page.locator('input[formControlName=meta_title]').fill('Phân bón Hồng Vân chuẩn SEO');
  await page.locator('form.seo-form button[type=submit]').click();
  await expect.poll(() => savedTitle).toBe('Phân bón Hồng Vân chuẩn SEO');
});

test('admin shell matches the reviewed desktop visual baseline', async ({ page }) => {
  await mockAuthenticatedAdmin(page, ['dashboard.view']);
  await page.goto('dashboard');
  await expect(page.locator('hv-admin-shell')).toHaveScreenshot('admin-shell-desktop.png');
});

test('production scripts, styles and fonts stay below the admin asset path', async ({ page }) => {
  await mockAuthenticatedAdmin(page, ['dashboard.view']);
  await page.goto('dashboard');

  const assetUrls = await page.evaluate(() => performance.getEntriesByType('resource')
    .map((entry) => entry.name)
    .filter((url) => /\.(?:css|js|woff2?)(?:\?|$)/.test(url)));

  expect(assetUrls.length).toBeGreaterThan(0);
  expect(assetUrls.every((url) => new URL(url).pathname.startsWith('/admin/'))).toBe(true);
  expect(assetUrls.some((url) => new URL(url).pathname.endsWith('.map'))).toBe(false);
});

async function mockAuthenticatedAdmin(page: Page, permissions: readonly string[], skin = 'indigo-light'): Promise<void> {
  await mockApiFallback(page);
  await mockPreferences(page, skin);
  await mockDashboard(page);
  await page.route('**/api/admin/v1/auth/me', (route) => route.fulfill({ json: envelope(adminUser(permissions)) }));
}

async function mockApiFallback(page: Page): Promise<void> {
  await page.route('**/api/admin/v1/**', (route) => {
    const path = new URL(route.request().url()).pathname;
    if (path.endsWith('/dashboard')) return route.fulfill({ json: envelope(dashboardSnapshot()) });
    return route.fulfill({ json: envelope([]) });
  });
}

async function mockPreferences(page: Page, skin = 'indigo-light'): Promise<void> {
  await page.route('**/api/admin/v1/preferences', (route) => route.fulfill({ json: envelope({
    theme: { fixed_header: true, fixed_sidenav: true, fixed_footer: false, sidenav_opened: true, sidenav_pinned: true, menu_orientation: 'vertical', menu_density: 'default', skin, rtl: false },
    locale: 'vi', favorite_menu_ids: [],
  }) }));
}

async function mockDashboard(page: Page): Promise<void> {
  await page.route('**/api/admin/v1/dashboard**', (route) => route.fulfill({ json: envelope(dashboardSnapshot()) }));
}

function dashboardSnapshot() {
  return {
    range: { from: '2026-07-05', to: '2026-08-03', timezone: 'Asia/Ho_Chi_Minh' },
    capabilities: { products: true, content: true, leads: true, activity: true, analytics: false, pages: false, top_viewed: false },
    cards: { products: { total: 3, published: 2 }, content: { drafts: 1, scheduled: 0, pages: null }, leads: { total: 1, new_in_range: 1, overdue_follow_up: 0, by_type: { contact: 1 }, by_status: { new: 1 } } },
    charts: { leads: [{ date: '2026-08-03', value: 1 }], published_products: [{ date: '2026-08-03', value: 2 }] }, recent_activity: [],
    analytics: { enabled: false, top_search_terms: [], top_viewed: [] }, generated_at: '2026-08-03T00:00:00Z', cache_ttl_seconds: 60,
  };
}

function adminUser(permissions: readonly string[]) {
  return { public_id: userId, name: 'QA Admin', email: 'admin@example.test', email_verified_at: '2026-08-03T00:00:00Z', is_active: true, locked_at: null, roles: ['super_admin'], permissions };
}

function productItem(sku = 'QA-NPK-001', translations: readonly { locale: string; name: string; slug: string }[] = [
  { locale: 'vi', name: 'Phân bón QA', slug: 'phan-bon-qa' }, { locale: 'en', name: 'QA fertilizer', slug: 'qa-fertilizer' }, { locale: 'zh', name: 'QA 肥料', slug: 'qa-fertilizer-zh' },
]) {
  return { public_id: productId, sku, code: 'QA-001', status: 'draft' as 'draft' | 'published', category: null, brand: null, origin: null, packaging: null, is_featured: false,
    price: { mode: 'contact', amount: null, minimum: null, maximum: null, currency: 'VND', unit: null, note: null, visible: true, display: { mode: 'contact', label: 'Liên hệ báo giá', shows_numeric_price: false, requires_quote: true } },
    translations: translations.map((item) => ({ ...item, short_description: null, description: null, benefits: null, usage_instructions: null, meta_title: null, meta_description: null })),
    media: [], tags: [], attributes: [], specifications: [], related_products: [], published_at: null, unpublished_at: null, deleted_at: null, created_at: '2026-08-03T00:00:00Z', updated_at: '2026-08-03T00:00:00Z' };
}

function mediaItem() {
  return { public_id: mediaId, folder: null, original_filename: 'qa-product.webp', normalized_filename: 'qa-product.webp', extension: 'webp', mime_type: 'image/webp', size_bytes: 1200, checksum_sha256: 'a'.repeat(64), width: 800, height: 600, status: 'ready', visibility: 'public', is_locked: false, title: 'QA product', alt_text: 'QA product', caption: null, content_url: `/api/admin/v1/media/${mediaId}/content`, variants: [], tags: [], usage_count: 0, usages: [], can_delete: true, deleted_at: null, created_at: '2026-08-03T00:00:00Z', updated_at: '2026-08-03T00:00:00Z' };
}

function leadItem() {
  return { public_id: leadId, type: 'contact' as const, status: 'new' as 'new' | 'contacted', source: 'contact-form', contact: { name: 'Khách hàng QA', phone: '0900000000', email: 'qa@example.test' }, original_payload: { message: 'Cần tư vấn' }, assignee: null, allowed_transitions: ['contacted'] as readonly ('contacted' | 'qualified')[], timeline: [], assignments: [], notes: [], consent_at: '2026-08-03T00:00:00Z', privacy_policy_version: 'v1', anonymized_at: null, next_follow_up_at: null, created_at: '2026-08-03T00:00:00Z', updated_at: '2026-08-03T00:00:00Z' };
}

function seoRecord() {
  return { public_id: '01K1SEOMETA000000000000001', locale: 'vi', meta_title: null, meta_description: null, canonical_url: null, robots_index: true, robots_follow: true, og_title: null, og_description: null, og_image: null, og_type: 'product', twitter_card: 'summary_large_image', twitter_title: null, twitter_description: null, focus_keywords: [], updated_at: null };
}

function paginated(data: unknown[]) {
  return { ...envelope(data), meta: { request_id: '01K1REQUEST000000000000001', pagination: { page: 1, last_page: 1, per_page: 20, total: data.length } } };
}

function envelope(data: unknown) {
  return { success: true as const, data, meta: { request_id: '01K1REQUEST000000000000001' }, message: null };
}
