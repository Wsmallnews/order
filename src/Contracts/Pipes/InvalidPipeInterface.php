<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 失效补偿阶段管道（订单超时未支付关单等场景）
 */
interface InvalidPipeInterface
{
    public function invalid(OrderRocket $rocket, Closure $next): OrderRocket;
}
