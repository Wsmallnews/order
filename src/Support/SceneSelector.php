<?php

namespace Wsmallnews\Order\Support;

/**
 * 场景选择器（OrderPipes::scene('product') 的链式句柄）
 */
class SceneSelector
{
    public function __construct(protected string $scene) {}

    /**
     * 选择阶段
     */
    public function stage(string $stage): StageSelector
    {
        return new StageSelector($this->scene, $stage);
    }
}
