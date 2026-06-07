<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 后台侧边栏菜单配置
    |--------------------------------------------------------------------------
    |
    | 结构说明：
    | - groups: 菜单分组数组
    |   - heading: 分组标题（支持翻译 __()）
    |   - collapsible: 是否可折叠
    |   - default_collapsed: 默认折叠状态
    |   - items: 菜单项数组
    |     - label: 显示文本（支持翻译）
    |     - icon: Heroicons 图标名称
    |     - route: 命名路由
    |     - url: 外部链接（与 route 二选一）
    |     - permission: 权限标识（spatie/laravel-permission）
    |     - current: 当前激活路由匹配模式
    |     - children: 子菜单（支持多级嵌套）
    |     - badge: 徽标内容（数字/字符串/闭包）
    |     - target: 链接目标 _blank 等
    |     - order: 排序权重（数值越小越靠前）
    |
    */

    'groups' => [
        [
            'heading' => 'Platform',
            'collapsible' => true,
            'default_collapsed' => false,
            'items' => [
                [
                    'label' => '仪表盘',
                    'icon' => 'home',
                    'route' => 'admin.index',
                    'permission' => 'admin.dashboard.view',
                    'current' => 'admin.index',
                    'order' => 1,
                ],
                [
                    'label' => '文章管理',
                    'icon' => 'document-text',
                    'route' => 'admin.posts.index',
                    'permission' => 'admin.posts.view',
                    'current' => 'admin.posts.*',
                    'order' => 2,
                ],
                [
                    'label' => '上传管理',
                    'icon' => 'arrow-up',
                    'route' => 'admin.upload-policies.index',
                    'permission' => 'admin.upload.view',
                    'current' => 'admin.upload-policies.*',
                    'order' => 3,
                ],
                [
                    'label' => '博客设置',
                    'icon' => 'cog-6-tooth',
                    'route' => 'admin.settings.blog',
                    'permission' => 'admin.settings.view',
                    'current' => 'admin.settings.*',
                    'order' => 4,
                ],
                [
                    'label' => '评论管理',
                    'icon' => 'chat-bubble-bottom-center-text',
                    'route' => 'admin.comments.index',
                    'permission' => 'admin.comments.view',
                    'current' => 'admin.comments.*',
                    'order' => 5,
                ],
                [
                    'label' => '插件管理',
                    'icon' => 'folder-git-2',
                    'route' => 'admin.plugins.index',
                    'permission' => 'admin.plugins.view',
                    'current' => 'admin.plugins.*',
                    'order' => 6,
                ],
                [
                    'label' => '导入导出',
                    'icon' => 'arrows-right-left',
                    'route' => 'admin.import-export.index',
                    'permission' => 'admin.posts.view',
                    'current' => 'admin.import-export.*',
                    'order' => 7,
                ],
            ],
        ],
        [
            'heading' => 'Monitoring',
            'collapsible' => true,
            'default_collapsed' => false,
            'items' => [
                [
                    'label' => 'Horizon',
                    'icon' => 'queue-list',
                    'url' => '/admin/horizon',
                    'permission' => null,
                    'order' => 1,
                ],
                [
                    'label' => 'Pulse',
                    'icon' => 'chart-bar-square',
                    'url' => '/admin/pulse',
                    'permission' => null,
                    'order' => 2,
                ],
                [
                    'label' => 'Telescope',
                    'icon' => 'magnifying-glass',
                    'url' => '/admin/telescope',
                    'permission' => null,
                    'order' => 3,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 移动端配置
    |--------------------------------------------------------------------------
    */
    'mobile' => [
        'breakpoint' => 'lg',
        'collapsible' => 'mobile',
        'show_search' => true,
        'search_placeholder' => '搜索菜单...',
    ],

    /*
    |--------------------------------------------------------------------------
    | 缓存配置
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'key' => 'admin:sidebar:menu',
    ],
];