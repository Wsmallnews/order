<?php

namespace Wsmallnews\Order\Shortcuts\Concerns;

use Illuminate\Support\Collection;
use Wsmallnews\Order\Enums\Item\AftersaleStatus;
use Wsmallnews\Order\Enums\Item\DeliveryStatus;
use Wsmallnews\Order\Enums\Item\EvaluateStatus;
use Wsmallnews\Order\Enums\Item\PayStatus;
use Wsmallnews\Order\Enums\Item\RefundStatus;
use Wsmallnews\Order\Models\OrderItem;
use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Order\Support\Utils;

/**
 * 订单附表落库：把 payloads.relate_items 写入 order_items（金额 fromMinor 赋值）。
 *
 * 通用实现：relate_* 快照字段全量转存；业务配方有附加附表（套餐/核销码等）
 * 时覆盖 Shortcut::save() 并在其后追加。
 */
trait SavesOrderItems
{
    /**
     * @return Collection<int, OrderItem>
     */
    public function save(OrderRocket $rocket): Collection
    {
        $payloads = $rocket->getPayloads();
        $order = $rocket->getRadar(OrderRocket::RADAR_ORDER);
        $buyer = $rocket->getBuyer();

        $scopeType = $rocket->getParam('scope_type', 'default');
        $scopeId = (int) $rocket->getParam('scope_id', 0);

        $currency = $order->currency ?: sn_money()->defaultCurrency();
        $orderItemModel = Utils::getOrderItemModel();

        foreach ($payloads['relate_items'] as $relateItem) {
            $orderItem = new $orderItemModel;

            $orderItem->scope_type = $scopeType;
            $orderItem->scope_id = $scopeId;
            $orderItem->order_id = $order->id;
            $orderItem->buyer_type = $buyer ? $buyer->getMorphClass() : 'anonymous';
            $orderItem->buyer_id = $buyer ? $buyer->getKey() : 0;

            // relate 快照
            $orderItem->relate_type = $relateItem['relate_type'];
            $orderItem->relate_id = $relateItem['relate_id'];
            $orderItem->relate_title = $relateItem['relate_title'];
            $orderItem->relate_subtitle = $relateItem['relate_subtitle'];
            $orderItem->relate_attributes = $relateItem['relate_attributes'];
            $orderItem->relate_image = $relateItem['relate_image'];
            $orderItem->relate_original_price = sn_money()->fromMinor((int) $relateItem['relate_original_price'], $currency);
            $orderItem->relate_price = sn_money()->fromMinor((int) $relateItem['relate_price'], $currency);
            $orderItem->relate_stock_num = $relateItem['relate_stock_num'];
            $orderItem->relate_num = $relateItem['relate_num'];
            $orderItem->relate_weight = $relateItem['relate_weight'];
            $orderItem->relate_sn = $relateItem['relate_sn'];
            $orderItem->relate_options = $relateItem['relate_options'];

            $orderItem->stock_unit = $relateItem['stock_unit'];
            $orderItem->stock_type = $relateItem['stock_type'];

            // 金额（JSON 字段集已整数分，标量金额 fromMinor 赋值防 cast 错账）
            $orderItem->original_amount_fields = $relateItem['original_amount_fields'];
            $orderItem->amount_fields = $relateItem['amount_fields'];
            $orderItem->original_amount = sn_money()->fromMinor((int) $relateItem['original_amount'], $currency);
            $orderItem->amount = sn_money()->fromMinor((int) $relateItem['amount'], $currency);
            $orderItem->score_amount = (int) $relateItem['score_amount'];
            $orderItem->discount_fields = $relateItem['discount_fields'];
            $orderItem->discount_amount = sn_money()->fromMinor((int) $relateItem['discount_amount'], $currency);
            $orderItem->total_fee = sn_money()->fromMinor((int) $relateItem['total_fee'], $currency);
            $orderItem->reonly_fee = sn_money()->fromMinor((int) $relateItem['reonly_fee'], $currency);

            $orderItem->pay_status = PayStatus::Unpaid;
            $orderItem->delivery_type = $relateItem['delivery_type'];
            $orderItem->delivery_status = DeliveryStatus::WaitingSend;
            $orderItem->delivery_amount = sn_money()->fromMinor((int) $relateItem['delivery_amount'], $currency);
            $orderItem->delivery_id = $relateItem['delivery_id'];

            $orderItem->aftersale_status = AftersaleStatus::Unafter;
            $orderItem->evaluate_status = EvaluateStatus::Unevaluate;
            $orderItem->refund_status = RefundStatus::Unrefund;

            // 展示性字段集（格式化串，仅展示）
            $orderItem->fields_infos = $relateItem['fields_infos'];

            $orderItem->options = array_merge([
                'order_status' => 'normal',        // 刚下单均为正常状态，订单 closed 时同步
            ], $relateItem['options'] ?? []);

            $orderItem->save();
        }

        $orderItems = $orderItemModel::query()->where('order_id', $order->id)->get();

        $rocket->setRadar(OrderRocket::RADAR_ORDER_ITEMS, $orderItems);

        return $orderItems;
    }
}
