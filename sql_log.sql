-- 表注释
 alter table eb_store_coupon_issue comment '优惠券前台领取表';
 alter table eb_store_coupon_issue_user comment '优惠券前台用户领取记录表';
 alter table eb_store_pink comment '拼团表';
 alter table eb_user_notice_see comment '用户通知发送记录表';
 alter table eb_user_extract comment '用户提现表';
 alter table eb_user_group comment '用户分组表';
 alter table eb_user_recharge comment '用户充值表';
 alter table eb_user_notice comment '用户通知表';
 alter table eb_store_visit comment '产品浏览分析表';

-- --产品浏览分析表表 字段
alter table eb_store_visit modify column  product_id int(11) comment '产品ID';
alter table eb_store_visit modify column cate_id int(11) comment '产品分类ID';
alter table eb_store_visit modify column type char(50) comment '产品类型';
alter table eb_store_visit modify column uid int(11) comment '用户ID';
alter table eb_store_visit modify column count int(11) comment '访问次数';
alter table eb_store_visit modify column content varchar(255) comment '备注描述';
alter table eb_store_visit modify column add_time int(11) comment '添加时间';

--
--  字段注释
--  缓存表  字段
--  alter table eb_cache modify column key varchar(32) comment '缓存key唯一值';
 alter table eb_cache modify column result text comment '缓存数据';
 alter table eb_cache modify column add_time int(10) comment '缓存时间';

-- --优惠券前台领取表 字段
alter table eb_store_coupon_issue modify column cid int(10) comment '优惠券ID';
alter table eb_store_coupon_issue modify column start_time int(10) comment '优惠券领取开启时间';
alter table eb_store_coupon_issue modify column end_time int(10) comment '优惠券领取结束时间';
alter table eb_store_coupon_issue modify column total_count int(10) comment '优惠券领取数量';
alter table eb_store_coupon_issue modify column remain_count int(10) comment '优惠券剩余领取数量';
alter table eb_store_coupon_issue modify column add_time int(10) comment '优惠券添加时间';

--  优惠券前台用户领取记录表 字段
alter table eb_store_coupon_issue_user modify column uid int(10) comment '领取优惠券用户ID';
alter table eb_store_coupon_issue_user modify column issue_coupon_id int(10) comment '优惠券前台领取ID';
alter table eb_store_coupon_issue_user modify column add_time int(10) comment '领取时间';

-- --用户分组表  字段
alter table eb_user_group modify column group_name varchar(64) comment '用户分组名称';

-- --用户充值表 字段
alter table eb_user_recharge modify column uid int(10) comment '充值用户UID';
alter table eb_user_recharge modify column order_id varchar(32) comment '订单号';
alter table eb_user_recharge modify column price decimal(8,2) comment '充值金额';
alter table eb_user_recharge modify column recharge_type varchar(32) comment '充值类型';
alter table eb_user_recharge modify column paid tinyint(1) comment '是否充值';
alter table eb_user_recharge modify column pay_time int(10) comment '充值支付时间';
alter table eb_user_recharge modify column add_time int(12) comment '充值时间';

-- --添加配置类型
ALTER TABLE `eb_system_config_tab` ADD `type` INT(2) NULL DEFAULT '0' COMMENT '配置类型' AFTER `icon`;

-- --添加小程序openid
ALTER TABLE `eb_wechat_user` ADD `routine_openid` varchar(32) NULL DEFAULT '' COMMENT '小程序唯一身份ID' AFTER `openid`;
-- --添加微信openid 允许为空
ALTER TABLE `eb_wechat_user` CHANGE `openid` `openid` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '用户的标识，对当前公众号唯一';
-- --添加附件记录表 允许为空
DROP TABLE IF EXISTS `eb_system_attachment`;
CREATE TABLE `eb_system_attachment` (
  `att_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '附件名称',
  `att_dir` varchar(200) NOT NULL COMMENT '附件路径',
  `satt_dir` varchar(200) NOT NULL COMMENT '附件压缩路径',
  `att_size` char(30) NOT NULL COMMENT '附件大小',
  `att_type` char(30) NOT NULL COMMENT '附件类型',
  `pid` int(10) NOT NULL COMMENT '分类ID0编辑器,1产品图片,2拼团图片,3砍价图片,4秒杀图片,5文章图片,6组合数据图',
  `time` int(11) NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`att_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='附件管理表';
SET FOREIGN_KEY_CHECKS = 1;
-- --修改文章表名称
ALTER  TABLE eb_wechat_news RENAME TO eb_article
-- --修改文章内容表名称
ALTER  TABLE eb_wechat_news_content RENAME TO eb_article_content
--  文章表 添加 is_hot字段和is_banner字段
alter table eb_article modify column is_hot tinyint(1) comment '是否热门(小程序)';
alter table eb_article modify column is_banner tinyint(1) comment '是否轮播图(小程序)';
--  砍价产品表 添加 is_hot字段和is_banner字段
alter table eb_store_bargain modify column look int(11) comment '砍价产品浏览量';
alter table eb_store_bargain modify column share int(11) comment '砍价成品分享次数';
-- --添加小程序模板消息表 允许为空
DROP TABLE IF EXISTS `eb_routine_template`;
CREATE TABLE `eb_routine_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tempkey` char(50) NOT NULL COMMENT '模板编号',
  `name` char(100) NOT NULL COMMENT '模板名',
  `content` varchar(1000) NOT NULL COMMENT '回复内容',
  `tempid` char(100) NOT NULL COMMENT '模板ID',
  `add_time` varchar(15) NOT NULL COMMENT '添加时间',
  `status` tinyint(4) NOT NULL COMMENT '模板状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='小程序模板消息表';
SET FOREIGN_KEY_CHECKS = 1;
-- --修改优惠劵领取为负数,还可以领
ALTER TABLE `eb_store_coupon_issue` ADD `is_permanent` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否无限张数' AFTER `remain_count`;

--  订单表 添加 is_channel字段
alter table eb_store_order modify column is_channel tinyint(1) comment '支付渠道(0微信公众号1微信小程序)';

-- 客服通知
ALTER TABLE `eb_store_service` ADD `notify` INT(2) NULL DEFAULT '0' COMMENT '订单通知1开启0关闭' AFTER `status`;