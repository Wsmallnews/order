<?php

namespace Wsmallnews\Order\Pipes\Common\Summary;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\SummaryPipeInterface;
use Wsmallnews\Order\OrderRocket;

/**
 * 汇总阶段通用管道：把 radars/relate_items 组装为 payloads（落单与展示数据源）
 */
class Start implements SummaryPipeInterface
{
    public function summary(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $response = $next($rocket);

        $radars = $rocket->getRadars();

        $relateItems = $rocket->getRelateItems();

        $formatRelateItems = [];
        foreach ($relateItems as $buyInfo) {
            $formatRelateItems[] = [
                'relate_type' => $buyInfo['relate_type'],
                'relate_id' => $buyInfo['relate_id'],
                'relate_title' => $buyInfo['relate_title'],
                'relate_subtitle' => $buyInfo['relate_subtitle'] ?? '',
                'relate_attributes' => $buyInfo['relate_attributes'] ?? [],
                'relate_image' => $buyInfo['relate_image'] ?? '',
                'relate_original_price' => $buyInfo['relate_original_price'],
                'relate_price' => $buyInfo['relate_price'],
                'relate_original_amount' => $buyInfo['relate_original_amount'],
                'relate_amount' => $buyInfo['relate_amount'],
                'relate_stock_num' => $buyInfo['relate_stock_num'],
                'relate_num' => $buyInfo['relate_num'],
                'relate_weight' => $buyInfo['relate_weight'] ?? 0,
                'relate_sn' => $buyInfo['relate_sn'] ?? '',
                'relate_options' => $buyInfo['relate_options'] ?? null,

                'original_amount_fields' => $buyInfo['original_amount_fields'],
                'amount_fields' => $buyInfo['amount_fields'],
                'original_amount' => $buyInfo['original_amount'],
                'amount' => $buyInfo['amount'],
                'score_amount' => $buyInfo['score_amount'] ?? 0,
                'discount_fields' => $buyInfo['discount_fields'],
                'discount_amount' => $buyInfo['discount_amount'],
                'total_fee' => $buyInfo['total_fee'],
                'reonly_fee' => $buyInfo['reonly_fee'],

                'delivery_type' => $buyInfo['delivery_type'] ?? '',
                'delivery_amount' => $buyInfo['delivery_amount'] ?? 0,
                'delivery_id' => $buyInfo['delivery_id'] ?? 0,

                'fields_infos' => $buyInfo['fields_infos'],
                'stock_type' => $buyInfo['stock_type'] ?? 'infinite',
                'stock_unit' => $buyInfo['stock_unit'] ?? '',
            ];
        }

        $rocket->mergePayloads([
            'relate_items' => $formatRelateItems,

            'original_amount_fields' => $radars['original_amount_fields'] ?? [],
            'amount_fields' => $radars['amount_fields'] ?? [],
            'discount_fields' => $radars['discount_fields'] ?? [],

            // 关联项目总金额（整数分，订单存表用）
            'relate_original_amount' => (int) ($radars['relate_original_amount'] ?? 0),
            'relate_amount' => (int) ($radars['relate_amount'] ?? 0),

            'original_amount_fields_info' => $radars['original_amount_fields_info'] ?? [],
            'amount_fields_info' => $radars['amount_fields_info'] ?? [],
            'discount_fields_info' => $radars['discount_fields_info'] ?? [],
            'fields_infos' => $radars['fields_infos'] ?? [],

            'original_order_amount' => (int) ($radars['original_order_amount'] ?? 0),
            'order_amount' => (int) ($radars['order_amount'] ?? 0),
            'score_amount' => (int) ($radars['score_amount'] ?? 0),
            'discount_amount' => (int) ($radars['discount_amount'] ?? 0),

            'pay_fee' => (int) ($radars['pay_fee'] ?? 0),

            'order' => null,        // create 时落库
        ]);

        return $response;
    }
}
