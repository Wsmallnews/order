<?php

namespace Wsmallnews\Order\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Wsmallnews\Order\OrderCreate;
use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Order\Shortcuts\Shop as ShopShortcut;

class Confirm extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected OrderCreate $orderCreate;

    protected OrderRocket $rocket;

    public ?Model $user = null;

    public string $order_type = 'product';

    public string $type = 'calc';       // calc=计算，create=创建

    public array $relateItems = [];

    public int $address_id = 0;

    public int $coupon_id = 0;

    public string $remark = '';

    public string $from = 'product-detail';

    public string $platform = 'web';

    public function mount(array $relateItems, ?string $order_type, ?string $from)
    {
        $this->relateItems = $relateItems;
        $this->order_type = $order_type ?: $this->order_type;
        $this->from = $from ?: $this->from;
    }

    public function boot()
    {
        $this->orderCreate = new OrderCreate($this->order_type, $this->user);
        $this->orderCreate->setParams([
            'relate_items' => $this->relateItems,
            'address_id' => $this->address_id,
            'coupon_id' => $this->coupon_id,
            'remark' => $this->remark,
            'from' => $this->from,
            'platform' => $this->platform,
        ]);

        $this->orderCreate->setShortcut((new ShopShortcut($this->order_type)));

        $this->rocket = $this->orderCreate->calc($this->type);
    }

    public function create()
    {
        $order = $this->orderCreate->create($this->rocket);

        $this->dispatch('order-create-finish', order_sn: $order->order_sn);
    }

    public function render()
    {

        $payloads = $this->rocket->getPayloads();

        return view('sn-order::livewire.confirm', [
            'order_type' => $this->order_type,
            'address_id' => $this->address_id,
            'coupon_id' => $this->coupon_id,
            'remark' => $this->remark,
            'from' => $this->from,
            ...$payloads,
        ])->title('订单确认');
    }
}
