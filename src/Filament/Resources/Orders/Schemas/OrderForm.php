<?php

namespace Wsmallnews\Order\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Wsmallnews\Order\Models\Order;
use Wsmallnews\Profile\Filament\Address\Forms\Fields\RegionCascade;

/**
 * 订单编辑表单：订单信息只读展示 + 收货地址快照编辑
 *
 * 地址字段实际存储在 OrderAddress 关联行上，经 formatStateUsing /
 * mutateFormDataBeforeSave（EditOrder）双向桥接。
 */
class OrderForm
{
    /**
     * @return array<int, mixed>
     */
    public static function components(): array
    {
        return [
            Section::make(__('sn-order::order.order_form.order_info'))
                ->schema([
                    TextInput::make('order_sn')
                        ->label(__('sn-order::order.order_table.order_sn'))
                        ->disabled(),

                    TextInput::make('buyer')
                        ->label(__('sn-order::order.order_table.buyer'))
                        ->formatStateUsing(fn (Order $record) => $record->buyer?->getFilamentName() ?? "{$record->buyer_type}#{$record->buyer_id}")
                        ->disabled(),

                    TextInput::make('order_amount')
                        ->label(__('sn-order::order.order_table.order_amount'))
                        ->formatStateUsing(fn (Order $record) => sn_money()->format($record->order_amount))
                        ->disabled(),

                    TextInput::make('status')
                        ->label(__('sn-order::order.order_table.status'))
                        ->formatStateUsing(fn (Order $record) => $record->status?->getLabel())
                        ->disabled(),

                    TextInput::make('created_at')
                        ->label(__('sn-order::order.order_table.created_at'))
                        ->disabled(),
                ])
                ->columns(3)
                ->columnSpanFull(),

            Section::make(__('sn-order::order.order_form.address_section'))
                ->schema([
                    TextInput::make('consignee')
                        ->label(__('sn-profile::profile.address.consignee'))
                        ->formatStateUsing(fn (Order $record) => $record->address?->consignee)
                        ->required()
                        ->maxLength(64),

                    TextInput::make('phone')
                        ->label(__('sn-profile::profile.address.phone'))
                        ->formatStateUsing(fn (Order $record) => $record->address?->phone)
                        ->required()
                        ->maxLength(32),

                    RegionCascade::make('region')
                        ->label(__('sn-profile::profile.address.region'))
                        ->required()
                        ->columnSpanFull()
                        ->formatStateUsing(fn (Order $record) => $record->address
                            ? RegionCascade::hydrateFromColumns($record->address)
                            : null),

                    TextInput::make('address_line1')
                        ->label(__('sn-profile::profile.address.address_line1'))
                        ->formatStateUsing(fn (Order $record) => $record->address?->address_line1)
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('address_line2')
                        ->label(__('sn-profile::profile.address.address_line2'))
                        ->formatStateUsing(fn (Order $record) => $record->address?->address_line2)
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('postal_code')
                        ->label(__('sn-profile::profile.address.postal_code'))
                        ->formatStateUsing(fn (Order $record) => $record->address?->postal_code)
                        ->maxLength(20),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
