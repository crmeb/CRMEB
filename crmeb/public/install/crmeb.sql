-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 
-- 服务器版本: 5.5.53
-- PHP 版本: 7.1.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `crmeb_v3_ky`
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章管理表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章分类表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_article_content`
--

CREATE TABLE IF NOT EXISTS `eb_article_content` (
  `nid` int(10) unsigned NOT NULL COMMENT '文章id',
  `content` text NOT NULL COMMENT '文章内容',
  UNIQUE KEY `nid` (`nid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章内容表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_cache`
--

CREATE TABLE IF NOT EXISTS `eb_cache` (
  `key` varchar(32) NOT NULL,
  `result` text COMMENT '缓存数据',
  `add_time` int(10) DEFAULT NULL COMMENT '缓存时间',
  PRIMARY KEY (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信缓存表';

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='快递公司表' AUTO_INCREMENT=426 ;

--
-- 转存表中的数据 `eb_express`
--

INSERT INTO `eb_express` (`id`, `code`, `name`, `sort`, `is_show`) VALUES
(1, 'LIMINWL', '利民物流', 1, 0),
(2, 'XINTIAN', '鑫天顺物流', 1, 0),
(3, 'henglu', '恒路物流', 1, 0),
(4, 'klwl', '康力物流', 1, 0),
(5, 'meiguo', '美国快递', 1, 0),
(6, 'a2u', 'A2U速递', 1, 0),
(7, 'benteng', '奔腾物流', 1, 0),
(8, 'ahdf', '德方物流', 1, 0),
(9, 'timedg', '万家通', 1, 0),
(10, 'ztong', '智通物流', 1, 0),
(11, 'xindan', '新蛋物流', 1, 0),
(12, 'bgpyghx', '挂号信', 1, 0),
(13, 'XFHONG', '鑫飞鸿物流快递', 1, 0),
(14, 'ALP', '阿里物流', 1, 0),
(15, 'BFWL', '滨发物流', 1, 0),
(16, 'SJWL', '宋军物流', 1, 0),
(17, 'SHUNFAWL', '顺发物流', 1, 0),
(18, 'TIANHEWL', '天河物流', 1, 0),
(19, 'YBWL', '邮联物流', 1, 0),
(20, 'SWHY', '盛旺货运', 1, 0),
(21, 'TSWL', '汤氏物流', 1, 0),
(22, 'YUANYUANWL', '圆圆物流', 1, 0),
(23, 'BALIANGWL', '八梁物流', 1, 0),
(24, 'ZGWL', '振刚物流', 1, 0),
(25, 'JIAYU', '佳宇物流', 1, 0),
(26, 'SHHX', '昊昕物流', 1, 0),
(27, 'ande', '安得物流', 1, 0),
(28, 'ppbyb', '贝邮宝', 1, 0),
(29, 'dida', '递达快递', 1, 0),
(30, 'jppost', '日本邮政', 1, 0),
(31, 'intmail', '中国邮政', 96, 0),
(32, 'HENGCHENGWL', '恒诚物流', 1, 0),
(33, 'HENGFENGWL', '恒丰物流', 1, 0),
(34, 'gdems', '广东ems快递', 1, 0),
(35, 'xlyt', '祥龙运通', 1, 0),
(36, 'gjbg', '国际包裹', 1, 0),
(37, 'uex', 'UEX', 1, 0),
(38, 'singpost', '新加坡邮政', 1, 0),
(39, 'guangdongyouzhengwuliu', '广东邮政', 1, 0),
(40, 'bht', 'BHT', 1, 0),
(41, 'cces', 'CCES快递', 1, 0),
(42, 'cloudexpress', 'CE易欧通国际速递', 1, 0),
(43, 'dasu', '达速物流', 1, 0),
(44, 'pfcexpress', '皇家物流', 1, 0),
(45, 'hjs', '猴急送', 1, 0),
(46, 'huilian', '辉联物流', 1, 0),
(47, 'huanqiu', '环球速运', 1, 0),
(48, 'huada', '华达快运', 1, 0),
(49, 'htwd', '华通务达物流', 1, 0),
(50, 'hipito', '海派通', 1, 0),
(51, 'hqtd', '环球通达', 1, 0),
(52, 'airgtc', '航空快递', 1, 0),
(53, 'haoyoukuai', '好又快物流', 1, 0),
(54, 'hanrun', '韩润物流', 1, 0),
(55, 'ccd', '河南次晨达', 1, 0),
(56, 'hfwuxi', '和丰同城', 1, 0),
(57, 'Sky', '荷兰', 1, 0),
(58, 'hongxun', '鸿讯物流', 1, 0),
(59, 'hongjie', '宏捷国际物流', 1, 0),
(60, 'httx56', '汇通天下物流', 1, 0),
(61, 'lqht', '恒通快递', 1, 0),
(62, 'jinguangsudikuaijian', '京广速递快件', 1, 0),
(63, 'junfengguoji', '骏丰国际速递', 1, 0),
(64, 'jiajiatong56', '佳家通', 1, 0),
(65, 'jrypex', '吉日优派', 1, 0),
(66, 'jinchengwuliu', '锦程国际物流', 1, 0),
(67, 'jgwl', '景光物流', 1, 0),
(68, 'pzhjst', '急顺通', 1, 0),
(69, 'ruexp', '捷网俄全通', 1, 0),
(70, 'jmjss', '金马甲', 1, 0),
(71, 'lanhu', '蓝弧快递', 1, 0),
(72, 'ltexp', '乐天速递', 1, 0),
(73, 'lutong', '鲁通快运', 1, 0),
(74, 'ledii', '乐递供应链', 1, 0),
(75, 'lundao', '论道国际物流', 1, 0),
(76, 'mailikuaidi', '麦力快递', 1, 0),
(77, 'mchy', '木春货运', 1, 0),
(78, 'meiquick', '美快国际物流', 1, 0),
(79, 'valueway', '美通快递', 1, 0),
(80, 'nuoyaao', '偌亚奥国际', 1, 0),
(81, 'euasia', '欧亚专线', 1, 0),
(82, 'pca', '澳大利亚PCA快递', 1, 0),
(83, 'pingandatengfei', '平安达腾飞', 1, 0),
(84, 'pjbest', '品骏快递', 1, 0),
(85, 'qbexpress', '秦邦快运', 1, 0),
(86, 'quanxintong', '全信通快递', 1, 0),
(87, 'quansutong', '全速通国际快递', 1, 0),
(88, 'qinyuan', '秦远物流', 1, 0),
(89, 'qichen', '启辰国际物流', 1, 0),
(90, 'quansu', '全速快运', 1, 0),
(91, 'qzx56', '全之鑫物流', 1, 0),
(92, 'qskdyxgs', '千顺快递', 1, 0),
(93, 'runhengfeng', '全时速运', 1, 0),
(94, 'rytsd', '日益通速递', 1, 0),
(95, 'ruidaex', '瑞达国际速递', 1, 0),
(96, 'shiyun', '世运快递', 1, 0),
(97, 'sfift', '十方通物流', 1, 0),
(98, 'stkd', '顺通快递', 1, 0),
(99, 'bgn', '布谷鸟快递', 1, 0),
(100, 'jiahuier', '佳惠尔快递', 1, 0),
(101, 'pingyou', '小包', 1, 0),
(102, 'yumeijie', '誉美捷快递', 1, 0),
(103, 'meilong', '美龙快递', 1, 0),
(104, 'guangtong', '广通速递', 1, 0),
(105, 'STARS', '星晨急便', 1, 0),
(106, 'NANHANG', '中国南方航空股份有限公司', 1, 0),
(107, 'lanbiao', '蓝镖快递', 1, 0),
(109, 'baotongda', '宝通达物流', 1, 0),
(110, 'dashun', '大顺物流', 1, 0),
(111, 'dada', '大达物流', 1, 0),
(112, 'fangfangda', '方方达物流', 1, 0),
(113, 'hebeijianhua', '河北建华物流', 1, 0),
(114, 'haolaiyun', '好来运快递', 1, 0),
(115, 'jinyue', '晋越快递', 1, 0),
(116, 'kuaitao', '快淘快递', 1, 0),
(117, 'peixing', '陪行物流', 1, 0),
(118, 'hkpost', '香港邮政', 1, 0),
(119, 'ytfh', '一统飞鸿快递', 1, 0),
(120, 'zhongxinda', '中信达快递', 1, 0),
(121, 'zhongtian', '中天快运', 1, 0),
(122, 'zuochuan', '佐川急便', 1, 0),
(123, 'chengguang', '程光快递', 1, 0),
(124, 'cszx', '城市之星', 1, 0),
(125, 'chuanzhi', '传志快递', 1, 0),
(126, 'feibao', '飞豹快递', 1, 0),
(127, 'huiqiang', '汇强快递', 1, 0),
(128, 'lejiedi', '乐捷递', 1, 0),
(129, 'lijisong', '成都立即送快递', 1, 0),
(130, 'minbang', '民邦速递', 1, 0),
(131, 'ocs', 'OCS国际快递', 1, 0),
(132, 'santai', '三态速递', 1, 0),
(133, 'saiaodi', '赛澳递', 1, 0),
(134, 'jingdong', '京东快递', 1, 0),
(135, 'zengyi', '增益快递', 1, 0),
(136, 'fanyu', '凡宇速递', 1, 0),
(137, 'fengda', '丰达快递', 1, 0),
(138, 'coe', '东方快递', 1, 0),
(139, 'ees', '百福东方快递', 1, 0),
(140, 'disifang', '递四方速递', 1, 0),
(141, 'rufeng', '如风达快递', 1, 0),
(142, 'changtong', '长通物流', 1, 0),
(143, 'chengshi100', '城市100快递', 1, 0),
(144, 'feibang', '飞邦物流', 1, 0),
(145, 'haosheng', '昊盛物流', 1, 0),
(146, 'yinsu', '音速速运', 1, 0),
(147, 'kuanrong', '宽容物流', 1, 0),
(148, 'tongcheng', '通成物流', 1, 0),
(149, 'tonghe', '通和天下物流', 1, 0),
(150, 'zhima', '芝麻开门', 1, 0),
(151, 'ririshun', '日日顺物流', 1, 0),
(152, 'anxun', '安迅物流', 1, 0),
(153, 'baiqian', '百千诚国际物流', 1, 0),
(154, 'chukouyi', '出口易', 1, 0),
(155, 'diantong', '店通快递', 1, 0),
(156, 'dajin', '大金物流', 1, 0),
(157, 'feite', '飞特物流', 1, 0),
(159, 'gnxb', '国内小包', 1, 0),
(160, 'huacheng', '华诚物流', 1, 0),
(161, 'huahan', '华翰物流', 1, 0),
(162, 'hengyu', '恒宇运通', 1, 0),
(163, 'huahang', '华航快递', 1, 0),
(164, 'jiuyi', '久易快递', 1, 0),
(165, 'jiete', '捷特快递', 1, 0),
(166, 'jingshi', '京世物流', 1, 0),
(167, 'kuayue', '跨越快递', 1, 0),
(168, 'mengsu', '蒙速快递', 1, 0),
(169, 'nanbei', '南北快递', 1, 0),
(171, 'pinganda', '平安达快递', 1, 0),
(172, 'ruifeng', '瑞丰速递', 1, 0),
(173, 'rongqing', '荣庆物流', 1, 0),
(174, 'suijia', '穗佳物流', 1, 0),
(175, 'simai', '思迈快递', 1, 0),
(176, 'suteng', '速腾快递', 1, 0),
(177, 'shengbang', '晟邦物流', 1, 0),
(178, 'suchengzhaipei', '速呈宅配', 1, 0),
(179, 'wuhuan', '五环速递', 1, 0),
(180, 'xingchengzhaipei', '星程宅配', 1, 0),
(181, 'yinjie', '顺捷丰达', 1, 0),
(183, 'yanwen', '燕文物流', 1, 0),
(184, 'zongxing', '纵行物流', 1, 0),
(185, 'aae', 'AAE快递', 1, 0),
(186, 'dhl', 'DHL快递', 1, 0),
(187, 'feihu', '飞狐快递', 1, 0),
(188, 'shunfeng', '顺丰速运', 92, 1),
(189, 'spring', '春风物流', 1, 0),
(190, 'yidatong', '易达通快递', 1, 0),
(191, 'PEWKEE', '彪记快递', 1, 0),
(192, 'PHOENIXEXP', '凤凰快递', 1, 0),
(193, 'CNGLS', 'GLS快递', 1, 0),
(194, 'BHTEXP', '华慧快递', 1, 0),
(195, 'B2B', '卡行天下', 1, 0),
(196, 'PEISI', '配思货运', 1, 0),
(197, 'SUNDAPOST', '上大物流', 1, 0),
(198, 'SUYUE', '苏粤货运', 1, 0),
(199, 'F5XM', '伍圆速递', 1, 0),
(200, 'GZWENJIE', '文捷航空速递', 1, 0),
(201, 'yuancheng', '远成物流', 1, 0),
(202, 'dpex', 'DPEX快递', 1, 0),
(203, 'anjie', '安捷快递', 1, 0),
(204, 'jldt', '嘉里大通', 1, 0),
(205, 'yousu', '优速快递', 1, 0),
(206, 'wanbo', '万博快递', 1, 0),
(207, 'sure', '速尔物流', 1, 0),
(208, 'sutong', '速通物流', 1, 0),
(209, 'JUNCHUANWL', '骏川物流', 1, 0),
(210, 'guada', '冠达快递', 1, 0),
(211, 'dsu', 'D速快递', 1, 0),
(212, 'LONGSHENWL', '龙胜物流', 1, 0),
(213, 'abc', '爱彼西快递', 1, 0),
(214, 'eyoubao', 'E邮宝', 1, 0),
(215, 'aol', 'AOL快递', 1, 0),
(216, 'jixianda', '急先达物流', 1, 0),
(217, 'haihong', '山东海红快递', 1, 0),
(218, 'feiyang', '飞洋快递', 1, 0),
(219, 'rpx', 'RPX保时达', 1, 0),
(220, 'zhaijisong', '宅急送', 1, 0),
(221, 'tiantian', '天天快递', 99, 0),
(222, 'yunwuliu', '云物流', 1, 0),
(223, 'jiuye', '九曳供应链', 1, 0),
(224, 'bsky', '百世快运', 1, 0),
(225, 'higo', '黑狗物流', 1, 0),
(226, 'arke', '方舟速递', 1, 0),
(227, 'zwsy', '中外速运', 1, 0),
(228, 'jxy', '吉祥邮', 1, 0),
(229, 'aramex', 'Aramex', 1, 0),
(230, 'guotong', '国通快递', 1, 0),
(231, 'jiayi', '佳怡物流', 1, 0),
(232, 'longbang', '龙邦快运', 1, 0),
(233, 'minhang', '民航快递', 1, 0),
(234, 'quanyi', '全一快递', 1, 0),
(235, 'quanchen', '全晨快递', 1, 0),
(236, 'usps', 'USPS快递', 1, 0),
(237, 'xinbang', '新邦物流', 1, 0),
(238, 'yuanzhi', '元智捷诚快递', 1, 0),
(239, 'zhongyou', '中邮物流', 1, 0),
(240, 'yuxin', '宇鑫物流', 1, 0),
(241, 'cnpex', '中环快递', 1, 0),
(242, 'shengfeng', '盛丰物流', 1, 0),
(243, 'yuantong', '圆通速递', 97, 1),
(244, 'jiayunmei', '加运美物流', 1, 0),
(245, 'ywfex', '源伟丰快递', 1, 0),
(246, 'xinfeng', '信丰物流', 1, 0),
(247, 'wanxiang', '万象物流', 1, 0),
(248, 'menduimen', '门对门', 1, 0),
(249, 'mingliang', '明亮物流', 1, 0),
(250, 'fengxingtianxia', '风行天下', 1, 0),
(251, 'gongsuda', '共速达物流', 1, 0),
(252, 'zhongtong', '中通快递', 100, 1),
(253, 'quanritong', '全日通快递', 1, 0),
(254, 'ems', 'EMS', 1, 1),
(255, 'wanjia', '万家物流', 1, 0),
(256, 'yuntong', '运通快递', 1, 0),
(257, 'feikuaida', '飞快达物流', 1, 0),
(258, 'haimeng', '海盟速递', 1, 0),
(259, 'zhongsukuaidi', '中速快件', 1, 0),
(260, 'yuefeng', '越丰快递', 1, 0),
(261, 'shenghui', '盛辉物流', 1, 0),
(262, 'datian', '大田物流', 1, 0),
(263, 'quanjitong', '全际通快递', 1, 0),
(264, 'longlangkuaidi', '隆浪快递', 1, 0),
(265, 'neweggozzo', '新蛋奥硕物流', 1, 0),
(266, 'shentong', '申通快递', 95, 1),
(267, 'haiwaihuanqiu', '海外环球', 1, 0),
(268, 'yad', '源安达快递', 1, 0),
(269, 'jindawuliu', '金大物流', 1, 0),
(270, 'sevendays', '七天连锁', 1, 0),
(271, 'tnt', 'TNT快递', 1, 0),
(272, 'huayu', '天地华宇物流', 1, 0),
(273, 'lianhaotong', '联昊通快递', 1, 0),
(274, 'nengda', '港中能达快递', 1, 0),
(275, 'LBWL', '联邦物流', 1, 0),
(276, 'ontrac', 'onTrac', 1, 0),
(277, 'feihang', '原飞航快递', 1, 0),
(278, 'bangsongwuliu', '邦送物流', 1, 0),
(279, 'huaxialong', '华夏龙物流', 1, 0),
(280, 'ztwy', '中天万运快递', 1, 0),
(281, 'fkd', '飞康达物流', 1, 0),
(282, 'anxinda', '安信达快递', 1, 0),
(283, 'quanfeng', '全峰快递', 1, 0),
(284, 'shengan', '圣安物流', 1, 0),
(285, 'jiaji', '佳吉物流', 1, 0),
(286, 'yunda', '韵达快运', 94, 0),
(287, 'ups', 'UPS快递', 1, 0),
(288, 'debang', '德邦物流', 1, 0),
(289, 'yafeng', '亚风速递', 1, 0),
(290, 'kuaijie', '快捷速递', 98, 0),
(291, 'huitong', '百世快递', 93, 0),
(293, 'aolau', 'AOL澳通速递', 1, 0),
(294, 'anneng', '安能物流', 1, 0),
(295, 'auexpress', '澳邮中国快运', 1, 0),
(296, 'exfresh', '安鲜达', 1, 0),
(297, 'bcwelt', 'BCWELT', 1, 0),
(298, 'youzhengguonei', '挂号信', 1, 0),
(299, 'xiaohongmao', '北青小红帽', 1, 0),
(300, 'lbbk', '宝凯物流', 1, 0),
(301, 'byht', '博源恒通', 1, 0),
(302, 'idada', '百成大达物流', 1, 0),
(303, 'baitengwuliu', '百腾物流', 1, 0),
(304, 'birdex', '笨鸟海淘', 1, 0),
(305, 'bsht', '百事亨通', 1, 0),
(306, 'dayang', '大洋物流快递', 1, 0),
(307, 'dechuangwuliu', '德创物流', 1, 0),
(308, 'donghanwl', '东瀚物流', 1, 0),
(309, 'dfpost', '达方物流', 1, 0),
(310, 'dongjun', '东骏快捷物流', 1, 0),
(311, 'dindon', '叮咚澳洲转运', 1, 0),
(312, 'dazhong', '大众佐川急便', 1, 0),
(313, 'decnlh', '德中快递', 1, 0),
(314, 'dekuncn', '德坤供应链', 1, 0),
(315, 'eshunda', '俄顺达', 1, 0),
(316, 'ewe', 'EWE全球快递', 1, 0),
(317, 'fedexuk', 'FedEx英国', 1, 0),
(318, 'fox', 'FOX国际速递', 1, 0),
(319, 'rufengda', '凡客如风达', 1, 0),
(320, 'fandaguoji', '颿达国际快递', 1, 0),
(321, 'hnfy', '飞鹰物流', 1, 0),
(322, 'flysman', '飞力士物流', 1, 0),
(323, 'sccod', '丰程物流', 1, 0),
(324, 'farlogistis', '泛远国际物流', 1, 0),
(325, 'gsm', 'GSM', 1, 0),
(326, 'gaticn', 'GATI快递', 1, 0),
(327, 'gts', 'GTS快递', 1, 0),
(328, 'gangkuai', '港快速递', 1, 0),
(329, 'gtsd', '高铁速递', 1, 0),
(330, 'tiandihuayu', '华宇物流', 1, 0),
(331, 'huangmajia', '黄马甲快递', 1, 0),
(332, 'ucs', '合众速递', 1, 0),
(333, 'huoban', '伙伴物流', 1, 0),
(334, 'nedahm', '红马速递', 1, 0),
(335, 'huiwen', '汇文配送', 1, 0),
(336, 'nmhuahe', '华赫物流', 1, 0),
(337, 'hangyu', '航宇快递', 1, 0),
(338, 'minsheng', '闽盛物流', 1, 0),
(339, 'riyu', '日昱物流', 1, 0),
(340, 'sxhongmajia', '山西红马甲', 1, 0),
(341, 'syjiahuier', '沈阳佳惠尔', 1, 0),
(342, 'shlindao', '上海林道货运', 1, 0),
(343, 'shunjiefengda', '顺捷丰达', 1, 0),
(344, 'subida', '速必达物流', 1, 0),
(345, 'bphchina', '速方国际物流', 1, 0),
(346, 'sendtochina', '速递中国', 1, 0),
(347, 'suning', '苏宁快递', 1, 0),
(348, 'sihaiet', '四海快递', 1, 0),
(349, 'tianzong', '天纵物流', 1, 0),
(350, 'chinatzx', '同舟行物流', 1, 0),
(351, 'nntengda', '腾达速递', 1, 0),
(352, 'sd138', '泰国138', 1, 0),
(353, 'tongdaxing', '通达兴物流', 1, 0),
(354, 'tlky', '天联快运', 1, 0),
(355, 'youshuwuliu', 'UC优速快递', 1, 0),
(356, 'ueq', 'UEQ快递', 1, 0),
(357, 'weitepai', '微特派快递', 1, 0),
(358, 'wtdchina', '威时沛运', 1, 0),
(359, 'wzhaunyun', '微转运', 1, 0),
(360, 'gswtkd', '万通快递', 1, 0),
(361, 'wotu', '渥途国际速运', 1, 0),
(362, 'xiyoute', '希优特快递', 1, 0),
(363, 'xilaikd', '喜来快递', 1, 0),
(364, 'xsrd', '鑫世锐达', 1, 0),
(365, 'xtb', '鑫通宝物流', 1, 0),
(366, 'xintianjie', '信天捷快递', 1, 0),
(367, 'xaetc', '西安胜峰', 1, 0),
(368, 'xianfeng', '先锋快递', 1, 0),
(369, 'sunspeedy', '新速航', 1, 0),
(370, 'xipost', '西邮寄', 1, 0),
(371, 'sinatone', '信联通', 1, 0),
(372, 'sunjex', '新杰物流', 1, 0),
(373, 'yundaexus', '韵达美国件', 1, 0),
(374, 'yxwl', '宇鑫物流', 1, 0),
(375, 'yitongda', '易通达', 1, 0),
(376, 'yiqiguojiwuliu', '一柒物流', 1, 0),
(377, 'yilingsuyun', '亿领速运', 1, 0),
(378, 'yujiawuliu', '煜嘉物流', 1, 0),
(379, 'gml', '英脉物流', 1, 0),
(380, 'leopard', '云豹国际货运', 1, 0),
(381, 'czwlyn', '云南中诚', 1, 0),
(382, 'sdyoupei', '优配速运', 1, 0),
(383, 'yongchang', '永昌物流', 1, 0),
(384, 'yufeng', '御风速运', 1, 0),
(385, 'yamaxunwuliu', '亚马逊物流', 1, 0),
(386, 'yousutongda', '优速通达', 1, 0),
(387, 'yishunhang', '亿顺航', 1, 0),
(388, 'yongwangda', '永旺达快递', 1, 0),
(389, 'ecmscn', '易满客', 1, 0),
(390, 'yingchao', '英超物流', 1, 0),
(391, 'edlogistics', '益递物流', 1, 0),
(392, 'yyexpress', '远洋国际', 1, 0),
(393, 'onehcang', '一号仓', 1, 0),
(394, 'ycgky', '远成快运', 1, 0),
(395, 'lineone', '一号线', 1, 0),
(396, 'ypsd', '壹品速递', 1, 0),
(397, 'vipexpress', '鹰运国际速递', 1, 0),
(398, 'el56', '易联通达物流', 1, 0),
(399, 'yyqc56', '一运全成物流', 1, 0),
(400, 'zhongtie', '中铁快运', 1, 0),
(401, 'ZTKY', '中铁物流', 1, 0),
(402, 'zzjh', '郑州建华快递', 1, 0),
(403, 'zhongruisudi', '中睿速递', 1, 0),
(404, 'zhongwaiyun', '中外运速递', 1, 0),
(405, 'zengyisudi', '增益速递', 1, 0),
(406, 'sujievip', '郑州速捷', 1, 0),
(407, 'zhichengtongda', '至诚通达快递', 1, 0),
(408, 'zhdwl', '众辉达物流', 1, 0),
(409, 'kuachangwuliu', '直邮易', 1, 0),
(410, 'topspeedex', '中运全速', 1, 0),
(411, 'otobv', '中欧快运', 1, 0),
(412, 'zsky123', '准实快运', 1, 0),
(413, 'donghong', '东红物流', 1, 0),
(414, 'kuaiyouda', '快优达速递', 1, 0),
(415, 'balunzhi', '巴伦支快递', 1, 0),
(416, 'hutongwuliu', '户通物流', 1, 0),
(417, 'xianchenglian', '西安城联速递', 1, 0),
(418, 'youbijia', '邮必佳', 1, 0),
(419, 'feiyuan', '飞远物流', 1, 0),
(420, 'chengji', '城际速递', 1, 0),
(421, 'huaqi', '华企快运', 1, 0),
(422, 'yibang', '一邦快递', 1, 0),
(423, 'citylink', 'CityLink快递', 1, 0),
(424, 'meixi', '美西快递', 1, 0),
(425, 'acs', 'ACS', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_access_token`
--

