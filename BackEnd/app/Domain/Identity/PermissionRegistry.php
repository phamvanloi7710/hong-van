<?php

namespace App\Domain\Identity;

final class PermissionRegistry
{
    public const SUPER_ADMIN_ROLE = 'super_admin';

    /** @var list<string> */
    public const ACTIONS = [
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'publish',
        'export',
        'manage_settings',
    ];

    /**
     * @return list<array{key: string, module: string, action: string, name: string}>
     */
    public static function definitions(): array
    {
        return [
            self::permission('dashboard', 'view', 'Xem tổng quan'),
            self::permission('system', 'health', 'Xem trạng thái hệ thống'),
            ...self::module('users', 'người dùng', ['view', 'create', 'update', 'delete', 'export']),
            ...self::module('roles', 'vai trò', ['view', 'create', 'update', 'delete']),
            ...self::module('permissions', 'quyền', ['view', 'create', 'update', 'delete']),
            ...self::module('settings', 'cài đặt', ['view', 'update', 'manage_settings']),
            ...self::module('localization', 'đa ngôn ngữ', ['view', 'update']),
            ...self::module('audit', 'nhật ký', ['view', 'export']),
            ...self::module('products', 'sản phẩm', ['view', 'create', 'update', 'delete', 'restore', 'publish', 'export']),
            ...self::module('crops', 'cây trồng', ['view', 'create', 'update', 'delete']),
            ...self::module('crop_solutions', 'giải pháp cây trồng', ['view', 'create', 'update', 'delete', 'publish']),
            ...self::module('services', 'dịch vụ', ['view', 'create', 'update', 'delete', 'restore', 'publish']),
            ...self::module('transportation', 'vận chuyển', ['view', 'create', 'update', 'delete', 'publish']),
            ...self::module('transport_requests', 'yêu cầu vận chuyển', ['view', 'update', 'export']),
            ...self::module('warehouses', 'kho bãi', ['view', 'create', 'update', 'delete', 'publish']),
            ...self::module('warehouse_requests', 'yêu cầu thuê kho', ['view', 'update', 'export']),
            ...self::module('leads', 'lead', ['view', 'update', 'export']),
            ...self::module('pages', 'trang', ['view', 'create', 'update', 'delete', 'restore', 'publish']),
            ...self::module('posts', 'bài viết', ['view', 'create', 'update', 'delete', 'restore', 'publish', 'export']),
            ...self::module('media', 'media', ['view', 'create', 'update', 'delete', 'restore']),
        ];
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
     * @param  list<string>  $actions
     * @return list<array{key: string, module: string, action: string, name: string}>
     */
    private static function module(string $module, string $label, array $actions): array
    {
        $actionLabels = [
            'view' => 'Xem',
            'create' => 'Tạo',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'restore' => 'Khôi phục',
            'publish' => 'Xuất bản',
            'export' => 'Xuất dữ liệu',
            'manage_settings' => 'Quản lý cài đặt',
        ];

        return array_map(
            static fn (string $action): array => self::permission(
                $module,
                $action,
                $actionLabels[$action].' '.$label,
            ),
            $actions,
        );
    }

    /**
     * @return array{key: string, module: string, action: string, name: string}
     */
    private static function permission(string $module, string $action, string $name): array
    {
        return [
            'key' => $module.'.'.$action,
            'module' => $module,
            'action' => $action,
            'name' => $name,
        ];
    }
}
