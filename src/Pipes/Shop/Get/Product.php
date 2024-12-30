<?php

namespace Wsmallnews\Order\Pipes\Shop\Get;

use Closure;
use Wsmallnews\Order\{
    Contracts\Pipes\GetPipeInterface,
    Exceptions\OrderCreateException,
    OrderRocket,
};
use Wsmallnews\Product\Models\Product as ProductModel;
use Wsmallnews\Product\Enums\SkuPriceStatus;
use Wsmallnews\Support\Exceptions\SupportException;

class Product implements GetPipeInterface
{

    public function get(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $scope_type = $rocket->getParam('scope_type', 'shop');
        $scope_id = $rocket->getParam('scope_id', 0);

        $products = $rocket->getRelateItems();

        foreach ($products as $key => &$buyInfo) {
            $product = ProductModel::with(['skuPrices', 'attributes' => function ($query) {
                $query->with(['children.attribute_repository', 'attribute_repository']);
            }])->show();
            // }, 'package_relates.children'])->show();

            // 这里多个店铺不能共用一个商品
            // $product->scopeInfo($scope_type, $scope_id);

            // @sn todo 这里要考虑如何写 transformer
            $product = $product->findOrFail($buyInfo['product_id']);

                // ->transform('buy', ['sku_prices', 'attributes.children'], [
                //     'scope_type' => $scope_type,
                //     'store_id' => $store_id
                // ]);

            $buyInfo['product'] = $product;
        }

        $rocket->setRelateItems($products);


        $response = $next($rocket);


        // ==============================后置 所有中间件走完之后，再计算=============================
        // 获取关联列表
        $relateItems = $rocket->getRelateItems();

        foreach ($relateItems as $key => &$buyInfo) {
            $product_sku_price_id = $buyInfo['product_sku_price_id'] ?? 0;

            $product = $buyInfo['product'];
            $skuPrices = $product->skuPrices;

            foreach ($skuPrices as $key => $skuPrice) {
                if ($skuPrice->id == $product_sku_price_id && $skuPrice->status == SkuPriceStatus::Up) {
                    $buyInfo['current_sku_price'] = $skuPrice;      // 当前购买规格
                    break;
                }
            }

            if (!isset($buyInfo['current_sku_price']) || !$buyInfo['current_sku_price']) {
                throw (new OrderCreateException('商品规格不存在'))->setRocket($rocket);
            }
        }

        return $response;
    }

}
