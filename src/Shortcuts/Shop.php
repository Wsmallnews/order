<?php

namespace Wsmallnews\Order\Shortcuts;

use Illuminate\Support\Collection;
use Wsmallnews\Order\Contracts\Shortcuts\ShortcutInterface;
use Wsmallnews\Order\Enums\Item\AftersaleStatus;
use Wsmallnews\Order\Enums\Item\DeliveryStatus;
use Wsmallnews\Order\Enums\Item\EvaluateStatus;
use Wsmallnews\Order\Enums\Item\PayStatus;
use Wsmallnews\Order\Enums\Item\RefundStatus;
use Wsmallnews\Order\Models\Order;
use Wsmallnews\Order\Models\OrderItem;
use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Order\Pipes\Shop\Calc\Product as ProductCalcPipe;
use Wsmallnews\Order\Pipes\Shop\Calc\ProductAttribute as ProductAttributeCalcPipe;
use Wsmallnews\Order\Pipes\Shop\Calc\Start as StartCalcPipe;
use Wsmallnews\Order\Pipes\Shop\Check\Product as ProductCheckPipe;
use Wsmallnews\Order\Pipes\Shop\Check\ProductAttribute as ProductAttributeCheckPipe;
use Wsmallnews\Order\Pipes\Shop\Check\Start as StartCheckPipe;
use Wsmallnews\Order\Pipes\Shop\Creating\Cart as CartCreatingPipe;
use Wsmallnews\Order\Pipes\Shop\Creating\LimitBuy as LimitBuyCreatingPipe;
use Wsmallnews\Order\Pipes\Shop\Creating\Money as MoneyCreatingPipe;
use Wsmallnews\Order\Pipes\Shop\Creating\Score as ScoreCreatingPipe;
use Wsmallnews\Order\Pipes\Shop\Creating\Start as StartCreatingPipe;
use Wsmallnews\Order\Pipes\Shop\Get\Product as ProductGetPipe;
use Wsmallnews\Order\Pipes\Shop\Get\Start as StartGetPipe;
use Wsmallnews\Order\Pipes\Shop\Summary\Product as ProductSummaryPipe;
use Wsmallnews\Order\Pipes\Shop\Summary\ProductAttribute as ProductAttributeSummaryPipe;
use Wsmallnews\Order\Pipes\Shop\Summary\Start as StartSummaryPipe;

class Shop implements ShortcutInterface
{
    protected $order_type = 'product';

    public function __construct($order_type = 'product')
    {
        $this->order_type = $order_type;
    }

    /**
     * 获取项目的 pipes
     */
    public function getGetPipes(): array
    {
        // 获取项目信息     GetPipeInterface
        $getPipes = [
            'start' => StartGetPipe::class,
            'product' => ProductGetPipe::class,

            // 目前缺少秒杀拼团活动状态的判断
        ];

        // if ($this->order_type == 'score') {
        //     $getPipes['product'] = ScoreProductPipe::class;
        // }

        return $getPipes;
    }

    /**
     * 获取检测的 pipes
     */
    public function getCheckPipes(): array
    {
        // 检查商品信息
        $checkPipes = [
            'start' => StartCheckPipe::class,

            'product' => ProductCheckPipe::class,

            'attribute' => ProductAttributeCheckPipe::class,

            // 验证拼团，秒杀活动

            // 'delivery' => DeliveryCheckPipe::class,
        ];

        // if ($this->order_type == 'score') {
        //     $checkPipes['score'] = ScoreProductCheckPipe::class;
        // }

        return $checkPipes;
    }

    /**
     * 获取计算的 pipes
     */
    public function getCalcPipes(): array
    {
        $calcAmountPipes = [
            'start' => StartCalcPipe::class,

            'product' => ProductCalcPipe::class,
            'product_attribute' => ProductAttributeCalcPipe::class,

            // 'score' => ScoreProductCalcPipe::class,
            // 'delivery' => DeliveryCalcPipe::class,
        ];

        $calcDiscountPipes = [
            // CouponCalcPipe::class,
        ];

        $calcPipes = array_merge($calcAmountPipes, $calcDiscountPipes);

        return $calcPipes;
    }

    public function getSummaryPipes(): array
    {
        // 通过 getCalcPipes 调用,只能写后置内容
        $endPipes = [
            'start' => StartSummaryPipe::class,

            'product' => ProductSummaryPipe::class,

            'product_attribute' => ProductAttributeSummaryPipe::class,

            // 'invoice' => InvoiceSummaryPipe::class,
        ];

        return $endPipes;
    }

