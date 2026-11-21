<?php

use Wsmallnews\Order\Models\Order;
use Wsmallnews\Order\Models\OrderAction;
use Wsmallnews\Order\Models\OrderItem;

// config for Wsmallnews/Order
return [
    /**
     * Custom models
     */
    'models' => [
        'order' => Order::class,
        'order_item' => OrderItem::class,
        'order_action' => OrderAction::class,
    ],

    /**
     * Panel register
     *
     * global_default 共享默认（非 FQCN 的 string key）会合并到所有条目：
     *   - navigation_group: 所有页面/资源的默认导航组
     *
     * 条目格式：
    /**
     * 本包组件消费 scope：引入必须声明 scopeable（默认 [pluginId, 0]，子 scope 用 scope_suffix）。
     *
     * 资源清单（默认解开即注册；内存注入 resources([...]) 对同类覆盖）。
     */

    /**
     * 文件基础目录，会自动拼接当前年月日 (仅用于 filament 默认上传组件 (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/order/',
];
