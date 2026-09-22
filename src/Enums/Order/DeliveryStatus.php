<?php

namespace Wsmallnews\Order\Enums\Order;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum DeliveryStatus: string implements HasColor, HasLabel
{
    use EnumHelper;

    case WaitingSend = 'waiting_send';

    case WaitingGet = 'waiting_get';

    case Geted = 'geted';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::WaitingSend => __('sn-order::order.order_delivery_status.waiting_send'),
            self::WaitingGet => __('sn-order::order.order_delivery_status.waiting_get'),
            self::Geted => __('sn-order::order.order_delivery_status.geted'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::WaitingSend => 'gray',
            self::WaitingGet => 'success',
            self::Geted => 'success',
        };
    }
}
