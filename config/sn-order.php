<?php

// config for Wsmallnews/Order
return [

    /**
     * order table name
     */
    'order_table_name' => 'sn_orders',

    /**
     * order item table name
     */
    'order_item_table_name' => 'sn_order_items',

    /*
     * Model name for order record.
     */
    'order_model' => \Wsmallnews\Order\Models\Order::class,
];
