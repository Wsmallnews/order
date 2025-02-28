<?php

namespace Wsmallnews\Order\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Wsmallnews\Order\Enums;
use Wsmallnews\Support\Casts\MoneyCast;
use Wsmallnews\Support\Models\SupportModel;

class OrderItem extends SupportModel
{
    protected $table = 'sn_order_items';

    protected $guarded = [];

    protected $casts = [
        // json
        'relate_attributes' => 'array',
        'relate_options' => 'array',
        'original_amount_fields' => 'array',
        'amount_fields' => 'array',
        'discount_fields' => 'array',
        'fields_infos' => 'array',
        'options' => 'array',

        // 金额
        'relate_original_price' => MoneyCast::class,
        'relate_price' => MoneyCast::class,

        'amount' => MoneyCast::class,
        'score_amount' => MoneyCast::class,
        'discount_amount' => MoneyCast::class,
        'total_fee' => MoneyCast::class,
        'reonly_fee' => MoneyCast::class,
        'delivery_amount' => MoneyCast::class,
        'refunded_fee' => MoneyCast::class,

        // Enum
        'pay_status' => Enums\Item\PayStatus::class,
        'refund_status' => Enums\Item\RefundStatus::class,
        'delivery_status' => Enums\Item\DeliveryStatus::class,
        'aftersale_status' => Enums\Item\AftersaleStatus::class,
        'evaluate_status' => Enums\Item\EvaluateStatus::class,
    ];

    /**
     * 记录操作日志时，将下面字段计入 json 中
     *
     * @param  self  $orderItem
     */
    public function getStatusFields($orderItem): array
    {
        return [
            'pay_status' => $orderItem->pay_status,
            'refund_status' => $orderItem->refund_status,
            'delivery_status' => $orderItem->delivery_status,
            'aftersale_status' => $orderItem->aftersale_status,
            'evaluate_status' => $orderItem->evaluate_status,
        ];
    }

    /**
     * buyer 购买人信息
     */
    public function buyer(): MorphTo
    {
        return $this->morphTo();
    }
}
