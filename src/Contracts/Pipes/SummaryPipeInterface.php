<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 总结订单的 interface
 */
interface SummaryPipeInterface
{
    /**
     * 订单下单前总结
     */
    public function summary(OrderRocket $rocket, Closure $next): OrderRocket;
}
