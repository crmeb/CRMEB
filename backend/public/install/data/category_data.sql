-- eb_category default data

INSERT INTO `eb_category` (`id`, `pid`, `owner_id`, `name`, `sort`, `type`, `other`, `add_time`) VALUES
(1, 0, 0, '客户性别', 6, 0, NULL, 0),
(2, 0, 0, '客户来源', 10, 0, NULL, 0),
(3, 0, 0, '用过哪类产品', 8, 0, NULL, 0),
(4, 0, 0, '客户年龄段', 4, 0, NULL, 0),
(5, 0, 0, '累计消费次数', 2, 0, NULL, 0),
(6, 0, 0, '累计消费金额', 0, 0, NULL, 0);