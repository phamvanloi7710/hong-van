import { PostLocale } from './post.models';

type Dictionary = Readonly<Record<string, string>>;

export const POST_TRANSLATIONS: Readonly<Record<PostLocale, Dictionary>> = {
  vi: {
    eyebrow: 'QUẢN TRỊ NỘI DUNG', title: 'Tin tức & bài viết', subtitle: 'Quản lý bài viết, danh mục, thẻ, tác giả và lịch xuất bản bằng ba ngôn ngữ.',
    posts: 'Bài viết', categories: 'Danh mục', tags: 'Thẻ', addPost: 'Thêm bài viết', addCategory: 'Thêm danh mục', addTag: 'Thêm thẻ', refresh: 'Làm mới',
    search: 'Tìm bài viết', status: 'Trạng thái', category: 'Danh mục', allStatuses: 'Tất cả trạng thái', allCategories: 'Tất cả danh mục', dataScope: 'Phạm vi dữ liệu', activeOnly: 'Đang sử dụng', trashOnly: 'Thùng rác', apply: 'Áp dụng', clear: 'Xóa lọc',
    emptyPosts: 'Chưa có bài viết phù hợp.', emptyCategories: 'Chưa có danh mục.', emptyTags: 'Chưa có thẻ.', loadError: 'Không thể tải dữ liệu bài viết.', operationError: 'Không thể thực hiện thao tác.',
    createPost: 'Tạo bài viết', editPost: 'Chỉnh sửa bài viết', createCategory: 'Tạo danh mục', editCategory: 'Chỉnh sửa danh mục', createTag: 'Tạo thẻ', editTag: 'Chỉnh sửa thẻ',
    general: 'Thông tin chung', localizedContent: 'Nội dung đa ngôn ngữ', publishing: 'Xuất bản', preview: 'Xem trước', code: 'Mã', author: 'Tác giả', currentAuthor: 'Người đang thao tác', parentCategory: 'Danh mục cha', noSelection: 'Không chọn',
    titleField: 'Tiêu đề', slug: 'Slug', excerpt: 'Tóm tắt', content: 'Nội dung', metaTitle: 'Meta title', metaDescription: 'Meta description', featured: 'Nổi bật', active: 'Đang hoạt động', order: 'Thứ tự',
    tagSelection: 'Thẻ bài viết', featuredImage: 'Ảnh đại diện', chooseImage: 'Chọn ảnh từ Media', removeImage: 'Bỏ ảnh', scheduledFor: 'Thời điểm hẹn xuất bản', publishedAt: 'Thời điểm đã xuất bản', unpublishedAt: 'Thời điểm ngừng hiển thị',
    draft: 'Bản nháp', scheduled: 'Hẹn lịch', published: 'Đã xuất bản', archived: 'Đã lưu trữ', languageVi: 'Tiếng Việt', languageEn: 'Tiếng Anh', languageZh: 'Tiếng Trung',
    save: 'Lưu', cancel: 'Hủy', close: 'Đóng', edit: 'Sửa', delete: 'Xóa', publish: 'Xuất bản', archive: 'Lưu trữ', restore: 'Khôi phục', postsCount: 'bài viết',
    created: 'Đã tạo dữ liệu.', updated: 'Đã cập nhật dữ liệu.', deleted: 'Đã chuyển vào thùng rác.', publishedMessage: 'Đã xuất bản bài viết.', archivedMessage: 'Đã lưu trữ bài viết.', restoredMessage: 'Đã khôi phục bài viết.', confirmDelete: 'Bạn chắc chắn muốn chuyển dữ liệu này vào thùng rác?',
    publicDeferred: 'Trang tin public, RSS và block danh sách bài viết trong Page Builder sẽ kết nối ở giai đoạn frontend cuối cùng.', sanitizerNotice: 'Nội dung HTML sẽ được Backend lọc theo allowlist; script, iframe, embed, event handler và URL nguy hiểm bị loại bỏ.', previewEmpty: 'Chưa có nội dung để xem trước.',
  },
  en: {
    eyebrow: 'CONTENT MANAGEMENT', title: 'News & posts', subtitle: 'Manage posts, categories, tags, authors and publishing schedules in three languages.',
    posts: 'Posts', categories: 'Categories', tags: 'Tags', addPost: 'Add post', addCategory: 'Add category', addTag: 'Add tag', refresh: 'Refresh',
    search: 'Search posts', status: 'Status', category: 'Category', allStatuses: 'All statuses', allCategories: 'All categories', dataScope: 'Data scope', activeOnly: 'Active items', trashOnly: 'Trash', apply: 'Apply', clear: 'Clear filters',
    emptyPosts: 'No matching posts.', emptyCategories: 'No categories.', emptyTags: 'No tags.', loadError: 'Unable to load post data.', operationError: 'Unable to complete the operation.',
    createPost: 'Create post', editPost: 'Edit post', createCategory: 'Create category', editCategory: 'Edit category', createTag: 'Create tag', editTag: 'Edit tag',
    general: 'General', localizedContent: 'Localized content', publishing: 'Publishing', preview: 'Preview', code: 'Code', author: 'Author', currentAuthor: 'Current administrator', parentCategory: 'Parent category', noSelection: 'None',
    titleField: 'Title', slug: 'Slug', excerpt: 'Excerpt', content: 'Content', metaTitle: 'Meta title', metaDescription: 'Meta description', featured: 'Featured', active: 'Active', order: 'Order',
    tagSelection: 'Post tags', featuredImage: 'Featured image', chooseImage: 'Choose from Media', removeImage: 'Remove image', scheduledFor: 'Scheduled publication time', publishedAt: 'Published time', unpublishedAt: 'End visibility time',
    draft: 'Draft', scheduled: 'Scheduled', published: 'Published', archived: 'Archived', languageVi: 'Vietnamese', languageEn: 'English', languageZh: 'Chinese',
    save: 'Save', cancel: 'Cancel', close: 'Close', edit: 'Edit', delete: 'Delete', publish: 'Publish', archive: 'Archive', restore: 'Restore', postsCount: 'posts',
    created: 'Data created.', updated: 'Data updated.', deleted: 'Moved to trash.', publishedMessage: 'Post published.', archivedMessage: 'Post archived.', restoredMessage: 'Post restored.', confirmDelete: 'Move this item to trash?',
    publicDeferred: 'Public news pages, RSS and the Page Builder post-list block will be connected during the final frontend phase.', sanitizerNotice: 'Backend allowlist sanitization removes scripts, iframes, embeds, event handlers and dangerous URLs.', previewEmpty: 'There is no content to preview.',
  },
  zh: {
    eyebrow: '内容管理', title: '新闻与文章', subtitle: '使用三种语言管理文章、分类、标签、作者和发布计划。',
    posts: '文章', categories: '分类', tags: '标签', addPost: '添加文章', addCategory: '添加分类', addTag: '添加标签', refresh: '刷新',
    search: '搜索文章', status: '状态', category: '分类', allStatuses: '全部状态', allCategories: '全部分类', dataScope: '数据范围', activeOnly: '使用中', trashOnly: '回收站', apply: '应用', clear: '清除筛选',
    emptyPosts: '没有匹配的文章。', emptyCategories: '暂无分类。', emptyTags: '暂无标签。', loadError: '无法加载文章数据。', operationError: '无法完成操作。',
    createPost: '创建文章', editPost: '编辑文章', createCategory: '创建分类', editCategory: '编辑分类', createTag: '创建标签', editTag: '编辑标签',
    general: '基本信息', localizedContent: '多语言内容', publishing: '发布', preview: '预览', code: '代码', author: '作者', currentAuthor: '当前管理员', parentCategory: '父分类', noSelection: '不选择',
    titleField: '标题', slug: 'Slug', excerpt: '摘要', content: '内容', metaTitle: 'Meta 标题', metaDescription: 'Meta 描述', featured: '推荐', active: '启用', order: '顺序',
    tagSelection: '文章标签', featuredImage: '特色图片', chooseImage: '从媒体库选择', removeImage: '移除图片', scheduledFor: '计划发布时间', publishedAt: '实际发布时间', unpublishedAt: '停止显示时间',
    draft: '草稿', scheduled: '定时', published: '已发布', archived: '已归档', languageVi: '越南语', languageEn: '英语', languageZh: '中文',
    save: '保存', cancel: '取消', close: '关闭', edit: '编辑', delete: '删除', publish: '发布', archive: '归档', restore: '恢复', postsCount: '篇文章',
    created: '数据已创建。', updated: '数据已更新。', deleted: '已移至回收站。', publishedMessage: '文章已发布。', archivedMessage: '文章已归档。', restoredMessage: '文章已恢复。', confirmDelete: '确定将此项移至回收站吗？',
    publicDeferred: '公共新闻页面、RSS 和 Page Builder 文章列表区块将在最后的前端阶段连接。', sanitizerNotice: 'Backend 白名单过滤会移除脚本、iframe、embed、事件处理器和危险网址。', previewEmpty: '暂无可预览的内容。',
  },
};
