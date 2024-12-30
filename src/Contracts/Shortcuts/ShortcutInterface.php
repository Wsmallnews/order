<?php

namespace Wsmallnews\Order\Contracts\Shortcuts;

use Illuminate\Support\Collection;
use Wsmallnews\Order\OrderRocket;

interface ShortcutInterface
{

    /**
     * 获取项目的 pipes
     *
     * @return array
     */
    public function getGetPipes(): array;


    /**
     * 获取检测的 pipes
     *
     * @return array
     */
    public function getCheckPipes(): array;


    /**
     * 获取计算的 pipes
     *
     * @return array
     */
    public function getCalcPipes(): array;


    /**
     * 获取 summary pipes
     *
     * @return array
     */
    public function getSummaryPipes(): array;


    /**
     * 获取创建的 pipes
     *
     * @return array
     */
    public function getCreatingPipes(): array;


    /**
     * 获取退款的 pipes
     *
     * @return array
     */
    // public function getRefundPipes(): array;


    /**
     * 获取失效的 pipes
     *
     * @return array
     */
    // public function getInvalidPipes(): array;

    /**
     * 存储订单相关的附表
     *
     * @param OrderRocket $rocket
     * @return Collection
     */
    public function save(OrderRocket $rocket): Collection;


    /**
     * 失败时执行此方法
     *
     * @param OrderRocket $rocket
     * @return void
     */
    // public function failBack(OrderRocket $rocket): void;


}
