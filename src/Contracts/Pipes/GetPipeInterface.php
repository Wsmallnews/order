<?php

namespace Wsmallnews\Order\Contracts\Pipes;

use Closure;
use Wsmallnews\Order\OrderRocket;

/**
 * 获取项目的 interface
 */
interface GetPipeInterface
{
    /**
     * 项目获取
     *
     * @param OrderRocket $rocket
     * @param Closure $next
     * @return OrderRocket
     */
    public function get(OrderRocket $rocket, Closure $next): OrderRocket;
}