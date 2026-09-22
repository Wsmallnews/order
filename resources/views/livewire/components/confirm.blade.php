@php
    $userAddressColumns = [
        'md' => 2,
        '2xl' => 3,
    ];
@endphp

<div class="w-full flex flex-col @4xl:flex-row flex-nowrap mx-auto sn-gap" x-data="snOrderConfirm({})">
    <div class="flex-1 flex flex-col sn-gap min-w-0">
        {{-- 收货地址（内嵌组件：scopeable 参数由调用方传入；卡片皮 + 行自带边距） --}}
        <div class="sn-container sn-padded">
            <livewire:sn-user::components.choose-address :user="$buyer" :columns="$userAddressColumns" />
        </div>

        {{-- 商品清单（行式列表：header 列头 + 行分割线，容器只穿卡片皮） --}}
        <div class="sn-container overflow-hidden">
            <div class="sn-list-header">
                <span>{{ __('sn-order::order.confirm.items') }}</span>
            </div>

            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($relate_items as $key => $item)
                    <li class="flex items-center gap-4 px-(--sn-space-card) py-4">
                        <div class="w-20 h-20 flex-none overflow-hidden rounded-lg">
                            <img class="w-full h-full object-contain" src="{{ $item['relate_image'] }}" alt="{{ $item['relate_title'] }}">
                        </div>

                        <div class="flex flex-col flex-1 min-w-0 gap-1">
                            <div class="text-base line-clamp-2 sn-title-text">{{ $item['relate_title'] }}</div>
                            <div class="text-sm sn-descript-text line-clamp-2">
                                {{ $item['relate_subtitle'] ?? '' }}
                                @if (! empty($item['relate_attributes']))
                                    · {{ join('；', $item['relate_attributes']) }}
                                @endif
                            </div>
                            <div class="text-base sn-title-text">{{ $item['relate_price'] }}</div>
                        </div>

                        <div class="flex flex-col items-end gap-1 flex-none">
                            <div class="sn-descript-text">x{{ $item['relate_num'] }}</div>
                            <div class="font-semibold sn-title-text">{{ $item['relate_amount'] }}</div>
                        </div>
                    </li>
                @empty
                    <li class="px-(--sn-space-card) py-8">
                        <x-sn-support::empty
                            :description="__('sn-order::order.confirm.items_empty')"
                            :contained="false"
                            icon-color="gray"
                            icon-size="md"
                        />
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- 金额汇总 + 提交（窄屏在下、@4xl 起右侧栏） --}}
    <div class="flex-none w-full @4xl:w-96 sn-container sn-padded h-fit">
        <div class="flex flex-col w-full sn-gap">
            @foreach ($amount_fields_info as $info)
                <div class="flex w-full items-center">
                    <div class="flex flex-1 items-center">
                        <div @class([
                            'text-base',
                            'font-bold' => (bool) $info['high_light'],
                        ])>
                            {{ $info['text'] }}
                        </div>
                        @if ($info['desc'])
                            <div class="text-sm sn-descript-text ml-2.5">({{ $info['desc'] }})</div>
                        @endif
                    </div>
                    <div @class([
                        'flex flex-1 justify-end',
                        'font-bold text-primary-600 dark:text-primary-400' => (bool) $info['high_light'],
                    ])>
                        {{ $info['value'] }}
                    </div>
                </div>
            @endforeach

            @foreach ($discount_fields_info as $info)
                <div class="flex w-full items-center">
                    <div class="flex flex-1 items-center">
                        <div @class([
                            'text-base',
                            'font-bold' => (bool) $info['high_light'],
                        ])>
                            {{ $info['text'] }}
                        </div>
                        @if ($info['desc'])
                            <div class="text-sm sn-descript-text ml-2.5">({{ $info['desc'] }})</div>
                        @endif
                    </div>
                    <div @class([
                        'flex flex-1 justify-end',
                        'font-bold text-primary-600 dark:text-primary-400' => (bool) $info['high_light'],
                    ])>
                        {{ $info['value'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-end justify-between w-full mt-6">
            <div class="flex items-end sn-gap">
                <div>{{ __('sn-order::order.confirm.pay_fee') }}：</div>
                <div class="text-xl font-bold sn-primary-text">{{ $pay_fee }}</div>
            </div>
            <x-filament::button @click="create">
                {{ __('sn-order::order.confirm.submit') }}
            </x-filament::button>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('snOrderConfirm', () => ({
                create() {
                    // calc=试算，create=真实创建订单（boot 阶段按 type 走创建管道）
                    this.$wire.set('type', 'create');
                    this.$wire.create();
                },
            }));
        });
    </script>
@endonce
