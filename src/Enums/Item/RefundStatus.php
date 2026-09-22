<?php

namespace Wsmallnews\Order\Enums\Item;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum RefundStatus: string implements HasColor, HasLabel
{
    use EnumHelper;

    case Unrefund = 'unrefund';

    case Refunded = 'refunded';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unrefund => __('sn-order::order.item_refund_status.unrefund'),
            self::Refunded => __('sn-order::order.item_refund_status.refunded'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unrefund => 'gray',
            self::Refunded => 'danger',
        };
    }
}
