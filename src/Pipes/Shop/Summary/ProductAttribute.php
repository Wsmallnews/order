<?php

namespace Wsmallnews\Order\Pipes\Shop\Summary;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\SummaryPipeInterface;
use Wsmallnews\Order\OrderRocket;

class ProductAttribute implements SummaryPipeInterface
{
    public function summary(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $products = $rocket->getRelateItems();

        foreach ($products as $key => &$buyInfo) {
            $product = $buyInfo['product'];
            $currentVariant = $buyInfo['current_variant'];

            $currentProductAttributeTexts = $buyInfo['product_attribute_texts'];

            // 补充信息
            $buyInfo['relate_attributes'] = array_merge(($buyInfo['relate_attributes'] ?? []), $currentProductAttributeTexts);

            $buyInfo['relate_options'] = array_merge(($buyInfo['relate_options'] ?? []), [
                // relate 相关的附加字段
                'original_product_attribute_price' => $buyInfo['original_product_attribute_price'],
                'product_attribute_price' => $buyInfo['product_attribute_price'],
                'product_attribute_texts' => $currentProductAttributeTexts,
                'format_product_attributes' => $buyInfo['format_product_attributes'],
            ]);
        }

        // 重设商品
        $rocket->setRelateItems($products);

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        // 没有额外需要追加到 payloads 中的信息

        return $response;
    }
}
