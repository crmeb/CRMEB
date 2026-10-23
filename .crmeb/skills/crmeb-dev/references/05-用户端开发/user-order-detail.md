# 用户端订单详情实现

订单中台统一提供订单详情接口，采用**声明式渲染架构（v8.1 单层结构）**：后端只返回 `page_config`（含 sections + extensions），`sections[].props` 为后端直填渲染值（非 JSONPath），前端渲染引擎按配置动态渲染，**新增产品类型无需编译前端**。

## 一、架构概述

### 1.1 声明式渲染架构（v8.1 单层结构）

```
┌─────────────────────────────────────────────────────────┐
│                    后端接口返回                          │
│                                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │   page_config（单层：配置+数据合一）              │  │
│  │                                                   │  │
│  │   sections[]                                      │  │
│  │   ├─ key          模块标识                        │  │
│  │   ├─ component    前端组件名                      │  │
│  │   ├─ props        渲染值（后端直填，非 JSONPath）  │  │
│  │   ├─ visible      是否可见（后端算好，只下发 true）│  │
│  │   └─ order        排序权重                        │  │
│  │                                                   │  │
│  │   extensions                                      │  │
│  │   ├─ extra_info / custom_form / invoice           │  │
│  │   └─ 各监听器注入的扩展数据                        │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  注：visible=false 的 section 不随接口下发（减小响应）   │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              前端 OrderDetailEngine                      │
│                                                         │
│  1. 读取 page_config.sections                           │
│  2. 按 order 排序（visible=false 已在后端过滤）          │
│  3. 对每个 section，按 component 找到组件                │
│  4. 直接将 props 传给组件（无需 JSONPath 解析）          │
│  5. 渲染                                                 │
│                                                         │
│  新增产品类型 → 前端零修改                               │
└─────────────────────────────────────────────────────────┘
```

> **v8.1 架构演进**：旧版为双层 `{page_config, data}` + JSONPath 绑定，新版简化为单层 `page_config`，props 直填渲染值，前端引擎无需二次解析。`data` 分组（status/product/pricing 等）仍在中台 `buildGroupedData()` 内部组装，但仅用于生成 sections，不随接口下发。

### 1.2 模块分层（3 层共 22 模块）

| 层 | 模块数 | 说明 | 提供方 |
|----|--------|------|--------|
| 第一层：核心模块 | 6 | 所有订单都有，中台统一组装 | 中台 `getDetail()` |
| 第二层：场景模块 | 5 | 按条件存在，中台计算 | 中台 `getDetail()` |
| 第三层：扩展模块 | 11 | 按需注入，事件驱动 | 各产品类型/营销/插件监听器 |

### 1.3 页面入口

- 页面文件：`frontend/uni-app/src/pages/center/order/detail/index.vue`
- 引擎文件：`frontend/uni-app/src/dynamic-pages/engines/OrderDetailEngine.vue`
- 组件目录：`frontend/uni-app/src/dynamic-pages/components/order/detail/`
- 接口文件：`frontend/uni-app/src/api/order-center.js`

## 二、后端接口

### 2.1 接口地址

```
GET /api/center/order/detail/{orderNo}
```

- 控制器：`backend/app/api/controller/v1/order/CenterOrderController.php::detail()`
- 服务层（用户端）：`backend/app/services/order/OrderCenterUserServices.php`（继承 `OrderCenterServices`）
- 核心组装：`backend/app/services/order/OrderCenterServices.php::getDetail()`

### 2.2 eb_order_center 中台表结构

`eb_order_center` 是订单数据的**单一数据源**，所有订单详情字段优先从此表读取。

| 分组 | 列名 | 类型 | 说明 |
|------|------|------|------|
| **订单标识** | `order_no` | varchar(32) | 订单号（全局唯一） |
| | `source_type` | varchar(32) | 来源类型标识（normal_order/virtual_order/cardkey_order/hotel_order/ticket_order） |
| | `source_id` | int(11) | 源订单表ID |
| | `uid` | int(11) | 用户ID |
| **分类维度** | `order_category` | varchar(32) | 订单大类：goods/points/member/recharge |
| | `product_type` | varchar(32) | 产品类型（normal/virtual/cardkey/hotel/ticket） |
| | `activity_type` | varchar(32) | 活动类型（seckill/combination/bargain/advance） |
| | `activity_id` | int(10) unsigned | 活动ID |
| **统一状态** | `status_code` | varchar(32) | 统一状态码（pending_payment/processing/completed/canceled/after_sale/refunded） |
| | `pay_status` | tinyint(1) | 支付状态：0未支付 1已支付 2部分退款 3全额退款 |
| | `refund_status` | tinyint(1) | 退款状态：0无退款 1申请中 2已退款 |
| **金额体系** | `total_price` | decimal(10,2) | 订单总金额 |
| | `pay_price` | decimal(10,2) | 实际支付金额 |
| | `pay_postage` | decimal(10,2) | 运费 |
| | `refund_price` | decimal(10,2) | 已退款金额 |
| | `pay_type` | varchar(32) | 支付方式key |
| | `coupon_price` | decimal(10,2) | 优惠券抵扣金额 |
| | `deduction_price` | decimal(10,2) | 积分抵扣金额 |
| | `use_integral_num` | int(11) | 使用的积分数 |
| | `member_discount` | decimal(10,2) | 等级会员优惠金额 |
| | `vip_discount` | decimal(10,2) | 付费会员优惠金额 |
| | `gift_price` | decimal(10,2) | 礼品费用 |
| **订单概要** | `total_num` | int(11) | 商品数量总数 |
| | `title` | varchar(255) | 订单标题摘要 |
| | `image` | varchar(500) | 缩略图 |
| | `subtitle` | varchar(255) | 副标题/SKU |
| **配送/履约** | `store_id` | int(11) | 门店ID（到店自提 >0） |
| | `verify_code` | varchar(32) | 核销码 |
| | `shipping_type` | tinyint(1) | 配送方式：1=快递配送 2=到店自提 |
| **扩展元数据** | `extra_info` | json | 扩展信息JSON |
| | `custom_form` | json | 自定义表单数据 |
| | `invoice` | json | 发票信息快照（为空时从 eb_store_order_invoice 查询） |
| | `mark` | text | 买家留言 |
| | `remark` | varchar(500) | 管理员备注 |
| **时间线** | `add_time` | int(11) | 下单时间 |
| | `pay_time` | int(11) | 支付时间 |
| | `delivery_time` | int(11) | 发货/履约时间 |
| | `take_time` | int(11) | 收货/完成时间 |
| | `refund_time` | int(11) | 退款时间 |
| | `cancel_time` | int(11) | 取消时间 |

### 2.3 响应结构（v8.1 单层）

接口返回单层 `{ page_config }` 结构，`page_config` 含 `sections` + `extensions`：

```jsonc
{
  "page_config": {
    "sections": [
      // 每个 section 声明一个渲染模块（visible=false 的不下发）
      {
        "key": "status",                    // 模块标识（唯一）
        "component": "status_bar",          // 前端组件名（见组件库）
        "props": {                          // 渲染值（后端直填，非 JSONPath）
          "text": "进行中",
          "icon": "icon-jinxingzhong",
          "subtitle": "商品正在配送中",
          "add_time": 1753987200,
          "code": "processing",
          "pay_status": 1
        },
        "visible": true,                    // 是否可见（后端算好，只下发 true）
        "order": 10                         // 排序权重（升序）
      }
      // ... 更多 sections
    ],
    "extensions": {
      // 扩展数据（extra_info / custom_form / invoice / 各监听器注入数据）
      "extra_info": { /* ... */ },
      "custom_form": [ /* ... */ ],
      "invoice": { /* ... */ }
    }
  }
}
```

