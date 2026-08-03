import { WarehouseLocale } from './warehouse.models';

type Dictionary = Readonly<Record<string, string>>;

export const WAREHOUSE_TRANSLATIONS: Readonly<Record<WarehouseLocale, Dictionary>> = {
  vi: {
    eyebrow: 'NĂNG LỰC KHO BÃI', title: 'Kho bãi', subtitle: 'Quản lý hồ sơ kho, tiện ích và dịch vụ công khai.', warehouses: 'Kho bãi', facilities: 'Tiện ích', services: 'Dịch vụ kho', add: 'Thêm', refresh: 'Làm mới', empty: 'Chưa có dữ liệu.',
    code: 'Mã', icon: 'Icon', name: 'Tên', slug: 'Slug', summary: 'Tóm tắt', description: 'Mô tả', address: 'Địa chỉ hiển thị', area: 'Diện tích', areaDescription: 'Mô tả diện tích', capacityDescription: 'Mô tả sức chứa', securityDescription: 'Mô tả an ninh', fireSafetyDescription: 'Mô tả PCCC', hoursDescription: 'Mô tả giờ làm việc',
    latitude: 'Vĩ độ', longitude: 'Kinh độ', mapDisplay: 'Hiển thị bản đồ', hidden: 'Ẩn', approximate: 'Xấp xỉ', exact: 'Chính xác', businessHours: 'Giờ làm việc', businessHoursHint: 'Mỗi dòng: mon|08:00|17:00|false', assignedFacilities: 'Tiện ích được gán', assignedServices: 'Dịch vụ được gán',
    status: 'Trạng thái', order: 'Thứ tự', featured: 'Nổi bật', active: 'Hoạt động', media: 'Media', chooseMedia: 'Chọn media', selectedMedia: 'Media đã chọn', remove: 'Gỡ', save: 'Lưu', cancel: 'Hủy', edit: 'Sửa', delete: 'Xóa', publish: 'Xuất bản',
    draft: 'Bản nháp', published: 'Đã xuất bản', scheduled: 'Hẹn lịch', archived: 'Đã lưu trữ', languageVi: 'Tiếng Việt', languageEn: 'Tiếng Anh', languageZh: 'Tiếng Trung', general: 'Thông tin chung', localizedContent: 'Nội dung đa ngôn ngữ', publishing: 'Xuất bản', publishedAt: 'Thời gian xuất bản', unpublishedAt: 'Thời gian ngừng hiển thị', metaTitle: 'Meta title', metaDescription: 'Meta description',
    create: 'Tạo dữ liệu', update: 'Cập nhật dữ liệu', created: 'Đã tạo dữ liệu.', updated: 'Đã cập nhật dữ liệu.', deleted: 'Đã xóa dữ liệu.', publishedMessage: 'Đã xuất bản.', close: 'Đóng', loadError: 'Không thể tải dữ liệu kho bãi.', operationError: 'Không thể thực hiện thao tác.', confirmDelete: 'Bạn chắc chắn muốn xóa dữ liệu này?',
    boundary: 'Module chỉ giới thiệu năng lực và nhận yêu cầu thuê; không có vị trí tồn, nhập/xuất hoặc sổ tồn kho.', mapPrivacy: 'Tọa độ là tùy chọn và tuân theo chế độ ẩn/xấp xỉ/chính xác; hệ thống không lưu API key bản đồ.', publicDeferred: 'Public SSR và Page Builder sẽ được kết nối ở giai đoạn frontend cuối cùng.',
  },
  en: {
    eyebrow: 'WAREHOUSE CAPABILITY', title: 'Warehouses', subtitle: 'Manage public warehouse profiles, facilities and services.', warehouses: 'Warehouses', facilities: 'Facilities', services: 'Warehouse services', add: 'Add', refresh: 'Refresh', empty: 'No data.',
    code: 'Code', icon: 'Icon', name: 'Name', slug: 'Slug', summary: 'Summary', description: 'Description', address: 'Display address', area: 'Area', areaDescription: 'Area description', capacityDescription: 'Capacity description', securityDescription: 'Security description', fireSafetyDescription: 'Fire-safety description', hoursDescription: 'Business-hours description',
    latitude: 'Latitude', longitude: 'Longitude', mapDisplay: 'Map display', hidden: 'Hidden', approximate: 'Approximate', exact: 'Exact', businessHours: 'Business hours', businessHoursHint: 'One line: mon|08:00|17:00|false', assignedFacilities: 'Assigned facilities', assignedServices: 'Assigned services',
    status: 'Status', order: 'Order', featured: 'Featured', active: 'Active', media: 'Media', chooseMedia: 'Choose media', selectedMedia: 'Selected media', remove: 'Remove', save: 'Save', cancel: 'Cancel', edit: 'Edit', delete: 'Delete', publish: 'Publish',
    draft: 'Draft', published: 'Published', scheduled: 'Scheduled', archived: 'Archived', languageVi: 'Vietnamese', languageEn: 'English', languageZh: 'Chinese', general: 'General', localizedContent: 'Localized content', publishing: 'Publishing', publishedAt: 'Publish at', unpublishedAt: 'Unpublish at', metaTitle: 'Meta title', metaDescription: 'Meta description',
    create: 'Create data', update: 'Update data', created: 'Data created.', updated: 'Data updated.', deleted: 'Data deleted.', publishedMessage: 'Published.', close: 'Close', loadError: 'Unable to load warehouse data.', operationError: 'Unable to complete the operation.', confirmDelete: 'Delete this item?',
    boundary: 'This module presents capabilities and accepts enquiries; it has no stock bins, inbound/outbound or inventory ledger.', mapPrivacy: 'Coordinates are optional and follow hidden/approximate/exact privacy; no map API key is stored.', publicDeferred: 'Public SSR and Page Builder will be connected during the final frontend phase.',
  },
  zh: {
    eyebrow: '仓储能力', title: '仓库', subtitle: '管理公开的仓库、设施和服务资料。', warehouses: '仓库', facilities: '设施', services: '仓储服务', add: '添加', refresh: '刷新', empty: '暂无数据。',
    code: '代码', icon: '图标', name: '名称', slug: 'Slug', summary: '摘要', description: '描述', address: '显示地址', area: '面积', areaDescription: '面积说明', capacityDescription: '容量说明', securityDescription: '安保说明', fireSafetyDescription: '消防说明', hoursDescription: '营业时间说明',
    latitude: '纬度', longitude: '经度', mapDisplay: '地图显示', hidden: '隐藏', approximate: '近似', exact: '精确', businessHours: '营业时间', businessHoursHint: '每行：mon|08:00|17:00|false', assignedFacilities: '已分配设施', assignedServices: '已分配服务',
    status: '状态', order: '顺序', featured: '推荐', active: '启用', media: '媒体', chooseMedia: '选择媒体', selectedMedia: '已选媒体', remove: '移除', save: '保存', cancel: '取消', edit: '编辑', delete: '删除', publish: '发布',
    draft: '草稿', published: '已发布', scheduled: '定时', archived: '已归档', languageVi: '越南语', languageEn: '英语', languageZh: '中文', general: '基本信息', localizedContent: '多语言内容', publishing: '发布', publishedAt: '发布时间', unpublishedAt: '停止显示时间', metaTitle: 'Meta title', metaDescription: 'Meta description',
    create: '创建数据', update: '更新数据', created: '数据已创建。', updated: '数据已更新。', deleted: '数据已删除。', publishedMessage: '已发布。', close: '关闭', loadError: '无法加载仓库数据。', operationError: '无法完成操作。', confirmDelete: '确定删除此项吗？',
    boundary: '此模块仅展示能力并接收咨询；不包含库位、入库/出库或库存台账。', mapPrivacy: '坐标可选并遵循隐藏/近似/精确隐私模式；不存储地图 API key。', publicDeferred: '公共 SSR 和 Page Builder 将在最后的前端阶段连接。',
  },
};
