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

--客服消息自动刷新
ALTER TABLE `eb_store_service_log` CHANGE COLUMN `type` `type` tinyint(1) DEFAULT '0' COMMENT '是否已读（0：否；1：是；）', CHANGE COLUMN `remind` `remind` tinyint(1) DEFAULT '0' COMMENT '是否提醒过';

--v2.5.2  产品表 添加 产品海报图字段
ALTER TABLE `eb_store_product` ADD COLUMN `code_path` varchar(64) NOT NULL COMMENT '产品二维码地址(用户小程序海报)' AFTER `browse`;

--v2.5.2  eb_system_group表 添加 产品海报图字段
UPDATE  eb_system_group SET fields='[{"name":"\u5206\u7c7b\u540d\u79f0","title":"name","type":"input","param":""},{"name":"\u5206\u7c7b\u56fe\u6807","title":"pic","type":"upload","param":""},{"name":"\u8df3\u8f6c\u8def\u5f84","title":"url","type":"select","param":"\/pages\/index\/index=\u5546\u57ce\u9996\u9875\n\/pages\/spread\/spread=\u4e2a\u4eba\u63a8\u5e7f\n\/pages\/coupon\/coupon=\u4f18\u60e0\u5238\n\/pages\/user\/user=\u4e2a\u4eba\u4e2d\u5fc3\n\/pages\/miao-list\/miao-list=\u79d2\u6740\u5217\u8868\n\/pages\/pink-list\/index=\u62fc\u56e2\u5217\u8868\u9875\n\/pages\/cut-list\/cut-list=\u780d\u4ef7\u5217\u8868\n\/pages\/productSort\/productSort=\u5206\u7c7b\u9875\u9762\n\/pages\/address\/address=\u5730\u5740\u5217\u8868\n\/pages\/cash\/cash=\u63d0\u73b0\u9875\u9762\n\/pages\/extension\/extension=\u63a8\u5e7f\u7edf\u8ba1\n\/pages\/main\/main=\u8d26\u6237\u91d1\u989d\n\/pages\/collect\/collect=\u6211\u7684\u6536\u85cf\n\/pages\/promotion-card\/promotion-card=\u63a8\u5e7f\u4e8c\u7ef4\u7801\u9875\u9762\n\/pages\/buycar\/buycar=\u8d2d\u7269\u8f66\u9875\u9762\n\/pages\/news-list\/news-list=\u6d88\u606f\u5217\u8868\u9875\n\/pages\/orders-list\/orders-list=\u8ba2\u5355\u5217\u8868\u9875\u9762\n\/pages\/new-list\/new-list=\u6587\u7ae0\u5217\u8868\u9875"},{"name":"\u5e95\u90e8\u83dc\u5355","title":"show","type":"radio","param":"\u662f=\u662f\n\u5426=\u5426"}]' where id = 47;

--v2.5.2 清空拼团表--
TRUNCATE table eb_store_pink

--2018-11-26 更新
ALTER TABLE `eb_user` CHANGE COLUMN `phone` `phone` char(15) COMMENT '手机号码';
ALTER TABLE `eb_store_product` CHANGE COLUMN `is_bargain` `is_bargain`TINYINT(1) UNSIGNED NULL COMMENT '砍价状态 0未开启 1开启';
ALTER TABLE `eb_store_seckill` CHANGE COLUMN `description` `description`TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容';
ALTER TABLE `eb_store_bargain` CHANGE COLUMN `add_time` `add_time` INT(11)UNSIGNED NULL COMMENT '添加时间';
ALTER TABLE `eb_store_combination` CHANGE  `attr` `attr`VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '活动属性';
ALTER TABLE `eb_store_combination` CHANGE COLUMN `mer_use` `mer_use`TINYINT(1) UNSIGNED NULL COMMENT '商户是否可用1可用0不可用';
ALTER TABLE `eb_store_product_reply` CHANGE COLUMN `merchant_reply_content` `merchant_reply_content` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '管理员回复内容';
ALTER TABLE `eb_store_product_reply` CHANGE COLUMN  `merchant_reply_time` `merchant_reply_time` INT(11) NULL COMMENT '管理员回复时间';
ALTER TABLE `eb_store_cart` CHANGE COLUMN `combination_id` `combination_id` INT(11) UNSIGNED NULL DEFAULT '0' COMMENT '拼团id';
ALTER TABLE `eb_store_order` CHANGE COLUMN `combination_id` `combination_id` int(11) UNSIGNED DEFAULT '0' COMMENT '拼团产品id0一般产品';

--海报表新增
DROP TABLE IF EXISTS `eb_routine_qrcode`;
CREATE TABLE `eb_routine_qrcode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '微信二维码ID',
  `third_type` varchar(32) NOT NULL COMMENT '二维码类型 spread(用户推广) product_spread(产品推广)',
  `third_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 0不可用 1可用',
  `add_time` varchar(255) DEFAULT NULL COMMENT '添加时间',
  `page` varchar(255) DEFAULT NULL COMMENT '小程序页面路径带参数',
  `qrcode_url` varchar(255) DEFAULT NULL COMMENT '小程序二维码路径',
  `url_time` int(11) unsigned DEFAULT NULL COMMENT '二维码添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='小程序二维码管理表';