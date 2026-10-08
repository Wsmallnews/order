<?php

namespace Wsmallnews\Order\Pipes\Common\Creating;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CreatingPipeInterface;
use Wsmallnews\Order\Models\Order as OrderModel;
use Wsmallnews\Order\OrderRocket;

/**
 * 创建阶段通用管道（收尾）：重载订单实体供后续消费（事件/返回值）
 */
class Start implements CreatingPipeInterface
{
    public function creating(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $response = $next($rocket);

        $order = $rocket->getRadar(OrderRocket::RADAR_ORDER);
        $rocket->setRadar(OrderRocket::RADAR_ORDER, OrderModel::findOrFail($order->id));

        return $response;
    }
}
