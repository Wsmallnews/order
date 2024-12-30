<?php

namespace Wsmallnews\Order\Pipes\Shop\Check;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CheckPipeInterface;
use Wsmallnews\Order\Exceptions\OrderCreateException;
use Wsmallnews\Order\OrderRocket;

class Product implements CheckPipeInterface
{
    public function check(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $products = $rocket->getRelateItems();

        if (! count($products)) {
            throw (new OrderCreateException('请选择要购买的产品'))->setRocket($rocket);
        }

        foreach ($products as $key => &$buyInfo) {
            $product = $buyInfo['product'];
            $currentSkuPrice = $buyInfo['current_sku_price'];

            // 最少购买一件
            $buyInfo['relate_num'] = $buyInfo['product_num'] ?? 1;
            $buyInfo['relate_num'] = intval($buyInfo['relate_num']) < 1 ? 1 : intval($buyInfo['relate_num']);
            $buyInfo['relate_stock_num'] = $product['sku_type'] == 'unit' ? ($buyInfo['relate_num'] * ($currentSkuPrice['convert_num'] ?: 1)) : $buyInfo['relate_num'];    // 基础库存数量

            // 当前购买规格
            if ($product['stock_type'] == 'stock' && $currentSkuPrice['stock'] < $buyInfo['relate_num']) {
                // 商品开启了库存，并且库存小于用户购买数量
                throw (new OrderCreateException('商品库存不足'))->setRocket($rocket);
            }
        }

        // 重设商品
        $rocket->setRelateItems($products);

        $response = $next($rocket);

        return $response;
    }
}
