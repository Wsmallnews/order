<?php

namespace Wsmallnews\Order\Pipes\Common\Fetch;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\FetchPipeInterface;
use Wsmallnews\Order\OrderRocket;

/**
 * 取数阶段通用管道：relate_items 从入参进入管道上下文
 */
class Start implements FetchPipeInterface
{
    public function fetch(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $rocket->setRelateItems((array) $rocket->getParam('relate_items', []));

        return $next($rocket);
    }
}
