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

        $rocket->mergePayloads([
            'original_amount_fields' => $radars['original_amount_fields'],
            'amount_fields' => $radars['amount_fields'],
            'discount_fields' => $radars['discount_fields'],

            // 关联项目的总金额，订单存表用
            'relate_original_amount' => $radars['relate_original_amount'],
            'relate_amount' => $radars['relate_amount'],

            'original_amount_fields_info' => $radars['original_amount_fields_info'],
            'amount_fields_info' => $radars['amount_fields_info'],
            'discount_fields_info' => $radars['discount_fields_info'],

            'original_order_amount' => $radars['original_order_amount'],
            'order_amount' => $radars['order_amount'],
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
