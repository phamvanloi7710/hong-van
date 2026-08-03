import {
  ADMIN_MENU_ITEMS,
  findAdminMenuItemByRoute,
  NAVIGABLE_ADMIN_MENU_ITEMS,
} from './admin-menu';

describe('ADMIN_MENU_ITEMS', () => {
  it('gives every visible leaf menu item a working route', () => {
    const leafItems = ADMIN_MENU_ITEMS.flatMap((item) => item.children ?? [item]);

    expect(leafItems.every((item) => item.route !== undefined)).toBe(true);
    expect(NAVIGABLE_ADMIN_MENU_ITEMS).toHaveLength(16);
    expect(new Set(NAVIGABLE_ADMIN_MENU_ITEMS.map((item) => item.route)).size).toBe(16);
  });

  it('resolves placeholder routes for the page header and favorite menu', () => {
    expect(findAdminMenuItemByRoute('/products')?.id).toBe('products');
    expect(findAdminMenuItemByRoute('/showcase')?.id).toBe('showcase');
    expect(findAdminMenuItemByRoute('/page-builder?mode=draft')?.id).toBe('page-builder');
    expect(findAdminMenuItemByRoute('/seo/details')?.id).toBe('seo');
  });
});
