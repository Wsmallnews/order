<?php

namespace Wsmallnews\Order;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Wsmallnews\Order\Contracts\BuyerInterface;
use Wsmallnews\Order\Contracts\Shortcuts\ShortcutInterface;
use Wsmallnews\Order\Enums\Order\DeliveryStatus;
use Wsmallnews\Order\Enums\Order\PayStatus;
use Wsmallnews\Order\Enums\Order\RefundStatus;
use Wsmallnews\Order\Enums\Order\Status as OrderStatus;
use Wsmallnews\Order\Exceptions\OrderCreateException;
use Wsmallnews\Order\Models\Order;
use Wsmallnews\Support\Exceptions\SupportException;

class OrderCreate
{
    public $rocket = null;

    /**
     * 传入参数
     *
     * @var array
     */
    public $params = [];

    public $calc_type = 'create';

    /**
     * @var ShortcutInterface
     */
    public $shortcut = null;

    // // public $money = 0;

    public $order_type = 'product';

    // public $scope_type = 'shop';
    // public $store_id = 0;

    // // public $goodsList = [];

    public ?BuyerInterface $buyer = null;

    public $request_identify = '';

    public function __construct($order_type, $buyer)
    {
        // $params['store_id'] = $params['store_id'] ?? 0;
        // $params['scope_type'] = $params['store_id'] ? 'store' : 'shop';

        $this->order_type = $order_type;
        // $this->store_id = $params['store_id'];
        // $this->scope_type = $params['scope_type'];

        $this->buyer = $buyer;
    }

    public function setParams($params)
    {
        $this->params = $params;

        // 生成本次请求唯一标识
        $this->request_identify = md5(client_unique() . ':' . time() . ':' . ($this->buyer ? $this->buyer->morphType() . ':' . $this->buyer->morphId() : 'anonymous:0') . ':' . json_encode($params));

        return $this;
    }

    /**
     * 设置 shortcut
     *
     * @return void
     */
    public function setShortcut(ShortcutInterface $shortcut)
    {
        $this->shortcut = $shortcut;

        return $this;
    }

    // public function setProducts($products): OrderCreate
    // {
    //     $this->params['products'] = $products;

    //     return $this;
    // }

    public function start($products = [])
    {
        // if ($products) {
        //     $this->setProducts($products);
        // }

        $this->rocket = (new OrderRocket)->setRadars([
            'buyer' => $this->buyer,
            'calc_type' => $this->calc_type,
            'request_identify' => $this->request_identify,
            'order_type' => $this->order_type,
        ])->setParams($this->params);

        // 获取 pipes
        $getPipes = $this->shortcut->getGetPipes();

        $this->rocket = Pipeline::send($this->rocket)
            ->through($getPipes)
            ->via('get')
            ->then(function ($rocket) {
                return $rocket;
            });
    }

    public function check()
    {
        // 获取 pipes
        $checkPipes = $this->shortcut->getCheckPipes();

        $this->rocket = Pipeline::send($this->rocket)
            ->through($checkPipes)
            ->via('check')
            ->then(function ($rocket) {
                return $rocket;
            });
    }

    public function calcAmount()
    {
        // 获取 pipes
        $calcPipes = $this->shortcut->getCalcPipes();

        $this->rocket = Pipeline::send($this->rocket)
            ->through($calcPipes)
            ->via('calc')
            ->then(function ($rocket) {
                return $rocket;
            });
    }

    public function summary()
    {
        // 获取 pipes
        $summaryPipes = $this->shortcut->getSummaryPipes();

        $this->rocket = Pipeline::send($this->rocket)
            ->through($summaryPipes)
            ->via('summary')
            ->then(function ($rocket) {
                return $rocket;
            });
    }

    /**
     * 计算订单
     *
     * @return OrderRocket
     */
    public function calc($calc_type = 'calc')
    {
        $this->calc_type = $calc_type;
        // 检查系统必要条件
        // check_env(['bcmath', 'queue']);

        $this->start();

        $this->check();

        // 计算订单各种费用
        $this->calcAmount();

        // summary
        $this->summary();

        return $this->rocket;
    }

    /**
     * 获取订单可用优惠券
     *
     * @return OrderRocket
     */
    // public function getCoupons($calc_type = 'coupon')
    // {
    //     $this->calc_type = $calc_type;
    //     // 检查系统必要条件
    //     // check_env(['bcmath', 'queue']);

    //     $this->start();

    //     $this->check();

    //     // 计算订单各种费用
    //     $this->calcAmount();

    //     // summary
    //     $this->summary();

    //     $couponCalcManager = new CouponCalcManager($this->rocket);
    //     // 获取全部可用优惠券
    //     return $couponCalcManager->getCoupons();
    // }

