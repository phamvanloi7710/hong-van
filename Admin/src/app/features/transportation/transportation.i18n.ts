import { TransportLocale } from './transportation.models';

type Dictionary = Readonly<Record<string, string>>;

export const TRANSPORT_TRANSLATIONS: Readonly<Record<TransportLocale, Dictionary>> = {
  vi: {
    eyebrow: 'NĂNG LỰC VẬN TẢI', title: 'Vận chuyển', subtitle: 'Quản lý đội xe, tuyến và khu vực phục vụ công khai.',
    types: 'Loại xe', vehicles: 'Đội xe', routes: 'Tuyến vận chuyển', areas: 'Khu vực phục vụ', add: 'Thêm', refresh: 'Làm mới', empty: 'Chưa có dữ liệu.',
    code: 'Mã', name: 'Tên', slug: 'Slug', summary: 'Tóm tắt', description: 'Mô tả', dimensions: 'Mô tả thùng/kích thước', vehicleType: 'Loại xe',
    payload: 'Tải trọng', unit: 'Đơn vị', availability: 'Hiển thị khả dụng', origin: 'Điểm đi', destination: 'Điểm đến', status: 'Trạng thái', order: 'Thứ tự',
    featured: 'Nổi bật', active: 'Hoạt động', media: 'Media', chooseMedia: 'Chọn media', selectedMedia: 'Media đã chọn', remove: 'Gỡ', save: 'Lưu', cancel: 'Hủy',
    edit: 'Sửa', delete: 'Xóa', publish: 'Xuất bản', draft: 'Bản nháp', published: 'Đã xuất bản', scheduled: 'Hẹn lịch', archived: 'Đã lưu trữ',
    available: 'Sẵn sàng', limited: 'Hạn chế', unavailable: 'Không khả dụng', contact: 'Liên hệ', languageVi: 'Tiếng Việt', languageEn: 'Tiếng Anh', languageZh: 'Tiếng Trung',
    general: 'Thông tin chung', localizedContent: 'Nội dung đa ngôn ngữ', publishing: 'Xuất bản', publishedAt: 'Thời gian xuất bản', unpublishedAt: 'Thời gian ngừng hiển thị',
    create: 'Tạo dữ liệu', update: 'Cập nhật dữ liệu', created: 'Đã tạo dữ liệu.', updated: 'Đã cập nhật dữ liệu.', deleted: 'Đã xóa dữ liệu.', publishedMessage: 'Đã xuất bản.',
    close: 'Đóng', loadError: 'Không thể tải dữ liệu vận chuyển.', operationError: 'Không thể thực hiện thao tác.', confirmDelete: 'Bạn chắc chắn muốn xóa dữ liệu này?',
    boundary: 'Module chỉ giới thiệu năng lực và nhận yêu cầu; không có GPS, điều phối hoặc tính cước tự động.',
    publicDeferred: 'Public SSR và Page Builder sẽ được kết nối ở giai đoạn frontend cuối cùng.',
  },
  en: {
    eyebrow: 'TRANSPORT CAPABILITY', title: 'Transportation', subtitle: 'Manage public fleet, route and service-area content.',
    types: 'Vehicle types', vehicles: 'Fleet', routes: 'Transport routes', areas: 'Service areas', add: 'Add', refresh: 'Refresh', empty: 'No data.',
    code: 'Code', name: 'Name', slug: 'Slug', summary: 'Summary', description: 'Description', dimensions: 'Body/dimension description', vehicleType: 'Vehicle type',
    payload: 'Payload', unit: 'Unit', availability: 'Availability display', origin: 'Origin', destination: 'Destination', status: 'Status', order: 'Order',
    featured: 'Featured', active: 'Active', media: 'Media', chooseMedia: 'Choose media', selectedMedia: 'Selected media', remove: 'Remove', save: 'Save', cancel: 'Cancel',
    edit: 'Edit', delete: 'Delete', publish: 'Publish', draft: 'Draft', published: 'Published', scheduled: 'Scheduled', archived: 'Archived',
    available: 'Available', limited: 'Limited', unavailable: 'Unavailable', contact: 'Contact', languageVi: 'Vietnamese', languageEn: 'English', languageZh: 'Chinese',
    general: 'General', localizedContent: 'Localized content', publishing: 'Publishing', publishedAt: 'Publish at', unpublishedAt: 'Unpublish at',
    create: 'Create data', update: 'Update data', created: 'Data created.', updated: 'Data updated.', deleted: 'Data deleted.', publishedMessage: 'Published.',
    close: 'Close', loadError: 'Unable to load transportation data.', operationError: 'Unable to complete the operation.', confirmDelete: 'Delete this item?',
    boundary: 'This module only presents capabilities and accepts enquiries; it has no GPS, dispatch or automatic fare calculation.',
    publicDeferred: 'Public SSR and Page Builder will be connected during the final frontend phase.',
  },
  zh: {
    eyebrow: '运输能力', title: '运输', subtitle: '管理公开的车队、路线和服务区域内容。',
    types: '车辆类型', vehicles: '车队', routes: '运输路线', areas: '服务区域', add: '添加', refresh: '刷新', empty: '暂无数据。',
    code: '代码', name: '名称', slug: 'Slug', summary: '摘要', description: '描述', dimensions: '车厢/尺寸描述', vehicleType: '车辆类型',
    payload: '载重', unit: '单位', availability: '可用性显示', origin: '起点', destination: '终点', status: '状态', order: '顺序',
    featured: '推荐', active: '启用', media: '媒体', chooseMedia: '选择媒体', selectedMedia: '已选媒体', remove: '移除', save: '保存', cancel: '取消',
    edit: '编辑', delete: '删除', publish: '发布', draft: '草稿', published: '已发布', scheduled: '定时', archived: '已归档',
    available: '可用', limited: '有限', unavailable: '不可用', contact: '联系', languageVi: '越南语', languageEn: '英语', languageZh: '中文',
    general: '基本信息', localizedContent: '多语言内容', publishing: '发布', publishedAt: '发布时间', unpublishedAt: '停止显示时间',
    create: '创建数据', update: '更新数据', created: '数据已创建。', updated: '数据已更新。', deleted: '数据已删除。', publishedMessage: '已发布。',
    close: '关闭', loadError: '无法加载运输数据。', operationError: '无法完成操作。', confirmDelete: '确定删除此项吗？',
    boundary: '此模块仅展示能力并接收咨询；不包含 GPS、调度或自动计价。',
    publicDeferred: '公共 SSR 和 Page Builder 将在最后的前端阶段连接。',
  },
};
