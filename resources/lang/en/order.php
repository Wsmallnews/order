<?php

// translations for Wsmallnews/Order
return [
    'global_default' => [
        'navigation_group' => 'Orders',
    ],

    'confirm' => [
        'title' => 'Confirm Order',
        'items' => 'Order Items',
        'items_empty' => 'No items in the order',
        'pay_fee' => 'Total',
        'submit' => 'Place Order',
    ],

    'order_status' => [
        'closed' => 'Closed',
        'unpaid' => 'Awaiting Payment',
        'paid' => 'Paid',
        'applying_refund' => 'Refund Requested',
        'waiting_send' => 'Awaiting Shipment',
        'waiting_get' => 'Awaiting Receipt',
        'geted' => 'Received',
        'completed' => 'Completed',
        'desc' => [
            'closed' => 'Buyer did not pay within the time limit.',
            'unpaid' => 'Waiting for the buyer to pay.',
            'paid' => 'Order has been paid.',
            'applying_refund' => 'Waiting for the seller to process the refund request.',
            'waiting_send' => 'Waiting for the seller to ship.',
            'waiting_get' => 'Waiting for the buyer to receive.',
            'geted' => 'Received.',
            'completed' => 'Transaction completed.',
        ],
    ],

    'order_pay_status' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
    ],

    'order_delivery_status' => [
        'waiting_send' => 'Not Shipped',
        'waiting_get' => 'Not Received',
        'geted' => 'Received',
    ],

    'order_refund_status' => [
        'unrefund' => 'No Refund',
        'hasrefund' => 'Refund Exists',
        'refunded' => 'Refunded',
    ],

    'item_pay_status' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
    ],

    'item_delivery_status' => [
        'waiting_send' => 'Not Shipped',
        'waiting_get' => 'Not Received',
        'geted' => 'Received',
    ],

    'item_refund_status' => [
        'unrefund' => 'No Refund',
        'refunded' => 'Refunded',
    ],

    'item_aftersale_status' => [
        'refuse' => 'After-sale Rejected',
        'unafter' => 'Not Requested',
        'ing' => 'After-sale Requested',
        'completed' => 'Completed',
    ],

    'item_evaluate_status' => [
        'unevaluate' => 'Not Reviewed',
        'evaluated' => 'Reviewed',
    ],
];
