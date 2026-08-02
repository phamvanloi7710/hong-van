import { expect, Page, test } from '@playwright/test';
import path from 'node:path';

const mediaId = '01K1MEDIA000000000000000001';
const folderId = '01K1FOLDER0000000000000001';

const mediaItem = {
  public_id: mediaId,
  folder: null,
  original_filename: 'hong-van-profile.pdf',
  normalized_filename: 'hong-van-profile.pdf',
  extension: 'pdf',
  mime_type: 'application/pdf',
  size_bytes: 248320,
  checksum_sha256: 'a'.repeat(64),
  width: null,
  height: null,
  status: 'ready',
  visibility: 'private',
  is_locked: false,
  title: 'Hồ sơ giới thiệu Hồng Vân',
  alt_text: null,
  caption: 'Tài liệu giới thiệu doanh nghiệp',
  content_url: `/api/admin/v1/media/${mediaId}/content`,
  variants: [],
  tags: [],
  usage_count: 1,
  usages: [{ public_id: '01K1USAGE0000000000000001', owner_type: 'settings', owner_public_id: 'company', field: 'profile_document' }],
  can_delete: true,
  deleted_at: null,
  created_at: '2026-08-03T00:00:00.000Z',
  updated_at: '2026-08-03T00:00:00.000Z',
};

const folder = {
  public_id: folderId,
  parent_id: null,
  name: 'Tài liệu công ty',
  slug: 'tai-lieu-cong-ty',
  sort_order: 0,
  is_locked: false,
  media_count: 1,
  children_count: 0,
};

test.beforeEach(async ({ page }) => {
  await mockAuthenticatedUser(page);
  await mockMediaApi(page);
  await page.goto('media');
  await expect(page.getByRole('heading', { name: 'Thư viện media' })).toBeVisible();
});

test('media workflow supports folders, search, selection, upload queue and list view', async ({ page }) => {
  await page.getByRole('button', { name: 'Tài liệu công ty' }).click();
  await expect(page.getByRole('navigation', { name: 'Đường dẫn thư mục' })).toContainText('Tài liệu công ty');

  await page.getByPlaceholder('Ví dụ: logo, banner, sản phẩm').fill('hồ sơ');
  await page.getByRole('button', { name: 'Áp dụng' }).click();
  await expect(page.getByText('Hồ sơ giới thiệu Hồng Vân')).toBeVisible();

  await page.getByRole('option', { name: /Hồ sơ giới thiệu Hồng Vân/ }).click();
  await expect(page.getByRole('toolbar', { name: 'Thao tác hàng loạt' })).toBeVisible();
  await expect(page.locator('textarea[formControlName=caption]')).toHaveValue('Tài liệu giới thiệu doanh nghiệp');
  await expect(page.getByText('profile_document')).toBeVisible();

  await page.getByRole('button', { name: 'Danh sách' }).click();
  await expect(page.locator('.media-results')).toHaveClass(/list-mode/);

  await page.getByRole('button', { name: 'Tải media' }).click();
  await page.locator('input[type=file]').setInputFiles(path.join(__dirname, 'fixtures', 'media-sample.pdf'));
  await expect(page.getByText('media-sample.pdf')).toBeVisible();
  await expect(page.getByRole('dialog', { name: 'Tiến trình tải file' })).toBeVisible();
});

test('media main screen matches the approved visual baseline', async ({ page }) => {
  await page.getByRole('option', { name: /Hồ sơ giới thiệu Hồng Vân/ }).click();
  await expect(page.locator('hv-media-page')).toHaveScreenshot('media-library-main.png');
});

