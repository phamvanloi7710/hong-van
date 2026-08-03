<?php

return [
    'skip_to_content' => '跳到主要内容',
    'navigation_label' => '主导航',
    'language_label' => '语言',
    'navigation' => ['home' => '首页', 'privacy' => '隐私政策', 'terms' => '使用条款'],
    'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
    'pages' => [
        'home' => [
            'title' => '首页',
            'eyebrow' => '企业网站平台',
            'heading' => '公共网站基础已准备就绪',
            'intro' => '内容正在准备中，随后将集成正式的 Laravel Blade 界面。',
            'status_title' => '服务器端渲染内容',
            'status_body' => '即使 JavaScript 不可用，核心文字仍可阅读。',
        ],
        'privacy' => ['title' => '隐私政策', 'placeholder' => '公司正在更新此政策内容。'],
        'terms' => ['title' => '使用条款', 'placeholder' => '公司正在更新这些条款。'],
    ],
    'legal_notice' => '请稍后返回查看正式内容。',
    'legal_identity' => ['name' => '法定名称', 'tax_code' => '税号'],
    'breadcrumbs' => '面包屑导航',
    'footer' => ['copyright' => '版权归 :company 所有。'],
    'errors' => [
        '404' => ['title' => '找不到页面', 'message' => '您请求的页面不存在或已被移动。'],
        '500' => ['title' => '服务暂时不可用', 'message' => '发生临时错误，请稍后再试。'],
        'back_home' => '返回首页',
    ],
];
