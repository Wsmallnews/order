<?php

namespace Wsmallnews\Order\Enums\Order;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum Status: string implements HasColor, HasDescription, HasLabel
{
    use EnumHelper;

    case Closed = 'closed';

    case Unpaid = 'unpaid';

    case Paid = 'paid';

    case ApplyingRefund = 'applying_refund';

    case WaitingSend = 'waiting_send';

    case WaitingGet = 'waiting_get';

    case Geted = 'geted';

    case Completed = 'completed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Closed => __('sn-order::order.order_status.closed'),
            self::Unpaid => __('sn-order::order.order_status.unpaid'),
            self::Paid => __('sn-order::order.order_status.paid'),
            self::ApplyingRefund => __('sn-order::order.order_status.applying_refund'),
            self::WaitingSend => __('sn-order::order.order_status.waiting_send'),
            self::WaitingGet => __('sn-order::order.order_status.waiting_get'),
            self::Geted => __('sn-order::order.order_status.geted'),
            self::Completed => __('sn-order::order.order_status.completed'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Closed => 'gray',
            self::Unpaid => 'gray',
            self::Paid => 'success',
            self::ApplyingRefund => 'success',
            self::WaitingSend => 'success',
            self::WaitingGet => 'success',
            self::Geted => 'success',
            self::Completed => 'success',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::Closed => __('sn-order::order.order_status.desc.closed'),
            self::Unpaid => __('sn-order::order.order_status.desc.unpaid'),
            self::Paid => __('sn-order::order.order_status.desc.paid'),
            self::ApplyingRefund => __('sn-order::order.order_status.desc.applying_refund'),
            self::WaitingSend => __('sn-order::order.order_status.desc.waiting_send'),
            self::WaitingGet => __('sn-order::order.order_status.desc.waiting_get'),
            self::Geted => __('sn-order::order.order_status.desc.geted'),
            self::Completed => __('sn-order::order.order_status.desc.completed'),
        };
    }
}
