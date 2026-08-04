import { AdminLocale } from '../../core/i18n/i18n.service';

const VI = {
  eyebrow: 'TRÌNH DỰNG TRANG',
  title: 'Page Builder',
  description: 'Sắp xếp block theo registry của server và lưu bản nháp an toàn.',
  loading: 'Đang tải Page Builder…',
  loadError: 'Không thể tải dữ liệu Page Builder.',
  emptyPages: 'Chưa có page để chỉnh sửa. Hãy tạo metadata page trước khi lưu document.',
  page: 'Page',
  noPage: 'Chưa chọn page',
  draft: 'Bản nháp',
  saved: 'Đã lưu',
  saving: 'Đang lưu…',
  unsaved: 'Có thay đổi chưa lưu',
  save: 'Lưu ngay',
  saveError: 'Không thể lưu bản nháp.',
  saveConflict: 'Bản nháp đã thay đổi ở phiên khác. Hãy tải lại trước khi tiếp tục.',
  savedNotice: 'Đã lưu bản nháp Page Builder.',
  refresh: 'Tải lại',
  undo: 'Hoàn tác',
  redo: 'Làm lại',
  publish: 'Xuất bản',
  schedule: 'Lên lịch',
  schedulePrompt: 'Nhập ngày giờ xuất bản (ví dụ 2026-08-05 09:00):',
  scheduleInvalid: 'Ngày giờ xuất bản không hợp lệ.',
  scheduledNotice: 'Đã lên lịch xuất bản.',
  versionHistory: 'Lịch sử phiên bản',
  versionNote: 'Ghi chú cho phiên bản:',
  versionSaved: 'Đã tạo mốc phiên bản bất biến.',
  previewVersion: 'Xem trước',
  noVersions: 'Chưa có mốc phiên bản.',
  rollback: 'Khôi phục',
  rollbackConfirm: 'Khôi phục phiên bản v{version} và xuất bản thành phiên bản mới?',
  rollbackNotice: 'Đã khôi phục và xuất bản phiên bản mới.',
  publishConfirm: 'Xuất bản bản nháp hiện tại ngay bây giờ?',
  publishedNotice: 'Đã xuất bản trang.',
  publishError: 'Không thể thực hiện thao tác xuất bản.',
  publishPlanned: 'Quy trình xuất bản sẽ được kết nối ở P28.',
  previewPlanned: 'Iframe Blade preview sẽ được kết nối ở P27.',
  previewConnecting: 'Đang mở phiên preview…',
  previewLoading: 'Đang tải Blade preview…',
  previewReady: 'Blade preview đã sẵn sàng',
  previewUpdating: 'Đang cập nhật preview…',
  previewError: 'Không thể tải Blade preview.',
  previewValidationError: 'Document preview chưa hợp lệ',
  previewRefresh: 'Làm mới preview',
  palette: 'Thư viện block',
  addBlock: 'Thêm block',
  workspaceNavigation: 'Thư viện block và cây document',
  paletteSearch: 'Tìm block',
  allCategories: 'Tất cả nhóm',
  layers: 'Cây document',
  emptyDocument: 'Kéo một layout block vào đây để bắt đầu.',
  canvas: 'Canvas',
  canvasHost: 'Blade iframe host',
  canvasHint: 'Canvas dùng trực tiếp Blade renderer và CSS của website public.',
  properties: 'Thuộc tính',
  selectBlock: 'Chọn một block trong cây document để chỉnh sửa.',
  content: 'Nội dung',
  responsiveStyle: 'Style responsive',
  visibility: 'Hiển thị theo thiết bị',
  visible: 'Hiển thị',
  hidden: 'Ẩn',
  desktop: 'Desktop',
  tablet: 'Tablet',
  mobile: 'Mobile',
  duplicate: 'Nhân bản block',
  delete: 'Xóa block',
  drag: 'Kéo block',
  select: 'Chọn block',
  noProperties: 'Block này không có thuộc tính nội dung.',
  unsupportedControl: 'Giá trị phức hợp được giữ nguyên và chỉ validate ở server.',
  required: 'Bắt buộc',
  breadcrumbs: 'Đường dẫn block',
  root: 'Document',
  blockCount: '{count} block',
  registryVersion: 'Registry {version}',
  readonly: 'Bạn chỉ có quyền xem. Các thao tác chỉnh sửa đã bị khóa.',
  navigationConfirm: 'Bản nháp có thay đổi chưa lưu. Rời khỏi Page Builder?',
  operationBlocked: 'Thao tác không hợp lệ theo cấu trúc block của server.',
  noCompatibleContainer: 'Block này cần một Khu vực hoặc Khung chứa. Hãy thêm/chọn container trước.',
  'failure.block-not-found': 'Không tìm thấy block.',
  'failure.definition-not-found': 'Block chưa có trong registry.',
  'failure.duplicate-id': 'Không thể tạo block ID duy nhất.',
  'failure.invalid-parent': 'Không thể đặt block vào vị trí này.',
  'failure.invalid-depth': 'Cấu trúc vượt quá độ sâu cho phép.',
  'failure.parent-capacity': 'Container đã đạt số block tối đa.',
  'failure.parent-minimum': 'Container cần giữ số block tối thiểu.',
  'failure.inside-descendant': 'Không thể chuyển block vào block con của chính nó.',
  'failure.document-limit': 'Document đã đạt giới hạn block.',
  'category.foundation': 'Nền tảng',
  'category.layout': 'Bố cục',
  'category.content': 'Nội dung',
  'category.media': 'Media',
  'category.business': 'Nghiệp vụ',
  'category.form': 'Biểu mẫu',
} as const;

