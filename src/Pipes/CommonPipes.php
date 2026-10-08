<?php

namespace Wsmallnews\Order\Pipes;

use Wsmallnews\Order\Pipes\Common\Calc\Start as CalcStartPipe;
use Wsmallnews\Order\Pipes\Common\Creating\Start as CreatingStartPipe;
use Wsmallnews\Order\Pipes\Common\Fetch\Start as FetchStartPipe;
use Wsmallnews\Order\Pipes\Common\Summary\Start as SummaryStartPipe;

/**
 * 通用管道聚合器：订单域基建管道（不感知业务实体）的快捷引用。
 *
 * 配方基类默认嵌入这些管道段，子配方只需声明业务段管道；
 * 引擎不强制注入——清单的单一事实源保持在配方类里。
 */
class CommonPipes
{
    /**
     * @return array<string, class-string>
     */
    public static function fetch(): array
    {
        return ['start' => FetchStartPipe::class];
    }

    /**
     * @return array<string, class-string>
     */
    public static function check(): array
    {
        return [];
    }

    /**
     * @return array<string, class-string>
     */
    public static function calc(): array
    {
        return ['start' => CalcStartPipe::class];
    }

    /**
     * @return array<string, class-string>
     */
    public static function summary(): array
    {
        return ['start' => SummaryStartPipe::class];
    }

    /**
     * @return array<string, class-string>
     */
    public static function creating(): array
    {
        return ['start' => CreatingStartPipe::class];
    }
}