**section 字段说明**：

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `key` | string | ✅ | 模块标识，同类型可重复（如多个 action_card） |
| `component` | string | ✅ | 前端组件名，必须在组件库中预置 |
| `props` | object | ❌ | 组件属性，**后端直填渲染值**（非 JSONPath），前端引擎直接传给组件 |
| `visible` | boolean | ❌ | 是否可见，默认 true。后端根据订单状态/类型算好，**false 的 section 不随接口下发** |
| `order` | int | ❌ | 排序权重，默认按组件库默认顺序。升序排列 |

> **props 直填渲染值**：v8.1 简化契约，去掉 JSONPath 二次解析。后端 `buildDefaultPageConfig()` 从 `buildGroupedData()` 的分组数据中读取最终值，直接填入 props。前端引擎无需实现 `getValueByPath()` 解析逻辑。

### 2.4 数据分组结构（22 模块，中台内部组装）

> **说明**：以下分组结构由中台 `buildGroupedData()` 内部组装，**不随接口直接下发**。它仅用于 `buildDefaultPageConfig()` 生成 sections 的 props 渲染值。接口实际返回见 2.3 节单层 `page_config`。
> 以下按 3 层分组整理，标注数据源、存在条件。`// ←` 表示数据来源。

```jsonc
{
  // ============================================================
  // 第一层：核心模块（6个，中台统一组装，所有订单都有）
  // ============================================================

  // 1. status — 状态头部+进度条
  "status": {
    "code": "processing",                  // ← 扩展表实时推导（adapter.resolveStatusCode）
    "text": "进行中",                       // ← OrderCenter::$statusMap
    "icon": "icon-jinxingzhong",           // ← 中台按 code 映射
    "subtitle": "商品正在配送中",            // ← business_status.text
    "source_status": "pending_shipped",    // ← 扩展表源状态（透传）
    "business_status": {                   // ← extra_info.business_status
      "code": "pending_receipt", "text": "待收货", "detail": "", "extend": []
    },
    "pay_status": 1,                       // ← eb_order_center.pay_status
    "refund_status": 0,                    // ← eb_order_center.refund_status
    "progress": {                          // ← Handler getProgress()
      "current": 2, "status": "processing",
      "nodes": [
        {"title": "提交订单", "key": "submit", "completed": true, "active": false, "time": "2026-07-29 12:00:00"},
        {"title": "支付成功", "key": "pay", "completed": true, "active": false, "time": "2026-07-29 12:01:00"},
        {"title": "商品发货", "key": "delivery", "completed": false, "active": true, "time": ""},
        {"title": "确认收货", "key": "take", "completed": false, "active": false, "time": ""},
        {"title": "订单完成", "key": "complete", "completed": false, "active": false, "time": ""}
      ]
    }
  },

  // 2. product — 商品卡片
  "product": {
    "title": "商品名称",                     // ← eb_order_center.title
    "image": "https://...",                 // ← eb_order_center.image
    "subtitle": "",                         // ← eb_order_center.subtitle
    "total_num": 1,                         // ← eb_order_center.total_num
    "items": [{                             // ← loadOrderItems()，优先 extra_info.items
      "cart_id": 3, "product_id": 3,
      "image": "https://...", "store_name": "商品名称",
      "price": "100.00", "true_price": "100.00",
      "cart_num": 1, "product_type": "normal",
      "sku": "", "attr_info": {}, "postage_price": "0.00"
    }]
  },

  // 3. pricing — 价格明细
  "pricing": {
    "items": [                              // ← buildPricingItems() 从实体列+事件组装
      {"label": "商品总额", "value": "100.00", "type": "subtotal"},
      {"label": "运费", "value": "10.00", "type": "shipping"},
      {"label": "优惠券抵扣", "value": "-20.00", "type": "coupon"},
      {"label": "积分抵扣", "value": "-5.00", "type": "integral"},
      {"label": "会员折扣", "value": "-3.00", "type": "member"}
    ],
    "total_price": "100.00",                // ← eb_order_center.total_price
    "pay_price": "82.00",                   // ← eb_order_center.pay_price
    "pay_postage": "10.00",                 // ← eb_order_center.pay_postage
    "refund_price": "0.00",                 // ← eb_order_center.refund_price
    "coupon_price": "20.00",                // ← 实体列 > extra_info 回退
    "deduction_price": "5.00",              // ← 实体列 > extra_info 回退
    "use_integral_num": 50,                 // ← 实体列 > extra_info 回退
    "member_discount": "3.00",              // ← 实体列 > extra_info 回退
    "vip_discount": "0.00",                 // ← 实体列
    "gift_price": "0.00"                    // ← 实体列
  },

  // 4. order_info — 订单基本信息
  "order_info": {
    "id": 1,                                // ← eb_order_center.id
    "order_no": "cp123456789",              // ← eb_order_center.order_no
    "uid": 1,                               // ← eb_order_center.uid
    "order_category": "goods",              // ← eb_order_center.order_category
    "product_type": "normal",               // ← eb_order_center.product_type
    "activity_type": "normal",              // ← eb_order_center.activity_type
    "activity_id": 0,                       // ← eb_order_center.activity_id
    "source_type": "normal_order",          // ← eb_order_center.source_type
    "source_id": 1,                         // ← eb_order_center.source_id
    "pay_type": "weixin",                   // ← eb_order_center.pay_type
    "pay_type_name": "微信支付",             // ← eb_pay_channel 查中文名
    "mark": "",                             // ← eb_order_center.mark（买家留言）
    "remark": "",                           // ← eb_order_center.remark（管理员备注）
    "add_time": 1753987200,                 // ← eb_order_center.add_time
    "pay_time": 1753987300,                 // ← eb_order_center.pay_time
    "delivery_time": 0,                     // ← eb_order_center.delivery_time
    "take_time": 0,                         // ← eb_order_center.take_time
    "refund_time": 0,                       // ← eb_order_center.refund_time
    "cancel_time": 0,                       // ← eb_order_center.cancel_time
    "is_del": 0,                            // ← eb_order_center.is_del
    "detail_route": "/pages/center/order/detail/index?order_no=cp123456789",
    "admin_detail_url": "/admin/product/normal/order/detail/cp123456789"
  },

  // 5. timeline — 时间线（移动端不返回）
  "timeline": [                             // ← eb_order_log
    {"title": "提交订单", "desc": "订单创建成功", "time": "2026-07-29 12:00:00"},
    {"title": "支付成功", "desc": "微信支付 ¥82.00", "time": "2026-07-29 12:01:00"}
  ],

  // 6. buttons — 底部操作按钮
  "buttons": [                              // ← Handler getStatusButtons()
    {"text": "查看物流", "action": "viewLogistics", "params": {"order_no": "cp123456789"}},
    {"text": "确认收货", "action": "confirmReceipt", "primary": true, "params": {"order_no": "cp123456789"}},
    {"text": "申请退款", "action": "applyRefund", "params": {"order_no": "cp123456789"}}
  ],

  // ============================================================
  // 第二层：场景模块（5个，按条件存在，中台计算）
  // ============================================================

  // 7. delivery — 配送/自提卡片（product_type=normal 时存在）
  "delivery": {
    "shipping_type": 1,                     // ← eb_order_center.shipping_type（1=快递配送 2=到店自提）
    "is_pickup": false,                     // ← shipping_type == 2
    "store_id": 0,                          // ← eb_order_center.store_id
    // 快递配送（shipping_type=1）时有 contact：
    "contact": {                            // ← extra_info.contact > 扩展表回退
      "name": "张三", "phone": "13800138000",
      "address": {"province": "广东省", "city": "深圳市", "district": "南山区", "detail": "科技园", "postal_code": ""}
    },
    "user_address": "广东省深圳市南山区科技园",  // ← contact.address 格式化字符串
    // 到店自提（shipping_type=2）时有 store（contact 被 unset）：
    "store": {                              // ← eb_system_store（按 store_id 查）
      "id": 1, "name": "深圳旗舰店", "phone": "0755-xxxxx",
      "address": "广东省深圳市南山区", "detailed_address": "xxx路xx号",
      "image": "https://...", "latitude": "22.54309", "longitude": "114.05786", "day_time": "08:00-20:00"
    }
  },

  // 8. countdown — 待支付倒计时（status_code=pending_payment 时存在）
  "countdown": {
    "end_time": 1753988100,                 // ← add_time + 配置超时秒数
    "remaining": 900,                       // ← 剩余秒数（后端算好，前端只做倒计时显示）
    "text": "请在 15:00 内完成支付，超时订单将自动取消"
  },

  // 9. verification — 核销详情（shipping_type=2 或需要核销的订单时存在）
  "verification": {
    "code": "TK123456",                     // ← eb_order_center.verify_code
    "qrcode_url": "/api/qrcode?text=TK123456",  // ← 中台生成的二维码图片URL
    "status": "pending",                    // pending=待核销 verified=已核销
    "verified_time": 0                      // 核销时间，0=未核销
  },

  // 10. notice — 运营提示条（按状态规则生成）
  "notice": [                               // ← 中台按 status_code + 配置计算
    {"type": "info", "text": "您的订单已发货，请留意收货"},
    {"type": "warning", "text": "签收后请在7天内确认收货"}
  ],

  // 11. related_orders — 关联订单（换货等衍生单时存在；系统不支持拆单，无子订单）
  "related_orders": [                       // ← 中台查关联订单
    {"order_no": "cp123456791", "relation": "exchange", "title": "换货订单", "status_text": "进行中"}
  ],

  // ============================================================
  // 第三层：扩展模块（11个，事件注入，按需存在）
  // ============================================================

  "extensions": {

    // 12. fulfillment — 履约凭证（卡密/下载链接/券码/电子票）
    // 由各产品类型监听器注入，结构因类型而异
    "fulfillment": {
      // cardkey 卡密商品：
      "type": "cardkey",
      "cards": [
        {"card_no": "CK001", "card_pwd": "PWD001", "valid_days": 30, "status": "unused", "expire_time": 1756675200}
      ],
      // virtual 虚拟商品：
      // "type": "virtual", "download_url": "https://...", "download_password": "abc123", "expire_time": 0
      // coupon 优惠券商品：
      // "type": "coupon", "coupon_code": "XYZ789", "face_value": "50.00", "expire_time": 1756675200
      // ticket 票务：
      // "type": "ticket", "venue": "国家体育馆", "show_time": 1759383000, "seat": "A区3排5座", "ticket_code": "T001"
    },

    // 13. activity — 活动进度（拼团/砍价/秒杀/积分）
    // 由交易模式监听器注入
    "activity": {
      "type": "combination",                // combination/bargain/seckill/integral
      // combination 拼团：
      "group_id": 10, "status": "grouping", "need_num": 3, "current_num": 1,
      "members": [{"uid": 1, "avatar": "...", "nickname": "张三", "is_head": true}],
      "expire_time": 1754073600,
      // bargain 砍价：
      // "type": "bargain", "bargain_id": 10, "current_price": "50.00", "min_price": "30.00",
      // "helpers": [{"uid": 2, "avatar": "...", "nickname": "李四", "cut_price": "5.00"}]
      // seckill 秒杀：
      // "type": "seckill", "seckill_id": 10, "original_price": "199.00", "seckill_price": "99.00"
      // integral 积分：
      // "type": "integral", "use_integral": 500, "integral_price": "50.00"
    },

    // 14. after_sale — 售后信息（有售后记录时存在）
    // 由售后模块监听器注入
    "after_sale": {
      "after_sale_no": "AS20260729001",
      "type": "refund",                     // refund/return_refund/exchange/repair
      "status": "processing",               // applying/processing/completed/rejected/canceled
      "status_text": "处理中",
      "amount": "82.00",                    // 退款金额
      "reason": "商品质量问题",
      "apply_time": 1753990000,
      "timeline": [                         // 售后进度时间线
        {"title": "申请退款", "time": "2026-07-29 14:00:00", "desc": "用户申请退款"},
        {"title": "商家审核", "time": "", "desc": "等待商家处理"}
      ],
      "return_logistics": null              // 退货物流（退货退款时存在）
    },

    // 15. discount — 优惠明细（优惠金额>0 时存在）
    // 由营销模块监听器注入
    "discount": {
      "coupon": {                           // 优惠券详情
        "coupon_id": 10, "name": "新人立减20", "face_value": "20.00", "type": "full_cut",
        "min_price": "0.00"
      },
      "full_reduction": {                   // 满减活动详情
        "activity_id": 10, "title": "满100减10", "rule": "满100元减10元"
      },
      "member": {                           // 会员权益
        "level_name": "黄金会员", "discount_amount": "3.00"
      }
    },

    // 16. review — 评价信息（已评价订单时存在）
    // 由评价模块监听器注入
    "review": {
      "status": "reviewed",                 // unreviewed/reviewed/additional
      "items": [
        {
          "product_id": 3, "product_name": "商品名称",
          "rating": 5, "comment": "很好用", "images": ["https://..."],
          "create_time": 1754073600,
          "merchant_reply": "感谢您的评价", "reply_time": 1754080000
        }
      ]
    },

    // 17. logistics — 物流轨迹（已发货实物订单）
    // 由物流模块监听器注入（或前端单独调接口）
    "logistics": {
      "delivery_name": "顺丰速运",
      "delivery_id": "SF1234567890",
      "delivery_type": "express",
      "tracks": [
        {"time": "2026-07-29 14:00:00", "status": "已揽收", "location": "深圳市"},
        {"time": "2026-07-29 16:00:00", "status": "运输中", "location": "深圳市"}
      ]
    },

    // 18. gift — 赠品列表（有赠品时存在）
    "gift": [
      {"product_id": 100, "image": "https://...", "name": "赠品小样", "num": 1}
    ],

    // 19. service — 上门服务信息（上门服务类型订单）
    // 由上门服务产品类型监听器注入
    "service": {
      "address": "广东省深圳市南山区科技园路1号",
      "contact_name": "张三", "contact_phone": "13800138000",
      "appointment_time": "2026-08-01 10:00-12:00",
      "service_staff": {"name": "技师王", "phone": "13900139000", "avatar": "..."},
      "status": "appointed"                 // appointed/in_service/completed
    },

    // 20. payment — 支付详情（已支付订单）
    "payment": {
      "trade_no": "4200000000202607291234567890",  // 第三方交易号
      "pay_time": 1753987300,
      "pay_method": "JSAPI",                // 支付方式（JSAPI/NATIVE/APP）
      "pay_price": "82.00"
    },

    // 21. agent — 分销佣金（分销插件注入）
    "agent": {
      "spread_uid": 100,
      "one_brokerage": "10.00",
      "two_brokerage": "5.00"
    },

    // 22. 营销入口类（按 status_code + 配置注入）
    "lottery": {                            // 抽奖入口（支付成功/订单完成）
      "title": "恭喜下单成功，抽取惊喜大奖",
      "image": "https://...", "link": "/pages/marketing/lottery/index"
    },
    "coupon_gift": [                        // 领券入口（订单完成）
      {"coupon_id": 20, "name": "5元无门槛券", "face_value": "5.00", "expire_time": 1756675200, "status": "unreceived"}
    ],
    "promotion": [                          // 促销引导（促转化/复购）
      {"title": "该商品正在参加秒杀", "image": "https://...", "link": "/pages/product/seckill/index"}
    ],
    "points_reward": {                      // 积分奖励（已完成）
      "get_points": 82, "status": "arrived", "text": "确认收货已获得 82 积分"
    },

    // 23. 通用增值类
    "recommend": [                          // 推荐商品
      {"product_id": 200, "store_name": "相关推荐商品", "image": "https://...", "price": "99.00"}
    ],
    "service_contact": {                    // 客服联系
      "type": "customer_service",           // customer_service/phone/wechat
      "phone": "400-xxx-xxxx", "qr_code": "https://...", "link": "/pages/customer/service"
    },
    "faq": [                                // 常见问题
      {"q": "如何申请退款？", "a": "在订单详情点击申请退款..."},
      {"q": "多久能收到货？", "a": "一般3-5个工作日..."}
    ],
    "after_sale_rule": {                    // 售后规则提示
      "deadline": 7, "deadline_unit": "day",
      "available_types": ["refund", "return_refund", "exchange"],
      "text": "支持7天内退货退款"
    },
    "share": {                              // 分享入口
      "title": "分享给好友", "type": "group_buy", "link": "/pages/share/order?no=cp123456789"
    },
    "contract": {                           // 合同/协议（大额/B端订单）
      "title": "查看服务协议", "url": "https://...", "signed": false
    },

    // 24. 通用扩展容器
    "custom_form": [                        // 自定义表单回显（custom_form 非空时）
      {"label": "custom_field_0", "title": "观众姓名", "value": "张三"}
    ],
    "invoice": {                            // 发票信息（order_center.invoice 为空时从 eb_store_order_invoice 查询）
      "title": "公司名称",                   // 发票抬头名称
      "header_type": 2,                     // 1=个人 2=企业
      "type": 1,                            // 1=电子发票 2=纸质发票
      "tax_number": "91110000MA00XXXXXX",   // 税号（企业抬头才有）
      "email": "test@example.com",          // 接收邮箱
      "phone": "13800138000",               // 联系电话
      "address": "北京市...",                // 地址
      "bank": "测试银行",                    // 开户银行
      "bank_account": "6222...",            // 银行账号
      "is_invoice": 1,                      // 0=未开票 1=已开票
      "invoice_number": "INV20260729001",   // 发票号码（已开票才有）
      "invoice_time": 1785325477,           // 开票时间（已开票才有）
      "is_refund": 0                        // 0=正常 1=已红冲
    },

    // 25. 原始扩展元数据（始终存在，供深度定制用）
    "extra_info": {                         // ← eb_order_center.extra_info（JSON 解析后）
      "items": [], "sku_info": {}, "product_id": 3, "store_info": {}, "activity_info": []
    }
  }
}
```

