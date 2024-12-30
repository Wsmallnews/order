<?php

namespace Wsmallnews\Order\Enums\Order;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

Enum Status :string implements HasColor, HasLabel, HasDescription
{

    use EnumHelper;

    case Closed = 'closed';

    case Unpaid = 'unpaid';

    case Paid = 'paid';
    
    case ApplyingRefund = 'applying_refund';
    
    case WaitingSend = 'waiting_send';
    
    case WaitingGet = 'waiting_get';

    case Geted = 'geted';
    
    case Completed = 'completed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Closed => '交易关闭',
            self::Unpaid => '等待支付',
            self::Paid => '已支付',
            self::ApplyingRefund => '申请退款中',
            self::WaitingSend => '等待发货',
            self::WaitingGet => '等待收货',
            self::Geted => '已收货',
            self::Completed => '交易完成',
        };
    }

    
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Closed => 'gray',
            self::Unpaid => 'gray',
            self::Paid => 'success',
            self::ApplyingRefund => 'success',
            self::WaitingSend => 'success',
            self::WaitingGet => 'success',
            self::Geted => 'success',
            self::Completed => 'success',
        };
    }


    public function getDescription(): ?string
    {
        return match ($this) {
            self::Closed => '买家未在规定时间内付款.',
            self::Unpaid => '等待买家付款.',
            self::Paid => '订单已支付.',
            self::ApplyingRefund => '等待卖家处理退款申请.',
            self::WaitingSend => '等待卖家发货.',
            self::WaitingGet => '等待买家收货.',
            self::Geted => '已收货.',
            self::Completed => '交易完成.',
        };
    }

}