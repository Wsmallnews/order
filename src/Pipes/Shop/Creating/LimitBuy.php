<?php

namespace Wsmallnews\Order\Pipes\Shop\Creating;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CreatingPipeInterface;
use Wsmallnews\Order\Exceptions\OrderCreateException;
use Wsmallnews\Order\OrderRocket;

class LimitBuy implements CreatingPipeInterface
{
    public function creating(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $buyer = $rocket->getBuyer();

        $products = $rocket->getRelateItems();

        foreach ($products as $buyInfo) {
            $product = $buyInfo['product'];
            $product_num = $buyInfo['product_num'];
            $product_stock_num = $buyInfo['product_stock_num'] ?? 1;

            $limit_type = $product['limit_type'];
            $limit_num = ($limit_type != 'none' && $product['limit_num'] > 0) ? $product['limit_num'] : 0;

            if ($limit_num) {
                // 查询用户老订单，判断本次下单数量，判断是否超过购买限制, 未支付的或者已完成的都算
                // $buy_stock_num = $buyer->orderItems()->where('product_id', $product['id'])
                //     ->where(function ($query) use ($limit_type) {
                //         if ($limit_type == 'daily') {
                //             // 按天限购
                //             $daily_start = strtotime(date('Y-m-d'));
                //             $daily_end = strtotime(date('Y-m-d', (time() + 86400))) - 1;
                //             $query->where('createtime', 'between', [$daily_start, $daily_end]);
                //             // } else if ($limit_type == 'activity') {
                //             //     $query->where('activity_id', $activity['id']);      // 这个活动下所有的购买记录
                //         } else {
                //             // all，不加任何条件
                //         }

                //         return $query;
                //     })
                //     ->where(function ($query) {
                //         $query->where('ext$.order_status', 'normal')->whereOr(function ($query) {
                //             // 订单关闭，但是是退款完成的也算
                //             $query->where('ext$.order_status', 'closed')->where('ext$.closed_type', 'refund_completed');
                //         });
                //     })->sum('product_stock_num');

                // @sn todo 限购问题
                $buy_stock_num = 0;

                if (($buy_stock_num + $product_stock_num) > $limit_num) {
                    $msg = '该商品' . ($limit_type == 'daily' ? '每日' : ($limit_type == 'activity' ? '活动期间' : '')) . '限购 ' . $limit_num . ' ' . ($product['stock_unit'] ?: '件');

                    if ($buy_stock_num < $limit_num) {
                        $msg .= '，当前还可购买 ' . ($limit_num - $buy_stock_num) . ' ' . ($product['stock_unit'] ?: '件');
                    }

                    throw (new OrderCreateException($msg))->setRocket($rocket);
                }
            }
        }

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再执行=============================

        return $response;
    }
}
