<?php

namespace Wsmallnews\Order\Pipes\Shop\Check;

use Closure;
use Wsmallnews\Order\{
    Contracts\Pipes\CheckPipeInterface,
    Exceptions\OrderCreateException,
    OrderRocket,
};
use Wsmallnews\Support\Exceptions\SupportException;

class Start implements CheckPipeInterface
{

    public function check(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $relateItems = $rocket->getRelateItems();
        foreach ($relateItems as $key => &$buyInfo) {
            // 配送类型
            $buyInfo['delivery_type'] = '';
            $buyInfo['delivery_type_text'] = '';
            $buyInfo['delivery_id'] = 0;
        }
        $rocket->setRelateItems($relateItems);

        $response = $next($rocket);

        // 后置动作

        return $response;
    }

}
