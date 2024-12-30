<?php

namespace Wsmallnews\Order\Pipes\Shop\Creating;

use Closure;
use Wsmallnews\Order\{
    Contracts\Pipes\CreatingPipeInterface,
    Exceptions\OrderCreateException,
    OrderRocket,
};
use Wsmallnews\Support\Exceptions\SupportException;

class Money implements CreatingPipeInterface
{

    public function creating(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $money = floatval($rocket->getParam('money', 0));         // 用户选择余额抵扣金额

        $user = $rocket->getRadar('user');
        // $user = User::findOrFail($user['id']);          // @sn todo 重新查询用户

        if ($money) {
            // 判断个人余额是否充足
            if ($user->money < $money) {
                // 余额不足
                throw (new OrderCreateException('用户余额不足'))->setRocket($rocket);
            }
        }

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再执行=============================

        // 支付余额
        if ($money) {
            $order = $rocket->getPayload('order', null);
            // $payManager = $rocket->getPayManager(function () use ($order, $user) {
            //     return new PayManager(new OrderAdapter($order), $user);
            // });
            // $payManager->pay('money', $money);           // 支付余额
        }

        return $response;
    }

}
