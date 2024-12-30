<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 订单开始创建 interface
 */
interface CreatingPipeInterface
{
    /**
     * 订单开始创建
     *
     * @param OrderRocket $rocket
     * @param Closure $next
     * @return OrderRocket
     */
    public function creating(OrderRocket $rocket, Closure $next): OrderRocket;
}
