import { AdminLocale } from '../../i18n/i18n.service';

export const NOTIFICATION_TRANSLATIONS: Record<AdminLocale, Readonly<Record<string, string>>> = {
  vi: { title: 'Thông báo', markAll: 'Đánh dấu tất cả đã đọc', empty: 'Chưa có thông báo.', leadReceived: 'Có lead mới cần xử lý', leadDetail: '{type} · {status}', loadError: 'Không thể tải thông báo.', viewAll: 'Mở trung tâm thông báo', unread: 'chưa đọc' },
  en: { title: 'Notifications', markAll: 'Mark all as read', empty: 'No notifications yet.', leadReceived: 'A new lead requires attention', leadDetail: '{type} · {status}', loadError: 'Unable to load notifications.', viewAll: 'Open notification center', unread: 'unread' },
  zh: { title: '通知', markAll: '全部标为已读', empty: '暂无通知。', leadReceived: '有新线索需要处理', leadDetail: '{type} · {status}', loadError: '无法加载通知。', viewAll: '打开通知中心', unread: '未读' },
};