### 2.5 page_config 默认 sections 配置

中台根据订单状态和产品类型，组装默认的 sections 配置。各产品类型可通过 Handler 覆盖。

**默认 sections（按 order 升序）**：

| order | key | component | visible 条件 | 说明 |
|-------|-----|-----------|-------------|------|
| 10 | `status` | `status_bar` | 始终 | 状态头部+进度条 |
| 15 | `countdown` | `countdown` | `status.code == 'pending_payment'` | 待支付倒计时 |
| 20 | `progress` | `progress_bar` | `status.code ∉ {after_sale, refunded, canceled}` | 进度条（独立展示） |
| 25 | `notice` | `notice_bar` | `notice != null` | 运营提示条 |
| 30 | `activity` | `activity_card` | `extensions.activity != null` | 活动进度（拼团等） |
| 40 | `delivery` | `address_card` / `store_card` | `product_type == 'normal'` | 配送/自提（按 shipping_type 选组件） |
| 50 | `product` | `product_card` | 始终 | 商品卡片 |
| 55 | `gift` | `product_list` | `extensions.gift != null` | 赠品列表 |
| 60 | `fulfillment` | `card_list` / `download_link` / `coupon_code` / `ticket_info` / `info_list` | `extensions.fulfillment != null` | 履约凭证（按 fulfillment.type 选组件） |
| 65 | `verification` | `qrcode` | `is_pickup && product_type=='normal' && verify_code` | 核销码 |
| 70 | `custom_form` | `custom_form` | `custom_form != null` | 自定义表单 |
| 75 | `logistics` | `timeline` | `extensions.logistics != null` | 物流轨迹 |
| 80 | `pricing` | `price_detail` | 始终 | 价格明细 |
| 85 | `order_info` | `info_list` | 始终 | 订单信息 |
| 90 | `invoice` | `info_list` | `extensions.invoice != null` | 发票（含开票状态） |
| 93 | `payment` | `info_list` | `pay_status == 1` | 支付详情 |
| 95 | `review` | `review_card` | `extensions.review != null` | 评价 |
| 100 | `after_sale` | `after_sale_progress` | `extensions.after_sale != null` | 售后进度 |
| 105 | `related_orders` | `related_order_list` | `related_orders != null` | 关联订单 |
| 110 | `discount` | `info_list` | `extensions.discount != null` | 优惠明细 |
| 113 | `agent` | `info_list` | `extensions.agent != null` | 分销信息 |
| 115 | `recommend` | `product_list` | `extensions.recommend != null` | 推荐商品 |
| 118 | `service` | `service_appointment` | `extensions.service != null` | 上门服务 |
| 120 | `lottery` | `action_card` | `extensions.lottery != null` | 抽奖入口 |
| 122 | `coupon_gift` | `action_card` | `extensions.coupon_gift != null` | 领券入口 |
| 124 | `promotion` | `action_card` | `extensions.promotion != null` | 促销引导 |
| 126 | `points_reward` | `action_card` | `extensions.points_reward != null` | 积分奖励 |
| 128 | `service_contact` | `action_card` | `extensions.service_contact ?? customer_service_switch` | 客服联系（受全局开关控制） |
| 130 | `share` | `action_card` | `extensions.share != null` | 分享入口 |
| 132 | `contract` | `contract_entry` | `extensions.contract != null` | 合同/协议 |
| 135 | `faq` | `faq_list` | `extensions.faq != null` | 常见问题 |
| 140 | `after_sale_rule` | `notice_bar` | `extensions.after_sale_rule != null` | 售后规则提示 |
| 900 | `timeline` | `timeline` | 始终（移动端移除） | 时间线 |
| 999 | `buttons` | `button_group` | `buttons.length > 0` | 操作按钮（固定底部） |

