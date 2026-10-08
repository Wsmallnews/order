<?php

namespace Wsmallnews\Order;

use Carbon\Carbon;
use Wsmallnews\Order\Models\Order as OrderModel;
use Wsmallnews\Order\Models\OrderAction as OrderActionModel;
use Wsmallnews\Pay\Models\PayRecord;
use Wsmallnews\Pay\Models\PayRefund;

class OrderOperate
{
    /**
     * 订单信息
     *
     * @var OrderModel
     */
    protected $order = null;

    // /**
    //  * 当前关系
    //  *
    //  * @var string
    //  */
    // protected $current_relate = null;

    // /**
    //  * 订单关联项 (products items 等)
    //  *
    //  * @var null|\think\Collection
    //  */
    // protected $relates = null;

    public function __construct(OrderModel $order)
    {
        $this->order = $order;
    }

    /**
     * 订单创建成功
     *
     * @return Order
     */
    public function created()
    {
        // if (!$this->orderManager->isPaid()) {
        //     // 订单未支付成功，添加订单自动关闭队列
        //     $close_minue = estore_setting('estore_order.auto_close');
        //     $close_minue = $close_minue > 0 ? $close_minue : 0;

        //     if ($close_minue) {
        //         // 更新订单，将过期时间存入订单，前台展示支付倒计时
        //         $order_ext['expired_time'] = time() + ($close_minue * 60);

        //         \think\Queue::later(($close_minue * 60), '\addons\estore\package\order\job\OrderAutoOper@autoClose', ['order' => $this->order], 'estore');
        //     } else {
        //         $order_ext['expired_time'] = 0;
        //     }

        //     $this->order->ext = array_merge($this->order->ext, $order_ext);      // 关闭时间
        //     $this->order->save();
        // }

        // return $this->order;
    }

    /**
     * 检测并支付 订单剩余应支付金额
     */
    public function checkAndPaid(): OrderModel
    {
        $paid_fee = $this->order->getPaidFee(true);    // 加锁读获取已支付金额（整数分）

        // 剩余应支付金额（整数分；金额赋值走 Money 对象，防 cast 标量按元解析错账）
        $remain_pay_fee = max(0, sn_money()->minor(sn_money()->subtract($this->order->pay_fee, $paid_fee)));

        if ($remain_pay_fee > 0) {
            // 订单部分支付
            $this->order->remain_pay_fee = sn_money()->fromMinor($remain_pay_fee, $this->order->getPayCurrency());
            $this->order->save();

            return $this->order;
        }

        // 订单已支付
        $this->order->remain_pay_fee = sn_money()->fromMinor(0, $this->order->getPayCurrency());
        $this->order->paid_at = Carbon::now();
        $this->order->pay_status = Enums\Order\PayStatus::Paid;
        $this->order->status = Enums\Order\Status::Paid;
        $this->order->save();

        // 更新关联表的支付状态
        $this->order->orderItems->each(function ($item) {
            $item->pay_status = Enums\Item\PayStatus::Paid;
            $item->save();
        });

        // 添加 action 记录，订单支付只需要记录一条 action 即可
        OrderActionModel::add(order: $this->order, message: '用户支付成功');

        // @sn todo 支付成功后续使用异步队列处理
        // \think\Queue::push('\addons\estore\package\order\job\OrderPaid@paid', ['order' => $this->order], 'estore-high');

        return $this->order;
    }

    /**
     * 退款成功回调：按支付单累计退款额判定部分/全额，联动订单与明细退款状态
     *
     * 注意：RefundSucceeded 事件在 pay 包累计 refunded_fee 写库之前触发，
     * 判定需自行加上本次退款单金额（$refund 参数的用途）。
     *
     * @param  PayRecord  $payRecord  支付单（refunded_fee 为事件时点累计，未含本次）
     * @param  PayRefund  $refund  本次退款单
     */
    public function refunded($payRecord, $refund = null): OrderModel
    {
        $payFee = (int) sn_money()->minor($payRecord->pay_fee);

        // 事件时点累计 + 本次退款额 = 真实累计退款
        $refundedFee = (int) sn_money()->minor($payRecord->refunded_fee)
            + (int) sn_money()->minor($refund?->refund_fee ?? 0);

        $isFullRefund = $payFee > 0 && $refundedFee >= $payFee;

        $this->order->refund_status = $isFullRefund
            ? Enums\Order\RefundStatus::Refunded
            : Enums\Order\RefundStatus::Hasrefund;
        $this->order->save();

        if ($isFullRefund) {
            // 全额退款：明细同步退款状态；部分退款不动明细
            $this->order->orderItems->each(function ($item) {
                $item->refund_status = Enums\Item\RefundStatus::Refunded;
                $item->save();
            });
        }

        OrderActionModel::add(
            order: $this->order,
            message: $isFullRefund ? '订单全额退款' : '订单部分退款',
        );

        return $this->order;
    }
}
