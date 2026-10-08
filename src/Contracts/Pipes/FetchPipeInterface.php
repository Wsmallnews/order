<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 取数阶段管道
 */
interface FetchPipeInterface
{
    public function fetch(OrderRocket $rocket, Closure $next): OrderRocket;
}
