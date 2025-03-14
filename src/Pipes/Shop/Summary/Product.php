<?php

namespace Wsmallnews\Order\Pipes\Shop\Summary;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\SummaryPipeInterface;
use Wsmallnews\Order\OrderRocket;

class Product implements SummaryPipeInterface
{
    public function summary(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $products = $rocket->getRelateItems();

        foreach ($products as $key => &$buyInfo) {
            $product = $buyInfo['product'];
            $currentSkuPrice = $buyInfo['current_sku_price'];

            // 补充信息
            $buyInfo['relate_type'] = 'product';
            $buyInfo['relate_id'] = $product->id;
            $buyInfo['relate_title'] = $product->title;
            $buyInfo['relate_subtitle'] = $product->subtitle;
            $buyInfo['relate_image'] = $product->image;
            $buyInfo['relate_attributes'] = array_merge(($buyInfo['relate_attributes'] ?? []), $currentSkuPrice['product_sku_text']);
            $buyInfo['stock_unit'] = $product['stock_unit'];
            $buyInfo['stock_type'] = $product['stock_type'];

            $buyInfo['relate_options'] = array_merge(($buyInfo['relate_options'] ?? []), [
                // relate 相关的附加字段
                'product_type' => $product['type'],
                'product_sku_price_id' => $currentSkuPrice['id'],
                'product_sku_text' => $currentSkuPrice['product_sku_text'],
                'original_product_price' => sn_currency()->formatByDecimal($currentSkuPrice['original_price']),
                'product_price' => sn_currency()->formatByDecimal($currentSkuPrice['price']),
                'product_attributes' => $currentSkuPrice['product_sku_text'],
                'product_sku_type' => $product['sku_type'],
            ]);
        }

        // 重设商品
        $rocket->setRelateItems($products);

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        return $response;
    }
}
