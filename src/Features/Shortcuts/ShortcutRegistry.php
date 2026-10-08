<?php

namespace Wsmallnews\Order\Features\Shortcuts;

use Wsmallnews\Order\Contracts\Shortcuts\ShortcutInterface;
use Wsmallnews\Order\Exceptions\OrderException;
use Wsmallnews\Order\Models\Order as OrderModel;

/**
 * 订单配方（Shortcut）注册表（容器单例，Facades\ShortcutRegistry 代理）。
 *
 * 职责：订单类型 → 配方类的注册/解析/反查——消费模块（shop 等）在
 * ServiceProvider 里 registerShortcut()，OrderCreate 按订单类型解析配方；
 * 退款/失效等逆向流程经订单实体的 type 反查同一配方。
 */
class ShortcutRegistry
{
    /**
     * 已注册配方：订单类型 => 配方类名
     *
     * @var array<string, class-string<ShortcutInterface>>
     */
    protected array $shortcuts = [];

    /**
     * 注册订单配方（消费模块 boot 时调用）
     *
     * @param  class-string<ShortcutInterface>  $shortcut
     */
    public function registerShortcut(string $type, string $shortcut): static
    {
        $this->shortcuts[$type] = $shortcut;

        return $this;
    }

    /**
     * 按订单类型解析配方
     */
    public function shortcut(string $type): ShortcutInterface
    {
        $shortcut = $this->shortcuts[$type] ?? null;

        if (! $shortcut) {
            throw new OrderException("Order shortcut [{$type}] is not registered.");
        }

        return app($shortcut);
    }

    /**
     * 从订单实体反查配方（订单类型 → 注册表）
     */
    public function resolveShortcut(OrderModel $order): ShortcutInterface
    {
        return $this->shortcut($order->type);
    }

    /**
     * 已注册的订单类型
     *
     * @return list<string>
     */
    public function registeredShortcuts(): array
    {
        return array_keys($this->shortcuts);
    }
}
