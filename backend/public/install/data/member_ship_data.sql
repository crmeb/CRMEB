-- eb_member_ship default data

INSERT INTO `eb_member_ship` (`id`, `type`, `title`, `vip_day`, `price`, `pre_price`, `sort`, `is_del`, `add_time`) VALUES
(1, 'month', '月卡', 30, '30.00', '0.01', 9, 0, 1588129765),
(2, 'quarter', '季卡', 90, '90.00', '80.00', 8, 0, 1588129794),
(3, 'year', '年卡', 365, '365.00', '300.00', 6, 0, 1588129818),
(4, 'ever', '永久', -1, '1000.00', '899.00', 1, 0, 1588129856),
(5, 'free', '试用', 7, '100.00', '0.00', 11, 0, 1588130680);