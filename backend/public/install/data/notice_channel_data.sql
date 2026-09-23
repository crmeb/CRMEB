-- eb_notice_channel default data

INSERT INTO `eb_notice_channel` (`channel_key`, `channel_name`, `owner_plugin_id`, `adapter_class`, `support_user`, `support_platform`, `status`, `sort`) VALUES
('is_system', '系统站内信', 'core', 'app\\services\\message\\channel\\SystemChannel', 1, 1, 1, 1),
('is_sms', '短信', 'core', 'app\\services\\message\\channel\\SmsChannel', 1, 1, 1, 2),
('is_email', '邮件', 'core', 'app\\services\\message\\channel\\EmailChannel', 1, 1, 1, 3),
('is_dingtalk', '钉钉', 'core', 'app\\services\\message\\channel\\DingTalkChannel', 0, 1, 1, 4),
('is_ent_wechat', '企业微信', 'core', 'app\\services\\message\\channel\\EntWechatChannel', 0, 1, 1, 5);