test('media workflow supports trash and restore', async ({ page }) => {
  await page.unroute('**/api/admin/v1/media**');
  let trashed = false;
  const deletableItem = (): typeof mediaItem => ({
    ...mediaItem,
    usage_count: 0,
    usages: [],
    can_delete: true,
    deleted_at: trashed ? '2026-08-03T01:00:00.000Z' : null,
  });

  await page.route('**/api/admin/v1/media**', async (route) => {
    const request = route.request();
    const url = new URL(request.url());
    if (url.pathname.endsWith('/folders')) {
      await route.fulfill({ json: envelope([folder]) });
      return;
    }
    if (url.pathname.endsWith(`/${mediaId}`)) {
      await route.fulfill({ json: envelope(deletableItem()) });
      return;
    }

    const onlyTrash = [...url.searchParams.entries()].some(([key, value]) => key.endsWith('[trashed]') && value === 'only');
    const visible = onlyTrash ? trashed : !trashed;
    await route.fulfill({
      json: {
        ...envelope(visible ? [deletableItem()] : []),
        meta: { request_id: '01K1REQUEST000000000000001', pagination: { page: 1, last_page: 1, per_page: 24, total: visible ? 1 : 0 } },
      },
    });
  });
  await page.route(`**/api/admin/v1/media/${mediaId}/trash`, async (route) => {
    trashed = true;
    await route.fulfill({ json: envelope(deletableItem()) });
  });
  await page.route(`**/api/admin/v1/media/${mediaId}/restore`, async (route) => {
    trashed = false;
    await route.fulfill({ json: envelope(deletableItem()) });
  });

  await page.reload();
  await page.getByRole('option', { name: /Hồ sơ giới thiệu Hồng Vân/ }).click();
  page.once('dialog', (dialog) => dialog.accept());
  await page.getByRole('button', { name: 'Chuyển vào thùng rác' }).click();
  await expect(page.getByText('Đã chuyển media vào thùng rác.')).toBeVisible();

  await page.getByRole('combobox', { name: 'Phạm vi' }).click();
  await page.getByRole('option', { name: 'Chỉ thùng rác' }).click();
  await page.getByRole('button', { name: 'Áp dụng' }).click();
  await page.getByRole('option', { name: /Hồ sơ giới thiệu Hồng Vân/ }).click();
  await page.getByRole('button', { name: 'Khôi phục' }).click();
  await expect(page.getByText('Đã khôi phục media.')).toBeVisible();
});

async function mockAuthenticatedUser(page: Page): Promise<void> {
  await page.route('**/api/admin/v1/**', async (route) => {
    await route.fulfill({ json: envelope([]) });
  });
  await page.route('**/api/admin/v1/auth/me', async (route) => {
    await route.fulfill({ json: envelope({
      public_id: '01K1USER000000000000000001',
      name: 'Quản trị Hồng Vân',
      email: 'admin@example.test',
      email_verified_at: '2026-08-03T00:00:00.000Z',
      is_active: true,
      locked_at: null,
      roles: ['super_admin'],
      permissions: ['media.view', 'media.create', 'media.update', 'media.delete', 'media.restore'],
    }) });
  });
  await page.route('**/api/admin/v1/preferences', async (route) => {
    await route.fulfill({ json: envelope({
      theme: {
        fixed_header: true,
        fixed_sidenav: true,
        fixed_footer: false,
        sidenav_opened: true,
        sidenav_pinned: true,
        menu_orientation: 'vertical',
        menu_density: 'comfortable',
        skin: 'skin-indigo-light',
        rtl: false,
      },
      locale: 'vi',
      favorite_menu_ids: [],
    }) });
  });
}

async function mockMediaApi(page: Page): Promise<void> {
  await page.route('**/api/admin/v1/media**', async (route) => {
    const request = route.request();
    const url = new URL(request.url());
    if (url.pathname.endsWith('/folders')) {
      await route.fulfill({ json: envelope([folder]) });
      return;
    }
    if (url.pathname.endsWith(`/${mediaId}`)) {
      await route.fulfill({ json: envelope(mediaItem) });
      return;
    }
    if (url.pathname.endsWith(`/${mediaId}/content`)) {
      await route.fulfill({ status: 200, contentType: 'application/pdf', body: '%PDF-1.4\n%%EOF' });
      return;
    }
    await route.fulfill({
      json: {
        ...envelope([mediaItem]),
        meta: { request_id: '01K1REQUEST000000000000001', pagination: { page: 1, last_page: 1, per_page: 24, total: 1 } },
      },
    });
  });
  await page.route('**/api/admin/v1/media/folders', async (route) => {
    await route.fulfill({ json: envelope([folder]) });
  });
  await page.route(`**/api/admin/v1/media/${mediaId}`, async (route) => {
    await route.fulfill({ json: envelope(mediaItem) });
  });
  await page.route(`**/api/admin/v1/media/${mediaId}/content`, async (route) => {
    await route.fulfill({ status: 200, contentType: 'application/pdf', body: '%PDF-1.4\n%%EOF' });
  });
}

function envelope(data: unknown): { readonly success: true; readonly data: unknown; readonly meta: { readonly request_id: string }; readonly message: null } {
  return { success: true, data, meta: { request_id: '01K1REQUEST000000000000001' }, message: null };
}
