import { Page } from '@playwright/test';
import { expect, test } from './support/qa-test';

const pageId = '01KZPAGEPREVIEW00000000001';
const blockId = 'block-preview-0001';
const sessionId = '01KZPREVIEWSESSION000000001';
const token = 't'.repeat(64);

test('Blade iframe preview enforces the message contract and follows editor updates', async ({ page }) => {
  let previewUpdates = 0;
  await mockAuthenticatedAdmin(page);
  await page.route('**/api/admin/v1/page-builder/registry', (route) => route.fulfill({ json: envelope(registryFixture()) }));
  await page.route('**/api/admin/v1/page-builder/pages?**', (route) => route.fulfill({ json: envelope([pageFixture()]) }));
  await page.route(`**/api/admin/v1/page-builder/pages/${pageId}/preview-sessions`, (route) => route.fulfill({
    status: 201,
    json: envelope(previewSession(1)),
  }));
  await page.route(`**/api/admin/v1/page-builder/pages/${pageId}/draft`, (route) => route.fulfill({ json: envelope(pageFixture()) }));
  await page.route(`**/api/admin/v1/page-builder/preview-sessions/${sessionId}`, (route) => {
    if (route.request().method() === 'DELETE') return route.fulfill({ json: envelope(null) });
    previewUpdates += 1;
    return route.fulfill({ json: envelope(previewSession(previewUpdates + 1)) });
  });
  await page.route(`**/api/admin/v1/page-builder/preview-sessions/${sessionId}/refresh`, (route) => route.fulfill({ json: envelope(previewSession(previewUpdates + 1)) }));
  await page.route('**/preview/page-builder/**', (route) => route.fulfill({
    contentType: 'text/html',
    body: previewHtml(),
  }));

  await page.goto('page-builder');
  const frame = page.frameLocator('iframe[title="Blade iframe host"]');
  await expect(frame.locator(`[data-block-id="${blockId}"]`)).toContainText('Blade renderer block');
  await expect(frame.locator('#preview-status')).toHaveText('ready-1');
  await expect(page.getByText('Blade preview đã sẵn sàng', { exact: true })).toBeVisible();

  await frame.locator('body').evaluate((body, payload) => {
    window.parent.postMessage(payload, window.location.origin);
    return body.tagName;
  }, { channel: 'hongvan.page-builder.preview', schemaVersion: 1, type: 'preview.block-selected', token: 'wrong-token', blockId });
  await expect(page.locator('.selected-summary')).toHaveCount(0);

  await frame.locator(`[data-block-id="${blockId}"]`).click();
  await expect(page.locator('.selected-summary')).toContainText('Vùng chờ nội dung');
  const inspectorInput = page.locator('.inspector-section input');
  await inspectorInput.fill('Nội dung đã cập nhật');
  await inspectorInput.press('Tab');
  await expect.poll(() => previewUpdates).toBe(1);
  await expect(frame.locator('#preview-status')).toHaveText('ready-2');

  await page.getByRole('button', { name: 'Tablet', exact: true }).click();
  await expect.poll(() => page.locator('.iframe-shell').evaluate((element) => Math.round(element.getBoundingClientRect().width))).toBeLessThanOrEqual(768);
});

async function mockAuthenticatedAdmin(page: Page): Promise<void> {
  await page.route('**/api/admin/v1/**', (route) => route.fulfill({ json: envelope([]) }));
  await page.route('**/api/admin/v1/preferences', (route) => route.fulfill({ json: envelope({
    theme: { fixed_header: true, fixed_sidenav: true, fixed_footer: false, sidenav_opened: true, sidenav_pinned: true, menu_orientation: 'vertical', menu_density: 'default', skin: 'indigo-light', rtl: false },
    locale: 'vi', favorite_menu_ids: [],
  }) }));
  await page.route('**/api/admin/v1/auth/me', (route) => route.fulfill({ json: envelope({
    public_id: '01KZUSERPREVIEW0000000001', name: 'QA Admin', email: 'admin@example.test', email_verified_at: '2026-08-03T00:00:00Z', is_active: true, locked_at: null,
    roles: ['super_admin'], permissions: ['dashboard.view', 'pages.view', 'pages.update', 'pages.publish'],
  }) }));
}

