<?php

namespace Wsmallnews\Order\Enums\Order;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

Enum RefundStatus :string implements HasColor, HasLabel
{

    use EnumHelper;

    case Unrefund = 'unrefund';

    case Hasrefund = 'hasrefund';

    case Refunded = 'refunded';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unrefund => '未退款',
            self::Hasrefund => '存在退款',
            self::Refunded => '已退款',
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