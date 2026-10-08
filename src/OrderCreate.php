<?php

namespace Wsmallnews\Order;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Wsmallnews\Order\Contracts\Shortcuts\ShortcutInterface;
use Wsmallnews\Order\Enums\Order\DeliveryStatus;
use Wsmallnews\Order\Enums\Order\PayStatus;
use Wsmallnews\Order\Enums\Order\RefundStatus;
use Wsmallnews\Order\Enums\Order\Status as OrderStatus;
use Wsmallnews\Order\Exceptions\OrderCreateException;
use Wsmallnews\Order\Features\Shortcuts\ShortcutRegistry;
use Wsmallnews\Order\Models\Order;
use Wsmallnews\Order\Models\OrderAddress;
use Wsmallnews\Order\Support\OrderPipes;
use Wsmallnews\Profile\Support\Utils as ProfileUtils;
use Wsmallnews\Support\Contracts\HasSnIdentifiable;
use Wsmallnews\Support\Exceptions\SupportException;

/**
 * 订单创建引擎：阶段化管道执行器（配方无感知）。
 *
 * 阶段顺序：fetch（取数）→ check（校验）→ calc（计价）→ summary（汇总），
 * create 时在事务内追加 creating 管道 + 落单 + 配方附表。
 * 管道清单 = 配方基础清单经 OrderPipes 注册表加工（跨包插拔）。
 */
class OrderCreate
{
    public ?OrderRocket $rocket = null;

    /**
     * 传入参数
     *
     * @var array<string, mixed>
     */
    public array $params = [];

    /**
     * calc=试算（确认页展示），create=落单
     */
    public string $calc_type = 'create';

    /**
     * 订单类型（orders.type，同时是管道注册表的场景标识）
     */
    public string $order_type;

    public ?HasSnIdentifiable $buyer = null;

    protected ?ShortcutInterface $shortcut = null;

    public string $request_identify = '';

    public function __construct(string $order_type, ?HasSnIdentifiable $buyer = null)
    {
        $this->order_type = $order_type;
        $this->buyer = $buyer;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;

        // 生成本次请求唯一标识
        $this->request_identify = md5(client_unique() . ':' . time() . ':' . ($this->buyer ? $this->buyer->getMorphClass() . ':' . $this->buyer->getKey() : 'anonymous:0') . ':' . json_encode($params));

        return $this;
    }

    /**
     * 显式设置配方（缺省时按 order_type 从场景注册表解析）
     */
    public function setShortcut(ShortcutInterface $shortcut): static
    {
        $this->shortcut = $shortcut;

        return $this;
    }

    public function getShortcut(): ShortcutInterface
    {
        return $this->shortcut ??= app(ShortcutRegistry::class)->shortcut($this->order_type);
    }

    /**
     * 计算订单（fetch → check → calc → summary）
     */
    public function calc(string $calc_type = 'calc'): OrderRocket
    {
        $this->calc_type = $calc_type;

        $this->fetch();
        $this->check();
        $this->calcAmount();
        $this->summary();

        return $this->rocket;
    }

    /**
     * 取数阶段
     */
    public function fetch(): void
    {
        $this->bootRocket();

        $pipes = OrderPipes::apply($this->order_type, OrderPipes::STAGE_FETCH, $this->getShortcut()->getFetchPipes());

        $this->rocket = $this->pipeline($pipes, 'fetch');
    }

    /**
     * 校验阶段
     */
    public function check(): void
    {
        $pipes = OrderPipes::apply($this->order_type, OrderPipes::STAGE_CHECK, $this->getShortcut()->getCheckPipes());

        $this->rocket = $this->pipeline($pipes, 'check');
    }

    /**
     * 计价阶段
     */
    public function calcAmount(): void
    {
        $pipes = OrderPipes::apply($this->order_type, OrderPipes::STAGE_CALC, $this->getShortcut()->getCalcPipes());

        $this->rocket = $this->pipeline($pipes, 'calc');
    }

    /**
     * 汇总阶段
     */
    public function summary(): void
    {
        $pipes = OrderPipes::apply($this->order_type, OrderPipes::STAGE_SUMMARY, $this->getShortcut()->getSummaryPipes());

        $this->rocket = $this->pipeline($pipes, 'summary');
    }

