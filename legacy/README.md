# legacy 目录（阶段 C 管道重排的完整存档）

> 本目录不在 composer autoload（PSR-4 只映射 `src/`），纯参考文件，勿在业务代码中引用。
> 存档的是阶段 C（2026-09-25）重排前的全部原文件，供对照审查新实现。

## 原文件 → 新去向对照表

### 商品域管道（重写迁入 product 包 `Wsmallnews\Product\Pipes\*`）

| 原文件（本目录） | 新文件 | 主要变化 |
|---|---|---|
| `Pipes/Shop/Get/Product.php` | `product/src/Pipes/Fetch/Product.php` | `->show()`→`->up()`；移除 `attributes` 关联加载；`GetPipeInterface`→`FetchPipeInterface`；异常消息改翻译键 |
| `Pipes/Shop/Check/Product.php` | `product/src/Pipes/Check/Product.php` | `sku_type`→`spec_type`；`convert_num`→`stock_convert_num`；库存比较改用换算后的 `relate_stock_num`（原代码误比 `relate_num`）；多单位判断走枚举 |
| `Pipes/Shop/Calc/Product.php` | `product/src/Pipes/Calc/Product.php` | `sn_currency()` 元字符串 → 整数分 int；`original_price` 空回落现价；fields_info 值改十进制串；`radarAdditionAmount`→`radarAdditionMinor` |
| `Pipes/Shop/Summary/Product.php` | `product/src/Pipes/Summary/Product.php` | `mainUrl['medium']`→`image`/媒体封面；`product_sku_text`→`product_spec_text`；`formatByDecimal` 快照→整数分；relate_options 精简为 spec 快照 |

> **声明（用户澄清，2026-09-25）**：原 relate_options 中的 `product_attributes` 与 `product_spec_text`
> **不是重复字段**——`product_attributes` 是预留给「小料/加料」的字段（与 relate_attributes 同源语义），
> 当时功能未完善所以值恰好与规格值相同。阶段 C 暂删合理（小料落地时经 relate_attributes 通道重新设计），
> 此处更正「历史冗余」的旧判断；后续实现小料功能时勿把它当作重复键照抄恢复。

### 通用管道（重写留在 order 包 `Pipes/Common/*`）

| 原文件（本目录） | 新文件 | 主要变化 |
|---|---|---|
| `Pipes/Shop/Get/Start.php` | `order/src/Pipes/Common/Fetch/Start.php` | 结构不变，规范化 |
| `Pipes/Shop/Calc/Start.php` | `order/src/Pipes/Common/Calc/Start.php` | 元字符串初始化（`'0'`）→ int 0；汇总改 `array_sum` 整数分；`pay_fee` 下限保护 |
| `Pipes/Shop/Summary/Start.php` | `order/src/Pipes/Common/Summary/Start.php` | 结构不变；payload 金额显式 `(int)`；未知键兜底 `?? 默认值` |
| `Pipes/Shop/Creating/Start.php` | `order/src/Pipes/Common/Creating/Start.php` | 结构不变，radar key 常量化 |

### 配方与入口（重写迁入 shop 包）

| 原文件（本目录） | 新文件 | 主要变化 |
|---|---|---|
| `Shortcuts/Shop.php` | `shop/src/Order/Shortcuts/ProductShortcut.php` | 类名 Shop→ProductShortcut；管道清单只声明业务段（通用段经 Shortcuts\Base 默认嵌入）；`save()` 20+ 行样板上移为 `Shortcuts/Concerns/SavesOrderItems`；`formatByDecimal`→整数分/fromMinor；getRefundPipes/getInvalidPipes 死引用清理（契约保留空清单机制） |
| （原 OrderCreate 内联） | `order/src/Shortcuts/Base.php` + `Pipes/CommonPipes.php` | 配方基类：通用段默认嵌入 + 注册表加工在引擎侧统一 apply |

### 移除的管道（规划独立扩展包，经 OrderPipes 注册表插回）

| 原文件（本目录） | 状态 |
|---|---|
| `Pipes/Shop/Creating/LimitBuy.php` | 限购扩展包（列 limit_type/limit_num 不存在，`limit_type` 引用为死代码） |
| `Pipes/Shop/Creating/Money.php` | 余额抵扣归钱包扩展域（WalletOperator 契约已就绪）；buyer->money 字段不存在 |
| `Pipes/Shop/Creating/Score.php` | 积分抵扣同上；空实现 |
| `Pipes/Shop/Creating/Cart.php` | 购物车清理（P3 购物车扩展）；CartManager 不存在，全注释占位 |

### 删除的管道（本目录保留原样供审查）

| 原文件（本目录） | 原因 |
|---|---|
| `Pipes/Shop/Check/Delivery.php` | 引用 user 包不存在的 Address 模型；配送校验归履约域后置 |
| `Pipes/Shop/Calc/ProductAttribute.php` | attribute 体系已搁置（用户决策） |
| `Pipes/Shop/Check/ProductAttribute.php` | 同上 |
| `Pipes/Shop/Summary/ProductAttribute.php` | 同上 |

### 契约（阶段 C 删除/更名）

| 原文件（本目录） | 去向 |
|---|---|
| `Contracts/Pipes/GetPipeInterface.php` | 更名 `Contracts/Pipes/FetchPipeInterface.php`（方法 get→fetch，与阶段命名对齐） |
| `Contracts/BuyerInterface.php` | **整体删除**：morph 用 Laravel 原生 `getMorphClass()/getKey()`，buyer = `?Model`；`orders()` 反查将来放消费方包（决策记录 #23） |
| `Traits/Buyerable.php` | 空 trait，随接口一并删除 |

## 引擎侧对照（不在本目录，见 git 历史）

- `OrderCreate.php`：think\Model 残留移除；`$radars['options']` 未定义变量修复；saveOrderAddress 死方法删除；金额 fromMinor 赋值；异常链保留 previous；管道清单经 `OrderPipes::apply()` 加工
- `OrderRocket.php`：`radarAdditionAmount`（sn_currency 元口径）→ `radarAdditionMinor`（int 分）；radar key 常量化；BuyerInterface 解耦
- `support/src/Rocket.php`：三个从未迁移的 trait（Accessable/Arrayable/Serializable，类加载即 fatal）→ 直接实现 ArrayAccess/JsonSerializable
