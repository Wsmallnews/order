<?php

namespace Wsmallnews\Order\Pipes\Shop\Check;

use Closure;
use Wsmallnews\Order\{
    Contracts\Pipes\CheckPipeInterface,
    Exceptions\OrderCreateException,
    OrderRocket,
};
use Wsmallnews\Product\ProductAttributeManager;
use Wsmallnews\Support\Exceptions\SupportException;

class ProductAttribute implements CheckPipeInterface
{

    public function check(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $products = $rocket->getRelateItems();

        $productAttributeManager = new ProductAttributeManager();

        foreach ($products as $key => &$buyInfo) {
            $product = $buyInfo['product'];
            $buyInfo['product_attributes'] = (isset($buyInfo['product_attributes']) && $buyInfo['product_attributes']) ? $buyInfo['product_attributes'] : [];
            $buyInfoProductAttributes = array_column($buyInfo['product_attributes'], null, 'id');
            $productAttributes = $product['attributes'] ?? [];

            // 检测用户要购买的属性是否正常
            try {
                $newProductAttributes = $productAttributeManager->buyCheckAndGet($productAttributes, $buyInfoProductAttributes);
                $formatAttributes = $productAttributeManager->formatAttribute($newProductAttributes);
            } catch (\Exception $e) {
                throw (new OrderCreateException($e->getMessage()))->setRocket($rocket);
            }

            // 将属性放到 buyInfo 中
            $buyInfo['buy_attributes'] = $newProductAttributes ?? [];
            $buyInfo['format_product_attributes'] = $formatAttributes ?? null;
        }

        $rocket->setRelateItems($products);

        return $next($rocket);
    }

}
