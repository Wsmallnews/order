<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 检查项目的 interface
 */
interface CheckPipeInterface
{
    /**
     * 商品获取
     *
     * @param OrderRocket $rocket
     * @param Closure $next
     * @return OrderRocket
     */
    public function check(OrderRocket $rocket, Closure $next): OrderRocket;
}