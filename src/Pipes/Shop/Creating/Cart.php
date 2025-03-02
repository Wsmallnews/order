<?php

namespace Wsmallnews\Order\Pipes\Shop\Creating;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CreatingPipeInterface;
use Wsmallnews\Order\OrderRocket;

class Cart implements CreatingPipeInterface
{
    public function creating(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $buyer = $rocket->getBuyer();
        $from = $rocket->getParam('from', 'detail');         // cart=从购物车下单， detail=从商品详情下单
        $scope_id = $rocket->getParam('scope_id', 0);
        $scope_type = $rocket->getParam('scope_type', 'default');

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        // $relates
        if ($from == 'cart' && $buyer) {
            // $relates = $rocket->getPayload('order_relates', []);
            // $relate_type = $rocket->getPayload('order_relate_type', 'product');

            // if ($relate_type == 'product') {        // 订单关联项是商品时，才可清除购物车(购物车中只存商品)
            //     $cartManager = (new CartManager($user))->setScopeInfo($scope_type, $scope_id);

            //     foreach ($relates as $relate) {
            //         $cartManager->modifyQueryUsing(function ($query) use ($scope_id, $relate) {
            //             $query->scopeProduct($relate['product_id'], $relate['product_sku_price_id'], $relate['product_attributes']);     // 产品相关
            //         });

            //         $cartManager->clear();      // 删除约束下的购物车记录
            //     }
            // }
        }

        return $response;
    }
}
