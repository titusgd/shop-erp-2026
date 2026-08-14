@props([
    'active' => null,
])

@php
    $navItems = [
        [
            'type' => 'link',
            'key' => 'dashboard',
            'label' => '儀表板',
            'route' => 'dashboard',
            'icon' => 'dashboard',
        ],
        [
            'type' => 'group',
            'key' => 'master-data',
            'label' => '基礎資料',
            'children' => [
                [
                    'key' => 'vendors',
                    'label' => '廠商管理',
                    'route' => 'vendors.index',
                    'icon' => 'building-office',
                ],
                [
                    'key' => 'customers',
                    'label' => '客戶管理',
                    'route' => 'customers.index',
                    'icon' => 'users',
                ],
                [
                    'type' => 'subgroup',
                    'key' => 'products',
                    'label' => '商品管理',
                    'icon' => 'cube',
                    'children' => [
                        [
                            'key' => 'products-create',
                            'label' => '新增商品',
                            'route' => 'products.create',
                            'active_routes' => ['products.create'],
                        ],
                        [
                            'key' => 'products-list',
                            'label' => '商品列表',
                            'route' => 'products.index',
                            'active_routes' => ['products.index', 'products.show', 'products.edit'],
                        ],
                    ],
                ],
                [
                    'key' => 'product-categories',
                    'label' => '商品分類',
                    'route' => 'product-categories.index',
                    'icon' => 'folder',
                ],
                [
                    'key' => 'product-units',
                    'label' => '商品單位',
                    'route' => 'product-units.index',
                    'icon' => 'scale',
                ],
                [
                    'key' => 'warehouse-types',
                    'label' => '倉庫類型管理',
                    'route' => 'warehouse-types.index',
                    'icon' => 'tag',
                ],
                [
                    'key' => 'warehouses',
                    'label' => '倉庫管理',
                    'route' => 'warehouses.index',
                    'icon' => 'home-modern',
                ],
                [
                    'key' => 'cities',
                    'label' => '縣市管理',
                    'route' => 'cities.index',
                    'icon' => 'map',
                ],
                [
                    'key' => 'districts',
                    'label' => '地區管理',
                    'route' => 'districts.index',
                    'icon' => 'map-pin',
                ],
            ],
        ],
        [
            'type' => 'group',
            'key' => 'purchasing',
            'label' => '採購管理',
            'children' => [
                [
                    'key' => 'purchase-requisitions',
                    'label' => '請購單',
                    'route' => 'purchase-requisitions.index',
                    'icon' => 'clipboard',
                ],
                [
                    'key' => 'purchase-orders',
                    'label' => '採購單',
                    'route' => 'purchase-orders.index',
                    'icon' => 'clipboard-document-list',
                ],
                [
                    'key' => 'goods-receipts',
                    'label' => '進貨單',
                    'href' => '#',
                    'icon' => 'truck',
                ],
                [
                    'key' => 'purchase-returns',
                    'label' => '採購退貨',
                    'href' => '#',
                    'icon' => 'arrow-uturn-left',
                ],
                [
                    'key' => 'accounts-payable',
                    'label' => '應付帳款',
                    'href' => '#',
                    'icon' => 'banknotes',
                ],
            ],
        ],
        [
            'type' => 'group',
            'key' => 'sales',
            'label' => '銷售管理',
            'children' => [
                [
                    'key' => 'quotations',
                    'label' => '報價單',
                    'href' => '#',
                    'icon' => 'document-text',
                ],
                [
                    'key' => 'sales-orders',
                    'label' => '銷售訂單',
                    'href' => '#',
                    'icon' => 'shopping-cart',
                ],
                [
                    'key' => 'shipments',
                    'label' => '出貨單',
                    'href' => '#',
                    'icon' => 'archive-box',
                ],
                [
                    'key' => 'sales-returns',
                    'label' => '銷售退貨',
                    'href' => '#',
                    'icon' => 'arrow-uturn-left',
                ],
                [
                    'key' => 'accounts-receivable',
                    'label' => '應收帳款',
                    'href' => '#',
                    'icon' => 'currency-dollar',
                ],
            ],
        ],
        [
            'type' => 'group',
            'key' => 'inventory',
            'label' => '庫存管理',
            'children' => [
                [
                    'key' => 'inventory',
                    'label' => '即時庫存',
                    'href' => '#',
                    'icon' => 'squares-2x2',
                ],
                [
                    'key' => 'inventory-movements',
                    'label' => '庫存異動',
                    'href' => '#',
                    'icon' => 'arrows-right-left',
                ],
                [
                    'key' => 'stock-transfers',
                    'label' => '調撥單',
                    'href' => '#',
                    'icon' => 'arrows-up-down',
                ],
                [
                    'key' => 'stocktakes',
                    'label' => '盤點單',
                    'href' => '#',
                    'icon' => 'clipboard-document-check',
                ],
                [
                    'key' => 'inventory-alerts',
                    'label' => '庫存警示',
                    'href' => '#',
                    'icon' => 'exclamation-triangle',
                ],
            ],
        ],
        [
            'type' => 'group',
            'key' => 'reports',
            'label' => '報表中心',
            'children' => [
                [
                    'key' => 'purchase-reports',
                    'label' => '採購報表',
                    'href' => '#',
                    'icon' => 'chart-bar',
                ],
                [
                    'key' => 'sales-reports',
                    'label' => '銷售報表',
                    'href' => '#',
                    'icon' => 'chart-pie',
                ],
                [
                    'key' => 'inventory-reports',
                    'label' => '庫存報表',
                    'href' => '#',
                    'icon' => 'presentation-chart-bar',
                ],
                [
                    'key' => 'gross-profit-analysis',
                    'label' => '毛利分析',
                    'href' => '#',
                    'icon' => 'chart-bar-square',
                ],
            ],
        ],
        [
            'type' => 'group',
            'key' => 'system',
            'label' => '系統管理',
            'children' => [
                [
                    'key' => 'users',
                    'label' => '使用者',
                    'route' => 'users.index',
                    'icon' => 'user-circle',
                ],
                [
                    'key' => 'roles',
                    'label' => '角色權限',
                    'href' => '#',
                    'icon' => 'shield-check',
                ],
                [
                    'key' => 'settings',
                    'label' => '系統設定',
                    'href' => '#',
                    'icon' => 'cog-6-tooth',
                ],
            ],
        ],
    ];

    $renderIcon = function (string $icon): string {
        $attrs = 'class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"';

        return match ($icon) {
            'dashboard' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>',
            'building-office' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>',
            'users' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
            'cube' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>',
            'folder' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>',
            'scale' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-3.75 9h7.5L12 3Zm0 0v18m-7.5-6.75h3.75m7.5 0H19.5M5.25 21h13.5" /></svg>',
            'tag' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>',
            'home-modern' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m0 0-3 1.091M18.75 3.75l-1.5.545M2.25 9l3 1.091m0 0L2.25 12m3-1.909 3.75 1.364" /></svg>',
            'map' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m0-8.25a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v8.25m0 0a.75.75 0 0 1-.75.75h-3.5a.75.75 0 0 1-.75-.75m6.75-9.75L21 4.5v14.25l-3.75-1.5m0-12.75L15 4.5v14.25l2.25.9M3 19.5l3.75-1.5V4.5L3 6v13.5Z" /></svg>',
            'map-pin' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>',
            'clipboard' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>',
            'clipboard-document-list' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.551 0c-.118-.008-.236-.016-.355-.023A2.25 2.25 0 0 0 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>',
            'truck' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.131 1.131 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>',
            'arrow-uturn-left' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>',
            'banknotes' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>',
            'document-text' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
            'shopping-cart' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>',
            'archive-box' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>',
            'currency-dollar' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
            'squares-2x2' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>',
            'arrows-right-left' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>',
            'arrows-up-down' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>',
            'clipboard-document-check' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-3.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" /></svg>',
            'exclamation-triangle' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>',
            'chart-bar' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>',
            'chart-pie' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>',
            'presentation-chart-bar' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" /></svg>',
            'chart-bar-square' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" /></svg>',
            'user-circle' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
            'shield-check' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>',
            'cog-6-tooth' => '<svg '.$attrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
            'chevron-down' => '<svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>',
            default => '',
        };
    };

    $routePrefixes = [
        'users' => 'users.*',
        'vendors' => 'vendors.*',
        'customers' => 'customers.*',
        'products' => 'products.*',
        'product-categories' => 'product-categories.*',
        'product-units' => 'product-units.*',
        'warehouse-types' => 'warehouse-types.*',
        'warehouses' => 'warehouses.*',
        'purchase-requisitions' => 'purchase-requisitions.*',
        'purchase-orders' => 'purchase-orders.*',
        'cities' => 'cities.*',
        'districts' => 'districts.*',
    ];

    $isItemActive = function (array $item) use ($active, $routePrefixes): bool {
        if ($active === ($item['key'] ?? null)) {
            return true;
        }

        if (! empty($item['active_routes']) && request()->routeIs(...$item['active_routes'])) {
            return true;
        }

        if (! empty($item['route']) && request()->routeIs($item['route'])) {
            return true;
        }

        $key = $item['key'] ?? null;

        return $key !== null
            && isset($routePrefixes[$key])
            && request()->routeIs($routePrefixes[$key]);
    };

    $isGroupOpen = function (array $group) use (&$isGroupOpen, $isItemActive, $active, $routePrefixes): bool {
        if ($active === ($group['key'] ?? null)) {
            return true;
        }

        $groupKey = $group['key'] ?? null;

        if ($groupKey !== null && isset($routePrefixes[$groupKey]) && request()->routeIs($routePrefixes[$groupKey])) {
            return true;
        }

        foreach ($group['children'] ?? [] as $child) {
            $childType = $child['type'] ?? 'link';

            if (in_array($childType, ['group', 'subgroup'], true)) {
                if ($isGroupOpen($child)) {
                    return true;
                }

                continue;
            }

            if ($isItemActive($child)) {
                return true;
            }
        }

        return false;
    };

    $resolveHref = function (array $item): string {
        if (! empty($item['route'])) {
            return route($item['route']);
        }

        return $item['href'] ?? '#';
    };

    $renderNavLink = function (array $item, bool $isActive, bool $nested = false) use ($renderIcon, $resolveHref): string {
        $classes = $isActive
            ? 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition bg-teal-50 text-teal-800'
            : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition text-slate-600 hover:bg-slate-50 hover:text-slate-900';

        if ($nested) {
            $classes .= ' pl-11';
        }

        $iconHtml = ! empty($item['icon']) ? $renderIcon($item['icon']) : '';

        return '<a href="'.e($resolveHref($item)).'" class="'.$classes.'">'
            .$iconHtml
            .'<span>'.e($item['label']).'</span>'
            .'</a>';
    };
@endphp

<aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
    <div class="flex h-16 items-center border-b border-slate-200 px-6">
        <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight text-slate-900">
            {{ config('app.name') }}
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @include('components.partials.sidebar-nav-items')
    </nav>
</aside>

<div id="mobile-nav" data-mobile-nav class="fixed inset-0 z-40 hidden lg:hidden" aria-hidden="true">
    <div data-mobile-nav-backdrop class="absolute inset-0 bg-slate-900/40"></div>

    <aside
        data-mobile-nav-panel
        class="absolute inset-y-0 left-0 flex w-72 max-w-[85vw] -translate-x-full flex-col bg-white shadow-xl transition-transform duration-200 ease-out"
    >
        <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight text-slate-900">
                {{ config('app.name') }}
            </a>
            <button
                type="button"
                data-mobile-nav-close
                class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                aria-label="關閉選單"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @include('components.partials.sidebar-nav-items')
        </nav>
    </aside>
</div>