> **payment 数据来源**：`trade_no`/`pay_method` 从 `extra_info` 读取（缺失留空），`pay_time`/`pay_price` 从实体列读取。
> **service_contact 可见性**：扩展模块注入 `extensions.service_contact` 时按其值判断；未注入时读取 `sys_config('customer_service_switch', 1)` 全局开关，默认显示。

### 2.6 数据源优先级规则

```
eb_order_center 实体列 > extra_info JSON > 扩展订单表回退 > Handler 动态生成 > 事件注入
```

| 字段 | 优先级 | 说明 |
|------|--------|------|
| `store_id` / `shipping_type` / `verify_code` | 实体列 | extra_info/扩展表不双写 |
| `delivery.contact` | extra_info > 扩展表 | 优先 `extra_info.contact`，缺失时从扩展表字段回退 |
| `status.code` | 扩展表实时推导 | 实体列存储值被扩展表实时重算覆盖 |
| `product.items` | extra_info > 购物车表 | 优先 `extra_info.items`，缺失时从源购物车表加载 |
| `pricing.coupon_price` 等金额 | 实体列 > extra_info | 实体列值为 0/0.00 时从 extra_info 回退 |

### 2.7 模块存在条件汇总

| 模块 | 存在条件 | 提供方 |
|------|---------|--------|
| status | 始终存在 | 中台 |
| product | 始终存在 | 中台 |
| pricing | 始终存在 | 中台 |
| order_info | 始终存在 | 中台 |
| timeline | 始终存在（移动端移除） | 中台 |
| buttons | 非空 | Handler |
| delivery | `product_type=normal` | 中台 |
| countdown | `status_code=pending_payment` | 中台 |
| verification | `shipping_type=2` 或需核销 | 中台 |
| notice | 按状态规则 | 中台 |
| related_orders | 有换货等衍生单 | 中台 |
| extensions.fulfillment | 各产品类型注入 | 产品类型监听器 |
| extensions.activity | 交易模式注入 | 交易模式监听器 |
| extensions.after_sale | 有售后记录 | 售后模块监听器 |
| extensions.discount | 优惠金额>0 | 营销模块监听器 |
| extensions.review | 已评价 | 评价模块监听器 |
| extensions.logistics | 已发货 | 物流模块监听器 |
| extensions.gift | 有赠品 | 营销模块监听器 |
| extensions.service | 上门服务类型 | 产品类型监听器 |
| extensions.payment | 已支付 | 中台/支付模块 |
| extensions.agent | 分销插件 | 分销监听器 |
| extensions.lottery 等 | 按状态+配置 | 营销模块监听器 |
| extensions.custom_form | custom_form 非空 | 中台 |
| extensions.invoice | order_center.invoice 非空或 eb_store_order_invoice 有记录 | 中台 |
| extensions.recommend | 有推荐数据 | 推荐模块监听器 |
| extensions.faq | 有配置 | 中台/配置 |
| extensions.after_sale_rule | 有售后规则 | 产品类型 Handler |
| extensions.share | 需分享 | 营销模块监听器 |
| extensions.contract | 有合同 | 合同模块监听器 |
| extensions.service_contact | 有客服配置 | 中台/配置 |

