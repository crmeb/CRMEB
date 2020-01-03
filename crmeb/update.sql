-- 小程序新增模板消息 19 11-12
UPDATE `eb_system_menus` SET `controller` = 'setting.system_droup_data' WHERE `controller` = 'setting.systemGroupData';

-- 清除小程序模板消息
TRUNCATE `eb_routine_template`;

-- 导入小程序订阅消息
INSERT INTO `eb_routine_template` (`id`, `tempkey`, `name`, `content`, `tempid`, `add_time`, `status`) VALUES
(1, '1128', '订单配送通知', '商品信息{{thing8.DATA}}\n订单编号{{character_string1.DATA}}\n配送人{{name4.DATA}}\n配送员电话{{phone_number10.DATA}}', '4wN7p3nF1IPiPNIPZnoOY3nZlrVP3dzM-Km0OLcpW48', '1575364233', 1),
(2, '1470', '提现结果通知', '提现状态{{thing1.DATA}}\n提现金额{{amount2.DATA}}\n提现账号{{thing3.DATA}}\n提现时间{{date4.DATA}}', 'xtBEkHdxyFSIQfiNe_CRga2mrmQizfArgSk7zC3hnbs', '1575364292', 1),
(3, '1481', '收货结果通知', '订单类型{{thing1.DATA}}\n订单商品{{thing2.DATA}}\n收货时间{{date5.DATA}}', 'AVmUHvKandN9a9ms_-5QsP9_PAzDoJ_VBB0vqQI1Eo0', '1575364327', 1),
(4, '1134', '订单取消通知', '取消原因{{thing1.DATA}}\n订单号{{number7.DATA}}\n取消时间{{date2.DATA}}\n订单类型{{thing5.DATA}}', 'xBilsNHAH527HBqrMgNoIA_biTfQ7A_bCbMxMx1uMM0', '1575364399', 1),
(5, '1458', '发货通知', '快递单号{{character_string2.DATA}}\n快递公司{{thing1.DATA}}\n发货时间{{time3.DATA}}\n订单商品{{thing5.DATA}}', 'vBrJgvoj4CgBOUIVQcRfsUkYun4orcllCfQ11SSs4wk', '1575364437', 1),
(6, '3098', '拼团成功通知', '活动名称{{thing1.DATA}}\n团长{{name3.DATA}}\n开团时间{{date5.DATA}}\n成团人数{{number2.DATA}}', 'V9fd7ssFZr5_twdgf--RfAExR4N08zU9Hk9auWDAI8g', '1575364508', 1),
(7, '2727', '砍价成功通知', '商品名称{{thing1.DATA}}\n底价{{amount2.DATA}}\n备注{{thing3.DATA}}', 'ehNGy-NRBJIENTdlwT8nBddGW2B4dPo6eKv3x1H6fOg', '1575364579', 1),
(8, '3116', ' 核销成功通知', '商品名称{{thing2.DATA}}\n订单号{{character_string3.DATA}}\n核销时间{{time4.DATA}}', '5wiR0TK43wguWdGzexocKvn9-nhELiJtoBKeqptsf84', '1575364738', 1),
(9, '1476', ' 新订单提醒', '订单商品{{thing2.DATA}}\n订单金额{{amount3.DATA}}\n订单编号{{character_string4.DATA}}\n订单时间{{date6.DATA}}\n订单类型{{thing1.DATA}}', 'F7ju2FdKqFQ8rXXzkB34HSYKa5_kOzJrpF9EZQc1pJ8', '1575364792', 1),
(10, '1451', ' 退款通知', '退款状态{{thing1.DATA}}\n退款商品{{thing2.DATA}}\n退款金额{{amount3.DATA}}\n退款单号{{character_string6.DATA}}', 'aqp6PzdU9vCUOUCHsuGFWvkZgp_cGQ_RKW7XCe9118I', '1575364895', 1),
(11, '755', ' 充值成功通知', '交易单号{{character_string1.DATA}}\n充值金额{{amount3.DATA}}\n账户余额{{amount4.DATA}}\n充值时间{{date5.DATA}}', '_0KAysps9Yj0SM3nacaF_9xw77w1NQYjOpnl4TQAp7k', '1575365017', 1),
(12, '1927', '付款成功通知', '付款单号{{character_string1.DATA}}\n付款金额{{amount2.DATA}}\n付款时间{{date3.DATA}}', 'jY2vT0Fge2srW9Izc-3wEE6WII-FQBvEi2J_duiAAck', '1575365111', 1),
(13, '1468', '申请退款通知', '订单编号{{character_string4.DATA}}\n申请时间{{date5.DATA}}\n订单金额{{amount2.DATA}}\n退款状态{{phrase7.DATA}}\n备注{{thing8.DATA}}', 'NOfT3qoOS3hkSzwt2LJg_LnU0NBzwSHXkSwKdx6QDwc', '1575440940', 1),
(14, '335', '积分到账提醒', '订单编号{{character_string2.DATA}}\n商品名称{{thing3.DATA}}\n支付金额{{amount4.DATA}}\n获得积分{{number5.DATA}}\n累计积分{{number6.DATA}}', 'TtdbifwMN-6D3hNld8jTc97A8Ohlqg4FtgmIgB28JPc', '1575516565', 1),
(15, '3353', '拼团状态通知', '商品名称{{thing2.DATA}}\n拼团人数{{thing1.DATA}}\n备注{{thing3.DATA}}', 'LkYDDYO-HQKT7NydGjrN7AJ1OUCf3mgZf3otVruhxOo', '1575516605', 1);

-- 增加索引，增加失效时间
ALTER TABLE `eb_cache` ADD `expire_time` INT NOT NULL DEFAULT '0' COMMENT '失效时间0=永久' AFTER `result`;
ALTER TABLE `eb_cache` ADD INDEX(`key`);

-- 修改文字错误
UPDATE `eb_system_config` SET `desc` = '人人分销默认每个人都可以分销，指定人分销后台指定人开启分销' WHERE `menu_name` = 'store_brokerage_statu';

-- 首发新品广告位
INSERT INTO `eb_system_config`(`menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES ('new_goods_bananr', 'upload', NULL, 5, NULL, 1, NULL, NULL, NULL, '', '首发新品广告图（414*99）', '首发新品广告图', 0, 1);
-- 调整token字段位500
ALTER TABLE `eb_user_token` CHANGE `token` `token` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'token';
-- 配置分类添加pid
ALTER TABLE `eb_system_config_tab` ADD `pid` INT(10) NULL DEFAULT '0' COMMENT '父级ID' AFTER `id`;