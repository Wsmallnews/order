<?php

namespace Wsmallnews\Order\Filament\Resources\Orders\Pages;

use Filament\Resources\Pages\EditRecord;
use Wsmallnews\Order\Filament\Resources\Orders\OrderResource;
use Wsmallnews\Order\Models\Order;
use Wsmallnews\Profile\Filament\Address\Forms\Fields\RegionCascade;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * 表单中的收货地址字段（含 region 拍平）写入订单地址快照行，不触碰主表
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $region = $data['region'] ?? null;

        $addressKeys = ['consignee', 'phone', 'address_line1', 'address_line2', 'postal_code'];

        $addressData = collect($data)
            ->only($addressKeys)
            ->merge(RegionCascade::flattenToColumns($region))
            ->all();

        /** @var Order $record */
        $record = $this->getRecord();

        $record->address()->updateOrCreate([], $addressData);

        return collect($data)->except([...$addressKeys, 'region'])->all();
    }
}
