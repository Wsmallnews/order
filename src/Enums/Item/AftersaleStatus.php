<?php

namespace Wsmallnews\Order\Enums\Item;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

Enum AftersaleStatus :string implements HasColor, HasLabel
{

    use EnumHelper;

    case Refuse = 'refuse';

    case Unafter = 'unafter';

    case Ing = 'ing';

    case Completed = 'completed';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Refuse => '售后驳回',
            self::Unafter => '未申请',
            self::Ing => '申请售后',
            self::Completed => '已完成',
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