<?php

namespace Wsmallnews\Order\Shortcuts;

use Wsmallnews\Order\Contracts\Shortcuts\ShortcutInterface;
use Wsmallnews\Order\Shortcuts\Concerns\SavesOrderItems;

/**
 * 配方基类（薄）：附表落库默认实现（SavesOrderItems）+ 用法范式。
 *
 * 管道清单采用**显式组合**：继承者直接实现接口方法，自己合并通用段——
 * 完整清单的单一事实源保持在配方类里，不在此处做模板方法式的隐藏拼接：
 *
 *     public function getCalcPipes(): array
 *     {
 *         return array_merge(CommonPipes::calc(), [
 *             'product' => CalcProductPipe::class,
 *         ]);
 *     }
 *
 * 注意：业务配方**必须** merge 对应阶段的通用段（Calc/Start 负责费用结构
 * 初始化与汇总，漏掉会导致下游管道找不到初始化字段）——端到端测试的金额
 * 断言是兜底防线。
 */
abstract class Base implements ShortcutInterface
{
    use SavesOrderItems;
}
