<?php

namespace Wsmallnews\Order\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Wsmallnews\Order\Support\Utils as OrderUtils;
use Wsmallnews\Profile\Models\Address;
use Wsmallnews\Support\Models\SupportModel;

/**
 * 订单收货地址快照
 *
 * 下单时从地址簿（sn_profile_addresses）复制全量字段，此后独立演化：
 * 地址簿的修改/删除不影响订单；后台编辑订单地址只改本行。
 * source_address_id 软关联溯源，无外键约束。
 */
class OrderAddress extends SupportModel
{
    protected $table = 'sn_order_addresses';

    protected $guarded = [];

    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
        'options' => 'array',
    ];

    /**
     * 复制的列（地址簿 → 订单快照）
     */
    public const SNAPSHOT_COLUMNS = [
        'consignee',
        'phone',
        'country_code',
        'administrative_area',
        'administrative_area_name',
        'locality',
        'locality_name',
        'dependent_locality',
        'dependent_locality_name',
        'township',
        'township_name',
        'address_line1',
        'address_line2',
        'postal_code',
        'longitude',
        'latitude',
    ];

    /**
     * 从地址簿条目生成订单地址快照（并累计地址使用次数）
     */
    public static function snapshot(Order $order, Address $address): static
    {
        $address->increment('used_num');

        /** @var static $orderAddress */
        $orderAddress = static::query()->create([
            'order_id' => $order->id,
            'source_address_id' => $address->id,
            ...collect($address->only(static::SNAPSHOT_COLUMNS))->all(),
        ]);

        return $orderAddress;
    }

    /**
     * 区划展示链（广东省　深圳市　南山区　南头街道）
     */
    protected function regionLabel(): Attribute
    {
        return Attribute::get(fn () => collect([
            $this->administrative_area_name,
            $this->locality_name,
            $this->dependent_locality_name,
            $this->township_name,
        ])->filter()->implode('　'));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderUtils::getOrderModel());
    }
}
