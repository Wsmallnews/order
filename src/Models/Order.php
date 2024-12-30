<?php

namespace Wsmallnews\Order\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Wsmallnews\Order\Enums;
use Wsmallnews\Support\Casts\MoneyCast;
use Wsmallnews\Support\Models\SupportModel;

class Order extends SupportModel
{
    protected $table = 'sn_orders';

    protected $guarded = [];

    protected $casts = [
        // json
        'original_amount_fields' => 'array',
        'amount_fields' => 'array',
        'discount_fields' => 'array',
        'fields_infos' => 'array',
        'options' => 'array',

        // 金额
        'relate_original_amount' => MoneyCast::class,
        'relate_amount' => MoneyCast::class,
        'order_amount' => MoneyCast::class,
        'score_amount' => MoneyCast::class,
        'discount_amount' => MoneyCast::class,
        'pay_fee' => MoneyCast::class,
        'original_pay_fee' => MoneyCast::class,
        'remain_pay_fee' => MoneyCast::class,

        // Enum
        'status' => Enums\Order\Status::class,
        'pay_status' => Enums\Order\PayStatus::class,
        'refund_status' => Enums\Order\RefundStatus::class,
        'delivery_status' => Enums\Order\DeliveryStatus::class,

        'paid_at' => 'timestamp',
    ];

    // protected function childrenNum(): Attribute
    // {
    //     $children = $this->children;
    //     return Attribute::make(
    //         get: fn () => $children->count(),
    //     );
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('sn-order.user_model'), 'user_id');
    }
}
