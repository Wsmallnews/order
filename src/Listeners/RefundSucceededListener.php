<?php

namespace Wsmallnews\Order\Listeners;

use Wsmallnews\Order\Models\Order;
use Wsmallnews\Order\OrderOperate;
use Wsmallnews\Pay\Events\RefundSucceeded;

/**
 * 退款成功 → 订单状态流转 + 场景补偿管道。
 *
 * pay 侧完成退款（同步或回调异步）后触发；订单按 payable morph 反查。
 * 非订单主体（钱包充值等其他 Payable）静默跳过。
 */
class RefundSucceededListener
{
    public function handle(RefundSucceeded $event): void
    {
        $order = $event->payRecord->payable;

        if (! $order instanceof Order) {
            return;
        }

        // 重查防陈旧模型（退款链路可能异步回调，状态已流转）
        $order = Order::query()->findOrFail($order->id);

        (new OrderOperate($order))->refunded($event->payRecord, $event->refund);
    }
}
