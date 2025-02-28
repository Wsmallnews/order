<?php

namespace Wsmallnews\Order\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Wsmallnews\Order\Enums\Order\PayStatus;
use Wsmallnews\Order\OrderOperate;
use Wsmallnews\Pay\Models\PayRecord;

trait Payable
{

    protected ?OrderOperate $orderOperate = null;


    /**
     * payable 的 scope_type
     */
    public function getScopeType(): string
    {
        return $this->scope_type;
    }


    /**
     * payable 的 scope_id
     */
    public function getScopeId(): int
    {
        return $this->scope_id;
    }


    /**
     * payable 的 scope 信息
     *
     * @return array
     */
    public function getScopeInfo(): array
    {
        return ['scope_type' => $this->scope_type, 'scope_id' => $this->scope_id];
    }

    /**
     * payable 的 type
     */
    public function morphType(): string
    {
        return $this->getMorphClass();
    }

    /**
     * payable 的 id
     */
    public function morphId(): int
    {
        return $this->getKey();
    }

    /**
     * payable 的 Options
     *
     * @return int
     */
    public function morphOptions(): array
    {
        return [];
    }



    /**
     * 是否已支付 （包含退款的订单，不包含货到付款的）
     */
    public function isPaid(): bool
    {
        return $this->pay_status == PayStatus::Paid;
    }

    /**
     * 获取订单剩余应支付金额
     */
    public function getRemainPayFee(): float
    {
        return (float)$this->remain_pay_fee;
    }


    /**
     * 检测是否支付
     */
    public function checkAndPaid(): Model
    {
        return $this->getOrderOperate()->checkAndPaid();
    }



    // @sn todo 补充后续方法



    /**
     * 获取订单已支付金额
     *
     * @param bool $is_lock 是否加锁
     * @return float
     */
    public function getPaidFee($is_lock = false): float
    {
        $query = $this->payRecords()->scopeable($this->getScopeType(), $this->getScopeId())->paid();
        $is_lock && $query->lockForUpdate();        // 加锁

        return (float)$query->sum('real_fee');
    }



    /**
     * 获取所有的付款成功的记录
     *
     * @param boolean $is_lock
     * @return Collection
     */
    public function getPaidPayRecords($is_lock = false): Collection
    {
        $query = $this->payRecords()->scopeable($this->getScopeType(), $this->getScopeId())->paid();
        $is_lock && $query->lockForUpdate();        // 加锁

        return $query->order('id', 'asc')->get();
    }




    /**
     * 获取订单剩余可退款金额
     *
     * @param Collection|null $payRecords
     * @return float
     */
    public function getRemainRefundMoney(Collection $payRecords = null): float
    {
        // 拿到 所有可退款的支付记录               @sn todo 这里如果是积分商城支付，退款了一部分积分，退了多少积分如何记录，refunded_fee 不能记录退了多少积分
        $payRecords = $payRecords && $payRecords->isNotEmpty() ? $payRecords : $this->getPaidPayRecords(true);

        // 支付金额，除了已经退完款的金额 (如果是非 1:1 的支付方式，real_fee 为真实抵扣金额)
        $paid_money = (string)array_sum($payRecords->column('real_fee'));
        // 已经退款金额 （如果是 非 1:1 的支付方式，这里是真实抵扣比例退款的真实金额）
        $refunded_money = (string)array_sum($payRecords->column('refunded_fee'));

        // 当前剩余的最大可退款金额，支付金额 - 已退款金额
        $remain_max_refund_money = bcsub($paid_money, $refunded_money, 2);

        return (float)$remain_max_refund_money;
    }





    /**
     * 获取 OrderOperate 实例
     *
     * @return OrderOperate
     */
    private function getOrderOperate(): OrderOperate
    {
        if ($this->orderOperate) {
            return $this->orderOperate;
        }
        return $this->orderOperate = new OrderOperate($this);
    }



    public function payRecords(): Relation
    {
        return $this->morphMany(PayRecord::class, 'payable');
    }

}
