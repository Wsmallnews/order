<?php

namespace Wsmallnews\Order\Pipes\Shop\Calc;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CalcPipeInterface;
use Wsmallnews\Order\OrderRocket;

class Start implements CalcPipeInterface
{
    public function calc(OrderRocket $rocket, Closure $next): OrderRocket
    {
        // 组合字段初始化
        $rocket->setRadar('original_amount_fields', []);            // 初始化 original_amount_fields
        $rocket->setRadar('amount_fields', []);                     // 初始化 amount_fields
        $rocket->setRadar('discount_fields', []);                   // 初始化 discount_fields

        $rocket->setRadar('original_amount_fields_info', []);            // 初始化 original_amount_fields
        $rocket->setRadar('amount_fields_info', []);                     // 初始化 amount_fields
        $rocket->setRadar('discount_fields_info', []);                   // 初始化 discount_fields

        // 订单相关费用
        $rocket->radarAdditionAmount('original_order_amount', 0);          // 原始订单总金额
        $rocket->radarAdditionAmount('order_amount', 0);                   // 订单总金额
        $rocket->radarAdditionAmount('discount_amount', 0);                // 优惠总价 (优惠券，活动等等)
        $rocket->radarAdditionAmount('pay_fee', 0);                        // 订单应支付金额

        // 临时关联项价格合计，方便累加用
        $rocket->radarAdditionAmount('relate_original_amount', 0);       // 关联项原始总价 (如果是商品包含属性价格)
        $rocket->radarAdditionAmount('relate_amount', 0);                // 关联项总价 (如果是商品包含属性价格)

        // 商品组合字段初始化
        $relateItems = $rocket->getRelateItems();
        foreach ($relateItems as $key => &$buyInfo) {
            $buyInfo['original_amount_fields'] = [];
            $buyInfo['amount_fields'] = [];
            $buyInfo['discount_fields'] = [];

            $buyInfo['original_amount_fields_info'] = [];
            $buyInfo['amount_fields_info'] = [];
            $buyInfo['discount_fields_info'] = [];

            // (需要频繁用，单独记录) relate 相关价格，商品价格，和商品属性价格累计，不单独区分
            $buyInfo['relate_original_price'] = '0';              // relate 原始单价总和： 商品的话包含 （商品原始价格：origin_price + 属性原始价格：product_attribute_origin_amount）
            $buyInfo['relate_price'] = '0';                       // relate 单价总和 ：（商品价格：price + 属性原始价格：product_attribute_amount）
            $buyInfo['relate_original_amount'] = '0';             // relate 原始总计：原始单价总和 * 购买数量
            $buyInfo['relate_amount'] = '0';                      // relate 总计：单价总和 * 购买数量

            // 总计，包含 relate 费用，运费等
            $buyInfo['original_amount'] = '0';                    // 原始总价 (原始总计 + 原始运费等等)
            $buyInfo['amount'] = '0';                             // 总价(总计 + 运费等等)

            // 配送费有字段单独记录
            $buyInfo['delivery_amount'] = '0';                             // 配送费

            // 优惠
            $buyInfo['discount_amount'] = '0';                       // 优惠总计  累计 discount_fields 的金额

            // total_fee = 总费用 - 总优惠； pay_fee = (总费用 - 配送费) - 总优惠
            $buyInfo['total_fee'] = '0';
            $buyInfo['reonly_fee'] = '0';
        }
        $rocket->setRelateItems($relateItems);

        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        $relateItems = $rocket->getRelateItems();
        foreach ($relateItems as $key => &$buyInfo) {
            // 将 fields_infos 记录到一个字段中
            $buyInfo['fields_infos'] = array_merge(($buyInfo['fields_infos'] ?? []), [
                'original_amount_fields_info' => $buyInfo['original_amount_fields_info'],
                'amount_fields_info' => $buyInfo['amount_fields_info'],
                'discount_fields_info' => $buyInfo['discount_fields_info'],
            ]);

            $originalAmountFields = $buyInfo['original_amount_fields'];
            $amountFields = $buyInfo['amount_fields'];
            $discountFields = $buyInfo['discount_fields'];

            // 原始总计
            foreach ($originalAmountFields as $key => $amount_field) {
                $buyInfo['original_amount'] = bcadd($buyInfo['original_amount'], (string) $amount_field, 2);
            }

            // 总计
            foreach ($amountFields as $key => $amount_field) {
                $buyInfo['amount'] = bcadd($buyInfo['amount'], (string) $amount_field, 2);
            }

            // 优惠总计
            foreach ($discountFields as $key => $discount_field) {
                $buyInfo['discount_amount'] = bcadd($buyInfo['discount_amount'], (string) $discount_field, 2);
            }

            // total_fee = 总费用 - 总优惠； pay_fee = (总费用 - 配送费) - 总优惠
            $buyInfo['reonly_fee'] = $buyInfo['total_fee'] = bcsub($buyInfo['amount'], $buyInfo['discount_amount'], 2);
            if (isset($buyInfo['delivery_amount'])) {
                $buyInfo['reonly_fee'] = bcsub($buyInfo['total_fee'], (string) $buyInfo['delivery_amount'], 2);
            }
        }
        $rocket->setRelateItems($relateItems);

        // 将 fields_infos 记录到一个字段中
        $rocket->setRadar('fields_infos', array_merge($rocket->getRadar('fields_infos', []), [
            'original_amount_fields_info' => $rocket->getRadar('original_amount_fields_info', []),
            'amount_fields_info' => $rocket->getRadar('amount_fields_info', []),
            'discount_fields' => $rocket->getRadar('discount_fields', []),
        ]));

        // 计算订单原始总金额
        $orderOriginalAmountFields = $rocket->getRadar('original_amount_fields', []);
        $orderAmountFields = $rocket->getRadar('amount_fields', []);
        $orderDiscountFields = $rocket->getRadar('discount_fields', []);

        foreach ($orderOriginalAmountFields as $key => $amount_field) {
            // 原始订单总金额
            $rocket->radarAdditionAmount('original_order_amount', $amount_field);
        }
        foreach ($orderAmountFields as $key => $amount_field) {
            // 订单总金额
            $rocket->radarAdditionAmount('order_amount', $amount_field);          // 原始订单总金额
        }
        foreach ($orderDiscountFields as $key => $amount_field) {
            // 订单总优惠
            $rocket->radarAdditionAmount('discount_amount', $amount_field);          // 原始订单总金额
        }

        // 计算订单应支付金额
        $pay_fee = bcsub($rocket->getRadar('order_amount'), $rocket->getPayload('discount_amount'), 2);
        $pay_fee = $pay_fee < 0 ? 0 : $pay_fee;
        $rocket->radarAdditionAmount('pay_fee', $pay_fee);

        return $response;
    }
}