export type PageBuilderTranslationKey = keyof typeof VI;

const EN: Record<PageBuilderTranslationKey, string> = {
  eyebrow: 'PAGE COMPOSER', title: 'Page Builder', description: 'Arrange server-registry blocks and safely save the draft.', loading: 'Loading Page Builder…', loadError: 'Unable to load Page Builder data.', emptyPages: 'No page is available for editing. Create page metadata before saving a document.', page: 'Page', noPage: 'No page selected', draft: 'Draft', saved: 'Saved', saving: 'Saving…', unsaved: 'Unsaved changes', save: 'Save now', saveError: 'Unable to save the draft.', saveConflict: 'The draft changed in another session. Reload before continuing.', savedNotice: 'Page Builder draft saved.', refresh: 'Reload', undo: 'Undo', redo: 'Redo', publish: 'Publish', publishPlanned: 'Publishing will be connected in P28.', previewPlanned: 'The Blade preview iframe is connected in P27.', previewConnecting: 'Opening preview session…', previewLoading: 'Loading Blade preview…', previewReady: 'Blade preview is ready', previewUpdating: 'Updating preview…', previewError: 'Unable to load Blade preview.', previewValidationError: 'The preview document is invalid', previewRefresh: 'Refresh preview', palette: 'Block library', addBlock: 'Add block', workspaceNavigation: 'Block library and document tree', paletteSearch: 'Search blocks', allCategories: 'All categories', layers: 'Document tree', emptyDocument: 'Drag a layout block here to begin.', canvas: 'Canvas', canvasHost: 'Blade iframe host', canvasHint: 'The canvas uses the public website Blade renderer and CSS directly.', properties: 'Properties', selectBlock: 'Select a block in the document tree to edit it.', content: 'Content', responsiveStyle: 'Responsive style', visibility: 'Device visibility', visible: 'Visible', hidden: 'Hidden', desktop: 'Desktop', tablet: 'Tablet', mobile: 'Mobile', duplicate: 'Duplicate block', delete: 'Delete block', drag: 'Drag block', select: 'Select block', noProperties: 'This block has no content properties.', unsupportedControl: 'Complex values are preserved and validated only by the server.', required: 'Required', breadcrumbs: 'Block breadcrumbs', root: 'Document', blockCount: '{count} blocks', registryVersion: 'Registry {version}', readonly: 'You have view-only access. Editing controls are locked.', navigationConfirm: 'The draft has unsaved changes. Leave Page Builder?', operationBlocked: 'The server block structure does not allow this operation.',
  'failure.block-not-found': 'The block was not found.', 'failure.definition-not-found': 'The block is not registered.', 'failure.duplicate-id': 'A unique block ID could not be generated.', 'failure.invalid-parent': 'The block cannot be placed here.', 'failure.invalid-depth': 'The structure exceeds the allowed depth.', 'failure.parent-capacity': 'The container has reached its block limit.', 'failure.parent-minimum': 'The container must retain its minimum block count.', 'failure.inside-descendant': 'A block cannot be moved into its own descendant.', 'failure.document-limit': 'The document block limit has been reached.',
  'category.foundation': 'Foundation', 'category.layout': 'Layout', 'category.content': 'Content', 'category.media': 'Media', 'category.business': 'Business', 'category.form': 'Forms',
  noCompatibleContainer: 'This block needs a compatible section or container. Add or select one first.',
  schedule: 'Schedule', schedulePrompt: 'Enter publication date and time (for example 2026-08-05 09:00):', scheduleInvalid: 'The publication date is invalid.', scheduledNotice: 'Publication scheduled.', versionHistory: 'Version history', versionNote: 'Version note:', versionSaved: 'Immutable version milestone saved.', previewVersion: 'Preview', noVersions: 'No version milestone yet.', rollback: 'Restore', rollbackConfirm: 'Restore v{version} and publish it as a new version?', rollbackNotice: 'A restored version was published.', publishConfirm: 'Publish the current draft now?', publishedNotice: 'Page published.', publishError: 'The publishing operation failed.',
};

