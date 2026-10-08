<?php

namespace Wsmallnews\Order\Contracts\Shortcuts;

use Illuminate\Support\Collection;
use Wsmallnews\Order\Models\OrderItem;
use Wsmallnews\Order\OrderRocket;

/**
 * 订单配方（Shortcut）：一种订单类型的管道清单 + 附表落库。
 *
 * 阶段顺序：fetch（取数）→ check（校验）→ calc（计价）→ summary（汇总），
 * create 时事务内追加 creating 管道 + 主单落库 + save() 附表；
 * refund / invalid 为补偿阶段（P2 库存/优惠券扩展包落地后启用）。
 */
interface ShortcutInterface
{
    /**
     * 取数阶段管道
     *
     * @return array<string, class-string>
     */
    public function getFetchPipes(): array;

    /**
     * 校验阶段管道
     *
     * @return array<string, class-string>
     */
    public function getCheckPipes(): array;

    /**
     * 计价阶段管道
     *
     * @return array<string, class-string>
     */
    public function getCalcPipes(): array;

    /**
     * 汇总阶段管道
     *
     * @return array<string, class-string>
     */
    public function getSummaryPipes(): array;

    /**
     * 落单阶段管道（事务内，主单落库前）
     *
     * @return array<string, class-string>
     */
    public function getCreatingPipes(): array;

    /**
     * 退款阶段管道
     *
     * @return array<string, class-string>
     */
    public function getRefundPipes(): array;

    /**
     * 失效阶段管道
     *
     * @return array<string, class-string>
     */
    public function getInvalidPipes(): array;

    /**
     * 存储订单相关的附表
     *
     * @return Collection<int, OrderItem>
     */
    public function save(OrderRocket $rocket): Collection;
}
