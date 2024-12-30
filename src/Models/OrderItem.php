<?php

namespace Wsmallnews\Order\Models;

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
        'evaluate_status' => Enums\Item\EvaluateStatus::class,
        'delivery_status' => Enums\Item\DeliveryStatus::class,
        'aftersale_status' => Enums\Item\AftersaleStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(config('sn-order.user_model'), 'user_id');
    }
}
