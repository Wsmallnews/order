<?php

namespace Wsmallnews\Order\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 购买人接口
 */
interface BuyerInterface
{
    /**
     * buyerable 的 type
     */
    public function morphType(): string;

    /**
     * buyerable 的 id
     */
    public function morphId(): int;


    /**
     * 关联订单
     * 
     * @return MorphMany
     */
    public function orders(): MorphMany;


    /**
     * 
     * @return MorphMany
     */
    public function orderItems(): MorphMany;
}
