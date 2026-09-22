<?php

namespace Wsmallnews\Order\Contracts;

/**
 * 可支付主体接口（订单侧的支付契约）
 *
 * 原本依赖 wsmallnews/pay 包的同名接口；pay 包未落地前由本包持有契约，
 * Order 实现它——pay 包接入后可直接 type-hint 本接口（或桥接到其自有契约）。
 */
interface PayableInterface
{
    /**
     * payable 的 scope_type
     */
    public function getScopeType(): string;

    /**
     * payable 的 scope_id
     */
    public function getScopeId(): int;

    /**
     * payable 的 type（morph 别名）
     */
    public function morphType(): string;

    /**
     * payable 的 id
     */
    public function morphId(): int;

    /**
     * payable 的 Options
     */
    public function morphOptions(): array;
}
