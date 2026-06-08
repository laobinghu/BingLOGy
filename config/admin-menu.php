<?php

return [
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
                    'label' => '博客设置',
                    'icon' => 'cog-6-tooth',
                    'route' => 'admin.settings.blog',
                    'permission' => 'admin.settings.view',
                    'current' => 'admin.settings.*',
                    'order' => 2,
                ],
                [
                    'label' => '文章管理',
                    'icon' => 'document-text',
                    'route' => 'admin.posts.index',
                    'permission' => 'admin.posts.view',
                    'current' => 'admin.posts.*',
                    'order' => 3,
                ],
                [
                    'label' => '评论管理',
                    'icon' => 'chat-bubble-bottom-center-text',
                    'route' => 'admin.comments.index',
                    'permission' => 'admin.comments.view',
                    'current' => 'admin.comments.*',
                    'order' => 4,
                ],
                [
                    'label' => '标签管理',
                    'icon' => 'tag',
                    'route' => 'admin.tags.index',
                    'permission' => 'admin.posts.view',
                    'current' => 'admin.tags.*',
                    'order' => 5,
                ],
                [
                    'label' => '上传管理',
                    'icon' => 'arrow-up',
                    'route' => 'admin.upload-policies.index',
                    'permission' => 'admin.upload.view',
                    'current' => 'admin.upload-policies.*',
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
                [
                    'label' => '插件管理',
                    'icon' => 'folder-git-2',
                    'route' => 'admin.plugins.index',
                    'permission' => 'admin.plugins.view',
                    'current' => 'admin.plugins.*',
                    'order' => 8,
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

    'mobile' => [
        'breakpoint' => 'lg',
        'collapsible' => 'mobile',
        'show_search' => true,
        'search_placeholder' => '搜索菜单...',
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'key' => 'admin:sidebar:menu',
    ],
];
