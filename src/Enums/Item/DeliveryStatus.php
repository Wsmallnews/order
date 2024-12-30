<?php

namespace Wsmallnews\Order\Enums\Item;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

Enum DeliveryStatus :string implements HasColor, HasLabel
{

    use EnumHelper;

    case WaitingSend = 'waiting_send';

    case WaitingGet = 'waiting_get';

    case Geted = 'geted';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::WaitingSend => '未发货',
            self::WaitingGet => '未收货',
            self::Geted => '已收货',
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