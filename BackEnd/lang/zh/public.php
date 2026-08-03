<?php

return [
    'skip_to_content' => '跳到主要内容',
    'navigation_label' => '主导航',
    'language_label' => '语言',
    'navigation' => [
        'home' => '首页', 'products' => '产品', 'services' => '服务', 'transportation' => '运输',
        'warehouses' => '仓储', 'news' => '资讯', 'contact' => '联系我们',
        'privacy' => '隐私政策', 'terms' => '使用条款',
    ],
    'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
    'header' => [
        'utility' => '服务农业、运输与仓储需求',
        'brand_caption' => '农业与物流',
        'scope' => '肥料产品 · 运输 · 仓储',
        'toggle_menu' => '打开或关闭菜单',
    ],
    'actions' => [
        'request_quote' => '申请报价', 'explore_catalog' => '查看目录', 'learn_more' => '了解更多',
        'contact_for_catalog' => '联系获取信息', 'request_information' => '申请咨询',
        'call_now' => '立即致电', 'send_email' => '发送邮件', 'view_details' => '查看详情',
    ],
    'pages' => [
        'home' => [
            'title' => '首页', 'eyebrow' => '面向企业与种植者的解决方案',
            'heading' => '连接农业产品与物流服务',
            'intro' => '了解 Hồng Vân 的肥料产品、运输及仓储服务。所有需求均通过咨询和直接报价方式受理。',
        ],
        'privacy' => ['title' => '隐私政策', 'placeholder' => '公司正在更新此政策内容。'],
        'terms' => ['title' => '使用条款', 'placeholder' => '公司正在更新这些条款。'],
    ],
    'home' => [
        'categories' => [
            'label' => '内容分类', 'title' => '主要分类', 'products' => '肥料产品',
            'solutions' => '作物解决方案', 'transportation' => '运输服务',
            'warehouses' => '仓储服务', 'news' => '资讯与知识',
        ],
        'promo' => [
            'transportation_kicker' => '企业服务', 'transportation' => '运输解决方案',
            'warehouse_kicker' => '运营能力', 'warehouse' => '仓储解决方案',
        ],
        'benefits' => [
            'label' => '核心优势',
            'catalog' => ['title' => '清晰的目录', 'description' => '产品信息经过整理，便于查找和比较。'],
            'support' => ['title' => '按需咨询', 'description' => '了解实际需求后再提供合适的解决方案。'],
            'quote' => ['title' => '直接报价', 'description' => '不提供在线销售；所有交易均通过咨询确认。'],
        ],
        'products' => [
            'eyebrow' => '产品目录', 'title' => '探索产品类别',
            'description' => '目录结构已准备好展示管理系统中已发布的产品数据。',
            'groups' => [
                'nutrition' => ['title' => '作物营养', 'description' => '满足不同生长阶段营养需求的产品类别。'],
                'soil' => ['title' => '土壤养护', 'description' => '关注土壤和种植条件的解决方案。'],
                'crop' => ['title' => '按作物分类', 'description' => '按不同作物类别的需求了解产品。'],
                'specialty' => ['title' => '专用产品', 'description' => '根据具体使用条件提供咨询的产品类别。'],
            ],
            'disclaimer' => '以上为界面导航分类。实际产品仅在 CMS 审核并发布后显示。',
        ],
        'services' => [
            'eyebrow' => '服务能力', 'title' => '一个窗口满足多种需求',
            'description' => '各服务区域已准备好接收管理系统中经过验证的数据。',
            'items' => [
                'advisory' => ['title' => '方案咨询', 'description' => '接收需求信息并引导选择合适的产品类别。'],
                'transportation' => ['title' => '运输服务', 'description' => '能力、路线及车辆信息将在内容审核后显示。'],
                'warehouses' => ['title' => '仓储服务', 'description' => '地点及仓库能力信息将在内容审核后显示。'],
            ],
        ],
        'news' => [
            'eyebrow' => '资讯与知识', 'title' => 'Hồng Vân 最新动态',
            'description' => '文章区域将自动接收 CMS 中已发布的内容。',
            'pending_title' => '内容正在更新',
            'pending_description' => '文章仅在编辑审核并发布后显示。',
        ],
        'contact' => [
            'eyebrow' => '联系 Hồng Vân', 'title' => '需要咨询或报价吗？',
            'description' => '请直接联系我们，说明产品、运输或仓储需求。',
            'pending' => '联系信息正在管理系统中更新。',
        ],
    ],
    'templates' => [
        'empty_title' => '暂无内容', 'empty_description' => '内容将在审核并发布后显示。',
        'detail_eyebrow' => '详细信息', 'quote_title' => '需要符合您需求的信息吗？',
        'quote_description' => '提交需求以获得咨询和直接报价。',
        'contact_description' => '联系信息由管理系统统一维护。',
        'contact_information' => '联系信息', 'contact_form' => '提交需求',
        'contact_form_pending' => '表单将在公共页面集成阶段连接到现有需求受理流程。',
    ],
    'legal_notice' => '请稍后返回查看正式内容。',
    'legal_identity' => ['name' => '法定名称', 'tax_code' => '税号'],
    'breadcrumbs' => '面包屑导航',
    'footer' => [
        'description' => '展示肥料产品、运输及仓储服务的企业网站。',
        'explore' => '探索', 'policies' => '政策', 'contact' => '联系我们',
        'contact_pending' => '联系信息正在更新。', 'copyright' => '版权归 :company 所有。',
    ],
    'errors' => [
        '404' => ['title' => '找不到页面', 'message' => '您请求的页面不存在或已被移动。'],
        '500' => ['title' => '服务暂时不可用', 'message' => '发生临时错误，请稍后再试。'],
        'back_home' => '返回首页',
    ],
];