CREATE TABLE IF NOT EXISTS `eb_routine_access_token` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '小程序access_token表ID',
  `access_token` varchar(256) NOT NULL COMMENT 'openid',
  `stop_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='小程序access_token表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_routine_access_token`
--

INSERT INTO `eb_routine_access_token` (`id`, `access_token`, `stop_time`) VALUES
(1, '20_z3MAutcbznCSyQPqMVOQVRUktcvLYUXAAICpCMXkpu5rLoVnBB0u88rnJr1sWDJlwj-S6aVhmswmLdW86e9Bg2ugd3BOayE6ntY6FfckSXWgvW2y5N0bLkBxHpCjJH2bQpuvnmMIZr08G32hWSQfACAZVT', 1554809658);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_form_id`
--

CREATE TABLE IF NOT EXISTS `eb_routine_form_id` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表单ID表ID',
  `uid` int(11) DEFAULT '0' COMMENT '用户uid',
  `form_id` varchar(36) NOT NULL COMMENT '表单ID',
  `stop_time` int(11) unsigned DEFAULT NULL COMMENT '表单ID失效时间',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '状态1 未使用 2不能使用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='表单id表记录表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_qrcode`
--

CREATE TABLE IF NOT EXISTS `eb_routine_qrcode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '微信二维码ID',
  `third_type` varchar(32) NOT NULL COMMENT '二维码类型 spread(用户推广) product_spread(产品推广)',
  `third_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 0不可用 1可用',
  `add_time` varchar(255) DEFAULT NULL COMMENT '添加时间',
  `page` varchar(255) DEFAULT NULL COMMENT '小程序页面路径带参数',
  `qrcode_url` varchar(255) DEFAULT NULL COMMENT '小程序二维码路径',
  `url_time` int(11) unsigned DEFAULT NULL COMMENT '二维码添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='小程序二维码管理表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `tempkey` (`tempkey`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='微信模板' AUTO_INCREMENT=27 ;

--
-- 转存表中的数据 `eb_routine_template`
--

INSERT INTO `eb_routine_template` (`id`, `tempkey`, `name`, `content`, `tempid`, `add_time`, `status`) VALUES
(13, 'AT0007', '订单发货提醒', '订单号{{keyword1.DATA}}\n快递公司{{keyword2.DATA}}\n快递单号{{keyword3.DATA}}\n发货时间{{keyword4.DATA}}\n备注{{keyword5.DATA}}', 'fLsTVQmI2AE9q48BL58tffLRcyCnPLC9guSFrEHXbR8', '1534469928', 1),
(14, 'AT0787', '退款成功通知', '订单号{{keyword1.DATA}}\n退款时间{{keyword2.DATA}}\n退款金额{{keyword3.DATA}}\n退款方式{{keyword4.DATA}}\n备注{{keyword5.DATA}}', 'vgTj1nk1A1bVObCX0Fw3h9WbXvay1URcHMITjGW13cc', '1534469993', 1),
(15, 'AT0009', '订单支付成功通知', '单号{{keyword1.DATA}}\n下单时间{{keyword2.DATA}}\n订单状态{{keyword3.DATA}}\n支付金额{{keyword4.DATA}}\n支付方式{{keyword5.DATA}}', 'Rwt2QIz3zHbQGPXCH3E8nBneWzgFFBMiMI6EShTBLpI', '1534470043', 1),
(16, 'AT1173', '砍价成功通知', '商品名称{{keyword1.DATA}}\n砍价金额{{keyword2.DATA}}\n底价{{keyword3.DATA}}\n砍掉价格{{keyword4.DATA}}\n支付金额{{keyword5.DATA}}\n备注{{keyword6.DATA}}', 'qu3nRoV9lTmmvVWOdgf-2uz8NPT3w71cOx7UDJ5Uryo', '1534470085', 1),
(17, 'AT0036', '退款通知', '订单编号{{keyword1.DATA}}\n退款原因{{keyword2.DATA}}\n退款时间{{keyword3.DATA}}\n退款金额{{keyword4.DATA}}\n退款方式{{keyword5.DATA}}', 'gYYXx5nz8eWHPd_zp42EdR9UzhdynOo8Eq5GDb4dTrc', '1534470134', 1),
(19, 'AT2430', '拼团取消通知', '活动名称{{keyword1.DATA}}\n订单编号{{keyword2.DATA}}\n订单金额{{keyword3.DATA}}', 'OmzyMqsWJFn3L68DmEcZqV9FdSHrr2ThHYe6Czs8iaM', '1553910500', 1),
(20, 'AT0310', '拼团失败通知', '商品名称{{keyword1.DATA}}\n失败原因{{keyword2.DATA}}\n订单号{{keyword3.DATA}}\n开团时间{{keyword4.DATA}}\n退款金额{{keyword5.DATA}}', 'GRpTaZ1-tHoLvrfM-iEsBBbVSIXgh7bO17b7Z2AUpKM', '1553910844', 1),
(21, 'AT0051', '拼团成功通知', '活动名称{{keyword1.DATA}}\n团长{{keyword2.DATA}}\n成团时间{{keyword3.DATA}}\n拼团价{{keyword4.DATA}}', 'dlYqzgGLrVgRq-Ealw31wCy5PV-rGof-99OppSZhWxY', '1553911022', 1),
(22, 'AT0541', '开团成功提醒', '开团时间{{keyword1.DATA}}\n截至时间{{keyword2.DATA}}\n产品名称{{keyword3.DATA}}\n单号{{keyword4.DATA}}\n支付金额{{keyword5.DATA}}', 'E2NURfqs4MnciPB1YzvjtMDZCMfCCOHOwCdKFl7XigU', '1555133496', 1),
(23, 'AT0241', '确认收货通知', '订单编号{{keyword1.DATA}}\n商品详情{{keyword2.DATA}}\n支付金额{{keyword3.DATA}}\n确认收货时间{{keyword4.DATA}}', '4-DQPQMVsJfHjiKhEACz0ofcs_PHupy3xHTX1N1tzTo', '1557384781', 1),
(24, 'AT0329', '退款失败通知', '订单号{{keyword1.DATA}}\n商品名称{{keyword2.DATA}}\n退款金额{{keyword3.DATA}}\n失败原因{{keyword4.DATA}}', 'rlOEIDa0s6PMG4L_CkDLpBWCPf_mGci3z86UC5LmXCc', '1557384804', 1),
(25, 'AT1242', '提现失败通知', '失败原因{{keyword1.DATA}}\n提现金额{{keyword2.DATA}}\n提现方式{{keyword3.DATA}}\n审核时间{{keyword4.DATA}}', 'MsVoI7IV4CTFObS9XoJYTjMWPh8zjbNRK5-Z_II0Dnk', '1558918703', 1),
(26, 'AT1330', ' 提现成功通知', '提现金额{{keyword1.DATA}}\n提现状态{{keyword2.DATA}}\n审核时间{{keyword3.DATA}}\n提现方式{{keyword4.DATA}}', 'Twp4EWZTRUF2mps0J5Inw55_DpbNEfqqdKvfEx_ihos', '1558918767', 1);

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
  `combination_id` int(11) unsigned DEFAULT '0' COMMENT '拼团id',
  `seckill_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀产品ID',
  `bargain_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '砍价id',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`uid`) USING BTREE,
  KEY `goods_id` (`product_id`) USING BTREE,
  KEY `uid` (`uid`,`is_pay`) USING BTREE,
  KEY `uid_2` (`uid`,`is_del`) USING BTREE,
  KEY `uid_3` (`uid`,`is_new`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE,
  KEY `is_base` (`is_show`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品分类表' AUTO_INCREMENT=27 ;

--
-- 转存表中的数据 `eb_store_category`
--

INSERT INTO `eb_store_category` (`id`, `pid`, `cate_name`, `sort`, `pic`, `is_show`, `add_time`) VALUES
(1, 0, '热门推荐', 2, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3db8b933d92.jpg', 1, 1547205038),
(2, 1, '热门促销', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dba1366885.jpg', 1, 1547205055),
(3, 1, '折扣专区', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dc0ef27068.jpg', 1, 1547550363),
(4, 1, '新品上线', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dbc6a38fab.jpg', 1, 1553783295),
(6, 0, '居家生活', 1, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3db8b933d92.jpg', 1, 1553783609),
(7, 6, '床垫', 1, 'http://datong.crmeb.net/public/uploads/attach/2019/03/29/5c9de8b7c5cc5.png', 1, 1553784473),
(8, 6, '灯具', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/03/29/5c9def00c2882.png', 1, 1553784490),
(9, 0, '家电电器', 3, '', 1, 1553852206),
(10, 0, '手机数码', 4, '', 1, 1553852227),
(11, 0, '智能设备', 5, '', 1, 1553852259),
(12, 0, '影音音响', 6, '', 1, 1553852288),
(13, 0, '服饰鞋帽', 7, '', 1, 1553852314),
(14, 0, '餐厨厨房', 8, '', 1, 1553852353),
(15, 0, '洗护健康', 9, '', 1, 1553852370),
(16, 0, '日杂用品', 10, '', 1, 1553852390),
(17, 0, '出行交通', 11, '', 1, 1553852413),
(18, 0, '配件设备', 13, 'http://kaifa.crmeb.net/uploads/attach/2019/08/20190809/6801781ff4403bdbb6764f4182394729.png', 1, 1553852458),
(19, 6, '家具', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/03/29/5c9def5fa968c.png', 1, 1553854308),
(20, 6, '床品件套', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/03/29/5c9df11e13742.png', 1, 1553854755),
(21, 6, '家饰花卉', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/03/29/5c9df170010cb.png', 1, 1553854835),
(22, 6, '布艺软装', 0, 'http://datong.crmeb.net/public/uploads/attach/2019/03/29/5c9df1b8f0a7a.png', 1, 1553854908),
(23, 0, '测试', 0, 'http://kaifa.crmeb.net/uploads/attach/2019/08/20190805/887db984b5f1138e08e1d9b069af766e.jpg', 1, 1565158900),
(24, 23, '测试1', 0, 'http://kaifa.crmeb.net/uploads/attach/2019/08/20190805/887db984b5f1138e08e1d9b069af766e.jpg', 1, 1565158917),
(25, 0, '测试2', 0, '', 1, 1565158947),
(26, 25, '测试22', 0, 'http://kaifa.crmeb.net/uploads/attach/2019/08/20190805/887db984b5f1138e08e1d9b069af766e.jpg', 1, 1565158963);

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `state` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `coupon_time` (`coupon_time`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠券表' AUTO_INCREMENT=10 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE,
  KEY `start_time` (`start_time`,`end_time`) USING BTREE,
  KEY `remain_count` (`remain_count`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠券前台领取表' AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon_issue_user`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon_issue_user` (
  `uid` int(10) DEFAULT NULL COMMENT '领取优惠券用户ID',
  `issue_coupon_id` int(10) DEFAULT NULL COMMENT '优惠券前台领取ID',
  `add_time` int(10) DEFAULT NULL COMMENT '领取时间',
  UNIQUE KEY `uid` (`uid`,`issue_coupon_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券前台用户领取记录表';

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `end_time` (`end_time`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_fail` (`is_fail`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券发放记录表' AUTO_INCREMENT=1 ;

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
  `freight_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '运费金额',
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
  `combination_id` int(11) unsigned DEFAULT '0' COMMENT '拼团产品id0一般产品',
  `pink_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '拼团id 0没有拼团',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT '成本价',
  `seckill_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀产品ID',
  `bargain_id` int(11) unsigned DEFAULT '0' COMMENT '砍价id',
  `is_channel` tinyint(1) unsigned DEFAULT '0' COMMENT '支付渠道(0微信公众号1微信小程序)',
  `is_remind` tinyint(1) unsigned DEFAULT '0',
  `is_system_del` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单表' AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单购物详情表';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单操作记录表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product`
--

CREATE TABLE IF NOT EXISTS `eb_store_product` (
  `id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `mer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商户Id(0为总后台管理员创建,不为0的时候是商户后台创建)',
  `image` varchar(256) NOT NULL COMMENT '商品图片',
  `slider_image` varchar(2000) NOT NULL COMMENT '轮播图',
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
  `is_bargain` tinyint(1) unsigned DEFAULT NULL COMMENT '砍价状态 0未开启 1开启',
  `ficti` mediumint(11) DEFAULT '100' COMMENT '虚拟销量',
  `browse` int(11) DEFAULT '0' COMMENT '浏览量',
  `code_path` varchar(64) NOT NULL COMMENT '产品二维码地址(用户小程序海报)',
  `soure_link` varchar(255) DEFAULT '' COMMENT '淘宝京东1688类型',
  PRIMARY KEY (`id`) USING BTREE,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品表' AUTO_INCREMENT=20 ;

--
-- 转存表中的数据 `eb_store_product`
--

INSERT INTO `eb_store_product` (`id`, `mer_id`, `image`, `slider_image`, `store_name`, `store_info`, `keyword`, `cate_id`, `price`, `vip_price`, `ot_price`, `postage`, `unit_name`, `sort`, `sales`, `stock`, `is_show`, `is_hot`, `is_benefit`, `is_best`, `is_new`, `description`, `add_time`, `is_postage`, `is_del`, `mer_use`, `give_integral`, `cost`, `is_seckill`, `is_bargain`, `ficti`, `browse`, `code_path`, `soure_link`) VALUES
(1, 0, 'https://record-1251018669.cos.ap-chengdu.myqcloud.com/0ee17201905151741257691.png', '["http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dba1366885.jpg","http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dba4187461.jpg","http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3db9de2b73a.jpg"]', '无线吸尘器F8 玫瑰金礼盒版', '【年货节活动价1699元，领取吸尘器优惠券再减50元，到手价仅1649元】', '无线吸尘器', '2,7', '0.01', '0.00', '1699.00', '1.00', '件', 1, 293, 330, 0, 1, 0, 1, 1, '<p><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dbb137d656.jpeg"/><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dbb229e820.jpeg"/><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dbb3b37f84.jpeg"/><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dbb513b06f.jpeg"/></p>', 1547205504, 0, 0, 0, '1699.00', '100.00', 0, 0, 81, 0, '', NULL),
(2, 0, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dbc27c69c7.jpg', '["http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dbc27c69c7.jpg","http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dbc6a38fab.jpg"]', '智能马桶盖 AI版', '智能马桶盖 AI版', '智能马桶', '3,2', '0.01', '0.00', '1999.00', '5.00', '件', 0, 212, 938, 1, 1, 1, 1, 1, '<p><img src="http://kaifa.crmeb.net/uploads/config/image/20190725/8827bb36f160a1c33ae710883966063b.png"/></p>', 1547516202, 1, 0, 0, '1999.00', '1500.00', 0, 0, 20, 0, '', NULL),
(3, 0, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dc0ef27068.jpg', '["http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dc0ef27068.jpg","http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dc15ba1972.jpg"]', '智米加湿器 白色', '智米加湿器 白色', '加湿器', '3,2', '249.00', '0.00', '299.00', '0.00', '件', 0, 110, 3949, 1, 1, 1, 1, 1, '<p><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dc1730a0c0.jpg"/><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dc182bccac.jpg"/></p>', 1547551009, 1, 0, 0, '249.00', '10.00', 0, 0, 8, 0, '', NULL),
(4, 0, 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dc23646fff.jpg', '["http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dc23646fff.jpg","http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dc15ba1972.jpg","http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dc0ef27068.jpg"]', '互联网电热水器1A', '3000w双管速热，动态360L热水用量，双重漏电保护，智能APP操控', '电热水器', '3,2', '999.00', '0.00', '1599.00', '0.00', '件', 0, 86, 414, 0, 1, 1, 0, 1, '<p><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dc286862fd.jpg"/><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dc294a9a0a.jpg"/><img src="http://datong.crmeb.net/public/uploads/editor/20190115/5c3dc2ba18a77.jpg"/></p>', 1547551346, 1, 0, 0, '999.00', '888.00', 0, 0, 10, 0, '', NULL),
(5, 0, 'http://datong.crmeb.net/public/uploads/editor/20190115/5c3dbb513b06f.jpeg', '["http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/editor\\/20190115\\/5c3dc294a9a0a.jpg","http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/editor\\/20190115\\/5c3dbb513b06f.jpeg"]', '测试', '阿萨德啊', '去', '4,7,2,3,19', '1.00', '0.00', '1.00', '0.00', '件', 0, 7, 94, 1, 1, 0, 1, 0, '', 1554863537, 0, 1, 0, '1.00', '1.00', 0, NULL, 1, 0, '', NULL),
(6, 0, 'https://activityu.crmeb.net//ed0437125c6a0b03313c95c180ad82cd.jpg', '["https:\\/\\/activityu.crmeb.net\\/\\/ed0437125c6a0b03313c95c180ad82cd.jpg","https:\\/\\/activityu.crmeb.net\\/\\/ed5a330b2e4df8743e7c8747d2ba077d.jpg","https:\\/\\/activityu.crmeb.net\\/\\/982ee257498f8ab6e757c09cbf2f6865.jpg","https:\\/\\/activityu.crmeb.net\\/\\/ecc9f0b3ae923da5ec116a9b40bd851c.jpg","https:\\/\\/activityu.crmeb.net\\/\\/2ca5fd434e88a579238aeaf31389e2ab.jpg","https:\\/\\/activityu.crmeb.net\\/\\/33f86af708018fbaeead7220642c6e83.jpg"]', '【小米小米9】小米94800万超广角三摄6GB+128GB全息幻彩蓝骁龙855全网通4G双卡双待水滴全面屏拍照游戏智能手机', '【小米小米9】小米94800万超广角三摄6GB+128GB全息幻彩蓝骁龙855全网通4G双卡双待水滴全面屏拍照游戏智能手机', '小米9', '2', '3999.00', '0.00', '3999.00', '0.00', '件', 0, 0, 100, 0, 1, 0, 0, 0, '<br><div id="zbViewModulesH" value="26470"></div><input id="zbViewModulesHeight" type="hidden" value="26470"><div skudesign="100010"></div><div class="ssd-module-wrap">\n    <div class="ssd-module M155426253860522 animate-M155426253860522" data-id="M155426253860522">\n        \n</div>\n<div class="ssd-module M155426253862223 animate-M155426253862223" data-id="M155426253862223">\n        \n</div>\n<div class="ssd-module M155426253863924 animate-M155426253863924" data-id="M155426253863924">\n        \n</div>\n<div class="ssd-module M155426253865225 animate-M155426253865225" data-id="M155426253865225">\n        \n</div>\n<div class="ssd-module M155426253867026 animate-M155426253867026" data-id="M155426253867026">\n        \n</div>\n<div class="ssd-module M155426253868827 animate-M155426253868827" data-id="M155426253868827">\n        \n</div>\n<div class="ssd-module M155426253870528 animate-M155426253870528" data-id="M155426253870528">\n        \n</div>\n<div class="ssd-module M155426253872529 animate-M155426253872529" data-id="M155426253872529">\n        \n</div>\n<div class="ssd-module M155426253874230 animate-M155426253874230" data-id="M155426253874230">\n        \n</div>\n<div class="ssd-module M155426253875531 animate-M155426253875531" data-id="M155426253875531">\n        \n</div>\n<div class="ssd-module M155426253876832 animate-M155426253876832" data-id="M155426253876832">\n        \n</div>\n<div class="ssd-module M155426253878633 animate-M155426253878633" data-id="M155426253878633">\n        \n</div>\n<div class="ssd-module M155426253880434 animate-M155426253880434" data-id="M155426253880434">\n        \n</div>\n<div class="ssd-module M155426253881935 animate-M155426253881935" data-id="M155426253881935">\n        \n</div>\n<div class="ssd-module M155426253883836 animate-M155426253883836" data-id="M155426253883836">\n        \n</div>\n<div class="ssd-module M155426253885437 animate-M155426253885437" data-id="M155426253885437">\n        \n</div>\n<div class="ssd-module M155426253887338 animate-M155426253887338" data-id="M155426253887338">\n        \n</div>\n<div class="ssd-module M155426253889039 animate-M155426253889039" data-id="M155426253889039">\n        \n</div>\n<div class="ssd-module M155426253890540 animate-M155426253890540" data-id="M155426253890540">\n        \n</div>\n<div class="ssd-module M155426253892241 animate-M155426253892241" data-id="M155426253892241">\n        \n</div>\n<div class="ssd-module M155426253894142 animate-M155426253894142" data-id="M155426253894142">\n        \n</div>\n\n</div>\n<!-- 2019-04-11 10:18:24 --> <br><script></script><br>', 1558580351, 0, 0, 0, '0.00', '3999.00', 0, NULL, 123, 0, '', 'https://item.jd.com/7437708.html'),
(7, 0, 'http://activity.crmeb.net/public/uploads/attach/2019/05/29//6f2a1ece45e307f274e3384410a3bd3a.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/6f2a1ece45e307f274e3384410a3bd3a.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/ec8484e93ac49309b5576bb5f96d202f.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/60fff157d277d17154d738403870a489.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/3bfee3357bbf0091c2cdfe7aa1da5eec.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/6f2bbcd0dffd379c6f91e95a308bcfb6.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/f92383a6a1be19a7588ccd227e458afd.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/353d754027763daf1ce61d94f7c3709a.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/5f273c529aa59d313f08a36a50a20380.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/27cf255f9990535ed3b333009f1df52c.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/a7dbbc6d4a2ecf16b592b880d937a770.jpg"]', '【华为畅享9S】华为HUAWEI畅享9S6GB+64GB珊瑚红全网通2400万超广角三摄珍珠屏大存储移动联通电信4G手机双卡双待', '【华为畅享9S】华为HUAWEI畅享9S6GB+64GB珊瑚红全网通2400万超广角三摄珍珠屏大存储移动联通电信4G手机双卡双待', '件', '2', '100.00', '0.00', '2000.00', '0.00', '件', 0, 25, 76, 1, 1, 0, 1, 0, '<br><div cssurl="//sku-market-gw.jd.com/css/pc/100005088680.css?t=1555464055068"></div><div skucode="100010"></div><center>\n<div class="content_tpl">\n<div class="formwork">\n<div class="formwork_img">\n<img src="http://activity.crmeb.net/public/uploads/attach/2019/05/29//51de55dd382eb9fd9bdc6b9c4218d866.jpg" usemap="#Mapnate20" border="0"> \n<map name="Mapnate20">\n<area shape="rect" coords="14,27,246,400" href="//item.jd.com/100004323338.html" target="_blank"> <area shape="rect" coords="261,27,491,400" href="https://item.jd.com/100000766433.html#crumb-wrap" target="_blank"> <area shape="rect" coords="506,27,736,400" href="https://item.jd.com/100002293114.html#crumb-wrap" target="_blank"> \n</map>\n<img src="http://activity.crmeb.net/public/uploads/attach/2019/05/29//3495c08138194a6f26d821760d6d62a7.jpg"> <img src="http://activity.crmeb.net/public/uploads/attach/2019/05/29//e030f09b773f5a7c8b8a19f77c0ea713.jpg"> \n</div>\n</div>\n</div>\n</center><br><script></script><br>', 1559101322, 0, 0, 0, '10.00', '1000.00', 0, NULL, 0, 0, '', 'https://item.jd.com/100005088680.html'),
(8, 0, 'http://activity.crmeb.net/public/uploads/attach/2019/05/29//71e85589cb7d3398d08f0d55bdb9031d.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/71e85589cb7d3398d08f0d55bdb9031d.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/996c4bad976844b4f3bcf73cbd6e0f15.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/e47f51861c11fc648a298b16c24d8627.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/105cf3b5bbe2e1c7e6366d09b71e88b2.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/e275a4c451e06248cecc0cfb2ed24fed.jpg"]', '【AppleiPhone8】AppleiPhone8(A1863)256GB深空灰色移动联通电信4G手机', '【AppleiPhone8】AppleiPhone8(A1863)256GB深空灰色移动联通电信4G手机', '件', '2', '0.00', '0.00', '1000.00', '0.00', '件', 0, 20, 982, 1, 1, 0, 0, 0, '<br><div skucode="100010"></div><table id="__01" width="750" height="1272" border="0" align="center" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="http://activity.crmeb.net/public/uploads/attach/2019/05/29//da410a5d704e8d94ada293edb79678a7.jpg" alt="" width="750" height="249" usemap="#Map01" border="0" <="" d=""></td></tr><tr><td><img src="http://activity.crmeb.net/public/uploads/attach/2019/05/29//9ccac843781c262ae1e35bd176d43411.jpg" width="750" height="341" alt=""></td></tr><tr><td><img src="http://activity.crmeb.net/public/uploads/attach/2019/05/29//57064ab7664452fef3b9dab19668f915.jpg" width="750" height="405" alt="" usemap="#Map02" border="0"></td></tr><tr><td><img src="http://activity.crmeb.net/public/uploads/attach/2019/05/29//92d55c4dc01ade157aba628edc00be0b.jpg" width="640" alt="" usemap="#Map03"><map name="Map01"><area shape="rect" coords="5,41,98,177" href="https://pro.m.jd.com/mall/active/bFBgZuiq1QFNCPfzog9sEHDfeN5/index.html" target="_blank"><area shape="rect" coords="231,67,330,194" href="https://mall.jd.com/view_page-86030491.html#jingzhunda" target="_blank"> <area shape="rect" coords="418,67,517,195" href="https://mall.jd.com/view_page-86030491.html#jingdongweixiu" target="_blank"> <area shape="rect" coords="606,67,705,195" href="https://mall.jd.com/view_page-86030491.html#yijiuhuanxin" target="_blank"> <map name="Map02"><area shape="rect" coords="3,38,250,402" href="https://item.jd.com/4996353.html" target="_blank"> <area shape="rect" coords="252,38,495,402" href="https://item.jd.com/771942.html" target="_blank"> <area shape="rect" coords="501,39,745,400" href="https://item.jd.com/5164987.html" target="_blank"> </map><map name="Map03"><area shape="rect" coords="196,220,532,252" href="https://support.apple.com/zh-cn/HT204073" target="_blank"> </map></map></td></tr></tbody></table><br><script></script><br>', 1559101359, 0, 0, 0, '0.00', '1000.00', 0, NULL, 0, 0, '', 'https://item.jd.com/5089241.html'),
(9, 0, 'http://activity.crmeb.net/public/uploads/attach/2019/05/29//51308e61ace45968fdef953b2ac6c241.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/51308e61ace45968fdef953b2ac6c241.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/d3f3999622cb39eddb966d8cc041cb79.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/c2a01686bee0024c0ae3d08367ef9836.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/7121d81126a84b20a4f8b7e1252d0306.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/a6cc4157d0cf8e79f5b4a7cac1423f25.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/391618fe80748151243ce5e8650ee3bb.jpg"]', '【雷神911Air星战】雷神(ThundeRobot)911Air星战二代旗舰版15.6英寸窄边框游戏笔记本电脑i79750H512GSSDRGB键盘GTX1650', '【雷神911Air星战】雷神(ThundeRobot)911Air星战二代旗舰版15.6英寸窄边框游戏笔记本电脑i79750H512GSSDRGB键盘GTX1650', '件', '2', '1000.00', '0.00', '1000.00', '0.00', '件', 0, 23, 983, 1, 1, 0, 0, 1, '<br><div id="zbViewModulesH" value="12744"></div><input id="zbViewModulesHeight" type="hidden" value="12744"><div skudesign="100010"></div><div class="ssd-module-wrap">\n    <div class="ssd-module M15574696198181 animate-M15574696198181" data-id="M15574696198181">\n        \n</div>\n<div class="ssd-module M15574696198302 animate-M15574696198302" data-id="M15574696198302">\n        \n</div>\n<div class="ssd-module M15574696198423 animate-M15574696198423" data-id="M15574696198423">\n        \n</div>\n<div class="ssd-module M15574696198564 animate-M15574696198564" data-id="M15574696198564">\n        \n</div>\n<div class="ssd-module M15574696198705 animate-M15574696198705" data-id="M15574696198705">\n        \n</div>\n<div class="ssd-module M15574696198856 animate-M15574696198856" data-id="M15574696198856">\n        \n</div>\n<div class="ssd-module M15574696199077 animate-M15574696199077" data-id="M15574696199077">\n        \n</div>\n<div class="ssd-module M15574696199278 animate-M15574696199278" data-id="M15574696199278">\n        \n</div>\n<div class="ssd-module M15574696199519 animate-M15574696199519" data-id="M15574696199519">\n        \n</div>\n<div class="ssd-module M155746961996910 animate-M155746961996910" data-id="M155746961996910">\n        \n</div>\n<div class="ssd-module M155746961998811 animate-M155746961998811" data-id="M155746961998811">\n        \n</div>\n<div class="ssd-module M155746962001812 animate-M155746962001812" data-id="M155746962001812">\n        \n</div>\n<div class="ssd-module M155746962004613 animate-M155746962004613" data-id="M155746962004613">\n        \n</div>\n<div class="ssd-module M155746962006814 animate-M155746962006814" data-id="M155746962006814">\n        \n</div>\n<div class="ssd-module M155746962008915 animate-M155746962008915" data-id="M155746962008915">\n        \n</div>\n\n</div>\n<!-- 2019-05-10 02:27:40 --> <br><script></script><br>', 1559110455, 0, 0, 0, '0.00', '1000.00', 0, NULL, 20, 0, '', 'https://item.jd.com/100005518258.html'),
(10, 0, 'http://activity.crmeb.net/public/uploads/attach/2019/05/30//b58f452dc89775b344bade7fdc3ede14.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/b58f452dc89775b344bade7fdc3ede14.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/fe90dcb696cfcef739565894f9e93d9d.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/75a24145aac82bce88931019f91e928a.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/cdbc02ce7907670aa099c486f8959154.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/f8c0ffa71bfc8efae28082bf05c8969a.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/5fda52231265c835f853dd284d7437f9.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/0eaba55adaedcd7d0b17c7202225eed9.jpg"]', '【华为华为10000mAh快充移动电源/充电宝】华为10000毫安充电宝/移动电源18W双向快充MicroUSB口输入白色适用于安卓/苹果/平板等', '【华为华为10000mAh快充移动电源/充电宝】华为10000毫安充电宝/移动电源18W双向快充MicroUSB口输入白色适用于安卓/苹果/平板等', '', '4', '300.00', '0.00', '300.00', '0.00', '件', 0, 0, 100, 0, 1, 0, 1, 0, '<br><div cssurl="//sku-market-gw.jd.com/css/pc/100002611539.css?t=1552645455602"></div><div skucode="100010"></div><center>\n<img src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//efbee681cb31fe72ed52c006b56f31cf.jpg"> \n</center><br><script></script><br>', 1559198939, 0, 0, 0, '0.00', '400.00', 0, NULL, 50, 0, '', 'https://item.jd.com/100002611539.html'),
(11, 0, 'http://activity.crmeb.net/public/uploads/attach/2019/05/30//0eecbfbca9ebc315c2882590fd55a209.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/0eecbfbca9ebc315c2882590fd55a209.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/1a730a7edcb0c373f8188b4d6090999e.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/1da1cff5adc9c053022373596032cbb4.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/d116eb69f06169eed8efd06fcd4dcb90.jpg","http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/30\\/\\/33f645b107441db0b05eaa428a888ac6.jpg"]', '伊利酸奶畅轻整箱装乳酸菌燕麦黄桃草莓早餐奶250克9瓶风味发酵乳', '伊利酸奶畅轻整箱装乳酸菌燕麦黄桃草莓早餐奶250克9瓶风味发酵乳', '', '4', '80.00', '0.00', '80.00', '0.00', '件', 0, 63, 937, 1, 1, 0, 1, 0, '<div style="width: 790.0px;height: 13870.0px;overflow: hidden;"><div style="width: 790.0px;height: 13870.0px;overflow: hidden;"><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//4466609a8fd2572a4366a0786f7893a5.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//f37e16bbbc014195001bc16fcc6cae63.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//960bad190414f774241379ccdf073576.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//8f20a9984fd968785de5e32053be9747.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//c00e4506123402f687405c69b80bb5c8.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//e8bccebd6534055129a8af8c488528a3.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//547c56bac0eb97085b776234e6104d42.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//ebd3cccb57e2d1b7a06b18fb1ee19978.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//a2981e2259068977cb15205d5516046b.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//e768dca53e023a3a79215fe2f2cf25fb.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//57ad0f7b95a510e91f5c080cd0ef45f0.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//3b2c7bafc9bfba1e01da50f2d44da19c.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//4af68ddf547e251bc349daac6b7ddc21.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//c59c6f708dda1ac28df3f627b1543f4e.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//a4cfd1a15c188d1c7793dcd6762c607f.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//83d0a242bbf6c4fce431da5a45ba72dc.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//c56ca302f42d2d16a770d3f87796e614.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//7d2008368b7a9122465c34f459ed55d7.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//cfef919448f30e5b433572edbd316ef5.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//793c7f0f2d8cdc74c7d94dc9c5a3d125.jpg"/><img style="display: block;width: 100.0%;" src="http://activity.crmeb.net/public/uploads/attach/2019/05/30//0d8fd269982df7991462bffcb92904f1.jpg"/></div></div>', 1559199293, 0, 0, 0, '0.00', '90.00', 0, NULL, 10, 0, '', 'https://detail.tmall.com/item.htm?spm=a1z10.5-b-s.w4011-19034888351.47.551172542eL9Go&id=569589000841&rn=cdeb08db2601055d6c446c7400b0057f&abbucket=3'),
(12, 0, 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg"]', '测撒测试', '测撒测试', '测撒测试', '3', '10.00', '0.00', '10.00', '0.00', '件', 0, 4, 996, 1, 1, 1, 1, 1, '', 1560650420, 1, 1, 0, '0.00', '5.00', 0, NULL, 100, 0, '', ''),
(13, 0, 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg"]', '测撒测试', '测撒测试', '测撒测试', '3', '10.00', '0.00', '10.00', '0.00', '件', 0, 1, 999, 1, 1, 1, 1, 1, '', 1560650420, 1, 1, 0, '0.00', '5.00', 0, NULL, 100, 0, '', ''),
(14, 0, 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg"]', '测撒测试', '测撒测试', '测撒测试', '3', '10.00', '0.00', '10.00', '0.00', '件', 0, 3, 997, 1, 1, 1, 1, 1, '', 1560650420, 1, 1, 0, '0.00', '5.00', 0, NULL, 100, 0, '', ''),
(15, 0, 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', '["http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg"]', '测撒测试', '测撒测试', '测撒测试', '3', '10.00', '0.00', '10.00', '0.00', '件', 0, 15, 998, 1, 1, 1, 1, 1, '', 1560650420, 1, 1, 0, '0.00', '5.00', 0, NULL, 100, 0, '', ''),
(17, 0, 'http://kaifa.crmeb.net/uploads/attach/2019/07/30/69eb5f09f947ff53244a57d5c021b817.jpg', '["http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/07\\/30\\/69eb5f09f947ff53244a57d5c021b817.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/07\\/30\\/ce0a2ed478552ca0bfc7092f337c8d10.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/07\\/30\\/13efc762f7fdfec35ad2eeedc541db4b.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/07\\/30\\/4d280506de9f99621d6c9257abb00002.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/07\\/30\\/b07e8708977de9c820d7d97958776f8d.jpg"]', '微信小程序公众号商城源码模板开发定制作拼团购带后台教程CRMEB', '微信小程序公众号商城源码模板开发定制作拼团购带后台教程CRMEB', '', '4', '498.00', '0.00', '498.00', '0.00', '件', 0, 0, 498, 1, 0, 0, 0, 0, '<p><br/></p><p><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/60a1b15d3a76d624e8fe4f29d6405329.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/4d756807800de48cf64515abde691858.jpg" style="max-width: 750.0px;"/><a href="http://undefined" target="_blank"><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/6eb36373e34467b616caf8ebd628b8a9.jpg" style="max-width: 750.0px;"/></a><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/9fd1c33715375d14a910dc6ac16a1f01.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/cf82af05b3627661f7311b437fd4ef75.png" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/6a199d8d05c1ade1d9d9d430af0c2135.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/ba7b6671f1ab84b7d1ff9f7ae483db97.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/17c4918d59ef74b5f15b63ce905cc301.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/29bcd8aeffa602b6bc37c9ea219f54a1.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/5ccce7202973e32e7b2086010e29038b.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/dc887ac553f4ea66365e69a626b4f030.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/77aedb47edfd4f9f3fe4d0be51bb2994.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/c57055e3fbd06b6947e49da47751ed7e.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/efdf887ad14277311c8f373197bd95ad.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/39154c06e825503436d4e488aecfc466.jpg" style="max-width: 750.0px;"/><img src="http://kaifa.crmeb.net/uploads/attach/2019/07/30/3bbf70c1bbebee6dec6717700a1a0fb1.jpg" style="max-width: 750.0px;"/></p><p><br/></p><div style="height: 1.0px;overflow: hidden;line-height: 20.0px;">添加影藏字符</div>', 1564471319, 0, 0, 0, '0.00', '498.00', 0, NULL, 498, 0, '', 'https://item.taobao.com/item.htm?spm=a1z10.1-c.w4004-10814341800.8.26d7346bMJoug6&id=575636560349'),
(18, 0, 'http://kaifa.crmeb.net/uploads/attach/2019/08/20190805/887db984b5f1138e08e1d9b069af766e.jpg', '["http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190805\\/887db984b5f1138e08e1d9b069af766e.jpg"]', '111', '111', '111', '', '20.00', '0.00', '20.00', '0.00', '件', 0, 0, 20, 0, 0, 0, 0, 0, '', 1565148206, 0, 0, 0, '0.00', '0.00', 0, NULL, 0, 0, '', ''),
(19, 0, 'http://kaifa.crmeb.net/uploads/attach/2019/08/13/4e3396f4248e9e5ef2eab5505216ade0.jpg', '["http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/13\\/4e3396f4248e9e5ef2eab5505216ade0.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/13\\/5653627e73313cf61c9620725c45a376.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/13\\/1d9d4158d2d7c7f0466e78207246e845.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/13\\/3f9bfd12b76f290d3ed82ea44ebb399a.jpg","http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/13\\/e8c9d50e6b7cef371fe742ab08abd6a4.jpg"]', '【直营】ZOJIRUSHI象印进口不锈钢便携保温杯KB48480ml日本tmall.hk天猫国际', '【直营】ZOJIRUSHI象印进口不锈钢便携保温杯KB48480ml日本tmall.hk天猫国际', '', '4', '22.00', '0.00', '56.00', '0.00', '件', 0, 0, 222, 1, 0, 0, 0, 0, '<div> <a name="hlg_list_1_17635940_start"></a> </div> <div> <a name="hlg_list_1_17599564_end"></a> </div> <div> <a name="hlg_list_1_17537058_start"></a> </div> <table style="margin: 0.0px auto;" border="0" cellspacing="0" cellpadding="0">  <tbody><tr> <td> <div> &nbsp; </div> <div> <a href="https://detail.tmall.hk/hk/item.htm?id=575153295527" target="_blank"></a><a href="https://detail.tmall.hk/hk/item.htm?id=575153295527" target="_blank"></a> </div> </td> </tr>  </tbody></table> <div> &nbsp; </div> <div> <img width="790" height="719" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/e882d4dffdca67b65a7a1e66fb209c4c.jpg"><img width="790" height="589" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/feb565a57f5d42c370c54df7bdacb050.jpg"><img width="790" height="581" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/12709ee798ac2dd5c21d7ab030aa4e13.png"><img width="790" height="1060" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/5cc24febd1722f8c7ec7d5cf74262743.png"><img width="790" height="1051" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/28ccd5e15404129a793045f57049f149.jpg"><img width="790" height="354" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/42cc797e7005e47baf26cc33655b4667.jpg"><img width="790" height="553" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/0a8d0d2e2cb85c94c0d5380058603c56.jpg"><img width="790" height="884" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/26b2896f313fb594884fb992e33c5fa8.jpg"><img width="790" height="891" style="font-weight: bold;font-size: 1.5em;" src="http://kaifa.crmeb.net/uploads/attach/2019/08/13/7d1991d9b7bf33e84782c6cd942224f6.jpg"> </div>', 1565687845, 0, 0, 0, '0.00', '22.00', 0, NULL, 22, 0, '', 'https://detail.tmall.hk/hk/item.htm?spm=a1z10.1-b.w4004-21302208419.5.46261c5bwAUxpO&pvid=b786cf12-f690-4910-9535-e0fe5825e8f6&pos=2&acm=03068.1003.1.702815&id=586996993214&scm=1007.12941.28043.100200300000000');

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `attr_name` varchar(32) NOT NULL COMMENT '属性名',
  `attr_values` varchar(256) NOT NULL COMMENT '属性值',
  KEY `store_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品属性表';

--
-- 转存表中的数据 `eb_store_product_attr`
--

INSERT INTO `eb_store_product_attr` (`product_id`, `attr_name`, `attr_values`) VALUES
(3, '容量', '3L,4L'),
(3, '颜色', '白色,黑色'),
(15, '颜色', '黑色,白色,紫色'),
(15, '规则', '大,小'),
(2, '孔距', '30cm,40cm'),
(2, '材质', '陶瓷,不锈钢'),
(7, '1', '2,3'),
(7, '3', '1'),
(8, '1', '1,3'),
(8, '2', '2');

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `result` text NOT NULL COMMENT '商品属性参数',
  `change_time` int(10) unsigned NOT NULL COMMENT '上次修改时间',
  UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品属性详情表';

--
-- 转存表中的数据 `eb_store_product_attr_result`
--

INSERT INTO `eb_store_product_attr_result` (`product_id`, `result`, `change_time`) VALUES
(2, '{"attr":[{"value":"\\u5b54\\u8ddd","detailValue":"","attrHidden":true,"detail":["30cm","40cm"]},{"value":"\\u6750\\u8d28","detailValue":"","attrHidden":true,"detail":["\\u9676\\u74f7","\\u4e0d\\u9508\\u94a2"]}],"value":[{"detail":{"\\u5b54\\u8ddd":"30cm","\\u6750\\u8d28":"\\u9676\\u74f7"},"cost":"1500.00","price":0.02,"sales":936,"pic":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dbc27c69c7.jpg","check":false},{"detail":{"\\u5b54\\u8ddd":"30cm","\\u6750\\u8d28":"\\u4e0d\\u9508\\u94a2"},"cost":"1500.00","price":0.03,"sales":936,"pic":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dbc27c69c7.jpg","check":false},{"detail":{"\\u5b54\\u8ddd":"40cm","\\u6750\\u8d28":"\\u9676\\u74f7"},"cost":"1500.00","price":"0.01","sales":938,"pic":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dbc27c69c7.jpg","check":false},{"detail":{"\\u5b54\\u8ddd":"40cm","\\u6750\\u8d28":"\\u4e0d\\u9508\\u94a2"},"cost":"1500.00","price":"0.01","sales":937,"pic":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dbc27c69c7.jpg","check":false}]}', 1565600373),
(3, '{"attr":[{"value":"\\u5bb9\\u91cf","detailValue":"","attrHidden":true,"detail":["3L","4L"]},{"value":"\\u989c\\u8272","detailValue":"","attrHidden":true,"detail":["\\u767d\\u8272","\\u9ed1\\u8272"]}],"value":[{"detail":{"\\u5bb9\\u91cf":"3L","\\u989c\\u8272":"\\u9ed1\\u8272"},"cost":"10.00","price":249,"sales":20,"pic":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dc15ba1972.jpg","check":false},{"detail":{"\\u5bb9\\u91cf":"4L","\\u989c\\u8272":"\\u9ed1\\u8272"},"cost":"10.00","price":299,"sales":999,"pic":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/01\\/15\\/5c3dc15ba1972.jpg","check":false}]}', 1559378824),
(7, '{"attr":[{"value":"1","detailValue":"","attrHidden":true,"detail":["2","3"]},{"value":"3","detailValue":"","attrHidden":true,"detail":["1"]}],"value":[{"detail":{"1":"2","3":"1"},"cost":"1000.00","price":"100.00","sales":76,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/6f2a1ece45e307f274e3384410a3bd3a.jpg","check":false},{"detail":{"1":"3","3":"1"},"cost":"1000.00","price":"100.00","sales":76,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/6f2a1ece45e307f274e3384410a3bd3a.jpg","check":false}]}', 1565665933),
(8, '{"attr":[{"value":"1","detailValue":"","attrHidden":true,"detail":["1","3"]},{"value":"2","detailValue":"","attrHidden":true,"detail":["2"]}],"value":[{"detail":{"1":"1","2":"2"},"cost":"1000.00","price":"0.00","sales":982,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/71e85589cb7d3398d08f0d55bdb9031d.jpg","check":false},{"detail":{"1":"3","2":"2"},"cost":"1000.00","price":"0.00","sales":982,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/71e85589cb7d3398d08f0d55bdb9031d.jpg","check":false}]}', 1565680773),
(9, '{"attr":[{"value":"\\u914d\\u7f6e","detailValue":"","attrHidden":true,"detail":["i7-9750H 512GSSD RGB\\u952e\\u76d8 GTX1650","i7-9750H 8G 512GSSD GTX1050","i5-9300H 8G 512GSSD GTX1050)"]}],"value":[{"detail":{"\\u914d\\u7f6e":"i7-9750H 512GSSD RGB\\u952e\\u76d8 GTX1650"},"cost":"1000.00","price":"1000.00","sales":984,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/51308e61ace45968fdef953b2ac6c241.jpg","check":false},{"detail":{"\\u914d\\u7f6e":"i7-9750H 8G 512GSSD GTX1050"},"cost":"1000.00","price":900,"sales":984,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/51308e61ace45968fdef953b2ac6c241.jpg","check":false},{"detail":{"\\u914d\\u7f6e":"i5-9300H 8G 512GSSD GTX1050)"},"cost":"1000.00","price":800,"sales":984,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/05\\/29\\/\\/51308e61ace45968fdef953b2ac6c241.jpg","check":false}]}', 1565172349),
(15, '{"attr":[{"value":"\\u989c\\u8272","detailValue":"","attrHidden":true,"detail":["\\u9ed1\\u8272","\\u767d\\u8272","\\u7d2b\\u8272"]},{"value":"\\u89c4\\u5219","detailValue":"","attrHidden":true,"detail":["\\u5927","\\u5c0f"]}],"value":[{"detail":{"\\u989c\\u8272":"\\u9ed1\\u8272","\\u89c4\\u5219":"\\u5927"},"price":70,"cost":"5.00","sales":998,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg","check":false},{"detail":{"\\u989c\\u8272":"\\u9ed1\\u8272","\\u89c4\\u5219":"\\u5c0f"},"cost":"5.00","price":60,"sales":998,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg","check":false},{"detail":{"\\u989c\\u8272":"\\u767d\\u8272","\\u89c4\\u5219":"\\u5927"},"cost":"5.00","price":50,"sales":998,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg","check":false},{"detail":{"\\u989c\\u8272":"\\u767d\\u8272","\\u89c4\\u5219":"\\u5c0f"},"cost":"5.00","price":40,"sales":998,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg","check":false},{"detail":{"\\u989c\\u8272":"\\u7d2b\\u8272","\\u89c4\\u5219":"\\u5927"},"cost":"5.00","price":10,"sales":202,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg","check":false},{"detail":{"\\u989c\\u8272":"\\u7d2b\\u8272","\\u89c4\\u5219":"\\u5c0f"},"cost":"5.00","price":20,"sales":200,"pic":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg","check":false}]}', 1563346586);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品属性值表';

--
-- 转存表中的数据 `eb_store_product_attr_value`
--

INSERT INTO `eb_store_product_attr_value` (`product_id`, `suk`, `stock`, `sales`, `price`, `image`, `unique`, `cost`) VALUES
(15, '小,白色', 997, 1, '40.00', 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', '08af4c92', '5.00'),
(15, '大,黑色', 993, 5, '70.00', 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', '1bf9fad8', '5.00'),
(8, '1,2', 980, 2, '0.00', 'http://activity.crmeb.net/public/uploads/attach/2019/05/29//71e85589cb7d3398d08f0d55bdb9031d.jpg', '304d1fc7', '1000.00'),
(2, '40cm,不锈钢', 937, 0, '0.01', 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dbc27c69c7.jpg', '39b97621', '1500.00'),
(2, '30cm,不锈钢', 936, 0, '0.03', 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dbc27c69c7.jpg', '51c7f751', '1500.00'),
(8, '2,3', 982, 0, '0.00', 'http://activity.crmeb.net/public/uploads/attach/2019/05/29//71e85589cb7d3398d08f0d55bdb9031d.jpg', '5fe3af25', '1000.00'),
(2, '30cm,陶瓷', 924, 12, '0.02', 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dbc27c69c7.jpg', '63912ead', '1500.00'),
(7, '1,2', 75, 1, '100.00', 'http://activity.crmeb.net/public/uploads/attach/2019/05/29//6f2a1ece45e307f274e3384410a3bd3a.jpg', '76862ff5', '1000.00'),
(15, '大,紫色', 202, 0, '10.00', 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', '7a06e7f9', '5.00'),
(2, '40cm,陶瓷', 938, 0, '0.01', 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dbc27c69c7.jpg', 'a6c4ecd2', '1500.00'),
(3, '4L,黑色', 967, 32, '299.00', 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dc15ba1972.jpg', 'aa5915e8', '10.00'),
(3, '3L,黑色', 0, 20, '249.00', 'http://datong.crmeb.net/public/uploads/attach/2019/01/15/5c3dc15ba1972.jpg', 'c1fd48a6', '10.00'),
(15, '小,紫色', 195, 5, '20.00', 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', 'd630e29a', '5.00'),
(7, '1,3', 76, 0, '100.00', 'http://activity.crmeb.net/public/uploads/attach/2019/05/29//6f2a1ece45e307f274e3384410a3bd3a.jpg', 'd7b47f88', '1000.00'),
(15, '小,黑色', 997, 1, '60.00', 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', 'e4d9a758', '5.00'),
(15, '大,白色', 997, 1, '50.00', 'http://activity.crmeb.net/public/uploads/editor/20190605/5cf737bf264e4.jpg', 'f208c727', '5.00');

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_cate`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品id',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='产品分类辅助表' AUTO_INCREMENT=73 ;

--
-- 转存表中的数据 `eb_store_product_cate`
--

INSERT INTO `eb_store_product_cate` (`id`, `product_id`, `cate_id`, `add_time`) VALUES
(14, 6, 2, 1558580351),
(24, 4, 3, 1559110372),
(25, 4, 2, 1559110372),
(28, 3, 3, 1559110403),
(29, 3, 2, 1559110403),
(38, 12, 3, 1560650420),
(49, 5, 4, 1562729229),
(50, 5, 7, 1562729229),
(51, 5, 2, 1562729229),
(52, 5, 3, 1562729229),
(53, 5, 19, 1562729229),
(55, 11, 4, 1562729250),
(59, 10, 4, 1562741711),
(60, 1, 2, 1562835320),
(61, 1, 7, 1562835320),
(64, 17, 4, 1564471319),
(65, 2, 3, 1565148046),
(66, 2, 2, 1565148046),
(67, 18, 0, 1565148206),
(68, 9, 2, 1565148922),
(70, 8, 2, 1565426114),
(71, 7, 2, 1565600246),
(72, 19, 4, 1565687845);

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
  UNIQUE KEY `uid` (`uid`,`product_id`,`type`,`category`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `category` (`category`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品点赞和收藏表';

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
  `merchant_reply_content` varchar(300) DEFAULT NULL COMMENT '管理员回复内容',
  `merchant_reply_time` int(11) DEFAULT NULL COMMENT '管理员回复时间',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未删除1已删除',
  `is_reply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未回复1已回复',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_id_2` (`oid`,`unique`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `parent_id` (`reply_type`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `product_score` (`product_score`) USING BTREE,
  KEY `service_score` (`service_score`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品秒杀产品表id',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `image` varchar(255) NOT NULL COMMENT '推荐图',
  `images` varchar(2000) NOT NULL COMMENT '轮播图',
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
  `description` text COMMENT '内容',
  `start_time` varchar(128) NOT NULL COMMENT '开始时间',
  `stop_time` varchar(128) NOT NULL COMMENT '结束时间',
  `add_time` varchar(128) NOT NULL COMMENT '添加时间',
  `status` tinyint(1) unsigned NOT NULL COMMENT '产品状态',
  `is_postage` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否包邮',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '热门推荐',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除 0未删除1已删除',
  `num` int(11) unsigned NOT NULL COMMENT '最多秒杀几个',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '显示',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `product_id` (`product_id`) USING BTREE,
  KEY `start_time` (`start_time`,`stop_time`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `is_hot` (`is_hot`) USING BTREE,
  KEY `is_show` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `is_postage` (`is_postage`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品秒杀产品表' AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `attr_name` varchar(32) NOT NULL COMMENT '属性名',
  `attr_values` varchar(256) NOT NULL COMMENT '属性值',
  KEY `store_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='秒杀商品属性表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `result` text NOT NULL COMMENT '商品属性参数',
  `change_time` int(10) unsigned NOT NULL COMMENT '上次修改时间',
  UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='秒杀商品属性详情表';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='秒杀商品属性值表';

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
  `notify` int(2) DEFAULT '0' COMMENT '订单通知1开启0关闭',
  `customer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否展示统计管理',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客服表' AUTO_INCREMENT=1 ;

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
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读（0：否；1：是；）',
  `remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提醒过',
  `msn_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '消息类型 1=文字 2=表情 3=图片 4=语音',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客服用户对话记录表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品浏览分析表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `account` (`account`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='后台管理员表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `eb_system_admin`
--

INSERT INTO `eb_system_admin` (`id`, `account`, `pwd`, `real_name`, `roles`, `last_ip`, `last_time`, `add_time`, `login_count`, `level`, `status`, `is_del`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin', '1', '127.0.0.1', 1566874006, 1566873825, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_attachment`
--

CREATE TABLE IF NOT EXISTS `eb_system_attachment` (
  `att_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '附件名称',
  `att_dir` varchar(200) NOT NULL DEFAULT '' COMMENT '附件路径',
  `satt_dir` varchar(200) DEFAULT NULL COMMENT '压缩图片路径',
  `att_size` char(30) NOT NULL DEFAULT '' COMMENT '附件大小',
  `att_type` char(30) NOT NULL DEFAULT '' COMMENT '附件类型',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '分类ID0编辑器,1产品图片,2拼团图片,3砍价图片,4秒杀图片,5文章图片,6组合数据图',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '上传时间',
  `image_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '图片上传类型 1本地 2七牛云 3OSS 4COS ',
  `module_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '图片上传模块类型 1 后台上传 2 用户生成',
  PRIMARY KEY (`att_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='附件管理表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_attachment_category`
--

CREATE TABLE IF NOT EXISTS `eb_system_attachment_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '父级ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `enname` varchar(50) NOT NULL COMMENT '分类目录',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='附件分类表' AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- 表的结构 `eb_system_config`
--

CREATE TABLE IF NOT EXISTS `eb_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置id',
  `menu_name` varchar(255) NOT NULL COMMENT '字段名称',
  `type` varchar(255) NOT NULL COMMENT '类型(文本框,单选按钮...)',
  `input_type` varchar(20) DEFAULT 'input' COMMENT '表单类型',
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='配置表' AUTO_INCREMENT=142 ;

--
-- 转存表中的数据 `eb_system_config`
--

INSERT INTO `eb_system_config` (`id`, `menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(1, 'site_name', 'text', 'input', 1, '', 0, 'required:true', 100, 0, '""', '网站名称', '网站名称', 0, 1),
(2, 'site_url', 'text', 'input', 1, '', 0, 'required:true,url:true', 100, 0, '""', '网站地址', '网站地址', 0, 1),
(3, 'site_logo', 'upload', NULL, 1, NULL, 1, NULL, 0, 0, '""', '后台LOGO', '左上角logo,建议尺寸[170*50]', 0, 1),
(4, 'site_phone', 'text', 'input', 1, '', 0, '', 100, 0, '""', '联系电话', '联系电话', 0, 1),
(5, 'seo_title', 'text', 'input', 1, '', 0, 'required:true', 100, 0, '""', 'SEO标题', 'SEO标题', 0, 1),
(6, 'site_email', 'text', 'input', 1, '', 0, 'email:true', 100, 0, '""', '联系邮箱', '联系邮箱', 0, 1),
(7, 'site_qq', 'text', 'input', 1, '', 0, 'qq:true', 100, 0, '""', '联系QQ', '联系QQ', 0, 1),
(8, 'site_close', 'radio', 'input', 1, '0=>开启\n1=>PC端关闭\n2=>WAP端关闭(含微信)\n3=>全部关闭', 0, '', 0, 0, '"0"', '网站关闭', '网站后台、商家中心不受影响。关闭网站也可访问', 0, 1),
(9, 'close_system', 'radio', 'input', 1, '0=>开启\n1=>关闭', 0, '', 0, 0, '"0"', '关闭后台', '关闭后台', 0, 2),
(10, 'wechat_name', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '""', '公众号名称', '公众号的名称', 0, 1),
(11, 'wechat_id', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '""', '微信号', '微信号', 0, 1),
(12, 'wechat_sourceid', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '""', '公众号原始id', '公众号原始id', 0, 1),
(13, 'wechat_appid', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '""', 'AppID', 'AppID', 0, 1),
(14, 'wechat_appsecret', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '""', 'AppSecret', 'AppSecret', 0, 1),
(15, 'wechat_token', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '""', '微信验证TOKEN', '微信验证TOKEN', 0, 1),
(16, 'wechat_encode', 'radio', 'input', 2, '0=>明文模式\n1=>兼容模式\n2=>安全模式', 0, '', 0, 0, '"0"', '消息加解密方式', '如需使用安全模式请在管理中心修改，仅限服务号和认证订阅号', 0, 1),
(17, 'wechat_encodingaeskey', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '""', 'EncodingAESKey', '公众号消息加解密Key,在使用安全模式情况下要填写该值，请先在管理中心修改，然后填写该值，仅限服务号和认证订阅号', 0, 1),
(18, 'wechat_share_img', 'upload', NULL, 2, NULL, 1, NULL, 0, 0, '""', '微信分享图片', '若填写此图片地址，则分享网页出去时会分享此图片。可有效防止分享图片变形', 0, 1),
(19, 'wechat_qrcode', 'upload', 'input', 2, '', 1, '', 0, 0, '""', '公众号二维码', '您的公众号二维码', 0, 1),
(20, 'wechat_type', 'radio', 'input', 2, '0=>服务号\n1=>订阅号', 0, '', 0, 0, '"0"', '公众号类型', '公众号的类型', 0, 1),
(21, 'wechat_share_title', 'text', 'input', 2, NULL, NULL, 'required:true', 100, 0, '"CRMEB"', '微信分享标题', '微信分享标题', 0, 1),
(22, 'wechat_share_synopsis', 'textarea', NULL, 2, NULL, NULL, NULL, 100, 5, '"CRMEB"', '微信分享简介', '微信分享简介', 0, 1),
(23, 'pay_weixin_appid', 'text', 'input', 4, '', 0, '', 100, 0, '""', 'Appid', '微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看。', 0, 1),
(24, 'pay_weixin_appsecret', 'text', 'input', 4, '', 0, '', 100, 0, '""', 'Appsecret', 'JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看。', 0, 1),
(25, 'pay_weixin_mchid', 'text', 'input', 4, '', 0, '', 100, 0, '""', 'Mchid', '受理商ID，身份标识', 0, 1),
(26, 'pay_weixin_client_cert', 'upload', 'input', 4, '', 3, '', 0, 0, '""', '微信支付证书', '微信支付证书，在微信商家平台中可以下载！文件名一般为apiclient_cert.pem', 0, 1),
(27, 'pay_weixin_client_key', 'upload', 'input', 4, '', 3, '', 0, 0, '""', '微信支付证书密钥', '微信支付证书密钥，在微信商家平台中可以下载！文件名一般为apiclient_key.pem', 0, 1),
(28, 'pay_weixin_key', 'text', 'input', 4, '', 0, '', 100, 0, '""', 'Key', '商户支付密钥Key。审核通过后，在微信发送的邮件中查看。', 0, 1),
(29, 'pay_weixin_open', 'radio', 'input', 4, '1=>开启\n0=>关闭', 0, '', 0, 0, '"1"', '开启', '是否启用微信支付', 0, 1),
(31, 'store_postage', 'text', 'input', 10, '', 0, 'number:true,min:0', 100, 0, '"0"', '邮费基础价', '商品邮费基础价格,最终金额为(基础价 + 商品1邮费 + 商品2邮费)', 0, 1),
(32, 'store_free_postage', 'text', 'input', 5, '', 0, 'number:true,min:-1', 100, 0, '""', '满额包邮', '商城商品满多少金额即可包邮', 0, 1),
(33, 'offline_postage', 'radio', 'input', 10, '0=>不包邮\n1=>包邮', 0, '', 0, 0, '"0"', '线下支付是否包邮', '用户选择线下支付时是否包邮', 0, 1),
(34, 'integral_ratio', 'text', 'input', 11, '', 0, 'number:true', 100, 0, '"1"', '积分抵用比例', '积分抵用比例(1积分抵多少金额)', 0, 1),
(35, 'site_service_phone', 'text', 'input', 1, '', 0, '', 100, 0, '""', '客服电话', '客服联系电话', 0, 1),
(44, 'store_user_min_recharge', 'text', 'input', 5, '', 0, 'required:true,number:true,min:0', 100, 0, '"0.01"', '用户最低充值金额', '用户单次最低充值金额', 0, 0),
(45, 'site_store_admin_uids', 'text', 'input', 5, '', 0, '', 100, 0, '"4"', '管理员用户ID', '管理员用户ID,用于接收商城订单提醒，到微信用户中查找编号，多个英文‘,’隔开', 0, 1),
(46, 'system_express_app_code', 'text', 'input', 10, '', 0, '', 100, 0, '""', '快递查询密钥', '阿里云快递查询接口密钥购买地址：https://market.aliyun.com/products/56928004/cmapi021863.html', 0, 1),
(47, 'main_business', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '" IT\\u79d1\\u6280 \\u4e92\\u8054\\u7f51|\\u7535\\u5b50\\u5546\\u52a1"', '微信模板消息_主营行业', '微信公众号模板消息中选择开通的主营行业', 0, 0),
(48, 'vice_business', 'text', 'input', 2, '', 0, 'required:true', 100, 0, '"IT\\u79d1\\u6280 IT\\u8f6f\\u4ef6\\u4e0e\\u670d\\u52a1 "', '微信模板消息_副营行业', '微信公众号模板消息中选择开通的副营行业', 0, 0),
(49, 'store_brokerage_ratio', 'text', 'input', 9, '', 0, 'required:true,min:0,max:100,number:true', 100, 0, '"80"', '一级返佣比例', '订单交易成功后给上级返佣的比例0 - 100,例:5 = 反订单金额的5%', 5, 1),
(50, 'wechat_first_sub_give_coupon', 'text', 'input', 12, '', 0, 'requred:true,digits:true,min:0', 100, 0, '""', '首次关注赠送优惠券ID', '首次关注赠送优惠券ID,0为不赠送', 0, 1),
(51, 'store_give_con_min_price', 'text', 'input', 12, '', 0, 'requred:true,digits:true,min:0', 100, 0, '"0.01"', '消费满多少赠送优惠券', '消费满多少赠送优惠券,0为不赠送', 0, 1),
(52, 'store_order_give_coupon', 'text', 'input', 12, '', 0, 'requred:true,digits:true,min:0', 100, 0, '""', '消费赠送优惠劵ID', '消费赠送优惠劵ID,0为不赠送', 0, 1),
(53, 'user_extract_min_price', 'text', 'input', 9, '', 0, 'required:true,number:true,min:0', 100, 0, '"100"', '提现最低金额', '用户提现最低金额', 0, 1),
(54, 'sx_sign_min_int', 'text', 'input', 11, '', 0, 'required:true,number:true,min:0', 100, 0, '"1"', '签到奖励最低积分', '签到奖励最低积分', 0, 1),
(55, 'sx_sign_max_int', 'text', 'input', 11, '', 0, 'required:true,number:true,min:0', 100, 0, '"5"', '签到奖励最高积分', '签到奖励最高积分', 0, 1),
(56, 'store_home_pink', 'upload', 'input', 5, '', 1, '', 0, 0, '"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190801\\/af69ee46c79ec68b578afea61b5a37e4.jpg"', '首页拼团广告图', '首页拼团广告图', 0, 1),
(57, 'about_us', 'upload', NULL, 1, NULL, 1, NULL, 0, 0, '""', '关于我们', '系统的标识', 0, 2),
(58, 'replenishment_num', 'text', 'input', 5, '', 0, 'required:true,number:true,min:0', 100, 0, '"20"', '待补货数量', '产品待补货数量低于多少时，提示补货', 0, 1),
(59, 'routine_appId', 'text', 'input', 7, '', 0, '', 100, 0, '""', 'appId', '小程序appID', 0, 1),
(60, 'routine_appsecret', 'text', 'input', 7, '', 0, '', 100, 0, '""', 'AppSecret', '小程序AppSecret', 0, 1),
(61, 'api', 'text', 'input', 2, '', 0, '', 100, 0, '"\\/api\\/wechat\\/serve"', '接口地址', '微信接口例如：http://www.abc.com/api/wechat/serve', 0, 1),
(62, 'paydir', 'textarea', 'input', 4, '', 0, '', 100, 5, '""', '配置目录', '支付目录配置系统不调用提示作用', 0, 1),
(73, 'routine_logo', 'upload', NULL, 7, NULL, 1, NULL, 0, 0, '""', '小程序logo', '小程序logo', 0, 1),
(74, 'routine_name', 'text', 'input', 7, '', 0, '', 100, 0, '"CRMEB"', '小程序名称', '小程序名称', 0, 1),
(76, 'routine_style', 'text', 'color', 7, NULL, NULL, NULL, 100, 0, '"#F02D5A"', '小程序风格', '小程序颜色', 0, 1),
(77, 'store_stock', 'text', 'input', 5, '', 0, '', 100, 0, '"2"', '警戒库存', '警戒库存提醒值', 0, 1),
(85, 'stor_reason', 'textarea', 'input', 5, '', 0, '', 100, 8, '"\\u6536\\u8d27\\u5730\\u5740\\u586b\\u9519\\u4e86\\r\\n\\u4e0e\\u63cf\\u8ff0\\u4e0d\\u7b26\\r\\n\\u4fe1\\u606f\\u586b\\u9519\\u4e86\\uff0c\\u91cd\\u65b0\\u62cd\\r\\n\\u6536\\u5230\\u5546\\u54c1\\u635f\\u574f\\u4e86\\r\\n\\u672a\\u6309\\u9884\\u5b9a\\u65f6\\u95f4\\u53d1\\u8d27\\r\\n\\u5176\\u5b83\\u539f\\u56e0"', '退货理由', '配置退货理由，一行一个理由', 0, 1),
(87, 'store_brokerage_two', 'text', 'input', 9, '', 0, 'required:true,min:0,max:100,number:true', 100, 0, '"60"', '二级返佣比例', '订单交易成功后给上级返佣的比例0 - 100,例:5 = 反订单金额的5%', 4, 1),
(88, 'store_brokerage_statu', 'radio', 'input', 9, '1=>指定分销\n2=>人人分销', 0, '', 0, 0, '"2"', '分销模式', '人人分销默认每个人都可以分销，制定人分销后台制定人开启分销', 10, 1),
(89, 'pay_routine_appid', 'text', 'input', 14, '', 0, 'required:true', 100, 0, '""', 'Appid', '小程序Appid', 0, 1),
(90, 'pay_routine_appsecret', 'text', 'input', 14, '', 0, 'required:true', 100, 0, '""', 'Appsecret', '小程序Appsecret', 0, 1),
(91, 'pay_routine_mchid', 'text', 'input', 14, '', 0, 'required:true', 100, 0, '""', 'Mchid', '商户号', 0, 1),
(92, 'pay_routine_key', 'text', 'input', 14, '', 0, 'required:true', 100, 0, '""', 'Key', '商户key', 0, 1),
(93, 'pay_routine_client_cert', 'upload', 'input', 14, '', 3, '', 0, 0, '""', '小程序支付证书', '小程序支付证书', 0, 1),
(94, 'pay_routine_client_key', 'upload', 'input', 14, '', 3, '', 0, 0, '""', '小程序支付证书密钥', '小程序支付证书密钥', 0, 1),
(98, 'wechat_avatar', 'upload', 'input', 2, '', 1, '', 0, 0, '""', '公众号logo', '公众号logo', 0, 1),
(99, 'user_extract_bank', 'textarea', 'input', 9, '', 0, '', 100, 5, '"\\u4e2d\\u56fd\\u519c\\u884c\\r\\n\\u4e2d\\u56fd\\u5efa\\u8bbe\\u94f6\\u884c\\r\\n\\u5de5\\u5546\\u94f6\\u884c"', '提现银行卡', '提现银行卡，每个银行换行', 0, 1),
(100, 'fast_info', 'text', 'input', 16, NULL, NULL, '', 100, NULL, '"\\u4e0a\\u767e\\u79cd\\u5546\\u54c1\\u5206\\u7c7b\\u4efb\\u60a8\\u9009\\u62e9"', '快速选择简介', '小程序首页配置快速选择简介', 0, 1),
(101, 'bast_info', 'text', 'input', 16, NULL, NULL, '', 100, NULL, '"\\u8001\\u674e\\u8bda\\u610f\\u63a8\\u8350\\u54c1\\u8d28\\u5546\\u54c1"', '精品推荐简介', '小程序首页配置精品推荐简介', 0, 1),
(102, 'first_info', 'text', 'input', 16, NULL, NULL, '', 100, NULL, '"\\u591a\\u4e2a\\u4f18\\u8d28\\u5546\\u54c1\\u6700\\u65b0\\u4e0a\\u67b6"', '首发新品简介', '小程序首页配置首发新品简介', 0, 1),
(103, 'sales_info', 'text', 'input', 16, NULL, NULL, '', 100, NULL, '"\\u5e93\\u5b58\\u5546\\u54c1\\u4f18\\u60e0\\u4fc3\\u9500\\u6d3b\\u52a8"', '促销单品简介', '小程序首页配置促销单品简介', 0, 1),
(104, 'fast_number', 'text', 'input', 16, NULL, NULL, 'required:true,digits:true,min:1', 100, NULL, '"10"', '快速选择分类个数', '小程序首页配置快速选择分类个数', 0, 1),
(105, 'bast_number', 'text', 'input', 16, NULL, NULL, 'required:true,digits:true,min:1', 100, NULL, '"10"', '精品推荐个数', '小程序首页配置精品推荐个数', 0, 1),
(106, 'first_number', 'text', 'input', 16, NULL, NULL, 'required:true,digits:true,min:1', 100, NULL, '"10"', '首发新品个数', '小程序首页配置首发新品个数', 0, 1),
(107, 'routine_index_logo', 'upload', NULL, 5, NULL, 1, NULL, NULL, NULL, '"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190801\\/deb30359d22c0b80f3cece86cb462104.png"', '主页logo图标', '主页logo图标尺寸(127*45)', 0, 1),
(108, 'upload_type', 'radio', 'input', 17, '1=>本地存储\n2=>七牛云存储\n3=>阿里云OSS\n4=>腾讯COS', NULL, NULL, NULL, NULL, '"1"', '上传类型', '文件上传的类型', 0, 1),
(109, 'uploadUrl', 'text', 'input', 17, NULL, NULL, 'url:true', 100, NULL, '""', '空间域名 Domain', '空间域名 Domain', 0, 1),
(110, 'accessKey', 'text', 'input', 17, NULL, NULL, '', 100, NULL, '""', 'accessKey', 'accessKey', 0, 1),
(111, 'secretKey', 'text', 'input', 17, NULL, NULL, '', 100, NULL, '""', 'secretKey', 'secretKey', 0, 1),
(112, 'storage_name', 'text', 'input', 17, NULL, NULL, '', 100, NULL, '""', '存储空间名称', '存储空间名称', 0, 1),
(113, 'order_cancel_time', 'text', 'input', 5, NULL, NULL, '', 100, NULL, '"0.1"', '普通商品未支付取消订单时间', '普通商品未支付取消订单时间，单位（小时）', 0, 1),
(114, 'order_activity_time', 'text', 'input', 5, NULL, NULL, '', 100, NULL, '"2"', '活动商品未支付取消订单时间', '活动商品未支付取消订单时间，单位（小时）', 0, 1),
(115, 'order_bargain_time', 'text', 'input', 5, NULL, NULL, NULL, 100, NULL, '""', '砍价未支付取消订单时间', '砍价未支付默认取消订单时间，单位（小时），如果为0将使用默认活动取消时间，优先使用单独活动配置', 0, 1),
(116, 'order_seckill_time', 'text', 'input', 5, NULL, NULL, NULL, 100, NULL, '""', '秒杀未支付订单取消时间', '秒杀未支付订单取消时间，单位（小时），如果为0将使用默认活动取消时间，优先使用单独活动配置', 0, 1),
(117, 'order_pink_time', 'text', 'input', 5, NULL, NULL, NULL, 100, NULL, '""', '拼团未支付取消订单时间', '拼团未支付取消订单时间,单位（小时），如果为0将使用默认活动取消时间，优先使用单独活动配置', 0, 1),
(118, 'storage_region', 'text', 'input', 17, NULL, NULL, '', 100, NULL, '""', '所属地域', '所属地域(腾讯COS时填写)', 0, 1),
(119, 'vip_open', 'radio', 'input', 5, '0=>关闭\n1=>开启', NULL, NULL, NULL, NULL, '"1"', '会员功能是否开启', '会员功能是否开启', 0, 1),
(120, 'new_order_audio_link', 'upload', 'input', 5, NULL, 3, NULL, NULL, NULL, '"\\/public\\/uploads\\/config\\/file\\/5cedd83eedba2.mp3"', '新订单语音提示', '新订单语音提示', 0, 1),
(121, 'seckill_header_banner', 'upload', 'input', 5, NULL, 1, NULL, NULL, NULL, '"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190805\\/887db984b5f1138e08e1d9b069af766e.jpg"', '秒杀头部banner', '秒杀头部banner', 0, 1),
(122, 'system_delivery_time', 'text', 'input', 5, NULL, NULL, 'required:true,digits:true,min:0', 100, NULL, '"1"', '自动收货时间', '系统自动收货时间(0为不设置自动收货)', 0, 1),
(123, 'sms_account', 'text', 'input', 18, NULL, NULL, '', 100, NULL, '""', '账号', '短信后台的登录账号', 0, 1),
(137, 'sms_token', 'text', 'input', 18, NULL, NULL, '', 100, NULL, '""', 'token/密码', 'token(注册时候的密码)', 0, 1),
(138, 'h5_avatar', 'upload', NULL, 1, NULL, 1, NULL, 0, 0, '"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190807\\/723adbdd4e49a0f9394dfc700ab5dba3.png"', '用户H5默认头像', '用户H5默认头像尺寸(80*80)', 0, 1),
(139, 'offline_pay_status', 'radio', NULL, 5, '1=>开启\n2=>关闭', NULL, NULL, NULL, NULL, '"2"', '线下支付状态', '线下支付状态', 0, 1),
(140, 'news_slides_limit', 'text', 'number', 1, NULL, NULL, 'required:true,digits:true,min:1', 100, NULL, '"5"', '新闻幻灯片限制数量', '新闻幻灯片限制数量', 0, 1),
(141, 'recharge_switch', 'radio', 'input', 5, '1=>开启\n0=>关闭', NULL, NULL, NULL, NULL, '"1"', '充值开关', '充值开关', 0, 1);

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='配置分类表' AUTO_INCREMENT=20 ;

--
-- 转存表中的数据 `eb_system_config_tab`
--

INSERT INTO `eb_system_config_tab` (`id`, `title`, `eng_title`, `status`, `info`, `icon`, `type`) VALUES
(1, '基础配置', 'basics', 1, 0, 'cog', 0),
(2, '公众号配置', 'wechat', 1, 0, 'weixin', 1),
(4, '公众号支付配置', 'pay', 1, 0, 'jpy', 2),
(5, '商城配置', 'store', 1, 0, 'shopping-cart', 0),
(7, '小程序配置', 'routine', 1, 0, 'weixin', 1),
(9, '分销配置', 'fenxiao', 1, 0, 'sitemap', 3),
(10, '物流配置', 'express', 1, 0, 'motorcycle', 0),
(11, '积分配置', 'point', 1, 0, 'powerpoint-o', 3),
(12, '优惠券配置', 'coupon', 1, 0, 'heartbeat', 3),
(14, '小程序支付配置', 'routine_pay', 1, 0, 'jpy', 2),
(16, '首页配置', 'routine_index_page', 1, 0, 'home', 0),
(17, '文件上传配置', 'upload_set', 1, 0, 'cloud-upload', 0),
(18, '短信配置', 'system_sms', 1, 0, 'send', 3);

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文件对比表' AUTO_INCREMENT=2187 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `config_name` (`config_name`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='组合数据表' AUTO_INCREMENT=62 ;

--
-- 转存表中的数据 `eb_system_group`
--

INSERT INTO `eb_system_group` (`id`, `name`, `info`, `config_name`, `fields`) VALUES
(47, '首页分类图标', '首页分类图标', 'routine_home_menus', '[{"name":"\\u5206\\u7c7b\\u540d\\u79f0","title":"name","type":"input","param":""},{"name":"\\u5206\\u7c7b\\u56fe\\u6807(90*90)","title":"pic","type":"upload","param":""},{"name":"\\u5c0f\\u7a0b\\u5e8f\\u8df3\\u8f6c\\u8def\\u5f84","title":"url","type":"select","param":"\\/pages\\/index\\/index=>\\u5546\\u57ce\\u9996\\u9875\\n\\/pages\\/user_spread_user\\/index=>\\u4e2a\\u4eba\\u63a8\\u5e7f\\n\\/pages\\/user_sgin\\/index=>\\u6211\\u8981\\u7b7e\\u5230\\n\\/pages\\/user_get_coupon\\/index=>\\u4f18\\u60e0\\u5238\\n\\/pages\\/user\\/user=>\\u4e2a\\u4eba\\u4e2d\\u5fc3\\n\\/pages\\/activity\\/goods_seckill\\/index=>\\u79d2\\u6740\\u5217\\u8868\\n\\/pages\\/goods_cate\\/goods_cate=>\\u5206\\u7c7b\\u9875\\u9762\\n\\/pages\\/user_address_list\\/index=>\\u5730\\u5740\\u5217\\u8868\\n\\/pages\\/user_cash\\/index=>\\u63d0\\u73b0\\u9875\\u9762\\n\\/pages\\/promoter-list\\/index=>\\u63a8\\u5e7f\\u7edf\\u8ba1\\n\\/pages\\/user_money\\/index=>\\u8d26\\u6237\\u91d1\\u989d\\n\\/pages\\/user_goods_collection\\/index=>\\u6211\\u7684\\u6536\\u85cf\\n\\/pages\\/promotion-card\\/promotion-card=>\\u63a8\\u5e7f\\u4e8c\\u7ef4\\u7801\\u9875\\u9762\\n\\/pages\\/order_addcart\\/order_addcart=>\\u8d2d\\u7269\\u8f66\\u9875\\u9762\\n\\/pages\\/order_list\\/index=>\\u8ba2\\u5355\\u5217\\u8868\\u9875\\u9762\\n\\/pages\\/news_list\\/index=>\\u6587\\u7ae0\\u5217\\u8868\\u9875"},{"name":"\\u5e95\\u90e8\\u83dc\\u5355","title":"show","type":"radio","param":"1=>\\u662f\\n2=>\\u5426"},{"name":"\\u516c\\u4f17\\u53f7\\u8df3\\u8f6c\\u8def\\u5f84","title":"wap_url","type":"select","param":"\\/=>\\u5546\\u57ce\\u9996\\u9875\\n\\/user\\/user_promotion=>\\u4e2a\\u4eba\\u63a8\\u5e7f\\n\\/user\\/sign=>\\u6211\\u8981\\u7b7e\\u5230\\n\\/user\\/get_coupon=>\\u4f18\\u60e0\\u5238\\n\\/user=>\\u4e2a\\u4eba\\u4e2d\\u5fc3\\n\\/activity\\/goods_seckill=>\\u79d2\\u6740\\u5217\\u8868\\n\\/activity\\/group=>\\u62fc\\u56e2\\u5217\\u8868\\u9875\\n\\/activity\\/bargain=>\\u780d\\u4ef7\\u5217\\u8868\\n\\/category=>\\u5206\\u7c7b\\u9875\\u9762\\n\\/user\\/add_manage=>\\u5730\\u5740\\u5217\\u8868\\n\\/user\\/user_cash=>\\u63d0\\u73b0\\u9875\\u9762\\n\\/user\\/promoter_list=>\\u63a8\\u5e7f\\u7edf\\u8ba1\\n\\/user\\/account=>\\u8d26\\u6237\\u91d1\\u989d\\n\\/collection=>\\u6211\\u7684\\u6536\\u85cf\\n\\/user\\/poster=>\\u63a8\\u5e7f\\u4e8c\\u7ef4\\u7801\\u9875\\u9762\\n\\/cart=>\\u8d2d\\u7269\\u8f66\\u9875\\u9762\\n\\/order\\/list\\/=>\\u8ba2\\u5355\\u5217\\u8868\\u9875\\u9762\\n\\/news_list=>\\u6587\\u7ae0\\u5217\\u8868\\u9875"}]'),
(48, '首页banner滚动图', '首页banner滚动图', 'routine_home_banner', '[{"name":"\\u6807\\u9898","title":"name","type":"input","param":""},{"name":"\\u5c0f\\u7a0b\\u5e8f\\u94fe\\u63a5","title":"url","type":"input","param":""},{"name":"\\u56fe\\u7247(750*375)","title":"pic","type":"upload","param":""},{"name":"\\u516c\\u4f17\\u53f7\\u94fe\\u63a5","title":"wap_url","type":"input","param":""}]'),
(49, '秒杀时间段', '秒杀时间段', 'routine_seckill_time', '[{"name":"\\u5f00\\u542f\\u65f6\\u95f4(\\u6574\\u6570\\u5c0f\\u65f6)","title":"time","type":"input","param":""},{"name":"\\u6301\\u7eed\\u65f6\\u95f4(\\u6574\\u6570\\u5c0f\\u65f6)","title":"continued","type":"input","param":""}]'),
(50, '首页滚动新闻', '首页滚动新闻', 'routine_home_roll_news', '[{"name":"\\u6eda\\u52a8\\u6587\\u5b57","title":"info","type":"input","param":""},{"name":"\\u5c0f\\u7a0b\\u5e8f\\u8df3\\u8f6c\\u8def\\u5f84","title":"url","type":"select","param":"\\/pages\\/index\\/index=>\\u5546\\u57ce\\u9996\\u9875\\n\\/pages\\/user_spread_user\\/index=>\\u4e2a\\u4eba\\u63a8\\u5e7f\\n\\/pages\\/user_sgin\\/index=>\\u6211\\u8981\\u7b7e\\u5230\\n\\/pages\\/user_get_coupon\\/index=>\\u4f18\\u60e0\\u5238\\n\\/pages\\/user\\/user=>\\u4e2a\\u4eba\\u4e2d\\u5fc3\\n\\/pages\\/activity\\/goods_seckill\\/index=>\\u79d2\\u6740\\u5217\\u8868\\n\\/pages\\/activity\\/goods_combination\\/index=>\\u62fc\\u56e2\\u5217\\u8868\\u9875\\n\\/pages\\/activity\\/goods_bargain\\/index=>\\u780d\\u4ef7\\u5217\\u8868\\n\\/pages\\/goods_cate\\/goods_cate=>\\u5206\\u7c7b\\u9875\\u9762\\n\\/pages\\/user_address_list\\/index=>\\u5730\\u5740\\u5217\\u8868\\n\\/pages\\/user_cash\\/index=>\\u63d0\\u73b0\\u9875\\u9762\\n\\/pages\\/promoter-list\\/index=>\\u63a8\\u5e7f\\u7edf\\u8ba1\\n\\/pages\\/user_money\\/index=>\\u8d26\\u6237\\u91d1\\u989d\\n\\/pages\\/user_goods_collection\\/index=>\\u6211\\u7684\\u6536\\u85cf\\n\\/pages\\/promotion-card\\/promotion-card=>\\u63a8\\u5e7f\\u4e8c\\u7ef4\\u7801\\u9875\\u9762\\n\\/pages\\/order_addcart\\/order_addcart=>\\u8d2d\\u7269\\u8f66\\u9875\\u9762\\n\\/pages\\/order_list\\/index=>\\u8ba2\\u5355\\u5217\\u8868\\u9875\\u9762\\n\\/pages\\/news_list\\/index=>\\u6587\\u7ae0\\u5217\\u8868\\u9875"},{"name":"\\u5e95\\u90e8\\u83dc\\u5355","title":"show","type":"radio","param":"1=>\\u662f\\n2=>\\u5426"},{"name":"\\u516c\\u4f17\\u53f7\\u8df3\\u8f6c\\u8def\\u5f84","title":"wap_url","type":"select","param":"\\/=>\\u5546\\u57ce\\u9996\\u9875\\n\\/user\\/user_promotion=>\\u4e2a\\u4eba\\u63a8\\u5e7f\\n\\/user\\/sign=>\\u6211\\u8981\\u7b7e\\u5230\\n\\/user\\/get_coupon=>\\u4f18\\u60e0\\u5238\\n\\/user=>\\u4e2a\\u4eba\\u4e2d\\u5fc3\\n\\/activity\\/goods_seckill=>\\u79d2\\u6740\\u5217\\u8868\\n\\/activity\\/group=>\\u62fc\\u56e2\\u5217\\u8868\\u9875\\n\\/activity\\/bargain=>\\u780d\\u4ef7\\u5217\\u8868\\n\\/category=>\\u5206\\u7c7b\\u9875\\u9762\\n\\/user\\/add_manage=>\\u5730\\u5740\\u5217\\u8868\\n\\/user\\/user_cash=>\\u63d0\\u73b0\\u9875\\u9762\\n\\/user\\/promoter_list=>\\u63a8\\u5e7f\\u7edf\\u8ba1\\n\\/user\\/account=>\\u8d26\\u6237\\u91d1\\u989d\\n\\/collection=>\\u6211\\u7684\\u6536\\u85cf\\n\\/user\\/poster=>\\u63a8\\u5e7f\\u4e8c\\u7ef4\\u7801\\u9875\\u9762\\n\\/cart=>\\u8d2d\\u7269\\u8f66\\u9875\\u9762\\n\\/order\\/list\\/=>\\u8ba2\\u5355\\u5217\\u8868\\u9875\\u9762\\n\\/news_list=>\\u6587\\u7ae0\\u5217\\u8868\\u9875"}]'),
(52, '首页精品推荐benner图', '首页精品推荐benner图', 'routine_home_bast_banner', '[{"name":"\\u56fe\\u7247","title":"img","type":"upload","param":""},{"name":"\\u63cf\\u8ff0","title":"comment","type":"input","param":""},{"name":"\\u5c0f\\u7a0b\\u5e8f\\u8df3\\u8f6c\\u94fe\\u63a5","title":"link","type":"input","param":""},{"name":"\\u516c\\u4f17\\u53f7\\u8df3\\u8f6c\\u94fe\\u63a5","title":"wap_link","type":"input","param":""}]'),
(53, '订单详情状态图', '订单详情状态图', 'order_details_images', '[{"name":"\\u8ba2\\u5355\\u72b6\\u6001","title":"order_status","type":"select","param":"0=>\\u672a\\u652f\\u4ed8\\n1=>\\u5f85\\u53d1\\u8d27\\n2=>\\u5f85\\u6536\\u8d27\\n3=>\\u5f85\\u8bc4\\u4ef7\\n4=>\\u5df2\\u5b8c\\u6210\\n9=>\\u7ebf\\u4e0b\\u4ed8\\u6b3e"},{"name":"\\u56fe\\u6807","title":"pic","type":"upload","param":""}]'),
(54, '个人中心菜单', '个人中心菜单', 'routine_my_menus', '[{"name":"\\u83dc\\u5355\\u540d","title":"name","type":"input","param":""},{"name":"\\u56fe\\u6807(52*52)","title":"pic","type":"upload","param":""},{"name":"\\u5c0f\\u7a0b\\u5e8f\\u8df3\\u8f6c\\u8def\\u5f84","title":"url","type":"select","param":"\\/pages\\/user_address_list\\/index=>\\u5730\\u5740\\u7ba1\\u7406\\n\\/pages\\/user_vip\\/index=>\\u4f1a\\u5458\\u4e2d\\u5fc3\\n\\/pages\\/user_spread_user\\/index=>\\u63a8\\u5e7f\\u4e2d\\u5fc3\\n\\/pages\\/user_money\\/index=>\\u6211\\u7684\\u4f59\\u989d\\n\\/pages\\/user_goods_collection\\/index=>\\u6211\\u7684\\u6536\\u85cf\\n\\/pages\\/user_coupon\\/index=>\\u4f18\\u60e0\\u5238\\n\\/pages\\/admin\\/index=>\\u540e\\u53f0\\u8ba2\\u5355\\u7ba1\\u7406\\n\\/pages\\/service\\/index=>\\u8054\\u7cfb\\u5ba2\\u670d"},{"name":"\\u516c\\u4f17\\u53f7\\u8df3\\u8f6c\\u8def\\u5f84","title":"wap_url","type":"select","param":"\\/user\\/add_manage=>\\u5730\\u5740\\u7ba1\\u7406\\n\\/user\\/vip=>\\u4f1a\\u5458\\u4e2d\\u5fc3\\n\\/user\\/user_promotion=>\\u63a8\\u5e7f\\u4e2d\\u5fc3\\n\\/user\\/account=>\\u6211\\u7684\\u4f59\\u989d\\n\\/collection=>\\u6211\\u7684\\u6536\\u85cf\\n\\/user\\/user_coupon=>\\u4f18\\u60e0\\u5238\\n\\/customer\\/list=>\\u5ba2\\u670d\\u5217\\u8868"}]'),
(55, '签到天数配置', '签到天数配置', 'sign_day_num', '[{"name":"\\u7b2c\\u51e0\\u5929","title":"day","type":"input","param":""},{"name":"\\u83b7\\u53d6\\u79ef\\u5206","title":"sign_num","type":"input","param":""}]'),
(56, '热门搜索', '热门搜索', 'routine_hot_search', '[{"name":"\\u6807\\u7b7e","title":"title","type":"input","param":""}]'),
(57, '热门榜单推荐图片', '热门榜单推荐图片', 'routine_home_hot_banner', '[{"name":"\\u56fe\\u7247","title":"img","type":"upload","param":""},{"name":"\\u63cf\\u8ff0","title":"comment","type":"input","param":""}]'),
(58, '首发新品推荐图片', '首发新品推荐图片', 'routine_home_new_banner', '[{"name":"\\u56fe\\u7247","title":"img","type":"upload","param":""},{"name":"\\u63cf\\u8ff0","title":"comment","type":"input","param":""},{"name":"\\u5c0f\\u7a0b\\u5e8f\\u8df3\\u8f6c\\u94fe\\u63a5","title":"link","type":"input","param":""},{"name":"\\u516c\\u4f17\\u53f7\\u8df3\\u8f6c\\u94fe\\u63a5","title":"wap_link","type":"input","param":""}]'),
(59, '促销单品推荐图片', '促销单品推荐图片', 'routine_home_benefit_banner', '[{"name":"\\u56fe\\u7247","title":"img","type":"upload","param":""},{"name":"\\u63cf\\u8ff0","title":"comment","type":"input","param":""},{"name":"\\u5c0f\\u7a0b\\u5e8f\\u8df3\\u8f6c\\u94fe\\u63a5","title":"link","type":"input","param":""},{"name":"\\u516c\\u4f17\\u53f7\\u8df3\\u8f6c\\u94fe\\u63a5","title":"wap_link","type":"input","param":""}]'),
(60, '分享海报', '分享海报', 'routine_spread_banner', '[{"name":"\\u540d\\u79f0","title":"title","type":"input","param":""},{"name":"\\u80cc\\u666f\\u56fe","title":"pic","type":"upload","param":""}]'),
(61, '首页文字配置', '首页文字配置', 'routine_index_page', '[{"name":"\\u5feb\\u901f\\u9009\\u62e9\\u7b80\\u4ecb","title":"fast_info","type":"input","param":"\\u4e0a\\u767e\\u79cd\\u5546\\u54c1\\u5206\\u7c7b\\u4efb\\u60a8\\u9009\\u62e9"},{"name":"\\u7cbe\\u54c1\\u63a8\\u8350\\u7b80\\u4ecb","title":"bast_info","type":"input","param":"\\u8bda\\u610f\\u63a8\\u8350\\u54c1\\u8d28\\u5546\\u54c1"},{"name":"\\u9996\\u53d1\\u65b0\\u54c1\\u7b80\\u4ecb","title":"first_info","type":"input","param":"\\u591a\\u4e2a\\u4f18\\u8d28\\u5546\\u54c1\\u6700\\u65b0\\u4e0a\\u67b6"},{"name":"\\u4fc3\\u9500\\u5355\\u54c1\\u7b80\\u4ecb","title":"sales_info","type":"input","param":"\\u5e93\\u5b58\\u5546\\u54c1\\u4f18\\u60e0\\u4fc3\\u9500\\u6d3b\\u52a8"},{"name":"\\u5feb\\u901f\\u9009\\u62e9\\u5206\\u7c7b\\u4e2a\\u6570","title":"fast_number","type":"input","param":"10"},{"name":"\\u7cbe\\u54c1\\u63a8\\u8350\\u4e2a\\u6570","title":"bast_number","type":"input","param":"10"},{"name":"\\u9996\\u53d1\\u65b0\\u54c1\\u4e2a\\u6570","title":"first_number","type":"input","param":"10"}]');

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='组合数据详情表' AUTO_INCREMENT=176 ;

--
-- 转存表中的数据 `eb_system_group_data`
--

INSERT INTO `eb_system_group_data` (`id`, `gid`, `value`, `add_time`, `sort`, `status`) VALUES
(99, 47, '{"name":{"type":"input","value":"\\u5546\\u54c1\\u5206\\u7c7b"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9ddc9f34bfd.png"},"url":{"type":"select","value":"\\/pages\\/goods_cate\\/goods_cate"},"show":{"type":"radio","value":"1"},"wap_url":{"type":"select","value":"\\/category"}}', 1533721963, 8, 1),
(100, 47, '{"name":{"type":"input","value":"\\u9886\\u4f18\\u60e0\\u5238"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9ddccecb7f3.png"},"url":{"type":"select","value":"\\/pages\\/user_get_coupon\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/user\\/get_coupon"}}', 1533722009, 7, 1),
(101, 47, '{"name":{"type":"input","value":"\\u884c\\u4e1a\\u8d44\\u8baf"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9ddcec57a80.png"},"url":{"type":"select","value":"\\/pages\\/news_list\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/news_list"}}', 1533722037, 6, 1),
(102, 47, '{"name":{"type":"input","value":"\\u6211\\u8981\\u7b7e\\u5230"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9ddd570b8b3.png"},"url":{"type":"select","value":"\\/pages\\/user_sgin\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/user\\/sign"}}', 1533722063, 5, 1),
(104, 48, '{"name":{"type":"input","value":"banenr2"},"url":{"type":"input","value":"\\/pages\\/pink-list\\/index?id=2"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9e015bdc6f5.jpg"}}', 1533722286, 10, 1),
(105, 47, '{"name":{"type":"input","value":"\\u6211\\u7684\\u6536\\u85cf"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9dddce0eac9.png"},"url":{"type":"select","value":"\\/pages\\/user_goods_collection\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/collection"}}', 1533797064, 5, 1),
(113, 49, '{"time":{"type":"input","value":"5"},"continued":{"type":"input","value":"2"}}', 1552443280, 1, 1),
(114, 49, '{"time":{"type":"input","value":"7"},"continued":{"type":"input","value":"3"}}', 1552443293, 1, 1),
(115, 49, '{"time":{"type":"input","value":"10"},"continued":{"type":"input","value":"2"}}', 1552443304, 1, 1),
(116, 49, '{"time":{"type":"input","value":"12"},"continued":{"type":"input","value":"2"}}', 1552481140, 1, 1),
(117, 49, '{"time":{"type":"input","value":"14"},"continued":{"type":"input","value":"2"}}', 1552481146, 1, 1),
(118, 49, '{"time":{"type":"input","value":"16"},"continued":{"type":"input","value":"2"}}', 1552481151, 1, 1),
(119, 49, '{"time":{"type":"input","value":"18"},"continued":{"type":"input","value":"2"}}', 1552481157, 1, 1),
(120, 49, '{"time":{"type":"input","value":"20"},"continued":{"type":"input","value":"9"}}', 1552481163, 1, 1),
(121, 50, '{"info":{"type":"input","value":"CRMEB\\u7535\\u5546\\u7cfb\\u7edf V 3.0 \\u5373\\u5c06\\u4e0a\\u7ebf\\uff01"},"url":{"type":"select","value":"\\/pages\\/news_list\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/news_list"}}', 1552611989, 1, 1),
(122, 50, '{"info":{"type":"input","value":"CRMEB\\u7535\\u5546\\u7cfb\\u7edf V 3.0 \\u5373\\u5c06\\u4e0a\\u7ebf\\uff01"},"url":{"type":"select","value":"\\/pages\\/news_list\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/news_list"}}', 1552612003, 1, 1),
(123, 50, '{"info":{"type":"input","value":"CRMEB\\u7535\\u5546\\u7cfb\\u7edf V 3.0 \\u5373\\u5c06\\u4e0a\\u7ebf\\uff01"},"url":{"type":"select","value":"\\/pages\\/news_list\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/news_list"}}', 1552613047, 1, 1),
(127, 52, '{"img":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/04\\/13\\/5cb18e247a1a9.jpg"},"comment":{"type":"input","value":"\\u7cbe\\u54c1\\u63a8\\u8350750*282"},"link":{"type":"input","value":"\\/pages\\/first-new-product\\/index"},"wap_link":{"type":"input","value":"\\/hot_new_goods\\/1"}}', 1552633893, 1, 1),
(128, 52, '{"img":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9e015bdc6f5.jpg"},"comment":{"type":"input","value":"\\u7cbe\\u54c1\\u63a8\\u8350750*282"},"link":{"type":"input","value":"\\/pages\\/first-new-product\\/index"},"wap_link":{"type":"input","value":"\\/hot_new_goods\\/1"}}', 1552633912, 1, 1),
(135, 54, '{"name":{"type":"input","value":"\\u4f1a\\u5458\\u4e2d\\u5fc3"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccc9934a7c.png"},"url":{"type":"select","value":"\\/pages\\/user_vip\\/index"},"wap_url":{"type":"select","value":"\\/user\\/vip"}}', 1553779918, 1, 1),
(137, 54, '{"name":{"type":"input","value":"\\u6211\\u7684\\u63a8\\u5e7f"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccc9943575.png"},"url":{"type":"select","value":"\\/pages\\/user_spread_user\\/index"},"wap_url":{"type":"select","value":"\\/user\\/user_promotion"}}', 1553779950, 1, 1),
(138, 54, '{"name":{"type":"input","value":"\\u6211\\u7684\\u4f59\\u989d"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccc992db31.png"},"url":{"type":"select","value":"\\/pages\\/user_money\\/index"},"wap_url":{"type":"select","value":"\\/user\\/account"}}', 1553779973, 1, 1),
(139, 54, '{"name":{"type":"input","value":"\\u5730\\u5740\\u4fe1\\u606f"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccc99101a8.png"},"url":{"type":"select","value":"\\/pages\\/user_address_list\\/index"},"wap_url":{"type":"select","value":"\\/user\\/add_manage"}}', 1553779988, 1, 1),
(140, 54, '{"name":{"type":"input","value":"\\u6211\\u7684\\u6536\\u85cf"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccc99269d1.png"},"url":{"type":"select","value":"\\/pages\\/user_goods_collection\\/index"},"wap_url":{"type":"select","value":"\\/collection"}}', 1553780003, 1, 1),
(141, 54, '{"name":{"type":"input","value":"\\u4f18\\u60e0\\u5238"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccc991f394.png"},"url":{"type":"select","value":"\\/pages\\/user_coupon\\/index"},"wap_url":{"type":"select","value":"\\/user\\/user_coupon"}}', 1553780017, 1, 1),
(142, 53, '{"order_status":{"type":"select","value":"0"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccca151e99.gif"}}', 1553780202, 1, 1),
(143, 53, '{"order_status":{"type":"select","value":"1"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccca12638a.gif"}}', 1553780210, 1, 1),
(144, 53, '{"order_status":{"type":"select","value":"2"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccca1c78cd.gif"}}', 1553780221, 1, 1),
(145, 53, '{"order_status":{"type":"select","value":"3"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccca178a67.gif"}}', 1553780230, 1, 1),
(146, 53, '{"order_status":{"type":"select","value":"4"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9ccca1a01b6.gif"}}', 1553780237, 1, 1),
(147, 55, '{"day":{"type":"input","value":"\\u7b2c\\u4e00\\u5929"},"sign_num":{"type":"input","value":"10"}}', 1553780276, 100, 1),
(148, 55, '{"day":{"type":"input","value":"\\u7b2c\\u4e8c\\u5929"},"sign_num":{"type":"input","value":"20"}}', 1553780292, 99, 1),
(149, 55, '{"day":{"type":"input","value":"\\u7b2c\\u4e09\\u5929"},"sign_num":{"type":"input","value":"30"}}', 1553780303, 90, 1),
(150, 55, '{"day":{"type":"input","value":"\\u7b2c\\u56db\\u5929"},"sign_num":{"type":"input","value":"40"}}', 1553780334, 60, 1),
(151, 55, '{"day":{"type":"input","value":"\\u7b2c\\u4e94\\u5929"},"sign_num":{"type":"input","value":"50"}}', 1553780351, 50, 1),
(152, 55, '{"day":{"type":"input","value":"\\u7b2c\\u516d\\u5929"},"sign_num":{"type":"input","value":"60"}}', 1553780364, 40, 1),
(153, 55, '{"day":{"type":"input","value":"\\u5956\\u52b1"},"sign_num":{"type":"input","value":"110"}}', 1553780389, 10, 1),
(154, 57, '{"img":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9cd03224d59.jpg"},"comment":{"type":"input","value":"1"}}', 1553780856, 1, 1),
(155, 58, '{"img":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9cd03224d59.jpg"},"comment":{"type":"input","value":"1"},"link":{"type":"input","value":"#"},"wap_link":{"type":"input","value":"#"}}', 1553780869, 1, 1),
(156, 59, '{"img":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/28\\/5c9cd03224d59.jpg"},"comment":{"type":"input","value":"1"},"link":{"type":"input","value":"#"}}', 1553780883, 1, 1),
(157, 56, '{"title":{"type":"input","value":"\\u5438\\u5c18\\u5668"}}', 1553782153, 1, 1),
(159, 47, '{"name":{"type":"input","value":"\\u79d2\\u6740\\u6d3b\\u52a8"},"pic":{"type":"upload","value":"http:\\/\\/datong.crmeb.net\\/public\\/uploads\\/attach\\/2019\\/03\\/29\\/5c9dde246ad96.png"},"url":{"type":"select","value":"\\/pages\\/activity\\/goods_seckill\\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\\/activity\\/goods_seckill"}}', 1553849905, 2, 1),
(161, 60, '{"title":{"type":"input","value":"\\u5206\\u4eab\\u6d77\\u62a5"},"pic":{"type":"upload","value":"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190810\\/887960975abc9a89e410db82e0089680.jpg"}}', 1553866489, 1, 1),
(163, 56, '{"title":{"type":"input","value":"\\u52a0\\u6e7f\\u5668"}}', 1553869694, 1, 1),
(164, 56, '{"title":{"type":"input","value":"\\u9a6c\\u6876"}}', 1553869701, 1, 1),
(165, 56, '{"title":{"type":"input","value":"\\u70ed\\u6c34\\u5668"}}', 1553869710, 1, 1),
(167, 60, '{"title":{"type":"input","value":"1"},"pic":{"type":"upload","value":"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190810\\/623a4c225738606e4c65f93217050c86.jpg"}}', 1555063900, 1, 1),
(168, 60, '{"title":{"type":"input","value":"2"},"pic":{"type":"upload","value":"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190810\\/93669bff568cf8eb967670d9cd3ca78c.jpg"}}', 1555067377, 1, 1),
(169, 57, '{"img":{"type":"upload","value":"http:\\/\\/activity.crmeb.net\\/public\\/uploads\\/editor\\/20190605\\/5cf737bf264e4.jpg"},"comment":{"type":"input","value":"asd"}}', 1560149769, 1, 1),
(172, 54, '{"name":{"type":"input","value":"\\u8054\\u7cfb\\u5ba2\\u670d"},"pic":{"type":"upload","value":"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/07\\/20190730\\/0ded3d3f72d654fb33c8c9f30a268c97.png"},"url":{"type":"select","value":"\\/pages\\/user_address_list\\/index"},"wap_url":{"type":"select","value":"\\/customer\\/list"}}', 1564482010, 1, 1),
(173, 61, '{"fast_info":{"type":"input","value":"\\u4e0a\\u767e\\u79cd\\u5546\\u54c1\\u5206\\u7c7b\\u4efb\\u60a8\\u9009\\u62e9"},"bast_info":{"type":"input","value":"\\u8bda\\u610f\\u63a8\\u8350\\u54c1\\u8d28\\u5546\\u54c1"},"first_info":{"type":"input","value":"\\u591a\\u4e2a\\u4f18\\u8d28\\u5546\\u54c1\\u6700\\u65b0\\u4e0a\\u67b6"},"sales_info":{"type":"input","value":"\\u5e93\\u5b58\\u5546\\u54c1\\u4f18\\u60e0\\u4fc3\\u9500\\u6d3b\\u52a8"},"fast_number":{"type":"input","value":"10"},"bast_number":{"type":"input","value":"10"},"first_number":{"type":"input","value":"10"}}', 1565146071, 1, 1),
(174, 54, '{"name":{"type":"input","value":"\\u7edf\\u8ba1\\u7ba1\\u7406"},"pic":{"type":"upload","value":"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190808\\/87a240841283cb614f3ba308fd299866.png"},"url":{"type":"select","value":"\\/customer\\/index"},"wap_url":{"type":"select","value":"\\/customer\\/index"}}', 1565259184, 1, 1),
(175, 53, '{"order_status":{"type":"select","value":"9"},"pic":{"type":"upload","value":"http:\\/\\/kaifa.crmeb.net\\/uploads\\/attach\\/2019\\/08\\/20190809\\/7ba5e7e9bcd1ed480fdd432b49a3161a.gif"}}', 1565317740, 1, 1);

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员操作记录表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE,
  KEY `access` (`access`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='菜单表' AUTO_INCREMENT=480 ;

--
-- 转存表中的数据 `eb_system_menus`
--

INSERT INTO `eb_system_menus` (`id`, `pid`, `icon`, `menu_name`, `module`, `controller`, `action`, `params`, `sort`, `is_show`, `access`) VALUES
(1, 289, '', '系统设置', 'admin', 'setting.systemConfig', 'index', '[]', 127, 1, 1),
(2, 153, '', '权限规则', 'admin', 'setting.systemMenus', 'index', '[]', 7, 1, 1),
(4, 153, '', '管理员列表', 'admin', 'setting.systemAdmin', 'index', '[]', 9, 1, 1),
(7, 467, '', '配置分类', 'admin', 'setting.systemConfigTab', 'index', '[]', 1, 1, 1),
(8, 153, '', '身份管理', 'admin', 'setting.systemRole', 'index', '[]', 10, 1, 1),
(9, 467, '', '组合数据', 'admin', 'setting.systemGroup', 'index', '[]', 1, 1, 1),
(11, 0, 'wechat', '公众号', 'admin', 'wechat', 'index', '[]', 91, 1, 1),
(12, 354, '', '微信关注回复', 'admin', 'wechat.reply', 'index', '{"key":"subscribe","title":"\\u7f16\\u8f91\\u65e0\\u914d\\u7f6e\\u9ed8\\u8ba4\\u56de\\u590d"}', 86, 1, 1),
(17, 360, '', '微信菜单', 'admin', 'wechat.menus', 'index', '[]', 95, 1, 1),
(19, 11, '', '图文管理', 'admin', 'wechat.wechatNewsCategory', 'index', '[]', 60, 1, 1),
(21, 0, 'magic', '维护', 'admin', 'system', 'index', '[]', 0, 1, 1),
(23, 0, 'laptop', '商品', 'admin', 'store', 'index', '[]', 110, 1, 1),
(24, 23, '', '商品管理', 'admin', 'store.storeProduct', 'index', '{"type":"1"}', 100, 1, 1),
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
(48, 117, '', '添加配置', 'admin', 'setting.systemConfig', 'create', '[]', 0, 0, 1),
(49, 46, '', '编辑配置分类', 'admin', 'setting.systemConfigTab', 'edit', '[]', 0, 0, 1),
(50, 46, '', '删除配置分类', 'admin', 'setting.systemConfigTab', 'delete', '[]', 0, 0, 1),
(51, 46, '', '查看子字段', 'admin', 'system.systemConfigTab', 'sonConfigTab', '[]', 0, 0, 1),
(52, 9, '', '组合数据附加权限', 'admin', 'setting.systemGroup', '', '[]', 0, 0, 1),
(53, 468, '', '添加数据', 'admin', 'setting.systemGroupData', 'create', '[]', 0, 0, 1),
(54, 468, '', '编辑数据', 'admin', 'setting.systemGroupData', 'edit', '[]', 0, 0, 1),
(55, 468, '', '删除数据', 'admin', 'setting.systemGroupData', 'delete', '[]', 0, 0, 1),
(56, 468, '', '数据列表', 'admin', 'setting.systemGroupData', 'index', '[]', 0, 0, 1),
(57, 52, '', '添加数据组', 'admin', 'setting.systemGroup', 'create', '[]', 0, 0, 1),
(58, 52, '', '删除数据组', 'admin', 'setting.systemGroup', 'delete', '[]', 0, 0, 1),
(59, 4, '', '管理员列表附加权限', 'admin', 'setting.systemAdmin', '', '[]', 0, 0, 1),
(60, 59, '', '添加管理员', 'admin', 'setting.systemAdmin', 'create', '[]', 0, 0, 1),
(61, 59, '', '编辑管理员', 'admin', 'setting.systemAdmin', 'edit', '[]', 0, 0, 1),
(62, 59, '', '删除管理员', 'admin', 'setting.systemAdmin', 'delete', '[]', 0, 0, 1),
(63, 8, '', '身份管理附加权限', 'admin', 'setting.systemRole', '', '[]', 0, 0, 1),
(64, 63, '', '添加身份', 'admin', 'setting.systemRole', 'create', '[]', 0, 0, 1),
(65, 63, '', '修改身份', 'admin', 'setting.systemRole', 'edit', '[]', 0, 0, 1),
(66, 63, '', '删除身份', 'admin', 'setting.systemRole', 'delete', '[]', 0, 0, 1),
(67, 8, '', '身份管理展示页', 'admin', 'setting.systemRole', 'index', '[]', 0, 0, 1),
(68, 4, '', '管理员列表展示页', 'admin', 'setting.systemAdmin', 'index', '[]', 0, 0, 1),
(69, 7, '', '配置分类展示页', 'admin', 'setting.systemConfigTab', 'index', '[]', 0, 0, 1),
(70, 9, '', '组合数据展示页', 'admin', 'setting.systemGroup', 'index', '[]', 0, 0, 1),
(71, 284, '', '文章分类管理展示页', 'admin', 'article.articleCategory', 'index', '[]', 0, 0, 1),
(72, 283, '', '文章管理展示页', 'admin', 'article.article', 'index', '[]', 0, 0, 1),
(73, 19, '', '图文消息展示页', 'admin', 'wechat.wechatNewsCategory', 'index', '[]', 0, 0, 1),
(74, 2, '', '菜单管理附加权限', 'admin', 'setting.systemMenus', '', '[]', 0, 0, 1),
(75, 74, '', '添加菜单', 'admin', 'setting.systemMenus', 'create', '[]', 0, 0, 1),
(76, 74, '', '编辑菜单', 'admin', 'setting.systemMenus', 'edit', '[]', 0, 0, 1),
(77, 74, '', '删除菜单', 'admin', 'setting.systemMenus', 'delete', '[]', 0, 0, 1),
(78, 2, '', '菜单管理展示页', 'admin', 'setting.systemMenus', 'index', '[]', 0, 0, 1),
(80, 0, 'leanpub', '内容', 'admin', 'article', 'index', '[]', 90, 1, 1),
(82, 11, '', '微信用户管理', 'admin', 'user', 'list', '[]', 5, 1, 1),
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
(100, 74, '', '添加提交菜单', 'admin', 'setting.systemMenus', 'save', '[]', 0, 0, 1),
(101, 74, '', '编辑提交菜单', 'admin', 'setting.systemMenus', 'update', '[]', 0, 0, 1),
(102, 59, '', '提交添加管理员', 'admin', 'setting.systemAdmin', 'save', '[]', 0, 0, 1),
(103, 59, '', '提交修改管理员', 'admin', 'setting.systemAdmin', 'update', '[]', 0, 0, 1),
(104, 63, '', '提交添加身份', 'admin', 'setting.systemRole', 'save', '[]', 0, 0, 1),
(105, 63, '', '提交修改身份', 'admin', 'setting.systemRole', 'update', '[]', 0, 0, 1),
(106, 46, '', '提交添加配置分类', 'admin', 'setting.systemConfigTab', 'save', '[]', 0, 0, 1),
(107, 46, '', '提交修改配置分类', 'admin', 'setting.systemConfigTab', 'update', '[]', 0, 0, 1),
(108, 117, '', '提交添加配置列表', 'admin', 'setting.systemConfig', 'save', '[]', 0, 0, 1),
(109, 52, '', '提交添加数据组', 'admin', 'setting.systemGroup', 'save', '[]', 0, 0, 1),
(110, 52, '', '提交修改数据组', 'admin', 'setting.systemGroup', 'update', '[]', 0, 0, 1),
(111, 468, '', '提交添加数据', 'admin', 'setting.systemGroupData', 'save', '[]', 0, 0, 1),
(112, 468, '', '提交修改数据', 'admin', 'setting.systemGroupData', 'update', '[]', 0, 0, 1),
(113, 33, '', '提交添加文章分类', 'admin', 'article.articleCategory', 'save', '[]', 0, 0, 1),
(114, 33, '', '提交添加文章分类', 'admin', 'article.articleCategory', 'update', '[]', 0, 0, 1),
(115, 42, '', '提交添加图文消息', 'admin', 'wechat.wechatNewsCategory', 'save', '[]', 0, 0, 1),
(116, 42, '', '提交编辑图文消息', 'admin', 'wechat.wechatNewsCategory', 'update', '[]', 0, 0, 1),
(117, 1, '', '配置列表附加权限', 'admin', 'setting.systemConfig', '', '[]', 0, 0, 1),
(118, 1, '', '配置列表展示页', 'admin', 'setting.systemConfig', 'index', '[]', 0, 0, 1),
(119, 117, '', '提交保存配置列表', 'admin', 'setting.systemConfig', 'save_basics', '[]', 0, 0, 1),
(123, 89, '', '提交添加关键字', 'admin', 'wechat.reply', 'save_keyword', '{"dis":"1"}', 0, 0, 1),
(124, 89, '', '提交修改关键字', 'admin', 'wechat.reply', 'save_keyword', '{"dis":"2"}', 0, 0, 1),
(126, 17, '', '微信菜单展示页', 'admin', 'wechat.menus', 'index', '[]', 0, 0, 1),
(127, 17, '', '微信菜单附加权限', 'admin', 'wechat.menus', '', '[]', 0, 0, 1),
(128, 127, '', '提交微信菜单按钮', 'admin', 'wechat.menus', 'save', '{"dis":"1"}', 0, 0, 1),
(129, 82, '', '用户行为纪录', 'admin', 'wechat.wechatMessage', 'index', '[]', 0, 1, 1),
(130, 469, '', '系统日志', 'admin', 'system.systemLog', 'index', '[]', 5, 1, 1),
(131, 130, '', '管理员操作记录展示页', 'admin', 'system.systemLog', 'index', '[]', 0, 0, 1),
(132, 129, '', '微信用户行为纪录展示页', 'admin', 'wechat.wechatMessage', 'index', '[]', 0, 0, 1),
(133, 82, '', '微信用户', 'admin', 'wechat.wechatUser', 'index', '[]', 1, 1, 1),
(134, 133, '', '微信用户展示页', 'admin', 'wechat.wechatUser', 'index', '[]', 0, 0, 1),
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
(151, 0, 'user', '会员', 'admin', 'user', 'index', '[]', 107, 1, 1),
(153, 289, '', '管理权限', 'admin', 'setting.systemAdmin', '', '[]', 50, 1, 1),
(155, 154, '', '商户产品展示页', 'admin', 'store.storeMerchant', 'index', '[]', 0, 0, 1),
(156, 154, '', '商户产品附加权限', 'admin', 'store.storeMerchant', '', '[]', 0, 0, 1),
(158, 157, '', '商户文章管理展示页', 'admin', 'wechat.wechatNews', 'merchantIndex', '[]', 0, 0, 1),
(159, 157, '', '商户文章管理附加权限', 'admin', 'wechat.wechatNews', '', '[]', 0, 0, 1),
(170, 290, '', '评论管理', 'admin', 'store.store_product_reply', 'index', '[]', 0, 1, 1),
(173, 469, '', '文件校验', 'admin', 'system.systemFile', 'index', '[]', 1, 1, 1),
(174, 360, '', '微信模板消息', 'admin', 'wechat.wechatTemplate', 'index', '[]', 1, 1, 1),
(175, 289, '', '客服管理', 'admin', 'wechat.storeService', 'index', '[]', 70, 1, 1),
(177, 151, '', '会员管理', 'admin', 'user.user', 'index', '[]', 10, 1, 1),
(179, 307, '', '充值记录', 'admin', 'finance.userRecharge', 'index', '[]', 1, 1, 1),
(190, 26, '', '订单管理展示页', 'admin', 'order.storeOrder', 'index', '[]', 0, 0, 1),
(191, 26, '', '订单管理附加权限', 'admin', 'order.storeOrder', '', '[]', 0, 0, 1),
(192, 191, '', '订单管理去发货', 'admin', 'order.storeOrder', 'deliver_goods', '[]', 0, 0, 1),
(193, 191, '', '订单管理备注', 'admin', 'order.storeOrder', 'remark', '[]', 0, 0, 1),
(194, 191, '', '订单管理去送货', 'admin', 'order.storeOrder', 'delivery', '[]', 0, 0, 1),
(195, 191, '', '订单管理已收货', 'admin', 'order.storeOrder', 'take_delivery', '[]', 0, 0, 1),
(196, 191, '', '订单管理退款', 'admin', 'order.storeOrder', 'refund_y', '[]', 0, 0, 1),
(197, 191, '', '订单管理修改订单', 'admin', 'order.storeOrder', 'edit', '[]', 0, 0, 1),
(198, 191, '', '订单管理修改订单提交', 'admin', 'order.storeOrder', 'update', '[]', 0, 0, 1),
(199, 191, '', '订单管理退积分', 'admin', 'order.storeOrder', 'integral_back', '[]', 0, 0, 1),
(200, 191, '', '订单管理退积分提交', 'admin', 'order.storeOrder', 'updateIntegralBack', '[]', 0, 0, 1),
(201, 191, '', '订单管理立即支付', 'admin', 'order.storeOrder', 'offline', '[]', 0, 0, 1),
(202, 191, '', '订单管理退款原因', 'admin', 'order.storeOrder', 'refund_n', '[]', 0, 0, 1),
(203, 191, '', '订单管理退款原因提交', 'admin', 'order.storeOrder', 'updateRefundN', '[]', 0, 0, 1),
(204, 191, '', '订单管理修改配送信息', 'admin', 'order.storeOrder', 'distribution', '[]', 0, 0, 1),
(205, 191, '', '订单管理修改配送信息提交', 'admin', 'order.storeOrder', 'updateDistribution', '[]', 0, 0, 1),
(206, 191, '', '订单管理退款提交', 'admin', 'order.storeOrder', 'updateRefundY', '[]', 0, 0, 1),
(207, 191, '', '订单管理去发货提交', 'admin', 'order.storeOrder', 'updateDeliveryGoods', '[]', 0, 0, 1),
(208, 191, '', '订单管理去送货提交', 'admin', 'order.storeOrder', 'updateDelivery', '[]', 0, 0, 1),
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
(241, 273, '', '秒杀产品', 'admin', 'ump.storeSeckill', 'index', '[]', 0, 1, 1),
(244, 294, '', '财务报表', 'admin', 'record.storeStatistics', 'index', '[]', 0, 1, 1),
(246, 295, '', '用户统计', 'admin', 'user.user', 'user_analysis', '[]', 0, 1, 1),
(247, 153, '', '个人资料', 'admin', 'setting.systemAdmin', 'admininfo', '[]', 0, 0, 1),
(248, 247, '', '个人资料附加权限', 'admin', 'setting.systemAdmin', '', '[]', 0, 0, 1),
(249, 248, '', '个人资料提交保存', 'admin', 'system.systemAdmin', 'setAdminInfo', '[]', 0, 0, 1),
(250, 247, '', '个人资料展示页', 'admin', 'setting.systemAdmin', 'admininfo', '[]', 0, 0, 1),
(252, 21, '', '在线更新', 'admin', 'system.systemUpgradeclient', 'index', '[]', 0, 1, 1),
(255, 289, '', '后台通知', 'admin', 'setting.systemNotice', 'index', '[]', 0, 0, 1),
(261, 147, '', '编辑产品', 'admin', 'store.storeproduct', 'edit', '[]', 0, 0, 1),
(262, 147, '', '添加产品', 'admin', 'store.storeproduct', 'create', '[]', 0, 0, 1),
(263, 147, '', '编辑产品详情', 'admin', 'store.storeproduct', 'edit_content', '[]', 0, 0, 1),
(264, 147, '', '开启秒杀', 'admin', 'store.storeproduct', 'seckill', '[]', 0, 0, 1),
(265, 147, '', '开启秒杀', 'admin', 'store.store_product', 'bargain', '[]', 0, 0, 1),
(266, 147, '', '产品编辑属性', 'admin', 'store.storeproduct', 'attr', '[]', 0, 0, 1),
(269, 0, 'cubes', '小程序', 'admin', 'routine', 'index', '[]', 92, 1, 1),
(270, 289, '', '支付配置', 'admin', 'setting.systemConfig', 'index', '{"type":"2","tab_id":"4"}', 120, 1, 1),
(273, 286, '', '秒杀管理', 'admin', '', '', '[]', 0, 1, 1),
(276, 469, '', '附件管理', 'admin', 'widget.images', 'index', '[]', 0, 0, 1),
(278, 469, '', '清除数据', 'admin', 'system.systemCleardata', 'index', '[]', 0, 1, 1),
(283, 80, '', '文章管理', 'admin', 'article.article', 'index', '[]', 0, 1, 1),
(284, 80, '', '文章分类', 'admin', 'article.article_category', 'index', '[]', 0, 1, 1),
(285, 0, 'building-o', '订单', 'admin', 'order', 'index', '[]', 109, 1, 1),
(286, 0, 'paper-plane', '营销', 'admin', 'ump', 'index', '[]', 105, 1, 1),
(287, 0, 'money', '财务', 'admin', 'finance', 'index', '[]', 103, 1, 1),
(288, 0, 'line-chart', '数据', 'admin', 'record', 'index', '[]', 100, 1, 1),
(289, 0, 'gear', '设置', 'admin', 'setting', 'index', '[]', 90, 1, 1),
(290, 285, '', '售后服务', 'admin', '', '', '[]', 0, 1, 1),
(293, 288, '', '交易数据', 'admin', '', '', '[]', 100, 1, 1),
(294, 288, '', '财务数据', 'admin', '', '', '[]', 80, 1, 1),
(295, 288, '', '会员数据', 'admin', '', '', '[]', 70, 1, 1),
(296, 288, '', '营销数据', 'admin', '', '', '[]', 90, 1, 1),
(297, 288, '', '排行榜', 'admin', '', '', '[]', 0, 1, 1),
(300, 294, '', '提现统计', 'admin', 'record.record', 'chart_cash', '[]', 0, 1, 1),
(301, 294, '', '充值统计', 'admin', 'record.record', 'chart_recharge', '[]', 0, 1, 1),
(302, 294, '', '返佣统计', 'admin', 'record.record', 'chart_rebate', '[]', 0, 1, 1),
(303, 295, '', '会员增长', 'admin', 'record.record', 'user_chart', '[]', 0, 1, 1),
(304, 295, '', '会员业务', 'admin', 'record.record', 'user_business_chart', '[]', 0, 1, 1),
(305, 295, '', '会员属性', 'admin', 'record.record', 'user_attr', '[]', 0, 1, 1),
(306, 287, '', '财务操作', 'admin', '', '', '[]', 100, 1, 1),
(307, 287, '', '财务记录', 'admin', '', '', '[]', 50, 1, 1),
(308, 287, '', '佣金记录', 'admin', '', '', '[]', 0, 1, 1),
(312, 307, '', '资金监控', 'admin', 'finance.finance', 'bill', '[]', 0, 1, 1),
(313, 308, '', '佣金记录', 'admin', 'finance.finance', 'commission_list', '[]', 0, 1, 1),
(314, 296, '', '积分统计', 'admin', 'record.record', 'chart_score', '[]', 0, 1, 1),
(315, 296, '', '优惠券统计', 'admin', 'record.record', 'chart_coupon', '[]', 0, 1, 1),
(317, 296, '', '秒杀统计', 'admin', 'record.record', 'chart_seckill', '[]', 0, 1, 1),
(319, 297, '', '产品销售排行', 'admin', 'record.record', 'ranking_saleslists', '[]', 0, 1, 1),
(320, 297, '', '返佣排行', 'admin', 'record.record', 'ranking_commission', '[]', 0, 1, 1),
(321, 297, '', '积分排行', 'admin', 'record.record', 'ranking_point', '[]', 0, 1, 1),
(329, 285, '', '营销订单', 'admin', 'user', 'user', '[]', 0, 0, 1),
(334, 329, '', '秒杀订单', 'admin', 'user', '', '[]', 0, 0, 1),
(335, 329, '', '积分兑换', 'admin', 'user', '', '[]', 0, 0, 1),
(337, 0, 'users', '分销', 'admin', 'agent', 'index', '[]', 106, 1, 1),
(340, 293, '', '订单统计', 'admin', 'record.record', 'chart_order', '[]', 0, 1, 1),
(341, 293, '', '产品统计', 'admin', 'record.record', 'chart_product', '[]', 0, 1, 1),
(349, 286, '', '积分', 'admin', 'userPoint', 'index', '[]', 0, 1, 1),
(350, 349, '', '积分配置', 'admin', 'setting.systemConfig', 'index', '{"type":"3","tab_id":"11"}', 0, 1, 1),
(351, 349, '', '积分日志', 'admin', 'ump.userPoint', 'index', '[]', 0, 1, 1),
(352, 148, '', '优惠券配置', 'admin', 'setting.systemConfig', 'index', '{"type":"3","tab_id":"12"}', 0, 1, 1),
(353, 337, '', '分销配置', 'admin', 'setting.systemConfig', 'index', '{"type":"3","tab_id":"9"}', 0, 1, 1),
(354, 11, '', '自动回复', '', '', '', '[]', 80, 1, 1),
(360, 11, '', '公众号配置', '', '', '', '[]', 100, 1, 1),
(361, 289, '', '应用配置', 'admin', 'setting.systemConfig', 'index', '{"type":"1","tab_id":"2"}', 127, 1, 1),
(362, 276, '', '附加权限', 'admin', 'widget.images', '', '[]', 0, 1, 1),
(363, 362, '', '上传图片', 'admin', 'widget.images', 'upload', '[]', 0, 0, 1),
(364, 362, '', '删除图片', 'admin', 'widget.images', 'delete', '[]', 0, 0, 1),
(365, 362, '', '附件管理', 'admin', 'widget.images', 'index', '[]', 0, 0, 1),
(369, 143, '', '添加产品分类', 'admin', 'store.storeCategory', 'create', '[]', 0, 0, 1),
(370, 143, '', '编辑产品分类', 'admin', 'store.storeCategory', 'edit', '[]', 0, 0, 1),
(371, 337, '', '分销员管理', 'admin', 'agent.agentManage', 'index', '[]', 0, 1, 1),
(372, 462, '', '首页幻灯片', 'admin', 'setting.system_group_data', 'index', '{"gid":"48"}', 0, 1, 1),
(373, 462, '', '首页导航按钮', 'admin', 'setting.system_group_data', 'index', '{"gid":"47"}', 0, 1, 1),
(374, 295, '', '分销会员业务', 'admin', 'record.record', 'user_distribution_chart', '[]', 0, 1, 1),
(376, 269, '', '小程序模板消息', 'admin', 'routine.routineTemplate', 'index', '[]', 0, 1, 1),
(377, 469, '', '数据备份', 'admin', 'system.systemDatabackup', 'index', '[]', 0, 1, 1),
(378, 289, '', '物流公司', 'admin', 'system.express', 'index', '[]', 0, 1, 1),
(379, 469, '', '文件管理', 'admin', 'system.systemFile', 'opendir', '[]', 0, 1, 1),
(380, 379, '', '权限规则', 'admin', 'system.systemFile', '', '[]', 0, 0, 1),
(381, 380, '', '打开文件', 'admin', 'system.systemFile', 'openfile', '[]', 0, 0, 1),
(382, 380, '', '编辑文件', 'admin', 'system.systemFile', 'savefile', '[]', 0, 0, 1),
(386, 362, '', '移动图片分类展示', 'admin', 'widget.images', 'moveimg', '[]', 0, 0, 1),
(387, 362, '', '编辑分类', 'admin', 'widget.images', 'updatecate', '[]', 0, 0, 1),
(388, 362, '', '添加分类', 'admin', 'widget.images', 'savecate', '[]', 0, 0, 1),
(389, 362, '', '移动图片分类', 'admin', 'widget.images', 'moveimgcecate', '[]', 0, 0, 1),
(390, 362, '', '编辑分类展示', 'admin', 'widget.images', 'editcate', '[]', 0, 0, 1),
(392, 362, '', '删除分类', 'admin', 'widget.images', 'deletecate', '[]', 0, 0, 1),
(393, 362, '', '添加分类展示', 'admin', 'widget.images', 'addcate', '[]', 0, 0, 1),
(394, 191, '', '订单获取列表', 'admin', 'order.storeOrder', 'order_list', '[]', 0, 0, 1),
(395, 82, '', '微信用户附加权限', 'admin', 'wechat.wechatUser', '', '[]', 0, 0, 1),
(396, 395, '', '推送消息', 'admin', 'wechat.wechat_news_category', 'push', '[]', 0, 0, 1),
(397, 395, '', '推送优惠券', 'admin', 'ump.storeCouponUser', 'grant', '[]', 0, 0, 1),
(398, 177, '', '会员列表页', 'admin', 'user.user', 'index', '[]', 0, 0, 1),
(399, 177, '', '会员附加权限', '', 'user.user', '', '[]', 0, 0, 1),
(400, 399, '', '修改用户状态', '', 'user.user', 'set_status', '[]', 0, 0, 1),
(401, 399, '', '编辑用户', 'admin', 'user.user', 'edit', '[]', 0, 0, 1),
(402, 399, '', '更新用户', 'admin', 'user.user', 'update', '[]', 0, 0, 1),
(403, 399, '', '查看用户', 'admin', 'user.user', 'see', '[]', 0, 0, 1),
(405, 399, '', '发优惠券', 'admin', 'ump.storeCouponUser', 'grant', '[]', 0, 0, 1),
(406, 399, '', '推送图文', 'admin', 'wechat.wechatNewsCategory', 'push', '[]', 0, 0, 1),
(407, 399, '', '发站内信', 'admin', 'user.userNotice', 'notice', '[]', 0, 0, 1),
(415, 371, '', '分销管理附加权限', 'admin', 'agent.agentManage', '', '[]', 0, 0, 1),
(416, 174, '', '微信模版消息附加权限', 'admin', 'wechat.wechatTemplate', '', '[]', 0, 0, 1),
(417, 416, '', '添加模版消息', 'admin', 'wechat.wechatTemplate', 'save', '[]', 0, 0, 1),
(418, 416, '', '添加模版消息展示', 'admin', 'wechat.wechatTemplate', 'create', '[]', 0, 0, 1),
(419, 416, '', '编辑模版消息展示', 'admin', 'wechat.wechatTemplate', 'edit', '[]', 0, 0, 1),
(420, 416, '', '更新模版消息展示', 'admin', 'wechat.wechatTemplate', 'update', '[]', 0, 0, 1),
(421, 416, '', '删除模版消息展示', 'admin', 'wechat.wechatTemplate', 'delete', '[]', 0, 0, 1),
(422, 376, '', '小程序模版消息附加权限', 'admin', 'routine.routineTemplate', '', '[]', 0, 0, 1),
(423, 422, '', '添加模版消息展示', 'admin', 'routine.routineTemplate', 'create', '[]', 0, 0, 1),
(424, 422, '', '添加模版消息', 'admin', 'routine.routineTemplate', 'save', '[]', 0, 0, 1),
(425, 422, '', '编辑模版消息展示', 'admin', 'routine.routineTemplate', 'edit', '[]', 0, 0, 1),
(426, 422, '', '编辑模版消息', 'admin', 'routine.routineTemplate', 'update', '[]', 0, 0, 1),
(427, 422, '', '删除模版消息', 'admin', 'routine.routineTemplate', 'delete', '[]', 0, 0, 1),
(439, 377, '', '数据库备份附加权限', 'admin', 'system.systemDatabackup', '', '[]', 0, 0, 1),
(440, 439, '', '查看表结构', 'admin', 'system.systemDatabackup', 'seetable', '[]', 0, 0, 1),
(441, 439, '', '优化表', 'admin', 'system.systemDatabackup', 'optimize', '[]', 0, 0, 1),
(442, 439, '', '修复表', 'admin', 'system.systemDatabackup', 'repair', '[]', 0, 0, 1),
(443, 439, '', '备份表', 'admin', 'system.systemDatabackup', 'backup', '[]', 0, 0, 1),
(444, 439, '', '删除备份', 'admin', 'system.systemDatabackup', 'delFile', '[]', 0, 0, 1),
(445, 439, '', '恢复备份', 'admin', 'system.systemDatabackup', 'import', '[]', 0, 0, 1),
(446, 439, '', '下载备份', 'admin', 'system.systemDatabackup', 'downloadFile', '[]', 0, 0, 1),
(447, 377, '', '数据备份展示页', 'admin', 'system.systemDatabackup', 'index', '[]', 0, 0, 1),
(448, 379, '', '文件管理展示页', 'admin', 'system.systemFile', 'index', '[]', 0, 0, 1),
(450, 371, '', '分销管理列表页', 'admin', 'agent.agentManage', 'index', '[]', 0, 0, 1),
(451, 376, '', '小程序模版消息列表页', 'admin', 'routine.routineTemplate', 'index', '[]', 0, 0, 1),
(452, 174, '', '微信模版消息列表页', 'admin', 'wechat.wechatTemplate', 'index', '[]', 0, 0, 1),
(453, 276, '', '附件管理展示页', 'admin', 'widget.images', 'index', '[]', 0, 0, 1),
(456, 151, '', '会员等级', 'admin', 'user.user_level', 'index', '[]', 0, 1, 1),
(458, 462, '', '签到天数配置', 'admin', 'setting.system_group_data', 'index', '{"gid":"55"}', 0, 1, 1),
(459, 462, '', '订单详情动态图', 'admin', 'setting.system_group_data', 'index', '{"gid":"53"}', 0, 1, 1),
(460, 462, '', '个人中心菜单', 'admin', 'setting.system_group_data', 'index', '{"gid":"54"}', 0, 1, 1),
(461, 462, '', '小程序首页滚动新闻', 'admin', 'setting.system_group_data', 'index', '{"gid":"50"}', 0, 1, 1),
(462, 289, '', '数据配置', 'admin', '', '', '[]', 100, 1, 1),
(463, 462, '', '热门榜单推荐banner', 'admin', 'setting.system_group_data', 'index', '{"gid":"57"}', 0, 1, 1),
(464, 462, '', '首发新品推荐banner', 'admin', 'setting.system_group_data', 'index', '{"gid":"58"}', 0, 1, 1),
(465, 462, '', '促销单品推荐banner', 'admin', 'setting.system_group_data', 'index', '{"gid":"59"}', 0, 1, 1),
(466, 462, '', '个人中心分销海报', 'admin', 'setting.system_group_data', 'index', '{"gid":"60"}', 0, 1, 1),
(467, 21, '', '开发配置', 'admin', 'system', '', '[]', 0, 1, 1),
(468, 1, '', '配置组合数据附加权限', 'admin', 'setting.systemGroupData', 'index', '[]', 0, 0, 1),
(469, 21, '', '安全维护', 'admin', 'system', '', '[]', 0, 1, 1),
(470, 1, '', '配置组合数据展示页', 'admin', 'setting.systemGroup', 'index', '[]', 0, 0, 1),
(471, 462, '', '小程序精品推荐', 'admin', 'setting.system_group_data', 'index', '{"gid":"52"}', 0, 1, 1),
(473, 273, '', '秒杀配置', 'admin', 'setting.system_group_data', 'index', '{"gid":"49"}', 0, 1, 1),
(474, 289, '', '短信设置', 'admin', 'sms', 'index', '[]', 1, 1, 1),
(475, 474, '', '短信模板', 'admin', 'sms.smsTemplateApply', 'index', '[]', 0, 1, 1),
(477, 474, '', '短信购买', 'admin', 'sms.smsPay', 'index', '[]', 0, 1, 1),
(478, 474, '', '短信配置', 'admin', 'sms.smsConfig', 'index', '{"type":"4","tab_id":"18"}', 1, 1, 1),
(479, 474, '', '公共短信模板', 'admin', 'sms.smsPublicTemp', 'index', '[]', 0, 1, 1);

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='通知模板表' AUTO_INCREMENT=10 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_id` (`admin_id`,`notice_type`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `is_visit` (`is_visit`) USING BTREE,
  KEY `is_click` (`is_click`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知记录表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='身份管理表' AUTO_INCREMENT=6 ;

--
-- 转存表中的数据 `eb_system_role`
--

INSERT INTO `eb_system_role` (`id`, `role_name`, `rules`, `level`, `status`) VALUES
(1, '超级管理员', '23,24,146,147,264,265,266,263,261,262,25,142,143,369,370,285,26,190,191,394,208,207,206,198,199,200,201,202,203,204,205,197,196,192,193,194,195,329,334,335,290,170,224,225,226,227,228,151,177,398,399,407,406,400,401,402,403,405,176,449,408,409,410,411,412,413,456,337,353,371,450,415,286,148,149,229,230,231,232,233,234,235,150,352,271,254,366,368,367,272,333,238,273,473,241,349,351,350,287,306,239,307,179,217,219,218,216,312,308,313,288,293,341,340,296,318,317,316,315,314,294,300,301,302,244,295,305,304,303,374,246,297,319,321,320,269,376,451,422,423,424,425,426,427,11,360,17,127,128,126,174,452,416,421,420,417,418,419,355,356,357,358,359,354,12,97,98,99,30,93,89,124,92,91,90,123,31,94,95,96,37,19,73,42,115,116,43,44,45,82,133,134,395,396,397,84,236,129,132,289,1,470,468,112,111,53,54,55,56,117,119,108,48,144,145,118,361,270,462,471,472,466,458,459,460,461,463,464,465,372,373,153,8,63,104,105,64,65,66,67,4,68,59,102,103,62,61,60,2,74,100,75,76,77,101,78,247,248,249,250,474,478,475,476,477,479,378,255,80,283,72,38,41,39,40,284,71,33,113,114,34,35,36,175,209,210,222,223,211,212,213,214,215,21,469,130,131,173,276,362,365,364,363,386,387,388,389,390,392,393,453,278,377,447,439,444,445,446,443,442,441,440,379,448,380,382,381,467,9,52,109,110,57,58,70,7,46,106,107,47,49,50,51,69,252,237,0', 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_user_level`
--

CREATE TABLE IF NOT EXISTS `eb_system_user_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mer_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '会员名称',
  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '购买金额',
  `valid_date` int(11) NOT NULL DEFAULT '0' COMMENT '有效时间',
  `is_forever` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为永久会员',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否购买,1=购买,0=不购买',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 1=显示,0=隐藏',
  `grade` int(11) NOT NULL DEFAULT '0' COMMENT '会员等级',
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '享受折扣',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '会员卡背景',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '会员图标',
  `explain` text NOT NULL COMMENT '说明',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除.1=删除,0=未删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='设置用户等级表' AUTO_INCREMENT=7 ;

--
-- 转存表中的数据 `eb_system_user_level`
--

INSERT INTO `eb_system_user_level` (`id`, `mer_id`, `name`, `money`, `valid_date`, `is_forever`, `is_pay`, `is_show`, `grade`, `discount`, `image`, `icon`, `explain`, `add_time`, `is_del`) VALUES
(1, 0, '普通会员1', '20.00', 0, 1, 0, 1, 1, '99.00', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8cd632.jpg', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8bc1e0.png', '普通会员', 1553824559, 0),
(2, 0, '青铜会员', '0.00', 0, 1, 0, 1, 2, '98.00', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca904016.jpg', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8f0a30.png', '青铜会员', 1553824639, 0),
(3, 0, '黄铜会员', '0.00', 0, 1, 0, 1, 3, '95.00', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8c3bff.jpg', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8e9365.png', '黄铜会员', 1553824742, 0),
(4, 0, '白银会员', '0.00', 0, 1, 0, 1, 4, '94.00', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8d6ae1.jpg', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8a27f0.png', '白银会员', 1553824797, 0),
(5, 0, '黄金会员', '0.00', 0, 1, 0, 1, 5, '90.00', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8b27f1.jpg', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8aa5b9.png', '黄金会员', 1553824837, 0),
(6, 0, '钻石会员', '0.00', 0, 1, 0, 1, 6, '88.00', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca8dfe16.jpg', 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccca90d2d3.png', '钻石会员', 1553824871, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_user_task`
--

CREATE TABLE IF NOT EXISTS `eb_system_user_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '任务名称',
  `real_name` varchar(255) NOT NULL DEFAULT '' COMMENT '配置原名',
  `task_type` varchar(50) NOT NULL DEFAULT '' COMMENT '任务类型',
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '限定数',
  `level_id` int(11) NOT NULL DEFAULT '0' COMMENT '等级id',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `is_must` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否务必达成任务,1务必达成,0=满足其一',
  `illustrate` varchar(255) NOT NULL DEFAULT '' COMMENT '任务说明',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='等级任务设置' AUTO_INCREMENT=15 ;

--
-- 转存表中的数据 `eb_system_user_task`
--

INSERT INTO `eb_system_user_task` (`id`, `name`, `real_name`, `task_type`, `number`, `level_id`, `sort`, `is_show`, `is_must`, `illustrate`, `add_time`) VALUES
(1, '满足积分100分', '积分数', 'SatisfactionIntegral', 100, 1, 0, 1, 1, '', 1553827616),
(2, '消费满100元', '消费金额', 'ConsumptionAmount', 100, 1, 0, 1, 1, '', 1553827625),
(3, '满足积分200分', '积分数', 'SatisfactionIntegral', 200, 2, 0, 1, 1, '', 1553827638),
(4, '累计签到20天', '累计签到', 'CumulativeAttendance', 20, 2, 0, 1, 1, '', 1553827681),
(5, '满足积分500分', '积分数', 'SatisfactionIntegral', 500, 3, 0, 1, 1, '', 1553827695),
(6, '累计签到30天', '累计签到', 'CumulativeAttendance', 30, 3, 0, 1, 1, '', 1553827703),
(7, '满足积分1000分', '积分数', 'SatisfactionIntegral', 1000, 4, 0, 1, 1, '', 1553827731),
(8, '分享给朋友10次', '分享给朋友', 'SharingTimes', 10, 4, 0, 1, 1, '', 1553827740),
(9, '满足积分1200分', '积分数', 'SatisfactionIntegral', 1200, 5, 0, 1, 1, '', 1553827759),
(10, '累计签到60天', '累计签到', 'CumulativeAttendance', 60, 5, 0, 1, 1, '', 1553827768),
(11, '消费5次', '消费次数', 'ConsumptionFrequency', 5, 5, 0, 1, 1, '', 1553827776),
(12, '满足积分2000分', '积分数', 'SatisfactionIntegral', 2000, 6, 0, 1, 1, '', 1553827791),
(13, '消费满10000元', '消费次数', 'ConsumptionAmount', 10000, 6, 0, 1, 1, '', 1553827803),
(14, '累计签到100天', '累计签到', 'CumulativeAttendance', 100, 6, 0, 1, 1, '', 1553827814);

-- --------------------------------------------------------

--
-- 表的结构 `eb_user`
--

CREATE TABLE IF NOT EXISTS `eb_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `account` varchar(32) NOT NULL COMMENT '用户账号',
  `pwd` varchar(32) NOT NULL COMMENT '用户密码',
  `real_name` varchar(25) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `birthday` int(11) NOT NULL DEFAULT '0' COMMENT '生日',
  `card_id` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证号码',
  `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '用户备注',
  `partner_id` int(11) NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户分组id',
  `nickname` varchar(16) NOT NULL COMMENT '用户昵称',
  `avatar` varchar(256) NOT NULL COMMENT '用户头像',
  `phone` char(15) DEFAULT NULL COMMENT '手机号码',
  `add_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  `add_ip` varchar(16) NOT NULL COMMENT '添加ip',
  `last_time` int(11) unsigned NOT NULL COMMENT '最后一次登录时间',
  `last_ip` varchar(16) NOT NULL COMMENT '最后一次登录ip',
  `now_money` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '用户余额',
  `brokerage_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '佣金金额',
  `integral` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '用户剩余积分',
  `sign_num` int(11) NOT NULL DEFAULT '0' COMMENT '连续签到天数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1为正常，0为禁止',
  `level` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `spread_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推广元id',
  `spread_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推广员关联时间',
  `user_type` varchar(32) NOT NULL COMMENT '用户类型',
  `is_promoter` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推广员',
  `pay_count` int(11) unsigned DEFAULT '0' COMMENT '用户购买次数',
  `spread_count` int(11) DEFAULT '0' COMMENT '下级人数',
  `clean_time` int(11) DEFAULT '0' COMMENT '清理会员时间',
  `addres` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `adminid` int(11) unsigned DEFAULT '0' COMMENT '管理员编号 ',
  `login_type` varchar(36) NOT NULL DEFAULT '' COMMENT '用户登陆类型，h5,wechat,routine',
  PRIMARY KEY (`uid`) USING BTREE,
  KEY `account` (`account`) USING BTREE,
  KEY `spreaduid` (`spread_uid`) USING BTREE,
  KEY `level` (`level`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_promoter` (`is_promoter`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `is_default` (`is_default`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户地址表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `openid` (`uid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `pm` (`pm`) USING BTREE,
  KEY `type` (`category`,`type`,`link_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户账单表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uid` (`uid`) USING BTREE,
  KEY `province` (`province`,`city`,`district`) USING BTREE,
  KEY `is_lock` (`is_lock`) USING BTREE,
  KEY `is_del` (`is_del`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商户申请表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `extract_type` (`extract_type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `openid` (`uid`) USING BTREE,
  KEY `fail_time` (`fail_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户提现表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_group`
--

CREATE TABLE IF NOT EXISTS `eb_user_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(64) DEFAULT NULL COMMENT '用户分组名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户分组表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_level`
--

CREATE TABLE IF NOT EXISTS `eb_user_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户uid',
  `level_id` int(11) NOT NULL DEFAULT '0' COMMENT '等级vip',
  `grade` int(11) NOT NULL DEFAULT '0' COMMENT '会员等级',
  `valid_time` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
  `is_forever` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否永久',
  `mer_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:禁止,1:正常',
  `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已通知',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除,0=未删除,1=删除',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `discount` int(11) NOT NULL DEFAULT '0' COMMENT '享受折扣',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户等级记录表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户通知表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_notice_see`
--

CREATE TABLE IF NOT EXISTS `eb_user_notice_see` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL COMMENT '查看的通知id',
  `uid` int(11) NOT NULL COMMENT '查看通知的用户id',
  `add_time` int(11) NOT NULL COMMENT '查看通知的时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户通知发送记录表' AUTO_INCREMENT=1 ;

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
  `refund_price` decimal(10,2) DEFAULT '0.00' COMMENT '退款金额',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `recharge_type` (`recharge_type`) USING BTREE,
  KEY `paid` (`paid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户充值表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_sign`
--

CREATE TABLE IF NOT EXISTS `eb_user_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户uid',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '签到说明',
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '获得积分',
  `balance` int(11) NOT NULL DEFAULT '0' COMMENT '剩余积分',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到记录表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_task_finish`
--

CREATE TABLE IF NOT EXISTS `eb_user_task_finish` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL DEFAULT '0' COMMENT '任务id',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有效',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户任务完成记录表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_token`
--

CREATE TABLE IF NOT EXISTS `eb_user_token` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户 id',
  `token` varchar(300) NOT NULL COMMENT 'token',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `expires_time` datetime NOT NULL COMMENT '到期事件',
  `login_ip` varchar(32) DEFAULT NULL COMMENT '登录ip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `type` (`type`,`media_id`) USING BTREE,
  KEY `type_2` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信回复表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `openid` (`openid`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户行为记录表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='图文消息管理表' AUTO_INCREMENT=31 ;

--
-- 转存表中的数据 `eb_wechat_news_category`
--

INSERT INTO `eb_wechat_news_category` (`id`, `cate_name`, `sort`, `status`, `new_id`, `add_time`) VALUES
(21, '小程序商城前景分析', 0, 1, '51,52,58,59,60', '1565520433');

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `third_type` (`third_type`,`third_id`) USING BTREE,
  KEY `ticket` (`ticket`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信二维码管理表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `key` (`key`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `hide` (`hide`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信关键字回复表' AUTO_INCREMENT=1 ;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `tempkey` (`tempkey`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='微信模板' AUTO_INCREMENT=16 ;

--
-- 转存表中的数据 `eb_wechat_template`
--

INSERT INTO `eb_wechat_template` (`id`, `tempkey`, `name`, `content`, `tempid`, `add_time`, `status`) VALUES
(3, 'OPENTM200565259', '订单发货提醒', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n物流公司：{{keyword2.DATA}}\n物流单号：{{keyword3.DATA}}\n{{remark.DATA}}', 'KusKZOFc_4CrRU_gzuXMdMMTfFeR-OLVVuDiMyR5PiM', '1515052638', 1),
(4, 'OPENTM413386489', '订单收货通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n订单状态：{{keyword2.DATA}}\n收货时间：{{keyword3.DATA}}\n商品详情：{{keyword4.DATA}}\n{{remark.DATA}}', 'UNyz81kgsn1WZHSqmwPiF9fUkcdZghfTZvN6qtDuu54', '1515052765', 1),
(5, 'OPENTM410119152', '退款进度通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n订单金额：{{keyword2.DATA}}\n下单时间：{{keyword3.DATA}}\n{{remark.DATA}}', 'xrXtApBFv0L3-YXKkl9WYB89hJxFGfQo3jSsk2WpAwI', '1515053049', 1),
(6, 'OPENTM405847076', '帐户资金变动提醒', '{{first.DATA}}\n变动类型：{{keyword1.DATA}}\n变动时间：{{keyword2.DATA}}\n变动金额：{{keyword3.DATA}}\n{{remark.DATA}}', 'Bk3XLd1Nwk9aNF1NIPBlyTDhrgNbzJW4H23OwVQdE-s', '1515053127', 1),
(7, 'OPENTM207707249', '订单发货提醒', '\n{{first.DATA}}\n商品明细：{{keyword1.DATA}}\n下单时间：{{keyword2.DATA}}\n配送地址：{{keyword3.DATA}}\n配送人：{{keyword4.DATA}}\n联系电话：{{keyword5.DATA}}\n{{remark.DATA}}', 'KusKZOFc_4CrRU_gzuXMdMMTfFeR-OLVVuDiMyR5PiM', '1515053313', 1),
(8, 'OPENTM408237350', '服务进度提醒', '{{first.DATA}}\n服务类型：{{keyword1.DATA}}\n服务状态：{{keyword2.DATA}}\n服务时间：{{keyword3.DATA}}\n{{remark.DATA}}', 'ul2g_y0oxqEhtWoSJBbLzmnPrUwtLnIAe30MTBRL7rw', '1515483915', 1),
(9, 'OPENTM204431262', '客服通知提醒', '{{first.DATA}}\n客户名称：{{keyword1.DATA}}\n客服类型：{{keyword2.DATA}}\n提醒内容：{{keyword3.DATA}}\n通知时间：{{keyword4.DATA}}\n{{remark.DATA}}', 'j51mawxEmTuCCtMrXwSTnRfXH93qutoOebs5RG4MyHY', '1515484354', 1),
(10, 'OPENTM407456411', '拼团成功通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n团购商品：{{keyword2.DATA}}\n{{remark.DATA}}', 'CNvCAz9GIoQri-ogSCODVRANCBUQjZIxWzWvizbHVoQ', '1520063823', 1),
(11, 'OPENTM401113750', '拼团失败通知', '{{first.DATA}}\n拼团商品：{{keyword1.DATA}}\n商品金额：{{keyword2.DATA}}\n退款金额：{{keyword3.DATA}}\n{{remark.DATA}}', 'mSg4ZexW1qaQH3FCrFLe746EYMlTFsZhfTB6VI_ElYg', '1520064059', 1),
(12, 'OPENTM205213550', '订单生成通知', '{{first.DATA}}\n时间：{{keyword1.DATA}}\n商品名称：{{keyword2.DATA}}\n订单号：{{keyword3.DATA}}\n{{remark.DATA}}', 'HYD99ERso6_PcA3hAT6pzH1RmO88H6IIe8crAVaXPRE', '1528966701', 1),
(13, 'OPENTM207791277', '订单支付成功通知', '{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n支付金额：{{keyword2.DATA}}\n{{remark.DATA}}', 'hJV1d1OwWB_lbPrSaRHi9RGr5CFAF4PJcZdYeg73Mtg', '1528966759', 1),
(14, 'OPENTM410292733', '砍价成功提醒', '{{first.DATA}}\n商品名称：{{keyword1.DATA}}\n底价：{{keyword2.DATA}}\n{{remark.DATA}}', 'SxBvid65JxK6PWOhlC2sgCG_mlopY1uKekEnEvAALp4', '1564566678', 1),
(15, 'OPENTM414349441', '开团成功通知', '{{first.DATA}}\n拼团名称：{{keyword1.DATA}}\n团购价格：{{keyword2.DATA}}\n拼团人数：{{keyword3.DATA}}\n{{remark.DATA}}', 'VDTU6X1UiK438tu-qcHKc_I76CtsEWVx-_Pbf1voiws', '1564567037', 1);

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
  `now_money` decimal(8,2) unsigned DEFAULT NULL COMMENT '佣金',
  `session_key` varchar(32) DEFAULT NULL COMMENT '小程序用户会话密匙',
  `user_type` varchar(32) DEFAULT 'wechat' COMMENT '用户类型',
  PRIMARY KEY (`uid`) USING BTREE,
  KEY `groupid` (`groupid`) USING BTREE,
  KEY `subscribe_time` (`subscribe_time`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `subscribe` (`subscribe`) USING BTREE,
  KEY `unionid` (`unionid`) USING BTREE,
  KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信用户表' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
