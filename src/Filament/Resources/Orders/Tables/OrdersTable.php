<?php

namespace Wsmallnews\Order\Filament\Resources\Orders\Tables;

use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wsmallnews\Order\Enums\Order\DeliveryStatus;
use Wsmallnews\Order\Enums\Order\PayStatus;
use Wsmallnews\Order\Enums\Order\Status as OrderStatus;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\Support\Filament\Filters\FilterComponents;
use Wsmallnews\Support\Filament\Tables\ColumnComponents;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                static::idColumn(),
                static::orderSnColumn(),
                static::buyerColumn(),
                static::orderAmountColumn(),
                static::payStatusColumn(),
                static::statusColumn(),
                static::deliveryStatusColumn(),
                static::createdAtColumn(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['buyer', 'address']))
            ->defaultSort('id', 'desc')
            ->searchPlaceholder(__('sn-order::order.order_table.search_placeholder'))
            ->filtersFormWidth(Width::Medium)
            ->filters([
                FilterComponents::statusFilter(PayStatus::class, 'pay_status', __('sn-order::order.order_table.pay_status')),
                FilterComponents::statusFilter(OrderStatus::class, 'status', __('sn-order::order.order_table.status')),
                FilterComponents::statusFilter(DeliveryStatus::class, 'delivery_status', __('sn-order::order.order_table.delivery_status')),
                ...FilterComponents::createUpdateRangeFilter(),
            ])
            ->recordActions([
                ...ActionComponents::recordActions([
                    EditAction::make(),
                ]),
            ]);
    }

    protected static function idColumn(): TextColumn
    {
        return TextColumn::make('id')
            ->label(__('sn-order::order.order_table.id'))
            ->searchable()
            ->sortable()
            ->alignCenter()
            ->toggleable();
    }

    protected static function orderSnColumn(): TextColumn
    {
        return TextColumn::make('order_sn')
            ->label(__('sn-order::order.order_table.order_sn'))
            ->searchable()
            ->copyable();
    }

    protected static function buyerColumn(): TextColumn
    {
        return ColumnComponents::morphColumn(
            'buyer_type',
            __('sn-order::order.order_table.buyer'),
            fn ($record) => $record->buyer,
            fn ($record) => $record->buyer_type,
            fn ($record) => $record->buyer_id,
        );
    }

    protected static function orderAmountColumn(): TextColumn
    {
        return TextColumn::make('order_amount')
            ->label(__('sn-order::order.order_table.order_amount'))
            ->formatStateUsing(fn ($record) => sn_money()->format($record->order_amount))
            ->sortable();
    }

    protected static function payStatusColumn(): TextColumn
    {
        return TextColumn::make('pay_status')
            ->label(__('sn-order::order.order_table.pay_status'))
            ->badge()
            ->alignCenter();
    }

    protected static function statusColumn(): TextColumn
    {
        return TextColumn::make('status')
            ->label(__('sn-order::order.order_table.status'))
            ->badge()
            ->alignCenter();
    }

    protected static function deliveryStatusColumn(): TextColumn
    {
        return TextColumn::make('delivery_status')
            ->label(__('sn-order::order.order_table.delivery_status'))
            ->badge()
            ->alignCenter()
            ->toggleable();
    }

    protected static function createdAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('sn-order::order.order_table.created_at'))
            ->sortable()
            ->toggleable();
    }
}
