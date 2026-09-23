<?php

namespace Wsmallnews\Order\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Wsmallnews\Order\Enums\Order\PayStatus;
use Wsmallnews\Order\OrderOperate;
use Wsmallnews\Pay\Support\Utils as PayUtils;

/**
 * 可支付主体实现（Wsmallnews\Pay\Contracts\PayableInterface）。
 *
 * 金额口径：整数分（订单币种，行内 currency 快照列）。
 */
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
     * @return array{scope_type: string, scope_id: int}
     */
    public function getScopeInfo(): array
    {
        return ['scope_type' => $this->scope_type, 'scope_id' => $this->scope_id];
    }

    /**
     * payable 的 morph type（别名）
     */
    public function morphType(): string
    {
        return $this->getMorphClass();
    }

    /**
     * payable 的 morph id
     */
    public function morphId(): int
    {
        return $this->getKey();
    }

    /**
     * payable 的附加选项（随支付单快照保存）
     */
    public function morphOptions(): array
    {
        return [];
    }

    /**
     * 交易币种（ISO 4217，创建订单时快照；空值回落站点默认币种）
     */
    public function getPayCurrency(): string
    {
        return $this->currency ?: sn_money()->defaultCurrency();
    }

    /**
     * 是否已支付（含已退款订单，不含货到付款）
     */
    public function isPaid(): bool
    {
        return $this->pay_status == PayStatus::Paid;
    }

    /**
     * 剩余应支付金额（整数分）
     */
    public function getRemainPayFee(): int
    {
        return sn_money()->minor($this->remain_pay_fee);
    }

    /**
     * 检测并流转支付状态（累计支付 >= 应付时置为已支付）
     */
    public function checkAndPaid(): Model
    {
        return $this->getOrderOperate()->checkAndPaid();
    }

    /**
     * 获取订单已支付金额（整数分）
     *
     * @param  bool  $is_lock  是否加锁
     */
    public function getPaidFee(bool $is_lock = false): int
    {
        $query = $this->payRecords()->scopeable($this->getScopeType(), $this->getScopeId())->paid();
        $is_lock && $query->lockForUpdate();        // 加锁

        return (int) $query->sum('real_fee');
    }

    /**
     * 获取所有的付款成功的记录
     */
    public function getPaidPayRecords(bool $is_lock = false): Collection
    {
        $query = $this->payRecords()->scopeable($this->getScopeType(), $this->getScopeId())->paid();
        $is_lock && $query->lockForUpdate();        // 加锁

        return $query->orderBy('id', 'asc')->get();
    }

    /**
     * 获取订单剩余可退款金额（整数分）
     */
    public function getRemainRefundMoney(?Collection $payRecords = null): int
    {
        // 拿到 所有可退款的支付记录               @sn todo 这里如果是积分商城支付，退款了一部分积分，退了多少积分如何记录，refunded_fee 不能记录退了多少积分
        $payRecords = $payRecords && $payRecords->isNotEmpty() ? $payRecords : $this->getPaidPayRecords(true);

        // 支付金额，除了已经退完款的金额 (如果是非 1:1 的支付方式，real_fee 为真实抵扣金额)
        $paid_money = $payRecords->sum(fn ($record) => sn_money()->minor($record->real_fee));

        // 已经退款金额 （如果是 非 1:1 的支付方式，这里是真实抵扣比例退款的真实金额）
        $refunded_money = $payRecords->sum(fn ($record) => sn_money()->minor($record->refunded_fee));

        // 当前剩余的最大可退款金额，支付金额 - 已退款金额
        return max(0, $paid_money - $refunded_money);
    }

    /**
     * 获取 OrderOperate 实例
     */
    private function getOrderOperate(): OrderOperate
    {
        if ($this->orderOperate) {
            return $this->orderOperate;
        }

        return $this->orderOperate = new OrderOperate($this);
    }

    /**
     * 支付记录（多态）：模型经 sn-pay.models.pay_record 配置解析（wsmallnews/pay 包提供，order 硬依赖 pay）
     */
    public function payRecords(): MorphMany
    {
        return $this->morphMany(PayUtils::getPayRecordModel(), 'payable');
    }

    /**
     * 退款单（多态）
     */
    public function payRefunds(): MorphMany
    {
        return $this->morphMany(PayUtils::getRefundModel(), 'refundable');
    }
}
