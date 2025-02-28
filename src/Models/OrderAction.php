<?php

namespace Wsmallnews\Order\Models;

use Wsmallnews\Order\Enums;
use Wsmallnews\Support\Casts\MoneyCast;
use Wsmallnews\Support\Models\SupportModel;

class OrderAction extends SupportModel
{
    protected $table = 'sn_order_actions';

    protected $guarded = [];

    protected $casts = [
        // json
        'order_status_fields' => 'array',
        'item_status_fields' => 'array',
        'options' => 'array',

        // Enum
        'order_status' => Enums\Order\Status::class,
    ];




    public static function add(Order $order, ?OrderItem $orderItem = null, $message = '', $options = [])
    {
        // $operator = Operator::get();                // 自动获取操作人
        $operator = ['type' => 'user', 'id' => 1];

        $action = new self();

        $action->scope_type = $order->scope_type;
        $action->scope_id = $order->scope_id;
        $action->order_id = $order->id;
        $action->order_type = $order->type;
        $action->order_item_id = $orderItem?->id ?? 0;
        $action->buyer_type = $order->buyer_type;
        $action->buyer_id = $order->buyer_id;
        $action->operator_type = $operator['type'];
        $action->operator_id = $operator['id'];
        $action->order_status = $order->status;                                         // 订单状态
        $action->order_status_fields = $order->getStatusFields($order);                 // 订单其余状态集合
        $action->item_status_fields = $orderItem?->getStatusFields($orderItem);         // 订单Item状态集合
        $action->message = $message;
        $action->options = array_merge([
            'operator' => $operator
        ], $options);

        $action->save();

        return $action;
    }

}
