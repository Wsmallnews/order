<?php

namespace Wsmallnews\Order;

use Wsmallnews\Support\Contracts\HasSnIdentifiable;
use Wsmallnews\Support\Rocket;

/**
 * 订单管道数据总线。
 *
 * 金额纪律（货币规范）：radars/buyInfo 中的金额一律整数分（int），
 * 累加走 radarAdditionMinor；模型金额赋值在落单处统一 fromMinor。
 */
class OrderRocket extends Rocket
{
    /**
     * Radar key 常量：管道间传值的定位锚（裸字符串 key 是拼错重灾区）
     */
    public const RADAR_BUYER = 'buyer';

    public const RADAR_CALC_TYPE = 'calc_type';

    public const RADAR_REQUEST_IDENTIFY = 'request_identify';

    public const RADAR_ORDER_TYPE = 'order_type';

    public const RADAR_RELATE_ITEMS = 'relate_items';

    public const RADAR_ORDER = 'order';

    public const RADAR_ORDER_ITEMS = 'order_items';

    /**
     * 获取当前购买者（身份统一约束 HasSnIdentifiable；morph 原生 getMorphClass/getKey）
     */
    public function getBuyer(): ?HasSnIdentifiable
    {
        return $this->getRadar(self::RADAR_BUYER);
    }

    /**
     * 设置当前购买者
     */
    public function setBuyer(?HasSnIdentifiable $buyer): Rocket
    {
        $this->mergeRadars([self::RADAR_BUYER => $buyer]);

        return $this;
    }

    /**
     * 获取当前计算类型（calc=试算，create=落单）
     */
    public function getCalcType(): string
    {
        return $this->getRadar(self::RADAR_CALC_TYPE, 'calc');
    }

    /**
     * 设置当前计算类型
     */
    public function setCalcType(string $calcType): Rocket
    {
        $this->mergeRadars([self::RADAR_CALC_TYPE => $calcType]);

        return $this;
    }

    /**
     * 获取relate列表
     */
    public function getRelateItems(): array
    {
        return $this->getRadar(self::RADAR_RELATE_ITEMS, []);
    }

    /**
     * 设置 relate 列表
     *
     * @param  array<int, array<string, mixed>>  $relateItems
     */
    public function setRelateItems(array $relateItems): Rocket
    {
        $this->mergeRadars([self::RADAR_RELATE_ITEMS => $relateItems]);

        return $this;
    }

    /**
     * 累加 radar 中的金额字段（整数分口径）
     */
    public function radarAdditionMinor(string $field, int $minor): Rocket
    {
        $current = (int) $this->getRadar($field, 0);
        $this->setRadar($field, $current + $minor);

        return $this;
    }

    /**
     * 读取 radar 金额字段为整数分
     */
    public function radarMinor(string $field): int
    {
        return (int) $this->getRadar($field, 0);
    }
}
