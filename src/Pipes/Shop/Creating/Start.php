<?php

namespace Wsmallnews\Order\Pipes\Shop\Creating;

use Closure;
use Wsmallnews\Order\{
    Contracts\Pipes\CreatingPipeInterface,
    Exceptions\OrderCreateException,
    Models\Order as OrderModel,
    OrderRocket,
};
use Wsmallnews\Support\Exceptions\SupportException;

class Start implements CreatingPipeInterface
{

    public function creating(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $user = $rocket->getRadar('user');

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        $order = $rocket->getRadar('order', null);
        $order = OrderModel::find($order->id);

        $rocket->setRadar('order', $order);
        // $orderOperManager = new OrderOperManager($order);
        // $order = $orderOperManager->created();      // 订单创建完成

        return $response;
    }

}
