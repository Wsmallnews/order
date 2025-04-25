@php
    $userAddressColumns = [
        'md' => 2,
        '2xl' => 3,
    ]
@endphp

<div class="w-full flex flex-wrap lg:flex-nowrap mx-auto gap-4" 
    x-data="confirmManager({
    })"
>
    <div class="flex-1 flex flex-col gap-4">
        <x-sn-support::base.block class="w-full rounded-lg overflow-hidden p-4">
            <livewire:sn-user-choose-address :user="$buyer" :columns="$userAddressColumns" />
        </x-sn-support::base.block>

        <x-sn-support::base.block class="w-full rounded-lg overflow-hidden p-4">
            @foreach($relate_items as $key => $item)
                <div class="lg:flex hidden">
                    <div class="flex flex-1 mr-4">
                        <div class="w-20 h-20 flex-none overflow-hidden rounded-lg mr-2.5">
                            <img class="w-full h-full object-contain" src="{{$item['relate_image']}}" alt="">
                        </div>
                        <div class="flex flex-col">
                            <div class="text-base line-clamp-2">{{$item['relate_title']}}</div>
                            <div class="text-sm text-slate-400 line-clamp-2">{{$item['relate_subtitle']}}</div>
                        </div>
                    </div>

                    <div class="flex flex-1">
                        @forelse($item['relate_attributes'] as $rkey => $attribute)
                            {{ $attribute }}
                        @empty
                        -
                        @endforelse
                    </div>

                    <div class="flex-none w-20 mr-4">
                        {{ $item['relate_price'] }}
                    </div>

                    <div class="flex-none w-20 mr-4">
                        {{ $item['relate_num'] }}
                    </div>

                    <div class="flex justify-end flex-none w-20">
                        {{ $item['relate_amount'] }}
                    </div>
                </div>

                <div class="flex lg:hidden">
                    <div class="w-20 h-20 flex-none overflow-hidden rounded-lg mr-2.5">
                        <img class="w-full h-full object-contain" src="{{$item['relate_image']}}" alt="">
                    </div>
                    <div class="flex flex-1 flex-col">
                        <div class="text-base line-clamp-2">{{$item['relate_title']}}</div>
                        <div class="text-sm text-slate-400">
                            {{ join(';', $item['relate_attributes']) }}
                        </div>

                        <div class="text-base">
                            {{ $item['relate_price'] }}
                        </div>

                        <div class="flex justify-end">
                            {{ $item['relate_num'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </x-sn-support::base.block>
    </div>
    
    <x-sn-support::base.block class="flex-none w-full lg:w-96 rounded-lg overflow-hidden p-4">
        @foreach($amount_fields_info as $info)
            <div class="flex w-full items-center">
                <div class="flex flex-1 items-center">
                    <div @class([
                        'text-base',
                        'font-bold' => (bool)$info['high_light']
                    ])>
                        {{ $info['text'] }}
                    </div>
                    <div class="text-sm text-slate-400 ml-2.5">{{ ($info['desc'] ? '(' . $info['desc'] . ')' : '') }}</div>
                </div>
                <div @class([
                    'flex flex-1 justify-end',
                    'font-bold text-primary-600' => (bool)$info['high_light'],
                ])>
                    {{ $info['value'] }}
                </div>
            </div>
        @endforeach

        @foreach($discount_fields_info as $info)
            <div class="flex w-full items-center">
                <div class="flex flex-1 items-center">
                    <div @class([
                        'text-base',
                        'font-bold' => (bool)$info['high_light']
                    ])>
                        {{ $info['text'] }}
                    </div>
                    <div class="text-sm text-slate-400 ml-2.5">{{ ($info['desc'] ? '(' . $info['desc'] . ')' : '') }}</div>
                </div>
                <div @class([
                    'flex flex-1 justify-end',
                    'font-bold text-primary-600' => (bool)$info['high_light'],
                ])>
                    {{ $info['value'] }}
                </div>
            </div>
        @endforeach

        <div class="flex">
            <div class="flex flex-1 items-end">
                <div class="">应付：</div>
                <div class="">{{ $pay_fee }}</div>
            </div>
            <div class="flex flex-1 justify-end">
                <x-filament::button @click="create">
                    提交订单
                </x-filament::button>
            </div>
        </div>
    </x-sn-support::base.block>
</div>


@assets
<script>
    function confirmManager({
    }) {
        return {
            init () {
            },

            create () {
                this.$wire.set('type', 'create');
                this.$wire.create();
            }
        }
    }
</script>
@endassets