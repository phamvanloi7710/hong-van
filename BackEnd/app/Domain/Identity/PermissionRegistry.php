<?php

namespace App\Domain\Identity;

final class PermissionRegistry
{
    public const SUPER_ADMIN_ROLE = 'super_admin';

    /** @var list<string> */
    public const ACTIONS = [
        'view',
        'view_all',
        'create',
        'update',
        'delete',
        'restore',
        'publish',
        'export',
        'import',
        'force_unlock',
        'manage_settings',
    ];

    /**
     * @return list<array{key: string, module: string, action: string, name: string, labels: array{vi: string, en: string, zh: string}}>
     */
    public static function definitions(): array
    {
        return [
            self::permission('dashboard', 'view', ['vi' => 'Xem tổng quan', 'en' => 'View dashboard', 'zh' => '查看仪表盘']),
            self::permission('system', 'health', ['vi' => 'Xem trạng thái hệ thống', 'en' => 'View system health', 'zh' => '查看系统状态']),
            ...self::module('users', ['vi' => 'người dùng', 'en' => 'users', 'zh' => '用户'], ['view', 'create', 'update', 'delete']),
            ...self::module('roles', ['vi' => 'vai trò', 'en' => 'roles', 'zh' => '角色'], ['view', 'create', 'update', 'delete']),
            ...self::module('permissions', ['vi' => 'quyền', 'en' => 'permissions', 'zh' => '权限'], ['view', 'create', 'update', 'delete']),
            ...self::module('settings', ['vi' => 'cài đặt', 'en' => 'settings', 'zh' => '设置'], ['view', 'update', 'manage_settings']),
            ...self::module('localization', ['vi' => 'đa ngôn ngữ', 'en' => 'localization', 'zh' => '本地化'], ['view', 'update']),
            ...self::module('audit', ['vi' => 'nhật ký', 'en' => 'audit logs', 'zh' => '审计日志'], ['view']),
            ...self::module('products', ['vi' => 'sản phẩm', 'en' => 'products', 'zh' => '产品'], ['view', 'create', 'update', 'delete', 'restore', 'publish']),
            ...self::module('crops', ['vi' => 'cây trồng', 'en' => 'crops', 'zh' => '作物'], ['view', 'create', 'update', 'delete']),
            ...self::module('crop_solutions', ['vi' => 'giải pháp cây trồng', 'en' => 'crop solutions', 'zh' => '作物解决方案'], ['view', 'create', 'update', 'delete', 'publish']),
            ...self::module('services', ['vi' => 'dịch vụ', 'en' => 'services', 'zh' => '服务'], ['view', 'create', 'update', 'delete', 'restore', 'publish']),
            ...self::module('transportation', ['vi' => 'vận chuyển', 'en' => 'transportation', 'zh' => '运输'], ['view', 'create', 'update', 'delete', 'publish']),
            ...self::module('warehouses', ['vi' => 'kho bãi', 'en' => 'warehouses', 'zh' => '仓库'], ['view', 'create', 'update', 'delete', 'publish']),
            ...self::module('leads', ['vi' => 'lead', 'en' => 'leads', 'zh' => '潜在客户'], ['view', 'view_all', 'update', 'export']),
            ...self::module('pages', ['vi' => 'trang', 'en' => 'pages', 'zh' => '页面'], ['view', 'create', 'update', 'publish', 'export', 'import', 'force_unlock']),
            ...self::module('posts', ['vi' => 'bài viết', 'en' => 'posts', 'zh' => '文章'], ['view', 'create', 'update', 'delete', 'restore', 'publish']),
            ...self::module('showcase', ['vi' => 'trưng bày doanh nghiệp', 'en' => 'showcase', 'zh' => '企业展示'], ['view', 'create', 'update', 'delete', 'restore', 'publish']),
            ...self::module('seo', ['vi' => 'SEO', 'en' => 'SEO', 'zh' => 'SEO'], ['view', 'update']),
            ...self::module('themes', ['vi' => 'giao diện public', 'en' => 'public themes', 'zh' => '公共主题'], ['view', 'update', 'publish']),
            ...self::module('media', ['vi' => 'media', 'en' => 'media', 'zh' => '媒体'], ['view', 'create', 'update', 'delete', 'restore']),
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_column(self::definitions(), 'key');
    }

    /** @return array{vi: string, en: string, zh: string}|null */
    public static function labels(string $key): ?array
    {
        foreach (self::definitions() as $definition) {
            if ($definition['key'] === $key) {
                return $definition['labels'];
            }
        }

        return null;
    }

    public static function isValidKey(string $key): bool
    {
        if (preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/', $key) !== 1) {
            return false;
        }

        [, $action] = explode('.', $key, 2);

        return $action === 'health' || in_array($action, self::ACTIONS, true);
    }

    /**
     * @param  array{vi: string, en: string, zh: string}  $moduleLabels
     * @param  list<string>  $actions
     * @return list<array{key: string, module: string, action: string, name: string, labels: array{vi: string, en: string, zh: string}}>
     */
    private static function module(string $module, array $moduleLabels, array $actions): array
    {
        return array_map(
            static function (string $action) use ($module, $moduleLabels): array {
                $actionLabels = self::actionLabels($action);

                return self::permission($module, $action, [
                    'vi' => $actionLabels['vi'].' '.$moduleLabels['vi'],
                    'en' => $actionLabels['en'].' '.$moduleLabels['en'],
                    'zh' => $moduleLabels['zh'].$actionLabels['zh'],
                ]);
            },
            $actions,
        );
    }

    /**
     * @param  array{vi: string, en: string, zh: string}  $labels
     * @return array{key: string, module: string, action: string, name: string, labels: array{vi: string, en: string, zh: string}}
     */
    private static function permission(string $module, string $action, array $labels): array
    {
        return [
            'key' => $module.'.'.$action,
            'module' => $module,
            'action' => $action,
            'name' => $labels['vi'],
            'labels' => $labels,
        ];
    }

    /** @return array{vi: string, en: string, zh: string} */
    private static function actionLabels(string $action): array
    {
        return [
            'view' => ['vi' => 'Xem', 'en' => 'View', 'zh' => '查看'],
            'view_all' => ['vi' => 'Xem tất cả', 'en' => 'View all', 'zh' => '查看全部'],
            'create' => ['vi' => 'Tạo', 'en' => 'Create', 'zh' => '创建'],
            'update' => ['vi' => 'Cập nhật', 'en' => 'Update', 'zh' => '更新'],
            'delete' => ['vi' => 'Xóa', 'en' => 'Delete', 'zh' => '删除'],
            'restore' => ['vi' => 'Khôi phục', 'en' => 'Restore', 'zh' => '恢复'],
            'publish' => ['vi' => 'Xuất bản', 'en' => 'Publish', 'zh' => '发布'],
            'export' => ['vi' => 'Xuất dữ liệu', 'en' => 'Export', 'zh' => '导出'],
            'import' => ['vi' => 'Nhập dữ liệu', 'en' => 'Import', 'zh' => '导入'],
            'force_unlock' => ['vi' => 'Buộc mở khóa', 'en' => 'Force unlock', 'zh' => '强制解锁'],
            'manage_settings' => ['vi' => 'Quản lý', 'en' => 'Manage', 'zh' => '管理'],
        ][$action];
    }
}
