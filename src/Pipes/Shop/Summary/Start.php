<?php

namespace Wsmallnews\Order\Pipes\Shop\Summary;

use Closure;
use Wsmallnews\Order\Contracts\Pipes\SummaryPipeInterface;
use Wsmallnews\Order\OrderRocket;

class Start implements SummaryPipeInterface
{
    public function summary(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $response = $next($rocket);

        // ==============================后置 所有中间件走完之后，再计算=============================

        $radars = $rocket->getRadars();


        // 设置 payloads
        $relateItems = $rocket->getRelateItems();

        $formatRelateItems = [];
        foreach ($relateItems as $key => $buyInfo) {
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
                'relate_sn' => $buyInfo['relate_sn'],
                'relate_options' => $buyInfo['relate_options'],
                'original_amount_fields' => $buyInfo['original_amount_fields'],
                'amount_fields' => $buyInfo['amount_fields'],
                'original_amount' => $buyInfo['original_amount'],
                'amount' => $buyInfo['amount'],
                'score_amount' => $buyInfo['score_amount'],
                'discount_fields' => $buyInfo['discount_fields'],
                'discount_amount' => $buyInfo['discount_amount'],
                'total_fee' => $buyInfo['total_fee'],
                'reonly_fee' => $buyInfo['reonly_fee'],
                'delivery_type' => $buyInfo['delivery_type'],
                'delivery_amount' => $buyInfo['delivery_amount'],
                'delivery_id' => $buyInfo['delivery_id'],
                'fields_infos' => $buyInfo['fields_infos'],
                'stock_type' => $buyInfo['stock_type'],
                'stock_unit' => $buyInfo['stock_unit'],
            ];

            $formatRelateItems[] = $current;
        }

        $rocket->mergePayloads([
            'relate_items' => $formatRelateItems,

            'original_amount_fields' => $radars['original_amount_fields'],
            'amount_fields' => $radars['amount_fields'],
            'discount_fields' => $radars['discount_fields'],

            // 关联项目的总金额，订单存表用
            'relate_original_amount' => $radars['relate_original_amount'],
            'relate_amount' => $radars['relate_amount'],

            'original_amount_fields_info' => $radars['original_amount_fields_info'],
            'amount_fields_info' => $radars['amount_fields_info'],
            'discount_fields_info' => $radars['discount_fields_info'],
            'fields_infos' => $radars['fields_infos'],

            'original_order_amount' => $radars['original_order_amount'],
            'order_amount' => $radars['order_amount'],
            'score_amount' => $radars['score_amount'] ?? 0,     // score_amount 要删掉，积分商城， 独立插件
            'discount_amount' => $radars['discount_amount'],

            'pay_fee' => $radars['pay_fee'],

            'order' => null,        // create 时候会存上
        ]);

        // $rocket->mergePayloads([
        //     'remark' => $rocket->getParam('remark'),
        //     'text_fields' => $rocket->getRadar('text_fields'),
        // ]);

        // // 记录需要存到订单 ext 中的字段
        // $rocket->mergeRadarField([
        //     'text_fields',
        // ], 'ext_fields');

        // // 将字段打包成字段集存入 payloads 中
        // $rocket->buildFileds([
        //     'original_amount_fields',
        //     'amount_fields',
        //     'discount_fields',
        //     'ext_fields'
        // ]);

        return $response;
    }
}
