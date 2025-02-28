<?php

namespace Wsmallnews\Order;

use Wsmallnews\Order\Contracts\BuyerInterface;
use Wsmallnews\Support\Rocket;

class OrderRocket extends Rocket
{
    /**
     * 获取当前购买者
     *
     * @return BuyerInterface|null
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
     *
     * @return string
     */
    public function getCalcType()
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
     * 获取商品列表
     */
    public function getRelateItems(): array
    {
        return $this->getRadar('relate_items', []);
    }

    public function setRelateItems($relateItems): Rocket
    {
        $data = ['relate_items' => $relateItems];
        $this->mergeRadars($data);

        return $this;
    }

    public function radarAdditionAmount($field, $value): Rocket
    {
        if ($this->getRadar($field)) {
            $value = bcadd((string) $this->getRadar($field), (string) $value, 2);
        }

        $this->setRadar($field, number_format((float) $value, 2, '.', ''));

        return $this;
    }

    // /**
    //  * 获取并保存支付管理类
    //  *
    //  * @param \Closure $callback
    //  * @return PayManager
    //  */
    // public function getPayManager(\Closure $callback)
    // {
    //     $payManager = $this->getRadar('pay_manager', null);
    //     if (!$payManager) {
    //         $payManager = $callback();
    //         $this->mergeRadars([
    //             'pay_manager' => $payManager
    //         ]);
    //     }

    //     return $payManager;
    // }
}
