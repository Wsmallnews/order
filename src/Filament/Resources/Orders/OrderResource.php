<?php

namespace Wsmallnews\Order\Filament\Resources\Orders;

use Closure;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Wsmallnews\Order\Filament\Resources\Orders\Pages\EditOrder;
use Wsmallnews\Order\Filament\Resources\Orders\Pages\ListOrders;
use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\Support\Filament\Resources\ResourceConfiguration;

final class OrderResource extends BaseResource
{
    use CanBeConfigured;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        $resolveForm = self::resolveCustomProperty('form');

        return $resolveForm instanceof Closure ? $resolveForm($schema, self::class) : parent::form($schema);
    }

    public static function table(Table $table): Table
    {
        $resolveTable = self::resolveCustomProperty('table');

        return $resolveTable instanceof Closure ? $resolveTable($table, self::class) : parent::table($table);
    }
}
