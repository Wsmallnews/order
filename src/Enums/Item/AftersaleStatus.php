<?php

namespace Wsmallnews\Order\Enums\Item;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum AftersaleStatus: string implements HasColor, HasLabel
{
    use EnumHelper;

    case Refuse = 'refuse';

    case Unafter = 'unafter';

    case Ing = 'ing';

    case Completed = 'completed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Refuse => __('sn-order::order.item_aftersale_status.refuse'),
            self::Unafter => __('sn-order::order.item_aftersale_status.unafter'),
            self::Ing => __('sn-order::order.item_aftersale_status.ing'),
            self::Completed => __('sn-order::order.item_aftersale_status.completed'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Refuse => 'gray',
            self::Unafter => 'success',
            self::Ing => 'success',
            self::Completed => 'success',
        };
    }
}
