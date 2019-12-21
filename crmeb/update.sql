-- 增加索引，增加失效时间
ALTER TABLE `eb_cache` ADD `expire_time` INT NOT NULL DEFAULT '0' COMMENT '失效时间0=永久' AFTER `result`;
ALTER TABLE `eb_cache` ADD INDEX(`key`);
-- 调整token字段位500
ALTER TABLE `eb_user_token` CHANGE `token` `token` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'token';
