<?php

namespace Wsmallnews\Order\Support;

/**
 * 订单管道注册表：跨包无侵入扩展点。
 *
 * 扩展包（coupon/会员价/库存 等）在自己的 ServiceProvider 里按
 * 「场景(type) + 阶段(stage) + key 锚点」插拔管道，不改动配方源码：
 *
 *     OrderPipes::scene('product')->stage('calc')->after('product', CouponCalcPipe::class, 'coupon');
 *
 * - scene：场景标识 = orders.type 值（shop 注册配方时声明的 type）
 * - stage：阶段（fetch/check/calc/summary/creating/refund/invalid）
 * - key：配方基础清单里的管道 key（'start'、'product' 等），作为定位锚点
 *
 * 支持位置操作：after / before / replace / remove / prepend / append。
 * 同一注册点重复注册以最后一次为准；操作目标 key 不存在时静默跳过
 * （基础清单由配方决定，扩展包不应假设配方细节）。
 */
class OrderPipes
{
    public const STAGE_FETCH = 'fetch';

    public const STAGE_CHECK = 'check';

    public const STAGE_CALC = 'calc';

    public const STAGE_SUMMARY = 'summary';

    public const STAGE_CREATING = 'creating';

    public const STAGE_REFUND = 'refund';

    public const STAGE_INVALID = 'invalid';

    /**
     * 全部注册操作：[scene][stage] => Collection<操作>
     *
     * @var array<string, array<string, array<int, array{op: string, key: string, pipe: ?string, as: ?string}>>>
     */
    protected static array $operations = [];

    /**
     * 选择场景
     */
    public static function scene(string $type): SceneSelector
    {
        return new SceneSelector($type);
    }

    /**
     * 注册一个操作（由选择器调用）
     */
    public static function addOperation(string $scene, string $stage, string $op, string $key, ?string $pipe, ?string $as): void
    {
        static::$operations[$scene][$stage][] = [
            'op' => $op,
            'key' => $key,
            'pipe' => $pipe,
            'as' => $as,
        ];
    }

    /**
     * 应用注册操作到基础清单（配方侧调用）
     *
     * @param  array<string, class-string>  $pipes  基础清单（key => 管道类）
     * @return array<string, class-string>
     */
    public static function apply(string $scene, string $stage, array $pipes): array
    {
        foreach (static::$operations[$scene][$stage] ?? [] as $operation) {
            $pipes = static::applyOperation($pipes, $operation);
        }

        return $pipes;
    }

    /**
     * 清空注册表（测试用）
     */
    public static function flush(): void
    {
        static::$operations = [];
    }

    /**
     * @param  array<string, class-string>  $pipes
     * @param  array{op: string, key: string, pipe: ?string, as: ?string}  $operation
     * @return array<string, class-string>
     */
    protected static function applyOperation(array $pipes, array $operation): array
    {
        $key = $operation['key'];
        $pipe = $operation['pipe'];
        $as = $operation['as'] ?? ($pipe ? class_basename($pipe) : $key);

        switch ($operation['op']) {
            case 'prepend':
                return [$as => $pipe, ...$pipes];

            case 'append':
                return [...$pipes, $as => $pipe];

            case 'replace':
                if (! array_key_exists($key, $pipes)) {
                    return $pipes;
                }

                $replaced = [];
                foreach ($pipes as $k => $v) {
                    $replaced[$k === $key ? $as : $k] = $k === $key ? $pipe : $v;
                }

                return $replaced;

            case 'remove':
                unset($pipes[$key]);

                return $pipes;

            case 'after':
            case 'before':
                if (! array_key_exists($key, $pipes) || ! $pipe) {
                    return $pipes;
                }

                $inserted = [];
                foreach ($pipes as $k => $v) {
                    if ($operation['op'] === 'before' && $k === $key) {
                        $inserted[$as] = $pipe;
                    }

                    $inserted[$k] = $v;

                    if ($operation['op'] === 'after' && $k === $key) {
                        $inserted[$as] = $pipe;
                    }
                }

                return $inserted;
        }

        return $pipes;
    }
}
