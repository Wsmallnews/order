<?php

namespace Wsmallnews\Order\Enums\Item;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum PayStatus: string implements HasColor, HasLabel
{
    use EnumHelper;

    case Unpaid = 'unpaid';

    case Paid = 'paid';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unpaid => __('sn-order::order.item_pay_status.unpaid'),
            self::Paid => __('sn-order::order.item_pay_status.paid'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unpaid => 'gray',
            self::Paid => 'success',
        };
    }
}
