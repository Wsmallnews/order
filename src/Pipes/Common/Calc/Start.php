<?php

namespace Wsmallnews\Order\Pipes\Common\Calc;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\CalcPipeInterface;
use Wsmallnews\Order\OrderRocket;

/**
 * 计价阶段通用聚合管道：费用结构初始化（前置）+ 各级汇总（后置）。
 *
 * 金额口径：整数分（int）。字段结构：
 * - buyInfo 级：original_amount_fields / amount_fields / discount_fields（键 => 分）
 * - 订单级 radar：同名三组 + original_order_amount / order_amount / discount_amount / pay_fee / relate_* 累计
 */
class Start implements CalcPipeInterface
{
    /**
     * 订单级金额 radar key
     */
    protected const ORDER_AMOUNT_RADARS = [
        'original_order_amount',
        'order_amount',
        'discount_amount',
        'pay_fee',
        'relate_original_amount',
        'relate_amount',
    ];

    public function calc(OrderRocket $rocket, Closure $next): OrderRocket
    {
        // ============================== 前置：结构初始化 ==============================
        $rocket->mergeRadars([
            'original_amount_fields' => [],
            'amount_fields' => [],
            'discount_fields' => [],
            'original_amount_fields_info' => [],
            'amount_fields_info' => [],
            'discount_fields_info' => [],
        ]);

        foreach (static::ORDER_AMOUNT_RADARS as $field) {
            $rocket->setRadar($field, 0);
        }

        $relateItems = $rocket->getRelateItems();
        foreach ($relateItems as $key => &$buyInfo) {
            $buyInfo['original_amount_fields'] = [];
            $buyInfo['amount_fields'] = [];
            $buyInfo['discount_fields'] = [];

            $buyInfo['original_amount_fields_info'] = [];
            $buyInfo['amount_fields_info'] = [];
            $buyInfo['discount_fields_info'] = [];

            // relate 单价合计（分）：商品价格 + 属性价格等（不乘数量）
            $buyInfo['relate_original_price'] = 0;
            $buyInfo['relate_price'] = 0;

            // relate 总价（分）：单价合计 * 购买数量
            $buyInfo['relate_original_amount'] = 0;
            $buyInfo['relate_amount'] = 0;

            // 含配送等的总价（分）
            $buyInfo['original_amount'] = 0;
            $buyInfo['amount'] = 0;

            // 配送费（分）单独记录
            $buyInfo['delivery_amount'] = 0;

            // 优惠（分）
            $buyInfo['discount_amount'] = 0;
            $buyInfo['score_amount'] = 0;

            // total_fee = 总费用 - 总优惠；reonly_fee = total_fee - 配送费
            $buyInfo['total_fee'] = 0;
            $buyInfo['reonly_fee'] = 0;
        }
        $rocket->setRelateItems($relateItems);

        $response = $next($rocket);

        // ============================== 后置：全部计价管道完成后汇总 ==============================
        $relateItems = $rocket->getRelateItems();
        foreach ($relateItems as $key => &$buyInfo) {
            // 展示字段集归拢（值统一十进制串，仅展示不参与运算）
            $buyInfo['fields_infos'] = array_merge(($buyInfo['fields_infos'] ?? []), [
                'original_amount_fields_info' => $buyInfo['original_amount_fields_info'],
                'amount_fields_info' => $buyInfo['amount_fields_info'],
                'discount_fields_info' => $buyInfo['discount_fields_info'],
            ]);

            // buyInfo 级汇总：各字段集求和（整数分）
            $buyInfo['original_amount'] = array_sum($buyInfo['original_amount_fields']);
            $buyInfo['amount'] = array_sum($buyInfo['amount_fields']);
            $buyInfo['discount_amount'] = array_sum($buyInfo['discount_fields']);

            // total_fee = 总费用 - 总优惠；reonly_fee = total_fee - 配送费
            $buyInfo['total_fee'] = max(0, $buyInfo['amount'] - $buyInfo['discount_amount']);
            $buyInfo['reonly_fee'] = max(0, $buyInfo['total_fee'] - $buyInfo['delivery_amount']);
        }
        $rocket->setRelateItems($relateItems);

        // 订单级展示字段集归拢
        $rocket->setRadar('fields_infos', array_merge($rocket->getRadar('fields_infos', []), [
            'original_amount_fields_info' => $rocket->getRadar('original_amount_fields_info', []),
            'amount_fields_info' => $rocket->getRadar('amount_fields_info', []),
            'discount_fields_info' => $rocket->getRadar('discount_fields_info', []),
        ]));

        // 订单级汇总：字段集逐项累加到订单金额 radar（整数分）
        foreach ($rocket->getRadar('original_amount_fields', []) as $amount) {
            $rocket->radarAdditionMinor('original_order_amount', (int) $amount);
        }
        foreach ($rocket->getRadar('amount_fields', []) as $amount) {
            $rocket->radarAdditionMinor('order_amount', (int) $amount);
        }
        foreach ($rocket->getRadar('discount_fields', []) as $amount) {
            $rocket->radarAdditionMinor('discount_amount', (int) $amount);
        }

        // 订单应付 = 订单总额 - 优惠总额（不能小于 0）
        $payFee = max(0, $rocket->radarMinor('order_amount') - $rocket->radarMinor('discount_amount'));
        $rocket->setRadar('pay_fee', $payFee);

        return $response;
    }
}
