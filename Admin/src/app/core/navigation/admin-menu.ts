import { AdminMenuItem } from './admin-menu.model';

export const ADMIN_MENU_ITEMS: readonly AdminMenuItem[] = [
  {
    id: 'dashboard',
    label: 'Tổng quan',
    icon: 'dashboard',
    route: '/dashboard',
  },
  {
    id: 'catalog',
    label: 'Danh mục',
    icon: 'inventory_2',
    children: [
      { id: 'products', label: 'Sản phẩm', icon: 'eco', disabled: true },
      {
        id: 'crop-solutions',
        label: 'Giải pháp cây trồng',
        icon: 'spa',
        disabled: true,
      },
      { id: 'services', label: 'Dịch vụ', icon: 'handyman', disabled: true },
    ],
  },
  {
    id: 'operations',
    label: 'Vận hành',
    icon: 'local_shipping',
    children: [
      { id: 'transportation', label: 'Vận chuyển', icon: 'route', disabled: true },
      { id: 'warehouses', label: 'Kho bãi', icon: 'warehouse', disabled: true },
      { id: 'leads', label: 'Liên hệ & báo giá', icon: 'contact_mail', disabled: true },
    ],
  },
  {
    id: 'content',
    label: 'Nội dung',
    icon: 'article',
    children: [
      { id: 'content-pages', label: 'Trang & bài viết', icon: 'description', disabled: true },
      { id: 'media', label: 'Thư viện media', icon: 'perm_media', disabled: true },
      { id: 'page-builder', label: 'Page Builder', icon: 'dashboard_customize', disabled: true },
      { id: 'seo', label: 'SEO', icon: 'travel_explore', disabled: true },
    ],
  },
  {
    id: 'system',
    label: 'Hệ thống',
    icon: 'settings',
    children: [
      {
        id: 'identity',
        label: 'Người dùng & phân quyền',
        icon: 'admin_panel_settings',
        disabled: true,
      },
      { id: 'settings', label: 'Cài đặt', icon: 'tune', disabled: true },
      { id: 'audit', label: 'Nhật ký thao tác', icon: 'history', disabled: true },
    ],
  },
];