    public function create($rocket)
    {
        try {
            DB::transaction(function () use ($rocket) {
                // 获取 pipes
                $creatingPipes = $this->shortcut->getCreatingPipes();

                $this->rocket = Pipeline::send($rocket)
                    ->through($creatingPipes)
                    ->via('creating')
                    ->then(function (OrderRocket $rocket) {
                        $order = $this->saveOrder($rocket);

                        $rocket->setRadar('order', $order);

                        $this->shortcut->save($rocket);

                        return $rocket;
                    });
            });
        } catch (SupportException $e) {
            // 下单失败，检测并返还锁定的库存
            // $this->shortcut->failBack($rocket);
            throw new OrderCreateException($e->getMessage());
        } catch (\Exception $e) {
            // 下单失败，检测并返还锁定的库存
            // $this->shortcut->failBack($rocket);
            // format_log_error($e, 'OrderCreate');        // 记录日志
            throw new OrderCreateException($e->getMessage());
        }

        // 获取订单
        $order = $this->rocket->getRadar('order', null);

        // 订单创建后
        // $hookData = [
        //     'order' => $order,
        // ];
        // \think\Hook::listen('order_create_after', $hookData);

        return $order;
    }

    public function saveOrder(OrderRocket $rocket)
    {
        $radars = $rocket->getRadars();

        $order = new Order;

        // $order->scope_type = $this->scope_type;
        // $order->store_id = $this->store_id;
        $order->type = $this->order_type;

        $order->order_sn = get_sn($this->buyer ? $this->buyer->id : 0);
        $order->buyer_type = $this->buyer?->morphType() ?? 'anonymous';
        $order->buyer_id = $this->buyer?->morphId() ?? 0;

        // [original_product_amount, original_delivery_amount, original_product_attribute_amount]
        $order->original_amount_fields = $radars['original_amount_fields'];
        // [product_amount, delivery_amount, product_attribute_amount]
        $order->amount_fields = $radars['amount_fields'];

        $order->relate_original_amount = $radars['relate_original_amount'];
        $order->relate_amount = $radars['relate_amount'];

        $order->order_amount = $radars['order_amount'];
        $order->score_amount = $radars['score_amount'] ?? 0;

        $order->remark = $this->params['remark'] ?? null;

        $order->status = OrderStatus::Unpaid;

        // 订单总优惠金额
        $order->discount_amount = $radars['discount_amount'];
        // [coupon_discount_fee]
        $order->discount_fields = $radars['discount_fields'];

        // 订单应支付金额
        $order->pay_fee = $radars['pay_fee'];
        $order->original_pay_fee = $radars['pay_fee'];
        $order->remain_pay_fee = $radars['pay_fee'];

        $order->pay_status = PayStatus::Unpaid;

        // $order->delivery_types = join(',', $radars['delivery_types']);
        $order->delivery_status = DeliveryStatus::WaitingSend;
        $order->refund_status = RefundStatus::Unrefund;

        $order->fields_infos = $radars['fields_infos'];

        $options = [];
        $order->options = array_merge($options, $radars['options'] ?? []);

        $order->platform = $this->params['platform'] ?? null;

        $order->save();

        // 重新查询订单
        $order = Order::findOrFail($order->id);

        // $invoice_status = 0;    // 没有申请
        // if ($result['invoice_status'] && $this->invoice_id) {
        //     // 可开具，并且申请了
        //     $invoice_status = 1;
        // } else if ($result['invoice_status'] == 0) {
        //     // 不可开具
        //     $invoice_status = -1;
        // }
        // $ext['invoice_status'] = $invoice_status;        // 可开具发票，并且申请了

        // $orderData['ext'] = $payloads['ext_fields'] ?? [];
        // $orderData['platform'] = request()->header('platform');

        // 这里保存的

        // // 添加收货地址信息
        // $need_address = $payloads['need_address'];
        // if ($need_address) {
        //     $user_address = $payloads['user_address'] ?? null;
        //     $this->saveOrderAddress($order, $user_address);
        // }

        return $order;
    }

    /**
     * 添加收货地址信息
     *
     * @param  \think\Model  $order
     * @param  array  $result
     * @return void
     */
    private function saveOrderAddress($order, $user_address)
    {
        // 保存收货地址
        $orderAddress = new Address;
        $orderAddress->order_id = $order->id;
        $orderAddress->user_id = $this->user ? $this->user->id : 0;
        $orderAddress->consignee = $user_address->consignee;
        $orderAddress->gender = $user_address->gender;
        $orderAddress->mobile = $user_address->mobile;
        $orderAddress->province_name = $user_address->province_name;
        $orderAddress->city_name = $user_address->city_name;
        $orderAddress->district_name = $user_address->district_name;
        $orderAddress->address = $user_address->address;
        $orderAddress->street_number = $user_address->street_number;
        $orderAddress->province_id = $user_address->province_id;
        $orderAddress->city_id = $user_address->city_id;
        $orderAddress->district_id = $user_address->district_id;
        $orderAddress->latitude = $user_address->latitude;
        $orderAddress->longitude = $user_address->longitude;
        $orderAddress->save();
    }
}