    /**
     * 获取创建的 pipes
     */
    public function getCreatingPipes(): array
    {
        $creatingPipes = [
            'start' => StartCreatingPipe::class,

            'score' => ScoreCreatingPipe::class,

            'money' => MoneyCreatingPipe::class,

            'limit_buy' => LimitBuyCreatingPipe::class,

            // 'score_product_stock_lock' => ScoreProductStockLockPipe::class,

            // 'stock_lock' => StockLockPipe::class,

            'cart' => CartCreatingPipe::class,

            // 'invoice' => InvoiceCreatingPipe::class,

        ];

        return $creatingPipes;
    }

    /**
     * 退款的 pipes @sn todo 这里还需要重新整理
     *
     * @param  string  $back_type  退回类型
     * @param  string  $relate_id  退款id
     */
    public function getRefundPipes($back_type, $relate_id = 0): array
    {
        $refundPipes = [
            'stock_back' => StockBackPipe::class . ':' . $back_type . ',' . $relate_id,
            'coupon_back' => CouponBackPipe::class . ':' . $back_type . ',' . $relate_id,
            'verify_back' => VerifyBackPipe::class . ':' . $back_type . ',' . $relate_id,
            'invoice_refunded_cancel' => InvoiceRefundedCancelPipe::class . ':' . $back_type . ',' . $relate_id,
        ];

        return $refundPipes;
    }

    /**
     * 订单失效的 pipes (没有付款)
     *
     * @param  string  $back_type  退回类型
     */
    public function getInvalidPipes($back_type): array
    {
        $invalidPipes = [
            'coupon_back' => CouponBackPipe::class . ':' . $back_type,     // 退回优惠券
            'stock_unlock' => StockUnLockPipe::class . ':' . $back_type,       // 库存解锁
            'score_product_stock_unlock' => ScoreProductStockUnLockPipe::class . ':' . $back_type,     // 积分库存解锁
            'invoice_invalid_cancel' => InvoiceInvalidCancelPipe::class . ':' . $back_type,                    // 订单失效取消发票
        ];

        return $invalidPipes;
    }

    /**
     * 保存 当前 shortcut 特有的 项目表
     *
     * @return \think\Collection
     */
    public function save(OrderRocket $rocket): Collection
    {
        $payloads = $rocket->getPayloads();
        $radars = $rocket->getRadars();
        $relateItems = $payloads['relate_items'];
        $order = $radars['order'];
        $buyer = $rocket->getBuyer();

        $scope_type = $rocket->getParam('scope_type', 'default');
        $scope_id = $rocket->getParam('scope_id', 0);

        // 添加 订单 商品
        foreach ($relateItems as $key => $relateItem) {
            $orderItem = new OrderItem;

            $orderItem->scope_type = $scope_type;
            $orderItem->scope_id = $scope_id;
            $orderItem->order_id = $order->id;
            $orderItem->buyer_type = $buyer?->morphType() ?? 'anonymous';
            $orderItem->buyer_id = $buyer?->morphId() ?? 0;

            $orderItem->relate_type = $relateItem['relate_type'];
            $orderItem->relate_id = $relateItem['relate_id'];
            $orderItem->relate_title = $relateItem['relate_title'];
            $orderItem->relate_subtitle = $relateItem['relate_subtitle'];
            $orderItem->relate_attributes = $relateItem['relate_attributes'];
            $orderItem->relate_image = $relateItem['relate_image'];
            $orderItem->relate_original_price = $relateItem['relate_original_price'];
            $orderItem->relate_price = $relateItem['relate_price'];
            $orderItem->relate_stock_num = $relateItem['relate_stock_num'];
            $orderItem->relate_num = $relateItem['relate_num'];
            $orderItem->relate_weight = $relateItem['relate_weight'];
            $orderItem->relate_sn = $relateItem['relate_sn'];

            $orderItem->relate_options = $relateItem['relate_options'] ?? null;     // relate 相关附加字段

            $orderItem->stock_unit = $relateItem['stock_unit'];
            $orderItem->stock_type = $relateItem['stock_type'];

            $orderItem->original_amount_fields = sn_currency()->formatByDecimal($relateItem['original_amount_fields']);
            $orderItem->amount_fields = sn_currency()->formatByDecimal($relateItem['amount_fields']);
            $orderItem->original_amount = $relateItem['original_amount'];
            $orderItem->amount = $relateItem['amount'];
            $orderItem->score_amount = $relateItem['score_amount'] ?? 0;

            $orderItem->discount_fields = sn_currency()->formatByDecimal($relateItem['discount_fields'] ?? []);
            $orderItem->discount_amount = $relateItem['discount_amount'];
            $orderItem->total_fee = $relateItem['total_fee'];
            $orderItem->reonly_fee = $relateItem['reonly_fee'];

            $orderItem->pay_status = PayStatus::Unpaid;
            $orderItem->delivery_type = $relateItem['delivery_type'];

            $orderItem->delivery_status = DeliveryStatus::WaitingSend;
            $orderItem->delivery_amount = $relateItem['delivery_amount'];

            $orderItem->delivery_id = $relateItem['delivery_id'];

            $orderItem->aftersale_status = AftersaleStatus::Unafter;
            $orderItem->evaluate_status = EvaluateStatus::Unevaluate;
            $orderItem->refund_status = RefundStatus::Unrefund;
            $orderItem->fields_infos = $relateItem['fields_infos'];

            $options = [
                'order_status' => 'normal',          // 刚下单都是正常状态，订单 closed 的时候，会变成 closed 状态
            ];
            $orderItem->options = array_merge($options, $relateItem['options'] ?? []);

            $orderItem->save();
        }

        // 重新查询 订单商品
        $orderItems = OrderItem::scopeable($scope_type, $scope_id)->where('order_id', $order->id)->get();

        $rocket->setRadar('order_items', $orderItems);

        return $orderItems;
    }

