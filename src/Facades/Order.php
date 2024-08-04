<?php

namespace Wsmallnews\Order\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wsmallnews\Order\Order
 */
class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wsmallnews\Order\Order::class;
    }
}
