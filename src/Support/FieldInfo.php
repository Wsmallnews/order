<?php

namespace Wsmallnews\Order\Support;

use Illuminate\Support\HtmlString;

/**
 * fields_infos 展示字段集：填充 + 解析双职责（数据契约的唯一所有者）。
 *
 * 填充侧（make）：管道计价时产出条目；
 * 解析侧（sorted / render）：视图循环渲染前排序、单条组合为整行 HtmlString——
 * 视图对数据结构零感知，加字段/换语言/改金额格式/调颜色只动本类。
 *
 * 数据契约（JSON 落库结构，新增计价项免改前端）：
 * - text / desc 存**翻译键**而非译文：渲染时 __() 求值（text_params 占位参数），
 *   切换语言即时生效；键缺失时 Laravel 原样返回，存量裸文本自动兼容
 * - field_type=amount（默认）时 value 存**整数分**，渲染走 sn_money 拆分格式化；
 *   其他类型（text 等）value 存展示值原样输出
 * - order_column：跨管道的排序锚（商品 1 / 运费 2 / 优惠券 3...），sorted() 按其归位
 * - high_light：true → 主题色 + 加粗（sn-primary-text）；false → 常规正文色（sn-neutral-text）
 */
class FieldInfo
{
    /**
     * @param  int|string  $value  amount 类型存整数分；其他类型存展示值
     * @param  array<string, mixed>  $textParams  翻译占位参数（如 ['num' => 2]）
     * @param  array<string, mixed>  $descParams
     * @return array{field_name: string, field_type: string, text: string, text_params: array<string, mixed>, desc: string, desc_params: array<string, mixed>, value: int|string, order_column: int, high_light: bool}
     */
    public static function make(
        string $fieldName,
        string $textKey,
        int | string $value,
        int $orderColumn = 0,
        bool $highLight = false,
        array $textParams = [],
        string $descKey = '',
        array $descParams = [],
        string $fieldType = 'amount',
    ): array {
        return [
            'field_name' => $fieldName,
            'field_type' => $fieldType,
            'text' => $textKey,
            'text_params' => $textParams,
            'desc' => $descKey,
            'desc_params' => $descParams,
            'value' => $value,
            'order_column' => $orderColumn,
            'high_light' => $highLight,
        ];
    }

    /**
     * 按 order_column 排序（跨管道合并顺序不可控，展示顺序与注册顺序解耦）
     *
     * @param  array<string, array>  $fields
     * @return array<int, array>
     */
    public static function sorted(array $fields): array
    {
        return collect($fields)
            ->sortBy(fn (array $item) => (int) ($item['order_column'] ?? 0))
            ->values()
            ->all();
    }

    /**
     * 单条渲染为整行 HtmlString（label+desc 左、value 右；行内金额场景，着重显示用 amount 组件）
     *
     * @param  array<string, mixed>  $item
     */
    public static function render(array $item): HtmlString
    {
        $textClass = (bool) ($item['high_light'] ?? false)
            ? 'sn-primary-text font-bold'
            : 'sn-neutral-text';

        $descHtml = filled($item['desc'] ?? '')
            ? sprintf(
                '<div class="text-sm sn-descript-text ml-2.5">(%s)</div>',
                e(__($item['desc'], $item['desc_params'] ?? [])),
            )
            : '';

        return new HtmlString(sprintf(
            '<div class="flex w-full items-center"><div class="flex flex-1 items-center"><div class="text-base %s">%s</div>%s</div><div class="flex flex-1 justify-end %s">%s</div></div>',
            $textClass,
            e(__($item['text'] ?? '', $item['text_params'] ?? [])),
            $descHtml,
            $textClass,
            static::renderValue($item),
        ));
    }

    /**
     * 值渲染：amount → 符号 + 千分位金额（订单默认币种）；其他类型原样（转义）
     *
     * @param  array<string, mixed>  $item
     */
    protected static function renderValue(array $item): string
    {
        $value = $item['value'] ?? 0;

        if (($item['field_type'] ?? 'amount') !== 'amount') {
            return e((string) $value);
        }

        $parts = sn_money()->formatParts((int) $value);

        return e($parts['symbol'] . $parts['amount']);
    }
}