    /**
     * 落单（事务内：creating 管道 → 主单落库 → 配方附表）
     */
    public function create(OrderRocket $rocket)
    {
        try {
            DB::transaction(function () use ($rocket) {
                $pipes = OrderPipes::apply($this->order_type, OrderPipes::STAGE_CREATING, $this->getShortcut()->getCreatingPipes());

                $this->rocket = Pipeline::send($rocket)
                    ->through($pipes)
                    ->via('creating')
                    ->then(function (OrderRocket $rocket) {
                        $order = $this->saveOrder($rocket);

                        $rocket->setRadar(OrderRocket::RADAR_ORDER, $order);

                        $this->getShortcut()->save($rocket);

                        return $rocket;
                    });
            });
        } catch (SupportException $e) {
            // P2 库存扩展落地时在此挂 failBack 补偿钩子（管道锁定的资源回滚）
            throw new OrderCreateException($e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            throw new OrderCreateException($e->getMessage(), 0, $e);
        }

        return $this->rocket->getRadar(OrderRocket::RADAR_ORDER);
    }

    /**
     * 初始化数据总线
     */
    protected function bootRocket(): void
    {
        $this->rocket = (new OrderRocket)->setRadars([
            OrderRocket::RADAR_BUYER => $this->buyer,
            OrderRocket::RADAR_CALC_TYPE => $this->calc_type,
            OrderRocket::RADAR_REQUEST_IDENTIFY => $this->request_identify,
            OrderRocket::RADAR_ORDER_TYPE => $this->order_type,
        ])->setParams($this->params);
    }

    /**
     * @param  array<string, class-string>  $pipes
     */
    protected function pipeline(array $pipes, string $stage): OrderRocket
    {
        return Pipeline::send($this->rocket)
            ->through($pipes)
            ->via($stage)
            ->then(fn (OrderRocket $rocket) => $rocket);
    }

    /**
     * 主单落库（金额整数分 → fromMinor 赋值，货币规范第 6 条）
     *
     * @param  array<string, mixed>  $payloads
     */
    protected function saveOrder(OrderRocket $rocket): Order
    {
        $payloads = $rocket->getPayloads() ?? collect([]);
        $currency = sn_money()->defaultCurrency();

        $order = new Order;

        $order->scope_type = $this->params['scope_type'];
        $order->scope_id = $this->params['scope_id'];
        $order->type = $this->order_type;
        $order->currency = $currency;       // 交易币种创建时快照

        $order->order_sn = get_sn($this->buyer ? $this->buyer->getKey() : 0);
        $order->buyer_type = $this->buyer ? $this->buyer->getMorphClass() : 'anonymous';
        $order->buyer_id = $this->buyer ? $this->buyer->getKey() : 0;

        // JSON 金额明细：整数分（键 => 金额），不存小数不嵌币种
        $order->original_amount_fields = $payloads['original_amount_fields'];
        $order->amount_fields = $payloads['amount_fields'];
        $order->discount_fields = $payloads['discount_fields'];

        $order->relate_original_amount = sn_money()->fromMinor((int) $payloads['relate_original_amount'], $currency);
        $order->relate_amount = sn_money()->fromMinor((int) $payloads['relate_amount'], $currency);
        $order->order_amount = sn_money()->fromMinor((int) $payloads['order_amount'], $currency);
        $order->score_amount = (int) ($payloads['score_amount'] ?? 0);       // 积分字段（非货币）
        $order->discount_amount = sn_money()->fromMinor((int) $payloads['discount_amount'], $currency);

        $payFee = (int) $payloads['pay_fee'];
        $order->pay_fee = sn_money()->fromMinor($payFee, $currency);
        $order->original_pay_fee = sn_money()->fromMinor($payFee, $currency);
        $order->remain_pay_fee = sn_money()->fromMinor($payFee, $currency);

        $order->remark = $this->params['remark'] ?? null;
        $order->platform = $this->params['platform'] ?? null;

        $order->status = OrderStatus::Unpaid;
        $order->pay_status = PayStatus::Unpaid;
        $order->delivery_status = DeliveryStatus::WaitingSend;
        $order->refund_status = RefundStatus::Unrefund;

        // 展示性字段集（格式化串，仅展示不参与运算）
        $order->fields_infos = $payloads['fields_infos'];

        $order->options = $rocket->getRadar('options', []);

        $order->save();

        // 收货地址快照：address_id 存在时校验归属（buyer 范围）后复制为订单地址
        $addressId = (int) $rocket->getParam('address_id', 0);

        if ($addressId > 0) {
            $buyer = $rocket->getBuyer();

            $addressModel = ProfileUtils::getAddressModel();

            $address = $addressModel::query()
                ->whereKey($addressId)
                ->when($buyer, fn ($query) => $query
                    ->where('owner_type', $buyer->getMorphClass())
                    ->where('owner_id', $buyer->getKey()))
                ->first();

            if (! $address) {
                throw new SupportException(__('sn-profile::profile.errors.address_not_owned'));
            }

            OrderAddress::snapshot($order, $address);
        }

        return $order->findOrFail($order->id);
    }
}