## 三、后端组装逻辑

### 3.1 getDetail() 完整组装流程

```
┌─ [Step 1] 主表查询 ─────────────────────────────────────────┐
│  $this->dao->get(['id' => $id]) → toArray()                 │
│  返回 eb_order_center 全部实体列                              │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 2] JSON 字段解析 ────────────────────────────────────┐
│  • extra_info  → parseExtraInfo() 解析 JSON                  │
│  • custom_form → parseExtraInfo() + transformCustomForm()    │
│  • invoice     → parseExtraInfo()（为空时 Step 3.6 从发票表查询）│
│  • pay_type    → getPayTypeName() → pay_type_name            │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 3] 源订单表实时信息加载 ──────────────────────────────┐
│  loadSourceOrderInfo(&$detail):                              │
│  ├─ adapter.resolveStatusCode() → 重算 status_code           │
│  ├─ adapter.resolveBusinessStatus() → business_status        │
│  ├─ 透传扩展表源状态 → source_status                         │
│  └─ contact 回退：extra_info 无 contact 时从扩展表组装        │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 3.5] 同步状态文案 ────────────────────────────────────┐
│  status_text = OrderCenter::$statusMap[status_code]          │
│  loadSourceOrderInfo 可能重算 status_code，此处同步刷新文案   │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 3.6] 发票信息加载 ────────────────────────────────────┐
│  order_center.invoice 为空时：                               │
│  loadOrderInvoice($id) → 查 eb_store_order_invoice 表        │
│  按 order_id 关联，组装 title/header_type/type/tax_number/  │
│  email/is_invoice/invoice_number/invoice_time 等完整字段    │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 4] 组装核心模块数据 ──────────────────────────────────┐
│  • status: 合并 status_code/status_text/business_status/     │
│            progress (Handler getProgress)                    │
│  • product: title/image/subtitle/total_num + order_items     │
│  • pricing: 实体列金额 + buildPricingItems() 明细数组         │
│  • order_info: 订单基本信息 + 时间戳                         │
│  • timeline: eb_order_log 读取                               │
│  • buttons: Handler getStatusButtons()                       │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 5] 组装场景模块数据 ──────────────────────────────────┐
│  • delivery: shipping_type/store_id + contact/store          │
│  • countdown: add_time + 配置超时 → end_time/remaining       │
│  • verification: verify_code + 生成 qrcode_url               │
│  • notice: 按 status_code 规则生成提示条数组                 │
│  • related_orders: 查关联订单                                │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 6] 金额回退处理 ─────────────────────────────────────┐
│  $priceFields 循环：实体列值为 0 时从 extra_info 回退         │
│  coupon_price / deduction_price / use_integral_num /          │
│  member_discount / vip_discount / gift_price                 │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 7] 商品明细加载 ─────────────────────────────────────┐
│  loadOrderItems() → product.items                            │
│  优先 extra_info.items，缺失时从源购物车表加载                │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 8] 触发扩展事件 ─────────────────────────────────────┐
│  event('OrderCenterDetailExtend', [                          │
│      'detail' => &$detail,                                   │
│      'source_type' => $detail['source_type'],                │
│      'source_id' => $detail['source_id'],                    │
│  ])                                                          │
│  监听器向 detail.extensions.* 注入数据                       │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 9] 组装 page_config ─────────────────────────────────┐
│  • buildDefaultPageConfig($data) 从分组数据读取值直填 props  │
│  • 调用 Handler::getPageConfig('order_detail') 获取声明配置  │
│  • Handler 未实现或返回空时使用中台默认 sections             │
│  • 根据 detail 数据计算每个 section 的 visible               │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─ [Step 10] 过滤+移动端裁剪 ─────────────────────────────────┐
│  • 只保留 visible=true 的 section（减小响应体积）            │
│  • 移动端请求时：unset timeline + 移除 timeline section      │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
              return ['page_config' => ['sections' => [...], 'extensions' => [...]]]
```

### 3.2 buildPricingItems() 价格明细组装

