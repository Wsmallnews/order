<?php

namespace Wsmallnews\Order\Pipes\Shop\Get;

use Closure;
use Wsmallnews\Order\{
    Contracts\Pipes\GetPipeInterface,
    Exceptions\OrderCreateException,
    OrderRocket,
};
use Wsmallnews\Support\Exceptions\SupportException;

class Start implements GetPipeInterface
{

    public function get(OrderRocket $rocket, Closure $next): OrderRocket
    {
        // 设置关联列表
        $rocket->setRelateItems($rocket->getParam('relate_items'));

        $response = $next($rocket);

        // 获取关联列表
        $relateItems = $rocket->getRelateItems();

        // 处理一些事情

        $rocket->setRelateItems($relateItems);

        return $response;
    }

}
