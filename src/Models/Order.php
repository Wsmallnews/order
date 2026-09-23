<?php

namespace Wsmallnews\Order\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wsmallnews\Order\Enums;
use Wsmallnews\Pay\Contracts\PayableInterface;
use Wsmallnews\Support\Casts\MoneyCast;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;

class Order extends SupportModel implements PayableInterface
{
    use SoftDeletes;
    use Traits\Payable;

    protected $table = 'sn_orders';

    protected $guarded = [];

    protected $casts = [
        // json
        'original_amount_fields' => 'array',
        'amount_fields' => 'array',
        'discount_fields' => 'array',
        'fields_infos' => 'array',
        'options' => 'array',

        // 金额（币种读行内 currency 列，空回落站点默认）
        'relate_original_amount' => MoneyCast::class . ':currency',
        'relate_amount' => MoneyCast::class . ':currency',
        'order_amount' => MoneyCast::class . ':currency',
        'score_amount' => MoneyCast::class,
        'discount_amount' => MoneyCast::class . ':currency',
        'pay_fee' => MoneyCast::class . ':currency',
        'original_pay_fee' => MoneyCast::class . ':currency',
        'remain_pay_fee' => MoneyCast::class . ':currency',

        // Enum
        'status' => Enums\Order\Status::class,
        'pay_status' => Enums\Order\PayStatus::class,
        'refund_status' => Enums\Order\RefundStatus::class,
        'delivery_status' => Enums\Order\DeliveryStatus::class,

        'paid_at' => 'timestamp',
    ];

    /**
     * 记录操作日志时，将下面字段计入 json 中
     *
     * @param  self  $order
     */
    public function getStatusFields($order): array
    {
        return [
            'pay_status' => $order->pay_status,
            'delivery_status' => $order->delivery_status,
            'refund_status' => $order->refund_status,
        ];
    }

    /**
     * buyer 购买人信息
     */
    public function buyer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * items
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * 租户
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }
}