```php
/**
 * 组装价格明细数组
 * @param array $detail 订单详情
 * @return array {items: [...], pay_price: string}
 */
protected function buildPricingItems(array $detail): array
{
    $items = [];
    // 商品总额（始终存在）
    $items[] = ['label' => '商品总额', 'value' => number_format($detail['total_price'] ?? 0, 2), 'type' => 'subtotal'];

    // 运费（>0 才显示）
    if (($detail['pay_postage'] ?? 0) > 0) {
        $items[] = ['label' => '运费', 'value' => '+' . number_format($detail['pay_postage'], 2), 'type' => 'shipping'];
    }
    // 优惠券抵扣
    if (!empty($detail['coupon_price'])) {
        $items[] = ['label' => '优惠券抵扣', 'value' => '-' . number_format($detail['coupon_price'], 2), 'type' => 'coupon'];
    }
    // 积分抵扣
    if (!empty($detail['deduction_price'])) {
        $items[] = ['label' => '积分抵扣', 'value' => '-' . number_format($detail['deduction_price'], 2), 'type' => 'integral'];
    }
    // 会员折扣
    if (!empty($detail['member_discount'])) {
        $items[] = ['label' => '会员折扣', 'value' => '-' . number_format($detail['member_discount'], 2), 'type' => 'member'];
    }
    // 事件注入额外金额项（服务费、上门费等）
    $eventData = ['items' => &$items, 'detail' => $detail];
    event('OrderPricingExtend', $eventData);

    return [
        'items'     => $eventData['items'],
        'pay_price' => number_format($detail['pay_price'] ?? 0, 2),
    ];
}
```

### 3.3 默认 page_config 组装（props 直填渲染值）

```php
/**
 * 组装默认 page_config sections（v8.1 单层结构，props 直填渲染值）
 * @param array $detail buildGroupedData() 返回的分组数据
 * @return array sections 数组
 */
protected function buildDefaultPageConfig(array $detail): array
{
    // $detail 为 buildGroupedData 的分组结构，从各功能域分组读取
    $statusCode = $detail['status']['code'] ?? '';
    $isPickup = ($detail['delivery']['shipping_type'] ?? 0) == 2;
    $productType = $detail['order_info']['product_type'] ?? 'normal';
    $sections = [];

    // 状态头部（始终存在）
    // props 直填渲染值（v8.1 简化契约：去掉 JSONPath，前端引擎无需二次解析）
    $sections[] = [
        'key'       => 'status',
        'component' => 'status_bar',
        'props'     => [
            'text'       => $detail['status']['text'] ?? '',
            'icon'       => $detail['status']['icon'] ?? '',
            'subtitle'   => $detail['status']['subtitle'] ?? '',
            'add_time'   => $detail['order_info']['add_time'] ?? 0,
            'code'       => $detail['status']['code'] ?? '',
            'pay_status' => $detail['status']['pay_status'] ?? 0,
        ],
        'visible'   => true,
        'order'     => 10,
    ];

    // 待支付倒计时
    $countdown = $detail['countdown'] ?? null;
    $sections[] = [
        'key'       => 'countdown',
        'component' => 'countdown',
        'props'     => [
            'end_time'  => $countdown['end_time'] ?? 0,
            'remaining' => $countdown['remaining'] ?? 0,
            'text'      => $countdown['text'] ?? '',
        ],
        'visible'   => $statusCode === 'pending_payment',
        'order'     => 15,
    ];

    // 进度条（非售后/退款/取消）
    $progress = $detail['status']['progress'] ?? [];
    $sections[] = [
        'key'       => 'progress',
        'component' => 'progress_bar',
        'props'     => [
            'nodes'   => $progress['nodes'] ?? [],
            'current' => $progress['current'] ?? 0,
        ],
        'visible'   => !in_array($statusCode, ['after_sale', 'refunded', 'canceled']),
        'order'     => 20,
    ];

    // ... 其余 section 按相同模式：从 $detail 分组读取值直填 props
    // （配送/商品/价格/订单信息/发票/支付详情/售后/营销入口等，见 2.5 节完整列表）

    return ['sections' => $sections];
}
```

> **关键差异（v8.1 vs 旧版）**：
> - 旧版 props 为 JSONPath 字符串（如 `'$.status.text'`），前端引擎需 `getValueByPath()` 解析
> - v8.1 props 为后端直填渲染值（如 `$detail['status']['text'] ?? ''`），前端引擎直接 `v-bind="section.props"` 传给组件

```php
/**
 * 根据 fulfillment.type 解析对应组件名
 */
protected function resolveFulfillmentComponent(array $detail): string
{
    $fulfillment = $detail['extensions']['fulfillment'] ?? ($detail['extend']['fulfillment'] ?? []);
    $type = $fulfillment['type'] ?? '';
    switch ($type) {
        case 'cardkey':
            return 'card_list';
        case 'virtual':
            return 'download_link';
        case 'coupon':
            return 'coupon_code';
        case 'ticket':
            return 'ticket_info';
        default:
            return 'info_list';
    }
}
```

## 四、前端组件库

### 4.1 通用组件（18个）

前端需预置以下组件，渲染引擎按 `component` 字段匹配：

| 组件名 | 用途 | props |
|--------|------|-------|
| `status_bar` | 状态头部（图标+文字+副标题） | `text`, `icon`, `subtitle`, `progress` |
| `progress_bar` | 节点式进度条 | `nodes[]`, `current` |
| `countdown` | 倒计时 | `end_time`, `remaining`, `text` |
| `product_card` | 商品卡片（图+名+规格+价+数量） | `product` |
| `product_list` | 商品列表（多商品/赠品/推荐） | `items[]`, `title` |
| `price_detail` | 价格明细（多行金额） | `items[]`, `pay_price` |
| `info_list` | 键值对信息列表 | `fields`, `title` |
| `address_card` | 收货地址卡片 | `contact`, `user_address` |
| `store_card` | 门店信息卡片 | `store`, `verify_code` |
| `qrcode` | 二维码展示 | `code`, `qrcode_url`, `status` |
| `timeline` | 时间线/物流轨迹 | `items[]` / `tracks[]` |
| `button_group` | 按钮组 | `buttons[]` |
| `notice_bar` | 提示条 | `notices[]` / `text`, `type` |
| `card_list` | 卡密/券码列表 | `fulfillment` |
| `download_link` | 下载链接/网盘信息 | `fulfillment` |
| `activity_card` | 活动进度卡片 | `activity` |
| `custom_form` | 自定义表单回显 | `items[]` |
| `action_card` | 行动入口卡片（抽奖/领券/促销/分享/客服） | `title`, `image`, `link`, `type` |

> **FulfillmentCard 双格式支持**：`card_list` / `download_link` 组件底层使用 `FulfillmentCard.vue`，兼容两种数据格式：
> 1. **fields 数组格式**（推荐）：`{title, fields: [{label, value, type, selectable}]}`，后端直接下发字段数组
> 2. **类型特定格式**：按 `fulfillment.type` 自动转换为 fields 数组渲染
>    - `cardkey`：`{type:'cardkey', cards:[{card_no, card_pwd, valid_days, status}]}` — 卡密按卡片分组展示
>    - `virtual`：`{type:'virtual', download_url, download_password, expire_time}` — 下载链接可点击复制
>    - `coupon`：`{type:'coupon', coupon_code, face_value, expire_time}` — 券码大字展示
>    - `ticket`：`{type:'ticket', venue, show_time, seat, ticket_code}` — 票务信息列表
>    - `logistics`：`{type:'logistics', delivery_name, delivery_id}` — 物流信息
>    - 未知类型：自动将对象键值对转为 fields 展示

