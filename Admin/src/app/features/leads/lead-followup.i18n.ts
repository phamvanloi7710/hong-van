import { AdminLocale } from '../../core/i18n/i18n.service';

export const LEAD_FOLLOW_UP_TRANSLATIONS: Record<AdminLocale, Readonly<Record<string, string>>> = {
  vi: { followUpAt: 'Hẹn chăm sóc tiếp theo', clearFollowUp: 'Xóa lịch hẹn', saveFollowUp: 'Lưu lịch hẹn' },
  en: { followUpAt: 'Next follow-up', clearFollowUp: 'Clear follow-up', saveFollowUp: 'Save follow-up' },
  zh: { followUpAt: '下次跟进', clearFollowUp: '清除跟进时间', saveFollowUp: '保存跟进时间' },
};
