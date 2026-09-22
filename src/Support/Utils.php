<?php

declare(strict_types=1);

namespace Wsmallnews\Order\Support;

use Wsmallnews\Order\Exceptions\OrderException;
use Wsmallnews\Support\Data\ScopeableContext;
use Wsmallnews\Support\Exceptions\InvalidScopeException;
use Wsmallnews\Support\Support\Utils as SupportUtils;

/**
 * Utility class for Order package configuration and helpers.
 */
class Utils
{
    /**
     * Get configuration value.
     *
     * @param  string|null  $name  Configuration key (dot notation)
     * @param  mixed  $default  Default value if not found
     */
    public static function getConfig(?string $name = null, mixed $default = null): mixed
    {
        $config = config('sn-order');

        return $name ? (data_get($config, $name) ?? $default) : $config;
    }

    /**
     * Get scopeable configuration as ScopeableContext object.
     *
     * @param  string|null  $key  实例键（null = main 默认实例）
     *
     * @throws OrderException
     */
    public static function getScopeableContext(?string $key = null): ScopeableContext
    {
        try {
            return SupportUtils::getScopeFromInstances('sn-order.scopeables', $key);
        } catch (InvalidScopeException $e) {
            throw new OrderException('Scopeable configuration error. ' . $e->getMessage());
        }
    }

    /**
     * Get scopeable array.
     *
     * @param  string|null  $key  实例键（null = main 默认实例）
     * @return array{scope_type: string, scope_id: int}
     *
     * @throws OrderException
     */
    public static function getScopeable(?string $key = null): array
    {
        return self::getScopeableContext($key)->toArray();
    }

    /**
     * Get scope type.
     *
     * @param  string|null  $key  实例键（null = main 默认实例）
     *
     * @throws OrderException
     */
    public static function getScopeType(?string $key = null): string
    {
        return self::getScopeableContext($key)->scopeType;
    }

    /**
     * Get scope ID.
     *
     * @param  string|null  $key  实例键（null = main 默认实例）
     *
     * @throws OrderException
     */
    public static function getScopeId(?string $key = null): int
    {
        return self::getScopeableContext($key)->scopeId;
    }

    /**
     * Get panel register raw config.
     *
     * @param  string|null  $type  Register type (pages, resources, global_default) or null for all
     */
    public static function getPanelRegister(?string $type = null): mixed
    {
        if (blank($type)) {
            return self::getConfig('panel_register', null);
        }

        return self::getConfig("panel_register.{$type}", null);
    }

    /**
     * Get model class by name.
     *
     * @param  string  $name  Model name (e.g., 'order', 'order_item')
     * @param  bool  $shouldException  Whether to throw exception if not found
     *
     * @throws OrderException
     */
    public static function getModel(string $name, bool $shouldException = true): ?string
    {
        $model = self::getConfig('models')[$name] ?? null;

        if (blank($model) && $shouldException) {
            throw new OrderException("Model {$name} not found.");
        }

        return $model;
    }

    /**
     * Get Order model class.
     */
    public static function getOrderModel(): string
    {
        return self::getModel('order');
    }

    /**
     * Get OrderItem model class.
     */
    public static function getOrderItemModel(): string
    {
        return self::getModel('order_item');
    }

    /**
     * 支付记录模型（sn-order.models.pay_record，由 wsmallnews/pay 包提供）
     *
     * 未配置或类不存在时返回 null（调用方应短路，支付相关功能不可用）
     */
    public static function getPayRecordModel(): ?string
    {
        $model = self::getConfig('models.pay_record');

        return (is_string($model) && class_exists($model)) ? $model : null;
    }

    /**
     * Get file directory path with optional type and date.
     *
     * @param  string|null  $type  Directory type
     */
    public static function getFileDirectory(?string $type = null): string
    {
        return self::getConfig('file_directory', 'sn/order/') . ($type ? $type . '/' : '') . date('Ymd');
    }
}
