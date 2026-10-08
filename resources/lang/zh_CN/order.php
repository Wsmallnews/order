<?php

// translations for Wsmallnews/Order
return [
    'global_default' => [
        'navigation_group' => '订单',
    ],

    'confirm' => [
        'title' => '确认订单',
        'items' => '商品清单',
        'items_empty' => '订单中没有商品',
        'pay_fee' => '应付',
        'submit' => '提交订单',
        'remark' => '订单备注',
        'remark_placeholder' => '给商家留言（选填）',
    ],

    'order_status' => [
        'closed' => '交易关闭',
        'unpaid' => '等待支付',
        'paid' => '已支付',
        'applying_refund' => '申请退款中',
        'waiting_send' => '等待发货',
        'waiting_get' => '等待收货',
        'geted' => '已收货',
        'completed' => '交易完成',
        'desc' => [
            'closed' => '买家未在规定时间内付款.',
            'unpaid' => '等待买家付款.',
            'paid' => '订单已支付.',
            'applying_refund' => '等待卖家处理退款申请.',
            'waiting_send' => '等待卖家发货.',
            'waiting_get' => '等待买家收货.',
            'geted' => '已收货.',
            'completed' => '交易完成.',
        ],
    ],

    'order_pay_status' => [
        'unpaid' => '未支付',
        'paid' => '已支付',
    ],

    'order_delivery_status' => [
        'waiting_send' => '未发货',
        'waiting_get' => '未收货',
        'geted' => '已收货',
    ],

    'order_refund_status' => [
        'unrefund' => '未退款',
        'hasrefund' => '存在退款',
        'refunded' => '已退款',
    ],

    'item_pay_status' => [
        'unpaid' => '未支付',
        'paid' => '已支付',
    ],

    'item_delivery_status' => [
        'waiting_send' => '未发货',
        'waiting_get' => '未收货',
        'geted' => '已收货',
    ],

    'item_refund_status' => [
        'unrefund' => '未退款',
        'refunded' => '已退款',
    ],

    'item_aftersale_status' => [
        'refuse' => '售后驳回',
        'unafter' => '未申请',
        'ing' => '申请售后',
        'completed' => '已完成',
    ],

    'item_evaluate_status' => [
        'unevaluate' => '未评价',
        'evaluated' => '已评价',
    ],

    'order_resource' => [
        'model_label' => '订单',
        'plural_model_label' => '订单',
        'navigation_label' => '订单',
    ],

    'order_table' => [
        'search_placeholder' => '搜索订单号 / ID',
        'id' => 'ID',
        'order_sn' => '订单号',
        'buyer' => '买家',
        'order_amount' => '订单金额',
        'pay_status' => '支付状态',
        'status' => '订单状态',
        'delivery_status' => '发货状态',
        'created_at' => '下单时间',
        'updated_at' => '更新时间',
    ],

    'order_form' => [
        'order_info' => '订单信息',
        'address_section' => '收货地址',
    ],
];
