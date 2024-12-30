<?php

namespace Wsmallnews\Order\Enums\Order;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

Enum PayStatus :string implements HasColor, HasLabel
{

    use EnumHelper;

    case Unpaid = 'unpaid';

    case Paid = 'paid';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unpaid => '未支付',
            self::Paid => '已支付',
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