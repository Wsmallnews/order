<?php

namespace Wsmallnews\Order\Pipes\Shop\Check;

use Closure;
use Wsmallnews\Delivery\Models\UserAddress;
use Wsmallnews\Order\Contracts\Pipes\CheckPipeInterface;
use Wsmallnews\Order\Exceptions\OrderCreateException;
use Wsmallnews\Order\OrderRocket;

class Delivery implements CheckPipeInterface
{
    public function check(OrderRocket $rocket, Closure $next): OrderRocket
    {
        $address_id = $rocket->getParam('address_id', 0);

        $products = $rocket->getRelateItems();

        foreach ($products as $key => &$buyInfo) {
            $product = $buyInfo['product'];

            // 最少购买一件
            $buyInfo['delivery_type'] = 'express';
            $buyInfo['delivery_type_text'] = '快递物流';
        }

        // 重设商品
        $rocket->setRelateItems($products);

        // 所有的配送方式
        $deliveryTypes = array_values(array_unique(array_filter(array_column($products, 'delivery_type'))));
        // 检测是否需要收货地址
        if (array_intersect(['express'], $deliveryTypes)) {
            $need_address = 1;
            // 用户收货地址
            if ($address_id) {
                $user = $rocket->getUser();
                $userAddress = UserAddress::where('user_id', ($user ? $user->id : 0))->find($rocket->getParam('address_id'));
            } else {
                // 获取默认收货地址

            }
            if ((! isset($userAddress) || is_null($userAddress)) && $rocket->getRadar('calc_type') == 'create') {
                throw (new OrderCreateException('请选择正确的收货地址'))->setRocket($rocket);
            }
        } else {
            // 不需要收货地址
            $need_address = 0;
        }

        // 记录需要存到订单 ext 中的字段
        $rocket->mergeRadarField(
            [
                'need_address',
                'delivery_types',
            ],
            'ext_fields'
        );

        $addressInfo = [
            'delivery_types' => $deliveryTypes,
            'need_address' => $need_address,
            'user_address' => $userAddress ?? null,
        ];
        $rocket->mergeRadars($addressInfo);
        $rocket->mergePayloads($addressInfo);

        $rocket->setProducts($products);

        return $next($rocket);

        $response = $next($rocket);

        return $response;

        $deliveryModel = new Delivery;

        $products = $rocket->getProducts();
        $delivery_type = $rocket->getParam('delivery_type', 'express');
        $delivery_type_text = $deliveryModel->typeList()[$delivery_type];

        foreach ($products as $key => &$buyInfo) {
            $product = $buyInfo['product'];

            if (in_array('autosend', $product['delivery_types'])) {
                // 自动发货类的
                $buyInfo['delivery_type'] = 'autosend';
                $buyInfo['delivery_type_text'] = $deliveryModel->typeList()['autosend'];
            } elseif (in_array('custom', $product['delivery_types'])) {
                // 手动发货类的
                $buyInfo['delivery_type'] = 'custom';
                $buyInfo['delivery_type_text'] = $deliveryModel->typeList()['custom'];
            } else {
                if (! in_array($delivery_type, $product['delivery_types'])) {
                    $product_title = mb_strlen($product['title']) > 10 ? mb_substr($product['title'], 0, 7) . '...' : $product['title'];

                    throw (new OrderCreateException('商品 ' . $product_title . '不支持 ' . $delivery_type_text))->setRocket($rocket);
                }

                // 将商品的配送方式存到 buyInfo
                $buyInfo['delivery_type'] = $delivery_type;
                $buyInfo['delivery_type_text'] = $delivery_type_text;

                // $buyInfo['use_store_id'] = $this->store_id;
            }
        }

        // 所有的配送方式
        $deliveryTypes = array_values(array_unique(array_filter(array_column($products, 'delivery_type'))));
        // 检测是否需要收货地址
        if (array_intersect(['express', 'store_delivery'], $deliveryTypes)) {
            $need_address = 1;
            // 用户收货地址
            if ($rocket->getParam('address_id')) {
                $user = $rocket->getUser();
                $userAddress = UserAddress::where('user_id', ($user ? $user->id : 0))->find($rocket->getParam('address_id'));
            }
            if ((! isset($userAddress) || is_null($userAddress)) && $rocket->getRadar('calc_type') == 'create') {
                throw (new OrderCreateException('请选择正确的收货地址'))->setRocket($rocket);
            }
        } else {
            // 不需要收货地址
            $need_address = 0;
        }

        // 记录需要存到订单 ext 中的字段
        $rocket->mergeRadarField(
            [
                'need_address',
                'delivery_types',
            ],
            'ext_fields'
        );

        $addressInfo = [
            'delivery_types' => $deliveryTypes,
            'need_address' => $need_address,
            'user_address' => $userAddress ?? null,
        ];
        $rocket->mergeRadars($addressInfo);
        $rocket->mergePayloads($addressInfo);

        $rocket->setProducts($products);

        return $next($rocket);

    }
}
