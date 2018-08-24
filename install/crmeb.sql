-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2018 �?08 �?24 �?00:32
-- 服务器版本: 5.5.53
-- PHP 版本: 7.0.12

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `8_21`
--

-- --------------------------------------------------------

--
-- 表的结构 `eb_article`
--

CREATE TABLE IF NOT EXISTS `eb_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章管理ID',
  `cid` varchar(255) DEFAULT '1' COMMENT '分类id',
  `title` varchar(255) NOT NULL COMMENT '文章标题',
  `author` varchar(255) DEFAULT NULL COMMENT '文章作者',
  `image_input` varchar(255) NOT NULL COMMENT '文章图片',
  `synopsis` varchar(255) DEFAULT NULL COMMENT '文章简介',
  `share_title` varchar(255) DEFAULT NULL COMMENT '文章分享标题',
  `share_synopsis` varchar(255) DEFAULT NULL COMMENT '文章分享简介',
  `visit` varchar(255) DEFAULT NULL COMMENT '浏览次数',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `url` varchar(255) DEFAULT NULL COMMENT '原文链接',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态',
  `add_time` varchar(255) NOT NULL COMMENT '添加时间',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id',
  `mer_id` int(10) unsigned DEFAULT '0' COMMENT '商户id',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否热门(小程序)',
  `is_banner` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否轮播图(小程序)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章管理表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_article_category`
--

CREATE TABLE IF NOT EXISTS `eb_article_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章分类id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `title` varchar(255) NOT NULL COMMENT '文章分类标题',
  `intr` varchar(255) DEFAULT NULL COMMENT '文章分类简介',
  `image` varchar(255) NOT NULL COMMENT '文章分类图片',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1删除0未删除',
  `add_time` varchar(255) NOT NULL COMMENT '添加时间',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章分类表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_article_content`
--

