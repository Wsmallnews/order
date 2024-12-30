<?php

namespace Wsmallnews\Order\Pipes\Shop\Calc;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CalcPipeInterface;
use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Product\ProductAttributeManager;

class ProductAttribute implements CalcPipeInterface
{
    public function calc(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $products = $rocket->getRelateItems();

        $productAttributeManager = new ProductAttributeManager;

        // 计算商品金额
        foreach ($products as $key => &$buyInfo) {
            $relate_num = $buyInfo['relate_num'];
            $buyAttributes = $buyInfo['buy_attributes'];

            // 计算金额和组合属性名称(单份)
            [$current_product_attribute_price, $currentProductAttributeTexts] = $productAttributeManager->buyCalc($buyAttributes);
            // 计算商品数量
            $current_product_attribute_amount = bcmul($current_product_attribute_price, (string) $relate_num, 2);

            // 累计 relate 价格
            $rocket->radarAdditionAmount('relate_original_amount', $current_product_attribute_amount);       // 累计 relate 原始总价
            $rocket->radarAdditionAmount('relate_amount', $current_product_attribute_amount);       // 累计 relate 总价

            // 单价，不乘商品数量
            $buyInfo['original_product_attribute_price'] = $current_product_attribute_price;          // 当前商品属性原始金额 不乘 商品数量
            $buyInfo['product_attribute_price'] = $current_product_attribute_price;                    // 当前商品属性金额 不乘 商品数量
            // 总价，乘以商品数量
            $buyInfo['original_product_attribute_amount'] = $current_product_attribute_amount;                // 当前商品属性原始总金额（价格 * 数量）
            $buyInfo['product_attribute_amount'] = $current_product_attribute_amount;                         // 当前商品属性总金额（价格 * 数量）
            // 属性中文
            $buyInfo['product_attribute_texts'] = $currentProductAttributeTexts;                           // 当前商品属性名称

            // 将属性的的单价累计到 relate 单价金额上
            $buyInfo['relate_original_price'] = bcadd($buyInfo['relate_original_price'], (string) $current_product_attribute_price, 2);    // 累计商品原始单价的总和
            $buyInfo['relate_price'] = bcadd($buyInfo['relate_price'], (string) $current_product_attribute_price, 2);                     // 累计商品现在单价的总和
            // 将属性金额累计到 relate 金额上
            $buyInfo['relate_original_amount'] = bcadd($buyInfo['relate_original_amount'], (string) $current_product_attribute_amount, 2);         // 当前relate原始总金额（原价 * 数量）
            $buyInfo['relate_amount'] = bcadd($buyInfo['relate_amount'], (string) $current_product_attribute_amount, 2);                           // 当前relate总金额（价格 * 数量）

            // 将费用存到字段集合中，方便后续存库
            $buyInfo['original_amount_fields']['original_product_attribute_amount'] = $current_product_attribute_amount;
            $buyInfo['amount_fields']['product_attribute_amount'] = $current_product_attribute_amount;

            // 将完整的费用存到字段集合中，方便后续展示
            $buyInfo['original_amount_fields_info']['original_product_attribute_amount'] = [
                'field_name' => 'original_product_attribute_amount',
                'field_type' => 'amount',
                'text' => '属性原始总价',
                'desc' => '',
                'value' => $current_product_attribute_amount,
                'order_column' => 1,
                'high_light' => 0,
            ];

            // 将完整的费用存到字段集合中，方便后续展示
            $buyInfo['amount_fields_info']['product_attribute_amount'] = [
                'field_name' => 'product_attribute_amount',
                'field_type' => 'amount',
                'text' => '属性总价',
                'desc' => '',
                'value' => $current_product_attribute_amount,
                'order_column' => 1,
                'high_light' => 0,
            ];
        }

        $rocket->setRelateItems($products);

        return $next($rocket);
    }
}
