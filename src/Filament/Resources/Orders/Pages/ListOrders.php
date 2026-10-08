<?php

namespace Wsmallnews\Order\Filament\Resources\Orders\Pages;

use Filament\Resources\Pages\ListRecords;
use Wsmallnews\Order\Filament\Resources\Orders\OrderResource;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
