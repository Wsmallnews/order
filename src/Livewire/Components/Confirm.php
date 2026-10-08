<?php

namespace Wsmallnews\Order\Livewire\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Wsmallnews\Order\OrderCreate;
use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Support\Contracts\HasSnIdentifiable;

/**
 * 下单确认组件：试算（calc）展示 + 落单（create）。
 *
 * 配方按 order_type 从场景注册表解析（消费模块 boot 时注册），
 * 组件本身不感知任何业务配方；buyer 为任意 morph 模型（原生 getMorphClass/getKey）。
 *
 * 收货地址：地址块由宿主页面承载（profile 包 ChooseAddress 组件），选中/新增经
 * sn-profile-address:selected 事件回传 addressId（生态事件约定，字符串耦合不产生类依赖）；
 * 初始地址与 ChooseAddress 同规则（默认优先 + 新建优先），保证两侧初始选中一致。
 */
class Confirm extends Base implements HasActions
{
    use InteractsWithActions;

    protected OrderCreate $orderCreate;

    protected OrderRocket $rocket;

    public ?HasSnIdentifiable $buyer = null;

    public string $order_type = 'product';

    public string $type = 'calc';       // calc=计算，create=创建

    public array $relateItems = [];

    /**
     * 选中的收货地址 ID（saveOrder 校验归属后写入地址快照）
     */
    #[Locked]
    public ?int $addressId = null;

    public string $from = 'product-detail';

    public string $platform = 'web';

    /**
     * 订单备注（买家留言，随单落库）
     */
    public string $remark = '';

    public function boot(): void
    {
        // 初始地址与 ChooseAddress 组件同规则（默认优先 + 新建优先）
        $this->addressId ??= $this->buyer?->addresses()->value('id');

        $this->orderCreate = new OrderCreate($this->order_type, $this->buyer);
        $this->orderCreate->setParams([
            ...$this->getScopeable(),
            'relate_items' => $this->relateItems,
            'address_id' => $this->addressId,
            'remark' => $this->remark,
            'from' => $this->from,
            'platform' => $this->platform,
        ]);

        $this->rocket = $this->orderCreate->calc($this->type);
    }

    /**
     * 地址选择回传（ChooseAddress 选中/新增均触发）
     */
    #[On('sn-profile-address:selected')]
    public function selectAddress(int $addressId): void
    {
        $this->addressId = $addressId;
    }

    public function create()
    {
        $order = $this->orderCreate->create($this->rocket);

        $this->dispatch('sn-order-created', order_sn: $order->order_sn);
    }

    public function render()
    {
        return view('sn-order::livewire.components.confirm', [
            'order_type' => $this->order_type,
            'from' => $this->from,
            ...$this->rocket->getPayloads()->all(),
        ])->title(__('sn-order::order.confirm.title'));
    }
}