> **info_list 通用对象渲染**：`info_list` 组件支持三种渲染模式（按 `section.key` 分派）：
> 1. **专用渲染**：`order_info` / `invoice` / `payment` 三个 key 有专用模板，按固定字段布局渲染
>    - `invoice`：展示 7 字段（抬头+企业/个人标签、发票类型、税号、邮箱、开票状态、发票号码、开票时间），已开票时才显示发票号码和开票时间
> 2. **通用对象渲染**：其他 key（如 `discount` / `agent`）走 `getInfoListRows()` 通用分支，将 `fields` 对象遍历为 `[{label, value}]` 行数组，标签经 `getInfoLabel()` 映射为中文
> 3. **降级**：`fields` 为空或非对象时渲染空内容

> **action_card 图片/链接支持**：`action_card` 组件支持 `image`（图片）和 `icon`（图标）两种左侧标识，`image` 优先。带 `link` 的行动入口（抽奖/促销/分享）由 `handleActionCard(action, link)` 统一跳转到 `link` 页面；客服入口（`action=goGoodCall`）跳转客服聊天页；领券/积分奖励等无 `link` 的入口提示"功能开发中"，由插件扩展具体逻辑。

### 4.2 特殊组件（7个，按需实现）

| 组件名 | 用途 |
|--------|------|
| `review_card` | 评价卡片（星级+文字+图片+商家回复） |
| `after_sale_progress` | 售后进度（售后时间线+退货物流） |
| `service_appointment` | 上门服务预约信息 |
| `contract_entry` | 合同查看入口 |
| `related_order_list` | 关联订单列表 |
| `faq_list` | 常见问题列表 |
| `coupon_code` | 优惠券码展示 |

### 4.3 组件降级策略

前端版本低于阈值时，未知 `component` 降级为 `info_list`，避免旧版小程序崩溃。`getPageConfig` 的 `appVersion` 参数支持版本协商。

## 五、OrderDetailEngine 渲染逻辑

引擎核心逻辑：读取 `page_config.sections` → 按 order 排序 → 匹配组件 → 直接传 props → 渲染（v8.1 无需 JSONPath 解析，visible=false 已在后端过滤）。

```vue
<template>
  <view class="order-detail" v-if="!loading">
    <!-- 按 sections 声明式渲染（props 为后端直填渲染值，直接 v-bind）
         组件通过 detail/index.js 的 require.context 自动注册 -->
    <template v-for="section in sortedSections" :key="section.key">
      <component
        :is="resolveSectionComponent(section) || InfoListComponent"
        v-bind="section.props"
        @action="handleAction"
      />
    </template>
  </view>
</template>

<script>
// 组件由 dynamic-pages/components/order/detail/index.js 统一注册与解析
import { resolveSectionComponent, InfoListComponent } from '../components/order/detail/index.js';
import actionMap from '../api/action-map.js';

export default {
  name: 'OrderDetailEngine',
  data() {
    return { loading: true, pageConfig: { sections: [], extensions: {} } };
  },
  computed: {
    // sections 按 order 升序排列（visible=false 已在后端过滤，前端无需再过滤）
    sortedSections() {
      return (this.pageConfig.sections || [])
        .sort((a, b) => (a.order || 100) - (b.order || 100));
    }
  },
  methods: {
    // 按 section 解析组件（未知组件降级为 InfoListComponent）
    resolveSectionComponent(section) {
      return resolveSectionComponent(section);
    },
    // 统一处理按钮/行动卡片事件
    handleAction(payload) {
      const { action, params } = payload;
      // 委托给 action-map 统一处理
      actionMap[action] && actionMap[action](params, this);
    }
  }
};
</script>
```

**组件自动注册机制（实际实现）**：组件注册表**不是**静态 `COMPONENT_MAP` 映射，而是 `dynamic-pages/components/order/detail/index.js` 通过 `require.context('./', false, /\.vue$/)` **自动扫描**目录下所有 `.vue` 文件，按组件静态声明的 `componentKey` 注册：

```js
// dynamic-pages/components/order/detail/index.js
var ctx = require.context('./', false, /\.vue$/);
var map = {};
ctx.keys().forEach(function (k) {
  var mod = ctx(k);
  var comp = mod.default || mod;
  var ck = comp.componentKey;      // 组件内静态声明的 componentKey
  if (!ck) return;                  // 未声明 componentKey 的组件跳过（如纯工具组件）
  var keys = Array.isArray(ck) ? ck : [ck];
  keys.forEach(function (key) {
    map[key] = comp;
  });
});

/**
 * 按 section 解析对应组件
 * 查找顺序：复合 key（component:key）→ 单 key（component）→ null
 */
export function resolveSectionComponent(section) {
  if (!section) return null;
  var composite = section.component + ':' + (section.key || '');
  return map[composite] || map[section.component] || null;
}

export { default as InfoListComponent } from './InfoList.vue';
```

**新增组件方式**：只需在 `dynamic-pages/components/order/detail/` 目录放 `.vue` 文件并在组件内声明 `componentKey`，**无需修改 `index.js` 或引擎**，即自动注册（支持 `componentKey` 为字符串或数组，数组可声明多个 key）。引擎对未命中组件统一降级为 `InfoListComponent`，保证旧版前端不崩溃。

> **v8.1 简化点**：
> - 移除 `resolveProps()` / `getValueByPath()` 方法（props 已是渲染值，直接 `v-bind="section.props"`）
> - `sortedVisibleSections` 改为 `sortedSections`（后端已过滤 visible=false，前端只排序）
> - `data` 不再需要 `detailData`（无 JSONPath 解析需求）

## 六、产品类型扩展契约

### 6.1 新增产品类型的标准动作

新增一个产品类型（如"上门服务"），二开开发者只需：

```
1. modules/product/onsite/Handler.php
   ├─ 实现 ProductTypeHandlerInterface 全部方法
   ├─ 实现 fulfillOrder() — 上门服务履约逻辑
   └─ 实现 getPageConfig('order_detail') — 声明 sections
       └─ 用 service_appointment + address_card + qrcode 组件

2. modules/product/onsite/listener/OrderCenterDetailExtendListener.php
   └─ 注入 extensions.service 数据（服务人员/时间/电话）

3. modules/product/onsite/event.php
   └─ 注册 OrderCenterDetailExtend 监听器

4. 完成。前端零修改，自动渲染新模块。
```

### 6.2 Handler 接口方法（影响详情页）

| 方法 | 用途 | 返回值 |
|------|------|--------|
| `getProgress(array $detail): array` | 进度条节点 | `{current, status, nodes[]}` |
| `getOrderButtons(array $detail): array` | 底部操作按钮 | `[{text, action, primary, params}]` |
| `getPageConfig(string $scene, string $appVersion): array` | 声明式渲染配置 | `{sections: [...]}` |
| `resolveStatusCode(array $sourceOrder): string` | 从扩展表推导状态码 | status_code 字符串 |
| `resolveBusinessStatus(array $sourceOrder): ?array` | 业务子状态 | `{code, text, detail, extend}` |
| `getOrderSourceType(): string` | 订单来源类型 | `onsite_order` 等 |

### 6.3 监听器注入扩展数据

通过 `OrderCenterDetailExtend` 事件，监听器向 `detail.extensions.*` 注入数据：

