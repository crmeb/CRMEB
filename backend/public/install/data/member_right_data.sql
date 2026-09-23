-- eb_member_right default data

INSERT INTO `eb_member_right` (`id`, `right_type`, `title`, `show_title`, `image`, `explain`, `number`, `sort`, `status`, `add_time`) VALUES
(1, 'integral', '消费返利', '消费返利', '/statics/system_images/member_integral.png', '消费返多倍积分', 2, 0, 1, 0),
(2, 'vip_price', '会员特价', '会员特价', '/statics/system_images/member_price.png', '商品享特价优惠', 1, 0, 1, 0),
(3, 'express', '运费折扣', '运费折扣', '/statics/system_images/member_freight.png', '运费折扣优惠', 90, 0, 1, 0),
(4, 'sign', '签到返利', '签到返利', '/statics/system_images/member_sign.png', '签到得多倍积分', 3, 0, 1, 0),
(5, 'offline', '线下折扣', '线下折扣', '/statics/system_images/member_discount.png', '线下支付折扣', 1, 0, 1, 0),
(6, 'coupon', '会员优惠券', '会员优惠券', '/statics/system_images/member_coupon.png', '领取会员券', 1, 0, 1, 0);