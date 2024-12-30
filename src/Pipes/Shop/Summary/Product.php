<?php

namespace Wsmallnews\Order\Pipes\Shop\Summary;

use Closure;
use Wsmallnews\Order\{
    Contracts\Pipes\SummaryPipeInterface,
    Exceptions\OrderCreateException,
    OrderRocket,
};
use Wsmallnews\Support\Exceptions\SupportException;

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
                'original_product_price' => $currentSkuPrice['original_price'],
                'product_price' => $currentSkuPrice['price'],
                'product_attributes' => $currentSkuPrice['product_sku_text'],
                'product_sku_type' => $product['sku_type'],
            ]);
        }

        // 重设商品
        $rocket->setRelateItems($products);


        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        // 设置 payloads
        $products = $rocket->getRelateItems();

        $formatRelateItems = [];
        foreach ($products as $key => $buyInfo) {
            $current = [
                'relate_type' => $buyInfo['relate_type'],
                'relate_id' => $buyInfo['relate_id'],
                'relate_title' => $buyInfo['relate_title'],
                'relate_subtitle' => $buyInfo['relate_subtitle'],
                'relate_attributes' => $buyInfo['relate_attributes'],
                'relate_image' => $buyInfo['relate_image'],
                'relate_original_price' => $buyInfo['relate_original_price'],
                'relate_price' => $buyInfo['relate_price'],
                'relate_original_amount' => $buyInfo['relate_original_amount'],
                'relate_amount' => $buyInfo['relate_amount'],
                'relate_stock_num' => $buyInfo['relate_stock_num'],
                'relate_num' => $buyInfo['relate_num'],
                'relate_weight' => $buyInfo['relate_weight'],
                'stock_type' => $buyInfo['stock_type'],
                'stock_unit' => $buyInfo['stock_unit'],
            ];

            $formatRelateItems[] = $current;
        }

        $rocket->mergePayloads([
            'relate_items' => $formatRelateItems,
        ]);
        
        return $response;
    }

}
