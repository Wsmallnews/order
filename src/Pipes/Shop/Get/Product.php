<?php

namespace Wsmallnews\Order\Pipes\Shop\Get;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\GetPipeInterface;
use Wsmallnews\Order\Exceptions\OrderCreateException;
use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Product\Enums\VariantStatus;
use Wsmallnews\Product\Models\Product as ProductModel;

class Product implements GetPipeInterface
{
    public function get(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $scope_type = $rocket->getParam('scope_type', 'default');
        $scope_id = $rocket->getParam('scope_id', 0);

        $products = $rocket->getRelateItems();

        foreach ($products as $key => &$buyInfo) {
            $product = ProductModel::with(['variants', 'attributes' => function ($query) {
                $query->with(['children.attribute_repository', 'attribute_repository']);
            }])->show();
            // }, 'package_relates.children'])->show();

            // 这里多个店铺不能共用一个商品
            $product->scopeable($scope_type, $scope_id);

            // @sn todo 这里要考虑如何写 transformer
            $product = $product->findOrFail($buyInfo['product_id']);

            // ->transform('buy', ['variants', 'attributes.children'], [
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
            $product_variant_id = $buyInfo['product_variant_id'] ?? 0;

            $product = $buyInfo['product'];
            $variants = $product->variants;

            foreach ($variants as $key => $variant) {
                if ($variant->id == $product_variant_id && $variant->status == VariantStatus::Up) {
                    $buyInfo['current_variant'] = $variant;      // 当前购买规格

                    break;
                }
            }

            if (! isset($buyInfo['current_variant']) || ! $buyInfo['current_variant']) {
                throw (new OrderCreateException('商品规格不存在'))->setRocket($rocket);
            }
        }

        return $response;
    }
}
