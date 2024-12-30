<?php

namespace Wsmallnews\Order\Shortcuts;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Pipeline;
use Wsmallnews\Order\Contracts\Shortcuts\ShortcutInterface;
use Wsmallnews\Order\Enums\Item\{
    PayStatus,
    RefundStatus,
    DeliveryStatus,
    AftersaleStatus,
    EvaluateStatus,
};
use Wsmallnews\Order\Models\Order;
use Wsmallnews\Order\Models\OrderItem;
use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Order\Pipes\Shop\{
    Get\Start as StartGetPipe,
    Get\Product as ProductGetPipe,

    Check\Start as StartCheckPipe,
    Check\Product as ProductCheckPipe,
    Check\ProductAttribute as ProductAttributeCheckPipe,

    Calc\Start as StartCalcPipe,
    Calc\Product as ProductCalcPipe,
    Calc\ProductAttribute as ProductAttributeCalcPipe,

    Summary\Start as StartSummaryPipe,
    Summary\Product as ProductSummaryPipe,
    Summary\ProductAttribute as ProductAttributeSummaryPipe,

    Creating\Start as StartCreatingPipe,
    Creating\Money as MoneyCreatingPipe,
    Creating\Score as ScoreCreatingPipe,
    Creating\LimitBuy as LimitBuyCreatingPipe,
    Creating\Cart as CartCreatingPipe,
};

class Shop implements ShortcutInterface
{

    protected $order_type = 'product';

    public function __construct($order_type = 'product')
    {
        $this->order_type = $order_type;
    }


    /**
     * 获取项目的 pipes
     *
     * @return array
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
     *
     * @return array
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
     *
     * @return array
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
     *
     * @return array
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
     * @param string $back_type     退回类型
     * @param string $relate_id     退款id
     * @return array
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
     * @param string $back_type     退回类型
     * @return array
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
     * @param OrderRocket $rocket
     * @return \think\Collection
     */
    public function save(OrderRocket $rocket): Collection
    {
        $radars = $rocket->getRadars();
        $products = $radars['relate_items'];
        $order = $radars['order'];
        $user = $radars['user'];

        // $store_id = $rocket->getParam('store_id', 0);
        // $scope_type = $rocket->getParam('scope_type', 'shop');

        // 添加 订单 商品
        foreach ($products as $key => $buyInfo) {
            $orderItem = new OrderItem();

            // $orderItem->scope_type = $scope_type;
            // $orderItem->store_id = $store_id;
            $orderItem->order_id = $order->id;
            $orderItem->user_id = $user ? $user->id : 0;

            $orderItem->relate_type = $buyInfo['relate_type'];
            $orderItem->relate_id = $buyInfo['relate_id'];
            $orderItem->relate_title = $buyInfo['relate_title'];
            $orderItem->relate_subtitle = $buyInfo['relate_subtitle'];
            $orderItem->relate_attributes = $buyInfo['relate_attributes'];
            $orderItem->relate_image = $buyInfo['relate_image'];
            $orderItem->relate_original_price = $buyInfo['relate_original_price'];
            $orderItem->relate_price = $buyInfo['relate_price'];
            $orderItem->relate_stock_num = $buyInfo['relate_stock_num'];
            $orderItem->relate_num = $buyInfo['relate_num'];
            $orderItem->relate_weight = $buyInfo['relate_weight'];
            $orderItem->relate_sn = $buyInfo['relate_sn'];

            $orderItem->relate_options = $buyInfo['relate_options'] ?? null;     // relate 相关附加字段

            $orderItem->stock_unit = $buyInfo['stock_unit'];
            $orderItem->stock_type = $buyInfo['stock_type'];
            
            $orderItem->original_amount_fields = $buyInfo['original_amount_fields'];
            $orderItem->amount_fields = $buyInfo['amount_fields'];
            $orderItem->amount = $buyInfo['amount'];
            $orderItem->score_amount = $buyInfo['score_amount'] ?? 0;
            
            $orderItem->discount_fields = $buyInfo['discount_fields'] ?? [];
            $orderItem->discount_amount = $buyInfo['discount_amount'];
            $orderItem->total_fee = $buyInfo['total_fee'];
            $orderItem->reonly_fee = $buyInfo['reonly_fee'];

            $orderItem->pay_status = PayStatus::Unpaid;
            $orderItem->delivery_type = $buyInfo['delivery_type'];
            
            $orderItem->delivery_status = DeliveryStatus::WaitingSend;
            $orderItem->delivery_amount = $buyInfo['delivery_amount'];

            $orderItem->delivery_id = $buyInfo['delivery_id'];

            $orderItem->aftersale_status = AftersaleStatus::Unafter;
            $orderItem->evaluate_status = EvaluateStatus::Unevaluate;
            $orderItem->refund_status = RefundStatus::Unrefund;
            $orderItem->fields_infos = $buyInfo['fields_infos'];

            $options = [
                'order_status' => 'normal'          // 刚下单都是正常状态，订单 closed 的时候，会变成 closed 状态
            ];
            $orderItem->options = array_merge($options, $buyInfo['options'] ?? []);

            $orderItem->save();
        }

        // 重新查询 订单商品
        $orderItems = OrderItem::where('order_id', $order->id)->get();

        $rocket->setRadar('order_items', $orderItems);

        return $orderItems;
    }



    /**
     * 添加套餐商品的 套餐项
     *
     * @param \think\Model $order
     * @param \think\Model $orderProduct
     * @param array $buyInfo
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
     * @param OrderRocket $rocket
     * @return void
     */
    // public function failBack(OrderRocket $rocket): void
    // {
    //     $store_id = $rocket->getParam('store_id', 0);
    //     $scope_type = $rocket->getParam('scope_type', 'shop');
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
