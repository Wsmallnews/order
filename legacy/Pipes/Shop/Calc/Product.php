<?php

namespace Wsmallnews\Order\Pipes\Shop\Calc;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CalcPipeInterface;
use Wsmallnews\Order\OrderRocket;

class Product implements CalcPipeInterface
{
    public function calc(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $products = $rocket->getRelateItems();

        // 计算商品金额
        foreach ($products as $key => &$buyInfo) {
            $product = $buyInfo['product'];
            $currentVariant = $buyInfo['current_variant'];

            // 当前商品原始总价
            $current_original_product_amount = sn_currency()->multiply($product['original_price'], $buyInfo['relate_num']);
            $rocket->radarAdditionAmount('relate_original_amount', $current_original_product_amount);       // 累计商品原始总价

            $current_product_amount = sn_currency()->multiply($currentVariant['price'], $buyInfo['relate_num']);
            $rocket->radarAdditionAmount('relate_amount', $current_product_amount);       // 累计商品总价

            // 商品总总量
            $current_weight = bcmul((string) $currentVariant['weight'], (string) $buyInfo['relate_num'], 2);

            // 单价，不乘商品数量
            $buyInfo['original_product_price'] = $product['original_price'];                                // 当前商品原始单价 不乘 购买数量
            $buyInfo['product_price'] = $currentVariant['price'];                                        // 当前商品单价 不乘 商品数量
            // 总价，乘以商品数量
            $buyInfo['original_product_amount'] = $current_original_product_amount;                         // 当前商品原始总金额（价格 * 数量）
            $buyInfo['product_amount'] = $current_product_amount;                                           // 当前商品总金额（价格 * 数量）

            // 商品相关的价格
            $buyInfo['relate_original_price'] = sn_currency()->add($buyInfo['relate_original_price'], $product['original_price']);      // 累计商品原始单价的总和
            $buyInfo['relate_price'] = sn_currency()->add($buyInfo['relate_price'], $currentVariant['price']);                             // 累计商品现在单价的总和
            $buyInfo['relate_original_amount'] = sn_currency()->add($buyInfo['relate_original_amount'], $current_original_product_amount);      // 当前relate原始总金额（原价
            $buyInfo['relate_amount'] = sn_currency()->add($buyInfo['relate_amount'], $current_product_amount);                    // 当前relate总金额（价格 * 数量）

            $buyInfo['relate_weight'] = $current_weight;        // 当前商品总重量
            $buyInfo['relate_sn'] = $currentVariant->product_sn;        // 当前商品货号

            // 将费用存到字段集合中，方便后续存库
            $buyInfo['original_amount_fields']['original_product_amount'] = $current_original_product_amount;
            $buyInfo['amount_fields']['product_amount'] = $current_product_amount;

            // 将完整的费用存到字段集合中，方便后续展示
            $buyInfo['original_amount_fields_info']['original_product_amount'] = [
                'field_name' => 'original_product_amount',
                'field_type' => 'amount',
                'text' => '商品原始总价',
                'desc' => '',
                'value' => $current_original_product_amount,
                'order_column' => 1,
                'high_light' => 0,
            ];
            $buyInfo['amount_fields_info']['product_amount'] = [
                'field_name' => 'product_amount',
                'field_type' => 'amount',
                'text' => '商品总价',
                'desc' => '共' . $buyInfo['relate_num'] . '件商品',
                'value' => $current_product_amount,
                'order_column' => 1,
                'high_light' => 1,
            ];
        }

        $rocket->setRelateItems($products);

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        // 将 relate 相关原始金额字段添加到订单 原始金额字段
        $rocket->setRadar('original_amount_fields.relate_original_amount', $rocket->getRadar('relate_original_amount'));            // 商品相关原始总价 (商品价，属性价等)
        // 将 relate 相关金额字段添加到订单 金额字段
        $rocket->setRadar('amount_fields.relate_amount', $rocket->getRadar('relate_amount'));                                       // 商品相关总价 (商品价，属性价等)

        // 将完整的费用存到字段集合中，方便后续展示
        $rocket->setRadar('original_amount_fields_info.relate_original_amount', [
            'field_name' => 'relate_original_amount',
            'field_type' => 'amount',
            'text' => '商品原始总价',
            'desc' => '',
            'value' => $rocket->getRadar('relate_original_amount'),
            'order_column' => 1,
            'high_light' => 0,
        ]);
        $rocket->setRadar('amount_fields_info.relate_amount', [
            'field_name' => 'relate_amount',
            'field_type' => 'amount',
            'text' => '商品总价',
            'desc' => '共' . array_sum(array_column($products, 'relate_num')) . '件商品',
            'value' => $rocket->getRadar('relate_amount'),
            'order_column' => 1,
            'high_light' => 1,
        ]);

        return $response;
    }
}
