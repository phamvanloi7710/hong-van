import { expect, test as base } from '@playwright/test';

export const test = base.extend<{ qaGuard: void }>({
  qaGuard: [async ({ page }, use) => {
    const failures: string[] = [];

    page.on('console', (message) => {
      const text = message.text();
      const expectedAnonymousProbe = text.startsWith('Failed to load resource:') && text.includes('401');
      if (message.type() === 'error' && !expectedAnonymousProbe) failures.push(`console: ${text}`);
    });
    page.on('pageerror', (error) => failures.push(`page: ${error.message}`));
    page.on('response', (response) => {
      const expectedAnonymousProbe = response.status() === 401 && response.url().endsWith('/api/admin/v1/auth/me');
      if (response.status() >= 400 && !expectedAnonymousProbe) failures.push(`http: ${response.status()} ${response.url()}`);
    });
    page.on('requestfailed', (request) => {
      const reason = request.failure()?.errorText ?? 'unknown error';
      if (reason !== 'net::ERR_ABORTED') failures.push(`network: ${request.method()} ${request.url()} (${reason})`);
    });

    await use();
    expect(failures, 'Browser console, page and network failures').toEqual([]);
  }, { auto: true }],
});

export { expect };
