<?php

use Wsmallnews\Order\Models\Order;
use Wsmallnews\Order\Models\OrderAction;
use Wsmallnews\Order\Models\OrderItem;
use Wsmallnews\Pay\Models\PayRecord;

// config for Wsmallnews/Order
return [
    /**
     * Scopeable 实例声明（单一事实源）
     *
     * main 为默认实例（必须存在）；未显式引用实例键的组件均使用 main，
     * 只有需要差异分区的实例才在此声明。
     */
    'scopeables' => [
        'main' => [
            'scope_type' => 'sn-order',
            'scope_id' => 0,
        ],
    ],

    /**
     * Custom models
     *
     * pay_record：支付记录模型（wsmallnews/pay 包提供）。默认指向 pay 的 PayRecord，
     * 未安装 pay 包时类不存在自动失效（支付记录相关方法调用会抛异常）——
     * 订单主流程（创建/查询）不依赖它。
     */
    'models' => [
        'order' => Order::class,
        'order_item' => OrderItem::class,
        'order_action' => OrderAction::class,
        'pay_record' => PayRecord::class,
    ],

    /**
     * Panel register
     *
     * global_default 共享默认（非 FQCN 的 string key）会合并到所有条目：
     *   - navigation_group: 所有页面/资源的默认导航组
     *
     * 条目格式：
     *   - 简单 FQCN：ClassName::class（仅合并共享默认）
     *   - 键值对：ClassName::class => ['key' => 'value']（合并共享默认 + 自定义覆盖）
     *   - 配置项键名使用 snake_case（如 navigation_label、navigation_icon）
     */
    'panel_register' => [
        'global_default' => [
            'navigation_group' => 'sn-order::order.global_default.navigation_group',
        ],
        'resources' => [],
        'pages' => [],
    ],

    /**
     * 文件基础目录，会自动拼接当前年月日 (仅用于 filament 默认上传组件 (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/order/',
];
