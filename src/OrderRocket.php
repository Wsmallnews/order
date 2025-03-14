<?php

namespace Wsmallnews\Order;

use Wsmallnews\Order\Contracts\BuyerInterface;
use Wsmallnews\Support\Rocket;

class OrderRocket extends Rocket
{
    /**
     * 获取当前购买者
     */
    public function getBuyer(): ?BuyerInterface
    {
        return $this->getRadar('buyer');
    }

    /**
     * 设置当前用户
     *
     * @param  BuyerInterface  $buyer
     */
    public function setBuyer($buyer): Rocket
    {
        $this->mergeRadars(['buyer' => $buyer]);

        return $this;
    }

    /**
     * 获取当前计算类型
     */
    public function getCalcType(): string
    {
        return $this->getRadar('calc_type');
    }

    /**
     * 设置当前计算类型
     *
     * @param  string  $calc_type
     */
    public function setCalcType($calc_type): Rocket
    {
        $this->mergeRadars(['calc_type' => $calc_type]);

        return $this;
    }

    /**
     * 获取relate列表
     */
    public function getRelateItems(): array
    {
        return $this->getRadar('relate_items', []);
    }

    /**
     * 设置 relate 列表
     *
     * @param  array  $relateItems
     */
    public function setRelateItems($relateItems): Rocket
    {
        $data = ['relate_items' => $relateItems];
        $this->mergeRadars($data);

        return $this;
    }

    /**
     * 累加 radar 中的字段的值
     *
     * @param  mixed  $field
     * @param  mixed  $value
     */
    public function radarAdditionAmount($field, $value): Rocket
    {
        if ($this->getRadar($field)) {
            $value = sn_currency()->add($this->getRadar($field), $value);
        }

        $this->setRadar($field, sn_currency()->parseMoney($value));

        return $this;
    }
}
