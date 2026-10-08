<?php

namespace Wsmallnews\Order\Filament\Resources\Orders;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Wsmallnews\Order\Filament\Resources\Orders\Schemas\OrderForm;
use Wsmallnews\Order\Filament\Resources\Orders\Tables\OrdersTable;
use Wsmallnews\Order\Support\Utils;

abstract class BaseResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ShoppingBag;

    protected static ?string $slug = 'orders';

    protected static ?string $recordTitleAttribute = 'order_sn';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return Utils::getOrderModel();
    }

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-order::order.order_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-order::order.order_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? __('sn-order::order.order_resource.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-order::order.global_default.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(OrderForm::components());
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }
}
