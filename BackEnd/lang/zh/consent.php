<?php

return [
    'banner' => [
        'title' => '您的隐私选择',
        'message' => '只有在您同意后，我们才会使用分析 Cookie。必要 Cookie 会保持启用，以确保网站安全运行。',
        'accept_all' => '全部接受',
        'necessary_only' => '仅必要 Cookie',
        'preferences' => 'Cookie 偏好',
        'save' => '保存选择',
        'revoke' => '撤回同意',
        'policy_link' => '查看 Cookie 政策',
    ],
    'categories' => [
        'necessary' => ['label' => '必要', 'description' => '用于安全、会话和网站核心功能，无法关闭。'],
        'analytics' => ['label' => '分析', 'description' => '用于衡量浏览和内容效果，不发送个人身份信息。'],
        'marketing' => ['label' => '营销', 'description' => '仅在启用此功能后用于已批准的营销服务商。'],
    ],
    'messages' => ['saved' => '已保存 Cookie 选择。', 'revoked' => '已撤回 Cookie 同意。'],
    'validation' => [
        'provider' => '该分析服务商未获批准。',
        'identifier_required' => '启用分析或更换服务商时必须填写跟踪标识。',
        'identifier_invalid' => '跟踪标识与所选服务商的格式不匹配。',
    ],
];
