<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\services\price;

/**
 * 价格链条位优先级定义
 *
 * 订单计价采用固定段位价格链，各优惠段按下述优先级顺序依次执行。
 * 本类为 crmeb 核心底层契约：中台计价服务（app 层）与交易营销类插件
 * （modules 层，如满减、满赠、加价购）均以本常量声明/校验自身段位，
 * 禁止自行约定其他执行顺序。
 *
 * 段位说明：
 * - [10] 会员价：随商品 SKU truePrice 进入计价基数，不单独减价
 * - [20] 促销优惠：营销插件通过 OrderPromotionCompute 事件缝口挂载（见 applyPromotion）
 * - [30] 优惠券、[40] 积分抵扣：由 OrderComputedServices 内置实现
 * - [50] 会员权益、[90] 运费：预留段位
 */
class PriceChainPriority
{
    /**
     * 段位 [10]：会员价
     * 计价基数为商品价格/SKU 价，会员价在购物车阶段已折算进 truePrice
     */
    public const MEMBER_PRICE = 10;

    /**
     * 段位 [20]：促销优惠段
     * 满减/满赠/加价购等交易营销玩法统一挂载此段，事件缝口：OrderPromotionCompute
     */
    public const PROMOTION = 20;

    /**
     * 段位 [30]：优惠券
     */
    public const COUPON = 30;

    /**
     * 段位 [40]：积分抵扣
     */
    public const INTEGRAL = 40;

    /**
     * 段位 [50]：会员权益（预留）
     */
    public const MEMBER_RIGHT = 50;

    /**
     * 段位 [90]：运费计算
     * 运费独立于商品金额优惠链，最后计算
     */
    public const POSTAGE = 90;
}