```php
// modules/product/onsite/listener/OrderCenterDetailExtendListener.php
class OrderCenterDetailExtendListener implements ListenerInterface
{
    public function handle($event): void
    {
        $detail = &$event['detail'];
        // 仅处理上门服务订单
        if (($detail['source_type'] ?? '') !== 'onsite_order') {
            return;
        }
        $orderNo = $detail['order_no'] ?? '';
        // 从扩展表查上门服务信息
        $serviceInfo = app(OnsiteOrderDao::class)->getOne(['order_no' => $orderNo]);
        if (!$serviceInfo) return;

        // 注入到 extensions.service
        $detail['extensions']['service'] = [
            'address' => $serviceInfo['address'],
            'contact_name' => $serviceInfo['contact_name'],
            'contact_phone' => $serviceInfo['contact_phone'],
            'appointment_time' => $serviceInfo['appointment_time'],
            'service_staff' => [
                'name' => $serviceInfo['staff_name'],
                'phone' => $serviceInfo['staff_phone'],
                'avatar' => $serviceInfo['staff_avatar'],
            ],
            'status' => $serviceInfo['service_status'],
        ];
    }
}
```

在 `event.php` 中注册：

```php
// modules/product/onsite/event.php
return [
    'listen' => [
        'OrderCenterDetailExtend' => [
            \modules\product\onsite\listener\OrderCenterDetailExtendListener::class,
        ],
    ],
];
```

### 6.4 Handler 自定义 page_config

产品类型可覆盖默认 sections 配置：

```php
// modules/product/onsite/Handler.php
// 注意：v8.1 单层结构下，Handler 的 getPageConfig 返回的 sections 的 props
// 也应直填渲染值（从 $detail 参数读取），不再使用 JSONPath
public function getPageConfig(string $scene, string $appVersion = '1.0.0'): array
{
    if ($scene !== 'order_detail') {
        return [];
    }
    // $detail 由 getDetailWithConfig() 传入（buildGroupedData 的分组结构）
    return [
        'sections' => [
            // 上门服务订单：上门信息卡片 + 核销码 + 商品 + 价格 + 订单信息
            // props 直填渲染值（从 $detail 分组读取，非 JSONPath）
            ['key' => 'service', 'component' => 'service_appointment',
             'props' => ['service' => $detail['extensions']['service'] ?? []], 'visible' => true, 'order' => 40],
            ['key' => 'product', 'component' => 'product_card',
             'props' => ['product' => $detail['product'] ?? []], 'visible' => true, 'order' => 50],
            ['key' => 'pricing', 'component' => 'price_detail',
             'props' => ['items' => $detail['pricing']['items'] ?? [], 'pay_price' => $detail['pricing']['pay_price'] ?? '0.00'], 'visible' => true, 'order' => 80],
            // ... 其他模块
        ],
    ];
}
```

## 七、移动端特殊处理

移动端订单详情接口**不返回** `timeline` 数据，且移除 `page_config` 中的 timeline section：

```php
public function detail(OrderCenterUserServices $services, string $orderNo)
{
    $result = $services->getDetailWithConfigByOrderNo($orderNo);
    // 移动端移除时间线 section（v8.1 单层结构，无顶层 data）
    $result['page_config']['sections'] = array_values(
        array_filter($result['page_config']['sections'], fn($s) => $s['key'] !== 'timeline')
    );
    return app('json')->success($result);
}
```

## 八、注意事项

1. **数据源原则**：`store_id`、`shipping_type`、`verify_code` 仅存储在中台表实体列，扩展表和 `extra_info` 不双写。
2. **状态码实时推导**：`status.code` 必须从扩展订单表实时重算（`resolveStatusCode()`）。`loadSourceOrderInfo()` 可能修改 `status_code`，之后必须同步刷新 `status_text`（`OrderCenter::$statusMap` 映射）。
3. **contact 回退**：优先 `extra_info.contact`，缺失时从扩展订单表 `real_name/user_phone/user_address` 回退。
4. **进度条规则**：`completed` 状态基于 `status.code` 判断；`pending_payment` 强制当前节点为"提交订单"。
5. **区块裁剪**：虚拟/卡密商品不显示 `delivery` 模块；前端只根据 `visible` 决定显隐。
6. **声明式渲染**：新增模块只改 `page_config.sections` + 注入 `extensions` 数据，前端零编译。
7. **组件降级**：未知 `component` 降级为 `info_list`，保证旧版前端不崩溃。
8. **营销模块防骚扰**：营销入口模块受全局开关配置控制，监听器读取 `system_config` 决定是否注入。
9. **操作按钮**：行为统一走 `action-map.js`，避免重复实现。
10. **移动端**：不返回 `timeline` 数据和 section。
11. **引擎实现策略**：当前 `OrderDetailEngine.vue` 对简单组件（status_bar/progress_bar/countdown/product_card/price_detail/info_list/address_card/store_card/qrcode/custom_form/notice_bar/button_group/action_card/coupon_code/ticket_info/review_card/related_order_list/service_appointment/faq_list/contract_entry/after_sale_progress）采用**内联模板渲染**；对复杂组件（`FulfillmentCard`/`ActivityCard`/`OrderTimeline`）使用独立 `.vue` 文件。两种方式等价，新增组件可按复杂度选择。
12. **统一单层结构（v8.1）**：`getCenterOrderDetail` 接口统一返回 `{page_config: {sections, extensions}}` 单层结构（无顶层 data），`sections[].props` 为后端直填渲染值（非 JSONPath）。前端所有调用方直接 `res.data.page_config.sections` 取渲染配置，`res.data.page_config.extensions` 取扩展数据。支付状态判断用 `section.props.pay_status === 1`（status section 的 props 中）。
13. **payment 模块**：中台在 `buildGroupedData()` 中按 `pay_status==1` 组装 `payment` 对象，`trade_no`/`pay_method` 从 `extra_info` 读取（旧订单可能缺失，留空），`pay_time`/`pay_price` 从实体列读取。
14. **service_contact 可见性**：扩展模块可通过注入 `extensions.service_contact` 精确控制客服入口显隐；未注入时中台读取 `sys_config('customer_service_switch', 1)` 全局开关，默认显示。
15. **info_list 通用渲染**：未在 `order_info`/`invoice`/`payment` 专用分支中的 `info_list` section（如 `discount`/`agent`），走 `getInfoListRows()` 通用对象遍历，字段标签经 `getInfoLabel()` 映射中文。新增扩展模块用 `info_list` 渲染键值对时，建议在 `getInfoLabel()` 补充字段映射。
16. **action_card 行动入口**：`image` 优先于 `icon` 显示左侧标识；带 `link` 的入口由 `handleActionCard(action, link)` 跳转，无 `link` 的入口（领券/积分奖励）需由插件扩展具体逻辑。
17. **发票信息加载**：`eb_order_center.invoice` 为空时，`loadOrderInvoice()` 从 `eb_store_order_invoice` 表按 `order_id` 查询发票申请记录，组装完整发票信息（抬头/类型/税号/邮箱/开票状态/发票号码）。前端发票专用渲染展示 7 个字段：发票抬头+类型标签（企业/个人）、发票类型（电子/纸质）、税号（企业才有）、邮箱、开票状态（已开票=绿色/未开票=橙色）、发票号码（已开票才有）、开票时间（已开票才有）。
