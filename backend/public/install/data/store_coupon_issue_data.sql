-- eb_store_coupon_issue default data

INSERT INTO `eb_store_coupon_issue` (`id`, `cid`, `coupon_title`, `start_time`, `end_time`, `total_count`, `remain_count`, `receive_limit`, `is_permanent`, `status`, `is_give_subscribe`, `is_full_give`, `full_reduction`, `is_del`, `add_time`, `title`, `integral`, `coupon_price`, `use_min_price`, `coupon_time`, `product_id`, `category_id`, `type`, `receive_type`, `start_use_time`, `end_use_time`, `sort`) VALUES
(1, 0, '新人专享', 0, 0, 0, 0, 1, 1, 1, 0, 0, '0.00', 0, 1676279814, '新人专享', 0, '10.00', '20.00', 90, '', 0, 0, 2, 0, 0, 0),
(2, 0, '会员专享', 0, 0, 0, 0, 1, 1, 1, 0, 0, '0.00', 0, 1676279844, '会员专享', 0, '9.90', '0.00', 30, '', 0, 0, 4, 0, 0, 0),
(3, 0, '通用券', 0, 0, 0, 0, 1, 1, 1, 0, 0, '0.00', 0, 1676279983, '通用券', 0, '7.00', '100.00', 30, '', 0, 0, 1, 0, 0, 0),
(4, 0, '商品券', 0, 0, 0, 0, 1, 1, 1, 0, 0, '0.00', 0, 1676280430, '商品券', 0, '100.00', '500.00', 30, '', 1, 1, 1, 0, 0, 0);