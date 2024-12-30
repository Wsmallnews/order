<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 计算订单的 interface
 */
interface CalcPipeInterface
{

    /**
     * 计算
     *
     * @param OrderRocket $rocket
     * @param Closure $next
     * @return OrderRocket
     */
    public function calc(OrderRocket $rocket, Closure $next): OrderRocket;
}