    /**
     * 添加套餐商品的 套餐项
     *
     * @param  \think\Model  $order
     * @param  \think\Model  $orderProduct
     * @param  array  $buyInfo
     * @return void
     */
    // public function orderProductPackageRelate($order, $orderProduct, $buyInfo)
    // {
    //     $product = $buyInfo['product'];
    //     $packageRelates = $product['package_relates'];

    //     // 转存 orderPackageItems
    //     foreach ($packageRelates as $packageRelate) {
    //         $orderProductPackageRelate = new OrderProductPackageRelate();
    //         $orderProductPackageRelate->user_id = $order->user_id;
    //         $orderProductPackageRelate->order_id = $order->id;
    //         $orderProductPackageRelate->order_relate_id = $orderProduct->id;
    //         $orderProductPackageRelate->parent_id = 0;
    //         $orderProductPackageRelate->product_id = $product['id'];
    //         $orderProductPackageRelate->package_parent_id = 0;
    //         $orderProductPackageRelate->relate_title = $packageRelate->relate_title;
    //         $orderProductPackageRelate->save();

    //         foreach ($packageRelate['children'] as $children) {
    //             if ($children['relate_type'] == 'custom') {
    //                 // 判断类型，处理数据
    //             }

    //             $childrenOrderProductPackageRelate = new OrderProductPackageRelate();
    //             $childrenOrderProductPackageRelate->user_id = $order->user_id;
    //             $childrenOrderProductPackageRelate->order_id = $order->id;
    //             $childrenOrderProductPackageRelate->order_relate_id = $orderProduct->id;
    //             $childrenOrderProductPackageRelate->parent_id = $orderProductPackageRelate->id;
    //             $childrenOrderProductPackageRelate->product_id = $product['id'];
    //             $childrenOrderProductPackageRelate->package_parent_id = $children->parent_id;
    //             $childrenOrderProductPackageRelate->relate_type = $children->relate_type;
    //             $childrenOrderProductPackageRelate->relate_id = $children->relate_id;
    //             $childrenOrderProductPackageRelate->relate_title = $children->relate_title;
    //             $childrenOrderProductPackageRelate->relate_image = $children->relate_image;
    //             $childrenOrderProductPackageRelate->relate_price = $children->relate_price;
    //             $childrenOrderProductPackageRelate->relate_ext = $children->relate_ext;
    //             $childrenOrderProductPackageRelate->num = $children->num;
    //             $childrenOrderProductPackageRelate->ext = $children->ext;
    //             $childrenOrderProductPackageRelate->save();
    //         }
    //     }
    // }

    /**
     * 失败时执行此方法
     *
     * @param  OrderRocket  $rocket
     * @return void
     */
    // public function failBack(OrderRocket $rocket): void
    // {
    //     $store_id = $rocket->getParam('store_id', 0);
    //     $scope_type = $rocket->getParam('scope_type', 'default');
    //     $request_identify = $rocket->getRadar('request_identify', '');

    //     // 删除锁定的商品库存
    //     $stockLockManager = (new StockLockManager('product'))->setScopeInfo($scope_type, $store_id)
    //         ->setTableType('product')->setLockIdentify($request_identify);
    //     $stockLockManager->failBackLockStock();

    //     // 删除锁定的积分商品库存
    //     $scoreProductStockLockManager = (new ScoreProductStockLockManager('score_product'))->setScopeInfo($scope_type, $store_id)
    //         ->setTableType('product')->setLockIdentify($request_identify);
    //     $scoreProductStockLockManager->failBackLockStock();
    // }
}