const ZH: Record<PageBuilderTranslationKey, string> = {
  eyebrow: '页面编排器', title: '页面构建器', description: '根据服务器注册表排列区块并安全保存草稿。', loading: '正在加载页面构建器…', loadError: '无法加载页面构建器数据。', emptyPages: '暂无可编辑页面，请先创建页面元数据再保存文档。', page: '页面', noPage: '未选择页面', draft: '草稿', saved: '已保存', saving: '正在保存…', unsaved: '有未保存的更改', save: '立即保存', saveError: '无法保存草稿。', saveConflict: '草稿已在其他会话中更改，请重新加载后继续。', savedNotice: '页面构建器草稿已保存。', refresh: '重新加载', undo: '撤销', redo: '重做', publish: '发布', publishPlanned: '发布流程将在 P28 中连接。', previewPlanned: 'Blade 预览 iframe 已在 P27 中连接。', previewConnecting: '正在开启预览会话…', previewLoading: '正在加载 Blade 预览…', previewReady: 'Blade 预览已就绪', previewUpdating: '正在更新预览…', previewError: '无法加载 Blade 预览。', previewValidationError: '预览文档无效', previewRefresh: '刷新预览', palette: '区块库', addBlock: '添加区块', workspaceNavigation: '区块库和文档树', paletteSearch: '搜索区块', allCategories: '全部分类', layers: '文档树', emptyDocument: '将布局区块拖到这里开始。', canvas: '画布', canvasHost: 'Blade iframe 宿主', canvasHint: '画布直接使用公开网站的 Blade renderer 和 CSS。', properties: '属性', selectBlock: '在文档树中选择区块进行编辑。', content: '内容', responsiveStyle: '响应式样式', visibility: '设备可见性', visible: '显示', hidden: '隐藏', desktop: '桌面', tablet: '平板', mobile: '手机', duplicate: '复制区块', delete: '删除区块', drag: '拖动区块', select: '选择区块', noProperties: '此区块没有内容属性。', unsupportedControl: '复杂值保持不变，仅由服务器验证。', required: '必填', breadcrumbs: '区块路径', root: '文档', blockCount: '{count} 个区块', registryVersion: '注册表 {version}', readonly: '您只有查看权限，编辑控件已锁定。', navigationConfirm: '草稿有未保存的更改，离开页面构建器？', operationBlocked: '服务器区块结构不允许此操作。',
  'failure.block-not-found': '找不到该区块。', 'failure.definition-not-found': '该区块未注册。', 'failure.duplicate-id': '无法生成唯一的区块 ID。', 'failure.invalid-parent': '该区块不能放在这里。', 'failure.invalid-depth': '结构超过允许深度。', 'failure.parent-capacity': '容器已达到区块上限。', 'failure.parent-minimum': '容器必须保留最少区块数。', 'failure.inside-descendant': '不能将区块移动到其自身后代中。', 'failure.document-limit': '文档已达到区块数量上限。',
  'category.foundation': '基础', 'category.layout': '布局', 'category.content': '内容', 'category.media': '媒体', 'category.business': '业务', 'category.form': '表单',
  noCompatibleContainer: '此区块需要兼容的区域或容器，请先添加或选择一个。',
  schedule: '计划发布', schedulePrompt: '请输入发布日期和时间（例如 2026-08-05 09:00）：', scheduleInvalid: '发布日期无效。', scheduledNotice: '已计划发布。', versionHistory: '版本历史', versionNote: '版本备注：', versionSaved: '已保存不可变版本里程碑。', previewVersion: '预览', noVersions: '暂无版本里程碑。', rollback: '恢复', rollbackConfirm: '恢复 v{version} 并发布为新版本？', rollbackNotice: '恢复的版本已发布。', publishConfirm: '立即发布当前草稿？', publishedNotice: '页面已发布。', publishError: '发布操作失败。',
};

const CATALOGS: Record<AdminLocale, Record<PageBuilderTranslationKey, string>> = {
  vi: VI,
  en: EN,
  zh: ZH,
};

export function pageBuilderText(
  locale: AdminLocale,
  key: PageBuilderTranslationKey,
  parameters: Readonly<Record<string, string | number>> = {},
): string {
  const translation = CATALOGS[locale][key];
  return Object.entries(parameters).reduce(
    (value, [name, replacement]) => value.replaceAll(`{${name}}`, String(replacement)),
    translation,
  );
}

export function pageBuilderCategory(locale: AdminLocale, category: string): string {
  const key = `category.${category}` as PageBuilderTranslationKey;
  return key in CATALOGS[locale] ? CATALOGS[locale][key] : category;
}