function registryFixture() {
  return {
    document: { schemaVersion: 1, limits: { maxBytes: 524288, maxDepth: 12, maxBlocks: 300 }, blockFields: [], pageSettings: { container: ['default', 'wide', 'full'], background: ['surface', 'muted', 'brand'] } },
    blocks: [{
      type: 'foundation.placeholder', version: 1, labels: { vi: 'Vùng chờ nội dung', en: 'Placeholder', zh: '占位区块' }, category: 'foundation', icon: 'widgets', thumbnail: null,
      schema: { props: { type: 'object', properties: { label: { type: 'string', maxLength: 160 } }, required: ['label'] }, style: { type: 'object', properties: { desktop: { type: 'object', properties: {} }, tablet: { type: 'object', properties: {} }, mobile: { type: 'object', properties: {} } } }, visibility: {}, bindings: {} },
      defaults: { props: { label: 'Blade renderer block' }, style: { desktop: {}, tablet: {}, mobile: {} }, visibility: { desktop: true, tablet: true, mobile: true }, bindings: {}, children: [] },
      allowRoot: true, allowedParents: [], allowedChildren: [], maxDepth: 12, minChildren: 0, maxChildren: 0, dataDependencies: [], permissions: [], cacheTags: ['page-builder'],
    }],
    dataSources: [], forms: [], cache: {},
  };
}

function pageFixture() {
  return {
    public_id: pageId, code: 'preview-page', type: 'standard', status: 'draft', is_home: false,
    translations: [{ locale: 'vi', title: 'Trang preview', navigation_label: 'Preview', slug: 'preview' }],
    draft: { public_id: '01KZDRAFTPREVIEW000000001', version_number: 1, status: 'draft', schema_version: 1, checksum: 'a'.repeat(64), document: documentFixture(), published_at: null, updated_at: null },
    published: null, created_at: null, updated_at: null,
  };
}

function documentFixture() {
  return {
    schemaVersion: 1, themeVersionId: null,
    pageSettings: { container: 'default', background: 'surface', hideHeader: false, hideFooter: false },
    blocks: [{ id: blockId, type: 'foundation.placeholder', version: 1, props: { label: 'Blade renderer block' }, style: { desktop: {}, tablet: {}, mobile: {} }, visibility: { desktop: true, tablet: true, mobile: true }, bindings: {}, children: [] }],
  };
}

function previewSession(revision: number) {
  return {
    public_id: sessionId, token,
    url: `http://hongvan.local/preview/page-builder/${token}?expires=9999999999&signature=test`,
    expires_at: '2099-08-03T12:00:00.000Z', ttl_seconds: 300, revision, message_schema_version: 1,
  };
}

function previewHtml(): string {
  return `<!doctype html><html><body><main><div class="pb-foundation-placeholder" data-block-id="${blockId}">Blade renderer block</div><output id="preview-status">ready-1</output></main><script>
    (() => {
      const channel = 'hongvan.page-builder.preview'; const schemaVersion = 1; const token = '${token}'; const origin = location.origin; let revision = 1;
      addEventListener('message', (event) => { const message = event.data; if (event.origin !== origin || event.source !== parent || !message || message.channel !== channel || message.schemaVersion !== schemaVersion || message.token !== token) return; if (message.type === 'preview.handshake') parent.postMessage({ channel, schemaVersion, type: 'preview.ready', token }, origin); if (message.type === 'preview.refresh') document.querySelector('#preview-status').textContent = 'ready-' + (++revision); });
      document.querySelector('[data-block-id]').addEventListener('click', () => parent.postMessage({ channel, schemaVersion, type: 'preview.block-selected', token, blockId: '${blockId}' }, origin));
      parent.postMessage({ channel, schemaVersion, type: 'preview.ready', token }, origin);
    })();
  </script></body></html>`;
}

function envelope(data: unknown) {
  return { success: true as const, data, meta: { request_id: '01KZREQUESTPREVIEW0000001' }, message: null };
}
