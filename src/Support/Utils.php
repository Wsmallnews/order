<?php

declare(strict_types=1);

namespace Wsmallnews\Order\Support;

use Wsmallnews\Order\Exceptions\OrderException;
ss
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
