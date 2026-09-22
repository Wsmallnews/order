<?php

namespace Wsmallnews\Order\Enums\Order;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum RefundStatus: string implements HasColor, HasLabel
{
    use EnumHelper;

    case Unrefund = 'unrefund';

    case Hasrefund = 'hasrefund';

    case Refunded = 'refunded';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unrefund => __('sn-order::order.order_refund_status.unrefund'),
            self::Hasrefund => __('sn-order::order.order_refund_status.hasrefund'),
            self::Refunded => __('sn-order::order.order_refund_status.refunded'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unrefund => 'gray',
            self::Hasrefund => 'warning',
            self::Refunded => 'danger',
        };
    }
}
