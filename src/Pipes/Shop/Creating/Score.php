<?php

namespace Wsmallnews\Order\Pipes\Shop\Creating;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CreatingPipeInterface;
use Wsmallnews\Order\Exceptions\OrderCreateException;
use Wsmallnews\Order\OrderRocket;

class Score implements CreatingPipeInterface
{
    public function creating(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $score_amount = intval($rocket->getPayload('score_amount', 0));

        $user = $rocket->getRadar('user');
        // $user = User::findOrFail($user['id']);          // @sn todo 重新查询用户

        // 如果需要支付积分
        if ($score_amount) {
            // 判断个人积分是否充足
            if ($user->score < $score_amount) {
                // 积分不足
                throw (new OrderCreateException('用户积分不足'))->setRocket($rocket);
            }
        }

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再执行=============================

        // 支付积分
        if ($score_amount) {
            $order = $rocket->getPayload('order', null);
            // $payManager = $rocket->getPayManager(function () use ($order, $user) {
            //     return new PayManager(new OrderAdapter($order), $user);
            // });
            // $payManager->pay('score', $score_amount);           // 支付积分
        }

        return $response;
    }
}
