<?php

namespace Wsmallnews\Order\Enums\Item;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum EvaluateStatus: string implements HasColor, HasLabel
{
    use EnumHelper;

    case Unevaluate = 'unevaluate';

    case Evaluated = 'evaluated';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unevaluate => __('sn-order::order.item_evaluate_status.unevaluate'),
            self::Evaluated => __('sn-order::order.item_evaluate_status.evaluated'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unevaluate => 'gray',
            self::Evaluated => 'success',
        };
    }
}
