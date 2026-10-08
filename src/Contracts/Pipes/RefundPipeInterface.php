<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 退款补偿阶段管道
 *
 * rocket 上下文：order / pay_record / refund 实体 + refund_items 明细
 * （item 级退款结构，售后扩展包将来填充：[{order_item_id, refund_fee}]）
 */
interface RefundPipeInterface
{
    public function refund(OrderRocket $rocket, Closure $next): OrderRocket;
}