CREATE TABLE IF NOT EXISTS `eb_article_content` (
  `nid` int(10) unsigned NOT NULL COMMENT '文章id',
  `content` text NOT NULL COMMENT '文章内容',
  UNIQUE KEY `nid` (`nid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章内容表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_cache`
--

CREATE TABLE IF NOT EXISTS `eb_cache` (
  `key` varchar(32) NOT NULL,
  `result` text COMMENT '缓存数据',
  `add_time` int(10) DEFAULT NULL COMMENT '缓存时间',
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `eb_cache`
--

INSERT INTO `eb_cache` (`key`, `result`, `add_time`) VALUES
('wechat_menus', '[{"type":"view","name":"\\u5546\\u57ce\\u9996\\u9875","sub_button":[],"url":"http:\\/\\/shop.crmeb.net\\/wap"},{"type":"miniprogram","name":"\\u5c0f\\u7a0b\\u5e8f\\u5546\\u57ce","sub_button":[],"url":"pages\\/index\\/index","appid":"wx7bc36cccc15e4be2","pagepath":"pages\\/index\\/index"},{"type":"view","name":"\\u4e2a\\u4eba\\u4e2d\\u5fc3","sub_button":[],"url":"http:\\/\\/shop.crmeb.net\\/wap\\/my\\/index.html"}]', 1532148956);

-- --------------------------------------------------------

--
-- 表的结构 `eb_express`
--

CREATE TABLE IF NOT EXISTS `eb_express` (
  `id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '快递公司id',
  `code` varchar(50) NOT NULL COMMENT '快递公司简称',
  `name` varchar(50) NOT NULL COMMENT '快递公司全称',
  `sort` int(11) NOT NULL COMMENT '排序',
  `is_show` tinyint(1) NOT NULL COMMENT '是否显示',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='快递公司表' AUTO_INCREMENT=426 ;

--
-- 转存表中的数据 `eb_express`
--

INSERT INTO `eb_express` (`id`, `code`, `name`, `sort`, `is_show`) VALUES
(1, 'LIMINWL', '利民物流', 1, 1),
(2, 'XINTIAN', '鑫天顺物流', 1, 1),
(3, 'henglu', '恒路物流', 1, 1),
(4, 'klwl', '康力物流', 1, 1),
(5, 'meiguo', '美国快递', 1, 1),
(6, 'a2u', 'A2U速递', 1, 1),
(7, 'benteng', '奔腾物流', 1, 1),
(8, 'ahdf', '德方物流', 1, 1),
(9, 'timedg', '万家通', 1, 1),
(10, 'ztong', '智通物流', 1, 1),
(11, 'xindan', '新蛋物流', 1, 1),
(12, 'bgpyghx', '挂号信', 1, 1),
(13, 'XFHONG', '鑫飞鸿物流快递', 1, 1),
(14, 'ALP', '阿里物流', 1, 1),
(15, 'BFWL', '滨发物流', 1, 1),
(16, 'SJWL', '宋军物流', 1, 1),
(17, 'SHUNFAWL', '顺发物流', 1, 1),
(18, 'TIANHEWL', '天河物流', 1, 1),
(19, 'YBWL', '邮联物流', 1, 1),
(20, 'SWHY', '盛旺货运', 1, 1),
(21, 'TSWL', '汤氏物流', 1, 1),
(22, 'YUANYUANWL', '圆圆物流', 1, 1),
(23, 'BALIANGWL', '八梁物流', 1, 1),
(24, 'ZGWL', '振刚物流', 1, 1),
(25, 'JIAYU', '佳宇物流', 1, 1),
(26, 'SHHX', '昊昕物流', 1, 1),
(27, 'ande', '安得物流', 1, 1),
(28, 'ppbyb', '贝邮宝', 1, 1),
(29, 'dida', '递达快递', 1, 1),
(30, 'jppost', '日本邮政', 1, 1),
(31, 'intmail', '中国邮政', 96, 1),
(32, 'HENGCHENGWL', '恒诚物流', 1, 1),
(33, 'HENGFENGWL', '恒丰物流', 1, 1),
(34, 'gdems', '广东ems快递', 1, 1),
(35, 'xlyt', '祥龙运通', 1, 1),
(36, 'gjbg', '国际包裹', 1, 1),
(37, 'uex', 'UEX', 1, 1),
(38, 'singpost', '新加坡邮政', 1, 1),
(39, 'guangdongyouzhengwuliu', '广东邮政', 1, 1),
(40, 'bht', 'BHT', 1, 1),
(41, 'cces', 'CCES快递', 1, 1),
(42, 'cloudexpress', 'CE易欧通国际速递', 1, 1),
(43, 'dasu', '达速物流', 1, 1),
(44, 'pfcexpress', '皇家物流', 1, 1),
(45, 'hjs', '猴急送', 1, 1),
(46, 'huilian', '辉联物流', 1, 1),
(47, 'huanqiu', '环球速运', 1, 1),
(48, 'huada', '华达快运', 1, 1),
(49, 'htwd', '华通务达物流', 1, 1),
(50, 'hipito', '海派通', 1, 1),
(51, 'hqtd', '环球通达', 1, 1),
(52, 'airgtc', '航空快递', 1, 1),
(53, 'haoyoukuai', '好又快物流', 1, 1),
(54, 'hanrun', '韩润物流', 1, 1),
(55, 'ccd', '河南次晨达', 1, 1),
(56, 'hfwuxi', '和丰同城', 1, 1),
(57, 'Sky', '荷兰', 1, 1),
(58, 'hongxun', '鸿讯物流', 1, 1),
(59, 'hongjie', '宏捷国际物流', 1, 1),
(60, 'httx56', '汇通天下物流', 1, 1),
(61, 'lqht', '恒通快递', 1, 1),
(62, 'jinguangsudikuaijian', '京广速递快件', 1, 1),
(63, 'junfengguoji', '骏丰国际速递', 1, 1),
(64, 'jiajiatong56', '佳家通', 1, 1),
(65, 'jrypex', '吉日优派', 1, 1),
(66, 'jinchengwuliu', '锦程国际物流', 1, 1),
(67, 'jgwl', '景光物流', 1, 1),
(68, 'pzhjst', '急顺通', 1, 1),
(69, 'ruexp', '捷网俄全通', 1, 1),
(70, 'jmjss', '金马甲', 1, 1),
(71, 'lanhu', '蓝弧快递', 1, 1),
(72, 'ltexp', '乐天速递', 1, 1),
(73, 'lutong', '鲁通快运', 1, 1),
(74, 'ledii', '乐递供应链', 1, 1),
(75, 'lundao', '论道国际物流', 1, 1),
(76, 'mailikuaidi', '麦力快递', 1, 1),
(77, 'mchy', '木春货运', 1, 1),
(78, 'meiquick', '美快国际物流', 1, 1),
(79, 'valueway', '美通快递', 1, 1),
(80, 'nuoyaao', '偌亚奥国际', 1, 1),
(81, 'euasia', '欧亚专线', 1, 1),
(82, 'pca', '澳大利亚PCA快递', 1, 1),
(83, 'pingandatengfei', '平安达腾飞', 1, 1),
(84, 'pjbest', '品骏快递', 1, 1),
(85, 'qbexpress', '秦邦快运', 1, 1),
(86, 'quanxintong', '全信通快递', 1, 1),
(87, 'quansutong', '全速通国际快递', 1, 1),
(88, 'qinyuan', '秦远物流', 1, 1),
(89, 'qichen', '启辰国际物流', 1, 1),
(90, 'quansu', '全速快运', 1, 1),
(91, 'qzx56', '全之鑫物流', 1, 1),
(92, 'qskdyxgs', '千顺快递', 1, 1),
(93, 'runhengfeng', '全时速运', 1, 1),
(94, 'rytsd', '日益通速递', 1, 1),
(95, 'ruidaex', '瑞达国际速递', 1, 1),
(96, 'shiyun', '世运快递', 1, 1),
(97, 'sfift', '十方通物流', 1, 1),
(98, 'stkd', '顺通快递', 1, 1),
(99, 'bgn', '布谷鸟快递', 1, 1),
(100, 'jiahuier', '佳惠尔快递', 1, 1),
(101, 'pingyou', '小包', 1, 1),
(102, 'yumeijie', '誉美捷快递', 1, 1),
(103, 'meilong', '美龙快递', 1, 1),
(104, 'guangtong', '广通速递', 1, 1),
(105, 'STARS', '星晨急便', 1, 1),
(106, 'NANHANG', '中国南方航空股份有限公司', 1, 1),
(107, 'lanbiao', '蓝镖快递', 1, 1),
(109, 'baotongda', '宝通达物流', 1, 1),
(110, 'dashun', '大顺物流', 1, 1),
(111, 'dada', '大达物流', 1, 1),
(112, 'fangfangda', '方方达物流', 1, 1),
(113, 'hebeijianhua', '河北建华物流', 1, 1),
(114, 'haolaiyun', '好来运快递', 1, 1),
(115, 'jinyue', '晋越快递', 1, 1),
(116, 'kuaitao', '快淘快递', 1, 1),
(117, 'peixing', '陪行物流', 1, 1),
(118, 'hkpost', '香港邮政', 1, 1),
(119, 'ytfh', '一统飞鸿快递', 1, 1),
(120, 'zhongxinda', '中信达快递', 1, 1),
(121, 'zhongtian', '中天快运', 1, 1),
(122, 'zuochuan', '佐川急便', 1, 1),
(123, 'chengguang', '程光快递', 1, 1),
(124, 'cszx', '城市之星', 1, 1),
(125, 'chuanzhi', '传志快递', 1, 1),
(126, 'feibao', '飞豹快递', 1, 1),
(127, 'huiqiang', '汇强快递', 1, 1),
(128, 'lejiedi', '乐捷递', 1, 1),
(129, 'lijisong', '成都立即送快递', 1, 1),
(130, 'minbang', '民邦速递', 1, 1),
(131, 'ocs', 'OCS国际快递', 1, 1),
(132, 'santai', '三态速递', 1, 1),
(133, 'saiaodi', '赛澳递', 1, 1),
(134, 'jingdong', '京东快递', 1, 1),
(135, 'zengyi', '增益快递', 1, 1),
(136, 'fanyu', '凡宇速递', 1, 1),
(137, 'fengda', '丰达快递', 1, 1),
(138, 'coe', '东方快递', 1, 1),
(139, 'ees', '百福东方快递', 1, 1),
(140, 'disifang', '递四方速递', 1, 1),
(141, 'rufeng', '如风达快递', 1, 1),
(142, 'changtong', '长通物流', 1, 1),
(143, 'chengshi100', '城市100快递', 1, 1),
(144, 'feibang', '飞邦物流', 1, 1),
(145, 'haosheng', '昊盛物流', 1, 1),
(146, 'yinsu', '音速速运', 1, 1),
(147, 'kuanrong', '宽容物流', 1, 1),
(148, 'tongcheng', '通成物流', 1, 1),
(149, 'tonghe', '通和天下物流', 1, 1),
(150, 'zhima', '芝麻开门', 1, 1),
(151, 'ririshun', '日日顺物流', 1, 1),
(152, 'anxun', '安迅物流', 1, 1),
(153, 'baiqian', '百千诚国际物流', 1, 1),
(154, 'chukouyi', '出口易', 1, 1),
(155, 'diantong', '店通快递', 1, 1),
(156, 'dajin', '大金物流', 1, 1),
(157, 'feite', '飞特物流', 1, 1),
(159, 'gnxb', '国内小包', 1, 1),
(160, 'huacheng', '华诚物流', 1, 1),
(161, 'huahan', '华翰物流', 1, 1),
(162, 'hengyu', '恒宇运通', 1, 1),
(163, 'huahang', '华航快递', 1, 1),
(164, 'jiuyi', '久易快递', 1, 1),
(165, 'jiete', '捷特快递', 1, 1),
(166, 'jingshi', '京世物流', 1, 1),
(167, 'kuayue', '跨越快递', 1, 1),
(168, 'mengsu', '蒙速快递', 1, 1),
(169, 'nanbei', '南北快递', 1, 1),
(171, 'pinganda', '平安达快递', 1, 1),
(172, 'ruifeng', '瑞丰速递', 1, 1),
(173, 'rongqing', '荣庆物流', 1, 1),
(174, 'suijia', '穗佳物流', 1, 1),
(175, 'simai', '思迈快递', 1, 1),
(176, 'suteng', '速腾快递', 1, 1),
(177, 'shengbang', '晟邦物流', 1, 1),
(178, 'suchengzhaipei', '速呈宅配', 1, 1),
(179, 'wuhuan', '五环速递', 1, 1),
(180, 'xingchengzhaipei', '星程宅配', 1, 1),
(181, 'yinjie', '顺捷丰达', 1, 1),
(183, 'yanwen', '燕文物流', 1, 1),
(184, 'zongxing', '纵行物流', 1, 1),
(185, 'aae', 'AAE快递', 1, 1),
(186, 'dhl', 'DHL快递', 1, 1),
(187, 'feihu', '飞狐快递', 1, 1),
(188, 'shunfeng', '顺丰速运', 92, 1),
(189, 'spring', '春风物流', 1, 1),
(190, 'yidatong', '易达通快递', 1, 1),
(191, 'PEWKEE', '彪记快递', 1, 1),
(192, 'PHOENIXEXP', '凤凰快递', 1, 1),
(193, 'CNGLS', 'GLS快递', 1, 1),
(194, 'BHTEXP', '华慧快递', 1, 1),
(195, 'B2B', '卡行天下', 1, 1),
(196, 'PEISI', '配思货运', 1, 1),
(197, 'SUNDAPOST', '上大物流', 1, 1),
(198, 'SUYUE', '苏粤货运', 1, 1),
(199, 'F5XM', '伍圆速递', 1, 1),
(200, 'GZWENJIE', '文捷航空速递', 1, 1),
(201, 'yuancheng', '远成物流', 1, 1),
(202, 'dpex', 'DPEX快递', 1, 1),
(203, 'anjie', '安捷快递', 1, 1),
(204, 'jldt', '嘉里大通', 1, 1),
(205, 'yousu', '优速快递', 1, 1),
(206, 'wanbo', '万博快递', 1, 1),
(207, 'sure', '速尔物流', 1, 1),
(208, 'sutong', '速通物流', 1, 1),
(209, 'JUNCHUANWL', '骏川物流', 1, 1),
(210, 'guada', '冠达快递', 1, 1),
(211, 'dsu', 'D速快递', 1, 1),
(212, 'LONGSHENWL', '龙胜物流', 1, 1),
(213, 'abc', '爱彼西快递', 1, 1),
(214, 'eyoubao', 'E邮宝', 1, 1),
(215, 'aol', 'AOL快递', 1, 1),
(216, 'jixianda', '急先达物流', 1, 1),
(217, 'haihong', '山东海红快递', 1, 1),
(218, 'feiyang', '飞洋快递', 1, 1),
(219, 'rpx', 'RPX保时达', 1, 1),
(220, 'zhaijisong', '宅急送', 1, 1),
(221, 'tiantian', '天天快递', 99, 1),
(222, 'yunwuliu', '云物流', 1, 1),
(223, 'jiuye', '九曳供应链', 1, 1),
(224, 'bsky', '百世快运', 1, 1),
(225, 'higo', '黑狗物流', 1, 1),
(226, 'arke', '方舟速递', 1, 1),
(227, 'zwsy', '中外速运', 1, 1),
(228, 'jxy', '吉祥邮', 1, 1),
(229, 'aramex', 'Aramex', 1, 1),
(230, 'guotong', '国通快递', 1, 1),
(231, 'jiayi', '佳怡物流', 1, 1),
(232, 'longbang', '龙邦快运', 1, 1),
(233, 'minhang', '民航快递', 1, 1),
(234, 'quanyi', '全一快递', 1, 1),
(235, 'quanchen', '全晨快递', 1, 1),
(236, 'usps', 'USPS快递', 1, 1),
(237, 'xinbang', '新邦物流', 1, 1),
(238, 'yuanzhi', '元智捷诚快递', 1, 1),
(239, 'zhongyou', '中邮物流', 1, 1),
(240, 'yuxin', '宇鑫物流', 1, 1),
(241, 'cnpex', '中环快递', 1, 1),
(242, 'shengfeng', '盛丰物流', 1, 1),
(243, 'yuantong', '圆通速递', 97, 1),
(244, 'jiayunmei', '加运美物流', 1, 1),
(245, 'ywfex', '源伟丰快递', 1, 1),
(246, 'xinfeng', '信丰物流', 1, 1),
(247, 'wanxiang', '万象物流', 1, 1),
(248, 'menduimen', '门对门', 1, 1),
(249, 'mingliang', '明亮物流', 1, 1),
(250, 'fengxingtianxia', '风行天下', 1, 1),
(251, 'gongsuda', '共速达物流', 1, 1),
(252, 'zhongtong', '中通快递', 100, 1),
(253, 'quanritong', '全日通快递', 1, 1),
(254, 'ems', 'EMS', 1, 1),
(255, 'wanjia', '万家物流', 1, 1),
(256, 'yuntong', '运通快递', 1, 1),
(257, 'feikuaida', '飞快达物流', 1, 1),
(258, 'haimeng', '海盟速递', 1, 1),
(259, 'zhongsukuaidi', '中速快件', 1, 1),
(260, 'yuefeng', '越丰快递', 1, 1),
(261, 'shenghui', '盛辉物流', 1, 1),
(262, 'datian', '大田物流', 1, 1),
(263, 'quanjitong', '全际通快递', 1, 1),
(264, 'longlangkuaidi', '隆浪快递', 1, 1),
(265, 'neweggozzo', '新蛋奥硕物流', 1, 1),
(266, 'shentong', '申通快递', 95, 1),
(267, 'haiwaihuanqiu', '海外环球', 1, 1),
(268, 'yad', '源安达快递', 1, 1),
(269, 'jindawuliu', '金大物流', 1, 1),
(270, 'sevendays', '七天连锁', 1, 1),
(271, 'tnt', 'TNT快递', 1, 1),
(272, 'huayu', '天地华宇物流', 1, 1),
(273, 'lianhaotong', '联昊通快递', 1, 1),
(274, 'nengda', '港中能达快递', 1, 1),
(275, 'LBWL', '联邦物流', 1, 1),
(276, 'ontrac', 'onTrac', 1, 1),
(277, 'feihang', '原飞航快递', 1, 1),
(278, 'bangsongwuliu', '邦送物流', 1, 1),
(279, 'huaxialong', '华夏龙物流', 1, 1),
(280, 'ztwy', '中天万运快递', 1, 1),
(281, 'fkd', '飞康达物流', 1, 1),
(282, 'anxinda', '安信达快递', 1, 1),
(283, 'quanfeng', '全峰快递', 1, 1),
(284, 'shengan', '圣安物流', 1, 1),
(285, 'jiaji', '佳吉物流', 1, 1),
(286, 'yunda', '韵达快运', 94, 1),
(287, 'ups', 'UPS快递', 1, 1),
(288, 'debang', '德邦物流', 1, 1),
(289, 'yafeng', '亚风速递', 1, 1),
(290, 'kuaijie', '快捷速递', 98, 1),
(291, 'huitong', '百世快递', 93, 1),
(293, 'aolau', 'AOL澳通速递', 1, 1),
(294, 'anneng', '安能物流', 1, 1),
(295, 'auexpress', '澳邮中国快运', 1, 1),
(296, 'exfresh', '安鲜达', 1, 1),
(297, 'bcwelt', 'BCWELT', 1, 1),
(298, 'youzhengguonei', '挂号信', 1, 1),
(299, 'xiaohongmao', '北青小红帽', 1, 1),
(300, 'lbbk', '宝凯物流', 1, 1),
(301, 'byht', '博源恒通', 1, 1),
(302, 'idada', '百成大达物流', 1, 1),
(303, 'baitengwuliu', '百腾物流', 1, 1),
(304, 'birdex', '笨鸟海淘', 1, 1),
(305, 'bsht', '百事亨通', 1, 1),
(306, 'dayang', '大洋物流快递', 1, 1),
(307, 'dechuangwuliu', '德创物流', 1, 1),
(308, 'donghanwl', '东瀚物流', 1, 1),
(309, 'dfpost', '达方物流', 1, 1),
(310, 'dongjun', '东骏快捷物流', 1, 1),
(311, 'dindon', '叮咚澳洲转运', 1, 1),
(312, 'dazhong', '大众佐川急便', 1, 1),
(313, 'decnlh', '德中快递', 1, 1),
(314, 'dekuncn', '德坤供应链', 1, 1),
(315, 'eshunda', '俄顺达', 1, 1),
(316, 'ewe', 'EWE全球快递', 1, 1),
(317, 'fedexuk', 'FedEx英国', 1, 1),
(318, 'fox', 'FOX国际速递', 1, 1),
(319, 'rufengda', '凡客如风达', 1, 1),
(320, 'fandaguoji', '颿达国际快递', 1, 1),
(321, 'hnfy', '飞鹰物流', 1, 1),
(322, 'flysman', '飞力士物流', 1, 1),
(323, 'sccod', '丰程物流', 1, 1),
(324, 'farlogistis', '泛远国际物流', 1, 1),
(325, 'gsm', 'GSM', 1, 1),
(326, 'gaticn', 'GATI快递', 1, 1),
(327, 'gts', 'GTS快递', 1, 1),
(328, 'gangkuai', '港快速递', 1, 1),
(329, 'gtsd', '高铁速递', 1, 1),
(330, 'tiandihuayu', '华宇物流', 1, 1),
(331, 'huangmajia', '黄马甲快递', 1, 1),
(332, 'ucs', '合众速递', 1, 1),
(333, 'huoban', '伙伴物流', 1, 1),
(334, 'nedahm', '红马速递', 1, 1),
(335, 'huiwen', '汇文配送', 1, 1),
(336, 'nmhuahe', '华赫物流', 1, 1),
(337, 'hangyu', '航宇快递', 1, 1),
(338, 'minsheng', '闽盛物流', 1, 1),
(339, 'riyu', '日昱物流', 1, 1),
(340, 'sxhongmajia', '山西红马甲', 1, 1),
(341, 'syjiahuier', '沈阳佳惠尔', 1, 1),
(342, 'shlindao', '上海林道货运', 1, 1),
(343, 'shunjiefengda', '顺捷丰达', 1, 1),
(344, 'subida', '速必达物流', 1, 1),
(345, 'bphchina', '速方国际物流', 1, 1),
(346, 'sendtochina', '速递中国', 1, 1),
(347, 'suning', '苏宁快递', 1, 1),
(348, 'sihaiet', '四海快递', 1, 1),
(349, 'tianzong', '天纵物流', 1, 1),
(350, 'chinatzx', '同舟行物流', 1, 1),
(351, 'nntengda', '腾达速递', 1, 1),
(352, 'sd138', '泰国138', 1, 1),
(353, 'tongdaxing', '通达兴物流', 1, 1),
(354, 'tlky', '天联快运', 1, 1),
(355, 'youshuwuliu', 'UC优速快递', 1, 1),
(356, 'ueq', 'UEQ快递', 1, 1),
(357, 'weitepai', '微特派快递', 1, 1),
(358, 'wtdchina', '威时沛运', 1, 1),
(359, 'wzhaunyun', '微转运', 1, 1),
(360, 'gswtkd', '万通快递', 1, 1),
(361, 'wotu', '渥途国际速运', 1, 1),
(362, 'xiyoute', '希优特快递', 1, 1),
(363, 'xilaikd', '喜来快递', 1, 1),
(364, 'xsrd', '鑫世锐达', 1, 1),
(365, 'xtb', '鑫通宝物流', 1, 1),
(366, 'xintianjie', '信天捷快递', 1, 1),
(367, 'xaetc', '西安胜峰', 1, 1),
(368, 'xianfeng', '先锋快递', 1, 1),
(369, 'sunspeedy', '新速航', 1, 1),
(370, 'xipost', '西邮寄', 1, 1),
(371, 'sinatone', '信联通', 1, 1),
(372, 'sunjex', '新杰物流', 1, 1),
(373, 'yundaexus', '韵达美国件', 1, 1),
(374, 'yxwl', '宇鑫物流', 1, 1),
(375, 'yitongda', '易通达', 1, 1),
(376, 'yiqiguojiwuliu', '一柒物流', 1, 1),
(377, 'yilingsuyun', '亿领速运', 1, 1),
(378, 'yujiawuliu', '煜嘉物流', 1, 1),
(379, 'gml', '英脉物流', 1, 1),
(380, 'leopard', '云豹国际货运', 1, 1),
(381, 'czwlyn', '云南中诚', 1, 1),
(382, 'sdyoupei', '优配速运', 1, 1),
(383, 'yongchang', '永昌物流', 1, 1),
(384, 'yufeng', '御风速运', 1, 1),
(385, 'yamaxunwuliu', '亚马逊物流', 1, 1),
(386, 'yousutongda', '优速通达', 1, 1),
(387, 'yishunhang', '亿顺航', 1, 1),
(388, 'yongwangda', '永旺达快递', 1, 1),
(389, 'ecmscn', '易满客', 1, 1),
(390, 'yingchao', '英超物流', 1, 1),
(391, 'edlogistics', '益递物流', 1, 1),
(392, 'yyexpress', '远洋国际', 1, 1),
(393, 'onehcang', '一号仓', 1, 1),
(394, 'ycgky', '远成快运', 1, 1),
(395, 'lineone', '一号线', 1, 1),
(396, 'ypsd', '壹品速递', 1, 1),
(397, 'vipexpress', '鹰运国际速递', 1, 1),
(398, 'el56', '易联通达物流', 1, 1),
(399, 'yyqc56', '一运全成物流', 1, 1),
(400, 'zhongtie', '中铁快运', 1, 1),
(401, 'ZTKY', '中铁物流', 1, 1),
(402, 'zzjh', '郑州建华快递', 1, 1),
(403, 'zhongruisudi', '中睿速递', 1, 1),
(404, 'zhongwaiyun', '中外运速递', 1, 1),
(405, 'zengyisudi', '增益速递', 1, 1),
(406, 'sujievip', '郑州速捷', 1, 1),
(407, 'zhichengtongda', '至诚通达快递', 1, 1),
(408, 'zhdwl', '众辉达物流', 1, 1),
(409, 'kuachangwuliu', '直邮易', 1, 1),
(410, 'topspeedex', '中运全速', 1, 1),
(411, 'otobv', '中欧快运', 1, 1),
(412, 'zsky123', '准实快运', 1, 1),
(413, 'donghong', '东红物流', 1, 1),
(414, 'kuaiyouda', '快优达速递', 1, 1),
(415, 'balunzhi', '巴伦支快递', 1, 1),
(416, 'hutongwuliu', '户通物流', 1, 1),
(417, 'xianchenglian', '西安城联速递', 1, 1),
(418, 'youbijia', '邮必佳', 1, 1),
(419, 'feiyuan', '飞远物流', 1, 1),
(420, 'chengji', '城际速递', 1, 1),
(421, 'huaqi', '华企快运', 1, 1),
(422, 'yibang', '一邦快递', 1, 1),
(423, 'citylink', 'CityLink快递', 1, 1),
(424, 'meixi', '美西快递', 1, 1),
(425, 'acs', 'ACS', 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_access_token`
--

CREATE TABLE IF NOT EXISTS `eb_routine_access_token` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '小程序access_token表ID',
  `access_token` varchar(256) NOT NULL COMMENT 'openid',
  `stop_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='小程序access_token表' AUTO_INCREMENT=7 ;

--
-- 转存表中的数据 `eb_routine_access_token`
--

INSERT INTO `eb_routine_access_token` (`id`, `access_token`, `stop_time`) VALUES
(1, '12_BwfjO2SAOP1dtZLQUtKrHGC2pv_M1DD51LhyOroDqwUMS3JRonwgSkympBk6kYbHvEecjRBcGAGDG47PPL8R1voD9V3wwpoZ9_rhtvdSS9ku9ehU2jCmnCzmQ5CG7RKW2t4Z7A9aNvaFMBbsDVOaAFASKD', 1534306647);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_form_id`
--

CREATE TABLE IF NOT EXISTS `eb_routine_form_id` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表单ID表ID',
  `uid` int(11) DEFAULT '0' COMMENT '用户uid',
  `form_id` varchar(32) NOT NULL COMMENT '表单ID',
  `stop_time` int(11) unsigned DEFAULT NULL COMMENT '表单ID失效时间',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '状态1 未使用 2不能使用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='表单id表记录表' AUTO_INCREMENT=138 ;

--
-- 转存表中的数据 `eb_routine_form_id`
--

INSERT INTO `eb_routine_form_id` (`id`, `uid`, `form_id`, `stop_time`, `status`) VALUES
(1, NULL, '1527218790919', 1527737190, 1),
(2, NULL, '1527218795445', 1527737195, 1),
(3, NULL, '1527218802623', 1527737202, 1),
(4, NULL, '1527219992546', 1527738392, 1),
(5, NULL, '18ed60695c60477263362db1fcd57711', 1528028492, 1),
(6, NULL, '4ac27cf74f1874089e7b9978b1655330', 1528028822, 1),
(7, NULL, 'd1c09ce577252707a6a3d37749e4fb89', 1528041339, 1),
(8, NULL, '587137c710636610dfac3434468bf59f', 1528041357, 1),
(9, NULL, 'fd26d2192ac2c9c39f33c4dbca0feb23', 1528041362, 1),
(10, NULL, 'e1ec0351a083cf7cdb16b01edb33e835', 1528041455, 1),
(11, NULL, '3f37c5dc20bc4f70f542def3ffc059dc', 1528041457, 1),
(12, NULL, '999c5282f1be9f952ddb126e38575c8c', 1528073720, 1),
(13, NULL, '1527555687142', 1528074086, 1),
(14, NULL, '1527561323929', 1528079722, 1),
(15, NULL, '1527561340989', 1528079739, 1),
(16, NULL, '1527561825052', 1528080224, 1),
(17, NULL, '1527580340878', 1528098739, 1),
(18, NULL, '1527580488444', 1528098887, 1),
(19, NULL, '7025a9ade58735b2042eb1736e534cdf', 1528161187, 1),
(20, NULL, 'b076d5a90fa7e4bd80ac8a9ca2d11f22', 1528161734, 1),
(21, NULL, 'aca6b85e10199c71edfbd17094660dda', 1528161867, 1),
(22, NULL, '7c375bfc6f48e99dd3c7ebae27be33be', 1528161872, 1),
(23, NULL, 'd6cf09152119814664124cd5ef7285f6', 1528161877, 1),
(24, NULL, '288510d01ec0c14182ea21c162c41e3d', 1528161890, 1),
(25, NULL, '57ba79e638b3f37903901b12bbb61c65', 1528162265, 1),
(26, NULL, '1ab1098aff8af0331a6e6d39436b295a', 1528162272, 1),
(27, NULL, '957bc8ef5ae594f4b21cfd05f499eee3', 1528162389, 1),
(28, NULL, 'a16f9c7fcbfe27266e4dc15be7157def', 1528162404, 1),
(29, NULL, 'bbc619f610dac7327861d1021a1a203b', 1528163111, 1),
(30, NULL, '1527678037164', 1528196436, 1),
(31, NULL, '1527681662506', 1528200061, 1),
(32, 167, '1c9b21e4dfa2e7adea3e9232905226ad', 1529464206, 1),
(33, 108, '1529579041074', 1530097441, 1),
(34, 201, '1529579324454', 1530097725, 1),
(35, 204, '1529580872559', 1530099273, 1),
(36, 200, '8f28fcfb33a0ef769ad41ad04031f9a8', 1530099285, 1),
(37, 200, '615b8299361adfcf3b8a08851efe77d9', 1530099308, 1),
(38, 205, '1529582976447', 1530101198, 1),
(39, 111, '95fef45924f9f18813703cf8e0b0e4ac', 1530185676, 1),
(40, 200, '153a471c1874e05aeb00ca9209170fac', 1530270537, 1),
(41, 200, '29bc7c3080f1fada7f08b4408d35d308', 1530270539, 1),
(42, 200, '36ea9f4451763b3f8746c6eb3abf06d5', 1530270580, 1),
(43, 214, '1529752200618', 1530270601, 2),
(44, 220, '1529892780729', 1530411191, 1),
(45, 220, '1529892787607', 1530411191, 1),
(46, 220, '1529892787798', 1530411192, 1),
(47, 220, '1529892787971', 1530411192, 1),
(48, 220, '1529892787455', 1530411201, 1),
(49, 220, '1529892786813', 1530411201, 1),
(50, 220, '1529892787269', 1530411201, 1),
(51, 220, '1529892784774', 1530411201, 1),
(52, 220, '1529892788272', 1530411201, 1),
(53, 220, '1529892788445', 1530411201, 1),
(54, 220, '1529892788604', 1530411201, 1),
(55, 220, '1529892788932', 1530411202, 1),
(56, 220, '1529892789101', 1530411202, 1),
(57, 220, '1529892789427', 1530411202, 1),
(58, 220, '1529892789286', 1530411202, 1),
(59, 220, '1529892789590', 1530411202, 1),
(60, 220, '1529892789761', 1530411203, 1),
(61, 220, '1529892788112', 1530411203, 1),
(62, 220, '1529892789931', 1530411204, 1),
(63, 220, '1529892790103', 1530411204, 1),
(64, 220, '1529892790282', 1530411204, 1),
(65, 220, '1529892790441', 1530411204, 1),
(66, 220, '1529892790604', 1530411204, 1),
(67, 108, '1529906238260', 1530424639, 1),
(68, 233, '7017a68780247b9b6d450940c81a036b', 1530497888, 1),
(69, 255, '3722157beb438d85215d3b1e6a0aa086', 1530832636, 1),
(70, 284, '05425d5a66e6ec39bb6dda8fc575897d', 1531527554, 1),
(71, 284, '7134b20b96e0c52071ca55dd43dcb740', 1531527654, 1),
(72, 284, '67b8007eb44dea3e21d1303c4627d859', 1531527654, 1),
(73, 284, '497b10bd921e61951504bb17045ccfb7', 1531527655, 1),
(74, 284, '998fe23bae16a35c1bc408277bb4833b', 1531527655, 1),
(75, 284, '33beeecb32f504cc5a56abe698590c28', 1531527655, 1),
(76, 111, '257c71cf394d97a056aa41143842027e', 1531733488, 2),
(77, 111, '108808ab96b9482425e27aa1c8b4ed2f', 1531733491, 2),
(78, 111, '28efdbca8f8bb079c19dbf70dee73c30', 1531733498, 1),
(79, 111, '155cce7f6101826b000c388c484d1a85', 1531884073, 1),
(80, 167, '1c41b9236234c363b1635a5e8f7bf4b3', 1531910918, 1),
(81, 297, '279ae6f7b87db40697959eb399ab4811', 1532307184, 1),
(82, 297, '59729e139999393a7968466f6a36ade6', 1532307185, 1),
(83, 313, '1fa36a911ca520d0b1dfbd4a72e1e2e0', 1532421107, 1),
(84, 331, '1532087781800', 1532606183, 1),
(85, 331, '1532087817201', 1532606218, 1),
(86, 3, '1532587856196', 1533106256, 1),
(87, 3, '1532587873382', 1533106273, 1),
(88, 3, '1532588350996', 1533106750, 1),
(89, 2, '80519719e947dc9129e746db73a25f3c', 1533178687, 1),
(90, 2, '3c9cb2a5fb31674d5558adda1d076cd0', 1533178772, 1),
(91, 2, '3906bc9da1a9938806098f87d9aac7d5', 1533178889, 1),
(92, 2, '5b3607db0a11927c1a09ec8d691a0de1', 1533179200, 1),
(93, 3, '89b61ca46cc27bed2720d0001e9db8e2', 1533194195, 1),
(94, 36, '1532831506204', 1533349908, 1),
(95, 16, '420d8468961c963b6c7a39907c1bdd9d', 1533459111, 1),
(96, 12, '22262a90af8b0ba7d1ed7d4e5eeb86c6', 1533547996, 1),
(97, 12, '0081bb67e7714757f4a07d5c3e6b086d', 1533547998, 1),
(98, 4, '42d5fdf09ccb1c9f6fefd09f13ea89fc', 1534132961, 1),
(99, 38, '25d6321fc7e109d1e35c47737a8aa7e1', 1534147788, 1),
(100, 38, '863ea4f4a8661938b2c0f202664d9437', 1534147791, 1),
(101, 38, '31cb89644d2994a54707f9b5ea9e1023', 1534147888, 1),
(102, 20, '08947883608babebd546c1b5072d3a92', 1534154855, 1),
(103, 20, '04463a9cebf7f41967578e03256818cb', 1534154856, 1),
(104, 38, '249b6abfe368e9d9ea4febab1f1d47ba', 1534154912, 1),
(105, 20, '80e51f277979f68ef81b00580fc09914', 1534154940, 1),
(106, 69, '5e2b5fd7b165cdaeabe40978456b3db4', 1534157549, 1),
(107, 69, '4e74f7833fc553e0f8c9c6276fa88d8b', 1534157551, 1),
(108, 69, '1cc154881ddbe5ac9f67079502e2cd8c', 1534157552, 1),
(109, 69, '50fd7a788e21384d83ddb10969ae0789', 1534168668, 1),
(110, 20, '5195192eb3c555f95c9c8826b42dfa04', 1534234252, 1),
(111, 69, '9f5bfe1cea77be9fc6794270082a934d', 1534327834, 1),
(112, 69, '55dfdd4d213d43bee59de007c3b16157', 1534327836, 1),
(113, 69, '5f1b2e497d704b798fca1662cc1c489b', 1534327836, 1),
(114, 69, '1fae9219e578d6a73156288e23af9f56', 1534327837, 1),
(115, 69, '4d727c5c756d8527e1e7ba73f817e8cd', 1534327839, 1),
(116, 90, '1534225778227', 1534744179, 1),
(117, 90, '1534225780683', 1534744181, 1),
(118, 90, '1534225782662', 1534744183, 1),
(119, 9, '053fe37911d3cd904437c7d35b8925df', 1534757449, 1),
(120, 9, '80008ab3085e3df4ea9885ca3ae4b570', 1534761869, 1),
(121, 9, '94ccd0ebb24a1c139a193192158626ee', 1534761871, 1),
(122, 9, '9a50ce063bf17fcc688cebebfb8b3e13', 1534761893, 1),
(123, 9, '9509be467ad133a10deb2bb9670d3a1e', 1534762146, 1),
(124, 9, '1ca15780bcf96beb330815019a213e30', 1534762148, 1),
(125, 9, '4d8fa5bd37b713a4538d44b9fae3ce16', 1534762766, 1),
(126, 9, '44d847fd08e64a7e9d29047ae8f67684', 1534762772, 1),
(127, 9, '22db8d2d1575f10e45fe5aca90f1e4a2', 1534907515, 1),
(128, 9, '3e7f64be264971fe81488717661a2836', 1534907536, 1),
(129, 9, '12e26cc391d5b420924a22d927395f31', 1534907536, 1),
(130, 9, '446108a5dcc7267c701d14cd7790d82a', 1534907543, 1),
(131, 102, '1534405722892', 1534924122, 1),
(132, 102, '1534405735813', 1534924135, 1),
(133, 102, '1534405737697', 1534924137, 1),
(134, 1, '10233218487fdda24f82f521049afc81', 1535005534, 1),
(135, 91, '426b31051314ac4bc51794a9120ee8ea', 1535012019, 1),
(136, 91, '917f5643b36171daaf3219b6c7213d55', 1535073308, 1),
(137, 107, '41b9feea378b755517b6bc886cfd0447', 1535074989, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_template`
--

CREATE TABLE IF NOT EXISTS `eb_routine_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板id',
  `tempkey` char(50) NOT NULL COMMENT '模板编号',
  `name` char(100) NOT NULL COMMENT '模板名',
  `content` varchar(1000) NOT NULL COMMENT '回复内容',
  `tempid` char(100) DEFAULT NULL COMMENT '模板ID',
  `add_time` varchar(15) NOT NULL COMMENT '添加时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `tempkey` (`tempkey`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信模板' AUTO_INCREMENT=18 ;

--
-- 转存表中的数据 `eb_routine_template`
--

INSERT INTO `eb_routine_template` (`id`, `tempkey`, `name`, `content`, `tempid`, `add_time`, `status`) VALUES
(12, 'AT0177', '订单配送通知', '订单编号{{keyword1.DATA}}\n配送员{{keyword2.DATA}}\n联系电话{{keyword3.DATA}}\n配送时间{{keyword4.DATA}}\n备注{{keyword5.DATA}}', 'mCxm8mO_ZeETohNq7sFMlcf0vWdAnCJylKog71J68JM', '1534469109', 1),
(13, 'AT0007', '订单发货提醒', '订单号{{keyword1.DATA}}\n快递公司{{keyword2.DATA}}\n快递单号{{keyword3.DATA}}\n发货时间{{keyword4.DATA}}\n备注{{keyword5.DATA}}', 'XQlyO_b3QocHBOrC69bfOCaOJq5kdKXQcdQtCO11sA0', '1534469928', 1),
(14, 'AT0787', '退款成功通知', '订单号{{keyword1.DATA}}\n退款时间{{keyword2.DATA}}\n退款金额{{keyword3.DATA}}\n退款方式{{keyword4.DATA}}\n备注{{keyword5.DATA}}', 'gQi8X-wuOYAwdVRBXaJVwfAXQ0ngjMxYcYVS0GT1anI', '1534469993', 1),
(15, 'AT0009', '订单支付成功通知', '单号{{keyword1.DATA}}\n下单时间{{keyword2.DATA}}\n订单状态{{keyword3.DATA}}\n支付金额{{keyword4.DATA}}\n支付方式{{keyword5.DATA}}', 'x5Jw630Rp63T34kv0Q43RaeVKtk5OFKDNkwcrwMp9FM', '1534470043', 1),
(16, 'AT1173', '砍价成功通知', '商品名称{{keyword1.DATA}}\n砍价金额{{keyword2.DATA}}\n底价{{keyword3.DATA}}\n砍掉价格{{keyword4.DATA}}\n支付金额{{keyword5.DATA}}\n备注{{keyword6.DATA}}', 'FofE1ABYV1iXkNFIvEOUy4j5lInos20KCwIW6gyZ2nM', '1534470085', 1),
(17, 'AT0036', '退款通知', '订单编号{{keyword1.DATA}}\n退款原因{{keyword2.DATA}}\n退款时间{{keyword3.DATA}}\n退款金额{{keyword4.DATA}}\n退款方式{{keyword5.DATA}}', 'JhmCRYO7ahP6nbCb6oO-BPYz8lIP2u9xs-CkZ63Z4HI', '1534470134', 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_bargain`
--

CREATE TABLE IF NOT EXISTS `eb_store_bargain` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '砍价产品ID',
  `product_id` int(11) unsigned NOT NULL COMMENT '关联产品ID',
  `title` varchar(255) NOT NULL COMMENT '砍价活动名称',
  `image` varchar(150) NOT NULL COMMENT '砍价活动图片',
  `unit_name` varchar(16) DEFAULT NULL COMMENT '单位名称',
  `stock` int(11) unsigned DEFAULT NULL COMMENT '库存',
  `sales` int(11) unsigned DEFAULT NULL COMMENT '销量',
  `images` varchar(1000) NOT NULL COMMENT '砍价产品轮播图',
  `start_time` int(11) unsigned NOT NULL COMMENT '砍价开启时间',
  `stop_time` int(11) unsigned NOT NULL COMMENT '砍价结束时间',
  `store_name` varchar(255) DEFAULT NULL COMMENT '砍价产品名称',
  `price` decimal(8,2) unsigned DEFAULT NULL COMMENT '砍价金额',
  `min_price` decimal(8,2) unsigned DEFAULT NULL COMMENT '砍价商品最低价',
  `num` int(11) unsigned DEFAULT NULL COMMENT '每次购买的砍价产品数量',
  `bargain_max_price` decimal(8,2) unsigned DEFAULT NULL COMMENT '用户每次砍价的最大金额',
  `bargain_min_price` decimal(8,2) unsigned DEFAULT NULL COMMENT '用户每次砍价的最小金额',
  `bargain_num` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '用户每次砍价的次数',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '砍价状态 0(到砍价时间不自动开启)  1(到砍价时间自动开启时间)',
  `description` text COMMENT '砍价详情',
  `give_integral` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '反多少积分',
  `info` varchar(255) DEFAULT NULL COMMENT '砍价活动简介',
  `cost` decimal(8,2) unsigned DEFAULT NULL COMMENT '成本价',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐0不推荐1推荐',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 0未删除 1删除',
  `add_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  `is_postage` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否包邮 0不包邮 1包邮',
  `postage` decimal(10,2) unsigned DEFAULT NULL COMMENT '邮费',
  `rule` text COMMENT '砍价规则',
  `look` int(11) unsigned DEFAULT '0' COMMENT '砍价产品浏览量',
  `share` int(11) unsigned DEFAULT '0' COMMENT '砍价产品分享量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='砍价表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_bargain_user`
--

CREATE TABLE IF NOT EXISTS `eb_store_bargain_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户参与砍价表ID',
  `uid` int(11) unsigned DEFAULT NULL COMMENT '用户ID',
  `bargain_id` int(11) unsigned DEFAULT NULL COMMENT '砍价产品id',
  `bargain_price_min` decimal(8,2) unsigned DEFAULT NULL COMMENT '砍价的最低价',
  `bargain_price` decimal(8,2) DEFAULT NULL COMMENT '砍价金额',
  `price` decimal(8,2) unsigned DEFAULT NULL COMMENT '砍掉的价格',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1参与中 2 活动结束参与失败 3活动结束参与成功',
  `add_time` int(11) unsigned DEFAULT NULL COMMENT '参与时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户参与砍价表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_bargain_user_help`
--

CREATE TABLE IF NOT EXISTS `eb_store_bargain_user_help` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '砍价用户帮助表ID',
  `uid` int(11) unsigned DEFAULT NULL COMMENT '帮助的用户id',
  `bargain_id` int(11) unsigned DEFAULT NULL COMMENT '砍价产品ID',
  `bargain_user_id` int(11) unsigned DEFAULT NULL COMMENT '用户参与砍价表id',
  `price` decimal(8,2) unsigned DEFAULT NULL COMMENT '帮助砍价多少金额',
  `add_time` int(11) unsigned DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='砍价用户帮助表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_cart`
--

CREATE TABLE IF NOT EXISTS `eb_store_cart` (
  `id` bigint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '购物车表ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(32) NOT NULL COMMENT '类型',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `product_attr_unique` varchar(16) NOT NULL DEFAULT '' COMMENT '商品属性',
  `cart_num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '商品数量',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = 未购买 1 = 已购买',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_new` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为立即购买',
  `combination_id` int(11) unsigned NOT NULL COMMENT '拼团id',
  `seckill_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀产品ID',
  `bargain_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '砍价id',
  PRIMARY KEY (`id`),
  KEY `user_id` (`uid`) USING BTREE,
  KEY `goods_id` (`product_id`) USING BTREE,
  KEY `uid` (`uid`,`is_pay`) USING BTREE,
  KEY `uid_2` (`uid`,`is_del`) USING BTREE,
  KEY `uid_3` (`uid`,`is_new`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='购物车表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_category`
--

CREATE TABLE IF NOT EXISTS `eb_store_category` (
  `id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT '商品分类表ID',
  `pid` mediumint(11) NOT NULL COMMENT '父id',
  `cate_name` varchar(100) NOT NULL COMMENT '分类名称',
  `sort` mediumint(11) NOT NULL COMMENT '排序',
  `pic` varchar(128) NOT NULL DEFAULT '' COMMENT '图标',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否推荐',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE,
  KEY `is_base` (`is_show`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品分类表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `mer_id` int(10) unsigned DEFAULT '0' COMMENT '商户id',
  `image` varchar(255) NOT NULL COMMENT '推荐图',
  `images` varchar(1000) NOT NULL COMMENT '轮播图',
  `title` varchar(255) NOT NULL COMMENT '活动标题',
  `attr` varchar(255) NOT NULL COMMENT '活动属性',
  `people` int(2) unsigned NOT NULL COMMENT '参团人数',
  `info` varchar(255) NOT NULL COMMENT '简介',
  `price` decimal(10,2) unsigned NOT NULL COMMENT '价格',
  `sort` int(10) unsigned NOT NULL COMMENT '排序',
  `sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `stock` int(10) unsigned NOT NULL COMMENT '库存',
  `add_time` varchar(128) NOT NULL COMMENT '添加时间',
  `is_host` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '推荐',
  `is_show` tinyint(1) unsigned NOT NULL COMMENT '产品状态',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `combination` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `mer_use` tinyint(1) unsigned NOT NULL COMMENT '商户是否可用1可用0不可用',
  `is_postage` tinyint(1) unsigned NOT NULL COMMENT '是否包邮1是0否',
  `postage` decimal(10,2) unsigned NOT NULL COMMENT '邮费',
  `description` text NOT NULL COMMENT '拼团内容',
  `start_time` int(11) unsigned NOT NULL COMMENT '拼团开始时间',
  `stop_time` int(11) unsigned NOT NULL COMMENT '拼团结束时间',
  `cost` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '拼图产品成本',
  `browse` int(11) DEFAULT '0' COMMENT '浏览量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='拼团产品表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `attr_name` varchar(32) NOT NULL COMMENT '属性名',
  `attr_values` varchar(256) NOT NULL COMMENT '属性值',
  KEY `store_id` (`product_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品属性表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `result` text NOT NULL COMMENT '商品属性参数',
  `change_time` int(10) unsigned NOT NULL COMMENT '上次修改时间',
  UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品属性详情表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination_attr_value`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination_attr_value` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `suk` varchar(128) NOT NULL COMMENT '商品属性索引值 (attr_value|attr_value[|....])',
  `stock` int(10) unsigned NOT NULL COMMENT '属性对应的库存',
  `sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `price` decimal(8,2) unsigned NOT NULL COMMENT '属性金额',
  `image` varchar(128) DEFAULT NULL COMMENT '图片',
  `unique` char(8) NOT NULL DEFAULT '' COMMENT '唯一值',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT '成本价',
  UNIQUE KEY `unique` (`unique`,`suk`) USING BTREE,
  KEY `store_id` (`product_id`,`suk`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品属性值表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '优惠券表ID',
  `title` varchar(64) NOT NULL COMMENT '优惠券名称',
  `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '兑换消耗积分值',
  `coupon_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '兑换的优惠券面值',
  `use_min_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '最低消费多少金额可用优惠券',
  `coupon_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券有效期限（单位：天）',
  `sort` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0：关闭，1：开启）',
  `add_time` int(11) unsigned NOT NULL COMMENT '兑换项目添加时间',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  KEY `state` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `coupon_time` (`coupon_time`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠券表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon_issue`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon_issue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) DEFAULT NULL COMMENT '优惠券ID',
  `start_time` int(10) DEFAULT NULL COMMENT '优惠券领取开启时间',
  `end_time` int(10) DEFAULT NULL COMMENT '优惠券领取结束时间',
  `total_count` int(10) DEFAULT NULL COMMENT '优惠券领取数量',
  `remain_count` int(10) DEFAULT NULL COMMENT '优惠券剩余领取数量',
  `is_permanent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否无限张数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 正常 0 未开启 -1 已无效',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) DEFAULT NULL COMMENT '优惠券添加时间',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`) USING BTREE,
  KEY `start_time` (`start_time`,`end_time`) USING BTREE,
  KEY `remain_count` (`remain_count`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠券前台领取表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon_issue_user`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon_issue_user` (
  `uid` int(10) DEFAULT NULL COMMENT '领取优惠券用户ID',
  `issue_coupon_id` int(10) DEFAULT NULL COMMENT '优惠券前台领取ID',
  `add_time` int(10) DEFAULT NULL COMMENT '领取时间',
  UNIQUE KEY `uid` (`uid`,`issue_coupon_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠券前台用户领取记录表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon_user`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '优惠券发放记录id',
  `cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '兑换的项目id',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券所属用户',
  `coupon_title` varchar(32) NOT NULL COMMENT '优惠券名称',
  `coupon_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠券的面值',
  `use_min_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '最低消费多少金额可用优惠券',
  `add_time` int(11) unsigned NOT NULL COMMENT '优惠券创建时间',
  `end_time` int(11) unsigned NOT NULL COMMENT '优惠券结束时间',
  `use_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  `type` varchar(32) NOT NULL DEFAULT 'send' COMMENT '获取方式',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0：未使用，1：已使用, 2:已过期）',
  `is_fail` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有效',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `end_time` (`end_time`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_fail` (`is_fail`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠券发放记录表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_order`
--

CREATE TABLE IF NOT EXISTS `eb_store_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_id` varchar(32) NOT NULL COMMENT '订单号',
  `uid` int(11) unsigned NOT NULL COMMENT '用户id',
  `real_name` varchar(32) NOT NULL COMMENT '用户姓名',
  `user_phone` varchar(18) NOT NULL COMMENT '用户电话',
  `user_address` varchar(100) NOT NULL COMMENT '详细地址',
  `cart_id` varchar(256) NOT NULL DEFAULT '[]' COMMENT '购物车id',
  `total_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单商品总数',
  `total_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单总价',
  `total_postage` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '邮费',
  `pay_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际支付金额',
  `pay_postage` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '支付邮费',
  `deduction_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '抵扣金额',
  `coupon_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券id',
  `coupon_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠券金额',
  `paid` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付状态',
  `pay_time` int(11) unsigned DEFAULT NULL COMMENT '支付时间',
  `pay_type` varchar(32) NOT NULL COMMENT '支付方式',
  `add_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态（-1 : 申请退款 -2 : 退货成功 0：待发货；1：待收货；2：已收货；3：待评价；-1：已退款）',
  `refund_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 未退款 1 申请中 2 已退款',
  `refund_reason_wap_img` varchar(255) DEFAULT NULL COMMENT '退款图片',
  `refund_reason_wap_explain` varchar(255) DEFAULT NULL COMMENT '退款用户说明',
  `refund_reason_time` int(11) unsigned DEFAULT NULL COMMENT '退款时间',
  `refund_reason_wap` varchar(255) DEFAULT NULL COMMENT '前台退款原因',
  `refund_reason` varchar(255) DEFAULT NULL COMMENT '不退款的理由',
  `refund_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `delivery_name` varchar(64) DEFAULT NULL COMMENT '快递名称/送货人姓名',
  `delivery_type` varchar(32) DEFAULT NULL COMMENT '发货类型',
  `delivery_id` varchar(64) DEFAULT NULL COMMENT '快递单号/手机号',
  `gain_integral` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '消费赚取积分',
  `use_integral` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '使用积分',
  `back_integral` decimal(8,2) unsigned DEFAULT NULL COMMENT '给用户退了多少积分',
  `mark` varchar(512) NOT NULL COMMENT '备注',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `unique` char(32) NOT NULL COMMENT '唯一id(md5加密)类似id',
  `remark` varchar(512) DEFAULT NULL COMMENT '管理员备注',
  `mer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商户ID',
  `is_mer_check` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `combination_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '拼团产品id0一般产品',
  `pink_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '拼团id 0没有拼团',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT '成本价',
  `seckill_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀产品ID',
  `bargain_id` int(11) unsigned DEFAULT '0' COMMENT '砍价id',
  `is_channel` tinyint(1) unsigned DEFAULT '0' COMMENT '支付渠道(0微信公众号1微信小程序)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id_2` (`order_id`,`uid`) USING BTREE,
  UNIQUE KEY `unique` (`unique`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `pay_price` (`pay_price`) USING BTREE,
  KEY `paid` (`paid`) USING BTREE,
  KEY `pay_time` (`pay_time`) USING BTREE,
  KEY `pay_type` (`pay_type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `coupon_id` (`coupon_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_order_cart_info`
--

CREATE TABLE IF NOT EXISTS `eb_store_order_cart_info` (
  `oid` int(11) unsigned NOT NULL COMMENT '订单id',
  `cart_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购物车id',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `cart_info` text NOT NULL COMMENT '购买东西的详细信息',
  `unique` char(32) NOT NULL COMMENT '唯一id',
  UNIQUE KEY `oid` (`oid`,`unique`) USING BTREE,
  KEY `cart_id` (`cart_id`) USING BTREE,
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单购物详情表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_order_status`
--

CREATE TABLE IF NOT EXISTS `eb_store_order_status` (
  `oid` int(10) unsigned NOT NULL COMMENT '订单id',
  `change_type` varchar(32) NOT NULL COMMENT '操作类型',
  `change_message` varchar(256) NOT NULL COMMENT '操作备注',
  `change_time` int(10) unsigned NOT NULL COMMENT '操作时间',
  KEY `oid` (`oid`) USING BTREE,
  KEY `change_type` (`change_type`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单操作记录表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_pink`
--

CREATE TABLE IF NOT EXISTS `eb_store_pink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `order_id` varchar(32) NOT NULL COMMENT '订单id 生成',
  `order_id_key` int(10) unsigned NOT NULL COMMENT '订单id  数据库',
  `total_num` int(10) unsigned NOT NULL COMMENT '购买商品个数',
  `total_price` decimal(10,2) unsigned NOT NULL COMMENT '购买总金额',
  `cid` int(10) unsigned NOT NULL COMMENT '拼团产品id',
  `pid` int(10) unsigned NOT NULL COMMENT '产品id',
  `people` int(10) unsigned NOT NULL COMMENT '拼图总人数',
  `price` decimal(10,2) unsigned NOT NULL COMMENT '拼团产品单价',
  `add_time` varchar(24) NOT NULL COMMENT '开始时间',
  `stop_time` varchar(24) NOT NULL,
  `k_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '团长id 0为团长',
  `is_tpl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送模板消息0未发送1已发送',
  `is_refund` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否退款 0未退款 1已退款',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态1进行中2已完成3未完成',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='拼团表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product`
--

CREATE TABLE IF NOT EXISTS `eb_store_product` (
  `id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `mer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商户Id(0为总后台管理员创建,不为0的时候是商户后台创建)',
  `image` varchar(128) NOT NULL COMMENT '商品图片',
  `slider_image` varchar(512) NOT NULL COMMENT '轮播图',
  `store_name` varchar(128) NOT NULL COMMENT '商品名称',
  `store_info` varchar(256) NOT NULL COMMENT '商品简介',
  `keyword` varchar(256) NOT NULL COMMENT '关键字',
  `cate_id` varchar(64) NOT NULL COMMENT '分类id',
  `price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `vip_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员价格',
  `ot_price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '市场价',
  `postage` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '邮费',
  `unit_name` varchar(32) NOT NULL COMMENT '单位名',
  `sort` smallint(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `sales` mediumint(11) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `stock` mediumint(11) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：未上架，1：上架）',
  `is_hot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否热卖',
  `is_benefit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否优惠',
  `is_best` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否精品',
  `is_new` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否新品',
  `description` text NOT NULL COMMENT '产品描述',
  `add_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  `is_postage` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否包邮',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `mer_use` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '商户是否代理 0不可代理1可代理',
  `give_integral` decimal(8,2) unsigned NOT NULL COMMENT '获得积分',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT '成本价',
  `is_seckill` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀状态 0 未开启 1已开启',
  `is_bargain` tinyint(1) unsigned NOT NULL COMMENT '砍价状态 0未开启 1开启',
  `ficti` mediumint(11) DEFAULT '100' COMMENT '虚拟销量',
  `browse` int(11) DEFAULT '0' COMMENT '浏览量',
  PRIMARY KEY (`id`),
  KEY `cate_id` (`cate_id`) USING BTREE,
  KEY `is_hot` (`is_hot`) USING BTREE,
  KEY `is_benefit` (`is_benefit`) USING BTREE,
  KEY `is_best` (`is_best`) USING BTREE,
  KEY `is_new` (`is_new`) USING BTREE,
  KEY `toggle_on_sale, is_del` (`is_del`) USING BTREE,
  KEY `price` (`price`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `sales` (`sales`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `is_postage` (`is_postage`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='商品表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_store_product`
--

INSERT INTO `eb_store_product` (`id`, `mer_id`, `image`, `slider_image`, `store_name`, `store_info`, `keyword`, `cate_id`, `price`, `vip_price`, `ot_price`, `postage`, `unit_name`, `sort`, `sales`, `stock`, `is_show`, `is_hot`, `is_benefit`, `is_best`, `is_new`, `description`, `add_time`, `is_postage`, `is_del`, `mer_use`, `give_integral`, `cost`, `is_seckill`, `is_bargain`, `ficti`, `browse`) VALUES
(1, 0, 'http://doemo.net/public/uploads/0/20180821/5b7b784d35389.jpg', '["http:\\/\\/doemo.net\\/public\\/uploads\\/0\\/20180821\\/5b7b784d35389.jpg"]', '测试测试', '测试测试测试测试测试测试', '测试测试', '', '10.00', '0.00', '10.00', '0.00', '件', 10, 10, 1, 1, 1, 1, 1, 1, '', 1534818410, 1, 0, 0, '0.00', '10.00', 0, 0, 1010, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `attr_name` varchar(32) NOT NULL COMMENT '属性名',
  `attr_values` varchar(256) NOT NULL COMMENT '属性值',
  KEY `store_id` (`product_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品属性表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `result` text NOT NULL COMMENT '商品属性参数',
  `change_time` int(10) unsigned NOT NULL COMMENT '上次修改时间',
  UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品属性详情表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr_value`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr_value` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `suk` varchar(128) NOT NULL COMMENT '商品属性索引值 (attr_value|attr_value[|....])',
  `stock` int(10) unsigned NOT NULL COMMENT '属性对应的库存',
  `sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `price` decimal(8,2) unsigned NOT NULL COMMENT '属性金额',
  `image` varchar(128) DEFAULT NULL COMMENT '图片',
  `unique` char(8) NOT NULL DEFAULT '' COMMENT '唯一值',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT '成本价',
  UNIQUE KEY `unique` (`unique`,`suk`) USING BTREE,
  KEY `store_id` (`product_id`,`suk`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品属性值表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_relation`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_relation` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `type` varchar(32) NOT NULL COMMENT '类型(收藏(collect）、点赞(like))',
  `category` varchar(32) NOT NULL COMMENT '某种类型的商品(普通商品、秒杀商品)',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  UNIQUE KEY `uid` (`uid`,`product_id`,`type`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `category` (`category`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品点赞和收藏表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_reply`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `oid` int(11) NOT NULL COMMENT '订单ID',
  `unique` char(32) NOT NULL COMMENT '唯一id',
  `product_id` int(11) NOT NULL COMMENT '产品id',
  `reply_type` varchar(32) NOT NULL DEFAULT 'product' COMMENT '某种商品类型(普通商品、秒杀商品）',
  `product_score` tinyint(1) NOT NULL COMMENT '商品分数',
  `service_score` tinyint(1) NOT NULL COMMENT '服务分数',
  `comment` varchar(512) NOT NULL COMMENT '评论内容',
  `pics` text NOT NULL COMMENT '评论图片',
  `add_time` int(11) NOT NULL COMMENT '评论时间',
  `merchant_reply_content` varchar(300) NOT NULL COMMENT '管理员回复内容',
  `merchant_reply_time` int(11) NOT NULL COMMENT '管理员回复时间',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未删除1已删除',
  `is_reply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未回复1已回复',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_id_2` (`oid`,`unique`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `parent_id` (`reply_type`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `product_score` (`product_score`) USING BTREE,
  KEY `service_score` (`service_score`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品秒杀产品表id',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `image` varchar(255) NOT NULL COMMENT '推荐图',
  `images` varchar(1000) NOT NULL COMMENT '轮播图',
  `title` varchar(255) NOT NULL COMMENT '活动标题',
  `info` varchar(255) NOT NULL COMMENT '简介',
  `price` decimal(10,2) unsigned NOT NULL COMMENT '价格',
  `cost` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本',
  `ot_price` decimal(10,2) unsigned NOT NULL COMMENT '原价',
  `give_integral` decimal(10,2) unsigned NOT NULL COMMENT '返多少积分',
  `sort` int(10) unsigned NOT NULL COMMENT '排序',
  `stock` int(10) unsigned NOT NULL COMMENT '库存',
  `sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `unit_name` varchar(16) NOT NULL COMMENT '单位名',
  `postage` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '邮费',
  `description` text NOT NULL COMMENT '内容',
  `start_time` varchar(128) NOT NULL COMMENT '开始时间',
  `stop_time` varchar(128) NOT NULL COMMENT '结束时间',
  `add_time` varchar(128) NOT NULL COMMENT '添加时间',
  `status` tinyint(1) unsigned NOT NULL COMMENT '产品状态',
  `is_postage` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否包邮',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '热门推荐',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除 0未删除1已删除',
  `num` int(11) unsigned NOT NULL COMMENT '最多秒杀几个',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '显示',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `start_time` (`start_time`,`stop_time`),
  KEY `is_del` (`is_del`),
  KEY `is_hot` (`is_hot`),
  KEY `is_show` (`status`),
  KEY `add_time` (`add_time`),
  KEY `sort` (`sort`),
  KEY `is_postage` (`is_postage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品秒杀产品表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `attr_name` varchar(32) NOT NULL COMMENT '属性名',
  `attr_values` varchar(256) NOT NULL COMMENT '属性值',
  KEY `store_id` (`product_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='秒杀商品属性表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `result` text NOT NULL COMMENT '商品属性参数',
  `change_time` int(10) unsigned NOT NULL COMMENT '上次修改时间',
  UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='秒杀商品属性详情表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr_value`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr_value` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `suk` varchar(128) NOT NULL COMMENT '商品属性索引值 (attr_value|attr_value[|....])',
  `stock` int(10) unsigned NOT NULL COMMENT '属性对应的库存',
  `sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销量',
  `price` decimal(8,2) unsigned NOT NULL COMMENT '属性金额',
  `image` varchar(128) DEFAULT NULL COMMENT '图片',
  `unique` char(8) NOT NULL DEFAULT '' COMMENT '唯一值',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT '成本价',
  UNIQUE KEY `unique` (`unique`,`suk`) USING BTREE,
  KEY `store_id` (`product_id`,`suk`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='秒杀商品属性值表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_service`
--

CREATE TABLE IF NOT EXISTS `eb_store_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '客服id',
  `mer_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `uid` int(11) NOT NULL COMMENT '客服uid',
  `avatar` varchar(250) NOT NULL COMMENT '客服头像',
  `nickname` varchar(50) NOT NULL COMMENT '代理名称',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0隐藏1显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='客服表' AUTO_INCREMENT=10 ;

--
-- 转存表中的数据 `eb_store_service`
--

INSERT INTO `eb_store_service` (`id`, `mer_id`, `uid`, `avatar`, `nickname`, `add_time`, `status`) VALUES
(1, 0, 90, 'http://thirdwx.qlogo.cn/mmopen/LneiciaJhByic2MV0ocMFdPHJzlaWskqtgN5qCAojya1LHbjlhIHzWOBehN78WTuAqUjOnUUbSpJKpYJlaysap1HUpfzeQg0ugP/132', '天会亮、心会暖', 1528681446, 1),
(9, 0, 1, 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLCx03Y4hkSeVgQZGZLYDSQz6SZ7PDDNSLj3RxVPYqibMvW4cIOicPSSmA0xbrL9DY2RkunA1pulAs9g/132', '等风来，随风去', 1534312905, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_service_log`
--

CREATE TABLE IF NOT EXISTS `eb_store_service_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '客服用户对话记录表ID',
  `mer_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `msn` text NOT NULL COMMENT '消息内容',
  `uid` int(11) NOT NULL COMMENT '发送人uid',
  `to_uid` int(11) NOT NULL COMMENT '接收人uid',
  `add_time` int(11) NOT NULL COMMENT '发送时间',
  `type` tinyint(1) DEFAULT NULL COMMENT '是否已读（0：否；1：是；）',
  `remind` tinyint(1) DEFAULT NULL COMMENT '是否提醒过',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='客服用户对话记录表' AUTO_INCREMENT=46 ;

--
-- 转存表中的数据 `eb_store_service_log`
--

INSERT INTO `eb_store_service_log` (`id`, `mer_id`, `msn`, `uid`, `to_uid`, `add_time`, `type`, `remind`) VALUES
(1, 0, '能收到消息吗', 65, 90, 1528875497, NULL, NULL),
(2, 0, '[拜拜]', 65, 105, 1528875827, NULL, NULL),
(3, 0, '[亲亲]', 65, 111, 1528875835, NULL, NULL),
(4, 0, '[害羞]', 72, 65, 1528875934, NULL, NULL),
(5, 0, '人呢？', 72, 65, 1528875957, NULL, NULL),
(6, 0, '[大笑]', 168, 65, 1528957108, NULL, NULL),
(7, 0, '[感觉真好]', 1, 65, 1528960540, NULL, NULL),
(8, 0, '你好', 169, 90, 1528974505, NULL, NULL),
(9, 0, '你好', 66, 65, 1528976476, NULL, NULL),
(10, 0, '哈咯呀', 66, 65, 1528976482, NULL, NULL),
(11, 0, '在吗', 172, 65, 1529026203, NULL, NULL),
(12, 0, '你这个没有分销功能吗', 172, 65, 1529026214, NULL, NULL),
(13, 0, '你好', 171, 90, 1529380637, NULL, NULL),
(14, 0, '[大喷血]', 171, 90, 1529380649, NULL, NULL),
(15, 0, 'hi', 176, 65, 1529382467, NULL, NULL),
(16, 0, '哦你我', 179, 65, 1529400014, NULL, NULL),
(17, 0, '出来接客', 185, 65, 1529464154, NULL, NULL),
(18, 0, '源码出售？', 185, 65, 1529464166, NULL, NULL),
(19, 0, '1', 191, 90, 1529559224, NULL, NULL),
(20, 0, '<img class="img" src="/public/uploads/wechat/media/75cfd4562cec21da0645.jpg" onclick="img_detail($(this))" />', 191, 65, 1529559261, NULL, NULL),
(21, 0, '<img class="img" src="/public/uploads/wechat/media/7621770cf214576487ed.jpg" onclick="img_detail($(this))" />', 191, 90, 1529559557, NULL, NULL),
(22, 0, '你好', 209, 90, 1529639162, NULL, NULL),
(23, 0, '这是微信聊天吗', 209, 90, 1529639172, NULL, NULL),
(24, 0, '11', 232, 65, 1529979290, NULL, NULL),
(25, 0, '明年', 239, 65, 1529996509, NULL, NULL),
(26, 0, '11', 260, 65, 1530326762, NULL, NULL),
(27, 0, '你好！', 264, 65, 1530380433, NULL, NULL),
(28, 0, '999', 261, 65, 1530500304, NULL, NULL),
(29, 0, '<img class="img" src="/public/uploads/wechat/media/82acc9a85b78bb3996ee.jpg" onclick="img_detail($(this))" />', 264, 90, 1530559560, NULL, NULL),
(30, 0, '你高', 264, 90, 1530676572, NULL, NULL),
(31, 0, '。。。', 280, 90, 1531184216, NULL, NULL),
(32, 0, '你好', 314, 90, 1531972853, NULL, NULL),
(33, 0, '你好', 327, 266, 1532052844, NULL, NULL),
(34, 0, '。。。。', 69, 266, 1533459833, NULL, NULL),
(35, 0, '[大笑]', 81, 266, 1533804713, NULL, NULL),
(36, 0, '435123123', 69, 266, 1533810766, NULL, NULL),
(37, 0, '你能回话。', 69, 266, 1533877078, NULL, NULL),
(38, 0, '[感觉真好]', 69, 266, 1533877086, NULL, NULL),
(39, 0, '<img class="img" src="/public/uploads/wechat/media/6227b83dd040458976a6.jpg" onclick="img_detail($(this))" />', 69, 266, 1534147509, NULL, NULL),
(40, 0, '啦啦啦啦', 20, 90, 1534233307, NULL, NULL),
(41, 0, '国家经济理论，了国家经济', 91, 90, 1534299668, NULL, NULL),
(42, 0, '斯里兰卡民主社会主义共和国', 91, 90, 1534299681, NULL, NULL),
(43, 0, '桂林路田林路', 91, 90, 1534299699, NULL, NULL),
(44, 0, '7989798', 20, 90, 1534299708, NULL, NULL),
(45, 0, '你以为陪你', 95, 90, 1534303730, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_visit`
--

CREATE TABLE IF NOT EXISTS `eb_store_visit` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL COMMENT '产品ID',
  `product_type` varchar(32) DEFAULT NULL COMMENT '产品类型',
  `cate_id` int(11) DEFAULT NULL COMMENT '产品分类ID',
  `type` char(50) DEFAULT NULL COMMENT '产品类型',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `count` int(11) DEFAULT NULL COMMENT '访问次数',
  `content` varchar(255) DEFAULT NULL COMMENT '备注描述',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='产品浏览分析表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_store_visit`
--

INSERT INTO `eb_store_visit` (`id`, `product_id`, `product_type`, `cate_id`, `type`, `uid`, `count`, `content`, `add_time`) VALUES
(1, 0, 'product', 0, 'search', 1, 1, '0', 1535070682);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_admin`
--

CREATE TABLE IF NOT EXISTS `eb_system_admin` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '后台管理员表ID',
  `account` varchar(32) NOT NULL COMMENT '后台管理员账号',
  `pwd` char(32) NOT NULL COMMENT '后台管理员密码',
  `real_name` varchar(16) NOT NULL COMMENT '后台管理员姓名',
  `roles` varchar(128) NOT NULL COMMENT '后台管理员权限(menus_id)',
  `last_ip` varchar(16) DEFAULT NULL COMMENT '后台管理员最后一次登录ip',
  `last_time` int(10) unsigned DEFAULT NULL COMMENT '后台管理员最后一次登录时间',
  `add_time` int(10) unsigned NOT NULL COMMENT '后台管理员添加时间',
  `login_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '后台管理员级别',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '后台管理员状态 1有效0无效',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account` (`account`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='后台管理员表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_system_admin`
--

INSERT INTO `eb_system_admin` (`id`, `account`, `pwd`, `real_name`, `roles`, `last_ip`, `last_time`, `add_time`, `login_count`, `level`, `status`, `is_del`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin', '1', '127.0.0.1', 1535070363, 1534816241, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_attachment`
--

CREATE TABLE IF NOT EXISTS `eb_system_attachment` (
  `att_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '附件名称',
  `att_dir` varchar(200) NOT NULL COMMENT '附件路径',
  `satt_dir` varchar(200) DEFAULT NULL COMMENT '压缩图片路径',
  `att_size` char(30) NOT NULL COMMENT '附件大小',
  `att_type` char(30) NOT NULL COMMENT '附件类型',
  `pid` int(10) NOT NULL COMMENT '分类ID0编辑器,1产品图片,2拼团图片,3砍价图片,4秒杀图片,5文章图片,6组合数据图',
  `time` int(11) NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`att_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='附件管理表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_system_attachment`
--

INSERT INTO `eb_system_attachment` (`att_id`, `name`, `att_dir`, `satt_dir`, `att_size`, `att_type`, `pid`, `time`) VALUES
(1, '5b7b784d35389.jpg', '\\public\\uploads\\0/20180821\\5b7b784d35389.jpg', '', '67105', 'image/jpeg', 0, 1534818381);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_attachment_category`
--

CREATE TABLE IF NOT EXISTS `eb_system_attachment_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '父级ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `enname` varchar(50) NOT NULL COMMENT '分类目录',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- 转存表中的数据 `eb_system_attachment_category`
--

INSERT INTO `eb_system_attachment_category` (`id`, `pid`, `name`, `enname`) VALUES
(1, 0, '产品图片', 'product'),
(2, 0, '新闻图片', 'news'),
(3, 0, '配置图片', 'config'),
(4, 3, '首页导航', 'indexnav'),
(5, 3, '首页幻灯片', 'mynav'),
(6, 3, '其它配置图', 'footnav'),
(7, 2, '公司新闻', 'compay'),
(8, 1, '拼团产品图', ''),
(9, 1, '秒杀图片', ''),
(10, 1, '砍价产品图', ''),
(11, 1, '普通产品图片', ''),
(21, 0, '衣服', ''),
(22, 0, '衣服2', ''),
(23, 0, '衣服3', ''),
(24, 0, '衣服4', ''),
(25, 0, '衣服5', '');

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_config`
--

CREATE TABLE IF NOT EXISTS `eb_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置id',
  `menu_name` varchar(255) NOT NULL COMMENT '字段名称',
  `type` varchar(255) NOT NULL COMMENT '类型(文本框,单选按钮...)',
  `config_tab_id` int(10) unsigned NOT NULL COMMENT '配置分类id',
  `parameter` varchar(255) DEFAULT NULL COMMENT '规则 单选框和多选框',
  `upload_type` tinyint(1) unsigned DEFAULT NULL COMMENT '上传文件格式1单图2多图3文件',
  `required` varchar(255) DEFAULT NULL COMMENT '规则',
  `width` int(10) unsigned DEFAULT NULL COMMENT '多行文本框的宽度',
  `high` int(10) unsigned DEFAULT NULL COMMENT '多行文框的高度',
  `value` varchar(5000) DEFAULT NULL COMMENT '默认值',
  `info` varchar(255) NOT NULL COMMENT '配置名称',
  `desc` varchar(255) DEFAULT NULL COMMENT '配置简介',
  `sort` int(10) unsigned NOT NULL COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL COMMENT '是否隐藏',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='配置表' AUTO_INCREMENT=96 ;

--
-- 转存表中的数据 `eb_system_config`
--

INSERT INTO `eb_system_config` (`id`, `menu_name`, `type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(1, 'site_name', 'text', 1, NULL, NULL, 'required:true', 100, NULL, '"CRMEB"', '网站名称', '网站名称', 0, 1),
(2, 'site_url', 'text', 1, NULL, NULL, 'required:true,url:true', 100, NULL, '"http:\\/\\/demo25.crmeb.net"', '网站地址', '网站地址', 0, 1),
(3, 'site_logo', 'upload', 1, NULL, 1, NULL, NULL, NULL, '"\\/public\\/uploads\\/config\\/image\\/5b77c4c33fb1f.png"', '后台LOGO', '左上角logo,建议尺寸[170*50]', 0, 1),
(4, 'site_phone', 'text', 1, NULL, NULL, NULL, 100, NULL, '"13679281569"', '联系电话', '联系电话', 0, 1),
(5, 'seo_title', 'text', 1, NULL, NULL, 'required:true', 100, NULL, '"crmeb\\u7535\\u5546\\u7cfb\\u7edf"', 'SEO标题', 'SEO标题', 0, 1),
(6, 'site_email', 'text', 1, NULL, NULL, 'email:true', 100, NULL, '"admin@xazbkj.com"', '联系邮箱', '联系邮箱', 0, 1),
(7, 'site_qq', 'text', 1, NULL, NULL, 'qq:true', 100, NULL, '"98718401"', '联系QQ', '联系QQ', 0, 1),
(8, 'site_close', 'radio', 1, '0=开启\n1=PC端关闭\n2=WAP端关闭(含微信)\n3=全部关闭', NULL, '', NULL, NULL, '"2"', '网站关闭', '网站后台、商家中心不受影响。关闭网站也可访问', 0, 1),
(9, 'close_system', 'radio', 1, '0=开启\n1=关闭', NULL, '', NULL, NULL, '"0"', '关闭后台', '关闭后台', 0, 2),
(10, 'wechat_name', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '"CRMEB"', '公众号名称', '公众号的名称', 0, 1),
(11, 'wechat_id', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '"CRMEB"', '微信号', '微信号', 0, 1),
(12, 'wechat_sourceid', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '""', '公众号原始id', '公众号原始id', 0, 1),
(13, 'wechat_appid', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '""', 'AppID', 'AppID', 0, 1),
(14, 'wechat_appsecret', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '""', 'AppSecret', 'AppSecret', 0, 1),
(15, 'wechat_token', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '""', '微信验证TOKEN', '微信验证TOKEN', 0, 1),
(16, 'wechat_encode', 'radio', 2, '0=明文模式\n1=兼容模式\n2=安全模式', NULL, '', NULL, NULL, '"0"', '消息加解密方式', '如需使用安全模式请在管理中心修改，仅限服务号和认证订阅号', 0, 1),
(17, 'wechat_encodingaeskey', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '""', 'EncodingAESKey', '公众号消息加解密Key,在使用安全模式情况下要填写该值，请先在管理中心修改，然后填写该值，仅限服务号和认证订阅号', 0, 1),
(18, 'wechat_share_img', 'upload', 3, NULL, 1, NULL, NULL, NULL, '""', '微信分享图片', '若填写此图片地址，则分享网页出去时会分享此图片。可有效防止分享图片变形', 0, 1),
(19, 'wechat_qrcode', 'upload', 2, NULL, 1, NULL, NULL, NULL, '""', '公众号二维码', '您的公众号二维码', 0, 1),
(20, 'wechat_type', 'radio', 2, '0=服务号\n1=订阅号', NULL, '', NULL, NULL, '"0"', '公众号类型', '公众号的类型', 0, 1),
(21, 'wechat_share_title', 'text', 3, NULL, NULL, 'required:true', 100, NULL, '""', '微信分享标题', '微信分享标题', 0, 1),
(22, 'wechat_share_synopsis', 'textarea', 3, NULL, NULL, NULL, 100, 5, '""', '微信分享简介', '微信分享简介', 0, 1),
(23, 'pay_weixin_appid', 'text', 4, NULL, NULL, '', 100, NULL, '""', 'Appid', '微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看。', 0, 1),
(24, 'pay_weixin_appsecret', 'text', 4, NULL, NULL, '', 100, NULL, '""', 'Appsecret', 'JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看。', 0, 1),
(25, 'pay_weixin_mchid', 'text', 4, NULL, NULL, '', 100, NULL, '""', 'Mchid', '受理商ID，身份标识', 0, 1),
(26, 'pay_weixin_client_cert', 'upload', 4, NULL, 3, NULL, NULL, NULL, '""', '微信支付证书', '微信支付证书，在微信商家平台中可以下载！文件名一般为apiclient_cert.pem', 0, 1),
(27, 'pay_weixin_client_key', 'upload', 4, NULL, 3, NULL, NULL, NULL, '""', '微信支付证书密钥', '微信支付证书密钥，在微信商家平台中可以下载！文件名一般为apiclient_key.pem', 0, 1),
(56, 'store_home_pink', 'upload', 5, NULL, 1, NULL, NULL, NULL, '"\\/public\\/uploads\\/config\\/image\\/5abcad84e2a52.jpg"', '首页拼团广告图', '首页拼团广告图', 0, 1),
(28, 'pay_weixin_key', 'text', 4, NULL, NULL, '', 100, NULL, '""', 'Key', '商户支付密钥Key。审核通过后，在微信发送的邮件中查看。', 0, 1),
(29, 'pay_weixin_open', 'radio', 4, '1=开启\n0=关闭', NULL, '', NULL, NULL, '"1"', '开启', '是否启用微信支付', 0, 1),
(31, 'store_postage', 'text', 10, NULL, NULL, 'number:true,min:0', 100, NULL, '"0"', '邮费基础价', '商品邮费基础价格,最终金额为(基础价 + 商品1邮费 + 商品2邮费)', 0, 1),
(32, 'store_free_postage', 'text', 5, NULL, NULL, 'number:true,min:-1', 100, NULL, '"20"', '满额包邮', '商城商品满多少金额即可包邮', 0, 1),
(33, 'offline_postage', 'radio', 10, '0=不包邮\n1=包邮', NULL, NULL, NULL, NULL, '"1"', '线下支付是否包邮', '用户选择线下支付时是否包邮', 0, 1),
(34, 'integral_ratio', 'text', 11, NULL, NULL, 'number:true', 100, NULL, '"0.01"', '积分抵用比例', '积分抵用比例(1积分抵多少金额)', 0, 1),
(35, 'site_service_phone', 'text', 1, NULL, NULL, NULL, 100, NULL, '"400-8888794"', '客服电话', '客服联系电话', 0, 1),
(44, 'store_user_min_recharge', 'text', 5, NULL, NULL, 'required:true,number:true,min:0', 100, NULL, '"0.01"', '用户最低充值金额', '用户单次最低充值金额', 0, 0),
(45, 'site_store_admin_uids', 'text', 5, NULL, NULL, '', 100, NULL, '"4"', '管理员用户ID', '管理员用户ID,用于接收商城订单提醒，到微信用户中查找编号，多个英文‘,’隔开', 0, 1),
(46, 'system_express_app_code', 'text', 10, NULL, NULL, '', 100, NULL, '"658da8789d89436699269f4aede6c868"', '快递查询密钥', '阿里云快递查询接口密钥购买地址：https://market.aliyun.com/products/57126001/cmapi011120.html', 0, 1),
(47, 'main_business', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '" IT\\u79d1\\u6280 \\u4e92\\u8054\\u7f51|\\u7535\\u5b50\\u5546\\u52a1"', '微信模板消息_主营行业', '微信公众号模板消息中选择开通的主营行业', 0, 0),
(48, 'vice_business', 'text', 2, NULL, NULL, 'required:true', 100, NULL, '"IT\\u79d1\\u6280 IT\\u8f6f\\u4ef6\\u4e0e\\u670d\\u52a1 "', '微信模板消息_副营行业', '微信公众号模板消息中选择开通的副营行业', 0, 0),
(49, 'store_brokerage_ratio', 'text', 9, NULL, NULL, 'required:true,min:0,max:100,number:true', 100, NULL, '"5"', '一级返佣比例', '订单交易成功后给上级返佣的比例0 - 100,例:5 = 反订单金额的5%', 5, 1),
(50, 'wechat_first_sub_give_coupon', 'text', 12, NULL, NULL, 'requred:true,digits:true,min:0', 100, NULL, '""', '首次关注赠送优惠券ID', '首次关注赠送优惠券ID,0为不赠送', 0, 1),
(51, 'store_give_con_min_price', 'text', 12, NULL, NULL, 'requred:true,digits:true,min:0', 100, NULL, '"0.01"', '消费满多少赠送优惠券', '消费满多少赠送优惠券,0为不赠送', 0, 1),
(52, 'store_order_give_coupon', 'text', 12, NULL, NULL, 'requred:true,digits:true,min:0', 100, NULL, '""', '消费赠送优惠劵ID', '消费赠送优惠劵ID,0为不赠送', 0, 1),
(53, 'user_extract_min_price', 'text', 9, NULL, NULL, 'required:true,number:true,min:0', 100, NULL, '"200"', '提现最低金额', '用户提现最低金额', 0, 1),
(54, 'sx_sign_min_int', 'text', 11, NULL, NULL, 'required:true,number:true,min:0', 100, NULL, '"1"', '签到奖励最低积分', '签到奖励最低积分', 0, 1),
(55, 'sx_sign_max_int', 'text', 11, NULL, NULL, 'required:true,number:true,min:0', 100, NULL, '"5"', '签到奖励最高积分', '签到奖励最高积分', 0, 1),
(57, 'about_us', 'upload', 1, NULL, 1, NULL, NULL, NULL, '"\\/public\\/uploads\\/config\\/image\\/5b77c4caa5e1d.png"', '关于我们', '系统的标识', 0, 1),
(61, 'api', 'text', 2, NULL, NULL, '', 100, NULL, '"\\/wechat\\/index\\/serve"', '接口地址', '微信接口例如：http://www.abc.com/wechat/index/serve', 0, 1),
(58, 'replenishment_num', 'text', 5, NULL, NULL, 'required:true,number:true,min:0', 100, NULL, '"20"', '待补货数量', '产品待补货数量低于多少时，提示补货', 0, 1),
(59, 'routine_appId', 'text', 7, NULL, NULL, '', 100, NULL, '""', 'appId', '小程序appID', 0, 1),
(60, 'routine_appsecret', 'text', 7, NULL, NULL, '', 100, NULL, '""', 'AppSecret', '小程序AppSecret', 0, 1),
(62, 'paydir', 'textarea', 4, NULL, NULL, NULL, 100, 5, '"\\/wap\\/my\\/\\r\\n\\/wap\\/my\\/order\\/uni\\/\\r\\n\\/wap\\/store\\/confirm_order\\/cartId\\/"', '配置目录', '支付目录配置系统不调用提示作用', 0, 1),
(73, 'routine_logo', 'upload', 7, NULL, 1, NULL, NULL, NULL, '"\\/public\\/uploads\\/config\\/image\\/5b2c4491c05f2.jpg"', '小程序logo', '小程序logo', 0, 1),
(74, 'routine_name', 'text', 7, NULL, NULL, '', 100, NULL, '"CRMEB"', '小程序名称', '小程序名称', 0, 1),
(76, 'routine_style', 'text', 7, NULL, NULL, '', 100, NULL, '"#FFFFFF"', '小程序风格', '小程序颜色', 0, 1),
(77, 'store_stock', 'text', 5, NULL, NULL, '', 100, NULL, '"1"', '警戒库存', '警戒库存提醒值', 0, 1),
(88, 'store_brokerage_statu', 'radio', 9, '1=指定分销\n2=人人分销', NULL, NULL, NULL, NULL, '"1"', '分销模式', '人人分销默认每个人都可以分销，制定人分销后台制定人开启分销', 10, 1),
(85, 'stor_reason', 'textarea', 5, NULL, NULL, NULL, 100, 8, '"\\u6536\\u8d27\\u5730\\u5740\\u586b\\u9519\\u4e86\\r\\n\\u4e0e\\u63cf\\u8ff0\\u4e0d\\u7b26\\r\\n\\u4fe1\\u606f\\u586b\\u9519\\u4e86\\uff0c\\u91cd\\u65b0\\u62cd\\r\\n\\u6536\\u5230\\u5546\\u54c1\\u635f\\u574f\\u4e86\\r\\n\\u672a\\u6309\\u9884\\u5b9a\\u65f6\\u95f4\\u53d1\\u8d27\\r\\n\\u5176\\u5b83\\u539f\\u56e0"', '退货理由', '配置退货理由，一行一个理由', 0, 1),
(87, 'store_brokerage_two', 'text', 9, NULL, NULL, 'required:true,min:0,max:100,number:true', 100, NULL, '"3"', '二级返佣比例', '订单交易成功后给上级返佣的比例0 - 100,例:5 = 反订单金额的5%', 4, 1),
(89, 'pay_routine_appid', 'text', 14, NULL, NULL, 'required:true', 100, NULL, '""', 'Appid', '小程序Appid', 0, 1),
(90, 'pay_routine_appsecret', 'text', 14, NULL, NULL, 'required:true', 100, NULL, '""', 'Appsecret', '小程序Appsecret', 0, 1),
(91, 'pay_routine_mchid', 'text', 14, NULL, NULL, 'required:true', 100, NULL, '""', 'Mchid', '商户号', 0, 1),
(92, 'pay_routine_key', 'text', 14, NULL, NULL, 'required:true', 100, NULL, '""', 'Key', '商户key', 0, 1),
(93, 'pay_routine_client_cert', 'upload', 14, NULL, 3, NULL, NULL, NULL, '""', '小程序支付证书', '小程序支付证书', 0, 1),
(94, 'pay_routine_client_key', 'upload', 14, NULL, 3, NULL, NULL, NULL, '""', '小程序支付证书密钥', '小程序支付证书密钥', 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_config_tab`
--

CREATE TABLE IF NOT EXISTS `eb_system_config_tab` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置分类id',
  `title` varchar(255) NOT NULL COMMENT '配置分类名称',
  `eng_title` varchar(255) NOT NULL COMMENT '配置分类英文名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '配置分类状态',
  `info` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '配置分类是否显示',
  `icon` varchar(30) DEFAULT NULL COMMENT '图标',
  `type` int(2) DEFAULT '0' COMMENT '配置类型',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='配置分类表' AUTO_INCREMENT=15 ;

--
-- 转存表中的数据 `eb_system_config_tab`
--

INSERT INTO `eb_system_config_tab` (`id`, `title`, `eng_title`, `status`, `info`, `icon`, `type`) VALUES
(1, '基础配置', 'basics', 1, 0, 'cog', 0),
(2, '公众号配置', 'wechat', 1, 0, 'weixin', 1),
(3, '公众号分享配置', 'wechat_share', 1, 0, 'whatsapp', 1),
(4, '公众号支付配置', 'pay', 1, 0, 'jpy', 1),
(5, '商城配置', 'store', 1, 0, 'shopping-cart', 0),
(7, '小程序配置', 'routine', 1, 0, 'weixin', 2),
(9, '分销配置', 'fenxiao', 1, 0, 'sitemap', 3),
(10, '物流配置', 'express', 1, 0, 'motorcycle', 0),
(11, '积分配置', 'point', 1, 0, 'powerpoint-o', 3),
(12, '优惠券配置', 'coupon', 1, 0, 'heartbeat', 3),
(14, '小程序支付配置', 'routine_pay', 1, 0, '', 2);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_file`
--

CREATE TABLE IF NOT EXISTS `eb_system_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件对比ID',
  `cthash` char(32) NOT NULL COMMENT '文件内容',
  `filename` varchar(255) NOT NULL COMMENT '文价名称',
  `atime` char(12) NOT NULL COMMENT '上次访问时间',
  `mtime` char(12) NOT NULL COMMENT '上次修改时间',
  `ctime` char(12) NOT NULL COMMENT '上次改变时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文件对比表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_group`
--

CREATE TABLE IF NOT EXISTS `eb_system_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组合数据ID',
  `name` varchar(50) NOT NULL COMMENT '数据组名称',
  `info` varchar(256) NOT NULL COMMENT '数据提示',
  `config_name` varchar(50) NOT NULL COMMENT '数据字段',
  `fields` text COMMENT '数据组字段以及类型（json数据）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_name` (`config_name`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='组合数据表' AUTO_INCREMENT=49 ;

--
-- 转存表中的数据 `eb_system_group`
--

INSERT INTO `eb_system_group` (`id`, `name`, `info`, `config_name`, `fields`) VALUES
(32, '个人中心菜单', '个人中心菜单设置', 'my_index_menu', '[{"name":"\\u540d\\u79f0","title":"name","type":"input","param":""},{"name":"\\u56fe\\u6807","title":"icon","type":"upload","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""}]'),
(34, '商城首页banner', '商城首页banner设置', 'store_home_banner', '[{"name":"\\u6807\\u9898","title":"title","type":"input","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""},{"name":"\\u56fe\\u7247","title":"pic","type":"upload","param":""}]'),
(35, '商城首页分类按钮', '商城首页分类按钮', 'store_home_menus', '[{"name":"\\u540d\\u79f0","title":"name","type":"input","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""},{"name":"\\u56fe\\u6807","title":"icon","type":"upload","param":""}]'),
(36, '商城首页滚动新闻', '商城首页滚动新闻', 'store_home_roll_news', '[{"name":"\\u6eda\\u52a8\\u6587\\u5b57","title":"info","type":"input","param":""},{"name":"\\u70b9\\u51fb\\u94fe\\u63a5","title":"url","type":"input","param":""}]'),
(37, '小程序首页猜你喜欢banner', '小程序首页猜你喜欢banner', 'routine_lovely', '[{"name":"\\u56fe\\u7247","title":"img","type":"upload","param":""}]'),
(38, '砍价列表图片', '砍价列表顶部图片', 'bargain_banner', '[{"name":"banner","title":"banner","type":"upload","param":""}]'),
(47, '小程序商城首页分类按钮', '小程序商城首页分类按钮', 'routine_home_menus', '[{"name":"\\u5206\\u7c7b\\u540d\\u79f0","title":"name","type":"input","param":""},{"name":"\\u5206\\u7c7b\\u56fe\\u6807","title":"pic","type":"upload","param":""},{"name":"\\u8df3\\u8f6c\\u8def\\u5f84","title":"url","type":"input","param":""},{"name":"\\u5e95\\u90e8\\u83dc\\u5355","title":"show","type":"radio","param":"\\u662f-\\u5426"}]'),
(48, '小程序商城首页banner', '小程序商城首页banner', 'routine_home_banner', '[{"name":"\\u6807\\u9898","title":"name","type":"input","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""},{"name":"\\u56fe\\u7247","title":"pic","type":"upload","param":""}]');

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_group_data`
--

CREATE TABLE IF NOT EXISTS `eb_system_group_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组合数据详情ID',
  `gid` int(11) NOT NULL COMMENT '对应的数据组id',
  `value` text NOT NULL COMMENT '数据组对应的数据值（json数据）',
  `add_time` int(10) NOT NULL COMMENT '添加数据时间',
  `sort` int(11) NOT NULL COMMENT '数据排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1：开启；2：关闭；）',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='组合数据详情表' AUTO_INCREMENT=108 ;

--
-- 转存表中的数据 `eb_system_group_data`
--

INSERT INTO `eb_system_group_data` (`id`, `gid`, `value`, `add_time`, `sort`, `status`) VALUES
(52, 32, '{"name":{"type":"input","value":"\\u6211\\u7684\\u79ef\\u5206"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5aeab2ef8a656.png"},"url":{"type":"input","value":"\\/wap\\/my\\/integral.html"}}', 1513846430, 1, 1),
(53, 32, '{"name":{"type":"input","value":"\\u4f18\\u60e0\\u5238"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5aeab3b43f217.png"},"url":{"type":"input","value":"\\/wap\\/my\\/coupon.html"}}', 1513846448, 1, 1),
(84, 34, '{"title":{"type":"input","value":"banner3"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/11\\/20180817\\/5b7670c42f24b.jpg"}}', 1522135667, 11, 1),
(56, 32, '{"name":{"type":"input","value":"\\u5df2\\u6536\\u85cf\\u5546\\u54c1"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5abc57454d6c7.png"},"url":{"type":"input","value":"\\/wap\\/my\\/collect.html"}}', 1513846605, 1, 1),
(57, 32, '{"name":{"type":"input","value":"\\u5730\\u5740\\u7ba1\\u7406"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5abc574fc0570.png"},"url":{"type":"input","value":"\\/wap\\/my\\/address.html"}}', 1513846618, 1, 1),
(87, 32, '{"name":{"type":"input","value":"\\u6211\\u7684\\u4f59\\u989d"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5aeab2b4c5f99.png"},"url":{"type":"input","value":"\\/wap\\/my\\/balance.html"}}', 1525330614, 1, 1),
(67, 32, '{"name":{"type":"input","value":"\\u804a\\u5929\\u8bb0\\u5f55"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5abc576dba8a2.png"},"url":{"type":"input","value":"\\/wap\\/service\\/service_new.html"}}', 1515570261, 1, 1),
(68, 34, '{"title":{"type":"input","value":"banner1"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2e4b6cf2.jpg"}}', 1515984801, 10, 1),
(69, 34, '{"title":{"type":"input","value":"banner2"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2aaa33d8.jpg"}}', 1515984809, 9, 2),
(71, 35, '{"name":{"type":"input","value":"\\u780d\\u4ef7\\u6d3b\\u52a8"},"url":{"type":"input","value":"\\/wap\\/store\\/cut_list.html"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6c0c5fdaae5.png"}}', 1515985418, 1, 1),
(72, 35, '{"name":{"type":"input","value":"\\u9886\\u5238\\u4e2d\\u5fc3"},"url":{"type":"input","value":"\\/wap\\/store\\/issue_coupon.html"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6c0e995f6d4.png"}}', 1515985426, 1, 1),
(74, 35, '{"name":{"type":"input","value":"\\u6bcf\\u65e5\\u7b7e\\u5230"},"url":{"type":"input","value":"\\/wap\\/my\\/sign_in.html"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6c0c5fe73e6.png"}}', 1515985441, 1, 1),
(80, 36, '{"info":{"type":"input","value":"CRMEB\\u7535\\u5546\\u7cfb\\u7edf V 2.5 \\u5373\\u5c06\\u4e0a\\u7ebf\\uff01"},"url":{"type":"input","value":"#"}}', 1515985907, 1, 1),
(81, 36, '{"info":{"type":"input","value":"CRMEB\\u5546\\u57ce V 2.5 \\u5c0f\\u7a0b\\u5e8f\\u516c\\u4f17\\u53f7\\u6570\\u636e\\u540c\\u6b65\\uff01"},"url":{"type":"input","value":"#"}}', 1515985918, 1, 1),
(107, 37, '{"img":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/8\\/20180817\\/5b768dfd6189a.jpg"}}', 1534496260, 0, 1),
(88, 37, '{"img":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2b0969d7.jpg"}}', 1526699754, 2, 1),
(89, 38, '{"banner":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2436876e.jpg"}}', 1527153599, 1, 1),
(86, 32, '{"name":{"type":"input","value":"\\u8054\\u7cfb\\u5ba2\\u670d"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5aeab3e1d4ecb.png"},"url":{"type":"input","value":"\\/wap\\/index\\/about.html"}}', 1522310836, 1, 1),
(90, 34, '{"title":{"type":"input","value":"1"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2a4445a8.jpg"}}', 1527823558, 1, 2),
(91, 37, '{"img":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2deb5b20.jpg"}}', 1528688012, 1, 1),
(92, 32, '{"name":{"type":"input","value":"\\u63a8\\u5e7f\\u4f63\\u91d1"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b293eaf82.png"},"url":{"type":"input","value":"\\/wap\\/my\\/user_pro.html"}}', 1530688244, 1, 1),
(99, 47, '{"name":{"type":"input","value":"\\u5206\\u7c7b"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180808\\/5b6ab28559200.png"},"url":{"type":"input","value":"\\/pages\\/productSort\\/productSort"},"show":{"type":"radio","value":"\\u662f"}}', 1533721963, 1, 1),
(100, 47, '{"name":{"type":"input","value":"\\u780d\\u4ef7"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180808\\/5b6abd8297e93.png"},"url":{"type":"input","value":"\\/pages\\/cut-list\\/cut-list"},"show":{"type":"radio","value":"\\u5426"}}', 1533722009, 1, 1),
(101, 47, '{"name":{"type":"input","value":"\\u79d2\\u6740"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180808\\/5b6abda83dc2a.png"},"url":{"type":"input","value":"\\/pages\\/miao-list\\/miao-list"},"show":{"type":"radio","value":"\\u5426"}}', 1533722037, 1, 1),
(102, 47, '{"name":{"type":"input","value":"\\u62fc\\u56e2"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180808\\/5b6abdc50d2d1.png"},"url":{"type":"input","value":"\\/pages\\/pink-list\\/index"},"show":{"type":"radio","value":"\\u5426"}}', 1533722063, 1, 1),
(103, 48, '{"name":{"type":"input","value":"banenr1"},"url":{"type":"input","value":"\\/pages\\/miao-list\\/miao-list"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2d35dc37.jpg"}}', 1533722245, 1, 1),
(104, 48, '{"name":{"type":"input","value":"banenr2"},"url":{"type":"input","value":"\\/pages\\/pink-list\\/index"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/11\\/20180817\\/5b7670c42f24b.jpg"}}', 1533722286, 10, 1),
(105, 47, '{"name":{"type":"input","value":"\\u54a8\\u8be2"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6bedbcb2f17.png"},"url":{"type":"input","value":"\\/pages\\/new-list\\/new-list"},"show":{"type":"radio","value":"\\u5426"}}', 1533797064, 1, 1),
(106, 32, '{"name":{"type":"input","value":"\\u6211\\u7684\\u780d\\u4ef7"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6c0c5fdaae5.png"},"url":{"type":"input","value":"\\/wap\\/my\\/user_cut.html"}}', 1533889033, 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_log`
--

CREATE TABLE IF NOT EXISTS `eb_system_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员操作记录ID',
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员id',
  `admin_name` varchar(64) NOT NULL COMMENT '管理员姓名',
  `path` varchar(128) NOT NULL COMMENT '链接',
  `page` varchar(64) NOT NULL COMMENT '行为',
  `method` varchar(12) NOT NULL COMMENT '访问类型',
  `ip` varchar(16) NOT NULL COMMENT '登录IP',
  `type` varchar(32) NOT NULL COMMENT '类型',
  `add_time` int(10) unsigned NOT NULL COMMENT '操作时间',
  `merchant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商户id',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='管理员操作记录表' AUTO_INCREMENT=60 ;

--
-- 转存表中的数据 `eb_system_log`
--

INSERT INTO `eb_system_log` (`id`, `admin_id`, `admin_name`, `path`, `page`, `method`, `ip`, `type`, `add_time`, `merchant_id`) VALUES
(1, 1, 'admin', 'admin/setting.systemmenus/index/cate/12', '权限规则', 'GET', '127.0.0.1', 'system', 1534816931, 0),
(2, 1, 'admin', 'admin/setting.systemmenus/index/pid/286', '未知', 'GET', '127.0.0.1', 'system', 1534816936, 0),
(3, 1, 'admin', 'admin/setting.systemmenus/index/pid/272', '未知', 'GET', '127.0.0.1', 'system', 1534816938, 0),
(4, 1, 'admin', 'admin/setting.systemmenus/index/pid/333', '未知', 'GET', '127.0.0.1', 'system', 1534816940, 0),
(5, 1, 'admin', 'admin/setting.systemmenus/index/pid/272', '未知', 'GET', '127.0.0.1', 'system', 1534816941, 0),
(6, 1, 'admin', 'admin/setting.systemmenus/delete/id/333', '未知', 'GET', '127.0.0.1', 'system', 1534816943, 0),
(7, 1, 'admin', 'admin/setting.systemmenus/delete/id/238', '未知', 'GET', '127.0.0.1', 'system', 1534816946, 0),
(8, 1, 'admin', 'admin/setting.systemmenus/index/pid/286', '未知', 'GET', '127.0.0.1', 'system', 1534816947, 0),
(9, 1, 'admin', 'admin/setting.systemmenus/delete/id/272', '未知', 'GET', '127.0.0.1', 'system', 1534816952, 0),
(10, 1, 'admin', 'admin/setting.systemmenus/index/pid/271', '未知', 'GET', '127.0.0.1', 'system', 1534816955, 0),
(11, 1, 'admin', 'admin/setting.systemmenus/index/pid/254', '未知', 'GET', '127.0.0.1', 'system', 1534816956, 0),
(12, 1, 'admin', 'admin/setting.systemmenus/delete/id/366', '未知', 'GET', '127.0.0.1', 'system', 1534816959, 0),
(13, 1, 'admin', 'admin/setting.systemmenus/index/pid/366', '未知', 'GET', '127.0.0.1', 'system', 1534816960, 0),
(14, 1, 'admin', 'admin/setting.systemmenus/delete/id/368', '未知', 'GET', '127.0.0.1', 'system', 1534816963, 0),
(15, 1, 'admin', 'admin/setting.systemmenus/delete/id/367', '未知', 'GET', '127.0.0.1', 'system', 1534816965, 0),
(16, 1, 'admin', 'admin/setting.systemmenus/index/pid/254', '未知', 'GET', '127.0.0.1', 'system', 1534816967, 0),
(17, 1, 'admin', 'admin/setting.systemmenus/index/pid/271', '未知', 'GET', '127.0.0.1', 'system', 1534816967, 0),
(18, 1, 'admin', 'admin/setting.systemmenus/index/pid/254', '未知', 'GET', '127.0.0.1', 'system', 1534816968, 0),
(19, 1, 'admin', 'admin/setting.systemmenus/delete/id/366', '未知', 'GET', '127.0.0.1', 'system', 1534816970, 0),
(20, 1, 'admin', 'admin/setting.systemmenus/index/pid/271', '未知', 'GET', '127.0.0.1', 'system', 1534816972, 0),
(21, 1, 'admin', 'admin/setting.systemmenus/delete/id/254', '未知', 'GET', '127.0.0.1', 'system', 1534816973, 0),
(22, 1, 'admin', 'admin/setting.systemmenus/index/pid/286', '未知', 'GET', '127.0.0.1', 'system', 1534816975, 0),
(23, 1, 'admin', 'admin/setting.systemmenus/delete/id/271', '未知', 'GET', '127.0.0.1', 'system', 1534816981, 0),
(24, 1, 'admin', 'admin/setting.systemconfig/index/type/3/tab_id/11', '积分配置', 'GET', '127.0.0.1', 'system', 1534817204, 0),
(25, 1, 'admin', 'admin/ump.userpoint/index/', '积分日志', 'GET', '127.0.0.1', 'system', 1534817205, 0),
(26, 1, 'admin', 'admin/ump.userpoint/getuserpointbadgelist/', '未知', 'GET', '127.0.0.1', 'system', 1534817205, 0),
(27, 1, 'admin', 'admin/ump.userpoint/getponitlist/', '未知', 'GET', '127.0.0.1', 'system', 1534817205, 0),
(28, 1, 'admin', 'admin/ump.storeseckill/index/', '限时秒杀', 'GET', '127.0.0.1', 'system', 1534817206, 0),
(29, 1, 'admin', 'admin/ump.storeseckill/get_seckill_list/', '未知', 'GET', '127.0.0.1', 'system', 1534817206, 0),
(30, 1, 'admin', 'admin/ump.storeseckill/index/', '限时秒杀', 'GET', '127.0.0.1', 'system', 1534817234, 0),
(31, 1, 'admin', 'admin/ump.storeseckill/get_seckill_list/', '未知', 'GET', '127.0.0.1', 'system', 1534817234, 0),
(32, 1, 'admin', 'admin/setting.systemconfigtab/index/', '配置分类展示页', 'GET', '127.0.0.1', 'system', 1534817606, 0),
(33, 1, 'admin', 'admin/setting.systemgroup/index/', '组合数据', 'GET', '127.0.0.1', 'system', 1534817611, 0),
(34, 1, 'admin', 'admin/setting.systemgroup/index/', '组合数据', 'GET', '127.0.0.1', 'system', 1534817632, 0),
(35, 1, 'admin', 'admin/setting.systemgroup/index/', '组合数据', 'GET', '127.0.0.1', 'system', 1534817643, 0),
(36, 1, 'admin', 'admin/setting.systemgroup/index/', '组合数据', 'GET', '127.0.0.1', 'system', 1534818052, 0),
(37, 1, 'admin', 'admin/setting.systemgroupdata/index/gid/35', '未知', 'GET', '127.0.0.1', 'system', 1534818055, 0),
(38, 1, 'admin', 'admin/setting.systemgroupdata/delete/id/73', '未知', 'GET', '127.0.0.1', 'system', 1534818059, 0),
(39, 1, 'admin', 'admin/setting.systemgroupdata/index/gid/35', '未知', 'GET', '127.0.0.1', 'system', 1534818060, 0),
(40, 1, 'admin', 'admin/store.storeproduct/index/type/1', '出售中商品', 'GET', '127.0.0.1', 'system', 1534818142, 0),
(41, 1, 'admin', 'admin/store.storeproduct/create/', '未知', 'GET', '127.0.0.1', 'system', 1534818144, 0),
(42, 1, 'admin', 'admin/store.storeproduct/create/', '未知', 'GET', '127.0.0.1', 'system', 1534818200, 0),
(43, 1, 'admin', 'admin/store.storeproduct/create/', '未知', 'GET', '127.0.0.1', 'system', 1534818356, 0),
(44, 1, 'admin', 'admin/widget.images/index/fodder/image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818370, 0),
(45, 1, 'admin', 'admin/widget.images/index/fodder/image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818370, 0),
(46, 1, 'admin', 'admin/widget.images/index/pid/8/fodder/image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818372, 0),
(47, 1, 'admin', 'admin/widget.images/index/pid/0/fodder/image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818372, 0),
(48, 1, 'admin', 'admin/widget.images/upload/pid/0', '上传图片', 'POST', '127.0.0.1', 'system', 1534818380, 0),
(49, 1, 'admin', 'admin/widget.images/index/pid/0/fodder/image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818382, 0),
(50, 1, 'admin', 'admin/widget.images/index/fodder/slider_image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818387, 0),
(51, 1, 'admin', 'admin/widget.images/index/fodder/slider_image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818387, 0),
(52, 1, 'admin', 'admin/widget.images/index/fodder/slider_image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818389, 0),
(53, 1, 'admin', 'admin/widget.images/index/fodder/slider_image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818389, 0),
(54, 1, 'admin', 'admin/widget.images/index/fodder/slider_image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818391, 0),
(55, 1, 'admin', 'admin/widget.images/index/fodder/slider_image', '附件管理', 'GET', '127.0.0.1', 'system', 1534818391, 0),
(56, 1, 'admin', 'admin/store.storeproduct/save/', '未知', 'POST', '127.0.0.1', 'system', 1534818410, 0),
(57, 1, 'admin', 'admin/store.storeproduct/index/type/1', '出售中商品', 'GET', '127.0.0.1', 'system', 1534818410, 0),
(58, 1, 'admin', 'admin/store.storeproduct/index/type/1', '出售中商品', 'GET', '127.0.0.1', 'system', 1534818593, 0),
(59, 1, 'admin', 'admin/store.storeproduct/index/type/1', '出售中商品', 'GET', '127.0.0.1', 'system', 1534819393, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_menus`
--

CREATE TABLE IF NOT EXISTS `eb_system_menus` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `icon` varchar(16) NOT NULL COMMENT '图标',
  `menu_name` varchar(32) NOT NULL DEFAULT '' COMMENT '按钮名',
  `module` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '模块名',
  `controller` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '控制器',
  `action` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '方法名',
  `params` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '[]' COMMENT '参数',
  `sort` tinyint(3) NOT NULL DEFAULT '1' COMMENT '排序',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `access` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '子管理员是否可用',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE,
  KEY `access` (`access`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='菜单表' AUTO_INCREMENT=377 ;

--
-- 转存表中的数据 `eb_system_menus`
--

INSERT INTO `eb_system_menus` (`id`, `pid`, `icon`, `menu_name`, `module`, `controller`, `action`, `params`, `sort`, `is_show`, `access`) VALUES
(1, 289, '', '系统设置', 'admin', 'setting.systemConfig', 'index', '[]', 90, 1, 1),
(2, 153, '', '权限规则', 'admin', 'setting.systemMenus', 'index', '{"cate":"12"}', 7, 1, 1),
(4, 153, '', '管理员列表', 'admin', 'setting.systemAdmin', 'index', '[]', 9, 1, 1),
(6, 1, '', '系统配置', 'admin', 'setting.systemConfig', 'index', '{"tab_id":"1"}', 1, 1, 1),
(7, 1, '', '配置分类', 'admin', 'setting.systemConfigTab', 'index', '[]', 1, 1, 1),
(8, 153, '', '身份管理', 'admin', 'setting.systemRole', 'index', '[]', 10, 1, 1),
(9, 1, '', '组合数据', 'admin', 'setting.systemGroup', 'index', '[]', 1, 1, 1),
(11, 0, 'wechat', '公众号', 'admin', 'wechat.wechat', 'index', '[]', 91, 1, 1),
(12, 354, '', '微信关注回复', 'admin', 'wechat.reply', 'index', '{"key":"subscribe","title":"\\u7f16\\u8f91\\u65e0\\u914d\\u7f6e\\u9ed8\\u8ba4\\u56de\\u590d"}', 86, 1, 1),
(17, 360, '', '微信菜单', 'admin', 'wechat.menus', 'index', '[]', 95, 1, 1),
(286, 0, 'paper-plane', '营销', 'admin', '', '', '[]', 105, 1, 1),
(19, 11, '', '图文管理', 'admin', 'wechat.wechatNewsCategory', 'index', '[]', 60, 1, 1),
(21, 0, 'magic', '维护', 'admin', 'system.system', '', '[]', 1, 1, 1),
(23, 0, 'laptop', '商品', 'admin', 'store.store', 'index', '[]', 110, 1, 1),
(24, 23, '', '商品管理', 'admin', 'store.storeProduct', 'index', '[]', 100, 1, 1),
(25, 23, '', '商品分类', 'admin', 'store.storeCategory', 'index', '[]', 1, 1, 1),
(26, 285, '', '订单管理', 'admin', 'order.storeOrder', 'index', '[]', 1, 1, 1),
(30, 354, '', '关键字回复', 'admin', 'wechat.reply', 'keyword', '[]', 85, 1, 1),
(31, 354, '', '无效关键词回复', 'admin', 'wechat.reply', 'index', '{"key":"default","title":"\\u7f16\\u8f91\\u65e0\\u6548\\u5173\\u952e\\u5b57\\u9ed8\\u8ba4\\u56de\\u590d"}', 84, 1, 1),
(33, 284, '', '附加权限', 'admin', 'article.articleCategory', '', '[]', 0, 0, 1),
(34, 33, '', '添加文章分类', 'admin', 'article.articleCategory', 'create', '[]', 0, 0, 1),
(35, 33, '', '编辑文章分类', 'admin', 'article.articleCategory', 'edit', '[]', 0, 0, 1),
(36, 33, '', '删除文章分类', 'admin', 'article.articleCategory', 'delete', '[]', 0, 0, 1),
(37, 31, '', '附加权限', 'admin', 'wechat.reply', '', '[]', 0, 0, 1),
(38, 283, '', '附加权限', 'admin', 'article.article', '', '[]', 0, 0, 1),
(39, 38, '', '添加文章', 'admin', 'article. article', 'create', '[]', 0, 0, 1),
(40, 38, '', '编辑文章', 'admin', 'article. article', 'add_new', '[]', 0, 0, 1),
(41, 38, '', '删除文章', 'admin', 'article. article', 'delete', '[]', 0, 0, 1),
(42, 19, '', '附加权限', 'admin', 'wechat.wechatNewsCategory', '', '[]', 0, 0, 1),
(43, 42, '', '添加图文消息', 'admin', 'wechat.wechatNewsCategory', 'create', '[]', 0, 0, 1),
(44, 42, '', '编辑图文消息', 'admin', 'wechat.wechatNewsCategory', 'edit', '[]', 0, 0, 1),
(45, 42, '', '删除图文消息', 'admin', 'wechat.wechatNewsCategory', 'delete', '[]', 0, 0, 1),
(46, 7, '', '配置分类附加权限', 'admin', 'setting.systemConfigTab', '', '[]', 0, 0, 1),
(47, 46, '', '添加配置分类', 'admin', 'setting.systemConfigTab', 'create', '[]', 0, 0, 1),
(48, 46, '', '添加配置', 'admin', 'setting.systemConfig', 'create', '[]', 0, 0, 1),
(49, 46, '', '编辑配置分类', 'admin', 'setting.systemConfigTab', 'edit', '[]', 0, 0, 1),
(50, 46, '', '删除配置分类', 'admin', 'setting.systemConfigTab', 'delete', '[]', 0, 0, 1),
(51, 46, '', '查看子字段', 'admin', 'system.systemConfigTab', 'sonConfigTab', '[]', 0, 0, 1),
(52, 9, '', '组合数据附加权限', 'admin', 'system.systemGroup', '', '[]', 0, 0, 1),
(53, 52, '', '添加数据', 'admin', 'system.systemGroupData', 'create', '[]', 0, 0, 1),
(54, 52, '', '编辑数据', 'admin', 'system.systemGroupData', 'edit', '[]', 0, 0, 1),
(55, 52, '', '删除数据', 'admin', 'system.systemGroupData', 'delete', '[]', 0, 0, 1),
(56, 52, '', '数据列表', 'admin', 'system.systemGroupData', 'index', '[]', 0, 0, 1),
(57, 52, '', '添加数据组', 'admin', 'system.systemGroup', 'create', '[]', 0, 0, 1),
(58, 52, '', '删除数据组', 'admin', 'system.systemGroup', 'delete', '[]', 0, 0, 1),
(59, 4, '', '管理员列表附加权限', 'admin', 'system.systemAdmin', '', '[]', 0, 0, 1),
(60, 59, '', '添加管理员', 'admin', 'system.systemAdmin', 'create', '[]', 0, 0, 1),
(61, 59, '', '编辑管理员', 'admin', 'system.systemAdmin', 'edit', '[]', 0, 0, 1),
(62, 59, '', '删除管理员', 'admin', 'system.systemAdmin', 'delete', '[]', 0, 0, 1),
(63, 8, '', '身份管理附加权限', 'admin', 'system.systemRole', '', '[]', 0, 0, 1),
(64, 63, '', '添加身份', 'admin', 'system.systemRole', 'create', '[]', 0, 0, 1),
(65, 63, '', '修改身份', 'admin', 'system.systemRole', 'edit', '[]', 0, 0, 1),
(66, 63, '', '删除身份', 'admin', 'system.systemRole', 'delete', '[]', 0, 0, 1),
(67, 8, '', '身份管理展示页', 'admin', 'system.systemRole', 'index', '[]', 0, 0, 1),
(68, 4, '', '管理员列表展示页', 'admin', 'system.systemAdmin', 'index', '[]', 0, 0, 1),
(69, 7, '', '配置分类展示页', 'admin', 'setting.systemConfigTab', 'index', '[]', 0, 0, 1),
(70, 9, '', '组合数据展示页', 'admin', 'system.systemGroup', 'index', '[]', 0, 0, 1),
(71, 284, '', '文章分类管理展示页', 'admin', 'article.articleCategory', 'index', '[]', 0, 0, 1),
(72, 283, '', '文章管理展示页', 'admin', 'article.article', 'index', '[]', 0, 0, 1),
(73, 19, '', '图文消息展示页', 'admin', 'wechat.wechatNewsCategory', 'index', '[]', 0, 0, 1),
(74, 2, '', '菜单管理附加权限', 'admin', 'system.systemMenus', '', '[]', 0, 0, 1),
(75, 74, '', '添加菜单', 'admin', 'system.systemMenus', 'create', '[]', 0, 0, 1),
(76, 74, '', '编辑菜单', 'admin', 'system.systemMenus', 'edit', '[]', 0, 0, 1),
(77, 74, '', '删除菜单', 'admin', 'system.systemMenus', 'delete', '[]', 0, 0, 1),
(78, 2, '', '菜单管理展示页', 'admin', 'system.systemMenus', 'index', '[]', 0, 0, 1),
(352, 148, '', '优惠券配置', 'admin', 'setting.systemConfig', 'index', '{"type":"3","tab_id":"12"}', 0, 1, 1),
(80, 0, 'leanpub', '内容', 'admin', 'wechat.wechatNews', 'index', '[]', 90, 1, 1),
(82, 151, '', '微信用户管理', 'admin', 'user', 'list', '[]', 5, 1, 1),
(84, 82, '', '用户标签', 'admin', 'wechat.wechatUser', 'tag', '[]', 0, 1, 1),
(89, 30, '', '关键字回复附加权限', 'admin', 'wechat.reply', '', '[]', 0, 0, 1),
(90, 89, '', '添加关键字', 'admin', 'wechat.reply', 'add_keyword', '[]', 0, 0, 1),
(91, 89, '', '修改关键字', 'admin', 'wechat.reply', 'info_keyword', '[]', 0, 0, 1),
(92, 89, '', '删除关键字', 'admin', 'wechat.reply', 'delete', '[]', 0, 0, 1),
(93, 30, '', '关键字回复展示页', 'admin', 'wechat.reply', 'keyword', '[]', 0, 0, 1),
(94, 31, '', '无效关键词回复展示页', 'admin', 'wechat.reply', 'index', '[]', 0, 0, 1),
(95, 31, '', '无效关键词回复附加权限', 'admin', 'wechat.reply', '', '[]', 0, 0, 1),
(96, 95, '', '无效关键词回复提交按钮', 'admin', 'wechat.reply', 'save', '{"key":"default","title":"编辑无效关键字默认回复"}', 0, 0, 1),
(97, 12, '', '微信关注回复展示页', 'admin', 'wechat.reply', 'index', '[]', 0, 0, 1),
(98, 12, '', '微信关注回复附加权限', 'admin', 'wechat.reply', '', '[]', 0, 0, 1),
(99, 98, '', '微信关注回复提交按钮', 'admin', 'wechat.reply', 'save', '{"key":"subscribe","title":"编辑无配置默认回复"}', 0, 0, 1),
(100, 74, '', '添加提交菜单', 'admin', 'system.systemMenus', 'save', '[]', 0, 0, 1),
(101, 74, '', '编辑提交菜单', 'admin', 'system.systemMenus', 'update', '[]', 0, 1, 1),
(102, 59, '', '提交添加管理员', 'admin', 'system.systemAdmin', 'save', '[]', 0, 0, 1),
(103, 59, '', '提交修改管理员', 'admin', 'system.systemAdmin', 'update', '[]', 0, 0, 1),
(104, 63, '', '提交添加身份', 'admin', 'system.systemRole', 'save', '[]', 0, 0, 1),
(105, 63, '', '提交修改身份', 'admin', 'system.systemRole', 'update', '[]', 0, 0, 1),
(106, 46, '', '提交添加配置分类', 'admin', 'setting.systemConfigTab', 'save', '[]', 0, 0, 1),
(107, 46, '', '提交修改配置分类', 'admin', 'setting.systemConfigTab', 'update', '[]', 0, 0, 1),
(108, 46, '', '提交添加配置列表', 'admin', 'setting.systemConfig', 'save', '[]', 0, 0, 1),
(109, 52, '', '提交添加数据组', 'admin', 'system.systemGroup', 'save', '[]', 0, 0, 1),
(110, 52, '', '提交修改数据组', 'admin', 'system.systemGroup', 'update', '[]', 0, 0, 1),
(111, 52, '', '提交添加数据', 'admin', 'system.systemGroupData', 'save', '[]', 0, 0, 1),
(112, 52, '', '提交修改数据', 'admin', 'system.systemGroupData', 'update', '[]', 0, 0, 1),
(113, 33, '', '提交添加文章分类', 'admin', 'article.articleCategory', 'save', '[]', 0, 0, 1),
(114, 33, '', '提交添加文章分类', 'admin', 'article.articleCategory', 'update', '[]', 0, 0, 1),
(115, 42, '', '提交添加图文消息', 'admin', 'wechat.wechatNewsCategory', 'save', '[]', 0, 0, 1),
(116, 42, '', '提交编辑图文消息', 'admin', 'wechat.wechatNewsCategory', 'update', '[]', 0, 0, 1),
(117, 6, '', '配置列表附加权限', 'admin', 'setting.systemConfig', '', '[]', 0, 0, 1),
(118, 6, '', '配置列表展示页', 'admin', 'setting.systemConfig', 'index', '[]', 0, 0, 1),
(119, 117, '', '提交保存配置列表', 'admin', 'setting.systemConfig', 'save_basics', '[]', 0, 0, 1),
(123, 89, '', '提交添加关键字', 'admin', 'wechat.reply', 'save_keyword', '{"dis":"1"}', 0, 0, 1),
(124, 89, '', '提交修改关键字', 'admin', 'wechat.reply', 'save_keyword', '{"dis":"2"}', 0, 0, 1),
(126, 17, '', '微信菜单展示页', 'admin', 'wechat.menus', 'index', '[]', 0, 0, 1),
(127, 17, '', '微信菜单附加权限', 'admin', 'wechat.menus', '', '[]', 0, 0, 1),
(128, 127, '', '提交微信菜单按钮', 'admin', 'wechat.menus', 'save', '{"dis":"1"}', 0, 0, 1),
(129, 82, '', '用户行为纪录', 'admin', 'wechat.wechatMessage', 'index', '[]', 0, 1, 1),
(130, 21, '', '系统日志', 'admin', 'system.systemLog', 'index', '[]', 5, 1, 1),
(131, 130, '', '管理员操作记录展示页', 'admin', 'system.systemLog', 'index', '[]', 0, 0, 1),
(132, 129, '', '微信用户行为纪录展示页', 'admin', 'wechat.wechatMessage', 'index', '[]', 0, 0, 1),
(133, 82, '', '微信用户', 'admin', 'wechat.wechatUser', 'index', '[]', 1, 1, 1),
(134, 133, '', '微信用户展示页', 'admin', 'wechat.wechatUser', 'index', '[]', 0, 0, 1),
(241, 273, '', '限时秒杀', 'admin', 'ump.storeSeckill', 'index', '[]', 0, 1, 1),
(137, 135, '', '添加通知模板', 'admin', 'system.systemNotice', 'create', '[]', 0, 0, 1),
(138, 135, '', '编辑通知模板', 'admin', 'system.systemNotice', 'edit', '[]', 0, 0, 1),
(139, 135, '', '删除辑通知模板', 'admin', 'system.systemNotice', 'delete', '[]', 0, 0, 1),
(140, 135, '', '提交编辑辑通知模板', 'admin', 'system.systemNotice', 'update', '[]', 0, 0, 1),
(141, 135, '', '提交添加辑通知模板', 'admin', 'system.systemNotice', 'save', '[]', 0, 0, 1),
(142, 25, '', '产品分类展示页', 'admin', 'store.storeCategory', 'index', '[]', 0, 0, 1),
(143, 25, '', '产品分类附加权限', 'admin', 'store.storeCategory', '', '[]', 0, 0, 1),
(144, 117, '', '获取配置列表上传文件的名称', 'admin', 'setting.systemConfig', 'getimagename', '[]', 0, 0, 1),
(145, 117, '', '配置列表上传文件', 'admin', 'setting.systemConfig', 'view_upload', '[]', 0, 0, 1),
(146, 24, '', '产品管理展示页', 'admin', 'store.storeProduct', 'index', '[]', 0, 0, 1),
(147, 24, '', '产品管理附加权限', 'admin', 'store.storeProduct', '', '[]', 0, 0, 1),
(148, 286, '', '优惠券', '', '', '', '[]', 10, 1, 1),
(149, 148, '', '优惠券制作', 'admin', 'ump.storeCoupon', 'index', '[]', 5, 1, 1),
(150, 148, '', '会员领取记录', 'admin', 'ump.storeCouponUser', 'index', '[]', 1, 1, 1),
(151, 0, 'user', '会员', 'admin', 'user.userList', 'list', '[]', 107, 1, 1),
(153, 289, '', '管理权限', 'admin', 'setting.systemAdmin', '', '[]', 100, 1, 1),
(155, 154, '', '商户产品展示页', 'admin', 'store.storeMerchant', 'index', '[]', 0, 0, 1),
(156, 154, '', '商户产品附加权限', 'admin', 'store.storeMerchant', '', '[]', 0, 0, 1),
(158, 157, '', '商户文章管理展示页', 'admin', 'wechat.wechatNews', 'merchantIndex', '[]', 0, 0, 1),
(159, 157, '', '商户文章管理附加权限', 'admin', 'wechat.wechatNews', '', '[]', 0, 0, 1),
(170, 290, '', '评论管理', 'admin', 'store.storeProductReply', '', '[]', 0, 1, 1),
(173, 21, '', '文件校验', 'admin', 'system.system_file', 'index', '[]', 1, 1, 1),
(174, 360, '', '微信模板消息', 'admin', 'wechat.wechatTemplate', 'index', '[]', 1, 1, 1),
(175, 11, '', '客服管理', 'admin', 'wechat.storeService', 'index', '[]', 70, 1, 1),
(176, 151, '', '站内通知', 'admin', 'user.user_notice', 'index', '[]', 8, 1, 1),
(177, 151, '', '会员管理', 'admin', 'user.user', 'index', '[]', 10, 1, 1),
(179, 307, '', '充值记录', 'admin', 'finance.userRecharge', 'index', '[]', 1, 1, 1),
(190, 26, '', '订单管理展示页', 'admin', 'store.storeOrder', 'index', '[]', 0, 0, 1),
(191, 26, '', '订单管理附加权限', 'admin', 'store.storeOrder', '', '[]', 0, 0, 1),
(192, 191, '', '订单管理去发货', 'admin', 'store.storeOrder', 'deliver_goods', '[]', 0, 0, 1),
(193, 191, '', '订单管理备注', 'admin', 'store.storeOrder', 'remark', '[]', 0, 0, 1),
(194, 191, '', '订单管理去送货', 'admin', 'store.storeOrder', 'delivery', '[]', 0, 0, 1),
(195, 191, '', '订单管理已收货', 'admin', 'store.storeOrder', 'take_delivery', '[]', 0, 0, 1),
(196, 191, '', '订单管理退款', 'admin', 'store.storeOrder', 'refund_y', '[]', 0, 0, 1),
(197, 191, '', '订单管理修改订单', 'admin', 'store.storeOrder', 'edit', '[]', 0, 0, 1),
(198, 191, '', '订单管理修改订单提交', 'admin', 'store.storeOrder', 'update', '[]', 0, 0, 1),
(199, 191, '', '订单管理退积分', 'admin', 'store.storeOrder', 'integral_back', '[]', 0, 0, 1),
(200, 191, '', '订单管理退积分提交', 'admin', 'store.storeOrder', 'updateIntegralBack', '[]', 0, 0, 1),
(201, 191, '', '订单管理立即支付', 'admin', 'store.storeOrder', 'offline', '[]', 0, 0, 1),
(202, 191, '', '订单管理退款原因', 'admin', 'store.storeOrder', 'refund_n', '[]', 0, 0, 1),
(203, 191, '', '订单管理退款原因提交', 'admin', 'store.storeOrder', 'updateRefundN', '[]', 0, 0, 1),
(204, 191, '', '订单管理修改配送信息', 'admin', 'store.storeOrder', 'distribution', '[]', 0, 0, 1),
(205, 191, '', '订单管理修改配送信息提交', 'admin', 'store.storeOrder', 'updateDistribution', '[]', 0, 0, 1),
(206, 191, '', '订单管理退款提交', 'admin', 'store.storeOrder', 'updateRefundY', '[]', 0, 0, 1),
(207, 191, '', '订单管理去发货提交', 'admin', 'store.storeOrder', 'updateDeliveryGoods', '[]', 0, 0, 1),
(208, 191, '', '订单管理去送货提交', 'admin', 'store.storeOrder', 'updateDelivery', '[]', 0, 0, 1),
(209, 175, '', '客服管理展示页', 'admin', 'store.storeService', 'index', '[]', 0, 0, 1),
(210, 175, '', '客服管理附加权限', 'admin', 'store.storeService', '', '[]', 0, 0, 1),
(211, 210, '', '客服管理添加', 'admin', 'store.storeService', 'create', '[]', 0, 0, 1),
(212, 210, '', '客服管理添加提交', 'admin', 'store.storeService', 'save', '[]', 0, 0, 1),
(213, 210, '', '客服管理编辑', 'admin', 'store.storeService', 'edit', '[]', 0, 0, 1),
(214, 210, '', '客服管理编辑提交', 'admin', 'store.storeService', 'update', '[]', 0, 0, 1),
(215, 210, '', '客服管理删除', 'admin', 'store.storeService', 'delete', '[]', 0, 0, 1),
(216, 179, '', '用户充值记录展示页', 'admin', 'user.userRecharge', 'index', '[]', 0, 0, 1),
(217, 179, '', '用户充值记录附加权限', 'admin', 'user.userRecharge', '', '[]', 0, 0, 1),
(218, 217, '', '用户充值记录退款', 'admin', 'user.userRecharge', 'edit', '[]', 0, 0, 1),
(219, 217, '', '用户充值记录退款提交', 'admin', 'user.userRecharge', 'updaterefundy', '[]', 0, 0, 1),
(220, 180, '', '预售卡管理批量修改预售卡金额', 'admin', 'presell.presellCard', 'batch_price', '[]', 0, 0, 1),
(221, 180, '', '预售卡管理批量修改预售卡金额提交', 'admin', 'presell.presellCard', 'savebatch', '[]', 0, 0, 1),
(222, 210, '', '客服管理聊天记录查询', 'admin', 'store.storeService', 'chat_user', '[]', 0, 0, 1),
(223, 210, '', '客服管理聊天记录查询详情', 'admin', 'store.storeService', 'chat_list', '[]', 0, 0, 1),
(224, 170, '', '评论管理展示页', 'admin', 'store.storeProductReply', 'index', '[]', 0, 0, 1),
(225, 170, '', '评论管理附加权限', 'admin', 'store.storeProductReply', '', '[]', 0, 0, 1),
(226, 225, '', '评论管理回复评论', 'admin', 'store.storeProductReply', 'set_reply', '[]', 0, 0, 1),
(227, 225, '', '评论管理修改回复评论', 'admin', 'store.storeProductReply', 'edit_reply', '[]', 0, 0, 1),
(228, 225, '', '评论管理删除评论', 'admin', 'store.storeProductReply', 'delete', '[]', 0, 0, 1),
(229, 149, '', '优惠券管理展示页', 'admin', 'store.storeCoupon', 'index', '[]', 0, 0, 1),
(230, 149, '', '优惠券管理附加权限', 'admin', 'store.storeCoupon', '', '[]', 0, 0, 1),
(231, 230, '', '优惠券管理添加', 'admin', 'store.storeCoupon', 'create', '[]', 0, 0, 1),
(232, 230, '', '优惠券管理添加提交', 'admin', 'store.storeCoupon', 'save', '[]', 0, 0, 1),
(233, 230, '', '优惠券管理删除', 'admin', 'store.storeCoupon', 'delete', '[]', 0, 0, 1),
(234, 230, '', '优惠券管理立即失效', 'admin', 'store.storeCoupon', 'status', '[]', 0, 0, 1),
(235, 148, '', '已发布管理', 'admin', 'ump.storeCouponIssue', 'index', '[]', 3, 1, 1),
(236, 82, '', '用户分组', 'admin', 'wechat.wechatUser', 'group', '[]', 0, 1, 1),
(237, 21, '', '刷新缓存', 'admin', 'system.clear', 'index', '[]', 0, 1, 1),
(239, 306, '', '提现申请', 'admin', 'finance.user_extract', 'index', '[]', 0, 1, 1),
(351, 349, '', '积分日志', 'admin', 'ump.userPoint', 'index', '[]', 0, 1, 1),
(244, 294, '', '财务报表', 'admin', 'record.storeStatistics', 'index', '[]', 0, 1, 1),
(245, 293, '', '商品统计', 'admin', 'store.storeProduct', 'statistics', '[]', 0, 0, 1),
(246, 295, '', '用户统计', 'admin', 'user.user', 'user_analysis', '[]', 0, 1, 1),
(247, 153, '', '个人资料', 'admin', 'setting.systemAdmin', 'admininfo', '[]', 0, 0, 1),
(248, 247, '', '个人资料附加权限', 'admin', 'system.systemAdmin', '', '[]', 0, 0, 1),
(249, 248, '', '个人资料提交保存', 'admin', 'system.systemAdmin', 'setAdminInfo', '[]', 0, 0, 1),
(250, 247, '', '个人资料展示页', 'admin', 'system.systemAdmin', 'admininfo', '[]', 0, 0, 1),
(251, 293, '', '订单统计', 'admin', 'order.storeOrder', 'orderchart', '[]', 0, 1, 1),
(252, 21, '', '在线更新', 'admin', 'system.system_upgradeclient', 'index', '[]', 0, 1, 1),
(253, 259, '', '添加更新包', 'admin', 'server.server_upgrade', 'add_upgrade', '[]', 0, 0, 1),
(255, 1, '', '后台通知', 'admin', 'setting.systemNotice', 'index', '[]', 0, 1, 1),
(256, 0, 'cloud', '服务器端', 'admin', 'upgrade', 'index', '[]', -100, 0, 1),
(257, 258, '', 'IP白名单', 'admin', 'server.server_upgrade', 'ip_hteb_list', '[]', 0, 0, 1),
(258, 256, '', '站点管理', 'admin', 'server.server_upgrade', 'index', '[]', 0, 1, 1),
(259, 256, '', '版本管理', 'admin', 'server.server_upgrade', 'versionlist', '[]', 0, 1, 1),
(260, 256, '', '升级日志', 'admin', 'server.server_upgrade', 'upgrade_log', '[]', 0, 1, 1),
(261, 147, '', '编辑产品', 'admin', 'store.storeproduct', 'edit', '[]', 0, 0, 1),
(262, 147, '', '添加产品', 'admin', 'store.storeproduct', 'create', '[]', 0, 0, 1),
(263, 147, '', '编辑产品详情', 'admin', 'store.storeproduct', 'edit_content', '[]', 0, 0, 1),
(264, 147, '', '开启秒杀', 'admin', 'store.storeproduct', 'seckill', '[]', 0, 0, 1),
(265, 147, '', '开启秒杀', 'admin', 'store.store_product', 'bargain', '[]', 0, 0, 1),
(266, 147, '', '产品编辑属性', 'admin', 'store.storeproduct', 'attr', '[]', 0, 0, 1),
(267, 360, '', '公众号接口配置', 'admin', 'setting.systemConfig', 'index', '{"type":"1","tab_id":"2"}', 100, 1, 1),
(269, 0, 'cubes', '小程序', 'admin', 'setting.system', '', '[]', 92, 1, 1),
(270, 269, '', '小程序配置', 'admin', 'setting.systemConfig', 'index_alone', '{"type":"2","tab_id":"7"}', 0, 1, 1),
(273, 286, '', '秒杀管理', 'admin', '', '', '[]', 0, 1, 1),
(293, 288, '', '交易数据', 'admin', '', '', '[]', 100, 1, 1),
(276, 21, '', '附件管理', 'admin', 'widget.images', 'index', '[]', 0, 0, 1),
(278, 21, '', '清除数据', 'admin', 'system.system_cleardata', 'index', '[]', 0, 1, 1),
(363, 362, '', '上传图片', 'admin', 'widget.images', 'upload', '[]', 0, 1, 1),
(364, 362, '', '删除图片', 'admin', 'widget.images', 'delete', '[]', 0, 1, 1),
(362, 276, '', '附加权限', '', '', '', '[]', 0, 1, 1),
(285, 0, 'building-o', '订单', 'admin', '', '', '[]', 109, 1, 1),
(283, 80, '', '文章管理', 'admin', 'article.article', 'index', '[]', 0, 1, 1),
(284, 80, '', '文章分类', 'admin', 'article.article_category', 'index', '[]', 0, 1, 1),
(287, 0, 'money', '财务', 'admin', '', '', '[]', 103, 1, 1),
(288, 0, 'line-chart', '数据', 'admin', '', '', '[]', 100, 0, 1),
(289, 0, 'gear', '设置', 'admin', '', '', '[]', 90, 1, 1),
(323, 24, '', '出售中商品', 'admin', 'store.storeProduct', 'index', '{"type":"1"}', 100, 1, 1),
(290, 285, '', '售后服务', 'admin', '', '', '[]', 0, 1, 1),
(353, 337, '', '分销配置', 'admin', 'setting.systemConfig', 'index', '{"type":"3","tab_id":"9"}', 0, 1, 1),
(306, 287, '', '财务操作', 'admin', '', '', '[]', 100, 1, 1),
(307, 287, '', '财务记录', 'admin', '', '', '[]', 50, 1, 1),
(308, 287, '', '佣金记录', 'admin', '', '', '[]', 0, 1, 1),
(372, 269, '', '首页幻灯片', 'admin', 'setting.system_group_data', 'index', '{"gid":"48"}', 0, 1, 1),
(312, 307, '', '资金监控', 'admin', 'finance.finance', 'bill', '[]', 0, 1, 1),
(313, 308, '', '佣金记录', 'admin', 'finance.finance', 'commission_list', '[]', 0, 1, 1),
(324, 24, '', '仓库中商品', 'admin', 'store.storeProduct', 'index', '{"type":"3"}', 80, 1, 1),
(325, 24, '', '已售馨商品', 'admin', 'store.storeProduct', 'index', '{"type":"4"}', 70, 1, 1),
(326, 24, '', '警戒商品', 'admin', 'store.storeProduct', 'index', '{"type":"5"}', 60, 1, 1),
(327, 24, '', '商品回收站', 'admin', 'store.storeProduct', 'index', '{"type":"6"}', 0, 1, 1),
(328, 24, '', '待上架商品', 'admin', 'store.storeProduct', 'index', '{"type":"2"}', 90, 1, 1),
(329, 285, '', '营销订单', 'admin', 'user', 'user', '[]', 0, 0, 1),
(371, 337, '', '分销员管理', 'admin', 'agent.agentManage', 'index', '[]', 0, 1, 1),
(354, 11, '', '自动回复', '', '', '', '[]', 80, 1, 1),
(334, 329, '', '秒杀订单', 'admin', 'user', '', '[]', 0, 0, 1),
(335, 329, '', '积分兑换', 'admin', 'user', '', '[]', 0, 0, 1),
(336, 151, '', '会员等级', 'admin', 'user.user', 'group', '[]', 0, 0, 1),
(337, 0, 'users', '分销', 'admin', 'user', 'user', '[]', 106, 1, 1),
(349, 286, '', '积分', 'admin', 'userPoint', 'index', '[]', 0, 1, 1),
(350, 349, '', '积分配置', 'admin', 'setting.systemConfig', 'index', '{"type":"3","tab_id":"11"}', 0, 1, 1),
(355, 11, '', '页面设置', '', '', '', '[]', 90, 1, 1),
(356, 355, '', '个人中心菜单', 'admin', 'setting.system_group_data', 'index', '{"gid":"32"}', 0, 1, 1),
(357, 355, '', '商城首页banner', 'admin', 'setting.system_group_data', 'index', '{"gid":"34"}', 0, 1, 1),
(358, 355, '', '商城首页分类按钮', 'admin', 'setting.system_group_data', 'index', '{"gid":"35"}', 0, 1, 1),
(359, 355, '', '商城首页滚动新闻', 'admin', 'setting.system_group_data', 'index', '{"gid":"36"}', 0, 1, 1),
(360, 11, '', '公众号配置', '', '', '', '[]', 100, 1, 1),
(361, 360, '', '公众号支付配置', 'admin', 'setting.systemConfig', 'index', '{"type":"1","tab_id":"4"}', 0, 1, 1),
(365, 362, '', '附件管理', 'admin', 'widget.images', 'index', '[]', 0, 1, 1),
(369, 143, '', '添加产品分类', 'admin', 'store.storeCategory', 'create', '[]', 0, 0, 1),
(370, 143, '', '编辑产品分类', 'admin', 'store.storeCategory', 'edit', '[]', 0, 0, 1),
(373, 269, '', '首页导航按钮', 'admin', 'setting.system_group_data', 'index', '{"gid":"47"}', 0, 1, 1),
(374, 295, '', '分销会员业务', 'admin', 'record.record', 'user_distribution_chart', '[]', 0, 1, 1),
(375, 269, '', '小程序支付配置', 'admin', 'setting.systemConfig', 'index_alone', '{"type":"2","tab_id":"14"}', 0, 1, 1),
(376, 269, '', '小程序模板消息', 'admin', 'routine.routineTemplate', 'index', '[]', 0, 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_notice`
--

CREATE TABLE IF NOT EXISTS `eb_system_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '通知模板id',
  `title` varchar(64) NOT NULL COMMENT '通知标题',
  `type` varchar(64) NOT NULL COMMENT '通知类型',
  `icon` varchar(16) NOT NULL COMMENT '图标',
  `url` varchar(64) NOT NULL COMMENT '链接',
  `table_title` varchar(256) NOT NULL COMMENT '通知数据',
  `template` varchar(64) NOT NULL COMMENT '通知模板',
  `push_admin` varchar(128) NOT NULL COMMENT '通知管理员id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='通知模板表' AUTO_INCREMENT=10 ;

--
-- 转存表中的数据 `eb_system_notice`
--

INSERT INTO `eb_system_notice` (`id`, `title`, `type`, `icon`, `url`, `table_title`, `template`, `push_admin`, `status`) VALUES
(5, '用户关注通知', 'user_sub', 'user-plus', '', '[{"title":"openid","key":"openid"},{"title":"\\u5fae\\u4fe1\\u6635\\u79f0","key":"nickname"},{"title":"\\u5173\\u6ce8\\u4e8b\\u4ef6","key":"add_time"}]', '有%u个新用户关注了公众号', '1', 1),
(7, '新订单提醒', '订单提醒', 'building-o', '', '[{"title":"\\u8ba2\\u5355\\u53f7","key":"key1"}]', '新订单提醒', '1', 1),
(9, '测试通知中 ', '测试', 'buysellads', '', '[{"title":"\\u8ba2\\u5355\\u53f7","key":"key1"}]', '测试', '20', 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_notice_admin`
--

CREATE TABLE IF NOT EXISTS `eb_system_notice_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '通知记录ID',
  `notice_type` varchar(64) NOT NULL COMMENT '通知类型',
  `admin_id` smallint(5) unsigned NOT NULL COMMENT '通知的管理员',
  `link_id` int(10) unsigned NOT NULL COMMENT '关联ID',
  `table_data` text NOT NULL COMMENT '通知的数据',
  `is_click` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '点击次数',
  `is_visit` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '访问次数',
  `visit_time` int(11) NOT NULL COMMENT '访问时间',
  `add_time` int(10) unsigned NOT NULL COMMENT '通知时间',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`,`notice_type`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `is_visit` (`is_visit`) USING BTREE,
  KEY `is_click` (`is_click`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='通知记录表' AUTO_INCREMENT=11 ;

--
-- 转存表中的数据 `eb_system_notice_admin`
--

INSERT INTO `eb_system_notice_admin` (`id`, `notice_type`, `admin_id`, `link_id`, `table_data`, `is_click`, `is_visit`, `visit_time`, `add_time`) VALUES
(7, 'user_sub', 1, 2, '{"openid":2,"nickname":123,"change_time":1512444900}', 0, 1, 1512525411, 1512444900),
(8, 'user_sub', 2, 2, '{"openid":2,"nickname":123,"change_time":1512444900}', 0, 1, 1512459748, 1512444901),
(9, 'user_sub', 1, 2, '{"openid":2,"nickname":123,"change_time":1512454840}', 0, 1, 1512525411, 1512454840),
(10, 'user_sub', 2, 2, '{"openid":2,"nickname":123,"change_time":1512454840}', 0, 1, 1512459748, 1512454840);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_role`
--

CREATE TABLE IF NOT EXISTS `eb_system_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '身份管理id',
  `role_name` varchar(32) NOT NULL COMMENT '身份管理名称',
  `rules` text NOT NULL COMMENT '身份管理权限(menus_id)',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `status` (`status`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='身份管理表' AUTO_INCREMENT=20 ;

--
-- 转存表中的数据 `eb_system_role`
--

INSERT INTO `eb_system_role` (`id`, `role_name`, `rules`, `level`, `status`) VALUES
(1, '超级管理员', '85,86,11,174,17,127,128,126,80,32,71,33,36,35,34,113,114,19,73,42,43,44,45,115,116,18,72,38,41,40,39,79,31,37,95,96,94,30,89,124,123,90,91,92,93,12,98,99,97,23,240,238,148,150,149,229,230,234,233,232,231,235,175,210,223,222,215,214,213,212,211,209,170,225,228,227,226,224,160,162,161,26,190,191,192,193,194,206,195,205,204,203,202,201,200,199,198,197,207,208,196,25,142,143,24,147,146,21,237,130,131,22,136,135,139,138,137,140,141,1,173,5,9,70,52,58,57,56,55,54,53,112,111,110,109,7,69,46,51,50,49,48,47,108,107,106,6,118,117,145,144,119,2,74,75,76,101,100,77,78,153,4,59,62,61,60,103,102,68,8,63,64,65,66,105,104,67,151,177,176,239,179,217,219,218,216,82,129,132,133,134,236,84', 0, 1),
(15, '子管理员', '11,174,17,127,128,126,80,32,33,36,35,34,113,114,71,19,42,45,44,43,116,115,73,79,31,37,95,96,94,30,89,124,123,90,91,92,93,12,98,99,97,23,241,240,238,148,149,230,234,233,232,231,229,150,235,175,210,212,211,213,214,223,222,215,209,170,225,228,227,226,224,26,191,198,192,202,193,203,201,200,194,199,196,197,195,208,207,206,205,204,190,25,142,143,24,146,147,151,177,176,239,179,217,219,218,216,82,236,84,133,134,129,132,18,72,38,39,40,41,153,8,67,63,66,65,64,105,104,4,59,60,61,62,103,102,68,247,250,243,246,245,244,1,2,78,74,101,9,52,58,57,56,55,54,53,111,110,109,112,70,5,7,69,46,51,50,49,48,47,108,107,106,6,118,117,144,145,119,21,173,237,130,131,0', 2, 1),
(10, '客服', '23,241,240,238,148,150,149,229,230,231,234,233,232,235,26,191,197,196,195,194,193,192,198,207,206,205,204,203,202,201,200,208,199,190,175,209,210,223,222,215,214,213,212,211,170,225,228,227,226,224,25,142,143,24,146,147,151,177,176,239,179,217,219,218,216,82,133,134,129,132,236,84', 1, 1),
(14, '演示账号', '146,142,26,191,195,194,193,192,196,197,198,208,207,206,205,204,203,202,201,200,199,190,290,170,224,225,228,227,226,177,176,82,133,134,236,84,129,132,337,371,353,149,229,230,232,233,234,231,235,150,273,241,238,306,239,179,216,217,218,219,245,244,246,269,376,375,373,270,372,17,126,127,128,174,354,12,98,99,97,30,89,92,91,90,123,124,93,31,95,96,94,37,175,210,215,214,222,223,213,212,211,209,19,73,42,45,44,43,116,115,8,67,63,104,66,65,64,105,4,68,59,103,102,62,61,60,78,101,250,70,56,112,111,110,109,7,69,46,47,48,49,51,50,108,107,106,6,118,117,145,144,119,80,284,71,33,36,35,34,114,113,283,72,38,41,40,39,173,237,130,131,365,24,25,285,151,0,148,286,272,287,307,293,294,295,360,11,153,2,74,247,9,52,1,21,362', 1, 1),
(16, '三级身份', '11,174,17,127,128,126,80,32,33,36,35,34,113,114,71,19,42,45,44,43,116,115,73,79,31,37,95,96,94,30,89,124,123,90,91,92,93,12,98,99,97,23,148,149,230,234,233,232,231,229,150,235,175,210,212,211,213,214,223,222,215,209,170,225,228,227,226,224,26,191,198,192,202,193,203,201,200,194,199,196,197,195,208,207,206,205,204,190,25,142,143,24,146,147,241,240,238,151,179,217,219,218,216,177,176,239,82,133,134,129,132,236,84,18,38,39,40,41,72,153,8,67,63,66,65,64,105,104,4,59,60,61,62,103,102,68,247,250,243,246,245,244,9,52,58,57,56,55,54,53,111,110,109,112,70,5,7,69,46,51,50,49,48,47,108,107,106,6,118,117,144,145,119,21,130,131,173,237,0,1', 3, 1),
(17, 'boss', '11,174,17,126,127,128,19,42,43,44,45,115,116,73,79,31,37,94,95,96,30,93,89,123,124,90,91,92,12,97,98,99,23,241,240,238,148,149,230,234,233,232,231,229,150,235,170,225,228,227,226,224,175,210,212,211,213,214,223,222,215,209,25,143,142,24,147,146,26,191,197,196,195,194,202,198,192,200,203,201,199,193,208,207,206,205,204,190,151,176,177,239,179,217,219,218,216,80,32,71,33,34,35,36,113,114,18,72,38,39,40,41,0', 2, 1),
(18, '管理员', '23,24,323,328,324,325,326,327,147,261,262,266,265,264,263,146,25,143,370,369,142,285,26,191,194,193,192,195,196,208,207,206,205,204,203,202,201,200,199,198,197,190,329,335,334,333,290,170,225,226,227,228,224,151,177,176,82,133,134,236,84,129,132,336,337,371,339,353,286,148,149,229,230,234,231,232,233,235,150,352,349,351,350,273,241,272,238,271,254,366,368,367,287,306,239,307,179,216,217,218,219,312,308,313,288,293,251,245,340,341,296,318,317,316,315,314,294,244,302,301,300,295,246,305,304,303,297,321,320,319,355,359,358,357,356,354,12,98,99,97,30,89,92,91,90,123,124,93,31,95,96,94,37,175,210,212,213,214,215,222,223,211,209,19,73,42,45,44,43,116,115,283,72,38,41,40,39,284,71,33,36,35,34,114,113,8,67,63,104,66,65,64,105,68,103,102,61,2,78,74,77,76,75,101,100,247,248,249,250,1,9,70,52,58,57,56,55,54,53,112,111,110,109,7,69,46,47,48,49,51,50,108,107,106,6,118,117,145,144,119,255,269,270,21,130,131,173,252,237,278,276,362,365,364,363,258,257,260,0,11,80,153,4,59,289,256', 1, 1),
(19, '公司内部', '23,24,323,328,324,325,326,327,147,261,262,263,266,265,264,146,25,143,370,369,142,285,26,191,195,194,193,192,196,197,208,207,206,205,204,203,202,201,200,199,198,190,329,335,334,333,290,170,224,225,228,227,226,177,176,82,133,134,236,84,129,132,337,371,353,286,148,149,229,230,231,232,233,234,235,150,352,349,351,350,273,241,272,238,271,254,366,368,367,306,239,179,216,217,218,219,340,341,296,318,317,316,315,314,302,301,300,246,305,304,303,297,321,320,319,270,11,360,267,17,126,127,128,174,361,355,359,358,357,356,354,12,98,99,97,30,89,92,91,90,123,124,93,31,95,96,94,37,175,210,213,214,215,222,223,212,211,209,19,73,42,45,44,43,116,115,289,153,8,67,63,104,66,65,64,105,4,68,59,103,102,62,61,60,2,78,74,77,76,75,101,100,247,250,248,249,1,9,70,52,58,57,56,55,54,53,112,111,110,109,7,69,46,47,48,49,51,50,108,107,106,6,118,117,145,144,119,255,283,72,38,41,40,39,284,71,33,36,35,34,114,113,130,131,173,252,237,276,362,365,364,363,0,151,287,307,293,288,294,295,269,80,21', 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_user`
--

CREATE TABLE IF NOT EXISTS `eb_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `account` varchar(32) NOT NULL COMMENT '用户账号',
  `pwd` varchar(32) NOT NULL COMMENT '用户密码',
  `nickname` varchar(16) NOT NULL COMMENT '用户昵称',
  `avatar` varchar(256) NOT NULL COMMENT '用户头像',
  `phone` char(15) NOT NULL COMMENT '手机号码',
  `add_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  `add_ip` varchar(16) NOT NULL COMMENT '添加ip',
  `last_time` int(11) unsigned NOT NULL COMMENT '最后一次登录时间',
  `last_ip` varchar(16) NOT NULL COMMENT '最后一次登录ip',
  `now_money` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '用户余额',
  `integral` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '用户剩余积分',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1为正常，0为禁止',
  `level` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `spread_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推广元id',
  `user_type` varchar(32) NOT NULL COMMENT '用户类型',
  `is_promoter` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推广员',
  `pay_count` int(11) unsigned DEFAULT '0' COMMENT '用户购买次数',
  PRIMARY KEY (`uid`),
  KEY `account` (`account`) USING BTREE,
  KEY `spreaduid` (`spread_uid`) USING BTREE,
  KEY `level` (`level`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_promoter` (`is_promoter`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_user`
--

INSERT INTO `eb_user` (`uid`, `account`, `pwd`, `nickname`, `avatar`, `phone`, `add_time`, `add_ip`, `last_time`, `last_ip`, `now_money`, `integral`, `status`, `level`, `spread_uid`, `user_type`, `is_promoter`, `pay_count`) VALUES
(1, 'liaofei', 'e10adc3949ba59abbe56e057f20f883e', '等风来，随风去', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLBaQPPnbg52bgibia1CZDruib1RwibHbBbnfxH1MUwbyz3G0Xub1LNX0ib5RFd7nZvo88gzHwib0OPibyfZQ/132', '', 1528859304, '140.207.54.80', 1535070458, '127.0.0.1', '0.00', '0.00', 1, 0, 0, 'wechat', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_address`
--

CREATE TABLE IF NOT EXISTS `eb_user_address` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户地址id',
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `real_name` varchar(32) NOT NULL COMMENT '收货人姓名',
  `phone` varchar(16) NOT NULL COMMENT '收货人电话',
  `province` varchar(64) NOT NULL COMMENT '收货人所在省',
  `city` varchar(64) NOT NULL COMMENT '收货人所在市',
  `district` varchar(64) NOT NULL COMMENT '收货人所在区',
  `detail` varchar(256) NOT NULL COMMENT '收货人详细地址',
  `post_code` int(10) unsigned NOT NULL COMMENT '邮编',
  `longitude` varchar(16) NOT NULL DEFAULT '0' COMMENT '经度',
  `latitude` varchar(16) NOT NULL DEFAULT '0' COMMENT '纬度',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `is_default` (`is_default`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户地址表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_bill`
--

CREATE TABLE IF NOT EXISTS `eb_user_bill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户账单id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `link_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '关联id',
  `pm` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 = 支出 1 = 获得',
  `title` varchar(64) NOT NULL COMMENT '账单标题',
  `category` varchar(64) NOT NULL COMMENT '明细种类',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '明细类型',
  `number` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '明细数字',
  `balance` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '剩余',
  `mark` varchar(512) NOT NULL COMMENT '备注',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = 带确定 1 = 有效 -1 = 无效',
  PRIMARY KEY (`id`),
  KEY `openid` (`uid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `pm` (`pm`) USING BTREE,
  KEY `type` (`category`,`type`,`link_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户账单表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_enter`
--

CREATE TABLE IF NOT EXISTS `eb_user_enter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商户申请ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `province` varchar(32) NOT NULL COMMENT '商户所在省',
  `city` varchar(32) NOT NULL COMMENT '商户所在市',
  `district` varchar(32) NOT NULL COMMENT '商户所在区',
  `address` varchar(256) NOT NULL COMMENT '商户详细地址',
  `merchant_name` varchar(256) NOT NULL COMMENT '商户名称',
  `link_user` varchar(32) NOT NULL,
  `link_tel` varchar(16) NOT NULL COMMENT '商户电话',
  `charter` varchar(512) NOT NULL COMMENT '商户证书',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `apply_time` int(10) unsigned NOT NULL COMMENT '审核时间',
  `success_time` int(11) NOT NULL COMMENT '通过时间',
  `fail_message` varchar(256) NOT NULL COMMENT '未通过原因',
  `fail_time` int(10) unsigned NOT NULL COMMENT '未通过时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1 审核未通过 0未审核 1审核通过',
  `is_lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 = 开启 1= 关闭',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`) USING BTREE,
  KEY `province` (`province`,`city`,`district`) USING BTREE,
  KEY `is_lock` (`is_lock`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商户申请表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_extract`
--

CREATE TABLE IF NOT EXISTS `eb_user_extract` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT NULL,
  `real_name` varchar(64) DEFAULT NULL COMMENT '名称',
  `extract_type` varchar(32) DEFAULT 'bank' COMMENT 'bank = 银行卡 alipay = 支付宝wx=微信',
  `bank_code` varchar(32) DEFAULT '0' COMMENT '银行卡',
  `bank_address` varchar(256) DEFAULT '' COMMENT '开户地址',
  `alipay_code` varchar(64) DEFAULT '' COMMENT '支付宝账号',
  `extract_price` decimal(8,2) unsigned DEFAULT '0.00' COMMENT '提现金额',
  `mark` varchar(512) DEFAULT NULL,
  `balance` decimal(8,2) unsigned DEFAULT '0.00',
  `fail_msg` varchar(128) DEFAULT NULL COMMENT '无效原因',
  `fail_time` int(10) unsigned DEFAULT NULL,
  `add_time` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  `status` tinyint(2) DEFAULT '0' COMMENT '-1 未通过 0 审核中 1 已提现',
  `wechat` varchar(15) DEFAULT NULL COMMENT '微信号',
  PRIMARY KEY (`id`),
  KEY `extract_type` (`extract_type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `openid` (`uid`) USING BTREE,
  KEY `fail_time` (`fail_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户提现表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_group`
--

CREATE TABLE IF NOT EXISTS `eb_user_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(64) DEFAULT NULL COMMENT '用户分组名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户分组表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_notice`
--

CREATE TABLE IF NOT EXISTS `eb_user_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` text NOT NULL COMMENT '接收消息的用户id（类型：json数据）',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '消息通知类型（1：系统消息；2：用户通知）',
  `user` varchar(20) NOT NULL DEFAULT '' COMMENT '发送人',
  `title` varchar(20) NOT NULL COMMENT '通知消息的标题信息',
  `content` varchar(500) NOT NULL COMMENT '通知消息的内容',
  `add_time` int(11) NOT NULL COMMENT '通知消息发送的时间',
  `is_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发送（0：未发送；1：已发送）',
  `send_time` int(11) NOT NULL COMMENT '发送时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户通知表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_notice_see`
--

CREATE TABLE IF NOT EXISTS `eb_user_notice_see` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL COMMENT '查看的通知id',
  `uid` int(11) NOT NULL COMMENT '查看通知的用户id',
  `add_time` int(11) NOT NULL COMMENT '查看通知的时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户通知发送记录表' AUTO_INCREMENT=24 ;

--
-- 转存表中的数据 `eb_user_notice_see`
--

INSERT INTO `eb_user_notice_see` (`id`, `nid`, `uid`, `add_time`) VALUES
(1, 2, 20, 1533784136),
(2, 3, 20, 1533785982),
(3, 4, 20, 1533788165),
(4, 8, 91, 1534229161),
(5, 7, 91, 1534229210),
(6, 6, 91, 1534229606),
(7, 5, 91, 1534229607),
(8, 12, 91, 1534229611),
(9, 13, 91, 1534229617),
(10, 14, 91, 1534236474),
(11, 16, 91, 1534237723),
(12, 11, 91, 1534237728),
(13, 20, 1, 1534297935),
(14, 21, 9, 1534302665),
(15, 24, 9, 1534308149),
(16, 26, 91, 1534316096),
(17, 23, 9, 1534318276),
(18, 22, 9, 1534318278),
(19, 24, 1, 1534406943),
(20, 22, 1, 1534406944),
(21, 23, 1, 1534406945),
(22, 34, 4, 1534497414),
(23, 24, 4, 1534497421);

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_recharge`
--

CREATE TABLE IF NOT EXISTS `eb_user_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT NULL COMMENT '充值用户UID',
  `order_id` varchar(32) DEFAULT NULL COMMENT '订单号',
  `price` decimal(8,2) DEFAULT NULL COMMENT '充值金额',
  `recharge_type` varchar(32) DEFAULT NULL COMMENT '充值类型',
  `paid` tinyint(1) DEFAULT NULL COMMENT '是否充值',
  `pay_time` int(10) DEFAULT NULL COMMENT '充值支付时间',
  `add_time` int(12) DEFAULT NULL COMMENT '充值时间',
  `refund_price` decimal(10,2) unsigned NOT NULL COMMENT '退款金额',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `recharge_type` (`recharge_type`) USING BTREE,
  KEY `paid` (`paid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户充值表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_media`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '微信视频音频id',
  `type` varchar(16) NOT NULL COMMENT '回复类型',
  `path` varchar(128) NOT NULL COMMENT '文件路径',
  `media_id` varchar(64) NOT NULL COMMENT '微信服务器返回的id',
  `url` varchar(256) NOT NULL COMMENT '地址',
  `temporary` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否永久或者临时 0永久1临时',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`media_id`) USING BTREE,
  KEY `type_2` (`type`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信回复表' AUTO_INCREMENT=13 ;

--
-- 转存表中的数据 `eb_wechat_media`
--

INSERT INTO `eb_wechat_media` (`id`, `type`, `path`, `media_id`, `url`, `temporary`, `add_time`) VALUES
(12, 'image', '/public/uploads/wechat/image/5b042ca618139.jpg', '6sFx6PzPF2v_Lv4FGOMzzwcwmM2wuoA63ZMSxiN-7DY', 'http://mmbiz.qpic.cn/mmbiz_jpg/xVkDhuiaGm78WOdUXuPE1oYLnU4J0LCEiaSuLhwwSrfdyINspibXsllaj8rOMSs5estAv0qhGuGniaqhb6HftecPuw/0?wx_fmt=jpeg', 0, 1527000231);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_message`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户行为记录id',
  `openid` varchar(32) NOT NULL COMMENT '用户openid',
  `type` varchar(32) NOT NULL COMMENT '操作类型',
  `result` varchar(512) NOT NULL COMMENT '操作详细记录',
  `add_time` int(10) unsigned NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户行为记录表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_news_category`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_news_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '图文消息管理ID',
  `cate_name` varchar(255) NOT NULL COMMENT '图文名称',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `new_id` varchar(255) NOT NULL COMMENT '文章id',
  `add_time` varchar(255) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图文消息管理表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_qrcode`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_qrcode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '微信二维码ID',
  `third_type` varchar(32) NOT NULL COMMENT '二维码类型',
  `third_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `ticket` varchar(255) NOT NULL COMMENT '二维码参数',
  `expire_seconds` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '二维码有效时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` varchar(255) NOT NULL COMMENT '添加时间',
  `url` varchar(255) NOT NULL COMMENT '微信访问url',
  `qrcode_url` varchar(255) NOT NULL COMMENT '微信二维码url',
  `scan` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被扫的次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `third_type` (`third_type`,`third_id`) USING BTREE,
  KEY `ticket` (`ticket`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信二维码管理表' AUTO_INCREMENT=126 ;

--
-- 转存表中的数据 `eb_wechat_qrcode`
--

INSERT INTO `eb_wechat_qrcode` (`id`, `third_type`, `third_id`, `ticket`, `expire_seconds`, `status`, `add_time`, `url`, `qrcode_url`, `scan`) VALUES
(1, 'spread', 345, 'gQF78TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTjFNd2dXUDBiRy0xMDAwMHcwMzQAAgQUk1ZbAwQAAAAA', 0, 1, '1532406871', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF78TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTjFNd2dXUDBiRy0xMDAwMHcwMzQAAgQUk1ZbAwQAAAAA', 'http://weixin.qq.com/q/02N1MwgWP0bG-10000w034', 0),
(2, 'spread', 344, 'gQEa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybVBsU2hIUDBiRy0xMDAwMGcwM3oAAgQUk1ZbAwQAAAAA', 0, 1, '1532406871', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybVBsU2hIUDBiRy0xMDAwMGcwM3oAAgQUk1ZbAwQAAAAA', 'http://weixin.qq.com/q/02mPlShHP0bG-10000g03z', 0),
(3, 'spread', 343, 'gQGD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXYzd2dUUDBiRy0xMDAwMHcwMzgAAgQcdFZbAwQAAAAA', 0, 1, '1532406872', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXYzd2dUUDBiRy0xMDAwMHcwMzgAAgQcdFZbAwQAAAAA', 'http://weixin.qq.com/q/021v3wgTP0bG-10000w038', 0),
(4, 'spread', 342, 'gQH97zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaFJ4ZmhnUDBiRy0xMDAwMGcwM3IAAgQddFZbAwQAAAAA', 0, 1, '1532406872', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH97zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaFJ4ZmhnUDBiRy0xMDAwMGcwM3IAAgQddFZbAwQAAAAA', 'http://weixin.qq.com/q/02hRxfhgP0bG-10000g03r', 0),
(5, 'spread', 341, 'gQH28DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAya2RrTmdPUDBiRy0xMDAwMHcwM1QAAgRPgVVbAwQAAAAA', 0, 1, '1532406872', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH28DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAya2RrTmdPUDBiRy0xMDAwMHcwM1QAAgRPgVVbAwQAAAAA', 'http://weixin.qq.com/q/02kdkNgOP0bG-10000w03T', 0),
(6, 'spread', 340, 'gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR1oxamd0UDBiRy0xMDAwME0wM0sAAgRQgVVbAwQAAAAA', 0, 1, '1532406872', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR1oxamd0UDBiRy0xMDAwME0wM0sAAgRQgVVbAwQAAAAA', 'http://weixin.qq.com/q/02GZ1jgtP0bG-10000M03K', 0),
(7, 'spread', 339, 'gQEq8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaDZkNGhrUDBiRy0xMDAwMHcwM1QAAgRQgVVbAwQAAAAA', 0, 1, '1532406872', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEq8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaDZkNGhrUDBiRy0xMDAwMHcwM1QAAgRQgVVbAwQAAAAA', 'http://weixin.qq.com/q/02h6d4hkP0bG-10000w03T', 0),
(8, 'spread', 338, 'gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR3pGVWhaUDBiRy0xMDAwME0wM04AAgRQgVVbAwQAAAAA', 0, 1, '1532406872', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR3pGVWhaUDBiRy0xMDAwME0wM04AAgRQgVVbAwQAAAAA', 'http://weixin.qq.com/q/02GzFUhZP0bG-10000M03N', 0),
(9, 'spread', 337, 'gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ2lVSmhQUDBiRy0xMDAwMDAwM1QAAgRQgVVbAwQAAAAA', 0, 1, '1532406873', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ2lVSmhQUDBiRy0xMDAwMDAwM1QAAgRQgVVbAwQAAAAA', 'http://weixin.qq.com/q/02CiUJhPP0bG-10000003T', 0),
(10, 'spread', 336, 'gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS3l0dGhuUDBiRy0xMDAwMGcwM3EAAgRQgVVbAwQAAAAA', 0, 1, '1532406873', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS3l0dGhuUDBiRy0xMDAwMGcwM3EAAgRQgVVbAwQAAAAA', 'http://weixin.qq.com/q/02KytthnP0bG-10000g03q', 0),
(11, 'spread', 335, 'gQE38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyOUlKcGd6UDBiRy0xMDAwMGcwM0IAAgRRgVVbAwQAAAAA', 0, 1, '1532406873', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyOUlKcGd6UDBiRy0xMDAwMGcwM0IAAgRRgVVbAwQAAAAA', 'http://weixin.qq.com/q/029IJpgzP0bG-10000g03B', 0),
(12, 'spread', 334, 'gQEu8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybDJKUGd1UDBiRy0xMDAwMGcwM1QAAgRRgVVbAwQAAAAA', 0, 1, '1532406873', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEu8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybDJKUGd1UDBiRy0xMDAwMGcwM1QAAgRRgVVbAwQAAAAA', 'http://weixin.qq.com/q/02l2JPguP0bG-10000g03T', 0),
(13, 'spread', 333, 'gQEX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG1IZWc4UDBiRy0xMDAwMGcwM1YAAgRRgVVbAwQAAAAA', 0, 1, '1532406873', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG1IZWc4UDBiRy0xMDAwMGcwM1YAAgRRgVVbAwQAAAAA', 'http://weixin.qq.com/q/02pmHeg8P0bG-10000g03V', 0),
(14, 'spread', 332, 'gQEy8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaWhJSWdtUDBiRy0xMDAwMDAwM2QAAgRRgVVbAwQAAAAA', 0, 1, '1532406873', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEy8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaWhJSWdtUDBiRy0xMDAwMDAwM2QAAgRRgVVbAwQAAAAA', 'http://weixin.qq.com/q/02ihIIgmP0bG-10000003d', 0),
(15, 'spread', 331, 'gQHh8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyb3lfeWhWUDBiRy0xMDAwMDAwM24AAgRRgVVbAwQAAAAA', 0, 1, '1532406873', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHh8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyb3lfeWhWUDBiRy0xMDAwMDAwM24AAgRRgVVbAwQAAAAA', 'http://weixin.qq.com/q/02oy_yhVP0bG-10000003n', 0),
(16, 'spread', 330, 'gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZGxwWmdWUDBiRy0xMDAwMHcwM2IAAgRLtFFbAwQAAAAA', 0, 1, '1532406874', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZGxwWmdWUDBiRy0xMDAwMHcwM2IAAgRLtFFbAwQAAAAA', 'http://weixin.qq.com/q/02dlpZgVP0bG-10000w03b', 0),
(17, 'spread', 329, 'gQGc8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVTdmdGd4UDBiRy0xMDAwMHcwM2oAAgSsZVFbAwQAAAAA', 0, 1, '1532406874', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGc8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVTdmdGd4UDBiRy0xMDAwMHcwM2oAAgSsZVFbAwQAAAAA', 'http://weixin.qq.com/q/02U7ftgxP0bG-10000w03j', 0),
(18, 'spread', 328, 'gQFz8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNmtKMmhaUDBiRy0xMDAwMDAwMzMAAgSOtC9ZAwQAAAAA', 0, 1, '1532406874', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFz8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNmtKMmhaUDBiRy0xMDAwMDAwMzMAAgSOtC9ZAwQAAAAA', 'http://weixin.qq.com/q/026kJ2hZP0bG-100000033', 0),
(19, 'spread', 327, 'gQFM8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWEdTX2czUDBiRy0xMDAwME0wM0EAAgTHay9ZAwQAAAAA', 0, 1, '1532406874', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFM8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWEdTX2czUDBiRy0xMDAwME0wM0EAAgTHay9ZAwQAAAAA', 'http://weixin.qq.com/q/02XGS_g3P0bG-10000M03A', 0),
(20, 'spread', 326, 'gQGU8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyci03d2hEUDBiRy0xMDAwMHcwM2kAAgQfay9ZAwQAAAAA', 0, 1, '1532406874', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGU8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyci03d2hEUDBiRy0xMDAwMHcwM2kAAgQfay9ZAwQAAAAA', 'http://weixin.qq.com/q/02r-7whDP0bG-10000w03i', 0),
(21, 'spread', 3, 'gQHM8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybkRjQmhrUDBiRy0xMDAwMDAwM0wAAgRm9lZbAwQAAAAA', 0, 1, '1532425830', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHM8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybkRjQmhrUDBiRy0xMDAwMDAwM0wAAgRm9lZbAwQAAAAA', 'http://weixin.qq.com/q/02nDcBhkP0bG-10000003L', 0),
(22, 'spread', 2, 'gQFq8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycXlweGgyUDBiRy0xMDAwME0wM08AAgRm9lZbAwQAAAAA', 0, 1, '1532425830', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFq8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycXlweGgyUDBiRy0xMDAwME0wM08AAgRm9lZbAwQAAAAA', 'http://weixin.qq.com/q/02qypxh2P0bG-10000M03O', 0),
(23, 'spread', 1, 'gQFZ8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFJHMWg1UDBiRy0xeUs5cmhyMWgAAgSufHNbAwQAjScA', 1536887214, 1, '1532425830', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFZ8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFJHMWg1UDBiRy0xeUs5cmhyMWgAAgSufHNbAwQAjScA', 'http://weixin.qq.com/q/02TRG1h5P0bG-1yK9rhr1h', 1),
(24, 'spread', 4, 'gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydWdhTmdrUDBiRy0xMDAwMGcwM08AAgQJA1dbAwQAAAAA', 0, 1, '1532429065', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydWdhTmdrUDBiRy0xMDAwMGcwM08AAgQJA1dbAwQAAAAA', 'http://weixin.qq.com/q/02ugaNgkP0bG-10000g03O', 0),
(25, 'spread', 7, 'gQHb8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUU1YR2dsUDBiRy0xMDAwMGcwM0cAAgRwxFdbAwQAAAAA', 0, 1, '1532478576', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHb8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUU1YR2dsUDBiRy0xMDAwMGcwM0cAAgRwxFdbAwQAAAAA', 'http://weixin.qq.com/q/02QMXGglP0bG-10000g03G', 0),
(26, 'spread', 6, 'gQEe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNnZKQmh0UDBiRy0xMDAwMDAwMzcAAgRwxFdbAwQAAAAA', 0, 1, '1532478576', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNnZKQmh0UDBiRy0xMDAwMDAwMzcAAgRwxFdbAwQAAAAA', 'http://weixin.qq.com/q/026vJBhtP0bG-100000037', 0),
(27, 'spread', 5, 'gQEA8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXcxWWg0UDBiRy0xMDAwMDAwM2oAAgRxxFdbAwQAAAAA', 0, 1, '1532478577', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEA8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXcxWWg0UDBiRy0xMDAwMDAwM2oAAgRxxFdbAwQAAAAA', 'http://weixin.qq.com/q/02aw1Yh4P0bG-10000003j', 0),
(28, 'spread', 8, 'gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXhxRWhpUDBiRy0xMDAwME0wM28AAgSN31dbAwQAAAAA', 0, 1, '1532485517', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXhxRWhpUDBiRy0xMDAwME0wM28AAgSN31dbAwQAAAAA', 'http://weixin.qq.com/q/02axqEhiP0bG-10000M03o', 0),
(29, 'spread', 11, 'gQGh8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX0M3R2c3UDBiRy0xMDAwMGcwM0UAAgSdJFhbAwQAAAAA', 0, 1, '1532503198', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGh8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX0M3R2c3UDBiRy0xMDAwMGcwM0UAAgSdJFhbAwQAAAAA', 'http://weixin.qq.com/q/02_C7Gg7P0bG-10000g03E', 0),
(30, 'spread', 10, 'gQEi8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN3E1aWd1UDBiRy0xMDAwME0wM3kAAgSeJFhbAwQAAAAA', 0, 1, '1532503198', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEi8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN3E1aWd1UDBiRy0xMDAwME0wM3kAAgSeJFhbAwQAAAAA', 'http://weixin.qq.com/q/027q5iguP0bG-10000M03y', 0),
(31, 'spread', 9, 'gQGH8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRW5zbGhfUDBiRy0xeW9Yck5yMVcAAgSYrnNbAwQAjScA', 1536899992, 1, '1532503198', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGH8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRW5zbGhfUDBiRy0xeW9Yck5yMVcAAgSYrnNbAwQAjScA', 'http://weixin.qq.com/q/02Enslh_P0bG-1yoXrNr1W', 0),
(32, 'spread', 20, 'gQEY8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM1BRbWgzUDBiRy0xeG1vcXhyMWwAAgRWi3JbAwQAjScA', 1536825430, 1, '1532568916', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEY8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM1BRbWgzUDBiRy0xeG1vcXhyMWwAAgRWi3JbAwQAjScA', 'http://weixin.qq.com/q/023PQmh3P0bG-1xmoqxr1l', 0),
(33, 'spread', 19, 'gQGC8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVGExcWh6UDBiRy0xMDAwMDAwM2cAAgRUJVlbAwQAAAAA', 0, 1, '1532568916', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGC8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVGExcWh6UDBiRy0xMDAwMDAwM2cAAgRUJVlbAwQAAAAA', 'http://weixin.qq.com/q/02Ta1qhzP0bG-10000003g', 0),
(34, 'spread', 17, 'gQGl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFh0QmhGUDBiRy0xMDAwME0wM1AAAgRUJVlbAwQAAAAA', 0, 1, '1532568916', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFh0QmhGUDBiRy0xMDAwME0wM1AAAgRUJVlbAwQAAAAA', 'http://weixin.qq.com/q/02TXtBhFP0bG-10000M03P', 0),
(35, 'spread', 15, 'gQHE8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQWJ2YWdyUDBiRy0xeVlLNmhyMWMAAgS8oV5bAwQAjScA', 1535520444, 1, '1532568917', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHE8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQWJ2YWdyUDBiRy0xeVlLNmhyMWMAAgS8oV5bAwQAjScA', 'http://weixin.qq.com/q/02AbvagrP0bG-1yYK6hr1c', 1),
(36, 'spread', 27, 'gQFR8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1dVWDlnQm5sWlcwY1E3ZjRsbW4zAAIEOPUXWAMEAAAAAA==', 0, 1, '1532662361', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFR8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1dVWDlnQm5sWlcwY1E3ZjRsbW4zAAIEOPUXWAMEAAAAAA==', 'http://weixin.qq.com/q/WUX9gBnlZW0cQ7f4lmn3', 0),
(37, 'spread', 25, 'gQFV8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLVNBQmdVUDBiRy0xMDAwMGcwM2QAAgRZklpbAwQAAAAA', 0, 1, '1532662361', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFV8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLVNBQmdVUDBiRy0xMDAwMGcwM2QAAgRZklpbAwQAAAAA', 'http://weixin.qq.com/q/02-SABgUP0bG-10000g03d', 0),
(38, 'spread', 23, 'gQHL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnljdGdVUDBiRy0xMDAwME0wM3QAAgRaklpbAwQAAAAA', 0, 1, '1532662362', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnljdGdVUDBiRy0xMDAwME0wM3QAAgRaklpbAwQAAAAA', 'http://weixin.qq.com/q/02FyctgUP0bG-10000M03t', 0),
(39, 'spread', 21, 'gQEo8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyamtFamdqUDBiRy0xMDAwMDAwMzEAAgRaklpbAwQAAAAA', 0, 1, '1532662362', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEo8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyamtFamdqUDBiRy0xMDAwMDAwMzEAAgRaklpbAwQAAAAA', 'http://weixin.qq.com/q/02jkEjgjP0bG-100000031', 0),
(40, 'spread', 38, 'gQED8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL05FWDlqV3JsRVcxb1VOcUJsbW4zAAIEaNkiWAMEAAAAAA==', 0, 1, '1532915675', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQED8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL05FWDlqV3JsRVcxb1VOcUJsbW4zAAIEaNkiWAMEAAAAAA==', 'http://weixin.qq.com/q/NEX9jWrlEW1oUNqBlmn3', 0),
(41, 'spread', 37, 'gQGE8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BVVXBUTmZsMlcyZ3ZVdGNRbW4zAAIEpsgiWAMEAAAAAA==', 0, 1, '1532915676', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGE8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BVVXBUTmZsMlcyZ3ZVdGNRbW4zAAIEpsgiWAMEAAAAAA==', 'http://weixin.qq.com/q/pUUpTNfl2W2gvUtcQmn3', 0),
(42, 'spread', 34, 'gQHL8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3ZVV001UWZsZG0wUDNGUC01Mm4zAAIEkaEiWAMEAAAAAA==', 0, 1, '1532915676', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHL8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3ZVV001UWZsZG0wUDNGUC01Mm4zAAIEkaEiWAMEAAAAAA==', 'http://weixin.qq.com/q/vUWM5Qfldm0P3FP-52n3', 0),
(43, 'spread', 33, 'gQEX8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL09VWE1hOExsS20xVEU5ZHRwMm4zAAIE7KAiWAMEAAAAAA==', 0, 1, '1532915676', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEX8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL09VWE1hOExsS20xVEU5ZHRwMm4zAAIE7KAiWAMEAAAAAA==', 'http://weixin.qq.com/q/OUXMa8LlKm1TE9dtp2n3', 0),
(44, 'spread', 32, 'gQEL8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2FVVnlrLWZsa0czcGpvZVJHV24zAAIEq1AcWAMEAAAAAA==', 0, 1, '1532915676', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEL8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2FVVnlrLWZsa0czcGpvZVJHV24zAAIEq1AcWAMEAAAAAA==', 'http://weixin.qq.com/q/aUVyk-flkG3pjoeRGWn3', 0),
(45, 'spread', 31, 'gQGk8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3MwVnBvcG5saFczOEMxMnVBbW4zAAIEKJoZWAMEAAAAAA==', 0, 1, '1532915676', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGk8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3MwVnBvcG5saFczOEMxMnVBbW4zAAIEKJoZWAMEAAAAAA==', 'http://weixin.qq.com/q/s0VpopnlhW38C12uAmn3', 0),
(46, 'spread', 30, 'gQF68DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2drWGhQQlhsZm0wSEtHeERpbW4zAAIEEpgZWAMEAAAAAA==', 0, 1, '1532915676', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF68DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2drWGhQQlhsZm0wSEtHeERpbW4zAAIEEpgZWAMEAAAAAA==', 'http://weixin.qq.com/q/gkXhPBXlfm0HKGxDimn3', 0),
(47, 'spread', 44, 'gQE08ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1UwV2Nna0xsZTIwQ0VMMkY5Mm4zAAIE9_8zWAMEAAAAAA==', 0, 1, '1533036102', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE08ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1UwV2Nna0xsZTIwQ0VMMkY5Mm4zAAIE9_8zWAMEAAAAAA==', 'http://weixin.qq.com/q/U0WcgkLle20CEL2F92n3', 0),
(48, 'spread', 43, 'gQFl8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzVrWHhteS1sRTIxcTBRaVptbW4zAAIE7ugzWAMEAAAAAA==', 0, 1, '1533036102', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFl8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzVrWHhteS1sRTIxcTBRaVptbW4zAAIE7ugzWAMEAAAAAA==', 'http://weixin.qq.com/q/5kXxmy-lE21q0QiZmmn3', 0),
(49, 'spread', 42, 'gQEZ8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1JFVzdVcXJsU20welA2cEQwR24zAAIEgOQzWAMEAAAAAA==', 0, 1, '1533036102', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEZ8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1JFVzdVcXJsU20welA2cEQwR24zAAIEgOQzWAMEAAAAAA==', 'http://weixin.qq.com/q/REW7UqrlSm0zP6pD0Gn3', 0),
(50, 'spread', 41, 'gQGb8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BrV3l3X1RsSzIxU0FraTYyV24zAAIEnx8lWAMEAAAAAA==', 0, 1, '1533036102', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGb8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BrV3l3X1RsSzIxU0FraTYyV24zAAIEnx8lWAMEAAAAAA==', 'http://weixin.qq.com/q/pkWyw_TlK21SAki62Wn3', 0),
(51, 'spread', 40, 'gQFy8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2VVV182NS1sV0cwaGpwZnQxV24zAAIEzkokWAMEAAAAAA==', 0, 1, '1533036102', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFy8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2VVV182NS1sV0cwaGpwZnQxV24zAAIEzkokWAMEAAAAAA==', 'http://weixin.qq.com/q/eUW_65-lWG0hjpft1Wn3', 0),
(52, 'spread', 39, 'gQFE8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzkwVVcyMWZsOG0yTF9CbmZmV24zAAIEsN8iWAMEAAAAAA==', 0, 1, '1533036102', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFE8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzkwVVcyMWZsOG0yTF9CbmZmV24zAAIEsN8iWAMEAAAAAA==', 'http://weixin.qq.com/q/90UW21fl8m2L_BnffWn3', 0),
(53, 'spread', 57, 'gQEP8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLW5JSmhOUDBiRy0xMDAwMDAwM2MAAgS7oEZYAwQAAAAA', 0, 1, '1533120232', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEP8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLW5JSmhOUDBiRy0xMDAwMDAwM2MAAgS7oEZYAwQAAAAA', 'http://weixin.qq.com/q/02-nIJhNP0bG-10000003c', 0),
(54, 'spread', 55, 'gQEw8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS1dqZGd4UDBiRy0xMDAwMHcwM2sAAgRFPEVYAwQAAAAA', 0, 1, '1533120232', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEw8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS1dqZGd4UDBiRy0xMDAwMHcwM2sAAgRFPEVYAwQAAAAA', 'http://weixin.qq.com/q/02KWjdgxP0bG-10000w03k', 0),
(55, 'spread', 54, 'gQHI8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAySlRVT2h3UDBiRy0xMDAwMDAwMzAAAgReO0VYAwQAAAAA', 0, 1, '1533120233', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHI8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAySlRVT2h3UDBiRy0xMDAwMDAwMzAAAgReO0VYAwQAAAAA', 'http://weixin.qq.com/q/02JTUOhwP0bG-100000030', 0),
(56, 'spread', 50, 'gQGr8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzdFWEJfM25sV1cwZzdRS0RxbW4zAAIEL8c3WAMEAAAAAA==', 0, 1, '1533120233', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGr8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzdFWEJfM25sV1cwZzdRS0RxbW4zAAIEL8c3WAMEAAAAAA==', 'http://weixin.qq.com/q/7EXB_3nlWW0g7QKDqmn3', 0),
(57, 'spread', 49, 'gQHX8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0ZVV2FoeS1sWUcwWlB2dWQ4V24zAAIE0o83WAMEAAAAAA==', 0, 1, '1533120233', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHX8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0ZVV2FoeS1sWUcwWlB2dWQ4V24zAAIE0o83WAMEAAAAAA==', 'http://weixin.qq.com/q/FUWahy-lYG0ZPvud8Wn3', 0),
(58, 'spread', 48, 'gQF78DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2tFV0tMT3ZsWkcwZDFINGk0V24zAAIETPQ0WAMEAAAAAA==', 0, 1, '1533120233', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF78DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2tFV0tMT3ZsWkcwZDFINGk0V24zAAIETPQ0WAMEAAAAAA==', 'http://weixin.qq.com/q/kEWKLOvlZG0d1H4i4Wn3', 0),
(59, 'spread', 46, 'gQHo8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3lrV3paTTdsTm0xUDR5UUIyR24zAAIEk/IzWAMEAAAAAA==', 0, 1, '1533120233', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHo8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3lrV3paTTdsTm0xUDR5UUIyR24zAAIEk/IzWAMEAAAAAA==', 'http://weixin.qq.com/q/ykWzZM7lNm1P4yQB2Gn3', 0),
(60, 'spread', 64, 'gQHD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybWNXVWc5UDBiRy0xMDAwME0wMzIAAgQUY1JYAwQAAAAA', 0, 1, '1533264284', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybWNXVWc5UDBiRy0xMDAwME0wMzIAAgQUY1JYAwQAAAAA', 'http://weixin.qq.com/q/02mcWUg9P0bG-10000M032', 0),
(61, 'spread', 62, 'gQHl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVJwTmcyUDBiRy0xMDAwME0wM2gAAgRsr1BYAwQAAAAA', 0, 1, '1533264284', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVJwTmcyUDBiRy0xMDAwME0wM2gAAgRsr1BYAwQAAAAA', 'http://weixin.qq.com/q/02yRpNg2P0bG-10000M03h', 0),
(62, 'spread', 61, 'gQEs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZXVieWhwUDBiRy0xMDAwMGcwMzQAAgQRc09YAwQAAAAA', 0, 1, '1533264284', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZXVieWhwUDBiRy0xMDAwMGcwMzQAAgQRc09YAwQAAAAA', 'http://weixin.qq.com/q/02eubyhpP0bG-10000g034', 0),
(63, 'spread', 60, 'gQGL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmFkdWhzUDBiRy0xMDAwMGcwM2UAAgQx7ExYAwQAAAAA', 0, 1, '1533264284', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmFkdWhzUDBiRy0xMDAwMGcwM2UAAgQx7ExYAwQAAAAA', 'http://weixin.qq.com/q/02RaduhsP0bG-10000g03e', 0),
(64, 'spread', 59, 'gQFt8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeEVRWmh0UDBiRy0xMDAwMDAwM1oAAgS/5kdYAwQAAAAA', 0, 1, '1533264284', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFt8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeEVRWmh0UDBiRy0xMDAwMDAwM1oAAgS/5kdYAwQAAAAA', 'http://weixin.qq.com/q/02xEQZhtP0bG-10000003Z', 0),
(65, 'spread', 67, 'gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaHNxTWdyUDBiRy0xMDAwMDAwM00AAgRColhYAwQAAAAA', 0, 1, '1533295071', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaHNxTWdyUDBiRy0xMDAwMDAwM00AAgRColhYAwQAAAAA', 'http://weixin.qq.com/q/02hsqMgrP0bG-10000003M', 0),
(66, 'spread', 66, 'gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyME15emdNUDBiRy0xMDAwMGcwM3IAAgSWQ1VYAwQAAAAA', 0, 1, '1533295071', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyME15emdNUDBiRy0xMDAwMGcwM3IAAgSWQ1VYAwQAAAAA', 'http://weixin.qq.com/q/020MyzgMP0bG-10000g03r', 0),
(67, 'spread', 65, 'gQF/8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydmVWeWh4UDBiRy0xMDAwMGcwM2EAAgRKuFRYAwQAAAAA', 0, 1, '1533295072', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF/8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydmVWeWh4UDBiRy0xMDAwMGcwM2EAAgRKuFRYAwQAAAAA', 'http://weixin.qq.com/q/02veVyhxP0bG-10000g03a', 0),
(68, 'spread', 69, 'gQHg8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycm9WS2hIUDBiRy0xMDAwMGcwM3EAAgQ6q1hYAwQAAAAA', 0, 1, '1533539635', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHg8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycm9WS2hIUDBiRy0xMDAwMGcwM3EAAgQ6q1hYAwQAAAAA', 'http://weixin.qq.com/q/02roVKhHP0bG-10000g03q', 0),
(69, 'spread', 71, 'gQGD8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZEV5ZGhWUDBiRy0xMDAwME0wM0MAAgSM4GRYAwQAAAAA', 0, 1, '1533622617', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGD8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZEV5ZGhWUDBiRy0xMDAwME0wM0MAAgSM4GRYAwQAAAAA', 'http://weixin.qq.com/q/02dEydhVP0bG-10000M03C', 0),
(70, 'spread', 74, 'gQE28TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ24yTGhTUDBiRy0xMDAwME0wM1kAAgQCz2xYAwQAAAAA', 0, 1, '1533779476', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE28TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ24yTGhTUDBiRy0xMDAwME0wM1kAAgQCz2xYAwQAAAAA', 'http://weixin.qq.com/q/02gn2LhSP0bG-10000M03Y', 0),
(71, 'spread', 73, 'gQGF8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTlR4S2duUDBiRy0xMDAwMDAwM3EAAgTleGpYAwQAAAAA', 0, 1, '1533779476', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGF8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTlR4S2duUDBiRy0xMDAwMDAwM3EAAgTleGpYAwQAAAAA', 'http://weixin.qq.com/q/02NTxKgnP0bG-10000003q', 0),
(72, 'spread', 82, 'gQHe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ0haS2dpUDBiRy0xMDAwMGcwM2MAAgTRr9laAwQAAAAA', 0, 1, '1533863539', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ0haS2dpUDBiRy0xMDAwMGcwM2MAAgTRr9laAwQAAAAA', 'http://weixin.qq.com/q/02gHZKgiP0bG-10000g03c', 0),
(73, 'spread', 81, 'gQHa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydEljVmdYUDBiRy0xMDAwME0wM28AAgTSr9laAwQAAAAA', 0, 1, '1533863539', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydEljVmdYUDBiRy0xMDAwME0wM28AAgTSr9laAwQAAAAA', 'http://weixin.qq.com/q/02tIcVgXP0bG-10000M03o', 0),
(74, 'spread', 80, 'gQFr8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLUQ0bGg4UDBiRy0xMDAwMGcwM0gAAgTSr9laAwQAAAAA', 0, 1, '1533863539', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFr8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLUQ0bGg4UDBiRy0xMDAwMGcwM0gAAgTSr9laAwQAAAAA', 'http://weixin.qq.com/q/02-D4lh8P0bG-10000g03H', 0),
(75, 'spread', 79, 'gQHw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1Z0SmdvUDBiRy0xMDAwME0wM1UAAgTSr9laAwQAAAAA', 0, 1, '1533863539', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1Z0SmdvUDBiRy0xMDAwME0wM1UAAgTSr9laAwQAAAAA', 'http://weixin.qq.com/q/02OVtJgoP0bG-10000M03U', 0),
(76, 'spread', 77, 'gQFd8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycVNuemczUDBiRy0xMDAwME0wM3YAAgQ2sNlaAwQAAAAA', 0, 1, '1533863539', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFd8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycVNuemczUDBiRy0xMDAwME0wM3YAAgQ2sNlaAwQAAAAA', 'http://weixin.qq.com/q/02qSnzg3P0bG-10000M03v', 0),
(77, 'spread', 76, 'gQGZ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycW5JOWdtUDBiRy0xMDAwME0wMzkAAgTYhnVYAwQAAAAA', 0, 1, '1533863540', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGZ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycW5JOWdtUDBiRy0xMDAwME0wMzkAAgTYhnVYAwQAAAAA', 'http://weixin.qq.com/q/02qnI9gmP0bG-10000M039', 0),
(78, 'spread', 86, 'gQEF8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ080SGhoUDBiRy0xMDAwMHcwM3QAAgTPr9laAwQAAAAA', 0, 1, '1534145730', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEF8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ080SGhoUDBiRy0xMDAwMHcwM3QAAgTPr9laAwQAAAAA', 'http://weixin.qq.com/q/02CO4HhhP0bG-10000w03t', 0),
(79, 'spread', 93, 'gQEG8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWWR5TWdjUDBiRy0xMDAwMDAwM04AAgTMr9laAwQAAAAA', 0, 1, '1534232932', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEG8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWWR5TWdjUDBiRy0xMDAwMDAwM04AAgTMr9laAwQAAAAA', 'http://weixin.qq.com/q/02YdyMgcP0bG-10000003N', 0),
(80, 'spread', 92, 'gQHX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRE44bmdwUDBiRy0xMDAwMHcwM3MAAgTNr9laAwQAAAAA', 0, 1, '1534232932', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRE44bmdwUDBiRy0xMDAwMHcwM3MAAgTNr9laAwQAAAAA', 'http://weixin.qq.com/q/02DN8ngpP0bG-10000w03s', 0),
(81, 'spread', 91, 'gQG78DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMkpydWh1UDBiRy0xQ3V1cjFyMUMAAgSe0XNbAwQAjScA', 1536908958, 1, '1534232933', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG78DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMkpydWh1UDBiRy0xQ3V1cjFyMUMAAgSe0XNbAwQAjScA', 'http://weixin.qq.com/q/022JruhuP0bG-1Cuur1r1C', 0),
(82, 'spread', 90, 'gQEB8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYllPUmh1UDBiRy0xMDAwME0wM0kAAgTNr9laAwQAAAAA', 0, 1, '1534232933', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEB8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYllPUmh1UDBiRy0xMDAwME0wM0kAAgTNr9laAwQAAAAA', 'http://weixin.qq.com/q/02bYORhuP0bG-10000M03I', 0),
(83, 'spread', 89, 'gQGT8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydG82MmctUDBiRy0xMDAwMDAwMy0AAgTOr9laAwQAAAAA', 0, 1, '1534232933', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGT8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydG82MmctUDBiRy0xMDAwMDAwMy0AAgTOr9laAwQAAAAA', 'http://weixin.qq.com/q/02to62g-P0bG-10000003-', 0),
(84, 'spread', 88, 'gQGQ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWElybmh4UDBiRy0xMDAwMGcwM3cAAgTOr9laAwQAAAAA', 0, 1, '1534232933', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGQ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWElybmh4UDBiRy0xMDAwMGcwM3cAAgTOr9laAwQAAAAA', 'http://weixin.qq.com/q/02XIrnhxP0bG-10000g03w', 0),
(85, 'spread', 87, 'gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaV9Lc2hlUDBiRy0xMDAwMDAwM1IAAgTPr9laAwQAAAAA', 0, 1, '1534232933', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaV9Lc2hlUDBiRy0xMDAwMDAwM1IAAgTPr9laAwQAAAAA', 'http://weixin.qq.com/q/02i_KsheP0bG-10000003R', 0),
(86, 'spread', 85, 'gQF08jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMUQyY2h2UDBiRy0xMDAwMDAwM0IAAgTPr9laAwQAAAAA', 0, 1, '1534232934', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF08jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMUQyY2h2UDBiRy0xMDAwMDAwM0IAAgTPr9laAwQAAAAA', 'http://weixin.qq.com/q/021D2chvP0bG-10000003B', 0),
(87, 'spread', 84, 'gQEi8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZjZWVmdMUDBiRy0xMDAwMGcwM0gAAgTRr9laAwQAAAAA', 0, 1, '1534232934', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEi8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZjZWVmdMUDBiRy0xMDAwMGcwM0gAAgTRr9laAwQAAAAA', 'http://weixin.qq.com/q/02f6VVgLP0bG-10000g03H', 0),
(88, 'spread', 83, 'gQHK8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyd2pBUGdsUDBiRy0xMDAwME0wM3AAAgTRr9laAwQAAAAA', 0, 1, '1534232934', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHK8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyd2pBUGdsUDBiRy0xMDAwME0wM3AAAgTRr9laAwQAAAAA', 'http://weixin.qq.com/q/02wjAPglP0bG-10000M03p', 0),
(89, 'spread', 78, 'gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycURIeGdPUDBiRy0xMDAwME0wM3gAAgQ1sNlaAwQAAAAA', 0, 1, '1534232934', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycURIeGdPUDBiRy0xMDAwME0wM3gAAgQ1sNlaAwQAAAAA', 'http://weixin.qq.com/q/02qDHxgOP0bG-10000M03x', 0),
(90, 'spread', 75, 'gQG68DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnpRSWdQUDBiRy0xMDAwMHcwM28AAgR_g3VYAwQAAAAA', 0, 1, '1534232934', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG68DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnpRSWdQUDBiRy0xMDAwMHcwM28AAgR_g3VYAwQAAAAA', 'http://weixin.qq.com/q/02FzQIgPP0bG-10000w03o', 0),
(91, 'spread', 72, 'gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVVOVWdhUDBiRy0xMDAwMHcwM3QAAgQB8GRYAwQAAAAA', 0, 1, '1534239978', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVVOVWdhUDBiRy0xMDAwMHcwM3QAAgQB8GRYAwQAAAAA', 'http://weixin.qq.com/q/02yUNUgaP0bG-10000w03t', 0),
(92, 'spread', 70, 'gQGr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1FLVWhOUDBiRy0xMDAwMDAwMzcAAgQYr1hYAwQAAAAA', 0, 1, '1534239978', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1FLVWhOUDBiRy0xMDAwMDAwMzcAAgQYr1hYAwQAAAAA', 'http://weixin.qq.com/q/02OQKUhNP0bG-100000037', 0),
(93, 'spread', 68, 'gQE68TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYnd6NWhlUDBiRy0xMDAwMHcwMzYAAgTpqlhYAwQAAAAA', 0, 1, '1534239978', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE68TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYnd6NWhlUDBiRy0xMDAwMHcwMzYAAgTpqlhYAwQAAAAA', 'http://weixin.qq.com/q/02bwz5heP0bG-10000w036', 0),
(94, 'spread', 63, 'gQEr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU3M1RGhQUDBiRy0xMDAwMHcwM0IAAgTtDVJYAwQAAAAA', 0, 1, '1534239979', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU3M1RGhQUDBiRy0xMDAwMHcwM0IAAgTtDVJYAwQAAAAA', 'http://weixin.qq.com/q/02Ss5DhPP0bG-10000w03B', 0),
(95, 'spread', 58, 'gQF18TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydTBPaWdYUDBiRy0xMDAwMDAwM08AAgRHiUdYAwQAAAAA', 0, 1, '1534239979', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF18TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydTBPaWdYUDBiRy0xMDAwMDAwM08AAgRHiUdYAwQAAAAA', 'http://weixin.qq.com/q/02u0OigXP0bG-10000003O', 0),
(96, 'spread', 56, 'gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUDg3dGcxUDBiRy0xMDAwMGcwM08AAgTVPUVYAwQAAAAA', 0, 1, '1534239979', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUDg3dGcxUDBiRy0xMDAwMGcwM08AAgTVPUVYAwQAAAAA', 'http://weixin.qq.com/q/02P87tg1P0bG-10000g03O', 0),
(97, 'spread', 13, 'gQHs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydlJFdmd1UDBiRy0xMDAwMGcwM24AAgTupHJbAwQAAAAA', 0, 1, '1534239982', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydlJFdmd1UDBiRy0xMDAwMGcwM24AAgTupHJbAwQAAAAA', 'http://weixin.qq.com/q/02vREvguP0bG-10000g03n', 0),
(98, 'spread', 12, 'gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG9HQWg5UDBiRy0xMDAwMHcwM1oAAgTupHJbAwQAAAAA', 0, 1, '1534239983', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG9HQWg5UDBiRy0xMDAwMHcwM1oAAgTupHJbAwQAAAAA', 'http://weixin.qq.com/q/02poGAh9P0bG-10000w03Z', 0),
(99, 'spread', 94, 'gQEY8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU29hRWdHUDBiRy0xMDAwME0wM1YAAgTMr9laAwQAAAAA', 0, 1, '1534294997', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEY8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU29hRWdHUDBiRy0xMDAwME0wM1YAAgTMr9laAwQAAAAA', 'http://weixin.qq.com/q/02SoaEgGP0bG-10000M03V', 0),
(100, 'spread', 95, 'gQHW8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUjJUT2dSUDBiRy0xejdzcnhyMWMAAgTHj3NbAwQAjScA', 1536892103, 1, '1534295469', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHW8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUjJUT2dSUDBiRy0xejdzcnhyMWMAAgTHj3NbAwQAjScA', 'http://weixin.qq.com/q/02R2TOgRP0bG-1z7srxr1c', 2),
(101, 'spread', 45, 'gQEH8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0lrVUFGSm5sN20yWExNd2FhMm4zAAIEhPAzWAMEAAAAAA==', 0, 1, '1534298167', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEH8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0lrVUFGSm5sN20yWExNd2FhMm4zAAIEhPAzWAMEAAAAAA==', 'http://weixin.qq.com/q/IkUAFJnl7m2XLMwaa2n3', 0),
(102, 'spread', 97, 'gQGz8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWlHamdJUDBiRy0xMDAwMHcwM3kAAgTLr9laAwQAAAAA', 0, 1, '1534301384', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGz8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWlHamdJUDBiRy0xMDAwMHcwM3kAAgTLr9laAwQAAAAA', 'http://weixin.qq.com/q/02eiGjgIP0bG-10000w03y', 0),
(103, 'spread', 96, 'gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMEpCOWdoUDBiRy0xMDAwMHcwMzAAAgTLr9laAwQAAAAA', 0, 1, '1534301385', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMEpCOWdoUDBiRy0xMDAwMHcwMzAAAgTLr9laAwQAAAAA', 'http://weixin.qq.com/q/020JB9ghP0bG-10000w030', 0),
(104, 'spread', 98, 'gQEN8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM2RrVGdnUDBiRy0xMDAwMDAwM3IAAgTKr9laAwQAAAAA', 0, 1, '1534312887', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEN8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM2RrVGdnUDBiRy0xMDAwMDAwM3IAAgTKr9laAwQAAAAA', 'http://weixin.qq.com/q/023dkTggP0bG-10000003r', 0),
(105, 'spread', 18, 'gQG48TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRjQ5YmhfUDBiRy0xMDAwMGcwMzEAAgTEwXNbAwQAAAAA', 0, 1, '1534312900', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG48TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRjQ5YmhfUDBiRy0xMDAwMGcwMzEAAgTEwXNbAwQAAAAA', 'http://weixin.qq.com/q/02F49bh_P0bG-10000g031', 0),
(106, 'spread', 16, 'gQGw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWnZuMmhCUDBiRy0xMDAwMDAwMzIAAgTEwXNbAwQAAAAA', 0, 1, '1534312900', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWnZuMmhCUDBiRy0xMDAwMDAwMzIAAgTEwXNbAwQAAAAA', 'http://weixin.qq.com/q/02Zvn2hBP0bG-100000032', 0),
(107, 'spread', 14, 'gQEx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX2NUbWdTUDBiRy0xMDAwME0wM3EAAgTEwXNbAwQAAAAA', 0, 1, '1534312900', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX2NUbWdTUDBiRy0xMDAwME0wM3EAAgTEwXNbAwQAAAAA', 'http://weixin.qq.com/q/02_cTmgSP0bG-10000M03q', 0),
(108, 'spread', 24, 'gQFO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmJIX2dMUDBiRy0xMDAwMDAwM24AAgTd2XRbAwQAAAAA', 0, 1, '1534384605', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmJIX2dMUDBiRy0xMDAwMDAwM24AAgTd2XRbAwQAAAAA', 'http://weixin.qq.com/q/02RbH_gLP0bG-10000003n', 0),
(109, 'spread', 99, 'gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN2lyN2dmUDBiRy0xMDAwMGcwM1AAAgTVsdlaAwQAAAAA', 0, 1, '1534403314', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN2lyN2dmUDBiRy0xMDAwMGcwM1AAAgTVsdlaAwQAAAAA', 'http://weixin.qq.com/q/027ir7gfP0bG-10000g03P', 0),
(110, 'spread', 103, 'gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQTV0QWhGUDBiRy0xMDAwMGcwMzUAAgSPrQxbAwQAAAAA', 0, 1, '1534405969', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQTV0QWhGUDBiRy0xMDAwMGcwMzUAAgSPrQxbAwQAAAAA', 'http://weixin.qq.com/q/02A5tAhFP0bG-10000g035', 0),
(111, 'spread', 102, 'gQGJ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWZzUWdjUDBiRy0xMDAwME0wM2cAAgS62vtaAwQAAAAA', 0, 1, '1534405969', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGJ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWZzUWdjUDBiRy0xMDAwME0wM2cAAgS62vtaAwQAAAAA', 'http://weixin.qq.com/q/02efsQgcP0bG-10000M03g', 0),
(112, 'spread', 101, 'gQGp8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUlVQVGdfUDBiRy0xMDAwMHcwM00AAgRDht5aAwQAAAAA', 0, 1, '1534405969', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGp8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUlVQVGdfUDBiRy0xMDAwMHcwM00AAgRDht5aAwQAAAAA', 'http://weixin.qq.com/q/02RUPTg_P0bG-10000w03M', 0),
(113, 'spread', 100, 'gQG38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNWJycWhsUDBiRy0xMDAwMDAwM0kAAgRDht5aAwQAAAAA', 0, 1, '1534405970', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNWJycWhsUDBiRy0xMDAwMDAwM0kAAgRDht5aAwQAAAAA', 'http://weixin.qq.com/q/025brqhlP0bG-10000003I', 0),
(114, 'spread', 22, 'gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMHZfQ2hBUDBiRy0xMDAwME0wM2wAAgSNMXVbAwQAAAAA', 0, 1, '1534407053', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMHZfQ2hBUDBiRy0xMDAwME0wM2wAAgSNMXVbAwQAAAAA', 'http://weixin.qq.com/q/020v_ChAP0bG-10000M03l', 0),
(115, 'spread', 36, 'gQG18DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0NFWFg0RWpsUkcwOUlfYVR2R24zAAIEXrwiWAMEAAAAAA==', 0, 1, '1534407067', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG18DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0NFWFg0RWpsUkcwOUlfYVR2R24zAAIEXrwiWAMEAAAAAA==', 'http://weixin.qq.com/q/CEXX4EjlRG09I_aTvGn3', 0),
(116, 'spread', 35, 'gQEa8joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzhFVy1IN3JsVTIwcTFoNFQxR24zAAIEmKEiWAMEAAAAAA==', 0, 1, '1534407067', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEa8joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzhFVy1IN3JsVTIwcTFoNFQxR24zAAIEmKEiWAMEAAAAAA==', 'http://weixin.qq.com/q/8EW-H7rlU20q1h4T1Gn3', 0),
(117, 'spread', 29, 'gQF/8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL01rV1BrQ1BsRW0xckF0enQ1R24zAAIEy/YXWAMEAAAAAA==', 0, 1, '1534407067', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF/8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL01rV1BrQ1BsRW0xckF0enQ1R24zAAIEy/YXWAMEAAAAAA==', 'http://weixin.qq.com/q/MkWPkCPlEm1rAtzt5Gn3', 0),
(118, 'spread', 28, 'gQGN7zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3drV29sVmpsWFcwa2pDeUF3Mm4zAAIEYPYXWAMEAAAAAA==', 0, 1, '1534407067', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGN7zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3drV29sVmpsWFcwa2pDeUF3Mm4zAAIEYPYXWAMEAAAAAA==', 'http://weixin.qq.com/q/wkWolVjlXW0kjCyAw2n3', 0),
(119, 'spread', 26, 'gQEc8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycThwWmcwUDBiRy0xMDAwME0wM3AAAgSbMXVbAwQAAAAA', 0, 1, '1534407067', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEc8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycThwWmcwUDBiRy0xMDAwME0wM3AAAgSbMXVbAwQAAAAA', 'http://weixin.qq.com/q/02q8pZg0P0bG-10000M03p', 0),
(120, 'spread', 53, 'gQF08DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybGd2Y2dKUDBiRy0xMDAwMHcwM00AAgQkKUVYAwQAAAAA', 0, 1, '1534407079', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF08DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybGd2Y2dKUDBiRy0xMDAwMHcwM00AAgQkKUVYAwQAAAAA', 'http://weixin.qq.com/q/02lgvcgJP0bG-10000w03M', 0),
(121, 'spread', 104, 'gQGx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydVJpR2hzUDBiRy0xMDAwMGcwM3IAAgSPrQxbAwQAAAAA', 0, 1, '1534408904', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydVJpR2hzUDBiRy0xMDAwMGcwM3IAAgSPrQxbAwQAAAAA', 'http://weixin.qq.com/q/02uRiGhsP0bG-10000g03r', 0),
(122, 'spread', 108, 'gQE_8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMTdBdWdUUDBiRy0xMDAwME0wMzYAAgSL8wxbAwQAAAAA', 0, 1, '1534726889', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE_8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMTdBdWdUUDBiRy0xMDAwME0wMzYAAgSL8wxbAwQAAAAA', 'http://weixin.qq.com/q/0217AugTP0bG-10000M036', 0),
(123, 'spread', 107, 'gQH_8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTkFrX2dWUDBiRy0xMDAwME0wMzMAAgSX7QxbAwQAAAAA', 0, 1, '1534726889', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH_8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTkFrX2dWUDBiRy0xMDAwME0wMzMAAgSX7QxbAwQAAAAA', 'http://weixin.qq.com/q/02NAk_gVP0bG-10000M033', 0),
(124, 'spread', 106, 'gQEh8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXdtaWczUDBiRy0xMDAwMDAwM3MAAgSPrQxbAwQAAAAA', 0, 1, '1534726889', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEh8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXdtaWczUDBiRy0xMDAwMDAwM3MAAgSPrQxbAwQAAAAA', 'http://weixin.qq.com/q/021wmig3P0bG-10000003s', 0),
(125, 'spread', 105, 'gQGO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycHR2RWdoUDBiRy0xMDAwMHcwM2kAAgSPrQxbAwQAAAAA', 0, 1, '1534726890', 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycHR2RWdoUDBiRy0xMDAwMHcwM2kAAgSPrQxbAwQAAAAA', 'http://weixin.qq.com/q/02ptvEghP0bG-10000w03i', 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_reply`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_reply` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '微信关键字回复id',
  `key` varchar(64) NOT NULL COMMENT '关键字',
  `type` varchar(32) NOT NULL COMMENT '回复类型',
  `data` text NOT NULL COMMENT '回复数据',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0=不可用  1 =可用',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `hide` (`hide`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信关键字回复表' AUTO_INCREMENT=30 ;

--
-- 转存表中的数据 `eb_wechat_reply`
--

INSERT INTO `eb_wechat_reply` (`id`, `key`, `type`, `data`, `status`, `hide`) VALUES
(1, 'subscribe', 'text', '{"content":"\\u6b22\\u8fce\\u5173\\u6ce8\\u201cCRMEB\\u201d\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\\uff01\\n\\u540e\\u53f0\\u4f53\\u9a8c\\u5730\\u5740\\uff1ahttp:\\/\\/demo.crmeb.net\\n\\u8d26\\u53f7\\uff1ademo \\u5bc6\\u7801\\uff1acrmeb.com\\n\\u670d\\u52a1\\u7535\\u8bdd\\uff1a400-8888-794"}', 1, 1),
(9, 'default', 'text', '{"content":"\\u66f4\\u591a\\u54a8\\u8be2\\u8bf7\\u62e8\\u6253\\u70ed\\u7ebf\\u7535\\u8bdd\\uff1a400-8888-794"}', 1, 1),
(21, '源码', 'text', '{"content":"\\u672a\\u7ecf\\u8fc7\\u5546\\u4e1a\\u6388\\u6743\\uff0c\\u4e0d\\u5f97\\u8fdb\\u884c\\u51fa\\u79df\\u3001\\u51fa\\u552e\\u3001\\u62b5\\u62bc\\u6216\\u53d1\\u653e\\u5b50\\u8bb8\\u53ef\\u8bc1\\u3002\\n\\u4e0b\\u8f7d\\u5730\\u5740\\uff1a\\n\\u94fe\\u63a5\\uff1ahttps:\\/\\/pan.baidu.com\\/s\\/1eMOoxWHvN7KuQTDLhIJjAg \\u5bc6\\u7801\\uff1a55RR"}', 1, 0),
(20, '演示', 'text', '{"content":"\\u540e\\u53f0\\u6f14\\u793a\\u5730\\u5740\\uff1ahttp:\\/\\/demo.crmeb.net\\/admin\\n\\u6f14\\u793a\\u8d26\\u53f7\\uff1ademo\\n\\u5bc6\\u7801\\uff1acrmeb.com"}', 1, 0),
(24, '客户常见问题', 'text', '{"content":"http:\\/\\/shop.crmeb.net\\/wap\\/article\\/index\\/cid\\/4"}', 1, 0),
(25, '开票信息', 'text', '{"content":"\\u516c\\u53f8\\u540d\\u79f0\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u7eb3\\u7a0e\\u8bc6\\u522b\\u53f7\\uff1a9161010409666664X0\\n\\u5730\\u5740\\uff1a\\u897f\\u5b89\\u5e02\\u83b2\\u6e56\\u533a\\u9f99\\u6e56MOCO\\u56fd\\u9645\\u7b2c1\\u5e621\\u5355\\u514314\\u5c4211411\\u53f7\\u623f\\n\\u5f00\\u6237\\u884c\\u548c\\u8d26\\u53f7\\uff1a\\u6c11\\u751f\\u94f6\\u884c\\u897f\\u5927\\u8857\\u652f\\u884c 691040854"}', 1, 0),
(22, '微信配置', 'text', '{"content":"\\u6388\\u6743\\u63a5\\u53e3 :\\n\\n\\/wechat\\/index\\/serve\\n\\u652f\\u4ed8api\\u63a5\\u53e3 :\\n\\n\\/wap\\/my\\/\\n\\/wap\\/my\\/order\\/uni\\/\\n\\/wap\\/store\\/confirm_order\\/cartId\\/\\n\\/wap\\/store\\/combination_order\\/\\n\\u5982\\u679c\\u670d\\u52a1\\u5668\\u914d\\u7f6e\\u6ca1\\u6709\\u9690\\u85cfindex.php,\\u8bf7\\u5728\\u63a5\\u53e3\\u524d\\u52a0\\u4e0aindex.php\\n\\u4f8b\\u5982\\uff1ahttp:\\/\\/www.abc.com\\/index.php\\/wechat\\/index\\/serve\\n\\u6a21\\u677f\\u6d88\\u606f\\n\\nIT\\u79d1\\u6280 | \\u4e92\\u8054\\u7f51|\\u7535\\u5b50\\u5546\\u52a1\\nIT\\u79d1\\u6280 | IT\\u8f6f\\u4ef6\\u4e0e\\u670d\\u52a1"}', 1, 0),
(23, '帮助', 'text', '{"content":"\\u5fae\\u4fe1\\u914d\\u7f6e\\n\\u6f14\\u793a\\n\\u6e90\\u7801\\n\\u5ba2\\u670d\\u7535\\u8bdd\\uff1a400-8888-794"}', 1, 0),
(26, '对公账户', 'text', '{"content":"\\u6cd5\\u4eba\\u8d26\\u53f7\\uff1a\\n\\u4e2d\\u56fd\\u519c\\u4e1a\\u94f6\\u884c\\u5361\\u53f7\\uff1a622848 0211 3030 15310  \\n\\u7528\\u6237\\u540d\\uff1a\\u8bb8\\u8363\\u8000  \\n\\u5f00\\u6237\\u884c\\u5730\\u5740\\uff1a\\u897f\\u5b89\\u52b3\\u52a8\\u8def\\u652f\\u884c\\n\\n\\n\\u516c\\u53f8\\u652f\\u4ed8\\u5b9d\\uff1a\\n\\u5e10\\u53f7\\uff1a1242777321@qq.com\\n\\u59d3\\u540d\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\n\\u5bf9\\u516c\\u8d26\\u6237\\uff1a\\n\\n\\u516c\\u53f8\\u540d\\u79f0\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u5f00\\u6237\\u884c\\uff1a\\u4e2d\\u56fd\\u6c11\\u751f\\u94f6\\u884c\\u80a1\\u4efd\\u6709\\u9650\\u516c\\u53f8\\u897f\\u5b89\\u897f\\u5927\\u8857\\u652f\\u884c\\n\\u5e10\\u53f7\\uff1a691040854 "}', 1, 0),
(27, '银行账号', 'text', '{"content":"\\u6cd5\\u4eba\\u8d26\\u53f7\\uff1a\\n\\u4e2d\\u56fd\\u519c\\u4e1a\\u94f6\\u884c\\u5361\\u53f7\\uff1a622848 0211 3030 15310  \\n\\u7528\\u6237\\u540d\\uff1a\\u8bb8\\u8363\\u8000  \\n\\u5f00\\u6237\\u884c\\u5730\\u5740\\uff1a\\u897f\\u5b89\\u52b3\\u52a8\\u8def\\u652f\\u884c\\n\\u516c\\u53f8\\u652f\\u4ed8\\u5b9d\\uff1a\\n\\u5e10\\u53f7\\uff1a1242777321@qq.com\\n\\u59d3\\u540d\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u5bf9\\u516c\\u8d26\\u6237\\uff1a\\n\\u516c\\u53f8\\u540d\\u79f0\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u5f00\\u6237\\u884c\\uff1a\\u4e2d\\u56fd\\u6c11\\u751f\\u94f6\\u884c\\u80a1\\u4efd\\u6709\\u9650\\u516c\\u53f8\\u897f\\u5b89\\u897f\\u5927\\u8857\\u652f\\u884c\\n\\u5e10\\u53f7\\uff1a691040854 "}', 1, 0),
(28, '案例', 'text', '{"content":"\\u832f\\u8336\\u9547\\u3001\\u7f8e\\u62fc\\u5427\\u3001"}', 1, 0),
(29, '公司电话', 'text', '{"content":"400-8888-794\\n029-65610380\\n029-68507850"}', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_template`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板id',
  `tempkey` char(50) NOT NULL COMMENT '模板编号',
  `name` char(100) NOT NULL COMMENT '模板名',
  `content` varchar(1000) NOT NULL COMMENT '回复内容',
  `tempid` char(100) DEFAULT NULL COMMENT '模板ID',
  `add_time` varchar(15) NOT NULL COMMENT '添加时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `tempkey` (`tempkey`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信模板' AUTO_INCREMENT=15 ;

--
-- 转存表中的数据 `eb_wechat_template`
--

INSERT INTO `eb_wechat_template` (`id`, `tempkey`, `name`, `content`, `tempid`, `add_time`, `status`) VALUES
(3, 'OPENTM200565259', '订单发货提醒', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n物流公司：{{keyword2.DATA}}\n物流单号：{{keyword3.DATA}}\n{{remark.DATA}}', 'RRsyuuWpCo81xCtfG-5qYnXXoeSQHY4mTVav0zzaZsM', '1515052638', 1),
(4, 'OPENTM413386489', '订单收货通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n订单状态：{{keyword2.DATA}}\n收货时间：{{keyword3.DATA}}\n商品详情：{{keyword4.DATA}}\n{{remark.DATA}}', 'caAhoWioDb2A8Ew1bTr4GTe6mdsDoM4kjp9XV5BC8hg', '1515052765', 1),
(5, 'OPENTM410119152', '退款进度通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n订单金额：{{keyword2.DATA}}\n下单时间：{{keyword3.DATA}}\n{{remark.DATA}}', '-WH6gUzezKnX9OTam9VrQEVyNWfr1bUhT6FRuBMotZw', '1515053049', 1),
(6, 'OPENTM405847076', '帐户资金变动提醒', '{{first.DATA}}\n变动类型：{{keyword1.DATA}}\n变动时间：{{keyword2.DATA}}\n变动金额：{{keyword3.DATA}}\n{{remark.DATA}}', 'dTNjE5QY6FzXyH0jbXEkNeNTgFQeMxdvqZRvDBqgatE', '1515053127', 1),
(7, 'OPENTM207707249', '订单发货提醒', '\n{{first.DATA}}\n商品明细：{{keyword1.DATA}}\n下单时间：{{keyword2.DATA}}\n配送地址：{{keyword3.DATA}}\n配送人：{{keyword4.DATA}}\n联系电话：{{keyword5.DATA}}\n{{remark.DATA}}', 'hC9PFuxOKq6u5kNZyl6VdHGgAuA6h5I3ztpuDk1ioAk', '1515053313', 1),
(8, 'OPENTM408237350', '服务进度提醒', '{{first.DATA}}\n服务类型：{{keyword1.DATA}}\n服务状态：{{keyword2.DATA}}\n服务时间：{{keyword3.DATA}}\n{{remark.DATA}}', '6Q7lusUXhX7HKcevHlEvMDC2qMuF2Yxtq52VTFNoNwg', '1515483915', 1),
(9, 'OPENTM204431262', '客服通知提醒', '{{first.DATA}}\n客户名称：{{keyword1.DATA}}\n客服类型：{{keyword2.DATA}}\n提醒内容：{{keyword3.DATA}}\n通知时间：{{keyword4.DATA}}\n{{remark.DATA}}', '6xvvsrYPGyTbYPPecVI1WihNpvWSAUsW1vBWGwL8Je0', '1515484354', 1),
(10, 'OPENTM407456411', '拼团成功通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n团购商品：{{keyword2.DATA}}\n{{remark.DATA}}', '8EI_FJ-h1UbIBYSXEm5BnV345fQs9s1eiELwlMUnbhk', '1520063823', 1),
(11, 'OPENTM401113750', '拼团失败通知', '{{first.DATA}}\n拼团商品：{{keyword1.DATA}}\n商品金额：{{keyword2.DATA}}\n退款金额：{{keyword3.DATA}}\n{{remark.DATA}}', 'BdO4l8H2p7OK8_f7Cx8DOqpE3D-mjDvjNdMeS05u2lI', '1520064059', 1),
(12, 'OPENTM205213550', '订单生成通知', '{{first.DATA}}\n时间：{{keyword1.DATA}}\n商品名称：{{keyword2.DATA}}\n订单号：{{keyword3.DATA}}\n{{remark.DATA}}', 'EY3j42ql2S6TBz5yK14iVjZqh4OSDOParZ9F_6e-56Q', '1528966701', 1),
(13, 'OPENTM207791277', '订单支付成功通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n支付金额：{{keyword2.DATA}}\n{{remark.DATA}}', 'UL7rLuzUIlYHNx5X_whUaAHWZWMmhZL35pUnJHgz8eI', '1528966759', 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_user`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '微信用户id',
  `unionid` varchar(30) DEFAULT NULL COMMENT '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段',
  `openid` varchar(30) DEFAULT NULL COMMENT '用户的标识，对当前公众号唯一',
  `routine_openid` varchar(32) DEFAULT NULL COMMENT '小程序唯一身份ID',
  `nickname` varchar(64) NOT NULL COMMENT '用户的昵称',
  `headimgurl` varchar(256) NOT NULL COMMENT '用户头像',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `city` varchar(64) NOT NULL COMMENT '用户所在城市',
  `language` varchar(64) NOT NULL COMMENT '用户的语言，简体中文为zh_CN',
  `province` varchar(64) NOT NULL COMMENT '用户所在省份',
  `country` varchar(64) NOT NULL COMMENT '用户所在国家',
  `remark` varchar(256) DEFAULT NULL COMMENT '公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注',
  `groupid` smallint(5) unsigned DEFAULT '0' COMMENT '用户所在的分组ID（兼容旧的用户分组接口）',
  `tagid_list` varchar(256) DEFAULT NULL COMMENT '用户被打上的标签ID列表',
  `subscribe` tinyint(3) unsigned DEFAULT '1' COMMENT '用户是否订阅该公众号标识',
  `subscribe_time` int(10) unsigned DEFAULT NULL COMMENT '关注公众号时间',
  `add_time` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  `stair` int(11) unsigned DEFAULT NULL COMMENT '一级推荐人',
  `second` int(11) unsigned DEFAULT NULL COMMENT '二级推荐人',
  `order_stair` int(11) DEFAULT NULL COMMENT '一级推荐人订单',
  `order_second` int(11) unsigned DEFAULT NULL COMMENT '二级推荐人订单',
  `now_money` int(11) unsigned DEFAULT NULL COMMENT '佣金',
  `session_key` varchar(32) DEFAULT NULL COMMENT '小程序用户会话密匙',
  `user_type` varchar(32) DEFAULT 'wechat' COMMENT '用户类型',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `openid` (`openid`) USING BTREE,
  KEY `groupid` (`groupid`) USING BTREE,
  KEY `subscribe_time` (`subscribe_time`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `subscribe` (`subscribe`) USING BTREE,
  KEY `unionid` (`unionid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信用户表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_wechat_user`
--

INSERT INTO `eb_wechat_user` (`uid`, `unionid`, `openid`, `routine_openid`, `nickname`, `headimgurl`, `sex`, `city`, `language`, `province`, `country`, `remark`, `groupid`, `tagid_list`, `subscribe`, `subscribe_time`, `add_time`, `stair`, `second`, `order_stair`, `order_second`, `now_money`, `session_key`, `user_type`) VALUES
(1, '', 'odbx_0X9rjARwCMUY6xqvJBMuWA8', NULL, '等风来，随风去', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLBaQPPnbg52bgibia1CZDruib1RwibHbBbnfxH1MUwbyz3G0Xub1LNX0ib5RFd7nZvo88gzHwib0OPibyfZQ/132', 1, '', 'zh_CN', '杜兰戈', '墨西哥', '', 0, '', 1, 1528858386, 1528859304, NULL, NULL, NULL, NULL, NULL, NULL, 'wechat');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
