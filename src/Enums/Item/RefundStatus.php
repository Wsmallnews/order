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
            self::Unrefund => '未退款',
            self::Refunded => '已退款',
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
