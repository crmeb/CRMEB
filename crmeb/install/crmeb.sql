-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2018-09-15 17:41:29
-- 服务器版本： 5.5.57-log
-- PHP Version: 7.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gitcrmeb`
--

-- --------------------------------------------------------

--
-- 表的结构 `eb_article`
--

CREATE TABLE IF NOT EXISTS `eb_article` (
  `id` int(10) unsigned NOT NULL COMMENT ''文章管理ID'',
  `cid` varchar(255) DEFAULT ''1'' COMMENT ''分类id'',
  `title` varchar(255) NOT NULL COMMENT ''文章标题'',
  `author` varchar(255) DEFAULT NULL COMMENT ''文章作者'',
  `image_input` varchar(255) NOT NULL COMMENT ''文章图片'',
  `synopsis` varchar(255) DEFAULT NULL COMMENT ''文章简介'',
  `share_title` varchar(255) DEFAULT NULL COMMENT ''文章分享标题'',
  `share_synopsis` varchar(255) DEFAULT NULL COMMENT ''文章分享简介'',
  `visit` varchar(255) DEFAULT NULL COMMENT ''浏览次数'',
  `sort` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''排序'',
  `url` varchar(255) DEFAULT NULL COMMENT ''原文链接'',
  `status` tinyint(1) unsigned NOT NULL COMMENT ''状态'',
  `add_time` varchar(255) NOT NULL COMMENT ''添加时间'',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否隐藏'',
  `admin_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''管理员id'',
  `mer_id` int(10) unsigned DEFAULT ''0'' COMMENT ''商户id'',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否热门(小程序)'',
  `is_banner` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否轮播图(小程序)''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''文章管理表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_article_category`
--

CREATE TABLE IF NOT EXISTS `eb_article_category` (
  `id` int(10) unsigned NOT NULL COMMENT ''文章分类id'',
  `pid` int(11) NOT NULL DEFAULT ''0'' COMMENT ''父级ID'',
  `title` varchar(255) NOT NULL COMMENT ''文章分类标题'',
  `intr` varchar(255) DEFAULT NULL COMMENT ''文章分类简介'',
  `image` varchar(255) NOT NULL COMMENT ''文章分类图片'',
  `status` tinyint(1) unsigned NOT NULL COMMENT ''状态'',
  `sort` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''排序'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''1删除0未删除'',
  `add_time` varchar(255) NOT NULL COMMENT ''添加时间'',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否隐藏''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''文章分类表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_article_content`
--

CREATE TABLE IF NOT EXISTS `eb_article_content` (
  `nid` int(10) unsigned NOT NULL COMMENT ''文章id'',
  `content` text NOT NULL COMMENT ''文章内容''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''文章内容表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_cache`
--

CREATE TABLE IF NOT EXISTS `eb_cache` (
  `key` varchar(32) NOT NULL,
  `result` text COMMENT ''缓存数据'',
  `add_time` int(10) DEFAULT NULL COMMENT ''缓存时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `eb_cache`
--

INSERT INTO `eb_cache` (`key`, `result`, `add_time`) VALUES
(''wechat_menus'', ''[{"type":"view","name":"\\u5546\\u57ce\\u9996\\u9875","sub_button":[],"url":"http:\\/\\/shop.crmeb.net\\/wap"},{"type":"miniprogram","name":"\\u5c0f\\u7a0b\\u5e8f\\u5546\\u57ce","sub_button":[],"url":"pages\\/index\\/index","appid":"wx7bc36cccc15e4be2","pagepath":"pages\\/index\\/index"},{"type":"view","name":"\\u4e2a\\u4eba\\u4e2d\\u5fc3","sub_button":[],"url":"http:\\/\\/shop.crmeb.net\\/wap\\/my\\/index.html"}]'', 1532148956);

-- --------------------------------------------------------

--
-- 表的结构 `eb_express`
--

CREATE TABLE IF NOT EXISTS `eb_express` (
  `id` mediumint(11) unsigned NOT NULL COMMENT ''快递公司id'',
  `code` varchar(50) NOT NULL COMMENT ''快递公司简称'',
  `name` varchar(50) NOT NULL COMMENT ''快递公司全称'',
  `sort` int(11) NOT NULL COMMENT ''排序'',
  `is_show` tinyint(1) NOT NULL COMMENT ''是否显示''
) ENGINE=MyISAM AUTO_INCREMENT=426 DEFAULT CHARSET=utf8 COMMENT=''快递公司表'';

--
-- 转存表中的数据 `eb_express`
--

INSERT INTO `eb_express` (`id`, `code`, `name`, `sort`, `is_show`) VALUES
(1, ''LIMINWL'', ''利民物流'', 1, 1),
(2, ''XINTIAN'', ''鑫天顺物流'', 1, 1),
(3, ''henglu'', ''恒路物流'', 1, 1),
(4, ''klwl'', ''康力物流'', 1, 1),
(5, ''meiguo'', ''美国快递'', 1, 1),
(6, ''a2u'', ''A2U速递'', 1, 1),
(7, ''benteng'', ''奔腾物流'', 1, 1),
(8, ''ahdf'', ''德方物流'', 1, 1),
(9, ''timedg'', ''万家通'', 1, 1),
(10, ''ztong'', ''智通物流'', 1, 1),
(11, ''xindan'', ''新蛋物流'', 1, 1),
(12, ''bgpyghx'', ''挂号信'', 1, 1),
(13, ''XFHONG'', ''鑫飞鸿物流快递'', 1, 1),
(14, ''ALP'', ''阿里物流'', 1, 1),
(15, ''BFWL'', ''滨发物流'', 1, 1),
(16, ''SJWL'', ''宋军物流'', 1, 1),
(17, ''SHUNFAWL'', ''顺发物流'', 1, 1),
(18, ''TIANHEWL'', ''天河物流'', 1, 1),
(19, ''YBWL'', ''邮联物流'', 1, 1),
(20, ''SWHY'', ''盛旺货运'', 1, 1),
(21, ''TSWL'', ''汤氏物流'', 1, 1),
(22, ''YUANYUANWL'', ''圆圆物流'', 1, 1),
(23, ''BALIANGWL'', ''八梁物流'', 1, 1),
(24, ''ZGWL'', ''振刚物流'', 1, 1),
(25, ''JIAYU'', ''佳宇物流'', 1, 1),
(26, ''SHHX'', ''昊昕物流'', 1, 1),
(27, ''ande'', ''安得物流'', 1, 1),
(28, ''ppbyb'', ''贝邮宝'', 1, 1),
(29, ''dida'', ''递达快递'', 1, 1),
(30, ''jppost'', ''日本邮政'', 1, 1),
(31, ''intmail'', ''中国邮政'', 96, 1),
(32, ''HENGCHENGWL'', ''恒诚物流'', 1, 1),
(33, ''HENGFENGWL'', ''恒丰物流'', 1, 1),
(34, ''gdems'', ''广东ems快递'', 1, 1),
(35, ''xlyt'', ''祥龙运通'', 1, 1),
(36, ''gjbg'', ''国际包裹'', 1, 1),
(37, ''uex'', ''UEX'', 1, 1),
(38, ''singpost'', ''新加坡邮政'', 1, 1),
(39, ''guangdongyouzhengwuliu'', ''广东邮政'', 1, 1),
(40, ''bht'', ''BHT'', 1, 1),
(41, ''cces'', ''CCES快递'', 1, 1),
(42, ''cloudexpress'', ''CE易欧通国际速递'', 1, 1),
(43, ''dasu'', ''达速物流'', 1, 1),
(44, ''pfcexpress'', ''皇家物流'', 1, 1),
(45, ''hjs'', ''猴急送'', 1, 1),
(46, ''huilian'', ''辉联物流'', 1, 1),
(47, ''huanqiu'', ''环球速运'', 1, 1),
(48, ''huada'', ''华达快运'', 1, 1),
(49, ''htwd'', ''华通务达物流'', 1, 1),
(50, ''hipito'', ''海派通'', 1, 1),
(51, ''hqtd'', ''环球通达'', 1, 1),
(52, ''airgtc'', ''航空快递'', 1, 1),
(53, ''haoyoukuai'', ''好又快物流'', 1, 1),
(54, ''hanrun'', ''韩润物流'', 1, 1),
(55, ''ccd'', ''河南次晨达'', 1, 1),
(56, ''hfwuxi'', ''和丰同城'', 1, 1),
(57, ''Sky'', ''荷兰'', 1, 1),
(58, ''hongxun'', ''鸿讯物流'', 1, 1),
(59, ''hongjie'', ''宏捷国际物流'', 1, 1),
(60, ''httx56'', ''汇通天下物流'', 1, 1),
(61, ''lqht'', ''恒通快递'', 1, 1),
(62, ''jinguangsudikuaijian'', ''京广速递快件'', 1, 1),
(63, ''junfengguoji'', ''骏丰国际速递'', 1, 1),
(64, ''jiajiatong56'', ''佳家通'', 1, 1),
(65, ''jrypex'', ''吉日优派'', 1, 1),
(66, ''jinchengwuliu'', ''锦程国际物流'', 1, 1),
(67, ''jgwl'', ''景光物流'', 1, 1),
(68, ''pzhjst'', ''急顺通'', 1, 1),
(69, ''ruexp'', ''捷网俄全通'', 1, 1),
(70, ''jmjss'', ''金马甲'', 1, 1),
(71, ''lanhu'', ''蓝弧快递'', 1, 1),
(72, ''ltexp'', ''乐天速递'', 1, 1),
(73, ''lutong'', ''鲁通快运'', 1, 1),
(74, ''ledii'', ''乐递供应链'', 1, 1),
(75, ''lundao'', ''论道国际物流'', 1, 1),
(76, ''mailikuaidi'', ''麦力快递'', 1, 1),
(77, ''mchy'', ''木春货运'', 1, 1),
(78, ''meiquick'', ''美快国际物流'', 1, 1),
(79, ''valueway'', ''美通快递'', 1, 1),
(80, ''nuoyaao'', ''偌亚奥国际'', 1, 1),
(81, ''euasia'', ''欧亚专线'', 1, 1),
(82, ''pca'', ''澳大利亚PCA快递'', 1, 1),
(83, ''pingandatengfei'', ''平安达腾飞'', 1, 1),
(84, ''pjbest'', ''品骏快递'', 1, 1),
(85, ''qbexpress'', ''秦邦快运'', 1, 1),
(86, ''quanxintong'', ''全信通快递'', 1, 1),
(87, ''quansutong'', ''全速通国际快递'', 1, 1),
(88, ''qinyuan'', ''秦远物流'', 1, 1),
(89, ''qichen'', ''启辰国际物流'', 1, 1),
(90, ''quansu'', ''全速快运'', 1, 1),
(91, ''qzx56'', ''全之鑫物流'', 1, 1),
(92, ''qskdyxgs'', ''千顺快递'', 1, 1),
(93, ''runhengfeng'', ''全时速运'', 1, 1),
(94, ''rytsd'', ''日益通速递'', 1, 1),
(95, ''ruidaex'', ''瑞达国际速递'', 1, 1),
(96, ''shiyun'', ''世运快递'', 1, 1),
(97, ''sfift'', ''十方通物流'', 1, 1),
(98, ''stkd'', ''顺通快递'', 1, 1),
(99, ''bgn'', ''布谷鸟快递'', 1, 1),
(100, ''jiahuier'', ''佳惠尔快递'', 1, 1),
(101, ''pingyou'', ''小包'', 1, 1),
(102, ''yumeijie'', ''誉美捷快递'', 1, 1),
(103, ''meilong'', ''美龙快递'', 1, 1),
(104, ''guangtong'', ''广通速递'', 1, 1),
(105, ''STARS'', ''星晨急便'', 1, 1),
(106, ''NANHANG'', ''中国南方航空股份有限公司'', 1, 1),
(107, ''lanbiao'', ''蓝镖快递'', 1, 1),
(109, ''baotongda'', ''宝通达物流'', 1, 1),
(110, ''dashun'', ''大顺物流'', 1, 1),
(111, ''dada'', ''大达物流'', 1, 1),
(112, ''fangfangda'', ''方方达物流'', 1, 1),
(113, ''hebeijianhua'', ''河北建华物流'', 1, 1),
(114, ''haolaiyun'', ''好来运快递'', 1, 1),
(115, ''jinyue'', ''晋越快递'', 1, 1),
(116, ''kuaitao'', ''快淘快递'', 1, 1),
(117, ''peixing'', ''陪行物流'', 1, 1),
(118, ''hkpost'', ''香港邮政'', 1, 1),
(119, ''ytfh'', ''一统飞鸿快递'', 1, 1),
(120, ''zhongxinda'', ''中信达快递'', 1, 1),
(121, ''zhongtian'', ''中天快运'', 1, 1),
(122, ''zuochuan'', ''佐川急便'', 1, 1),
(123, ''chengguang'', ''程光快递'', 1, 1),
(124, ''cszx'', ''城市之星'', 1, 1),
(125, ''chuanzhi'', ''传志快递'', 1, 1),
(126, ''feibao'', ''飞豹快递'', 1, 1),
(127, ''huiqiang'', ''汇强快递'', 1, 1),
(128, ''lejiedi'', ''乐捷递'', 1, 1),
(129, ''lijisong'', ''成都立即送快递'', 1, 1),
(130, ''minbang'', ''民邦速递'', 1, 1),
(131, ''ocs'', ''OCS国际快递'', 1, 1),
(132, ''santai'', ''三态速递'', 1, 1),
(133, ''saiaodi'', ''赛澳递'', 1, 1),
(134, ''jingdong'', ''京东快递'', 1, 1),
(135, ''zengyi'', ''增益快递'', 1, 1),
(136, ''fanyu'', ''凡宇速递'', 1, 1),
(137, ''fengda'', ''丰达快递'', 1, 1),
(138, ''coe'', ''东方快递'', 1, 1),
(139, ''ees'', ''百福东方快递'', 1, 1),
(140, ''disifang'', ''递四方速递'', 1, 1),
(141, ''rufeng'', ''如风达快递'', 1, 1),
(142, ''changtong'', ''长通物流'', 1, 1),
(143, ''chengshi100'', ''城市100快递'', 1, 1),
(144, ''feibang'', ''飞邦物流'', 1, 1),
(145, ''haosheng'', ''昊盛物流'', 1, 1),
(146, ''yinsu'', ''音速速运'', 1, 1),
(147, ''kuanrong'', ''宽容物流'', 1, 1),
(148, ''tongcheng'', ''通成物流'', 1, 1),
(149, ''tonghe'', ''通和天下物流'', 1, 1),
(150, ''zhima'', ''芝麻开门'', 1, 1),
(151, ''ririshun'', ''日日顺物流'', 1, 1),
(152, ''anxun'', ''安迅物流'', 1, 1),
(153, ''baiqian'', ''百千诚国际物流'', 1, 1),
(154, ''chukouyi'', ''出口易'', 1, 1),
(155, ''diantong'', ''店通快递'', 1, 1),
(156, ''dajin'', ''大金物流'', 1, 1),
(157, ''feite'', ''飞特物流'', 1, 1),
(159, ''gnxb'', ''国内小包'', 1, 1),
(160, ''huacheng'', ''华诚物流'', 1, 1),
(161, ''huahan'', ''华翰物流'', 1, 1),
(162, ''hengyu'', ''恒宇运通'', 1, 1),
(163, ''huahang'', ''华航快递'', 1, 1),
(164, ''jiuyi'', ''久易快递'', 1, 1),
(165, ''jiete'', ''捷特快递'', 1, 1),
(166, ''jingshi'', ''京世物流'', 1, 1),
(167, ''kuayue'', ''跨越快递'', 1, 1),
(168, ''mengsu'', ''蒙速快递'', 1, 1),
(169, ''nanbei'', ''南北快递'', 1, 1),
(171, ''pinganda'', ''平安达快递'', 1, 1),
(172, ''ruifeng'', ''瑞丰速递'', 1, 1),
(173, ''rongqing'', ''荣庆物流'', 1, 1),
(174, ''suijia'', ''穗佳物流'', 1, 1),
(175, ''simai'', ''思迈快递'', 1, 1),
(176, ''suteng'', ''速腾快递'', 1, 1),
(177, ''shengbang'', ''晟邦物流'', 1, 1),
(178, ''suchengzhaipei'', ''速呈宅配'', 1, 1),
(179, ''wuhuan'', ''五环速递'', 1, 1),
(180, ''xingchengzhaipei'', ''星程宅配'', 1, 1),
(181, ''yinjie'', ''顺捷丰达'', 1, 1),
(183, ''yanwen'', ''燕文物流'', 1, 1),
(184, ''zongxing'', ''纵行物流'', 1, 1),
(185, ''aae'', ''AAE快递'', 1, 1),
(186, ''dhl'', ''DHL快递'', 1, 1),
(187, ''feihu'', ''飞狐快递'', 1, 1),
(188, ''shunfeng'', ''顺丰速运'', 92, 1),
(189, ''spring'', ''春风物流'', 1, 1),
(190, ''yidatong'', ''易达通快递'', 1, 1),
(191, ''PEWKEE'', ''彪记快递'', 1, 1),
(192, ''PHOENIXEXP'', ''凤凰快递'', 1, 1),
(193, ''CNGLS'', ''GLS快递'', 1, 1),
(194, ''BHTEXP'', ''华慧快递'', 1, 1),
(195, ''B2B'', ''卡行天下'', 1, 1),
(196, ''PEISI'', ''配思货运'', 1, 1),
(197, ''SUNDAPOST'', ''上大物流'', 1, 1),
(198, ''SUYUE'', ''苏粤货运'', 1, 1),
(199, ''F5XM'', ''伍圆速递'', 1, 1),
(200, ''GZWENJIE'', ''文捷航空速递'', 1, 1),
(201, ''yuancheng'', ''远成物流'', 1, 1),
(202, ''dpex'', ''DPEX快递'', 1, 1),
(203, ''anjie'', ''安捷快递'', 1, 1),
(204, ''jldt'', ''嘉里大通'', 1, 1),
(205, ''yousu'', ''优速快递'', 1, 1),
(206, ''wanbo'', ''万博快递'', 1, 1),
(207, ''sure'', ''速尔物流'', 1, 1),
(208, ''sutong'', ''速通物流'', 1, 1),
(209, ''JUNCHUANWL'', ''骏川物流'', 1, 1),
(210, ''guada'', ''冠达快递'', 1, 1),
(211, ''dsu'', ''D速快递'', 1, 1),
(212, ''LONGSHENWL'', ''龙胜物流'', 1, 1),
(213, ''abc'', ''爱彼西快递'', 1, 1),
(214, ''eyoubao'', ''E邮宝'', 1, 1),
(215, ''aol'', ''AOL快递'', 1, 1),
(216, ''jixianda'', ''急先达物流'', 1, 1),
(217, ''haihong'', ''山东海红快递'', 1, 1),
(218, ''feiyang'', ''飞洋快递'', 1, 1),
(219, ''rpx'', ''RPX保时达'', 1, 1),
(220, ''zhaijisong'', ''宅急送'', 1, 1),
(221, ''tiantian'', ''天天快递'', 99, 1),
(222, ''yunwuliu'', ''云物流'', 1, 1),
(223, ''jiuye'', ''九曳供应链'', 1, 1),
(224, ''bsky'', ''百世快运'', 1, 1),
(225, ''higo'', ''黑狗物流'', 1, 1),
(226, ''arke'', ''方舟速递'', 1, 1),
(227, ''zwsy'', ''中外速运'', 1, 1),
(228, ''jxy'', ''吉祥邮'', 1, 1),
(229, ''aramex'', ''Aramex'', 1, 1),
(230, ''guotong'', ''国通快递'', 1, 1),
(231, ''jiayi'', ''佳怡物流'', 1, 1),
(232, ''longbang'', ''龙邦快运'', 1, 1),
(233, ''minhang'', ''民航快递'', 1, 1),
(234, ''quanyi'', ''全一快递'', 1, 1),
(235, ''quanchen'', ''全晨快递'', 1, 1),
(236, ''usps'', ''USPS快递'', 1, 1),
(237, ''xinbang'', ''新邦物流'', 1, 1),
(238, ''yuanzhi'', ''元智捷诚快递'', 1, 1),
(239, ''zhongyou'', ''中邮物流'', 1, 1),
(240, ''yuxin'', ''宇鑫物流'', 1, 1),
(241, ''cnpex'', ''中环快递'', 1, 1),
(242, ''shengfeng'', ''盛丰物流'', 1, 1),
(243, ''yuantong'', ''圆通速递'', 97, 1),
(244, ''jiayunmei'', ''加运美物流'', 1, 1),
(245, ''ywfex'', ''源伟丰快递'', 1, 1),
(246, ''xinfeng'', ''信丰物流'', 1, 1),
(247, ''wanxiang'', ''万象物流'', 1, 1),
(248, ''menduimen'', ''门对门'', 1, 1),
(249, ''mingliang'', ''明亮物流'', 1, 1),
(250, ''fengxingtianxia'', ''风行天下'', 1, 1),
(251, ''gongsuda'', ''共速达物流'', 1, 1),
(252, ''zhongtong'', ''中通快递'', 100, 1),
(253, ''quanritong'', ''全日通快递'', 1, 1),
(254, ''ems'', ''EMS'', 1, 1),
(255, ''wanjia'', ''万家物流'', 1, 1),
(256, ''yuntong'', ''运通快递'', 1, 1),
(257, ''feikuaida'', ''飞快达物流'', 1, 1),
(258, ''haimeng'', ''海盟速递'', 1, 1),
(259, ''zhongsukuaidi'', ''中速快件'', 1, 1),
(260, ''yuefeng'', ''越丰快递'', 1, 1),
(261, ''shenghui'', ''盛辉物流'', 1, 1),
(262, ''datian'', ''大田物流'', 1, 1),
(263, ''quanjitong'', ''全际通快递'', 1, 1),
(264, ''longlangkuaidi'', ''隆浪快递'', 1, 1),
(265, ''neweggozzo'', ''新蛋奥硕物流'', 1, 1),
(266, ''shentong'', ''申通快递'', 95, 1),
(267, ''haiwaihuanqiu'', ''海外环球'', 1, 1),
(268, ''yad'', ''源安达快递'', 1, 1),
(269, ''jindawuliu'', ''金大物流'', 1, 1),
(270, ''sevendays'', ''七天连锁'', 1, 1),
(271, ''tnt'', ''TNT快递'', 1, 1),
(272, ''huayu'', ''天地华宇物流'', 1, 1),
(273, ''lianhaotong'', ''联昊通快递'', 1, 1),
(274, ''nengda'', ''港中能达快递'', 1, 1),
(275, ''LBWL'', ''联邦物流'', 1, 1),
(276, ''ontrac'', ''onTrac'', 1, 1),
(277, ''feihang'', ''原飞航快递'', 1, 1),
(278, ''bangsongwuliu'', ''邦送物流'', 1, 1),
(279, ''huaxialong'', ''华夏龙物流'', 1, 1),
(280, ''ztwy'', ''中天万运快递'', 1, 1),
(281, ''fkd'', ''飞康达物流'', 1, 1),
(282, ''anxinda'', ''安信达快递'', 1, 1),
(283, ''quanfeng'', ''全峰快递'', 1, 1),
(284, ''shengan'', ''圣安物流'', 1, 1),
(285, ''jiaji'', ''佳吉物流'', 1, 1),
(286, ''yunda'', ''韵达快运'', 94, 1),
(287, ''ups'', ''UPS快递'', 1, 1),
(288, ''debang'', ''德邦物流'', 1, 1),
(289, ''yafeng'', ''亚风速递'', 1, 1),
(290, ''kuaijie'', ''快捷速递'', 98, 1),
(291, ''huitong'', ''百世快递'', 93, 1),
(293, ''aolau'', ''AOL澳通速递'', 1, 1),
(294, ''anneng'', ''安能物流'', 1, 1),
(295, ''auexpress'', ''澳邮中国快运'', 1, 1),
(296, ''exfresh'', ''安鲜达'', 1, 1),
(297, ''bcwelt'', ''BCWELT'', 1, 1),
(298, ''youzhengguonei'', ''挂号信'', 1, 1),
(299, ''xiaohongmao'', ''北青小红帽'', 1, 1),
(300, ''lbbk'', ''宝凯物流'', 1, 1),
(301, ''byht'', ''博源恒通'', 1, 1),
(302, ''idada'', ''百成大达物流'', 1, 1),
(303, ''baitengwuliu'', ''百腾物流'', 1, 1),
(304, ''birdex'', ''笨鸟海淘'', 1, 1),
(305, ''bsht'', ''百事亨通'', 1, 1),
(306, ''dayang'', ''大洋物流快递'', 1, 1),
(307, ''dechuangwuliu'', ''德创物流'', 1, 1),
(308, ''donghanwl'', ''东瀚物流'', 1, 1),
(309, ''dfpost'', ''达方物流'', 1, 1),
(310, ''dongjun'', ''东骏快捷物流'', 1, 1),
(311, ''dindon'', ''叮咚澳洲转运'', 1, 1),
(312, ''dazhong'', ''大众佐川急便'', 1, 1),
(313, ''decnlh'', ''德中快递'', 1, 1),
(314, ''dekuncn'', ''德坤供应链'', 1, 1),
(315, ''eshunda'', ''俄顺达'', 1, 1),
(316, ''ewe'', ''EWE全球快递'', 1, 1),
(317, ''fedexuk'', ''FedEx英国'', 1, 1),
(318, ''fox'', ''FOX国际速递'', 1, 1),
(319, ''rufengda'', ''凡客如风达'', 1, 1),
(320, ''fandaguoji'', ''颿达国际快递'', 1, 1),
(321, ''hnfy'', ''飞鹰物流'', 1, 1),
(322, ''flysman'', ''飞力士物流'', 1, 1),
(323, ''sccod'', ''丰程物流'', 1, 1),
(324, ''farlogistis'', ''泛远国际物流'', 1, 1),
(325, ''gsm'', ''GSM'', 1, 1),
(326, ''gaticn'', ''GATI快递'', 1, 1),
(327, ''gts'', ''GTS快递'', 1, 1),
(328, ''gangkuai'', ''港快速递'', 1, 1),
(329, ''gtsd'', ''高铁速递'', 1, 1),
(330, ''tiandihuayu'', ''华宇物流'', 1, 1),
(331, ''huangmajia'', ''黄马甲快递'', 1, 1),
(332, ''ucs'', ''合众速递'', 1, 1),
(333, ''huoban'', ''伙伴物流'', 1, 1),
(334, ''nedahm'', ''红马速递'', 1, 1),
(335, ''huiwen'', ''汇文配送'', 1, 1),
(336, ''nmhuahe'', ''华赫物流'', 1, 1),
(337, ''hangyu'', ''航宇快递'', 1, 1),
(338, ''minsheng'', ''闽盛物流'', 1, 1),
(339, ''riyu'', ''日昱物流'', 1, 1),
(340, ''sxhongmajia'', ''山西红马甲'', 1, 1),
(341, ''syjiahuier'', ''沈阳佳惠尔'', 1, 1),
(342, ''shlindao'', ''上海林道货运'', 1, 1),
(343, ''shunjiefengda'', ''顺捷丰达'', 1, 1),
(344, ''subida'', ''速必达物流'', 1, 1),
(345, ''bphchina'', ''速方国际物流'', 1, 1),
(346, ''sendtochina'', ''速递中国'', 1, 1),
(347, ''suning'', ''苏宁快递'', 1, 1),
(348, ''sihaiet'', ''四海快递'', 1, 1),
(349, ''tianzong'', ''天纵物流'', 1, 1),
(350, ''chinatzx'', ''同舟行物流'', 1, 1),
(351, ''nntengda'', ''腾达速递'', 1, 1),
(352, ''sd138'', ''泰国138'', 1, 1),
(353, ''tongdaxing'', ''通达兴物流'', 1, 1),
(354, ''tlky'', ''天联快运'', 1, 1),
(355, ''youshuwuliu'', ''UC优速快递'', 1, 1),
(356, ''ueq'', ''UEQ快递'', 1, 1),
(357, ''weitepai'', ''微特派快递'', 1, 1),
(358, ''wtdchina'', ''威时沛运'', 1, 1),
(359, ''wzhaunyun'', ''微转运'', 1, 1),
(360, ''gswtkd'', ''万通快递'', 1, 1),
(361, ''wotu'', ''渥途国际速运'', 1, 1),
(362, ''xiyoute'', ''希优特快递'', 1, 1),
(363, ''xilaikd'', ''喜来快递'', 1, 1),
(364, ''xsrd'', ''鑫世锐达'', 1, 1),
(365, ''xtb'', ''鑫通宝物流'', 1, 1),
(366, ''xintianjie'', ''信天捷快递'', 1, 1),
(367, ''xaetc'', ''西安胜峰'', 1, 1),
(368, ''xianfeng'', ''先锋快递'', 1, 1),
(369, ''sunspeedy'', ''新速航'', 1, 1),
(370, ''xipost'', ''西邮寄'', 1, 1),
(371, ''sinatone'', ''信联通'', 1, 1),
(372, ''sunjex'', ''新杰物流'', 1, 1),
(373, ''yundaexus'', ''韵达美国件'', 1, 1),
(374, ''yxwl'', ''宇鑫物流'', 1, 1),
(375, ''yitongda'', ''易通达'', 1, 1),
(376, ''yiqiguojiwuliu'', ''一柒物流'', 1, 1),
(377, ''yilingsuyun'', ''亿领速运'', 1, 1),
(378, ''yujiawuliu'', ''煜嘉物流'', 1, 1),
(379, ''gml'', ''英脉物流'', 1, 1),
(380, ''leopard'', ''云豹国际货运'', 1, 1),
(381, ''czwlyn'', ''云南中诚'', 1, 1),
(382, ''sdyoupei'', ''优配速运'', 1, 1),
(383, ''yongchang'', ''永昌物流'', 1, 1),
(384, ''yufeng'', ''御风速运'', 1, 1),
(385, ''yamaxunwuliu'', ''亚马逊物流'', 1, 1),
(386, ''yousutongda'', ''优速通达'', 1, 1),
(387, ''yishunhang'', ''亿顺航'', 1, 1),
(388, ''yongwangda'', ''永旺达快递'', 1, 1),
(389, ''ecmscn'', ''易满客'', 1, 1),
(390, ''yingchao'', ''英超物流'', 1, 1),
(391, ''edlogistics'', ''益递物流'', 1, 1),
(392, ''yyexpress'', ''远洋国际'', 1, 1),
(393, ''onehcang'', ''一号仓'', 1, 1),
(394, ''ycgky'', ''远成快运'', 1, 1),
(395, ''lineone'', ''一号线'', 1, 1),
(396, ''ypsd'', ''壹品速递'', 1, 1),
(397, ''vipexpress'', ''鹰运国际速递'', 1, 1),
(398, ''el56'', ''易联通达物流'', 1, 1),
(399, ''yyqc56'', ''一运全成物流'', 1, 1),
(400, ''zhongtie'', ''中铁快运'', 1, 1),
(401, ''ZTKY'', ''中铁物流'', 1, 1),
(402, ''zzjh'', ''郑州建华快递'', 1, 1),
(403, ''zhongruisudi'', ''中睿速递'', 1, 1),
(404, ''zhongwaiyun'', ''中外运速递'', 1, 1),
(405, ''zengyisudi'', ''增益速递'', 1, 1),
(406, ''sujievip'', ''郑州速捷'', 1, 1),
(407, ''zhichengtongda'', ''至诚通达快递'', 1, 1),
(408, ''zhdwl'', ''众辉达物流'', 1, 1),
(409, ''kuachangwuliu'', ''直邮易'', 1, 1),
(410, ''topspeedex'', ''中运全速'', 1, 1),
(411, ''otobv'', ''中欧快运'', 1, 1),
(412, ''zsky123'', ''准实快运'', 1, 1),
(413, ''donghong'', ''东红物流'', 1, 1),
(414, ''kuaiyouda'', ''快优达速递'', 1, 1),
(415, ''balunzhi'', ''巴伦支快递'', 1, 1),
(416, ''hutongwuliu'', ''户通物流'', 1, 1),
(417, ''xianchenglian'', ''西安城联速递'', 1, 1),
(418, ''youbijia'', ''邮必佳'', 1, 1),
(419, ''feiyuan'', ''飞远物流'', 1, 1),
(420, ''chengji'', ''城际速递'', 1, 1),
(421, ''huaqi'', ''华企快运'', 1, 1),
(422, ''yibang'', ''一邦快递'', 1, 1),
(423, ''citylink'', ''CityLink快递'', 1, 1),
(424, ''meixi'', ''美西快递'', 1, 1),
(425, ''acs'', ''ACS'', 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_access_token`
--

CREATE TABLE IF NOT EXISTS `eb_routine_access_token` (
  `id` int(11) unsigned NOT NULL COMMENT ''小程序access_token表ID'',
  `access_token` varchar(256) NOT NULL COMMENT ''openid'',
  `stop_time` int(11) unsigned NOT NULL COMMENT ''添加时间''
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT=''小程序access_token表'';

--
-- 转存表中的数据 `eb_routine_access_token`
--

INSERT INTO `eb_routine_access_token` (`id`, `access_token`, `stop_time`) VALUES
(1, ''12_BwfjO2SAOP1dtZLQUtKrHGC2pv_M1DD51LhyOroDqwUMS3JRonwgSkympBk6kYbHvEecjRBcGAGDG47PPL8R1voD9V3wwpoZ9_rhtvdSS9ku9ehU2jCmnCzmQ5CG7RKW2t4Z7A9aNvaFMBbsDVOaAFASKD'', 1534306647);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_form_id`
--

CREATE TABLE IF NOT EXISTS `eb_routine_form_id` (
  `id` int(11) unsigned NOT NULL COMMENT ''表单ID表ID'',
  `uid` int(11) DEFAULT ''0'' COMMENT ''用户uid'',
  `form_id` varchar(32) NOT NULL COMMENT ''表单ID'',
  `stop_time` int(11) unsigned DEFAULT NULL COMMENT ''表单ID失效时间'',
  `status` tinyint(1) unsigned DEFAULT ''0'' COMMENT ''状态1 未使用 2不能使用''
) ENGINE=MyISAM AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COMMENT=''表单id表记录表'';

--
-- 转存表中的数据 `eb_routine_form_id`
--

INSERT INTO `eb_routine_form_id` (`id`, `uid`, `form_id`, `stop_time`, `status`) VALUES
(1, NULL, ''1527218790919'', 1527737190, 1),
(2, NULL, ''1527218795445'', 1527737195, 1),
(3, NULL, ''1527218802623'', 1527737202, 1),
(4, NULL, ''1527219992546'', 1527738392, 1),
(5, NULL, ''18ed60695c60477263362db1fcd57711'', 1528028492, 1),
(6, NULL, ''4ac27cf74f1874089e7b9978b1655330'', 1528028822, 1),
(7, NULL, ''d1c09ce577252707a6a3d37749e4fb89'', 1528041339, 1),
(8, NULL, ''587137c710636610dfac3434468bf59f'', 1528041357, 1),
(9, NULL, ''fd26d2192ac2c9c39f33c4dbca0feb23'', 1528041362, 1),
(10, NULL, ''e1ec0351a083cf7cdb16b01edb33e835'', 1528041455, 1),
(11, NULL, ''3f37c5dc20bc4f70f542def3ffc059dc'', 1528041457, 1),
(12, NULL, ''999c5282f1be9f952ddb126e38575c8c'', 1528073720, 1),
(13, NULL, ''1527555687142'', 1528074086, 1),
(14, NULL, ''1527561323929'', 1528079722, 1),
(15, NULL, ''1527561340989'', 1528079739, 1),
(16, NULL, ''1527561825052'', 1528080224, 1),
(17, NULL, ''1527580340878'', 1528098739, 1),
(18, NULL, ''1527580488444'', 1528098887, 1),
(19, NULL, ''7025a9ade58735b2042eb1736e534cdf'', 1528161187, 1),
(20, NULL, ''b076d5a90fa7e4bd80ac8a9ca2d11f22'', 1528161734, 1),
(21, NULL, ''aca6b85e10199c71edfbd17094660dda'', 1528161867, 1),
(22, NULL, ''7c375bfc6f48e99dd3c7ebae27be33be'', 1528161872, 1),
(23, NULL, ''d6cf09152119814664124cd5ef7285f6'', 1528161877, 1),
(24, NULL, ''288510d01ec0c14182ea21c162c41e3d'', 1528161890, 1),
(25, NULL, ''57ba79e638b3f37903901b12bbb61c65'', 1528162265, 1),
(26, NULL, ''1ab1098aff8af0331a6e6d39436b295a'', 1528162272, 1),
(27, NULL, ''957bc8ef5ae594f4b21cfd05f499eee3'', 1528162389, 1),
(28, NULL, ''a16f9c7fcbfe27266e4dc15be7157def'', 1528162404, 1),
(29, NULL, ''bbc619f610dac7327861d1021a1a203b'', 1528163111, 1),
(30, NULL, ''1527678037164'', 1528196436, 1),
(31, NULL, ''1527681662506'', 1528200061, 1),
(32, 167, ''1c9b21e4dfa2e7adea3e9232905226ad'', 1529464206, 1),
(33, 108, ''1529579041074'', 1530097441, 1),
(34, 201, ''1529579324454'', 1530097725, 1),
(35, 204, ''1529580872559'', 1530099273, 1),
(36, 200, ''8f28fcfb33a0ef769ad41ad04031f9a8'', 1530099285, 1),
(37, 200, ''615b8299361adfcf3b8a08851efe77d9'', 1530099308, 1),
(38, 205, ''1529582976447'', 1530101198, 1),
(39, 111, ''95fef45924f9f18813703cf8e0b0e4ac'', 1530185676, 1),
(40, 200, ''153a471c1874e05aeb00ca9209170fac'', 1530270537, 1),
(41, 200, ''29bc7c3080f1fada7f08b4408d35d308'', 1530270539, 1),
(42, 200, ''36ea9f4451763b3f8746c6eb3abf06d5'', 1530270580, 1),
(43, 214, ''1529752200618'', 1530270601, 2),
(44, 220, ''1529892780729'', 1530411191, 1),
(45, 220, ''1529892787607'', 1530411191, 1),
(46, 220, ''1529892787798'', 1530411192, 1),
(47, 220, ''1529892787971'', 1530411192, 1),
(48, 220, ''1529892787455'', 1530411201, 1),
(49, 220, ''1529892786813'', 1530411201, 1),
(50, 220, ''1529892787269'', 1530411201, 1),
(51, 220, ''1529892784774'', 1530411201, 1),
(52, 220, ''1529892788272'', 1530411201, 1),
(53, 220, ''1529892788445'', 1530411201, 1),
(54, 220, ''1529892788604'', 1530411201, 1),
(55, 220, ''1529892788932'', 1530411202, 1),
(56, 220, ''1529892789101'', 1530411202, 1),
(57, 220, ''1529892789427'', 1530411202, 1),
(58, 220, ''1529892789286'', 1530411202, 1),
(59, 220, ''1529892789590'', 1530411202, 1),
(60, 220, ''1529892789761'', 1530411203, 1),
(61, 220, ''1529892788112'', 1530411203, 1),
(62, 220, ''1529892789931'', 1530411204, 1),
(63, 220, ''1529892790103'', 1530411204, 1),
(64, 220, ''1529892790282'', 1530411204, 1),
(65, 220, ''1529892790441'', 1530411204, 1),
(66, 220, ''1529892790604'', 1530411204, 1),
(67, 108, ''1529906238260'', 1530424639, 1),
(68, 233, ''7017a68780247b9b6d450940c81a036b'', 1530497888, 1),
(69, 255, ''3722157beb438d85215d3b1e6a0aa086'', 1530832636, 1),
(70, 284, ''05425d5a66e6ec39bb6dda8fc575897d'', 1531527554, 1),
(71, 284, ''7134b20b96e0c52071ca55dd43dcb740'', 1531527654, 1),
(72, 284, ''67b8007eb44dea3e21d1303c4627d859'', 1531527654, 1),
(73, 284, ''497b10bd921e61951504bb17045ccfb7'', 1531527655, 1),
(74, 284, ''998fe23bae16a35c1bc408277bb4833b'', 1531527655, 1),
(75, 284, ''33beeecb32f504cc5a56abe698590c28'', 1531527655, 1),
(76, 111, ''257c71cf394d97a056aa41143842027e'', 1531733488, 2),
(77, 111, ''108808ab96b9482425e27aa1c8b4ed2f'', 1531733491, 2),
(78, 111, ''28efdbca8f8bb079c19dbf70dee73c30'', 1531733498, 1),
(79, 111, ''155cce7f6101826b000c388c484d1a85'', 1531884073, 1),
(80, 167, ''1c41b9236234c363b1635a5e8f7bf4b3'', 1531910918, 1),
(81, 297, ''279ae6f7b87db40697959eb399ab4811'', 1532307184, 1),
(82, 297, ''59729e139999393a7968466f6a36ade6'', 1532307185, 1),
(83, 313, ''1fa36a911ca520d0b1dfbd4a72e1e2e0'', 1532421107, 1),
(84, 331, ''1532087781800'', 1532606183, 1),
(85, 331, ''1532087817201'', 1532606218, 1),
(86, 3, ''1532587856196'', 1533106256, 1),
(87, 3, ''1532587873382'', 1533106273, 1),
(88, 3, ''1532588350996'', 1533106750, 1),
(89, 2, ''80519719e947dc9129e746db73a25f3c'', 1533178687, 1),
(90, 2, ''3c9cb2a5fb31674d5558adda1d076cd0'', 1533178772, 1),
(91, 2, ''3906bc9da1a9938806098f87d9aac7d5'', 1533178889, 1),
(92, 2, ''5b3607db0a11927c1a09ec8d691a0de1'', 1533179200, 1),
(93, 3, ''89b61ca46cc27bed2720d0001e9db8e2'', 1533194195, 1),
(94, 36, ''1532831506204'', 1533349908, 1),
(95, 16, ''420d8468961c963b6c7a39907c1bdd9d'', 1533459111, 1),
(96, 12, ''22262a90af8b0ba7d1ed7d4e5eeb86c6'', 1533547996, 1),
(97, 12, ''0081bb67e7714757f4a07d5c3e6b086d'', 1533547998, 1),
(98, 4, ''42d5fdf09ccb1c9f6fefd09f13ea89fc'', 1534132961, 1),
(99, 38, ''25d6321fc7e109d1e35c47737a8aa7e1'', 1534147788, 1),
(100, 38, ''863ea4f4a8661938b2c0f202664d9437'', 1534147791, 1),
(101, 38, ''31cb89644d2994a54707f9b5ea9e1023'', 1534147888, 1),
(102, 20, ''08947883608babebd546c1b5072d3a92'', 1534154855, 1),
(103, 20, ''04463a9cebf7f41967578e03256818cb'', 1534154856, 1),
(104, 38, ''249b6abfe368e9d9ea4febab1f1d47ba'', 1534154912, 1),
(105, 20, ''80e51f277979f68ef81b00580fc09914'', 1534154940, 1),
(106, 69, ''5e2b5fd7b165cdaeabe40978456b3db4'', 1534157549, 1),
(107, 69, ''4e74f7833fc553e0f8c9c6276fa88d8b'', 1534157551, 1),
(108, 69, ''1cc154881ddbe5ac9f67079502e2cd8c'', 1534157552, 1),
(109, 69, ''50fd7a788e21384d83ddb10969ae0789'', 1534168668, 1),
(110, 20, ''5195192eb3c555f95c9c8826b42dfa04'', 1534234252, 1),
(111, 69, ''9f5bfe1cea77be9fc6794270082a934d'', 1534327834, 1),
(112, 69, ''55dfdd4d213d43bee59de007c3b16157'', 1534327836, 1),
(113, 69, ''5f1b2e497d704b798fca1662cc1c489b'', 1534327836, 1),
(114, 69, ''1fae9219e578d6a73156288e23af9f56'', 1534327837, 1),
(115, 69, ''4d727c5c756d8527e1e7ba73f817e8cd'', 1534327839, 1),
(116, 90, ''1534225778227'', 1534744179, 1),
(117, 90, ''1534225780683'', 1534744181, 1),
(118, 90, ''1534225782662'', 1534744183, 1),
(119, 9, ''053fe37911d3cd904437c7d35b8925df'', 1534757449, 1),
(120, 9, ''80008ab3085e3df4ea9885ca3ae4b570'', 1534761869, 1),
(121, 9, ''94ccd0ebb24a1c139a193192158626ee'', 1534761871, 1),
(122, 9, ''9a50ce063bf17fcc688cebebfb8b3e13'', 1534761893, 1),
(123, 9, ''9509be467ad133a10deb2bb9670d3a1e'', 1534762146, 1),
(124, 9, ''1ca15780bcf96beb330815019a213e30'', 1534762148, 1),
(125, 9, ''4d8fa5bd37b713a4538d44b9fae3ce16'', 1534762766, 1),
(126, 9, ''44d847fd08e64a7e9d29047ae8f67684'', 1534762772, 1),
(127, 9, ''22db8d2d1575f10e45fe5aca90f1e4a2'', 1534907515, 1),
(128, 9, ''3e7f64be264971fe81488717661a2836'', 1534907536, 1),
(129, 9, ''12e26cc391d5b420924a22d927395f31'', 1534907536, 1),
(130, 9, ''446108a5dcc7267c701d14cd7790d82a'', 1534907543, 1),
(131, 102, ''1534405722892'', 1534924122, 1),
(132, 102, ''1534405735813'', 1534924135, 1),
(133, 102, ''1534405737697'', 1534924137, 1),
(134, 1, ''10233218487fdda24f82f521049afc81'', 1535005534, 1),
(135, 91, ''426b31051314ac4bc51794a9120ee8ea'', 1535012019, 1),
(136, 91, ''917f5643b36171daaf3219b6c7213d55'', 1535073308, 1),
(137, 107, ''41b9feea378b755517b6bc886cfd0447'', 1535074989, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_routine_template`
--

CREATE TABLE IF NOT EXISTS `eb_routine_template` (
  `id` int(10) unsigned NOT NULL COMMENT ''模板id'',
  `tempkey` char(50) NOT NULL COMMENT ''模板编号'',
  `name` char(100) NOT NULL COMMENT ''模板名'',
  `content` varchar(1000) NOT NULL COMMENT ''回复内容'',
  `tempid` char(100) DEFAULT NULL COMMENT ''模板ID'',
  `add_time` varchar(15) NOT NULL COMMENT ''添加时间'',
  `status` tinyint(4) NOT NULL DEFAULT ''0'' COMMENT ''状态''
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT=''微信模板'';

--
-- 转存表中的数据 `eb_routine_template`
--

INSERT INTO `eb_routine_template` (`id`, `tempkey`, `name`, `content`, `tempid`, `add_time`, `status`) VALUES
(12, ''AT0177'', ''订单配送通知'', ''订单编号{{keyword1.DATA}}\n配送员{{keyword2.DATA}}\n联系电话{{keyword3.DATA}}\n配送时间{{keyword4.DATA}}\n备注{{keyword5.DATA}}'', ''mCxm8mO_ZeETohNq7sFMlcf0vWdAnCJylKog71J68JM'', ''1534469109'', 1),
(13, ''AT0007'', ''订单发货提醒'', ''订单号{{keyword1.DATA}}\n快递公司{{keyword2.DATA}}\n快递单号{{keyword3.DATA}}\n发货时间{{keyword4.DATA}}\n备注{{keyword5.DATA}}'', ''XQlyO_b3QocHBOrC69bfOCaOJq5kdKXQcdQtCO11sA0'', ''1534469928'', 1),
(14, ''AT0787'', ''退款成功通知'', ''订单号{{keyword1.DATA}}\n退款时间{{keyword2.DATA}}\n退款金额{{keyword3.DATA}}\n退款方式{{keyword4.DATA}}\n备注{{keyword5.DATA}}'', ''gQi8X-wuOYAwdVRBXaJVwfAXQ0ngjMxYcYVS0GT1anI'', ''1534469993'', 1),
(15, ''AT0009'', ''订单支付成功通知'', ''单号{{keyword1.DATA}}\n下单时间{{keyword2.DATA}}\n订单状态{{keyword3.DATA}}\n支付金额{{keyword4.DATA}}\n支付方式{{keyword5.DATA}}'', ''x5Jw630Rp63T34kv0Q43RaeVKtk5OFKDNkwcrwMp9FM'', ''1534470043'', 1),
(16, ''AT1173'', ''砍价成功通知'', ''商品名称{{keyword1.DATA}}\n砍价金额{{keyword2.DATA}}\n底价{{keyword3.DATA}}\n砍掉价格{{keyword4.DATA}}\n支付金额{{keyword5.DATA}}\n备注{{keyword6.DATA}}'', ''FofE1ABYV1iXkNFIvEOUy4j5lInos20KCwIW6gyZ2nM'', ''1534470085'', 1),
(17, ''AT0036'', ''退款通知'', ''订单编号{{keyword1.DATA}}\n退款原因{{keyword2.DATA}}\n退款时间{{keyword3.DATA}}\n退款金额{{keyword4.DATA}}\n退款方式{{keyword5.DATA}}'', ''JhmCRYO7ahP6nbCb6oO-BPYz8lIP2u9xs-CkZ63Z4HI'', ''1534470134'', 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_bargain`
--

CREATE TABLE IF NOT EXISTS `eb_store_bargain` (
  `id` int(11) unsigned NOT NULL COMMENT ''砍价产品ID'',
  `product_id` int(11) unsigned NOT NULL COMMENT ''关联产品ID'',
  `title` varchar(255) NOT NULL COMMENT ''砍价活动名称'',
  `image` varchar(150) NOT NULL COMMENT ''砍价活动图片'',
  `unit_name` varchar(16) DEFAULT NULL COMMENT ''单位名称'',
  `stock` int(11) unsigned DEFAULT NULL COMMENT ''库存'',
  `sales` int(11) unsigned DEFAULT NULL COMMENT ''销量'',
  `images` varchar(1000) NOT NULL COMMENT ''砍价产品轮播图'',
  `start_time` int(11) unsigned NOT NULL COMMENT ''砍价开启时间'',
  `stop_time` int(11) unsigned NOT NULL COMMENT ''砍价结束时间'',
  `store_name` varchar(255) DEFAULT NULL COMMENT ''砍价产品名称'',
  `price` decimal(8,2) unsigned DEFAULT NULL COMMENT ''砍价金额'',
  `min_price` decimal(8,2) unsigned DEFAULT NULL COMMENT ''砍价商品最低价'',
  `num` int(11) unsigned DEFAULT NULL COMMENT ''每次购买的砍价产品数量'',
  `bargain_max_price` decimal(8,2) unsigned DEFAULT NULL COMMENT ''用户每次砍价的最大金额'',
  `bargain_min_price` decimal(8,2) unsigned DEFAULT NULL COMMENT ''用户每次砍价的最小金额'',
  `bargain_num` int(11) unsigned NOT NULL DEFAULT ''1'' COMMENT ''用户每次砍价的次数'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''砍价状态 0(到砍价时间不自动开启)  1(到砍价时间自动开启时间)'',
  `description` text COMMENT ''砍价详情'',
  `give_integral` decimal(10,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''反多少积分'',
  `info` varchar(255) DEFAULT NULL COMMENT ''砍价活动简介'',
  `cost` decimal(8,2) unsigned DEFAULT NULL COMMENT ''成本价'',
  `sort` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''排序'',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否推荐0不推荐1推荐'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否删除 0未删除 1删除'',
  `add_time` int(11) unsigned NOT NULL COMMENT ''添加时间'',
  `is_postage` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''是否包邮 0不包邮 1包邮'',
  `postage` decimal(10,2) unsigned DEFAULT NULL COMMENT ''邮费'',
  `rule` text COMMENT ''砍价规则'',
  `look` int(11) unsigned DEFAULT ''0'' COMMENT ''砍价产品浏览量'',
  `share` int(11) unsigned DEFAULT ''0'' COMMENT ''砍价产品分享量''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''砍价表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_bargain_user`
--

CREATE TABLE IF NOT EXISTS `eb_store_bargain_user` (
  `id` int(11) unsigned NOT NULL COMMENT ''用户参与砍价表ID'',
  `uid` int(11) unsigned DEFAULT NULL COMMENT ''用户ID'',
  `bargain_id` int(11) unsigned DEFAULT NULL COMMENT ''砍价产品id'',
  `bargain_price_min` decimal(8,2) unsigned DEFAULT NULL COMMENT ''砍价的最低价'',
  `bargain_price` decimal(8,2) DEFAULT NULL COMMENT ''砍价金额'',
  `price` decimal(8,2) unsigned DEFAULT NULL COMMENT ''砍掉的价格'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''状态 1参与中 2 活动结束参与失败 3活动结束参与成功'',
  `add_time` int(11) unsigned DEFAULT NULL COMMENT ''参与时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户参与砍价表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_bargain_user_help`
--

CREATE TABLE IF NOT EXISTS `eb_store_bargain_user_help` (
  `id` int(11) unsigned NOT NULL COMMENT ''砍价用户帮助表ID'',
  `uid` int(11) unsigned DEFAULT NULL COMMENT ''帮助的用户id'',
  `bargain_id` int(11) unsigned DEFAULT NULL COMMENT ''砍价产品ID'',
  `bargain_user_id` int(11) unsigned DEFAULT NULL COMMENT ''用户参与砍价表id'',
  `price` decimal(8,2) unsigned DEFAULT NULL COMMENT ''帮助砍价多少金额'',
  `add_time` int(11) unsigned DEFAULT NULL COMMENT ''添加时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''砍价用户帮助表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_cart`
--

CREATE TABLE IF NOT EXISTS `eb_store_cart` (
  `id` bigint(8) unsigned NOT NULL COMMENT ''购物车表ID'',
  `uid` int(10) unsigned NOT NULL COMMENT ''用户ID'',
  `type` varchar(32) NOT NULL COMMENT ''类型'',
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `product_attr_unique` varchar(16) NOT NULL DEFAULT '''' COMMENT ''商品属性'',
  `cart_num` smallint(5) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商品数量'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''添加时间'',
  `is_pay` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''0 = 未购买 1 = 已购买'',
  `is_del` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否删除'',
  `is_new` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否为立即购买'',
  `combination_id` int(11) unsigned NOT NULL COMMENT ''拼团id'',
  `seckill_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''秒杀产品ID'',
  `bargain_id` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''砍价id''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''购物车表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_category`
--

CREATE TABLE IF NOT EXISTS `eb_store_category` (
  `id` mediumint(11) NOT NULL COMMENT ''商品分类表ID'',
  `pid` mediumint(11) NOT NULL COMMENT ''父id'',
  `cate_name` varchar(100) NOT NULL COMMENT ''分类名称'',
  `sort` mediumint(11) NOT NULL COMMENT ''排序'',
  `pic` varchar(128) NOT NULL DEFAULT '''' COMMENT ''图标'',
  `is_show` tinyint(1) NOT NULL DEFAULT ''1'' COMMENT ''是否推荐'',
  `add_time` int(11) NOT NULL COMMENT ''添加时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品分类表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination` (
  `id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品id'',
  `mer_id` int(10) unsigned DEFAULT ''0'' COMMENT ''商户id'',
  `image` varchar(255) NOT NULL COMMENT ''推荐图'',
  `images` varchar(1000) NOT NULL COMMENT ''轮播图'',
  `title` varchar(255) NOT NULL COMMENT ''活动标题'',
  `attr` varchar(255) NOT NULL COMMENT ''活动属性'',
  `people` int(2) unsigned NOT NULL COMMENT ''参团人数'',
  `info` varchar(255) NOT NULL COMMENT ''简介'',
  `price` decimal(10,2) unsigned NOT NULL COMMENT ''价格'',
  `sort` int(10) unsigned NOT NULL COMMENT ''排序'',
  `sales` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''销量'',
  `stock` int(10) unsigned NOT NULL COMMENT ''库存'',
  `add_time` varchar(128) NOT NULL COMMENT ''添加时间'',
  `is_host` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''推荐'',
  `is_show` tinyint(1) unsigned NOT NULL COMMENT ''产品状态'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'',
  `combination` tinyint(1) unsigned NOT NULL DEFAULT ''1'',
  `mer_use` tinyint(1) unsigned NOT NULL COMMENT ''商户是否可用1可用0不可用'',
  `is_postage` tinyint(1) unsigned NOT NULL COMMENT ''是否包邮1是0否'',
  `postage` decimal(10,2) unsigned NOT NULL COMMENT ''邮费'',
  `description` text NOT NULL COMMENT ''拼团内容'',
  `start_time` int(11) unsigned NOT NULL COMMENT ''拼团开始时间'',
  `stop_time` int(11) unsigned NOT NULL COMMENT ''拼团结束时间'',
  `cost` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''拼图产品成本'',
  `browse` int(11) DEFAULT ''0'' COMMENT ''浏览量''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT=''拼团产品表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商品ID'',
  `attr_name` varchar(32) NOT NULL COMMENT ''属性名'',
  `attr_values` varchar(256) NOT NULL COMMENT ''属性值''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品属性表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `result` text NOT NULL COMMENT ''商品属性参数'',
  `change_time` int(10) unsigned NOT NULL COMMENT ''上次修改时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品属性详情表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_combination_attr_value`
--

CREATE TABLE IF NOT EXISTS `eb_store_combination_attr_value` (
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `suk` varchar(128) NOT NULL COMMENT ''商品属性索引值 (attr_value|attr_value[|....])'',
  `stock` int(10) unsigned NOT NULL COMMENT ''属性对应的库存'',
  `sales` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''销量'',
  `price` decimal(8,2) unsigned NOT NULL COMMENT ''属性金额'',
  `image` varchar(128) DEFAULT NULL COMMENT ''图片'',
  `unique` char(8) NOT NULL DEFAULT '''' COMMENT ''唯一值'',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT ''成本价''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品属性值表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon` (
  `id` int(11) unsigned NOT NULL COMMENT ''优惠券表ID'',
  `title` varchar(64) NOT NULL COMMENT ''优惠券名称'',
  `integral` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''兑换消耗积分值'',
  `coupon_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''兑换的优惠券面值'',
  `use_min_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''最低消费多少金额可用优惠券'',
  `coupon_time` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''优惠券有效期限（单位：天）'',
  `sort` int(11) unsigned NOT NULL DEFAULT ''1'' COMMENT ''排序'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''状态（0：关闭，1：开启）'',
  `add_time` int(11) unsigned NOT NULL COMMENT ''兑换项目添加时间'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否删除''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''优惠券表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon_issue`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon_issue` (
  `id` int(10) unsigned NOT NULL,
  `cid` int(10) DEFAULT NULL COMMENT ''优惠券ID'',
  `start_time` int(10) DEFAULT NULL COMMENT ''优惠券领取开启时间'',
  `end_time` int(10) DEFAULT NULL COMMENT ''优惠券领取结束时间'',
  `total_count` int(10) DEFAULT NULL COMMENT ''优惠券领取数量'',
  `remain_count` int(10) DEFAULT NULL COMMENT ''优惠券剩余领取数量'',
  `is_permanent` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否无限张数'',
  `status` tinyint(1) NOT NULL DEFAULT ''1'' COMMENT ''1 正常 0 未开启 -1 已无效'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'',
  `add_time` int(10) DEFAULT NULL COMMENT ''优惠券添加时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''优惠券前台领取表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon_issue_user`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon_issue_user` (
  `uid` int(10) DEFAULT NULL COMMENT ''领取优惠券用户ID'',
  `issue_coupon_id` int(10) DEFAULT NULL COMMENT ''优惠券前台领取ID'',
  `add_time` int(10) DEFAULT NULL COMMENT ''领取时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''优惠券前台用户领取记录表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_coupon_user`
--

CREATE TABLE IF NOT EXISTS `eb_store_coupon_user` (
  `id` int(11) NOT NULL COMMENT ''优惠券发放记录id'',
  `cid` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''兑换的项目id'',
  `uid` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''优惠券所属用户'',
  `coupon_title` varchar(32) NOT NULL COMMENT ''优惠券名称'',
  `coupon_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''优惠券的面值'',
  `use_min_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''最低消费多少金额可用优惠券'',
  `add_time` int(11) unsigned NOT NULL COMMENT ''优惠券创建时间'',
  `end_time` int(11) unsigned NOT NULL COMMENT ''优惠券结束时间'',
  `use_time` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''使用时间'',
  `type` varchar(32) NOT NULL DEFAULT ''send'' COMMENT ''获取方式'',
  `status` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''状态（0：未使用，1：已使用, 2:已过期）'',
  `is_fail` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否有效''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''优惠券发放记录表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_order`
--

CREATE TABLE IF NOT EXISTS `eb_store_order` (
  `id` int(11) unsigned NOT NULL COMMENT ''订单ID'',
  `order_id` varchar(32) NOT NULL COMMENT ''订单号'',
  `uid` int(11) unsigned NOT NULL COMMENT ''用户id'',
  `real_name` varchar(32) NOT NULL COMMENT ''用户姓名'',
  `user_phone` varchar(18) NOT NULL COMMENT ''用户电话'',
  `user_address` varchar(100) NOT NULL COMMENT ''详细地址'',
  `cart_id` varchar(256) NOT NULL DEFAULT ''[]'' COMMENT ''购物车id'',
  `total_num` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''订单商品总数'',
  `total_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''订单总价'',
  `total_postage` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''邮费'',
  `pay_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''实际支付金额'',
  `pay_postage` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''支付邮费'',
  `deduction_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''抵扣金额'',
  `coupon_id` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''优惠券id'',
  `coupon_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''优惠券金额'',
  `paid` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''支付状态'',
  `pay_time` int(11) unsigned DEFAULT NULL COMMENT ''支付时间'',
  `pay_type` varchar(32) NOT NULL COMMENT ''支付方式'',
  `add_time` int(11) unsigned NOT NULL COMMENT ''创建时间'',
  `status` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''订单状态（-1 : 申请退款 -2 : 退货成功 0：待发货；1：待收货；2：已收货；3：待评价；-1：已退款）'',
  `refund_status` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''0 未退款 1 申请中 2 已退款'',
  `refund_reason_wap_img` varchar(255) DEFAULT NULL COMMENT ''退款图片'',
  `refund_reason_wap_explain` varchar(255) DEFAULT NULL COMMENT ''退款用户说明'',
  `refund_reason_time` int(11) unsigned DEFAULT NULL COMMENT ''退款时间'',
  `refund_reason_wap` varchar(255) DEFAULT NULL COMMENT ''前台退款原因'',
  `refund_reason` varchar(255) DEFAULT NULL COMMENT ''不退款的理由'',
  `refund_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''退款金额'',
  `delivery_name` varchar(64) DEFAULT NULL COMMENT ''快递名称/送货人姓名'',
  `delivery_type` varchar(32) DEFAULT NULL COMMENT ''发货类型'',
  `delivery_id` varchar(64) DEFAULT NULL COMMENT ''快递单号/手机号'',
  `gain_integral` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''消费赚取积分'',
  `use_integral` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''使用积分'',
  `back_integral` decimal(8,2) unsigned DEFAULT NULL COMMENT ''给用户退了多少积分'',
  `mark` varchar(512) NOT NULL COMMENT ''备注'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否删除'',
  `unique` char(32) NOT NULL COMMENT ''唯一id(md5加密)类似id'',
  `remark` varchar(512) DEFAULT NULL COMMENT ''管理员备注'',
  `mer_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商户ID'',
  `is_mer_check` tinyint(3) unsigned NOT NULL DEFAULT ''0'',
  `combination_id` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''拼团产品id0一般产品'',
  `pink_id` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''拼团id 0没有拼团'',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT ''成本价'',
  `seckill_id` int(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''秒杀产品ID'',
  `bargain_id` int(11) unsigned DEFAULT ''0'' COMMENT ''砍价id'',
  `is_channel` tinyint(1) unsigned DEFAULT ''0'' COMMENT ''支付渠道(0微信公众号1微信小程序)''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''订单表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_order_cart_info`
--

CREATE TABLE IF NOT EXISTS `eb_store_order_cart_info` (
  `oid` int(11) unsigned NOT NULL COMMENT ''订单id'',
  `cart_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''购物车id'',
  `product_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商品ID'',
  `cart_info` text NOT NULL COMMENT ''购买东西的详细信息'',
  `unique` char(32) NOT NULL COMMENT ''唯一id''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''订单购物详情表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_order_status`
--

CREATE TABLE IF NOT EXISTS `eb_store_order_status` (
  `oid` int(10) unsigned NOT NULL COMMENT ''订单id'',
  `change_type` varchar(32) NOT NULL COMMENT ''操作类型'',
  `change_message` varchar(256) NOT NULL COMMENT ''操作备注'',
  `change_time` int(10) unsigned NOT NULL COMMENT ''操作时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''订单操作记录表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_pink`
--

CREATE TABLE IF NOT EXISTS `eb_store_pink` (
  `id` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL COMMENT ''用户id'',
  `order_id` varchar(32) NOT NULL COMMENT ''订单id 生成'',
  `order_id_key` int(10) unsigned NOT NULL COMMENT ''订单id  数据库'',
  `total_num` int(10) unsigned NOT NULL COMMENT ''购买商品个数'',
  `total_price` decimal(10,2) unsigned NOT NULL COMMENT ''购买总金额'',
  `cid` int(10) unsigned NOT NULL COMMENT ''拼团产品id'',
  `pid` int(10) unsigned NOT NULL COMMENT ''产品id'',
  `people` int(10) unsigned NOT NULL COMMENT ''拼图总人数'',
  `price` decimal(10,2) unsigned NOT NULL COMMENT ''拼团产品单价'',
  `add_time` varchar(24) NOT NULL COMMENT ''开始时间'',
  `stop_time` varchar(24) NOT NULL,
  `k_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''团长id 0为团长'',
  `is_tpl` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否发送模板消息0未发送1已发送'',
  `is_refund` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否退款 0未退款 1已退款'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''状态1进行中2已完成3未完成''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''拼团表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product`
--

CREATE TABLE IF NOT EXISTS `eb_store_product` (
  `id` mediumint(11) NOT NULL COMMENT ''商品id'',
  `mer_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商户Id(0为总后台管理员创建,不为0的时候是商户后台创建)'',
  `image` varchar(128) NOT NULL COMMENT ''商品图片'',
  `slider_image` varchar(512) NOT NULL COMMENT ''轮播图'',
  `store_name` varchar(128) NOT NULL COMMENT ''商品名称'',
  `store_info` varchar(256) NOT NULL COMMENT ''商品简介'',
  `keyword` varchar(256) NOT NULL COMMENT ''关键字'',
  `cate_id` varchar(64) NOT NULL COMMENT ''分类id'',
  `price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''商品价格'',
  `vip_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''会员价格'',
  `ot_price` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''市场价'',
  `postage` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''邮费'',
  `unit_name` varchar(32) NOT NULL COMMENT ''单位名'',
  `sort` smallint(11) NOT NULL DEFAULT ''0'' COMMENT ''排序'',
  `sales` mediumint(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''销量'',
  `stock` mediumint(11) unsigned NOT NULL DEFAULT ''0'' COMMENT ''库存'',
  `is_show` tinyint(1) NOT NULL DEFAULT ''1'' COMMENT ''状态（0：未上架，1：上架）'',
  `is_hot` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否热卖'',
  `is_benefit` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否优惠'',
  `is_best` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否精品'',
  `is_new` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否新品'',
  `description` text NOT NULL COMMENT ''产品描述'',
  `add_time` int(11) unsigned NOT NULL COMMENT ''添加时间'',
  `is_postage` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否包邮'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否删除'',
  `mer_use` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商户是否代理 0不可代理1可代理'',
  `give_integral` decimal(8,2) unsigned NOT NULL COMMENT ''获得积分'',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT ''成本价'',
  `is_seckill` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''秒杀状态 0 未开启 1已开启'',
  `is_bargain` tinyint(1) unsigned NOT NULL COMMENT ''砍价状态 0未开启 1开启'',
  `ficti` mediumint(11) DEFAULT ''100'' COMMENT ''虚拟销量'',
  `browse` int(11) DEFAULT ''0'' COMMENT ''浏览量''
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT=''商品表'';

--
-- 转存表中的数据 `eb_store_product`
--

INSERT INTO `eb_store_product` (`id`, `mer_id`, `image`, `slider_image`, `store_name`, `store_info`, `keyword`, `cate_id`, `price`, `vip_price`, `ot_price`, `postage`, `unit_name`, `sort`, `sales`, `stock`, `is_show`, `is_hot`, `is_benefit`, `is_best`, `is_new`, `description`, `add_time`, `is_postage`, `is_del`, `mer_use`, `give_integral`, `cost`, `is_seckill`, `is_bargain`, `ficti`, `browse`) VALUES
(1, 0, ''http://git.crmeb.net/public/uploads/0/20180915/5b9cd2b4e97c3.png'', ''["http:\\/\\/git.crmeb.net\\/public\\/uploads\\/0\\/20180915\\/5b9cd2b4e97c3.png"]'', ''测试测试'', ''测试测试测试测试测试测试'', ''测试测试'', '''', ''10.00'', ''0.00'', ''10.00'', ''0.00'', ''件'', 10, 10, 1, 1, 1, 1, 1, 1, '''', 1534818410, 1, 0, 0, ''0.00'', ''10.00'', 0, 0, 1010, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商品ID'',
  `attr_name` varchar(32) NOT NULL COMMENT ''属性名'',
  `attr_values` varchar(256) NOT NULL COMMENT ''属性值''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品属性表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `result` text NOT NULL COMMENT ''商品属性参数'',
  `change_time` int(10) unsigned NOT NULL COMMENT ''上次修改时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品属性详情表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_attr_value`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_attr_value` (
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `suk` varchar(128) NOT NULL COMMENT ''商品属性索引值 (attr_value|attr_value[|....])'',
  `stock` int(10) unsigned NOT NULL COMMENT ''属性对应的库存'',
  `sales` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''销量'',
  `price` decimal(8,2) unsigned NOT NULL COMMENT ''属性金额'',
  `image` varchar(128) DEFAULT NULL COMMENT ''图片'',
  `unique` char(8) NOT NULL DEFAULT '''' COMMENT ''唯一值'',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT ''成本价''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品属性值表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_relation`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_relation` (
  `uid` int(10) unsigned NOT NULL COMMENT ''用户ID'',
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `type` varchar(32) NOT NULL COMMENT ''类型(收藏(collect）、点赞(like))'',
  `category` varchar(32) NOT NULL COMMENT ''某种类型的商品(普通商品、秒杀商品)'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''添加时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品点赞和收藏表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_product_reply`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_reply` (
  `id` int(11) NOT NULL COMMENT ''评论ID'',
  `uid` int(11) NOT NULL COMMENT ''用户ID'',
  `oid` int(11) NOT NULL COMMENT ''订单ID'',
  `unique` char(32) NOT NULL COMMENT ''唯一id'',
  `product_id` int(11) NOT NULL COMMENT ''产品id'',
  `reply_type` varchar(32) NOT NULL DEFAULT ''product'' COMMENT ''某种商品类型(普通商品、秒杀商品）'',
  `product_score` tinyint(1) NOT NULL COMMENT ''商品分数'',
  `service_score` tinyint(1) NOT NULL COMMENT ''服务分数'',
  `comment` varchar(512) NOT NULL COMMENT ''评论内容'',
  `pics` text NOT NULL COMMENT ''评论图片'',
  `add_time` int(11) NOT NULL COMMENT ''评论时间'',
  `merchant_reply_content` varchar(300) NOT NULL COMMENT ''管理员回复内容'',
  `merchant_reply_time` int(11) NOT NULL COMMENT ''管理员回复时间'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''0未删除1已删除'',
  `is_reply` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''0未回复1已回复''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''评论表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill` (
  `id` int(10) unsigned NOT NULL COMMENT ''商品秒杀产品表id'',
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品id'',
  `image` varchar(255) NOT NULL COMMENT ''推荐图'',
  `images` varchar(1000) NOT NULL COMMENT ''轮播图'',
  `title` varchar(255) NOT NULL COMMENT ''活动标题'',
  `info` varchar(255) NOT NULL COMMENT ''简介'',
  `price` decimal(10,2) unsigned NOT NULL COMMENT ''价格'',
  `cost` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''成本'',
  `ot_price` decimal(10,2) unsigned NOT NULL COMMENT ''原价'',
  `give_integral` decimal(10,2) unsigned NOT NULL COMMENT ''返多少积分'',
  `sort` int(10) unsigned NOT NULL COMMENT ''排序'',
  `stock` int(10) unsigned NOT NULL COMMENT ''库存'',
  `sales` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''销量'',
  `unit_name` varchar(16) NOT NULL COMMENT ''单位名'',
  `postage` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''邮费'',
  `description` text NOT NULL COMMENT ''内容'',
  `start_time` varchar(128) NOT NULL COMMENT ''开始时间'',
  `stop_time` varchar(128) NOT NULL COMMENT ''结束时间'',
  `add_time` varchar(128) NOT NULL COMMENT ''添加时间'',
  `status` tinyint(1) unsigned NOT NULL COMMENT ''产品状态'',
  `is_postage` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否包邮'',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''热门推荐'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''删除 0未删除1已删除'',
  `num` int(11) unsigned NOT NULL COMMENT ''最多秒杀几个'',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''显示''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商品秒杀产品表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr` (
  `product_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商品ID'',
  `attr_name` varchar(32) NOT NULL COMMENT ''属性名'',
  `attr_values` varchar(256) NOT NULL COMMENT ''属性值''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''秒杀商品属性表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr_result`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr_result` (
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `result` text NOT NULL COMMENT ''商品属性参数'',
  `change_time` int(10) unsigned NOT NULL COMMENT ''上次修改时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''秒杀商品属性详情表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_seckill_attr_value`
--

CREATE TABLE IF NOT EXISTS `eb_store_seckill_attr_value` (
  `product_id` int(10) unsigned NOT NULL COMMENT ''商品ID'',
  `suk` varchar(128) NOT NULL COMMENT ''商品属性索引值 (attr_value|attr_value[|....])'',
  `stock` int(10) unsigned NOT NULL COMMENT ''属性对应的库存'',
  `sales` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''销量'',
  `price` decimal(8,2) unsigned NOT NULL COMMENT ''属性金额'',
  `image` varchar(128) DEFAULT NULL COMMENT ''图片'',
  `unique` char(8) NOT NULL DEFAULT '''' COMMENT ''唯一值'',
  `cost` decimal(8,2) unsigned NOT NULL COMMENT ''成本价''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''秒杀商品属性值表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_service`
--

CREATE TABLE IF NOT EXISTS `eb_store_service` (
  `id` int(11) NOT NULL COMMENT ''客服id'',
  `mer_id` int(11) NOT NULL DEFAULT ''0'' COMMENT ''商户id'',
  `uid` int(11) NOT NULL COMMENT ''客服uid'',
  `avatar` varchar(250) NOT NULL COMMENT ''客服头像'',
  `nickname` varchar(50) NOT NULL COMMENT ''代理名称'',
  `add_time` int(11) NOT NULL COMMENT ''添加时间'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''0隐藏1显示''
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT=''客服表'';

--
-- 转存表中的数据 `eb_store_service`
--

INSERT INTO `eb_store_service` (`id`, `mer_id`, `uid`, `avatar`, `nickname`, `add_time`, `status`) VALUES
(1, 0, 90, ''http://thirdwx.qlogo.cn/mmopen/LneiciaJhByic2MV0ocMFdPHJzlaWskqtgN5qCAojya1LHbjlhIHzWOBehN78WTuAqUjOnUUbSpJKpYJlaysap1HUpfzeQg0ugP/132'', ''天会亮、心会暖'', 1528681446, 1),
(9, 0, 1, ''http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLCx03Y4hkSeVgQZGZLYDSQz6SZ7PDDNSLj3RxVPYqibMvW4cIOicPSSmA0xbrL9DY2RkunA1pulAs9g/132'', ''等风来，随风去'', 1534312905, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_service_log`
--

CREATE TABLE IF NOT EXISTS `eb_store_service_log` (
  `id` int(11) NOT NULL COMMENT ''客服用户对话记录表ID'',
  `mer_id` int(11) NOT NULL DEFAULT ''0'' COMMENT ''商户id'',
  `msn` text NOT NULL COMMENT ''消息内容'',
  `uid` int(11) NOT NULL COMMENT ''发送人uid'',
  `to_uid` int(11) NOT NULL COMMENT ''接收人uid'',
  `add_time` int(11) NOT NULL COMMENT ''发送时间'',
  `type` tinyint(1) DEFAULT NULL COMMENT ''是否已读（0：否；1：是；）'',
  `remind` tinyint(1) DEFAULT NULL COMMENT ''是否提醒过''
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COMMENT=''客服用户对话记录表'';

--
-- 转存表中的数据 `eb_store_service_log`
--

INSERT INTO `eb_store_service_log` (`id`, `mer_id`, `msn`, `uid`, `to_uid`, `add_time`, `type`, `remind`) VALUES
(1, 0, ''能收到消息吗'', 65, 90, 1528875497, NULL, NULL),
(2, 0, ''[拜拜]'', 65, 105, 1528875827, NULL, NULL),
(3, 0, ''[亲亲]'', 65, 111, 1528875835, NULL, NULL),
(4, 0, ''[害羞]'', 72, 65, 1528875934, NULL, NULL),
(5, 0, ''人呢？'', 72, 65, 1528875957, NULL, NULL),
(6, 0, ''[大笑]'', 168, 65, 1528957108, NULL, NULL),
(7, 0, ''[感觉真好]'', 1, 65, 1528960540, NULL, NULL),
(8, 0, ''你好'', 169, 90, 1528974505, NULL, NULL),
(9, 0, ''你好'', 66, 65, 1528976476, NULL, NULL),
(10, 0, ''哈咯呀'', 66, 65, 1528976482, NULL, NULL),
(11, 0, ''在吗'', 172, 65, 1529026203, NULL, NULL),
(12, 0, ''你这个没有分销功能吗'', 172, 65, 1529026214, NULL, NULL),
(13, 0, ''你好'', 171, 90, 1529380637, NULL, NULL),
(14, 0, ''[大喷血]'', 171, 90, 1529380649, NULL, NULL),
(15, 0, ''hi'', 176, 65, 1529382467, NULL, NULL),
(16, 0, ''哦你我'', 179, 65, 1529400014, NULL, NULL),
(17, 0, ''出来接客'', 185, 65, 1529464154, NULL, NULL),
(18, 0, ''源码出售？'', 185, 65, 1529464166, NULL, NULL),
(19, 0, ''1'', 191, 90, 1529559224, NULL, NULL),
(20, 0, ''<img class="img" src="/public/uploads/wechat/media/75cfd4562cec21da0645.jpg" onclick="img_detail($(this))" />'', 191, 65, 1529559261, NULL, NULL),
(21, 0, ''<img class="img" src="/public/uploads/wechat/media/7621770cf214576487ed.jpg" onclick="img_detail($(this))" />'', 191, 90, 1529559557, NULL, NULL),
(22, 0, ''你好'', 209, 90, 1529639162, NULL, NULL),
(23, 0, ''这是微信聊天吗'', 209, 90, 1529639172, NULL, NULL),
(24, 0, ''11'', 232, 65, 1529979290, NULL, NULL),
(25, 0, ''明年'', 239, 65, 1529996509, NULL, NULL),
(26, 0, ''11'', 260, 65, 1530326762, NULL, NULL),
(27, 0, ''你好！'', 264, 65, 1530380433, NULL, NULL),
(28, 0, ''999'', 261, 65, 1530500304, NULL, NULL),
(29, 0, ''<img class="img" src="/public/uploads/wechat/media/82acc9a85b78bb3996ee.jpg" onclick="img_detail($(this))" />'', 264, 90, 1530559560, NULL, NULL),
(30, 0, ''你高'', 264, 90, 1530676572, NULL, NULL),
(31, 0, ''。。。'', 280, 90, 1531184216, NULL, NULL),
(32, 0, ''你好'', 314, 90, 1531972853, NULL, NULL),
(33, 0, ''你好'', 327, 266, 1532052844, NULL, NULL),
(34, 0, ''。。。。'', 69, 266, 1533459833, NULL, NULL),
(35, 0, ''[大笑]'', 81, 266, 1533804713, NULL, NULL),
(36, 0, ''435123123'', 69, 266, 1533810766, NULL, NULL),
(37, 0, ''你能回话。'', 69, 266, 1533877078, NULL, NULL),
(38, 0, ''[感觉真好]'', 69, 266, 1533877086, NULL, NULL),
(39, 0, ''<img class="img" src="/public/uploads/wechat/media/6227b83dd040458976a6.jpg" onclick="img_detail($(this))" />'', 69, 266, 1534147509, NULL, NULL),
(40, 0, ''啦啦啦啦'', 20, 90, 1534233307, NULL, NULL),
(41, 0, ''国家经济理论，了国家经济'', 91, 90, 1534299668, NULL, NULL),
(42, 0, ''斯里兰卡民主社会主义共和国'', 91, 90, 1534299681, NULL, NULL),
(43, 0, ''桂林路田林路'', 91, 90, 1534299699, NULL, NULL),
(44, 0, ''7989798'', 20, 90, 1534299708, NULL, NULL),
(45, 0, ''你以为陪你'', 95, 90, 1534303730, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `eb_store_visit`
--

CREATE TABLE IF NOT EXISTS `eb_store_visit` (
  `id` int(10) NOT NULL,
  `product_id` int(11) DEFAULT NULL COMMENT ''产品ID'',
  `product_type` varchar(32) DEFAULT NULL COMMENT ''产品类型'',
  `cate_id` int(11) DEFAULT NULL COMMENT ''产品分类ID'',
  `type` char(50) DEFAULT NULL COMMENT ''产品类型'',
  `uid` int(11) DEFAULT NULL COMMENT ''用户ID'',
  `count` int(11) DEFAULT NULL COMMENT ''访问次数'',
  `content` varchar(255) DEFAULT NULL COMMENT ''备注描述'',
  `add_time` int(11) DEFAULT NULL COMMENT ''添加时间''
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT=''产品浏览分析表'';

--
-- 转存表中的数据 `eb_store_visit`
--

INSERT INTO `eb_store_visit` (`id`, `product_id`, `product_type`, `cate_id`, `type`, `uid`, `count`, `content`, `add_time`) VALUES
(1, 0, ''product'', 0, ''search'', 1, 1, ''0'', 1535070682);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_admin`
--

CREATE TABLE IF NOT EXISTS `eb_system_admin` (
  `id` smallint(5) unsigned NOT NULL COMMENT ''后台管理员表ID'',
  `account` varchar(32) NOT NULL COMMENT ''后台管理员账号'',
  `pwd` char(32) NOT NULL COMMENT ''后台管理员密码'',
  `real_name` varchar(16) NOT NULL COMMENT ''后台管理员姓名'',
  `roles` varchar(128) NOT NULL COMMENT ''后台管理员权限(menus_id)'',
  `last_ip` varchar(16) DEFAULT NULL COMMENT ''后台管理员最后一次登录ip'',
  `last_time` int(10) unsigned DEFAULT NULL COMMENT ''后台管理员最后一次登录时间'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''后台管理员添加时间'',
  `login_count` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''登录次数'',
  `level` tinyint(3) unsigned NOT NULL DEFAULT ''1'' COMMENT ''后台管理员级别'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''后台管理员状态 1有效0无效'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0''
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT=''后台管理员表'';

--
-- 转存表中的数据 `eb_system_admin`
--

INSERT INTO `eb_system_admin` (`id`, `account`, `pwd`, `real_name`, `roles`, `last_ip`, `last_time`, `add_time`, `login_count`, `level`, `status`, `is_del`) VALUES
(1, ''admin'', ''7fef6171469e80d32c0559f88b377245'', ''admin'', ''1'', ''123.139.134.255'', 1537004006, 1537003524, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_attachment`
--

CREATE TABLE IF NOT EXISTS `eb_system_attachment` (
  `att_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT ''附件名称'',
  `att_dir` varchar(200) NOT NULL COMMENT ''附件路径'',
  `satt_dir` varchar(200) DEFAULT NULL COMMENT ''压缩图片路径'',
  `att_size` char(30) NOT NULL COMMENT ''附件大小'',
  `att_type` char(30) NOT NULL COMMENT ''附件类型'',
  `pid` int(10) NOT NULL COMMENT ''分类ID0编辑器,1产品图片,2拼团图片,3砍价图片,4秒杀图片,5文章图片,6组合数据图'',
  `time` int(11) NOT NULL COMMENT ''上传时间''
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT=''附件管理表'';

--
-- 转存表中的数据 `eb_system_attachment`
--

INSERT INTO `eb_system_attachment` (`att_id`, `name`, `att_dir`, `satt_dir`, `att_size`, `att_type`, `pid`, `time`) VALUES
(2, ''5b7f5424a1a59.png'', ''\\public\\uploads\\0/20180824\\5b7f5424a1a59.png'', '''', ''2628'', ''image/png'', 0, 1535071268),
(3, ''5b7f542501bd4.png'', ''\\public\\uploads\\0/20180824\\5b7f542501bd4.png'', '''', ''2148'', ''image/png'', 0, 1535071269),
(4, ''5b7f5425288f5.png'', ''\\public\\uploads\\0/20180824\\5b7f5425288f5.png'', '''', ''2020'', ''image/png'', 0, 1535071269),
(5, ''5b7f5425524f7.png'', ''\\public\\uploads\\0/20180824\\5b7f5425524f7.png'', '''', ''1708'', ''image/png'', 0, 1535071269),
(6, ''5b7f54257b159.png'', ''\\public\\uploads\\0/20180824\\5b7f54257b159.png'', '''', ''2326'', ''image/png'', 0, 1535071269),
(7, ''5b7f5425a39d2.png'', ''\\public\\uploads\\0/20180824\\5b7f5425a39d2.png'', '''', ''2446'', ''image/png'', 0, 1535071269),
(8, ''5b7f5425ce95c.png'', ''\\public\\uploads\\0/20180824\\5b7f5425ce95c.png'', '''', ''2226'', ''image/png'', 0, 1535071269),
(9, ''5b7f542607db7.png'', ''\\public\\uploads\\0/20180824\\5b7f542607db7.png'', '''', ''3451'', ''image/png'', 0, 1535071270),
(10, ''5b7f54262f2a8.png'', ''\\public\\uploads\\0/20180824\\5b7f54262f2a8.png'', '''', ''2612'', ''image/png'', 0, 1535071270),
(11, ''5b7f542664e15.png'', ''\\public\\uploads\\0/20180824\\5b7f542664e15.png'', '''', ''2596'', ''image/png'', 0, 1535071270),
(12, ''5b7f542695f48.png'', ''\\public\\uploads\\0/20180824\\5b7f542695f48.png'', '''', ''2020'', ''image/png'', 0, 1535071270),
(13, ''5b9cd2b4e97c3.png'', ''/public/uploads/0/20180915/5b9cd2b4e97c3.png'', '''', ''106073'', ''image/png'', 0, 1537004212);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_attachment_category`
--

CREATE TABLE IF NOT EXISTS `eb_system_attachment_category` (
  `id` int(11) NOT NULL,
  `pid` int(11) DEFAULT ''0'' COMMENT ''父级ID'',
  `name` varchar(50) NOT NULL COMMENT ''分类名称'',
  `enname` varchar(50) NOT NULL COMMENT ''分类目录''
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `eb_system_attachment_category`
--

INSERT INTO `eb_system_attachment_category` (`id`, `pid`, `name`, `enname`) VALUES
(1, 0, ''产品图片'', ''product''),
(2, 0, ''新闻图片'', ''news''),
(3, 0, ''配置图片'', ''config''),
(4, 3, ''首页导航'', ''indexnav''),
(5, 3, ''首页幻灯片'', ''mynav''),
(6, 3, ''其它配置图'', ''footnav''),
(7, 2, ''公司新闻'', ''compay''),
(8, 1, ''拼团产品图'', ''''),
(9, 1, ''秒杀图片'', ''''),
(10, 1, ''砍价产品图'', ''''),
(11, 1, ''普通产品图片'', ''''),
(21, 0, ''衣服'', ''''),
(22, 0, ''衣服2'', ''''),
(23, 0, ''衣服3'', ''''),
(24, 0, ''衣服4'', ''''),
(25, 0, ''衣服5'', '''');

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_config`
--

CREATE TABLE IF NOT EXISTS `eb_system_config` (
  `id` int(10) unsigned NOT NULL COMMENT ''配置id'',
  `menu_name` varchar(255) NOT NULL COMMENT ''字段名称'',
  `type` varchar(255) NOT NULL COMMENT ''类型(文本框,单选按钮...)'',
  `config_tab_id` int(10) unsigned NOT NULL COMMENT ''配置分类id'',
  `parameter` varchar(255) DEFAULT NULL COMMENT ''规则 单选框和多选框'',
  `upload_type` tinyint(1) unsigned DEFAULT NULL COMMENT ''上传文件格式1单图2多图3文件'',
  `required` varchar(255) DEFAULT NULL COMMENT ''规则'',
  `width` int(10) unsigned DEFAULT NULL COMMENT ''多行文本框的宽度'',
  `high` int(10) unsigned DEFAULT NULL COMMENT ''多行文框的高度'',
  `value` varchar(5000) DEFAULT NULL COMMENT ''默认值'',
  `info` varchar(255) NOT NULL COMMENT ''配置名称'',
  `desc` varchar(255) DEFAULT NULL COMMENT ''配置简介'',
  `sort` int(10) unsigned NOT NULL COMMENT ''排序'',
  `status` tinyint(1) unsigned NOT NULL COMMENT ''是否隐藏''
) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COMMENT=''配置表'';

--
-- 转存表中的数据 `eb_system_config`
--

INSERT INTO `eb_system_config` (`id`, `menu_name`, `type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(1, ''site_name'', ''text'', 1, NULL, NULL, ''required:true'', 100, NULL, ''"CRMEB"'', ''网站名称'', ''网站名称'', 0, 1),
(2, ''site_url'', ''text'', 1, NULL, NULL, ''required:true,url:true'', 100, NULL, ''"http:\\/\\/demo25.crmeb.net"'', ''网站地址'', ''网站地址'', 0, 1),
(3, ''site_logo'', ''upload'', 1, NULL, 1, NULL, NULL, NULL, ''"\\/public\\/uploads\\/config\\/image\\/5b77c4c33fb1f.png"'', ''后台LOGO'', ''左上角logo,建议尺寸[170*50]'', 0, 1),
(4, ''site_phone'', ''text'', 1, NULL, NULL, NULL, 100, NULL, ''"13679281569"'', ''联系电话'', ''联系电话'', 0, 1),
(5, ''seo_title'', ''text'', 1, NULL, NULL, ''required:true'', 100, NULL, ''"crmeb\\u7535\\u5546\\u7cfb\\u7edf"'', ''SEO标题'', ''SEO标题'', 0, 1),
(6, ''site_email'', ''text'', 1, NULL, NULL, ''email:true'', 100, NULL, ''"admin@xazbkj.com"'', ''联系邮箱'', ''联系邮箱'', 0, 1),
(7, ''site_qq'', ''text'', 1, NULL, NULL, ''qq:true'', 100, NULL, ''"98718401"'', ''联系QQ'', ''联系QQ'', 0, 1),
(8, ''site_close'', ''radio'', 1, ''0=开启\n1=PC端关闭\n2=WAP端关闭(含微信)\n3=全部关闭'', NULL, '''', NULL, NULL, ''"2"'', ''网站关闭'', ''网站后台、商家中心不受影响。关闭网站也可访问'', 0, 1),
(9, ''close_system'', ''radio'', 1, ''0=开启\n1=关闭'', NULL, '''', NULL, NULL, ''"0"'', ''关闭后台'', ''关闭后台'', 0, 2),
(10, ''wechat_name'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''"CRMEB"'', ''公众号名称'', ''公众号的名称'', 0, 1),
(11, ''wechat_id'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''"CRMEB"'', ''微信号'', ''微信号'', 0, 1),
(12, ''wechat_sourceid'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''公众号原始id'', ''公众号原始id'', 0, 1),
(13, ''wechat_appid'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''AppID'', ''AppID'', 0, 1),
(14, ''wechat_appsecret'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''AppSecret'', ''AppSecret'', 0, 1),
(15, ''wechat_token'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''微信验证TOKEN'', ''微信验证TOKEN'', 0, 1),
(16, ''wechat_encode'', ''radio'', 2, ''0=明文模式\n1=兼容模式\n2=安全模式'', NULL, '''', NULL, NULL, ''"0"'', ''消息加解密方式'', ''如需使用安全模式请在管理中心修改，仅限服务号和认证订阅号'', 0, 1),
(17, ''wechat_encodingaeskey'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''EncodingAESKey'', ''公众号消息加解密Key,在使用安全模式情况下要填写该值，请先在管理中心修改，然后填写该值，仅限服务号和认证订阅号'', 0, 1),
(18, ''wechat_share_img'', ''upload'', 3, NULL, 1, NULL, NULL, NULL, ''""'', ''微信分享图片'', ''若填写此图片地址，则分享网页出去时会分享此图片。可有效防止分享图片变形'', 0, 1),
(19, ''wechat_qrcode'', ''upload'', 2, NULL, 1, NULL, NULL, NULL, ''""'', ''公众号二维码'', ''您的公众号二维码'', 0, 1),
(20, ''wechat_type'', ''radio'', 2, ''0=服务号\n1=订阅号'', NULL, '''', NULL, NULL, ''"0"'', ''公众号类型'', ''公众号的类型'', 0, 1),
(21, ''wechat_share_title'', ''text'', 3, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''微信分享标题'', ''微信分享标题'', 0, 1),
(22, ''wechat_share_synopsis'', ''textarea'', 3, NULL, NULL, NULL, 100, 5, ''""'', ''微信分享简介'', ''微信分享简介'', 0, 1),
(23, ''pay_weixin_appid'', ''text'', 4, NULL, NULL, '''', 100, NULL, ''""'', ''Appid'', ''微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看。'', 0, 1),
(24, ''pay_weixin_appsecret'', ''text'', 4, NULL, NULL, '''', 100, NULL, ''""'', ''Appsecret'', ''JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看。'', 0, 1),
(25, ''pay_weixin_mchid'', ''text'', 4, NULL, NULL, '''', 100, NULL, ''""'', ''Mchid'', ''受理商ID，身份标识'', 0, 1),
(26, ''pay_weixin_client_cert'', ''upload'', 4, NULL, 3, NULL, NULL, NULL, ''""'', ''微信支付证书'', ''微信支付证书，在微信商家平台中可以下载！文件名一般为apiclient_cert.pem'', 0, 1),
(27, ''pay_weixin_client_key'', ''upload'', 4, NULL, 3, NULL, NULL, NULL, ''""'', ''微信支付证书密钥'', ''微信支付证书密钥，在微信商家平台中可以下载！文件名一般为apiclient_key.pem'', 0, 1),
(56, ''store_home_pink'', ''upload'', 5, NULL, 1, NULL, NULL, NULL, ''"\\/public\\/uploads\\/config\\/image\\/5abcad84e2a52.jpg"'', ''首页拼团广告图'', ''首页拼团广告图'', 0, 1),
(28, ''pay_weixin_key'', ''text'', 4, NULL, NULL, '''', 100, NULL, ''""'', ''Key'', ''商户支付密钥Key。审核通过后，在微信发送的邮件中查看。'', 0, 1),
(29, ''pay_weixin_open'', ''radio'', 4, ''1=开启\n0=关闭'', NULL, '''', NULL, NULL, ''"1"'', ''开启'', ''是否启用微信支付'', 0, 1),
(31, ''store_postage'', ''text'', 10, NULL, NULL, ''number:true,min:0'', 100, NULL, ''"0"'', ''邮费基础价'', ''商品邮费基础价格,最终金额为(基础价 + 商品1邮费 + 商品2邮费)'', 0, 1),
(32, ''store_free_postage'', ''text'', 5, NULL, NULL, ''number:true,min:-1'', 100, NULL, ''"20"'', ''满额包邮'', ''商城商品满多少金额即可包邮'', 0, 1),
(33, ''offline_postage'', ''radio'', 10, ''0=不包邮\n1=包邮'', NULL, NULL, NULL, NULL, ''"1"'', ''线下支付是否包邮'', ''用户选择线下支付时是否包邮'', 0, 1),
(34, ''integral_ratio'', ''text'', 11, NULL, NULL, ''number:true'', 100, NULL, ''"0.01"'', ''积分抵用比例'', ''积分抵用比例(1积分抵多少金额)'', 0, 1),
(35, ''site_service_phone'', ''text'', 1, NULL, NULL, NULL, 100, NULL, ''"400-8888794"'', ''客服电话'', ''客服联系电话'', 0, 1),
(44, ''store_user_min_recharge'', ''text'', 5, NULL, NULL, ''required:true,number:true,min:0'', 100, NULL, ''"0.01"'', ''用户最低充值金额'', ''用户单次最低充值金额'', 0, 0),
(45, ''site_store_admin_uids'', ''text'', 5, NULL, NULL, '''', 100, NULL, ''"4"'', ''管理员用户ID'', ''管理员用户ID,用于接收商城订单提醒，到微信用户中查找编号，多个英文‘,’隔开'', 0, 1),
(46, ''system_express_app_code'', ''text'', 10, NULL, NULL, '''', 100, NULL, ''"658da8789d89436699269f4aede6c868"'', ''快递查询密钥'', ''阿里云快递查询接口密钥购买地址：https://market.aliyun.com/products/57126001/cmapi011120.html'', 0, 1),
(47, ''main_business'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''" IT\\u79d1\\u6280 \\u4e92\\u8054\\u7f51|\\u7535\\u5b50\\u5546\\u52a1"'', ''微信模板消息_主营行业'', ''微信公众号模板消息中选择开通的主营行业'', 0, 0),
(48, ''vice_business'', ''text'', 2, NULL, NULL, ''required:true'', 100, NULL, ''"IT\\u79d1\\u6280 IT\\u8f6f\\u4ef6\\u4e0e\\u670d\\u52a1 "'', ''微信模板消息_副营行业'', ''微信公众号模板消息中选择开通的副营行业'', 0, 0),
(49, ''store_brokerage_ratio'', ''text'', 9, NULL, NULL, ''required:true,min:0,max:100,number:true'', 100, NULL, ''"5"'', ''一级返佣比例'', ''订单交易成功后给上级返佣的比例0 - 100,例:5 = 反订单金额的5%'', 5, 1),
(50, ''wechat_first_sub_give_coupon'', ''text'', 12, NULL, NULL, ''requred:true,digits:true,min:0'', 100, NULL, ''""'', ''首次关注赠送优惠券ID'', ''首次关注赠送优惠券ID,0为不赠送'', 0, 1),
(51, ''store_give_con_min_price'', ''text'', 12, NULL, NULL, ''requred:true,digits:true,min:0'', 100, NULL, ''"0.01"'', ''消费满多少赠送优惠券'', ''消费满多少赠送优惠券,0为不赠送'', 0, 1),
(52, ''store_order_give_coupon'', ''text'', 12, NULL, NULL, ''requred:true,digits:true,min:0'', 100, NULL, ''""'', ''消费赠送优惠劵ID'', ''消费赠送优惠劵ID,0为不赠送'', 0, 1),
(53, ''user_extract_min_price'', ''text'', 9, NULL, NULL, ''required:true,number:true,min:0'', 100, NULL, ''"200"'', ''提现最低金额'', ''用户提现最低金额'', 0, 1),
(54, ''sx_sign_min_int'', ''text'', 11, NULL, NULL, ''required:true,number:true,min:0'', 100, NULL, ''"1"'', ''签到奖励最低积分'', ''签到奖励最低积分'', 0, 1),
(55, ''sx_sign_max_int'', ''text'', 11, NULL, NULL, ''required:true,number:true,min:0'', 100, NULL, ''"5"'', ''签到奖励最高积分'', ''签到奖励最高积分'', 0, 1),
(57, ''about_us'', ''upload'', 1, NULL, 1, NULL, NULL, NULL, ''"\\/public\\/uploads\\/config\\/image\\/5b77c4caa5e1d.png"'', ''关于我们'', ''系统的标识'', 0, 1),
(61, ''api'', ''text'', 2, NULL, NULL, '''', 100, NULL, ''"\\/wechat\\/index\\/serve"'', ''接口地址'', ''微信接口例如：http://www.abc.com/wechat/index/serve'', 0, 1),
(58, ''replenishment_num'', ''text'', 5, NULL, NULL, ''required:true,number:true,min:0'', 100, NULL, ''"20"'', ''待补货数量'', ''产品待补货数量低于多少时，提示补货'', 0, 1),
(59, ''routine_appId'', ''text'', 7, NULL, NULL, '''', 100, NULL, ''""'', ''appId'', ''小程序appID'', 0, 1),
(60, ''routine_appsecret'', ''text'', 7, NULL, NULL, '''', 100, NULL, ''""'', ''AppSecret'', ''小程序AppSecret'', 0, 1),
(62, ''paydir'', ''textarea'', 4, NULL, NULL, NULL, 100, 5, ''"\\/wap\\/my\\/\\r\\n\\/wap\\/my\\/order\\/uni\\/\\r\\n\\/wap\\/store\\/confirm_order\\/cartId\\/"'', ''配置目录'', ''支付目录配置系统不调用提示作用'', 0, 1),
(73, ''routine_logo'', ''upload'', 7, NULL, 1, NULL, NULL, NULL, ''"\\/public\\/uploads\\/config\\/image\\/5b2c4491c05f2.jpg"'', ''小程序logo'', ''小程序logo'', 0, 1),
(74, ''routine_name'', ''text'', 7, NULL, NULL, '''', 100, NULL, ''"CRMEB"'', ''小程序名称'', ''小程序名称'', 0, 1),
(76, ''routine_style'', ''text'', 7, NULL, NULL, '''', 100, NULL, ''"#FFFFFF"'', ''小程序风格'', ''小程序颜色'', 0, 1),
(77, ''store_stock'', ''text'', 5, NULL, NULL, '''', 100, NULL, ''"1"'', ''警戒库存'', ''警戒库存提醒值'', 0, 1),
(88, ''store_brokerage_statu'', ''radio'', 9, ''1=指定分销\n2=人人分销'', NULL, NULL, NULL, NULL, ''"1"'', ''分销模式'', ''人人分销默认每个人都可以分销，制定人分销后台制定人开启分销'', 10, 1),
(85, ''stor_reason'', ''textarea'', 5, NULL, NULL, NULL, 100, 8, ''"\\u6536\\u8d27\\u5730\\u5740\\u586b\\u9519\\u4e86\\r\\n\\u4e0e\\u63cf\\u8ff0\\u4e0d\\u7b26\\r\\n\\u4fe1\\u606f\\u586b\\u9519\\u4e86\\uff0c\\u91cd\\u65b0\\u62cd\\r\\n\\u6536\\u5230\\u5546\\u54c1\\u635f\\u574f\\u4e86\\r\\n\\u672a\\u6309\\u9884\\u5b9a\\u65f6\\u95f4\\u53d1\\u8d27\\r\\n\\u5176\\u5b83\\u539f\\u56e0"'', ''退货理由'', ''配置退货理由，一行一个理由'', 0, 1),
(87, ''store_brokerage_two'', ''text'', 9, NULL, NULL, ''required:true,min:0,max:100,number:true'', 100, NULL, ''"3"'', ''二级返佣比例'', ''订单交易成功后给上级返佣的比例0 - 100,例:5 = 反订单金额的5%'', 4, 1),
(89, ''pay_routine_appid'', ''text'', 14, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''Appid'', ''小程序Appid'', 0, 1),
(90, ''pay_routine_appsecret'', ''text'', 14, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''Appsecret'', ''小程序Appsecret'', 0, 1),
(91, ''pay_routine_mchid'', ''text'', 14, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''Mchid'', ''商户号'', 0, 1),
(92, ''pay_routine_key'', ''text'', 14, NULL, NULL, ''required:true'', 100, NULL, ''""'', ''Key'', ''商户key'', 0, 1),
(93, ''pay_routine_client_cert'', ''upload'', 14, NULL, 3, NULL, NULL, NULL, ''""'', ''小程序支付证书'', ''小程序支付证书'', 0, 1),
(94, ''pay_routine_client_key'', ''upload'', 14, NULL, 3, NULL, NULL, NULL, ''""'', ''小程序支付证书密钥'', ''小程序支付证书密钥'', 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_config_tab`
--

CREATE TABLE IF NOT EXISTS `eb_system_config_tab` (
  `id` int(10) unsigned NOT NULL COMMENT ''配置分类id'',
  `title` varchar(255) NOT NULL COMMENT ''配置分类名称'',
  `eng_title` varchar(255) NOT NULL COMMENT ''配置分类英文名称'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''配置分类状态'',
  `info` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''配置分类是否显示'',
  `icon` varchar(30) DEFAULT NULL COMMENT ''图标'',
  `type` int(2) DEFAULT ''0'' COMMENT ''配置类型''
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT=''配置分类表'';

--
-- 转存表中的数据 `eb_system_config_tab`
--

INSERT INTO `eb_system_config_tab` (`id`, `title`, `eng_title`, `status`, `info`, `icon`, `type`) VALUES
(1, ''基础配置'', ''basics'', 1, 0, ''cog'', 0),
(2, ''公众号配置'', ''wechat'', 1, 0, ''weixin'', 1),
(3, ''公众号分享配置'', ''wechat_share'', 1, 0, ''whatsapp'', 1),
(4, ''公众号支付配置'', ''pay'', 1, 0, ''jpy'', 1),
(5, ''商城配置'', ''store'', 1, 0, ''shopping-cart'', 0),
(7, ''小程序配置'', ''routine'', 1, 0, ''weixin'', 2),
(9, ''分销配置'', ''fenxiao'', 1, 0, ''sitemap'', 3),
(10, ''物流配置'', ''express'', 1, 0, ''motorcycle'', 0),
(11, ''积分配置'', ''point'', 1, 0, ''powerpoint-o'', 3),
(12, ''优惠券配置'', ''coupon'', 1, 0, ''heartbeat'', 3),
(14, ''小程序支付配置'', ''routine_pay'', 1, 0, '''', 2);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_file`
--

CREATE TABLE IF NOT EXISTS `eb_system_file` (
  `id` int(10) unsigned NOT NULL COMMENT ''文件对比ID'',
  `cthash` char(32) NOT NULL COMMENT ''文件内容'',
  `filename` varchar(255) NOT NULL COMMENT ''文价名称'',
  `atime` char(12) NOT NULL COMMENT ''上次访问时间'',
  `mtime` char(12) NOT NULL COMMENT ''上次修改时间'',
  `ctime` char(12) NOT NULL COMMENT ''上次改变时间''
) ENGINE=MyISAM AUTO_INCREMENT=1711 DEFAULT CHARSET=utf8 COMMENT=''文件对比表'';

--
-- 转存表中的数据 `eb_system_file`
--

INSERT INTO `eb_system_file` (`id`, `cthash`, `filename`, `atime`, `mtime`, `ctime`) VALUES
(1, ''75aa1be193db411f33422f9ba3deafc4'', ''./application/database.php'', ''1537003879'', ''1537003654'', ''1537003654''),
(2, ''104ce1fc08aea296e4cb00c95e0fc06b'', ''./application/index/controller/Index.php'', ''1536956755'', ''1536288162'', ''1536288162''),
(3, ''27caf9acf09a1ab4e56416f9cb9cf5c9'', ''./application/index/config.php'', ''1536956755'', ''1536288162'', ''1536288162''),
(4, ''d53574ef24a1fbbe284b36cca4394fbc'', ''./application/version.php'', ''1536956756'', ''1536288162'', ''1536288162''),
(5, ''741e396a0042034e0af0e3ecbb045bc8'', ''./application/wap/controller/Wechat.php'', ''1536956756'', ''1536559857'', ''1536559857''),
(6, ''a59331fcf88db9754c8594c5358f3eb1'', ''./application/wap/controller/Store.php'', ''1536956756'', ''1536288162'', ''1536288162''),
(7, ''48663eabf6feb1af61d5f1e4bda53cc8'', ''./application/wap/controller/Article.php'', ''1536956757'', ''1536288162'', ''1536288162''),
(8, ''dc8a3dab4ba9e32d644b983dc8d63721'', ''./application/wap/controller/AuthController.php'', ''1536956761'', ''1536288162'', ''1536288162''),
(9, ''e12a8422c25300702de845c4b9d94d06'', ''./application/wap/controller/Login.php'', ''1536956763'', ''1536288162'', ''1536288162''),
(10, ''8cafef333cc96213024f2c110328d90c'', ''./application/wap/controller/My.php'', ''1536956763'', ''1536288162'', ''1536288162''),
(11, ''1c09e4fdb243cf37f34780c40231c203'', ''./application/wap/controller/Merchant.php'', ''1536956767'', ''1536288162'', ''1536288162''),
(12, ''71b5fb4eea993668851aae0f3640ebd3'', ''./application/wap/controller/PublicApi.php'', ''1536956771'', ''1536288162'', ''1536288162''),
(13, ''97297ac94dac6dbbfe627bbad227667c'', ''./application/wap/controller/Service.php'', ''1536956771'', ''1536288162'', ''1536288162''),
(14, ''6509043043aebe617613dc619b1cd304'', ''./application/wap/controller/AuthApi.php'', ''1536956775'', ''1536288162'', ''1536288162''),
(15, ''d30db471578d35f480936487d8234aff'', ''./application/wap/controller/Index.php'', ''1536956785'', ''1536288162'', ''1536288162''),
(16, ''b00838aed87d62ed28c438b9f7849794'', ''./application/wap/model/wap/ArticleCategory.php'', ''1536956786'', ''1536288162'', ''1536288162''),
(17, ''3a91a9b06530b422638595ca88487aef'', ''./application/wap/model/user/UserSign.php'', ''1536956787'', ''1536288162'', ''1536288162''),
(18, ''544744ef99b7cd0a5ece1c10c12b08d0'', ''./application/wap/model/user/UserNotice.php'', ''1536956787'', ''1536288162'', ''1536288162''),
(19, ''335d98c4054cee2e50d31c2e2623b113'', ''./application/wap/model/user/UserBill.php'', ''1536956787'', ''1536288162'', ''1536288162''),
(20, ''c21e1470d5e2ba4eaa352e92f31220ab'', ''./application/wap/model/user/User.php'', ''1536956787'', ''1536288162'', ''1536288162''),
(21, ''f51ed1cf69ab65d48af4d6adc735c3fa'', ''./application/wap/model/user/WechatUser.php'', ''1536956788'', ''1536288162'', ''1536288162''),
(22, ''f50b65f8f485868c830470e0d4ec729b'', ''./application/wap/model/user/UserExtract.php'', ''1536956788'', ''1536288162'', ''1536288162''),
(23, ''1fe71744f6cc1b5e523a7c6023750fc4'', ''./application/wap/model/user/UserRecharge.php'', ''1536956788'', ''1536288162'', ''1536288162''),
(24, ''c94f8487058738679d21c7e7d3758a8a'', ''./application/wap/model/user/UserAddress.php'', ''1536956788'', ''1536288162'', ''1536288162''),
(25, ''05af84854c9ffb9f8ed7780c87e40194'', ''./application/wap/model/store/StoreProductAttr.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(26, ''8469dba3b7ea8f19eda13b877227b79e'', ''./application/wap/model/store/StoreServiceLog.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(27, ''f51ea797db8ccc34829f4e1b329d19fe'', ''./application/wap/model/store/StoreCategory.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(28, ''1960c2955e5a94587d464822f782801f'', ''./application/wap/model/store/StoreOrder.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(29, ''6b2b8358d3d18c22791119d0b432cfff'', ''./application/wap/model/store/StoreCouponUser.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(30, ''df8314d620e2cef46c4b708b61624c09'', ''./application/wap/model/store/StoreService.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(31, ''4bf70f0c8251350898027b51e993a6b0'', ''./application/wap/model/store/StoreBargainUserHelp.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(32, ''90872dce75cb73e305b08a05f2b43090'', ''./application/wap/model/store/StoreCombination.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(33, ''b88e4aac92ad9b05a75a58f34ed15218'', ''./application/wap/model/store/StorePink.php'', ''1536956789'', ''1536288162'', ''1536288162''),
(34, ''8008406d05cfa1f5e1dcdcf24c3dfb34'', ''./application/wap/model/store/StoreProductRelation.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(35, ''5ffb7428aae32a9ec70545df3854a68a'', ''./application/wap/model/store/StoreCart.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(36, ''c1d5323106f4c84888d78a851bec9a75'', ''./application/wap/model/store/StoreProductReply.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(37, ''a959cc0d519a37fd8fbab5c724e69b0b'', ''./application/wap/model/store/StoreOrderStatus.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(38, ''31ecb93228b02310df86be0d9cb556ac'', ''./application/wap/model/store/StoreCoupon.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(39, ''fa4144248a5b5a635031491608fdf388'', ''./application/wap/model/store/StoreProduct.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(40, ''7913f2b5f05a44a326c35f08c0b38a86'', ''./application/wap/model/store/StoreOrderCartInfo.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(41, ''2c444ef031ffce1a16d1e5c3cb1c0125'', ''./application/wap/model/store/StoreBargainUser.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(42, ''def8f7b5580a027ab604594c281e20f1'', ''./application/wap/model/store/StoreSeckill.php'', ''1536956790'', ''1536288162'', ''1536288162''),
(43, ''5106a642f3ced5c1d88e782df24bb4a8'', ''./application/wap/model/store/StoreBargain.php'', ''1536956791'', ''1536288162'', ''1536288162''),
(44, ''f40ff7468d302846d508efcd552735fe'', ''./application/wap/model/store/StoreCouponIssueUser.php'', ''1536956793'', ''1536288162'', ''1536288162''),
(45, ''b59ea754e15617e93dc769858e5c8477'', ''./application/wap/model/store/StoreCouponIssue.php'', ''1536956795'', ''1536288162'', ''1536288162''),
(46, ''bf1624663ed88f0d0f1edbd5b290598b'', ''./application/wap/view/first/my/user_pro.html'', ''1536956800'', ''1536288162'', ''1536288162''),
(47, ''745640a32bada85340afe9c0f45a1b19'', ''./application/wap/view/first/my/order_pink.html'', ''1536956802'', ''1536288162'', ''1536288162''),
(48, ''0325522d640c371d192cc8654b915b99'', ''./application/wap/view/first/my/order.html'', ''1536956802'', ''1536288162'', ''1536288162''),
(49, ''98f1a977ed8bd2279540d58a5ba7ea70'', ''./application/wap/view/first/my/coupon.html'', ''1536956806'', ''1536288162'', ''1536288162''),
(50, ''c62c9636681b7c27eb3a81c529eabc50'', ''./application/wap/view/first/my/user_cut.html'', ''1536956806'', ''1536288162'', ''1536288162''),
(51, ''dd679b30ef2541ed85b963c5cfd5c334'', ''./application/wap/view/first/my/address.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(52, ''911970b9541fbc555362d3f6e32c0d83'', ''./application/wap/view/first/my/express.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(53, ''5eef5b4a105ab095eaec139ce591bc96'', ''./application/wap/view/first/my/order_customer.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(54, ''10657df1797844f4afa91166d73f1087'', ''./application/wap/view/first/my/spread_list.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(55, ''602ea83b6066d6af604b6eae51fa9986'', ''./application/wap/view/first/my/edit_address.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(56, ''99d2da95ba6c83e9cabcea924d79765a'', ''./application/wap/view/first/my/order_list.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(57, ''66f1af7a58efa5dce2597e4244f5ae2b'', ''./application/wap/view/first/my/sign_in.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(58, ''ad0198e6952b3b3e00d7f210ce646288'', ''./application/wap/view/first/my/integral.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(59, ''5ec392e1f350ab5c6917c218db14890e'', ''./application/wap/view/first/my/index.html'', ''1536956807'', ''1536288162'', ''1536288162''),
(60, ''43499386ddc6d600b6c61265ec585814'', ''./application/wap/view/first/my/balance.html'', ''1536956808'', ''1536288162'', ''1536288162''),
(61, ''66e033dac849c1749e6305c5cdb9b5db'', ''./application/wap/view/first/my/collect.html'', ''1536956808'', ''1536288162'', ''1536288162''),
(62, ''aebbfafb4004a8daa471f6b211ef98f7'', ''./application/wap/view/first/my/notice.html'', ''1536956808'', ''1536288162'', ''1536288162''),
(63, ''2b12a8225c3502babab588a19c6a3994'', ''./application/wap/view/first/my/order_reply.html'', ''1536956808'', ''1536288162'', ''1536288162''),
(64, ''96061ac6b3f69780fa9098fda668c308'', ''./application/wap/view/first/my/commission.html'', ''1536956808'', ''1536288162'', ''1536288162''),
(65, ''87d07538a6940a10ff16ebba81b0575c'', ''./application/wap/view/first/my/extract.html'', ''1536956808'', ''1536288162'', ''1536288162''),
(66, ''785288dfe02e012d6981658e2986b981'', ''./application/wap/view/first/my/order_pink_after.html'', ''1536956808'', ''1536288162'', ''1536288162''),
(67, ''5fd881481a9a47ed96806d4cc8a5d5b4'', ''./application/wap/view/first/static/wap/sx/font/iconfont.eot'', ''1536956809'', ''1536288162'', ''1536288162''),
(68, ''edf7b178b729d8c4f2c4f520112cca9c'', ''./application/wap/view/first/static/wap/sx/font/iconfont.svg'', ''1536956809'', ''1536288162'', ''1536288162''),
(69, ''10984d654728e3c7144ef544a4fb0397'', ''./application/wap/view/first/static/wap/sx/font/iconfont.ttf'', ''1536956809'', ''1536288162'', ''1536288162''),
(70, ''4ba1a9c4c7691bf3f14b0f02256e7484'', ''./application/wap/view/first/static/wap/sx/font/iconfont.woff'', ''1536956809'', ''1536288162'', ''1536288162''),
(71, ''0cdc63e7f3f3d23a5fc991f8801af8df'', ''./application/wap/view/first/static/wap/sx/font/iconfont.css'', ''1536956809'', ''1536288162'', ''1536288162''),
(72, ''e3f4544486133798845e9654ef8b1577'', ''./application/wap/view/first/static/wap/sx/font/iconfont.js'', ''1536956809'', ''1536288162'', ''1536288162''),
(73, ''be4af37694eefca47673e41b86c2ced8'', ''./application/wap/view/first/static/wap/sx/css/style.css'', ''1536956810'', ''1536288162'', ''1536288162''),
(74, ''0176bf1163b6f65f3c8cf11cd367e67c'', ''./application/wap/view/first/static/wap/sx/css/swiper-3.4.1.min.css'', ''1536956810'', ''1536288162'', ''1536288162''),
(75, ''cd00b8ef8c34316c6ccf8b995ae4accc'', ''./application/wap/view/first/static/wap/sx/css/reset.css'', ''1536956810'', ''1536288162'', ''1536288162''),
(76, ''2f1ff83ce406537c73424a037ebe0dcf'', ''./application/wap/view/first/static/wap/sx/images/pro-banner.jpg'', ''1536956811'', ''1536288162'', ''1536288162''),
(77, ''d6e45de39d0bb105311abec5c0fe25ea'', ''./application/wap/view/first/static/wap/sx/images/close.png'', ''1536956811'', ''1536288162'', ''1536288162''),
(78, ''872aa687f375a400633d0dfe235207bb'', ''./application/wap/view/first/static/wap/sx/images/index-icon.png'', ''1536956811'', ''1536288162'', ''1536288162''),
(79, ''e0a7a5b525ec2955efe1cc8c8d1ff1a5'', ''./application/wap/view/first/static/wap/sx/images/nav-bg.jpg'', ''1536956812'', ''1536288162'', ''1536288162''),
(80, ''6469c80d241b03a9753700d92e40d192'', ''./application/wap/view/first/static/wap/sx/images/edit.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(81, ''19a7be0746683111480a438cc61f08e2'', ''./application/wap/view/first/static/wap/sx/images/state-sqtk.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(82, ''bb6f23a89a5dc82b8bf8b32ee88be905'', ''./application/wap/view/first/static/wap/sx/images/index-icon02.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(83, ''19532e9260bc164ae316776d06d719e4'', ''./application/wap/view/first/static/wap/sx/images/index-nav04.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(84, ''3b78c31b99d836465876435bbc63d659'', ''./application/wap/view/first/static/wap/sx/images/state-send.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(85, ''d1f6c952b995dec76fc3c82703e12b37'', ''./application/wap/view/first/static/wap/sx/images/user-service05.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(86, ''280976f0d7e008d2ebcb5fe9d2da54f9'', ''./application/wap/view/first/static/wap/sx/images/pt-success.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(87, ''fee383307d877797ba4aa660027bbd00'', ''./application/wap/view/first/static/wap/sx/images/user-service07.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(88, ''db1c7430abf632bf2b922b64e4d2a645'', ''./application/wap/view/first/static/wap/sx/images/state-ytk.png'', ''1536956812'', ''1536288162'', ''1536288162''),
(89, ''e1e5153db9a7bc54c46756b57286fe3b'', ''./application/wap/view/first/static/wap/sx/images/state-nobuy.png.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(90, ''fd2188bc7e4518ec094492c915fb5f9d'', ''./application/wap/view/first/static/wap/sx/images/hot-icon.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(91, ''4e04786e110aa382c4ee7ed2e7f927e7'', ''./application/wap/view/first/static/wap/sx/images/foot-icon.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(92, ''fa5dda51a40bee121990d754d593bb73'', ''./application/wap/view/first/static/wap/sx/images/hot-banner.jpg'', ''1536956813'', ''1536288162'', ''1536288162''),
(93, ''69c5e2b5d7ebb3f1646f431f630b6faf'', ''./application/wap/view/first/static/wap/sx/images/pt-bg.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(94, ''2a3899e911c47ba674269686277856fb'', ''./application/wap/view/first/static/wap/sx/images/user-singin-bg.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(95, ''35316825d7c4dca4f1240e4b5c02c41a'', ''./application/wap/view/first/static/wap/sx/images/xin-icon.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(96, ''2f180f0ccfeac8de49c26d6ff4555e33'', ''./application/wap/view/first/static/wap/sx/images/status-complete.gif'', ''1536956813'', ''1536288162'', ''1536288162''),
(97, ''aa7ceb2b66db9251a03431536b161de3'', ''./application/wap/view/first/static/wap/sx/images/user-orders-list001.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(98, ''ae4e74bb129661a82930c8f081b1ad78'', ''./application/wap/view/first/static/wap/sx/images/gc-icon.png'', ''1536956813'', ''1536288162'', ''1536288162''),
(99, ''5e305dae2a31cf92498a26186f098b17'', ''./application/wap/view/first/static/wap/sx/images/model-close.png'', ''1536956814'', ''1536288162'', ''1536288162''),
(100, ''e68cc3a827cbf175c665cf3bd15c79c9'', ''./application/wap/view/first/static/wap/sx/images/001.jpg'', ''1536956814'', ''1536288162'', ''1536288162''),
(101, ''5a07ddf08caf040064ae0e2967aaf24f'', ''./application/wap/view/first/static/wap/sx/images/product-banner.jpg'', ''1536956814'', ''1536288162'', ''1536288162''),
(102, ''f2e366273a55e13fe0d18b9a007ce197'', ''./application/wap/view/first/static/wap/sx/images/address-icon01.png'', ''1536956814'', ''1536288162'', ''1536288162''),
(103, ''28a1bed4dbc4f8bcc217bf7a525b0ce6'', ''./application/wap/view/first/static/wap/sx/images/1.png'', ''1536956814'', ''1536288162'', ''1536288162''),
(104, ''7481ef58ed721cbbb7ee18fbf74d5380'', ''./application/wap/view/first/static/wap/sx/images/search-icon3.png'', ''1536956814'', ''1536288162'', ''1536288162''),
(105, ''398033d11a496d73c82509d4eb9466d2'', ''./application/wap/view/first/static/wap/sx/images/user-service06.png'', ''1536956814'', ''1536288162'', ''1536288162''),
(106, ''be8858b75101518ce9af9914fb1417bc'', ''./application/wap/view/first/static/wap/sx/images/user-service02.png'', ''1536956814'', ''1536288162'', ''1536288162''),
(107, ''13da7410c9d6ca50fd56d3e103ceb07c'', ''./application/wap/view/first/static/wap/sx/images/singin-title-bg.jpg'', ''1536956814'', ''1536288162'', ''1536288162''),
(108, ''0d921d59699caca425eba4f65f8fdfe2'', ''./application/wap/view/first/static/wap/sx/images/state-dqh.png'', ''1536956814'', ''1536288162'', ''1536288162''),
(109, ''8d17f175fd00902023461e882dc923cb'', ''./application/wap/view/first/static/wap/sx/images/search-icon.png'', ''1536956815'', ''1536288162'', ''1536288162''),
(110, ''ecde49e514f8e94765b06f03a765c509'', ''./application/wap/view/first/static/wap/sx/images/index-nav01.png'', ''1536956815'', ''1536288162'', ''1536288162''),
(111, ''0efb9b070f66f6812f28223161a978a6'', ''./application/wap/view/first/static/wap/sx/images/status-received.gif'', ''1536956815'', ''1536288162'', ''1536288162''),
(112, ''bf3e47117344c465c70fe454c80eef7e'', ''./application/wap/view/first/static/wap/sx/images/dropup.png'', ''1536956815'', ''1536288162'', ''1536288162''),
(113, ''67e46344d74a2c64de7113e9378c115f'', ''./application/wap/view/first/static/wap/sx/images/star-icon.png'', ''1536956815'', ''1536288162'', ''1536288162''),
(114, ''779c10d6e39a57f0f5e875b0d261adeb'', ''./application/wap/view/first/static/wap/sx/images/logistics-bg.png'', ''1536956815'', ''1536288162'', ''1536288162''),
(115, ''94839573b7f23d5d0e636026dc50d23b'', ''./application/wap/view/first/static/wap/sx/images/user-service01.png'', ''1536956815'', ''1536288162'', ''1536288162''),
(116, ''79feef98255fcf8092e72ad8003f4d10'', ''./application/wap/view/first/static/wap/sx/images/status-drop.gif'', ''1536956815'', ''1536288162'', ''1536288162''),
(117, ''c528b79c688684e10b058cab48fb8050'', ''./application/wap/view/first/static/wap/sx/images/dropdown.png'', ''1536956815'', ''1536288162'', ''1536288162''),
(118, ''b789a558388f783435a366f312378008'', ''./application/wap/view/first/static/wap/sx/images/fail.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(119, ''1d3648f8c28cfd9d99e3b595279ee5d3'', ''./application/wap/view/first/static/wap/sx/images/user-orders-list004.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(120, ''e245f8e7aed16c4f6793f229b70c9482'', ''./application/wap/view/first/static/wap/sx/images/count-wrapper-bg.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(121, ''b7e1c35162a389b3716c84b55cb4bc8d'', ''./application/wap/view/first/static/wap/sx/images/details-icon.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(122, ''8b42487613b7f2157c3208e3337dd3fc'', ''./application/wap/view/first/static/wap/sx/images/state-ysh.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(123, ''be642d609eec50f8f9e8ed2bc5197662'', ''./application/wap/view/first/static/wap/sx/images/banner1.jpg'', ''1536288162'', ''1536288162'', ''1536288162''),
(124, ''d3d0e30e040b02095569402de4b94032'', ''./application/wap/view/first/static/wap/sx/images/state-nfh.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(125, ''716b54653579b8239fb68b8a7ef61c6b'', ''./application/wap/view/first/static/wap/sx/images/user-service08.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(126, ''1cad69a59f5e9efc7dd65fd9d2db0e3e'', ''./application/wap/view/first/static/wap/sx/images/user-service03.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(127, ''a07da7e3968646ea25b221c8a447627c'', ''./application/wap/view/first/static/wap/sx/images/user-orders-list002.png'', ''1536956816'', ''1536288162'', ''1536288162''),
(128, ''a1f53eca545924cbf627a6ecfce1e3ea'', ''./application/wap/view/first/static/wap/sx/images/warn.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(129, ''7fcd142493534480e8339d93df304f87'', ''./application/wap/view/first/static/wap/sx/images/enter.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(130, ''228ce8de0fd770e8fd35584b91913f40'', ''./application/wap/view/first/static/wap/sx/images/more-icon.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(131, ''2175ce29a75052222243547341f7c6d9'', ''./application/wap/view/first/static/wap/sx/images/mtw-close.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(132, ''d925630a86763dc96f181ff398150de0'', ''./application/wap/view/first/static/wap/sx/images/index-nav02.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(133, ''4e8f2e75a7dda23ce879001a79631a1a'', ''./application/wap/view/first/static/wap/sx/images/index-nav03.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(134, ''4111e361016559f17571ca7f2c7bcb71'', ''./application/wap/view/first/static/wap/sx/images/user-orders-list003.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(135, ''bbecabe6a23274f8a9d7e282ae9c43ae'', ''./application/wap/view/first/static/wap/sx/images/user-service04.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(136, ''6272576897a2e42385ddbcf41435d938'', ''./application/wap/view/first/static/wap/sx/images/avatar.jpg'', ''1536956817'', ''1536288162'', ''1536288162''),
(137, ''d81e58a72cebac3b4462afa380a9a816'', ''./application/wap/view/first/static/wap/sx/images/lv1.png'', ''1536956817'', ''1536288162'', ''1536288162''),
(138, ''b1ece6497e977b03be68b6c31466f2cd'', ''./application/wap/view/first/static/wap/sx/images/pt-error.png'', ''1536956818'', ''1536288162'', ''1536288162''),
(139, ''114153c26ab2f7057f8b358ad330974b'', ''./application/wap/view/first/static/wap/sx/images/user-orders-list005.png'', ''1536956818'', ''1536288162'', ''1536288162''),
(140, ''fb4e7f517289f36b42c1629891623cc2'', ''./application/wap/view/first/static/wap/sx/images/wenhao.png'', ''1536956818'', ''1536288162'', ''1536288162''),
(141, ''6f5464feaa77fa830cd6fda1ce4dbab2'', ''./application/wap/view/first/static/wap/sx/images/state-ypj.png'', ''1536956818'', ''1536288162'', ''1536288162''),
(142, ''a3f43bab5ace67f3616f5ebfc66744dc'', ''./application/wap/view/first/static/wap/sx/js/iscroll.js'', ''1536956819'', ''1536288162'', ''1536288162''),
(143, ''5790ead7ad3ba27397aedfa3d263b867'', ''./application/wap/view/first/static/wap/sx/js/jquery-1.11.2.min.js'', ''1536956819'', ''1536288162'', ''1536288162''),
(144, ''8ba31474130566d0d42a0656b86d3c64'', ''./application/wap/view/first/static/wap/sx/js/swiper-3.4.1.jquery.min.js'', ''1536956819'', ''1536288162'', ''1536288162''),
(145, ''8a010634d0be8abb8370dc2aa45e065c'', ''./application/wap/view/first/static/wap/sx/js/jquery.downCount.js'', ''1536956819'', ''1536288162'', ''1536288162''),
(146, ''524e37e86d1add9491b552e36cb66fe3'', ''./application/wap/view/first/static/wap/sx/js/media.js'', ''1536956819'', ''1536288162'', ''1536288162''),
(147, ''5fd881481a9a47ed96806d4cc8a5d5b4'', ''./application/wap/view/first/static/wap/crmeb/font/iconfont.eot'', ''1536956821'', ''1536288162'', ''1536288162''),
(148, ''edf7b178b729d8c4f2c4f520112cca9c'', ''./application/wap/view/first/static/wap/crmeb/font/iconfont.svg'', ''1536956821'', ''1536288162'', ''1536288162''),
(149, ''10984d654728e3c7144ef544a4fb0397'', ''./application/wap/view/first/static/wap/crmeb/font/iconfont.ttf'', ''1536956821'', ''1536288162'', ''1536288162''),
(150, ''4ba1a9c4c7691bf3f14b0f02256e7484'', ''./application/wap/view/first/static/wap/crmeb/font/iconfont.woff'', ''1536956821'', ''1536288162'', ''1536288162''),
(151, ''0cdc63e7f3f3d23a5fc991f8801af8df'', ''./application/wap/view/first/static/wap/crmeb/font/iconfont.css'', ''1536956821'', ''1536288162'', ''1536288162''),
(152, ''e3f4544486133798845e9654ef8b1577'', ''./application/wap/view/first/static/wap/crmeb/font/iconfont.js'', ''1536956821'', ''1536288162'', ''1536288162''),
(153, ''21806dcf07b06f561e83981879e991d0'', ''./application/wap/view/first/static/wap/crmeb/module/refund-reason.js'', ''1536956822'', ''1536288162'', ''1536288162''),
(154, ''dad622705b398bc7a01154388a41ab36'', ''./application/wap/view/first/static/wap/crmeb/module/store.js'', ''1536956822'', ''1536288162'', ''1536288162''),
(155, ''061beb21d3fe56f42db2db12f1fe6ab8'', ''./application/wap/view/first/static/wap/crmeb/module/store/use-address.js'', ''1536956822'', ''1536288162'', ''1536288162''),
(156, ''9e9c3925fe78b39f0f919a2478b08779'', ''./application/wap/view/first/static/wap/crmeb/module/store/scroll-load.js'', ''1536956822'', ''1536288162'', ''1536288162''),
(157, ''d78b17fec2c20006b8a3ff421c6185b0'', ''./application/wap/view/first/static/wap/crmeb/module/store/seckill-card.js'', ''1536956822'', ''1536288162'', ''1536288162''),
(158, ''3d093a2ed730902107a3324abf141044'', ''./application/wap/view/first/static/wap/crmeb/module/store/use-coupon.js'', ''1536956823'', ''1536288162'', ''1536288162''),
(159, ''abc3a549d19c6ff66315901780796f75'', ''./application/wap/view/first/static/wap/crmeb/module/store/shop-card.js'', ''1536956823'', ''1536288162'', ''1536288162''),
(160, ''68c20c7eed5ae2919667a4fcf0bd5cab'', ''./application/wap/view/first/static/wap/crmeb/module/cart.js'', ''1536956823'', ''1536288162'', ''1536288162''),
(161, ''cedfc2e0b18a63ab39383ee3c4b9dd15'', ''./application/wap/view/first/static/wap/crmeb/module/store_service/mobile.js'', ''1536956824'', ''1536288162'', ''1536288162''),
(162, ''3fd2f2e9ab37ba9e2b20d47fcd6ee39c'', ''./application/wap/view/first/static/wap/crmeb/module/store_service/unslider.js'', ''1536956824'', ''1536288162'', ''1536288162''),
(163, ''611bcada29dc1690bd362325e4e01484'', ''./application/wap/view/first/static/wap/crmeb/module/store_service/jquery.touchSwipe.min.js'', ''1536956824'', ''1536288162'', ''1536288162''),
(164, ''e8cdc98d1a718ec002f0ba18f2edac0c'', ''./application/wap/view/first/static/wap/crmeb/module/store_service/moment.min.js'', ''1536956824'', ''1536288162'', ''1536288162''),
(165, ''08ecb77133008a15f48e14f791e8fb4c'', ''./application/wap/view/first/static/wap/crmeb/module/store_service/msn.js'', ''1536956824'', ''1536288162'', ''1536288162''),
(166, ''31de704764d37447bb8e00917e3a0d63'', ''./application/wap/view/first/static/wap/crmeb/css/service.css'', ''1536956825'', ''1536288162'', ''1536288162''),
(167, ''efaf1ffd9a687d0a482f3d8b78b12555'', ''./application/wap/view/first/static/wap/crmeb/css/store_service.css'', ''1536956825'', ''1536288162'', ''1536288162''),
(168, ''ddba69b4c4771c76fbb9281c3f2d6135'', ''./application/wap/view/first/static/wap/crmeb/css/style.css'', ''1536956826'', ''1536288162'', ''1536288162''),
(169, ''49afb224e1387b63aa36feed01174050'', ''./application/wap/view/first/static/wap/crmeb/images/right-menu-icon.png'', ''1536956826'', ''1536288162'', ''1536288162''),
(170, ''d444c68ad0b3aaeb84520ed84474049c'', ''./application/wap/view/first/static/wap/crmeb/images/icon-shandian.png'', ''1536956826'', ''1536288162'', ''1536288162''),
(171, ''8d619244496a35e5588a991424b8066d'', ''./application/wap/view/first/static/wap/crmeb/images/index-icon.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(172, ''4e95ffe4dd68f9600f3b4aa03b3ca83e'', ''./application/wap/view/first/static/wap/crmeb/images/search1.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(173, ''6469c80d241b03a9753700d92e40d192'', ''./application/wap/view/first/static/wap/crmeb/images/edit.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(174, ''1640851ee7de444214548555dc50d4c6'', ''./application/wap/view/first/static/wap/crmeb/images/yiwen.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(175, ''55ad375b306ccd8089e69901d141be60'', ''./application/wap/view/first/static/wap/crmeb/images/empty_order.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(176, ''e0e1e1ae381a32ac48c046f1676f5679'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list009.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(177, ''19a7be0746683111480a438cc61f08e2'', ''./application/wap/view/first/static/wap/crmeb/images/state-sqtk.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(178, ''e1e5153db9a7bc54c46756b57286fe3b'', ''./application/wap/view/first/static/wap/crmeb/images/state-nobuy.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(179, ''c93d59fc82c5358f5ef729b0286382ef'', ''./application/wap/view/first/static/wap/crmeb/images/index-icon02.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(180, ''41f1e97bbae0cf2ff2222e1f710c2666'', ''./application/wap/view/first/static/wap/crmeb/images/count.png'', ''1536956827'', ''1536288162'', ''1536288162''),
(181, ''929f55c1dd295be126c77a1cf432a7ff'', ''./application/wap/view/first/static/wap/crmeb/images/time.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(182, ''8fe78f810fbb9317148396ca21787b3c'', ''./application/wap/view/first/static/wap/crmeb/images/user-address.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(183, ''1a3a4920cb3ce8f8018736442f51b29a'', ''./application/wap/view/first/static/wap/crmeb/images/addto-pic.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(184, ''1f8d6ac88fae6292ada406e9918e5a03'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list004.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(185, ''1a524b4706ce8ab3a17bd5cf6d1a8b60'', ''./application/wap/view/first/static/wap/crmeb/images/fail_collect.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(186, ''3b78c31b99d836465876435bbc63d659'', ''./application/wap/view/first/static/wap/crmeb/images/state-send.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(187, ''38438252add269e1077d5699a3988ab3'', ''./application/wap/view/first/static/wap/crmeb/images/logistics-icon.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(188, ''d1f6c952b995dec76fc3c82703e12b37'', ''./application/wap/view/first/static/wap/crmeb/images/user-service05.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(189, ''6dadb31cdd0f9a6bef3dd413d158a2da'', ''./application/wap/view/first/static/wap/crmeb/images/xianxia02.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(190, ''fee383307d877797ba4aa660027bbd00'', ''./application/wap/view/first/static/wap/crmeb/images/user-service07.png'', ''1536956828'', ''1536288162'', ''1536288162''),
(191, ''111be19d491055d9dbd45017ea992889'', ''./application/wap/view/first/static/wap/crmeb/images/delete-btn.png'', ''1536956829'', ''1536288162'', ''1536288162''),
(192, ''5b7bbdeafa7546566b15e25ad3ff0264'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list002.png'', ''1536956829'', ''1536288162'', ''1536288162''),
(193, ''fa0bedf8fb77e64365dd411d81cde893'', ''./application/wap/view/first/static/wap/crmeb/images/share-info.png'', ''1536956829'', ''1536288162'', ''1536288162''),
(194, ''ba1d378801ce5ecb00bd6ee9632a83ef'', ''./application/wap/view/first/static/wap/crmeb/images/more.gif'', ''1536956829'', ''1536288162'', ''1536288162''),
(195, ''db1c7430abf632bf2b922b64e4d2a645'', ''./application/wap/view/first/static/wap/crmeb/images/state-ytk.png'', ''1536956829'', ''1536288162'', ''1536288162''),
(196, ''52862267485ebcc84ef1fe0c7f68eebf'', ''./application/wap/view/first/static/wap/crmeb/images/tu.jpg'', ''1536956829'', ''1536288162'', ''1536288162''),
(197, ''f8ebd32657b38ed26e01f4d2d4791dd4'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list006.png'', ''1536956829'', ''1536288162'', ''1536288162''),
(198, ''ea30fbf071eb5b41d3a8f31bc7a4f206'', ''./application/wap/view/first/static/wap/crmeb/images/empty_kefu.png'', ''1536956829'', ''1536288162'', ''1536288162''),
(199, ''e4ab1c82c74a6f9b928a9f4e0e1a23c4'', ''./application/wap/view/first/static/wap/crmeb/images/drug-04.jpg'', ''1536956829'', ''1536288162'', ''1536288162''),
(200, ''4b2d954533dcf4fd875d0bcd58aaecab'', ''./application/wap/view/first/static/wap/crmeb/images/star-full.png'', ''1536956829'', ''1536288162'', ''1536288162''),
(201, ''59499ea4992425edf8e8fb90e765f692'', ''./application/wap/view/first/static/wap/crmeb/images/user-balance-bg.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(202, ''f4a3e58f5c69894f7887b5d6412342f1'', ''./application/wap/view/first/static/wap/crmeb/images/integral-content-abg.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(203, ''1faaaa65dac4c191a50a9066ff85a05c'', ''./application/wap/view/first/static/wap/crmeb/images/service-icon.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(204, ''fd2188bc7e4518ec094492c915fb5f9d'', ''./application/wap/view/first/static/wap/crmeb/images/hot-icon.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(205, ''aad03a33f18acfac56ce57443dae7ed6'', ''./application/wap/view/first/static/wap/crmeb/images/integral-content-bg.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(206, ''7ae1a028c00c333e3c93dde1dcaeaabf'', ''./application/wap/view/first/static/wap/crmeb/images/phone-icon.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(207, ''332475344b7c41e4356298b25d587b9a'', ''./application/wap/view/first/static/wap/crmeb/images/ico-select.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(208, ''d5a8a97bad4e75f02aadc065d54526eb'', ''./application/wap/view/first/static/wap/crmeb/images/myci.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(209, ''702cb04ca0b64b5aa9086759990a1202'', ''./application/wap/view/first/static/wap/crmeb/images/like-line.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(210, ''b59879038f7d61ea940b6bc5bf817b90'', ''./application/wap/view/first/static/wap/crmeb/images/crmeb-logo.png'', ''1536956830'', ''1536288162'', ''1536288162''),
(211, ''c04e4b7c9f5f3418a96d9eada9421231'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list001.png'', ''1536956831'', ''1536288162'', ''1536288162''),
(212, ''0f78f231136d94b164e2dea8a734b76b'', ''./application/wap/view/first/static/wap/crmeb/images/drug-03.jpg'', ''1536956831'', ''1536288162'', ''1536288162''),
(213, ''35316825d7c4dca4f1240e4b5c02c41a'', ''./application/wap/view/first/static/wap/crmeb/images/xin-icon.png'', ''1536956831'', ''1536288162'', ''1536288162''),
(214, ''a382c6d88168927cf59d0699531f21a2'', ''./application/wap/view/first/static/wap/crmeb/images/title-back.png'', ''1536956833'', ''1536288162'', ''1536288162''),
(215, ''9074d1772f07b4e32279a3d45aab88eb'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list010.png'', ''1536956833'', ''1536288162'', ''1536288162''),
(216, ''f04397b692a93e5ef91432206b8402c7'', ''./application/wap/view/first/static/wap/crmeb/images/index-icon03.png'', ''1536956833'', ''1536288162'', ''1536288162''),
(217, ''19ebb3f416367e6d02e54013fa2e2b77'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list005.png'', ''1536956835'', ''1536288162'', ''1536288162''),
(218, ''a05942d96005875b9ead21fb4aec420d'', ''./application/wap/view/first/static/wap/crmeb/images/expired-img.png'', ''1536956835'', ''1536288162'', ''1536288162''),
(219, ''2920ecd024e1c81cf0852d5a65306fe5'', ''./application/wap/view/first/static/wap/crmeb/images/user-orders-list001.png'', ''1536956837'', ''1536288162'', ''1536288162''),
(220, ''ae4e74bb129661a82930c8f081b1ad78'', ''./application/wap/view/first/static/wap/crmeb/images/gc-icon.png'', ''1536956837'', ''1536288162'', ''1536288162''),
(221, ''fecb52c315ec4a40e99492050c7888ab'', ''./application/wap/view/first/static/wap/crmeb/images/drug-09.jpg'', ''1536956838'', ''1536288162'', ''1536288162''),
(222, ''5e305dae2a31cf92498a26186f098b17'', ''./application/wap/view/first/static/wap/crmeb/images/model-close.png'', ''1536956840'', ''1536288162'', ''1536288162''),
(223, ''778cc285b448a11610bc1aadc36e2bdd'', ''./application/wap/view/first/static/wap/crmeb/images/img_icon.png'', ''1536956840'', ''1536288162'', ''1536288162''),
(224, ''e68cc3a827cbf175c665cf3bd15c79c9'', ''./application/wap/view/first/static/wap/crmeb/images/001.jpg'', ''1536956840'', ''1536288162'', ''1536288162''),
(225, ''76e6275cf70ec0671145d96053fd0f87'', ''./application/wap/view/first/static/wap/crmeb/images/edit01.png'', ''1536956842'', ''1536288162'', ''1536288162''),
(226, ''f2e366273a55e13fe0d18b9a007ce197'', ''./application/wap/view/first/static/wap/crmeb/images/address-icon01.png'', ''1536956842'', ''1536288162'', ''1536288162''),
(227, ''d940da485aa636002f2e2cd5eb2455f8'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list008.png'', ''1536956842'', ''1536288162'', ''1536288162''),
(228, ''28a1bed4dbc4f8bcc217bf7a525b0ce6'', ''./application/wap/view/first/static/wap/crmeb/images/1.png'', ''1536956844'', ''1536288162'', ''1536288162''),
(229, ''c0b0ec9f63f8bb3afe50826def79e12e'', ''./application/wap/view/first/static/wap/crmeb/images/empt_coupon.png'', ''1536956844'', ''1536288162'', ''1536288162''),
(230, ''398033d11a496d73c82509d4eb9466d2'', ''./application/wap/view/first/static/wap/crmeb/images/user-service06.png'', ''1536956845'', ''1536288162'', ''1536288162''),
(231, ''be8858b75101518ce9af9914fb1417bc'', ''./application/wap/view/first/static/wap/crmeb/images/user-service02.png'', ''1536956847'', ''1536288162'', ''1536288162''),
(232, ''23da22c2bfd6d05db040f32325a22af7'', ''./application/wap/view/first/static/wap/crmeb/images/empty_message.png'', ''1536956847'', ''1536288162'', ''1536288162''),
(233, ''4092791ef0f38a65411cc481247282a7'', ''./application/wap/view/first/static/wap/crmeb/images/buy-cars.png'', ''1536956847'', ''1536288162'', ''1536288162''),
(234, ''0d921d59699caca425eba4f65f8fdfe2'', ''./application/wap/view/first/static/wap/crmeb/images/state-dqh.png'', ''1536956849'', ''1536288162'', ''1536288162''),
(235, ''b7ac406f273c4967acf85a27fc053da2'', ''./application/wap/view/first/static/wap/crmeb/images/empty_collect.png'', ''1536956849'', ''1536288162'', ''1536288162''),
(236, ''247a7fc750f6cda0a67a7579f2a4ff71'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list003.png'', ''1536956849'', ''1536288162'', ''1536288162''),
(237, ''de4ef1237d0d70d5642ca9cf5a177a38'', ''./application/wap/view/first/static/wap/crmeb/images/empty_address.png'', ''1536956851'', ''1536288162'', ''1536288162''),
(238, ''b7833c06f8c482976e7e83a826c22df9'', ''./application/wap/view/first/static/wap/crmeb/images/search-icon.png'', ''1536956851'', ''1536288162'', ''1536288162''),
(239, ''219ca75519f440b077b5008d528856f4'', ''./application/wap/view/first/static/wap/crmeb/images/line.png'', ''1536956851'', ''1536288162'', ''1536288162''),
(240, ''8b9aa6898f3ec02464d11cb837f583a3'', ''./application/wap/view/first/static/wap/crmeb/images/like-line-right.png'', ''1536956854'', ''1536288162'', ''1536288162''),
(241, ''dbeafa06020513e52c3b8693a25cfc8e'', ''./application/wap/view/first/static/wap/crmeb/images/drug-06.jpg'', ''1536956854'', ''1536288162'', ''1536288162''),
(242, ''1bf9f56070a72e38a51d9556684ae23a'', ''./application/wap/view/first/static/wap/crmeb/images/delete-btn1.png'', ''1536956854'', ''1536288162'', ''1536288162''),
(243, ''6820027e0030666538d8c29d61dd740b'', ''./application/wap/view/first/static/wap/crmeb/images/yue02.png'', ''1536956854'', ''1536288162'', ''1536288162''),
(244, ''d591419bc4865740bbd379b64f811c63'', ''./application/wap/view/first/static/wap/crmeb/images/exceptional-bg.jpg'', ''1536956854'', ''1536288162'', ''1536288162''),
(245, ''3a1c0de40c5b7297b1677b5ce18485ec'', ''./application/wap/view/first/static/wap/crmeb/images/star-icon.png'', ''1536956854'', ''1536288162'', ''1536288162''),
(246, ''779c10d6e39a57f0f5e875b0d261adeb'', ''./application/wap/view/first/static/wap/crmeb/images/logistics-bg.png'', ''1536956854'', ''1536288162'', ''1536288162''),
(247, ''94839573b7f23d5d0e636026dc50d23b'', ''./application/wap/view/first/static/wap/crmeb/images/user-service01.png'', ''1536956854'', ''1536288162'', ''1536288162''),
(248, ''b591b93a8b407321c7e5f0776aa1467d'', ''./application/wap/view/first/static/wap/crmeb/images/toci.png'', ''1536956854'', ''1536288162'', ''1536288162''),
(249, ''0e7883897649523b8eb26e0452e72e40'', ''./application/wap/view/first/static/wap/crmeb/images/enterprise-info.jpg'', ''1536956855'', ''1536288162'', ''1536288162''),
(250, ''87d00b582736f06be791b7de367848a3'', ''./application/wap/view/first/static/wap/crmeb/images/drug-07.jpg'', ''1536956855'', ''1536288162'', ''1536288162''),
(251, ''f8753eb5f8b143d5b376f7693c10ef35'', ''./application/wap/view/first/static/wap/crmeb/images/empty_reply.png'', ''1536956855'', ''1536288162'', ''1536288162''),
(252, ''130653ac1426444794c1768e836d6684'', ''./application/wap/view/first/static/wap/crmeb/images/user-orders-list004.png'', ''1536956855'', ''1536288162'', ''1536288162''),
(253, ''dca456346a0426031bc37bffc405a71c'', ''./application/wap/view/first/static/wap/crmeb/images/enter2.png'', ''1536956855'', ''1536288162'', ''1536288162''),
(254, ''319253331b8e77011d93d0ec64458ced'', ''./application/wap/view/first/static/wap/crmeb/images/ruzhu-banner.png'', ''1536956855'', ''1536288162'', ''1536288162''),
(255, ''fcc5f444cf865a9b540f57da82053bf3'', ''./application/wap/view/first/static/wap/crmeb/images/drug-05.jpg'', ''1536956855'', ''1536288162'', ''1536288162''),
(256, ''8b42487613b7f2157c3208e3337dd3fc'', ''./application/wap/view/first/static/wap/crmeb/images/state-ysh.png'', ''1536956855'', ''1536288162'', ''1536288162''),
(257, ''8894d224a04e2285b988a341fae46a0b'', ''./application/wap/view/first/static/wap/crmeb/images/banner1.jpg'', ''1536956855'', ''1536288162'', ''1536288162''),
(258, ''0e20368c7f50169c38311a0ddc9e817d'', ''./application/wap/view/first/static/wap/crmeb/images/banner.jpg'', ''1536956855'', ''1536288162'', ''1536288162''),
(259, ''9e768a5f5c096e3c95fd6787f4dcaf15'', ''./application/wap/view/first/static/wap/crmeb/images/chat_img.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(260, ''d3d0e30e040b02095569402de4b94032'', ''./application/wap/view/first/static/wap/crmeb/images/state-nfh.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(261, ''7323539619e0d44c4bda4d25bbbac357'', ''./application/wap/view/first/static/wap/crmeb/images/select-add.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(262, ''f183876082b28a433e3596938c190efe'', ''./application/wap/view/first/static/wap/crmeb/images/drug-08.jpg'', ''1536956856'', ''1536288162'', ''1536288162''),
(263, ''716b54653579b8239fb68b8a7ef61c6b'', ''./application/wap/view/first/static/wap/crmeb/images/user-service08.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(264, ''ea8090fa1516bf5cfdc797944fc2e57f'', ''./application/wap/view/first/static/wap/crmeb/images/enter01.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(265, ''e70be90df32ad161610212294b08c321'', ''./application/wap/view/first/static/wap/crmeb/images/nav-list007.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(266, ''7954c33a03c549ff14fc8a958457e2b0'', ''./application/wap/view/first/static/wap/crmeb/images/empty_integral.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(267, ''c0b0ec9f63f8bb3afe50826def79e12e'', ''./application/wap/view/first/static/wap/crmeb/images/empty_coupon.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(268, ''5c3642ab67d16731e37ffb7fbde1c02a'', ''./application/wap/view/first/static/wap/crmeb/images/user-service03.png'', ''1536956856'', ''1536288162'', ''1536288162''),
(269, ''6c927f8b9d5d01758853a4851043feba'', ''./application/wap/view/first/static/wap/crmeb/images/user-orders-list002.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(270, ''7fcd142493534480e8339d93df304f87'', ''./application/wap/view/first/static/wap/crmeb/images/enter.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(271, ''3b3b6b95b2d2ff585e350d8e57ada0c1'', ''./application/wap/view/first/static/wap/crmeb/images/integral-content-icon.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(272, ''7e13458f5f1315e033ec64f2820ea4eb'', ''./application/wap/view/first/static/wap/crmeb/images/empty_cart.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(273, ''228ce8de0fd770e8fd35584b91913f40'', ''./application/wap/view/first/static/wap/crmeb/images/more-icon.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(274, ''485351bd59076b80fe0d12804a0b33b8'', ''./application/wap/view/first/static/wap/crmeb/images/thickness-wrapper-bg.jpg'', ''1536956857'', ''1536288162'', ''1536288162''),
(275, ''3e5dfb3a15e34345827997b39659ecdf'', ''./application/wap/view/first/static/wap/crmeb/images/audit-status.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(276, ''5c3e5d084215b9c283427b909dfb8609'', ''./application/wap/view/first/static/wap/crmeb/images/camera-icon.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(277, ''8b79e87104b2bc529ace5fa42ce6d111'', ''./application/wap/view/first/static/wap/crmeb/images/discount-list-icon.png'', ''1536956857'', ''1536288162'', ''1536288162''),
(278, ''1f7e145e875524962f563188a325ff07'', ''./application/wap/view/first/static/wap/crmeb/images/ruzhu_banner.jpg'', ''1536956857'', ''1536288162'', ''1536288162''),
(279, ''cb7c3e0df60c7bb6b128b239d3ffc3e4'', ''./application/wap/view/first/static/wap/crmeb/images/user-orders-list003.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(280, ''2b12a8b52fe9544ea7c3156204117880'', ''./application/wap/view/first/static/wap/crmeb/images/empty_product.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(281, ''35b8327a57ca3fb173693468a500abfe'', ''./application/wap/view/first/static/wap/crmeb/images/user-service04.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(282, ''bdc0b9349efb78155934618441e909be'', ''./application/wap/view/first/static/wap/crmeb/images/ico-select02.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(283, ''63b5fcf4f27efa8f96c54c9668a1ac55'', ''./application/wap/view/first/static/wap/crmeb/images/delete-btn3.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(284, ''6272576897a2e42385ddbcf41435d938'', ''./application/wap/view/first/static/wap/crmeb/images/avatar.jpg'', ''1536956858'', ''1536288162'', ''1536288162''),
(285, ''e67186e02404320e3b71421be6c01d24'', ''./application/wap/view/first/static/wap/crmeb/images/weixin02.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(286, ''00b8bd7ec55dd8f730c53bfdc3b23c9f'', ''./application/wap/view/first/static/wap/crmeb/images/drug-10.jpg'', ''1536956858'', ''1536288162'', ''1536288162''),
(287, ''be8a3f7dd919cd4fb1a86d7df02273bd'', ''./application/wap/view/first/static/wap/crmeb/images/ewm-bar.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(288, ''0dc318734bc531b463957d3d9d74fbf2'', ''./application/wap/view/first/static/wap/crmeb/images/empty_detail.png'', ''1536956858'', ''1536288162'', ''1536288162''),
(289, ''108a659b47331d0190214aec84f829c5'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/22.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(290, ''d8105b8b143e6a398f5c93e92fd556c7'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/13.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(291, ''c7c0f7c7cd156b809b7b33943b08433e'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/31.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(292, ''922a0500c4ebbf38113043acc4dbd546'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/14.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(293, ''ea0b7dd15328b31ebd2a442c77fe8671'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/48.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(294, ''0ac0c03365a99534da5cc8e44c21258d'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/2.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(295, ''b3b8754526a57a456c9ed1be8bd02cc6'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/44.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(296, ''5be21292fd108d0221d8d6f46ec8ab37'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/50.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(297, ''30be00066787c2c6ebcb791a4decab1c'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/33.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(298, ''8db347f9ed9c68e967ab96b0559e2566'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/49.png'', ''1536956859'', ''1536288162'', ''1536288162''),
(299, ''f0cf714bd8cb5ce4765788b9bbfec102'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/11.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(300, ''69ef787a723448dc74f70c4a17a3da7a'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/43.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(301, ''37a792f5bcbe7074c8220ac0b71c4633'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/24.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(302, ''c965baf3c7b2791b9a74dc91085dc6df'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/41.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(303, ''b575b5b056dc819e6f7dbb0236170ded'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/53.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(304, ''ec6f9dffa2a82e851da3c50aa85df45b'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/52.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(305, ''febb6ac4689648448f7723404d932698'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/4.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(306, ''da7c90ae577cb99e09ea9415956dd51a'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/35.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(307, ''ff0c815d18f6046b4659a5fd0a121274'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/45.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(308, ''71bcfcf4a059d935a6d7b9e84190a091'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/3.png'', ''1536956860'', ''1536288162'', ''1536288162''),
(309, ''4acd2acf3532002a536103ce5e04ba14'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/6.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(310, ''3dd8ed544ffd11d46ddf02c1e8371570'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/25.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(311, ''814101b72f9e1a7dab020cbf03da487e'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/38.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(312, ''80f508014f34d2220c63b9a78eaa2663'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/1.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(313, ''0c53c00e8cf37fa59f9503486fb57985'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/7.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(314, ''096d1fb6be32cf4629c067cd47fb3886'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/9.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(315, ''16b309dcd2bf56d8cb154a6455337ce0'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/27.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(316, ''1977c2ba78c34d095a16d1eac150bbfc'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/17.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(317, ''36a64b4fd48ec067b113fe38087f3c81'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/8.png'', ''1536956861'', ''1536288162'', ''1536288162''),
(318, ''f0f2d734f49d24edf8be0bd09ded5c45'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/47.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(319, ''9e2695ccdd32b7345bfa2cd825a525f7'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/26.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(320, ''43f4d62af900377bd32b1f72887e38e6'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/5.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(321, ''7964e582196572ce3551d9e5efc3314e'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/39.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(322, ''9f2850690f75a13b9c4677fcf0332465'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/51.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(323, ''c99ff75b73de0a80c499389dc30d4136'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/40.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(324, ''960ae580c47cc4ae9ca0af5281760a8a'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/36.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(325, ''b63e56b2f0aca1705a869c4da45c4036'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/37.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(326, ''5f15154e3e8a92660c22d05ad591d4f6'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/16.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(327, ''cf3811ee8cfcc83ccf835916182f1f0d'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/32.png'', ''1536956862'', ''1536288162'', ''1536288162''),
(328, ''b14cb7fde60d868b8706056593c9428c'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/28.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(329, ''6080aa7f002f495f7a1ee8efff90fb74'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/12.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(330, ''4ed28c0b6228c45749e325e540363f5c'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/29.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(331, ''1537430c5f47917aa0c95a800425885e'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/10.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(332, ''e07877d7ce4ff2241b5554ce81ac1268'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/19.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(333, ''b8f5fd58ca9536ac58f0ff76433d925a'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/21.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(334, ''090c3e596d9bbcc089a01394ce4a2a6c'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/30.png'', ''1536956863'', ''1536288162'', ''1536288162'');
INSERT INTO `eb_system_file` (`id`, `cthash`, `filename`, `atime`, `mtime`, `ctime`) VALUES
(335, ''2c09d836b77d91ff82e441ced4830df7'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/15.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(336, ''870fdb248491dc271a3c6d13de948273'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/23.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(337, ''ce8566c247441b2819ae65bb5deeb670'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/20.png'', ''1536956863'', ''1536288162'', ''1536288162''),
(338, ''ef75495d03c4d637b742dfd1e68b964a'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/46.png'', ''1536956864'', ''1536288162'', ''1536288162''),
(339, ''35ecba52545ace6090293dfe7a701a7f'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/42.png'', ''1536956864'', ''1536288162'', ''1536288162''),
(340, ''8a049701933acf5f973d247f42dcd03b'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/18.png'', ''1536956864'', ''1536288162'', ''1536288162''),
(341, ''180ffe87a0ac13fa924c5e7c7e70b6dd'', ''./application/wap/view/first/static/wap/crmeb/images/storeservice/34.png'', ''1536956864'', ''1536288162'', ''1536288162''),
(342, ''077aa3d917ec786367757480d1f7f494'', ''./application/wap/view/first/static/wap/crmeb/images/drug-02.jpg'', ''1536956865'', ''1536288162'', ''1536288162''),
(343, ''3b5e238761d7f4af194581ea211bea51'', ''./application/wap/view/first/static/wap/crmeb/images/star-empty.png'', ''1536956865'', ''1536288162'', ''1536288162''),
(344, ''ab1c5a35b0dda53a6422fa24ff337d6e'', ''./application/wap/view/first/static/wap/crmeb/images/drug-01.jpg'', ''1536956865'', ''1536288162'', ''1536288162''),
(345, ''b808020f81c2d7eec34eb62e353fb416'', ''./application/wap/view/first/static/wap/crmeb/images/video-play.png'', ''1536956865'', ''1536288162'', ''1536288162''),
(346, ''e02cbc013861be0f079a23609dac9cf9'', ''./application/wap/view/first/static/wap/crmeb/images/user-orders-list005.png'', ''1536956865'', ''1536288162'', ''1536288162''),
(347, ''6cfd6f2169537900d7f0d3f037ddf746'', ''./application/wap/view/first/static/wap/crmeb/images/express_icon.jpg'', ''1536956865'', ''1536288162'', ''1536288162''),
(348, ''fb4e7f517289f36b42c1629891623cc2'', ''./application/wap/view/first/static/wap/crmeb/images/wenhao.png'', ''1536956865'', ''1536288162'', ''1536288162''),
(349, ''6f5464feaa77fa830cd6fda1ce4dbab2'', ''./application/wap/view/first/static/wap/crmeb/images/state-ypj.png'', ''1536956865'', ''1536288162'', ''1536288162''),
(350, ''17d766ecada9a0f414ef00fbdc8b1411'', ''./application/wap/view/first/static/wap/crmeb/picture/index-icon.png'', ''1536956866'', ''1536288162'', ''1536288162''),
(351, ''6d7afdc1aa43b4362a8c9c9878ba1ab4'', ''./application/wap/view/first/static/wap/crmeb/picture/#U70ed#U5356.png'', ''1536956866'', ''1536288162'', ''1536288162''),
(352, ''c634cfcba59540ca6c76da534405dbc3'', ''./application/wap/view/first/static/wap/crmeb/picture/#U4fc3#U9500.png'', ''1536956866'', ''1536288162'', ''1536288162''),
(353, ''9b98bd7f6e0793535307b72f17bd73b8'', ''./application/wap/view/first/static/wap/crmeb/picture/#U65b0#U54c1.png'', ''1536956866'', ''1536288162'', ''1536288162''),
(354, ''71f6f7b3999a72d37bef15d1e5eb430d'', ''./application/wap/view/first/static/wap/crmeb/picture/test.jpg'', ''1536288162'', ''1536288162'', ''1536288162''),
(355, ''383a23e34d454e69aa70d2b1eeb8d5eb'', ''./application/wap/view/first/static/wap/crmeb/picture/001.jpg'', ''1536956867'', ''1536288162'', ''1536288162''),
(356, ''51affd3bc1bc053e13943375b60a571a'', ''./application/wap/view/first/static/wap/crmeb/picture/003.jpg'', ''1536956867'', ''1536288162'', ''1536288162''),
(357, ''17d766ecada9a0f414ef00fbdc8b1411'', ''./application/wap/view/first/static/wap/crmeb/picture/img.png'', ''1536956867'', ''1536288162'', ''1536288162''),
(358, ''02a88b5b43a46bb5608eb1fbe5b88eae'', ''./application/wap/view/first/static/wap/crmeb/picture/#U62e8#U53f7.png'', ''1536956867'', ''1536288162'', ''1536288162''),
(359, ''63811bc309c22e32ffe682c280344367'', ''./application/wap/view/first/static/wap/crmeb/picture/002.jpg'', ''1536956867'', ''1536288162'', ''1536288162''),
(360, ''6e7f1aeee960e2d9bc80cfbf4914484a'', ''./application/wap/view/first/static/wap/crmeb/picture/004.jpg'', ''1536956867'', ''1536288162'', ''1536288162''),
(361, ''0e96ea4ff16da9aa5e58940828c7a379'', ''./application/wap/view/first/static/wap/crmeb/picture/#U4fe1#U5c01.png'', ''1536956867'', ''1536288162'', ''1536288162''),
(362, ''63f5de3a2cb695f34912de04a89bee41'', ''./application/wap/view/first/static/wap/crmeb/picture/001.png'', ''1536956867'', ''1536288162'', ''1536288162''),
(363, ''1686ec765986843a17c1a1f4b7318f15'', ''./application/wap/view/first/static/wap/crmeb/picture/drug-banner.jpg'', ''1536956869'', ''1536288162'', ''1536288162''),
(364, ''d19d756e4efc4e9b5a4ceea5d7cf5eaf'', ''./application/wap/view/first/static/wap/crmeb/picture/avatar.jpg'', ''1536956870'', ''1536288162'', ''1536288162''),
(365, ''39d408a208ddb827b17e459da72e94b7'', ''./application/wap/view/first/static/wap/crmeb/picture/ewm.jpg'', ''1536956870'', ''1536288162'', ''1536288162''),
(366, ''61c5bc2c8be74181f095947a700733cb'', ''./application/wap/view/first/static/wap/crmeb/js/lottie.min.js'', ''1536956870'', ''1536288162'', ''1536288162''),
(367, ''5790ead7ad3ba27397aedfa3d263b867'', ''./application/wap/view/first/static/wap/crmeb/js/jquery-1.11.2.min.js'', ''1536956871'', ''1536288162'', ''1536288162''),
(368, ''8ba31474130566d0d42a0656b86d3c64'', ''./application/wap/view/first/static/wap/crmeb/js/swiper-3.4.1.jquery.min.js'', ''1536956871'', ''1536288162'', ''1536288162''),
(369, ''8a010634d0be8abb8370dc2aa45e065c'', ''./application/wap/view/first/static/wap/crmeb/js/jquery.downCount.js'', ''1536956871'', ''1536288162'', ''1536288162''),
(370, ''9200c742b8d7ec1a796757e95fa1520c'', ''./application/wap/view/first/static/wap/crmeb/js/animation.json'', ''1536956871'', ''1536288162'', ''1536288162''),
(371, ''b495c90270253d49afaac9b65563efae'', ''./application/wap/view/first/static/wap/crmeb/js/common.js'', ''1536956871'', ''1536288162'', ''1536288162''),
(372, ''524e37e86d1add9491b552e36cb66fe3'', ''./application/wap/view/first/static/wap/crmeb/js/media.js'', ''1536956871'', ''1536288162'', ''1536288162''),
(373, ''2aa3bf97cd455499c2117dfeb6458c8c'', ''./application/wap/view/first/static/wap/crmeb/js/base.js'', ''1536956871'', ''1536288162'', ''1536288162''),
(374, ''f85be0f6523f37d7d16b8ac682c76cf7'', ''./application/wap/view/first/static/wap/crmeb/js/car-model.js'', ''1536956871'', ''1536288162'', ''1536288162''),
(375, ''f08909a24ab3c14e05db4da649504732'', ''./application/wap/view/first/static/wap/bargain/font/iconfont.eot'', ''1536956873'', ''1536288162'', ''1536288162''),
(376, ''fd8e589117ba847e5b9493a4c1655ed0'', ''./application/wap/view/first/static/wap/bargain/font/iconfont.svg'', ''1536956873'', ''1536288162'', ''1536288162''),
(377, ''c572afcc31e6977463c26ed66f6790f1'', ''./application/wap/view/first/static/wap/bargain/font/iconfont.ttf'', ''1536956873'', ''1536288162'', ''1536288162''),
(378, ''093a5f3ceb70ea9952d77014b0a50a61'', ''./application/wap/view/first/static/wap/bargain/font/iconfont.woff'', ''1536956873'', ''1536288162'', ''1536288162''),
(379, ''e6ddc273d7f046acaa7312ae0690231b'', ''./application/wap/view/first/static/wap/bargain/font/iconfont.css'', ''1536956873'', ''1536288162'', ''1536288162''),
(380, ''e26414f42bc01fc7185cbbaa48dad984'', ''./application/wap/view/first/static/wap/bargain/font/iconfont.js'', ''1536956873'', ''1536288162'', ''1536288162''),
(381, ''6af34d0737ad0ca608111771cf74cc79'', ''./application/wap/view/first/static/wap/bargain/css/swiper.min.css'', ''1536956874'', ''1536288162'', ''1536288162''),
(382, ''15b4e7634a4fd7f871bafa4385916763'', ''./application/wap/view/first/static/wap/bargain/css/base.css'', ''1536956874'', ''1536288162'', ''1536288162''),
(383, ''007eba4047ec5c0a505d99fe1f8d0492'', ''./application/wap/view/first/static/wap/bargain/css/style.css'', ''1536956874'', ''1536288162'', ''1536288162''),
(384, ''106701d8d5454f65c8a846f81730c242'', ''./application/wap/view/first/static/wap/bargain/css/reset.css'', ''1536956874'', ''1536288162'', ''1536288162''),
(385, ''8ec0ba966fa302986d9a05f829e6a3ca'', ''./application/wap/view/first/static/wap/bargain/css/FJL.picker.css'', ''1536956874'', ''1536288162'', ''1536288162''),
(386, ''701c108935205a98f5d3ea6382370036'', ''./application/wap/view/first/static/wap/bargain/images/cut-con-bg.png'', ''1536956875'', ''1536288162'', ''1536288162''),
(387, ''72e7a97aec80f65e21cf7483dad21ebb'', ''./application/wap/view/first/static/wap/bargain/images/owl_happy.png'', ''1536956875'', ''1536288162'', ''1536288162''),
(388, ''cf2cb8811f231dcc5aa6c1c69abe82bf'', ''./application/wap/view/first/static/wap/bargain/images/member-binding-line.png'', ''1536956875'', ''1536288162'', ''1536288162''),
(389, ''3ed15c7e7e9bf3e625b0914183d8dc21'', ''./application/wap/view/first/static/wap/bargain/images/new-page-pic.jpg'', ''1536956875'', ''1536288162'', ''1536288162''),
(390, ''2c42ffe56fb690e0251ccfbd2c199073'', ''./application/wap/view/first/static/wap/bargain/images/member-binding-line2.png'', ''1536956876'', ''1536288162'', ''1536288162''),
(391, ''d76763af8f166ccd2935545787aa43cb'', ''./application/wap/view/first/static/wap/bargain/images/cut-but-icon.png'', ''1536956876'', ''1536288162'', ''1536288162''),
(392, ''30b640f20f49da4a46e49480cec5f583'', ''./application/wap/view/first/static/wap/bargain/images/new-page-banner.jpg'', ''1536956876'', ''1536288162'', ''1536288162''),
(393, ''7b22ed142170d3a2e17a75bb3c123fdd'', ''./application/wap/view/first/static/wap/bargain/images/order-submission.jpg'', ''1536956876'', ''1536288162'', ''1536288162''),
(394, ''4719bed779ccab85f47ae0ef125465b4'', ''./application/wap/view/first/static/wap/bargain/images/promotion-bg.png'', ''1536956876'', ''1536288162'', ''1536288162''),
(395, ''8f96d450cb3d2e31adaba69540a74d71'', ''./application/wap/view/first/static/wap/bargain/images/cut-con-title.png'', ''1536956876'', ''1536288162'', ''1536288162''),
(396, ''1a332b15dbd3791fff6ec3abad3a6692'', ''./application/wap/view/first/static/wap/bargain/images/count-icon.png'', ''1536956876'', ''1536288162'', ''1536288162''),
(397, ''d71a119fae573ef125e9adb7b4911669'', ''./application/wap/view/first/static/wap/bargain/images/time-icon.png'', ''1536956876'', ''1536288162'', ''1536288162''),
(398, ''04dbf174fccec162d1b9ff8a69218fc8'', ''./application/wap/view/first/static/wap/bargain/images/cut-con-line.png'', ''1536956876'', ''1536288162'', ''1536288162''),
(399, ''12c5a0cd61282e77c5cc77072780dfcc'', ''./application/wap/view/first/static/wap/bargain/images/cut-list-bg.jpg'', ''1536956877'', ''1536288162'', ''1536288162''),
(400, ''d6945dbf690beb104aa803841cfc2741'', ''./application/wap/view/first/static/wap/bargain/images/cut-con-mask.png'', ''1536956877'', ''1536288162'', ''1536288162''),
(401, ''8cf2d95817f15e247b2f17af24719874'', ''./application/wap/view/first/static/wap/bargain/images/newtext.png'', ''1536956877'', ''1536288162'', ''1536288162''),
(402, ''e2f2ae6f900fa95ebc88a45fb9218b28'', ''./application/wap/view/first/static/wap/bargain/images/order-list.jpg'', ''1536956877'', ''1536288162'', ''1536288162''),
(403, ''6f6b3f95e7e2d9cce01cc5ca7f87223a'', ''./application/wap/view/first/static/wap/bargain/images/cut-con-line.jpg'', ''1536956877'', ''1536288162'', ''1536288162''),
(404, ''e2c54f7aac00cf865900089592ff4420'', ''./application/wap/view/first/static/wap/bargain/js/FJL.min.js'', ''1536956878'', ''1536288162'', ''1536288162''),
(405, ''c834bef6c94c1220bf1ebaf0b9a8f01e'', ''./application/wap/view/first/static/wap/bargain/js/media_750.js'', ''1536956878'', ''1536288162'', ''1536288162''),
(406, ''b4699fa2f3452754d3d6d059ef107cf5'', ''./application/wap/view/first/static/wap/bargain/js/FJL.picker.min.js'', ''1536956878'', ''1536288162'', ''1536288162''),
(407, ''f20c4cecb1c88d476f9a698fd7a7ee06'', ''./application/wap/view/first/static/wap/bargain/js/jquery-2.1.4.min.js'', ''1536956878'', ''1536288162'', ''1536288162''),
(408, ''524e37e86d1add9491b552e36cb66fe3'', ''./application/wap/view/first/static/wap/bargain/js/media.js'', ''1536956878'', ''1536288162'', ''1536288162''),
(409, ''fb13ef3e875ca3497ede35d3774be9d3'', ''./application/wap/view/first/static/wap/bargain/js/swiper.min.js'', ''1536956878'', ''1536288162'', ''1536288162''),
(410, ''5b68d396062fedd882debb260998a387'', ''./application/wap/view/first/index/spread.html'', ''1536956881'', ''1536288162'', ''1536288162''),
(411, ''29657a893ffa9184b61b767cb0dad9de'', ''./application/wap/view/first/index/index.html'', ''1536956881'', ''1536288162'', ''1536288162''),
(412, ''31a60eca8ee05181e1317d6f93983bf3'', ''./application/wap/view/first/index/about.html'', ''1536956881'', ''1536288162'', ''1536288162''),
(413, ''31c4b2464d31d429161da4b2266d3568'', ''./application/wap/view/first/login/index.html'', ''1536956882'', ''1536288162'', ''1536288162''),
(414, ''6585778504e8ede299170c7140c330d2'', ''./application/wap/view/first/service/service_ing.html'', ''1536956883'', ''1536288162'', ''1536288162''),
(415, ''009ec00b63d49670263594300d0715f4'', ''./application/wap/view/first/service/service_list.html'', ''1536956883'', ''1536288162'', ''1536288162''),
(416, ''78737ac30b1088486d212bace5b2e86a'', ''./application/wap/view/first/service/server_ing.html'', ''1536956883'', ''1536288162'', ''1536288162''),
(417, ''b9563fa9ee9ad34a9bf35c1402ca594e'', ''./application/wap/view/first/service/service_new.html'', ''1536956883'', ''1536288162'', ''1536288162''),
(418, ''0fff8ff3e57ad877c617421a6a7730d2'', ''./application/wap/view/first/store/confirm_order.html'', ''1536956884'', ''1536288162'', ''1536288162''),
(419, ''4aeded8eedfd39b1c6a1ac5926397d9b'', ''./application/wap/view/first/store/category.html'', ''1536956884'', ''1536288162'', ''1536288162''),
(420, ''1820524518cceb5b64c1020b6e660bf4'', ''./application/wap/view/first/store/seckill_index.html'', ''1536956884'', ''1536288162'', ''1536288162''),
(421, ''a6d7d2cba2f43d482e4827722fc8f4b4'', ''./application/wap/view/first/store/reply_list.html'', ''1536956884'', ''1536288162'', ''1536288162''),
(422, ''5f85cdc53c18cd34eb97b4695f07198a'', ''./application/wap/view/first/store/detail.html'', ''1536956885'', ''1536288162'', ''1536288162''),
(423, ''aa45318b5ce1dfbba51124e7591bdf95'', ''./application/wap/view/first/store/index.html'', ''1536956885'', ''1536288162'', ''1536288162''),
(424, ''71eb1709eac1ec2642b0c34bea95b595'', ''./application/wap/view/first/store/issue_coupon.html'', ''1536956885'', ''1536288162'', ''1536288162''),
(425, ''3f19c5307b3aff1c68a5a1d8ce52126f'', ''./application/wap/view/first/store/seckill_detail.html'', ''1536956885'', ''1536288162'', ''1536288162''),
(426, ''8f3bcad53cca380b15d90a14478de850'', ''./application/wap/view/first/store/cart.html'', ''1536956885'', ''1536288162'', ''1536288162''),
(427, ''7e3de9b10618efcc9a48f1fb2d0b0079'', ''./application/wap/view/first/article/index.html'', ''1536956886'', ''1536288162'', ''1536288162''),
(428, ''86351da04951adb02275e4289dcbadc4'', ''./application/wap/view/first/article/visit.html'', ''1536956886'', ''1536288162'', ''1536288162''),
(429, ''42675e0f04a53fd6a8757653dccb287c'', ''./application/wap/view/first/public/store_menu.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(430, ''489dc3209e361dfb15c6da365c46dbe3'', ''./application/wap/view/first/public/foot.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(431, ''3272d9f6eedc9b111788c08e51790b79'', ''./application/wap/view/first/public/container.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(432, ''28dcbe8de5219675102312ad153003ad'', ''./application/wap/view/first/public/error.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(433, ''5096149c69dd7d47efc1e33fce75b1c9'', ''./application/wap/view/first/public/head.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(434, ''9c1cb0ecc21631054cca8e45b7d3ffbb'', ''./application/wap/view/first/public/success.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(435, ''bb4fcfa99bf956f33a90877fa310f089'', ''./application/wap/view/first/public/style.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(436, ''34286022649a5c7805b6de76d5d06910'', ''./application/wap/view/first/public/right_nav.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(437, ''bc28ac31905c3f6107356e29741841f5'', ''./application/wap/view/first/public/requirejs.html'', ''1536956887'', ''1536288162'', ''1536288162''),
(438, ''466c95004805a0330cc690fc4fe4e2e5'', ''./application/wap/common.php'', ''1536956890'', ''1536288162'', ''1536288162''),
(439, ''6287a2ea3838305457333a1756fcada6'', ''./application/wap/config.php'', ''1536956890'', ''1536288162'', ''1536288162''),
(440, ''add91c123886aeb7f3d294c3f49b0e84'', ''./application/route.php'', ''1536956890'', ''1536288162'', ''1536288162''),
(441, ''8f987736aa148885fac17fcf4d73b933'', ''./application/admin/controller/widget/Images.php'', ''1536956891'', ''1536288162'', ''1536288162''),
(442, ''ef77d67cd9a6c86ae70831547ea7734d'', ''./application/admin/controller/widget/Widgets.php'', ''1536956891'', ''1536288162'', ''1536288162''),
(443, ''14222e150c918c99038f0af5e2bda186'', ''./application/admin/controller/order/StoreOrder.php'', ''1536956891'', ''1536288162'', ''1536288162''),
(444, ''1091d702f3bd7623238981c1c7fed82e'', ''./application/admin/controller/order/StoreOrderPink.php'', ''1536956892'', ''1536288162'', ''1536288162''),
(445, ''a6fe440eb261ba90b514bae6a32f1ee1'', ''./application/admin/controller/Common.php'', ''1536956892'', ''1536288162'', ''1536288162''),
(446, ''5e4cf24567b6f3b0ff1a5e985c34bf3a'', ''./application/admin/controller/agent/AgentManage.php'', ''1536956892'', ''1536288162'', ''1536288162''),
(447, ''7b26232da0a9ec7098e488497d0cccfa'', ''./application/admin/controller/wechat/Reply.php'', ''1536956899'', ''1536734094'', ''1536734094''),
(448, ''6eedba963cf6b6236c5fa3457d691aca'', ''./application/admin/controller/wechat/Menus.php'', ''1536956909'', ''1536288162'', ''1536288162''),
(449, ''58763447de90199c3d85f4a20de8f7ed'', ''./application/admin/controller/wechat/StoreService.php'', ''1536956909'', ''1536288162'', ''1536288162''),
(450, ''795c0ed0e42b93a065c559df7ecfb3c1'', ''./application/admin/controller/wechat/WechatNewsCategory.php'', ''1536956910'', ''1536288162'', ''1536288162''),
(451, ''362d50df62ca5e5c12de17c569def000'', ''./application/admin/controller/wechat/WechatNews.php'', ''1536956910'', ''1536288162'', ''1536288162''),
(452, ''cae75f8c0c3e03b7b313c955841b9b17'', ''./application/admin/controller/wechat/WechatMessage.php'', ''1536956910'', ''1536288162'', ''1536288162''),
(453, ''3db884b734d329c8d95e40150cf757ab'', ''./application/admin/controller/wechat/ArticleCategory.php'', ''1536956910'', ''1536288162'', ''1536288162''),
(454, ''c453aab495d2f3c56f1485bea9f3e105'', ''./application/admin/controller/wechat/WechatUser.php'', ''1536956910'', ''1536288162'', ''1536288162''),
(455, ''203bd48981215d284ffc3c381fa5fe41'', ''./application/admin/controller/wechat/WechatTemplate.php'', ''1536956910'', ''1536288162'', ''1536288162''),
(456, ''070a6af695ccec4c7b25aeaf3eb04539'', ''./application/admin/controller/wechat/index.php'', ''1536956910'', ''1536288162'', ''1536288162''),
(457, ''78753c83833e9b9c98308b68546396ac'', ''./application/admin/controller/AuthController.php'', ''1536956911'', ''1536288162'', ''1536288162''),
(458, ''df79cfb699c9b32ad5f321b05767af94'', ''./application/admin/controller/user/UserNotice.php'', ''1536956911'', ''1536288162'', ''1536288162''),
(459, ''614640a10959a0b1ae848f2b727009b1'', ''./application/admin/controller/user/User.php'', ''1536956911'', ''1536288162'', ''1536288162''),
(460, ''599d7894214eafb6e87b5d870395403a'', ''./application/admin/controller/record/StoreStatistics.php'', ''1536956912'', ''1536288162'', ''1536288162''),
(461, ''e558f15160f09a8568aa6f319c1557fd'', ''./application/admin/controller/record/Record.php'', ''1536956912'', ''1536288162'', ''1536288162''),
(462, ''1437ad438f5bb38d63e3e149a87925f4'', ''./application/admin/controller/setting/SystemAdmin.php'', ''1536956913'', ''1536288162'', ''1536288162''),
(463, ''d89ae3115dffbe3a2c3b76e41e3fb978'', ''./application/admin/controller/setting/SystemGroupData.php'', ''1536956913'', ''1536288162'', ''1536288162''),
(464, ''37c7e28e1905a3b32dd46ae40f00deca'', ''./application/admin/controller/setting/SystemConfig.php'', ''1536956913'', ''1536288162'', ''1536288162''),
(465, ''8b811d6d00ab3358b130fd02720b0ef8'', ''./application/admin/controller/setting/SystemRole.php'', ''1536956913'', ''1536288162'', ''1536288162''),
(466, ''0112b5cca9df5c8c7601dab83e058b05'', ''./application/admin/controller/setting/SystemConfigTab.php'', ''1536956913'', ''1536288162'', ''1536288162''),
(467, ''de43fc2c90b5d9cb7fe7f44d0fd76728'', ''./application/admin/controller/setting/SystemGroup.php'', ''1536956913'', ''1536288162'', ''1536288162''),
(468, ''a9c651067115308d02334b85aac50e82'', ''./application/admin/controller/setting/SystemMenus.php'', ''1536956914'', ''1536288162'', ''1536288162''),
(469, ''2c33bb1e70426696e4e69d14c2a9cf55'', ''./application/admin/controller/setting/SystemNotice.php'', ''1536956924'', ''1536288162'', ''1536288162''),
(470, ''f7ea7ff9498edcb03ec2b60b1434000d'', ''./application/admin/controller/Login.php'', ''1536956926'', ''1536288162'', ''1536288162''),
(471, ''2790bc8b9459d9629be87a022fca934b'', ''./application/admin/controller/ump/StoreCouponUser.php'', ''1536956927'', ''1536288162'', ''1536288162''),
(472, ''c73bd0a7d895ea95a517196912dcc1c1'', ''./application/admin/controller/ump/UserPoint.php'', ''1536956927'', ''1536288162'', ''1536288162''),
(473, ''b5c2c26ea7cff1b78ff0d84ee61f743b'', ''./application/admin/controller/ump/StoreCoupon.php'', ''1536956927'', ''1536288162'', ''1536288162''),
(474, ''12e8708941d012ca74cfe67e1e9615db'', ''./application/admin/controller/ump/StoreSeckill.php'', ''1536956927'', ''1536288162'', ''1536288162''),
(475, ''f82b8e96d92567b4de1d06ab83cf8ea9'', ''./application/admin/controller/ump/StoreCouponIssue.php'', ''1536956927'', ''1536288162'', ''1536288162''),
(476, ''76659d7b83219aa2cc47bff01d995729'', ''./application/admin/controller/store/StoreCategory.php'', ''1536956928'', ''1536288162'', ''1536288162''),
(477, ''c0bbac9836540a2e9838a894fcef1c8b'', ''./application/admin/controller/store/StoreProductReply.php'', ''1536956928'', ''1536288162'', ''1536288162''),
(478, ''d6365b4a9ae5205618672b38ca88c294'', ''./application/admin/controller/store/StoreProduct.php'', ''1536956928'', ''1536573879'', ''1536573879''),
(479, ''e5fd10785512fc4a96f63aa81a02b277'', ''./application/admin/controller/store/StoreInfoMana.php'', ''1536956928'', ''1536288162'', ''1536288162''),
(480, ''c208e99dfcde1f0997463f3a69e849ff'', ''./application/admin/controller/routine/RoutineTemplate.php'', ''1536956929'', ''1536288162'', ''1536288162''),
(481, ''4963020746c639def571076009ad5616'', ''./application/admin/controller/system/SystemFile.php'', ''1536956930'', ''1536288162'', ''1536288162''),
(482, ''e6e852c3bcbfc0d72a951b9f88a61eee'', ''./application/admin/controller/system/Clear.php'', ''1536956930'', ''1536288162'', ''1536288162''),
(483, ''6c5854ef594c4dff55d03d3440dcab60'', ''./application/admin/controller/system/SystemCleardata.php'', ''1536956930'', ''1536288162'', ''1536288162''),
(484, ''7b89422e891e89043afbaac5e7f0caad'', ''./application/admin/controller/system/SystemAttachment.php'', ''1536956930'', ''1536288162'', ''1536288162''),
(485, ''3e49239d448363b5b4cb725628e3e6b5'', ''./application/admin/controller/system/SystemUpgradeclient.php'', ''1536956930'', ''1536288162'', ''1536288162''),
(486, ''d413b6ff4d821ce3d59af480a0a41e9a'', ''./application/admin/controller/system/SystemClear.php'', ''1536956932'', ''1536288162'', ''1536288162''),
(487, ''2050f672673aca11df0e092e7d52ac15'', ''./application/admin/controller/system/SystemLog.php'', ''1536956935'', ''1536288162'', ''1536288162''),
(488, ''31df27626fb6b05b3b0900bf8025a9c6'', ''./application/admin/controller/AuthApi.php'', ''1536956937'', ''1536288162'', ''1536288162''),
(489, ''9a3a5dbd5542e55dadc0e098ace19618'', ''./application/admin/controller/article/Article.php'', ''1536956945'', ''1536288162'', ''1536288162''),
(490, ''e9b3e026fe2bfec46ad070ce12316e1c'', ''./application/admin/controller/article/ArticleCategory.php'', ''1536956956'', ''1536288162'', ''1536288162''),
(491, ''bd34f90159a78668c6d13b343170303c'', ''./application/admin/controller/finance/Finance.php'', ''1536956956'', ''1536288162'', ''1536288162''),
(492, ''fc69b46f2715100b810449ab91025f6d'', ''./application/admin/controller/finance/UserExtract.php'', ''1536956957'', ''1536288162'', ''1536288162''),
(493, ''e4e193f7d2233f584a8ff50b5437c197'', ''./application/admin/controller/finance/UserRecharge.php'', ''1536956957'', ''1536288162'', ''1536288162''),
(494, ''58052a8ac240d45883cd5ca1f121ce9a'', ''./application/admin/controller/Index.php'', ''1536956957'', ''1536288162'', ''1536288162''),
(495, ''1c27a3268ca555ccf406eb9f7db5b266'', ''./application/admin/readme.txt'', ''1536956958'', ''1536288162'', ''1536288162''),
(496, ''cc950e7da38df3017ea7d477a95f71fd'', ''./application/admin/model/order/StoreOrder.php'', ''1536956958'', ''1536288162'', ''1536288162''),
(497, ''2a8a2541a34c3a9bd73f32f557c69f0e'', ''./application/admin/model/order/StoreOrderStatus.php'', ''1536956958'', ''1536288162'', ''1536288162''),
(498, ''35561d0c3b8fa5f3add2b50de08981c6'', ''./application/admin/model/wechat/StoreServiceLog.php'', ''1536956959'', ''1536288162'', ''1536288162''),
(499, ''95b7e284eb6a724b9d8f73f233cb6be7'', ''./application/admin/model/wechat/StoreService.php'', ''1536956959'', ''1536288162'', ''1536288162''),
(500, ''2323cd012036ce22044d1e51dcc112ef'', ''./application/admin/model/wechat/WechatNewsCategory.php'', ''1536956959'', ''1536288162'', ''1536288162''),
(501, ''23a0669dc7c5f14b06a54e780f574ef9'', ''./application/admin/model/wechat/WechatNews.php'', ''1536956959'', ''1536288162'', ''1536288162''),
(502, ''326932a33f9806f02fbae16980a3e6c9'', ''./application/admin/model/wechat/WechatQrcode.php'', ''1536956959'', ''1536288162'', ''1536288162''),
(503, ''ff2b6beecda2696e17b9fe3fd374963b'', ''./application/admin/model/wechat/WechatReply.php'', ''1536956960'', ''1536288162'', ''1536288162''),
(504, ''82ec5f4eed3dcf022700a51b69437ee0'', ''./application/admin/model/wechat/WechatMessage.php'', ''1536956960'', ''1536288162'', ''1536288162''),
(505, ''627d094c4e20670d0a56cda98134bc61'', ''./application/admin/model/wechat/ArticleCategory.php'', ''1536956960'', ''1536288162'', ''1536288162''),
(506, ''334e613aa69c11a4ef47392fe8cc0398'', ''./application/admin/model/wechat/WechatUser.php'', ''1536956960'', ''1536288162'', ''1536288162''),
(507, ''d9e48a600a43efa4d4b1cfc8498c378c'', ''./application/admin/model/wechat/WechatTemplate.php'', ''1536956960'', ''1536288162'', ''1536288162''),
(508, ''68513bca071465f26e50c2e9c7e49075'', ''./application/admin/model/user/UserNotice.php'', ''1536956961'', ''1536288162'', ''1536288162''),
(509, ''3b8e6d86b4c9647b1069184115cada03'', ''./application/admin/model/user/UserPoint.php'', ''1536956961'', ''1536288162'', ''1536288162''),
(510, ''8a0305e608b5127fddbf085ee2a94bbc'', ''./application/admin/model/user/UserNoticeSee.php'', ''1536956961'', ''1536288162'', ''1536288162''),
(511, ''71b92805d820b87ca527b82b2886f115'', ''./application/admin/model/user/UserBill.php'', ''1536956961'', ''1536288162'', ''1536288162''),
(512, ''9404397496808c667393c8c5bcf2278e'', ''./application/admin/model/user/User.php'', ''1536956961'', ''1536288162'', ''1536288162''),
(513, ''fa0fdbc763b7ac917d4b59deeb1e8f6f'', ''./application/admin/model/user/UserExtract.php'', ''1536956961'', ''1536288162'', ''1536288162''),
(514, ''3854e43465a892ebee379673ba480b29'', ''./application/admin/model/user/UserRecharge.php'', ''1536956962'', ''1536288162'', ''1536288162''),
(515, ''106ce9ca42ba1ca7bbe7ab09864f5247'', ''./application/admin/model/record/StoreStatistics.php'', ''1536956962'', ''1536288162'', ''1536288162''),
(516, ''e4bf82f6d2f98b37e13560168ddac08a'', ''./application/admin/model/record/StoreVisit.php'', ''1536956966'', ''1536288162'', ''1536288162''),
(517, ''f183fc02bc7006b3bd4f6e5d94b71c7e'', ''./application/admin/model/ump/StoreSeckillAttrResult.php'', ''1536956971'', ''1536288162'', ''1536288162''),
(518, ''f0b76f833800261dc7ba9af12e810099'', ''./application/admin/model/ump/StoreCouponUser.php'', ''1536956971'', ''1536288162'', ''1536288162''),
(519, ''1173ae016c762a611c6ca6ecc51703f5'', ''./application/admin/model/ump/StoreCoupon.php'', ''1536956972'', ''1536288162'', ''1536288162''),
(520, ''d34c1f65e1383fbbe54bb5063bdf55a3'', ''./application/admin/model/ump/StoreSeckillAttrValue.php'', ''1536956972'', ''1536288162'', ''1536288162''),
(521, ''502ca1950d6f95ba4360fecaac60ae85'', ''./application/admin/model/ump/StoreSeckill.php'', ''1536956972'', ''1536288162'', ''1536288162''),
(522, ''2eb7a8a0a52ad43e298bf949b54f38ca'', ''./application/admin/model/ump/StoreSeckillAttr.php'', ''1536956972'', ''1536288162'', ''1536288162''),
(523, ''6ffbb4218605c65b6284f864b48f198f'', ''./application/admin/model/ump/StoreCouponIssueUser.php'', ''1536956972'', ''1536288162'', ''1536288162''),
(524, ''cd3e7ef93a3de2bd3b26b683cc1af2ae'', ''./application/admin/model/ump/StoreCouponIssue.php'', ''1536956972'', ''1536288162'', ''1536288162''),
(525, ''8aaea89361fc2d2149adc2618adef958'', ''./application/admin/model/store/StoreProductAttr.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(526, ''62e96b66fb500ca592f83df9dec0a362'', ''./application/admin/model/store/StoreServiceLog.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(527, ''c19d45f80fcc9777f909d409a7a11f15'', ''./application/admin/model/store/StoreCategory.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(528, ''59e7bdf7d60e9ead882b7b1d188a4f37'', ''./application/admin/model/store/StoreCouponUser.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(529, ''d12d43b34d3177d838c02d0d0bcbe429'', ''./application/admin/model/store/StoreService.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(530, ''27f537286d81696b6f0ecea6ffb00de3'', ''./application/admin/model/store/StoreProductAttrValue.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(531, ''884acba55d0b1ebc5c30b5e67ae85e63'', ''./application/admin/model/store/StoreProductRelation.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(532, ''f941d3b71d221b869f556c08471ded74'', ''./application/admin/model/store/StoreProductReply.php'', ''1536956973'', ''1536288162'', ''1536288162''),
(533, ''680ada3f10bcfab9950118b673c7392e'', ''./application/admin/model/store/StoreProductAttrResult.php'', ''1536956974'', ''1536288162'', ''1536288162''),
(534, ''4af5b68c52c5ecfe8182484acec73623'', ''./application/admin/model/store/StoreVisit.php'', ''1536956974'', ''1536288162'', ''1536288162''),
(535, ''c772186ada46e2f512d1215e44c877af'', ''./application/admin/model/store/StoreProduct.php'', ''1536956974'', ''1536573879'', ''1536573879''),
(536, ''3d16b102c70250229f0d938ef0cb811c'', ''./application/admin/model/store/StoreBargain.php'', ''1536956974'', ''1536288162'', ''1536288162''),
(537, ''06572b3ff9076967fb6a075bf018e2d3'', ''./application/admin/model/routine/RoutineTemplate.php'', ''1536956975'', ''1536288162'', ''1536288162''),
(538, ''db8ef4253cff82a6edf762df2b4f1fa7'', ''./application/admin/model/system/SystemFile.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(539, ''b19f6084fa439ce28b95d50898708ff8'', ''./application/admin/model/system/SystemAdmin.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(540, ''bfc6526a19ff531475d11a40158d1ca1'', ''./application/admin/model/system/SystemGroupData.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(541, ''78a338c5fddb510fb1148b2a782ecbe7'', ''./application/admin/model/system/SystemConfig.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(542, ''e9ae59f5d6feff16ccdfc7d5a772df43'', ''./application/admin/model/system/SystemRole.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(543, ''8dd787c8341cbc5e632606b4c73f044c'', ''./application/admin/model/system/SystemConfigTab.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(544, ''bd891dd9361e9726fa4861dd46b70a86'', ''./application/admin/model/system/SystemGroup.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(545, ''cb3f84fd85e53882dbe1eaab6b800a07'', ''./application/admin/model/system/SystemAttachmentCategory.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(546, ''da870dfba2783e81eaba7d5c9b8181cf'', ''./application/admin/model/system/SystemAttachment.php'', ''1536956976'', ''1536288162'', ''1536288162''),
(547, ''3e0008d697048eedd39f7730e724974e'', ''./application/admin/model/system/SystemMenus.php'', ''1536956977'', ''1536288162'', ''1536288162''),
(548, ''88be9548ce18976a55cefbf5f3e525a7'', ''./application/admin/model/system/SystemLog.php'', ''1536956977'', ''1536288162'', ''1536288162''),
(549, ''c8b1d7fc8519d04d18a73f35050b4f4a'', ''./application/admin/model/system/SystemNotice.php'', ''1536956977'', ''1536288162'', ''1536288162''),
(550, ''93c056f4d87b1d38bd3a9912689514cd'', ''./application/admin/model/article/Article.php'', ''1536956977'', ''1536288162'', ''1536288162''),
(551, ''fa8cdf1cc2e81435f91d32d65064c7f6'', ''./application/admin/model/article/ArticleCategory.php'', ''1536956978'', ''1536288162'', ''1536288162''),
(552, ''925f2af58ddc0e15f176894e007b48d6'', ''./application/admin/model/finance/FinanceModel.php'', ''1536956982'', ''1536288162'', ''1536288162''),
(553, ''a664fc1af1f62f456e14cd46b7cd8f97'', ''./application/admin/view/widget/images.php'', ''1536957000'', ''1536572155'', ''1536572155''),
(554, ''f26ad5248b6faf233ff16ef315e5cd50'', ''./application/admin/view/widget/icon.php'', ''1536957000'', ''1536572368'', ''1536572368''),
(555, ''cb207c835af5a44835191162dadd76bc'', ''./application/admin/view/order/store_order/order_status.php'', ''1536957001'', ''1536288162'', ''1536288162''),
(556, ''335a661d32750f2326e43d3a3bfd9fda'', ''./application/admin/view/order/store_order/orderchart.php'', ''1536957001'', ''1536288162'', ''1536288162''),
(557, ''766f4e75c2b6540503629acf3b02dcd9'', ''./application/admin/view/order/store_order/order_info.php'', ''1536957001'', ''1536288162'', ''1536288162''),
(558, ''8c43a76ba6cf9fd9150b445fefd1eec6'', ''./application/admin/view/order/store_order/express.php'', ''1536957001'', ''1536288162'', ''1536288162''),
(559, ''98c803288b04588d7fbe8e46944d790f'', ''./application/admin/view/order/store_order/index.php'', ''1536957001'', ''1536288162'', ''1536288162''),
(560, ''cb207c835af5a44835191162dadd76bc'', ''./application/admin/view/order/store_order_pink/order_status.php'', ''1536957002'', ''1536288162'', ''1536288162''),
(561, ''1c416ee41183f76e88cc3cd94b32a352'', ''./application/admin/view/index/main.php'', ''1536957003'', ''1536288162'', ''1536288162''),
(562, ''4b5df335d47e62f510b61b2c5de3c51c'', ''./application/admin/view/index/index.php'', ''1536957004'', ''1536300541'', ''1536300541''),
(563, ''ce030182d92848c2d888a05e16246bf7'', ''./application/admin/view/agent/agent_manage/stair.php'', ''1536957004'', ''1536288162'', ''1536288162''),
(564, ''b8a7a08d0bf739124158bb0638f2d4d2'', ''./application/admin/view/agent/agent_manage/index.php'', ''1536957004'', ''1536288162'', ''1536288162''),
(565, ''c32be0ba8f9eb4915d6e112382aa609a'', ''./application/admin/view/agent/agent_manage/now_money.php'', ''1536957005'', ''1536288162'', ''1536288162''),
(566, ''a664c040a6de55725de41fd8d9f94ffb'', ''./application/admin/view/wechat/wechat_template/index.php'', ''1536957006'', ''1536288162'', ''1536288162''),
(567, ''ce030182d92848c2d888a05e16246bf7'', ''./application/admin/view/wechat/wechat_user/stair.php'', ''1536957007'', ''1536288162'', ''1536288162''),
(568, ''5fee9b280690bc7bb267bbc4342f01d4'', ''./application/admin/view/wechat/wechat_user/group.php'', ''1536957007'', ''1536288162'', ''1536288162''),
(569, ''8df467607664a5f62ce00898f0460882'', ''./application/admin/view/wechat/wechat_user/tag.php'', ''1536957007'', ''1536288162'', ''1536288162''),
(570, ''5962efa558009c3af918c2b2f80c3309'', ''./application/admin/view/wechat/wechat_user/index.php'', ''1536957007'', ''1536288162'', ''1536288162''),
(571, ''c32be0ba8f9eb4915d6e112382aa609a'', ''./application/admin/view/wechat/wechat_user/now_money.php'', ''1536957007'', ''1536288162'', ''1536288162''),
(572, ''5c8eb76c8df38451fc4a8a6345fabbbc'', ''./application/admin/view/wechat/menus/index.php'', ''1536957008'', ''1536288162'', ''1536288162''),
(573, ''fe805360d6392a301c462036495cea21'', ''./application/admin/view/wechat/reply/add_keyword.php'', ''1536957009'', ''1536288162'', ''1536288162''),
(574, ''f07d48c6924ff548e2115192f120230f'', ''./application/admin/view/wechat/reply/index.php'', ''1536957009'', ''1536288162'', ''1536288162''),
(575, ''477752685ad729c7dfc43a87f77c2f1b'', ''./application/admin/view/wechat/reply/keyword.php'', ''1536957009'', ''1536288162'', ''1536288162''),
(576, ''5d12c36c19e1d92e0837596f0341dec9'', ''./application/admin/view/wechat/wechat_message/index.php'', ''1536957010'', ''1536288162'', ''1536288162''),
(577, ''95806bbb0fe4bb42c01d24764e3cf6f6'', ''./application/admin/view/wechat/wechat_news_category/append.php'', ''1536957011'', ''1536288162'', ''1536288162''),
(578, ''dcf8075991e64b5453783ad59974c61e'', ''./application/admin/view/wechat/wechat_news_category/select.php'', ''1536957011'', ''1536288162'', ''1536288162''),
(579, ''9b4586d130ce3e42f010940f685dc50e'', ''./application/admin/view/wechat/wechat_news_category/edit.php'', ''1536957011'', ''1536288162'', ''1536288162''),
(580, ''57a345e5c9a627a7f197da6fdd9fd375'', ''./application/admin/view/wechat/wechat_news_category/create.php'', ''1536957011'', ''1536288162'', ''1536288162''),
(581, ''7631dd22d2340e43f4a6ea474c7b864a'', ''./application/admin/view/wechat/wechat_news_category/send_news.php'', ''1536957011'', ''1536288162'', ''1536288162''),
(582, ''a5c34e07a6d678cce531a8d8d0c687fa'', ''./application/admin/view/wechat/wechat_news_category/index.php'', ''1536957011'', ''1536288162'', ''1536288162''),
(583, ''a63e7c2404d74cd366f55ea3449873e0'', ''./application/admin/view/wechat/store_service/chat_list.php'', ''1536957012'', ''1536288162'', ''1536288162''),
(584, ''b8c1c68c6566c2ed8f1413441792e796'', ''./application/admin/view/wechat/store_service/chat_user.php'', ''1536957012'', ''1536288162'', ''1536288162''),
(585, ''2b051c4c430b9bb8a0e2d7c9c4135767'', ''./application/admin/view/wechat/store_service/edit.php'', ''1536957012'', ''1536288162'', ''1536288162''),
(586, ''6e045a63a8eb9e408968da3bdc2bbbb7'', ''./application/admin/view/wechat/store_service/create.php'', ''1536957012'', ''1536288162'', ''1536288162''),
(587, ''1ad7cfcdbb56242f62bb62647b2812fd'', ''./application/admin/view/wechat/store_service/index.html'', ''1536957013'', ''1536288162'', ''1536288162''),
(588, ''73ccb3cbc3e0b4b59038733bb8853ec1'', ''./application/admin/view/wechat/store_service/index.php'', ''1536957013'', ''1536288162'', ''1536288162''),
(589, ''477479c8ae3807c5f4bdbbd890d4a4c0'', ''./application/admin/view/login/index.php'', ''1536957014'', ''1536288162'', ''1536288162''),
(590, ''3014e4b5c671cf0c533ce50d51a218a2'', ''./application/admin/view/user/user_recharge/index.php'', ''1536957015'', ''1536288162'', ''1536288162''),
(591, ''f9734a2e0d30f32b2516105317b8c84a'', ''./application/admin/view/user/user_extract/index.php'', ''1536957016'', ''1536288162'', ''1536288162''),
(592, ''831348633e7468dc43a97d188d98eceb'', ''./application/admin/view/user/user/user_analysis.php'', ''1536957016'', ''1536288162'', ''1536288162''),
(593, ''d66463f742460beea3417b35c1c9ba9d'', ''./application/admin/view/user/user/see.php'', ''1536957016'', ''1536288162'', ''1536288162''),
(594, ''b98e7a900d1fd6db26038420a14f4d98'', ''./application/admin/view/user/user/index.php'', ''1536957017'', ''1536748208'', ''1536748208''),
(595, ''14e02db8f07fb05d10e9e556b2353e08'', ''./application/admin/view/user/user_notice/user.php'', ''1536957017'', ''1536288162'', ''1536288162''),
(596, ''4b6b62710941e7736dc065add02614d7'', ''./application/admin/view/user/user_notice/user_create.php'', ''1536957017'', ''1536288162'', ''1536288162''),
(597, ''2b051c4c430b9bb8a0e2d7c9c4135767'', ''./application/admin/view/user/user_notice/create.php'', ''1536957018'', ''1536288162'', ''1536288162''),
(598, ''5fad0ce0e4750bdfe345c627b25445b7'', ''./application/admin/view/user/user_notice/notice.php'', ''1536957018'', ''1536288162'', ''1536288162''),
(599, ''8d40634b7e0933bd46a7f3cfd224404b'', ''./application/admin/view/user/user_notice/index.php'', ''1536957018'', ''1536288162'', ''1536288162''),
(600, ''f3d45ffe48a0393aaab2cc5d5301128a'', ''./application/admin/view/record/store_statistics/index.php'', ''1536957019'', ''1536288162'', ''1536288162''),
(601, ''d091167425ec8f446e4c5e8d912fa76d'', ''./application/admin/view/record/record/ranking_point.php'', ''1536957020'', ''1536288162'', ''1536288162''),
(602, ''33e8b16438fcb3096af919d3e66d07df'', ''./application/admin/view/record/record/user_distribution_chart.php'', ''1536957020'', ''1536288162'', ''1536288162''),
(603, ''d21c7d17828aecd7b49ee26686ec5521'', ''./application/admin/view/record/record/chart_order.php'', ''1536957020'', ''1536288162'', ''1536288162''),
(604, ''5ad56960dfeec445d7dd19053f0245ec'', ''./application/admin/view/record/record/chart_seckill.php'', ''1536957020'', ''1536288162'', ''1536288162''),
(605, ''e7814a9121ef3a8ed2fe20550e1b7cd2'', ''./application/admin/view/record/record/user_attr.php'', ''1536957020'', ''1536288162'', ''1536288162''),
(606, ''895f964d3291eb4bcc9a374a21b58e21'', ''./application/admin/view/record/record/product_info.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(607, ''ac191fb969cedb047c6c0d0206483ea9'', ''./application/admin/view/record/record/ranking_saleslists.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(608, ''a2616902de6938c8e35266a5920dd369'', ''./application/admin/view/record/record/chart_product.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(609, ''3d79f3af091c09405b7ee6b91f006e94'', ''./application/admin/view/record/record/user_chart.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(610, ''d43bbe293f21be1b2f81ab543a05810a'', ''./application/admin/view/record/record/chart_rebate.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(611, ''58147338b79e1016ba112df135d3aedb'', ''./application/admin/view/record/record/chart_score.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(612, ''82c8cdb1171bd378e2b37dd6aebbf260'', ''./application/admin/view/record/record/chart_bargain.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(613, ''595dc43f13dd8f74c47e29115099ebac'', ''./application/admin/view/record/record/chart_cash.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(614, ''dd3d319e4d1d90228d38f7bff767ed52'', ''./application/admin/view/record/record/chart_combination.php'', ''1536957021'', ''1536288162'', ''1536288162''),
(615, ''456e531d6cd00c59745bbf31b220534a'', ''./application/admin/view/record/record/chart_coupon.php'', ''1536957022'', ''1536288162'', ''1536288162''),
(616, ''2bef559a6556e291f41a5ac6d2e25871'', ''./application/admin/view/record/record/ranking_commission.php'', ''1536957022'', ''1536288162'', ''1536288162''),
(617, ''7b87526de740ca7d551b98c8a50ff59c'', ''./application/admin/view/record/record/chart_recharge.php'', ''1536957022'', ''1536288162'', ''1536288162''),
(618, ''cd9acb4f0608569569097e3ded026a16'', ''./application/admin/view/record/record/user_business_chart.php'', ''1536957022'', ''1536288162'', ''1536288162''),
(619, ''75b15c251272b8536859456d31544526'', ''./application/admin/view/setting/system_menus/edit_content.php'', ''1536957023'', ''1536288162'', ''1536288162''),
(620, ''89e30648779670a248ad1656aafb3e6d'', ''./application/admin/view/setting/system_menus/index.php'', ''1536957023'', ''1536288162'', ''1536288162''),
(621, ''d51d0a337566e65e510ec76f1985bba8'', ''./application/admin/view/setting/system_config_tab/create_base.php'', ''1536957024'', ''1536288162'', ''1536288162''),
(622, ''9191b529705ba8fc2b59aba994ae9a1c'', ''./application/admin/view/setting/system_config_tab/edit.php'', ''1536957024'', ''1536288162'', ''1536288162''),
(623, ''0369972eb75cfc652b4101c12313754b'', ''./application/admin/view/setting/system_config_tab/create.php'', ''1536957024'', ''1536288162'', ''1536288162''),
(624, ''5c8d163c80cbfe25d19e458163351c66'', ''./application/admin/view/setting/system_config_tab/index.php'', ''1536957025'', ''1536288162'', ''1536288162''),
(625, ''caa0cb64b79bb426b1940e57352e64a7'', ''./application/admin/view/setting/system_config_tab/edit_cinfig.php'', ''1536957027'', ''1536288162'', ''1536288162''),
(626, ''063bacded0c112904b23d33bfd069d7f'', ''./application/admin/view/setting/system_config_tab/sonconfigtab.php'', ''1536957027'', ''1536288162'', ''1536288162''),
(627, ''2b051c4c430b9bb8a0e2d7c9c4135767'', ''./application/admin/view/setting/system_group_data/edit.php'', ''1536957029'', ''1536288162'', ''1536288162''),
(628, ''2b051c4c430b9bb8a0e2d7c9c4135767'', ''./application/admin/view/setting/system_group_data/create.php'', ''1536957030'', ''1536288162'', ''1536288162''),
(629, ''28ff23f03da1c00b38e347b62c60679a'', ''./application/admin/view/setting/system_group_data/index.php'', ''1536957030'', ''1536288162'', ''1536288162''),
(630, ''34a21b8ac7ca9778633a1b0f47bb6aeb'', ''./application/admin/view/setting/system_group/create.php'', ''1536957030'', ''1536288162'', ''1536288162''),
(631, ''daef4e5394af5d332d32274a412c2303'', ''./application/admin/view/setting/system_group/index.php'', ''1536957031'', ''1536288162'', ''1536288162''),
(632, ''ad977c490eea2422623d78facd0ed060'', ''./application/admin/view/setting/system_notice/message.php'', ''1536957031'', ''1536288162'', ''1536288162''),
(633, ''e6217329a24f1c615b2f68cc53b74d75'', ''./application/admin/view/setting/system_notice/index.php'', ''1536957032'', ''1536288162'', ''1536288162''),
(634, ''478db91e0899f14e80db7eb18536ea11'', ''./application/admin/view/setting/system_admin/admininfo.php'', ''1536957032'', ''1536288162'', ''1536288162''),
(635, ''7548c98d4700cbb552143a6e1bdd6926'', ''./application/admin/view/setting/system_admin/edit.php'', ''1536957032'', ''1536288162'', ''1536288162''),
(636, ''4c7d8112a736f846aeefa9e8322da0d7'', ''./application/admin/view/setting/system_admin/create.php'', ''1536957033'', ''1536288162'', ''1536288162''),
(637, ''b4e65d762c80221ce3db3afabc568e2d'', ''./application/admin/view/setting/system_admin/index.php'', ''1536957033'', ''1536288162'', ''1536288162''),
(638, ''5571f7c404b0514343e1ee62b60c0ee0'', ''./application/admin/view/setting/system_role/edit.php'', ''1536957033'', ''1536288162'', ''1536288162''),
(639, ''c1067200b7e9a027605673e02305676d'', ''./application/admin/view/setting/system_role/create.php'', ''1536957034'', ''1536288162'', ''1536288162''),
(640, ''42ac5c415d2d28564adef4137ac6ca94'', ''./application/admin/view/setting/system_role/index.php'', ''1536957034'', ''1536288162'', ''1536288162''),
(641, ''c51673f4a8da758a6eadc2e634a1c955'', ''./application/admin/view/setting/system_config/create.php'', ''1536957035'', ''1536288162'', ''1536288162''),
(642, ''6884fad035786d6101dd38937d53eb88'', ''./application/admin/view/setting/system_config/index_alone.php'', ''1536957035'', ''1536288162'', ''1536288162''),
(643, ''11b67fd126602f0375086d6fe2963224'', ''./application/admin/view/setting/system_config/index.php'', ''1536957035'', ''1536288162'', ''1536288162''),
(644, ''99a4104ec8ca4ce59ebe0c1e7967a9e3'', ''./application/admin/view/ump/store_bargain/index.php'', ''1536957036'', ''1536288162'', ''1536288162''),
(645, ''709cb4f604c7c2fc8748c82d7b1c5f7a'', ''./application/admin/view/ump/user_point/index.php'', ''1536957037'', ''1536288162'', ''1536288162''),
(646, ''42c130e43c3040ad128eb1321acf7a0d'', ''./application/admin/view/ump/store_coupon_user/index.php'', ''1536957038'', ''1536288162'', ''1536288162''),
(647, ''e01e1326f226cb09cc3c13ca309cca6c'', ''./application/admin/view/ump/store_coupon/grant_group.php'', ''1536957039'', ''1536288162'', ''1536288162''),
(648, ''9530115564cb68bc2865334d36d98fdf'', ''./application/admin/view/ump/store_coupon/grant.php'', ''1536957039'', ''1536288162'', ''1536288162''),
(649, ''7546553efddbc4762c7558a57a5b587f'', ''./application/admin/view/ump/store_coupon/grant_subscribe.php'', ''1536957039'', ''1536288162'', ''1536288162''),
(650, ''13cd859df03212c9016ae2fd5ade62bb'', ''./application/admin/view/ump/store_coupon/grant_all.php'', ''1536957039'', ''1536288162'', ''1536288162''),
(651, ''6c65cf72de5d67e44e76ed9a6d06c7d8'', ''./application/admin/view/ump/store_coupon/grant_tag.php'', ''1536957039'', ''1536288162'', ''1536288162''),
(652, ''efb83bf16aa54d71e1c6b3d1811491c3'', ''./application/admin/view/ump/store_coupon/index.php'', ''1536957039'', ''1536288162'', ''1536288162''),
(653, ''78bb76c017941c89d618ed8146bc2df4'', ''./application/admin/view/ump/store_seckill/index.php'', ''1536957040'', ''1536288162'', ''1536288162''),
(654, ''51eb7c96192d57ee86a0f3728dad275e'', ''./application/admin/view/ump/store_combination/order_pink.php'', ''1536957041'', ''1536288162'', ''1536288162''),
(655, ''f4b9b71d36c47765f163e10916c223a8'', ''./application/admin/view/ump/store_combination/combina_list.php'', ''1536957041'', ''1536288162'', ''1536288162''),
(656, ''857f3cce5849f255267c22bb7b4643a3'', ''./application/admin/view/ump/store_combination/attr.php'', ''1536957041'', ''1536288162'', ''1536288162''),
(657, ''365a8e644ca073b716bb95d3a4c2db19'', ''./application/admin/view/ump/store_combination/index.php'', ''1536957041'', ''1536288162'', ''1536288162''),
(658, ''0830d6bfb0cc4feda498e08629c15e1b'', ''./application/admin/view/ump/store_coupon_issue/issue_log.php'', ''1536957042'', ''1536288162'', ''1536288162''),
(659, ''24b32bcf733e1d64ebf86fd1798f5cdc'', ''./application/admin/view/ump/store_coupon_issue/index.php'', ''1536957042'', ''1536288162'', ''1536288162''),
(660, ''ac44d9f8699b53bdef5b14db121eadd1'', ''./application/admin/view/store/store_bargain/index.php'', ''1536957044'', ''1536288162'', ''1536288162''),
(661, ''3b5098b7f0ef7509de278742209e5300'', ''./application/admin/view/store/store_category/index.php'', ''1536957044'', ''1536288162'', ''1536288162''),
(662, ''d2caad9dae5ad5b45a60e7646ac1f2ef'', ''./application/admin/view/store/store_coupon_user/index.php'', ''1536957045'', ''1536288162'', ''1536288162''),
(663, ''f3d45ffe48a0393aaab2cc5d5301128a'', ''./application/admin/view/store/store_statistics/index.php'', ''1536957046'', ''1536288162'', ''1536288162''),
(664, ''cb207c835af5a44835191162dadd76bc'', ''./application/admin/view/store/store_order/order_status.php'', ''1536957047'', ''1536288162'', ''1536288162''),
(665, ''335a661d32750f2326e43d3a3bfd9fda'', ''./application/admin/view/store/store_order/orderchart.php'', ''1536957047'', ''1536288162'', ''1536288162''),
(666, ''766f4e75c2b6540503629acf3b02dcd9'', ''./application/admin/view/store/store_order/order_info.php'', ''1536957047'', ''1536288162'', ''1536288162''),
(667, ''8c43a76ba6cf9fd9150b445fefd1eec6'', ''./application/admin/view/store/store_order/express.php'', ''1536957047'', ''1536288162'', ''1536288162''),
(668, ''2d2b731de611264ee4b58acd171c5f16'', ''./application/admin/view/store/store_order/index.php'', ''1536957047'', ''1536288162'', ''1536288162''),
(669, ''e01e1326f226cb09cc3c13ca309cca6c'', ''./application/admin/view/store/store_coupon/grant_group.php'', ''1536957048'', ''1536288162'', ''1536288162''),
(670, ''9530115564cb68bc2865334d36d98fdf'', ''./application/admin/view/store/store_coupon/grant.php'', ''1536957048'', ''1536288162'', ''1536288162''),
(671, ''7546553efddbc4762c7558a57a5b587f'', ''./application/admin/view/store/store_coupon/grant_subscribe.php'', ''1536957048'', ''1536288162'', ''1536288162''),
(672, ''13cd859df03212c9016ae2fd5ade62bb'', ''./application/admin/view/store/store_coupon/grant_all.php'', ''1536957048'', ''1536288162'', ''1536288162''),
(673, ''6c65cf72de5d67e44e76ed9a6d06c7d8'', ''./application/admin/view/store/store_coupon/grant_tag.php'', ''1536957048'', ''1536288162'', ''1536288162''),
(674, ''d3984ed6f80788fa1878b7b1e6b2f0fc'', ''./application/admin/view/store/store_coupon/index.php'', ''1536957048'', ''1536288162'', ''1536288162''),
(675, ''857f3cce5849f255267c22bb7b4643a3'', ''./application/admin/view/store/store_seckill/attr.php'', ''1536957049'', ''1536288162'', ''1536288162''),
(676, ''b08607c0cbd748c9c509e23299dd85ce'', ''./application/admin/view/store/store_seckill/index.php'', ''1536957049'', ''1536288162'', ''1536288162''),
(677, ''32d6051cea9859d5f5190b1970553b37'', ''./application/admin/view/store/store_product/statistics.php'', ''1536957050'', ''1536288162'', ''1536288162''),
(678, ''d4d34e81d754ac47a0d8b5ee11b4f419'', ''./application/admin/view/store/store_product/like.php'', ''1536957050'', ''1536288162'', ''1536288162''),
(679, ''f694a5f28298a06b96221f4c28a13291'', ''./application/admin/view/store/store_product/collect.php'', ''1536957050'', ''1536288162'', ''1536288162''),
(680, ''890761ff4b5f50da42c0ba83e018d5bb'', ''./application/admin/view/store/store_product/attr.php'', ''1536957051'', ''1536288162'', ''1536288162''),
(681, ''60ec2d64d646c2768cf003cf8cdb71f4'', ''./application/admin/view/store/store_product/index.php'', ''1536957051'', ''1536573879'', ''1536573879''),
(682, ''857f3cce5849f255267c22bb7b4643a3'', ''./application/admin/view/store/store_combination/attr.php'', ''1536957051'', ''1536288162'', ''1536288162''),
(683, ''e122df0c2336d7e601f12e350cd286a6'', ''./application/admin/view/store/store_combination/index.php'', ''1536957052'', ''1536288162'', ''1536288162'');
INSERT INTO `eb_system_file` (`id`, `cthash`, `filename`, `atime`, `mtime`, `ctime`) VALUES
(684, ''aac99d2e96ff1db76bff40e384d0a40e'', ''./application/admin/view/store/store_product_reply/index.php'', ''1536957052'', ''1536288162'', ''1536288162''),
(685, ''0830d6bfb0cc4feda498e08629c15e1b'', ''./application/admin/view/store/store_coupon_issue/issue_log.php'', ''1536957053'', ''1536288162'', ''1536288162''),
(686, ''5fe7598390265aa1ab7639c1fe9e117c'', ''./application/admin/view/store/store_coupon_issue/index.php'', ''1536957053'', ''1536288162'', ''1536288162''),
(687, ''a63e7c2404d74cd366f55ea3449873e0'', ''./application/admin/view/store/store_service/chat_list.php'', ''1536957054'', ''1536288162'', ''1536288162''),
(688, ''b8c1c68c6566c2ed8f1413441792e796'', ''./application/admin/view/store/store_service/chat_user.php'', ''1536957054'', ''1536288162'', ''1536288162''),
(689, ''2b051c4c430b9bb8a0e2d7c9c4135767'', ''./application/admin/view/store/store_service/edit.php'', ''1536957054'', ''1536288162'', ''1536288162''),
(690, ''6e045a63a8eb9e408968da3bdc2bbbb7'', ''./application/admin/view/store/store_service/create.php'', ''1536957054'', ''1536288162'', ''1536288162''),
(691, ''1ad7cfcdbb56242f62bb62647b2812fd'', ''./application/admin/view/store/store_service/index.html'', ''1536957055'', ''1536288162'', ''1536288162''),
(692, ''73ccb3cbc3e0b4b59038733bb8853ec1'', ''./application/admin/view/store/store_service/index.php'', ''1536957055'', ''1536288162'', ''1536288162''),
(693, ''d069eaa2df23e73f6ca6dc40cfdf6e3d'', ''./application/admin/view/store/store_order_pink/order_pink.php'', ''1536957055'', ''1536288162'', ''1536288162''),
(694, ''cb207c835af5a44835191162dadd76bc'', ''./application/admin/view/store/store_order_pink/order_status.php'', ''1536957055'', ''1536288162'', ''1536288162''),
(695, ''f4b9b71d36c47765f163e10916c223a8'', ''./application/admin/view/store/store_order_pink/index.php'', ''1536957056'', ''1536288162'', ''1536288162''),
(696, ''8cf0e501b57ce4d3763f686372243d62'', ''./application/admin/view/routine/routine_template/index.php'', ''1536957059'', ''1536288162'', ''1536288162''),
(697, ''ffb1fac95b54df459bf7b8f7dabb3564'', ''./application/admin/view/system/clear/index.php'', ''1536957061'', ''1536288162'', ''1536288162''),
(698, ''62ac86e55b1fbaf45b430b521bd52230'', ''./application/admin/view/system/system_file/index.php'', ''1536957061'', ''1536288162'', ''1536288162''),
(699, ''f665c5f235ae689a71d96bd92ef1151d'', ''./application/admin/view/system/system_log/index.php'', ''1536957062'', ''1536288162'', ''1536288162''),
(700, ''5fb8ef30bd449e373b86fa858d67e0f3'', ''./application/admin/view/system/system_upgradeclient/index.php'', ''1536957063'', ''1536288162'', ''1536288162''),
(701, ''42526ec1d6f42ba9b6d3a520a0228728'', ''./application/admin/view/system/system_cleardata/index.php'', ''1536957064'', ''1536288162'', ''1536288162''),
(702, ''3b441ca860f5f7995e632d61f9b667dd'', ''./application/admin/view/article/article_category/index.php'', ''1536957065'', ''1536288162'', ''1536288162''),
(703, ''d54bd0ed9172935bfe700d2c1eb93c6e'', ''./application/admin/view/article/article/merchantindex.php'', ''1536957066'', ''1536288162'', ''1536288162''),
(704, ''3d33f93237fcae56e094e797dca1d4ee'', ''./application/admin/view/article/article/create.php'', ''1536957066'', ''1536288162'', ''1536288162''),
(705, ''9a318f229d38808f202d388284b14d65'', ''./application/admin/view/article/article/index.php'', ''1536957066'', ''1536288162'', ''1536288162''),
(706, ''7119a7c5448edaf86b28ce064f8ec863'', ''./application/admin/view/finance/user_recharge/index.php'', ''1536957068'', ''1536288162'', ''1536288162''),
(707, ''f9734a2e0d30f32b2516105317b8c84a'', ''./application/admin/view/finance/user_extract/index.php'', ''1536957069'', ''1536288162'', ''1536288162''),
(708, ''93ea925781aa7475f621b0b29a5cc16b'', ''./application/admin/view/finance/finance/commission_list.php'', ''1536957069'', ''1536288162'', ''1536288162''),
(709, ''dbc1bdaa008234fea511c170e9cb82ec'', ''./application/admin/view/finance/finance/bill.php'', ''1536957069'', ''1536288162'', ''1536288162''),
(710, ''d6edaacb9842aa799f872de75dc277ec'', ''./application/admin/view/finance/finance/content_info.php'', ''1536957070'', ''1536288162'', ''1536288162''),
(711, ''ec2424c5928cf40d01e2ad2a987a753c'', ''./application/admin/view/public/common_form.php'', ''1536957071'', ''1536288162'', ''1536288162''),
(712, ''88885232decc53c072c1722481e2f04e'', ''./application/admin/view/public/exception.php'', ''1536957071'', ''1536288162'', ''1536288162''),
(713, ''bca7f06aad9580512aa57822b1173c53'', ''./application/admin/view/public/inner_footer.php'', ''1536957071'', ''1536288162'', ''1536288162''),
(714, ''9d775fe520e46e5edcf938436c273516'', ''./application/admin/view/public/success.php'', ''1536957071'', ''1536744701'', ''1536744701''),
(715, ''0b0912be929d0c1fd31fb505f1503360'', ''./application/admin/view/public/container.php'', ''1536957071'', ''1536288162'', ''1536288162''),
(716, ''55a236211c39474cfb16d494116302e0'', ''./application/admin/view/public/edit_content.php'', ''1536957071'', ''1536288162'', ''1536288162''),
(717, ''c25d4e6497994dca56c6a3091eeefc82'', ''./application/admin/view/public/frame_head.php'', ''1536957072'', ''1536288162'', ''1536288162''),
(718, ''3e88985ba612f723c9dd7634c8428d70'', ''./application/admin/view/public/form-builder.php'', ''1536957072'', ''1536288162'', ''1536288162''),
(719, ''f10175ccbcd03bfa9cb27758a8441b18'', ''./application/admin/view/public/error.php'', ''1536957072'', ''1536744701'', ''1536744701''),
(720, ''b6f774b9b18f4c5f315cedf72a47e1d0'', ''./application/admin/view/public/head.php'', ''1536957072'', ''1536288162'', ''1536288162''),
(721, ''d41d8cd98f00b204e9800998ecf8427e'', ''./application/admin/view/public/frame_footer.php'', ''1537004169'', ''1536288162'', ''1536288162''),
(722, ''29858562aed26e1be0bd196d9384dc09'', ''./application/admin/view/public/inner_page.php'', ''1536957072'', ''1536288162'', ''1536288162''),
(723, ''b5dc127af353593ab33fa0025b7b7c12'', ''./application/admin/view/public/notice.php'', ''1536957072'', ''1536288162'', ''1536288162''),
(724, ''36ae891e0e0dbb8c42e9939c50a5e16c'', ''./application/admin/view/public/style.php'', ''1536957072'', ''1536288162'', ''1536288162''),
(725, ''af692c6dd83d1c87ad94e3675dd166f3'', ''./application/admin/view/public/foot.php'', ''1536957072'', ''1536288162'', ''1536288162''),
(726, ''263fdaed54380bdd89bd4f3b40c3d72a'', ''./application/admin/common.php'', ''1536957074'', ''1536288162'', ''1536288162''),
(727, ''73b1958e84f95fdb857c6120af268e73'', ''./application/admin/config.php'', ''1536957074'', ''1536288162'', ''1536288162''),
(728, ''d41d8cd98f00b204e9800998ecf8427e'', ''./application/index.html'', ''1536288162'', ''1536288162'', ''1536288162''),
(729, ''8da33d8a86d4e44da0a29d6a61b210b8'', ''./application/common.php'', ''1536957075'', ''1536288162'', ''1536288162''),
(730, ''c431683d9b27f00e28f9cd2c132752db'', ''./application/constant.php'', ''1536957075'', ''1536290598'', ''1536290598''),
(731, ''0953227b53840cf3bdd52a6224b7fa52'', ''./application/config.php'', ''1536981055'', ''1536980535'', ''1536980535''),
(732, ''255a3dc3a4317ddccbb88298611068e7'', ''./application/routine/controller/AuthController.php'', ''1536957075'', ''1536288162'', ''1536288162''),
(733, ''96e1b9546228612eb8f6a65f32b7eaef'', ''./application/routine/controller/Login.php'', ''1536957075'', ''1536288162'', ''1536288162''),
(734, ''006681504cd42df2593853d38171dd1c'', ''./application/routine/controller/AuthApi.php'', ''1536957075'', ''1536823545'', ''1536823545''),
(735, ''b750369e754a9cb6259d7417f83dd8ac'', ''./application/routine/model/user/UserSign.php'', ''1536957076'', ''1536288162'', ''1536288162''),
(736, ''10311a3e5744847a6559281db8096d9b'', ''./application/routine/model/user/UserNotice.php'', ''1536957076'', ''1536288162'', ''1536288162''),
(737, ''acdfb8a3373c40e8ea2e6a22c81efd0a'', ''./application/routine/model/user/RoutineUser.php'', ''1536957076'', ''1536288162'', ''1536288162''),
(738, ''8acb993698e5abc54838c9095d27df56'', ''./application/routine/model/user/UserBill.php'', ''1536957076'', ''1536288162'', ''1536288162''),
(739, ''e51614f9d16ff0401337402317595e91'', ''./application/routine/model/user/User.php'', ''1536957077'', ''1536541467'', ''1536541467''),
(740, ''7594246ef8c5ea9dd9a49c10a7155353'', ''./application/routine/model/user/WechatUser.php'', ''1536957077'', ''1536541467'', ''1536541467''),
(741, ''c6e4c53c9f77371147f953471f47f449'', ''./application/routine/model/user/UserExtract.php'', ''1536957077'', ''1536823545'', ''1536823545''),
(742, ''96e4cd8369a1e9fc8c826bf5ed953b79'', ''./application/routine/model/user/UserRecharge.php'', ''1536957077'', ''1536560060'', ''1536560060''),
(743, ''dfd018f6c7c9f90de4d3d607ab7acfde'', ''./application/routine/model/user/UserAddress.php'', ''1536957079'', ''1536288162'', ''1536288162''),
(744, ''266cd406c502fef7572481e7f25f52fa'', ''./application/routine/model/store/StoreProductAttr.php'', ''1536957080'', ''1536288162'', ''1536288162''),
(745, ''241bffe8be92bef5d195bd160e36c16e'', ''./application/routine/model/store/StoreServiceLog.php'', ''1536957080'', ''1536288162'', ''1536288162''),
(746, ''5ee49e1a91343bc2207643a8c8055e6b'', ''./application/routine/model/store/StoreCategory.php'', ''1536957080'', ''1536541467'', ''1536541467''),
(747, ''e468018b930cf0f26223b25837575243'', ''./application/routine/model/store/StoreOrder.php'', ''1536957080'', ''1536823545'', ''1536823545''),
(748, ''51f288145d9be47458157fd238a79948'', ''./application/routine/model/store/StoreCouponUser.php'', ''1536957080'', ''1536288162'', ''1536288162''),
(749, ''c9db0489fb401fb5f52a338c452b4eec'', ''./application/routine/model/store/StoreService.php'', ''1536957080'', ''1536288162'', ''1536288162''),
(750, ''bf30461f714d5b1420fb6bb00379b19f'', ''./application/routine/model/store/StorePink.php'', ''1536957080'', ''1536541467'', ''1536541467''),
(751, ''906968f93c35b1de1ff1b5223dc3f990'', ''./application/routine/model/store/StoreProductRelation.php'', ''1536957081'', ''1536541467'', ''1536541467''),
(752, ''6c145b1d657b89c8d4864c35df91b9fb'', ''./application/routine/model/store/StoreCart.php'', ''1536957081'', ''1536541467'', ''1536541467''),
(753, ''596c02b74d59a725d43cd0113fd92ea6'', ''./application/routine/model/store/StoreProductReply.php'', ''1536957081'', ''1536541467'', ''1536541467''),
(754, ''edad8bdc18e62d69160d681c13b184e9'', ''./application/routine/model/store/StoreOrderStatus.php'', ''1536957081'', ''1536288162'', ''1536288162''),
(755, ''c2ed7b8da6a649785764ed95c901a592'', ''./application/routine/model/store/StoreCoupon.php'', ''1536957081'', ''1536288162'', ''1536288162''),
(756, ''ba009576555bc85d5e1c7f76c6ca4978'', ''./application/routine/model/store/StoreProduct.php'', ''1536957081'', ''1536541467'', ''1536541467''),
(757, ''a520074291a62ec8557b638fa11123fc'', ''./application/routine/model/store/StoreOrderCartInfo.php'', ''1536957081'', ''1536288162'', ''1536288162''),
(758, ''207ababacf6f7e937867d1ceadf47bf5'', ''./application/routine/model/store/StoreSeckill.php'', ''1536957081'', ''1536541467'', ''1536541467''),
(759, ''ceac59fcac88c94e88ad7f1a6165d9e4'', ''./application/routine/model/store/StoreCouponIssueUser.php'', ''1536957081'', ''1536288162'', ''1536288162''),
(760, ''dc4bd4f94378208bb0410b37777a3f07'', ''./application/routine/model/store/StoreCouponIssue.php'', ''1536957082'', ''1536288162'', ''1536288162''),
(761, ''87bc919c9fad3f7ad6a8d095a3db79c1'', ''./application/routine/model/routine/RoutineTemplate.php'', ''1536957082'', ''1536288162'', ''1536288162''),
(762, ''d51880a0d41b6cab9e3cbb9e73e4a71a'', ''./application/routine/model/routine/RoutineCode.php'', ''1536957082'', ''1536288162'', ''1536288162''),
(763, ''64d55a7422de5ad2cb4f8d6969602f0c'', ''./application/routine/model/routine/RoutineFormId.php'', ''1536957083'', ''1536288162'', ''1536288162''),
(764, ''41c804e8f802774ba061b5c27ce88bcf'', ''./application/routine/model/routine/RoutineServer.php'', ''1536957083'', ''1536541467'', ''1536541467''),
(765, ''ebea0635300a398e7aca704bcad3d8a5'', ''./application/routine/model/article/Article.php'', ''1536957083'', ''1536288162'', ''1536288162''),
(766, ''a6a8cebca7c32a324dbda2b0c1feb86a'', ''./application/routine/model/article/ArticleCategory.php'', ''1536957084'', ''1536288162'', ''1536288162''),
(767, ''8c795599b04209e262e7b21d2683ccc1'', ''./application/routine/view/crmebN/font/font.wxss'', ''1536957085'', ''1536288162'', ''1536288162''),
(768, ''3465b90fad126098b1b51a8590535755'', ''./application/routine/view/crmebN/app.json'', ''1536957086'', ''1536749677'', ''1536749677''),
(769, ''2681fb2a96696cfdb1ec798f40f30aeb'', ''./application/routine/view/crmebN/icon/line.jpg'', ''1536957086'', ''1536288162'', ''1536288162''),
(770, ''99dc458dd8aba81c46d8770db8d64fb8'', ''./application/routine/view/crmebN/icon/拼团图标.png'', ''1536957086'', ''1536288162'', ''1536288162''),
(771, ''9a7ebbc4f184e3ab5cf62dc8b01203a6'', ''./application/routine/view/crmebN/icon/圆角矩形-2-拷贝.png'', ''1536957086'', ''1536288162'', ''1536288162''),
(772, ''fc2ccd72581a35ef936d7dd3a6b832cc'', ''./application/routine/view/crmebN/icon/关闭.png'', ''1536957086'', ''1536288162'', ''1536288162''),
(773, ''d42648998aff2b265922c5562c6ed48e'', ''./application/routine/view/crmebN/icon/送货1.png'', ''1536957086'', ''1536288162'', ''1536288162''),
(774, ''6ab86634f72463ad70ccc31146506c1f'', ''./application/routine/view/crmebN/icon/star-icon.png'', ''1536957086'', ''1536288162'', ''1536288162''),
(775, ''43b5e71981d9bc0fdbbee1f85bbe58ab'', ''./application/routine/view/crmebN/icon/图层-1.png'', ''1536957086'', ''1536288162'', ''1536288162''),
(776, ''97501fbf327c9a4b8d1f17c15aef5d0b'', ''./application/routine/view/crmebN/icon/图层-22.png'', ''1536957087'', ''1536288162'', ''1536288162''),
(777, ''61d27727328e247e48311c052f3c2776'', ''./application/routine/view/crmebN/icon/形状-2-拷贝-3.png'', ''1536957087'', ''1536288162'', ''1536288162''),
(778, ''82e09aafe1af38b2b1ec5aa3d31d8e9c'', ''./application/routine/view/crmebN/icon/形状-2-拷贝.png'', ''1536957087'', ''1536288162'', ''1536288162''),
(779, ''ee099afa057df75f73296ed18994507f'', ''./application/routine/view/crmebN/icon/圆角矩形-1.png'', ''1536957087'', ''1536288162'', ''1536288162''),
(780, ''10aeb75a7946aec162aa33b12db6c69d'', ''./application/routine/view/crmebN/wxParse/wxParse.js'', ''1536957088'', ''1536288162'', ''1536288162''),
(781, ''5ff599fc7f294d102664b787f64d9714'', ''./application/routine/view/crmebN/wxParse/wxParse.wxml'', ''1536957088'', ''1536288162'', ''1536288162''),
(782, ''d87382882b090a6ccc3c0127e2762204'', ''./application/routine/view/crmebN/wxParse/showdown.js'', ''1536957088'', ''1536288162'', ''1536288162''),
(783, ''3f26a52ecf93f82e38023895df587938'', ''./application/routine/view/crmebN/wxParse/html2json.js'', ''1536957088'', ''1536288162'', ''1536288162''),
(784, ''cc5a7967be3390391dceb2ee08cc7496'', ''./application/routine/view/crmebN/wxParse/htmlparser.js'', ''1536957088'', ''1536288162'', ''1536288162''),
(785, ''8b25bd23ae3b405effb87bd3b420e238'', ''./application/routine/view/crmebN/wxParse/wxParse.wxss'', ''1536957088'', ''1536288162'', ''1536288162''),
(786, ''7d270acab7a0b8e628f2b02421416db1'', ''./application/routine/view/crmebN/wxParse/wxDiscode.js'', ''1536957088'', ''1536288162'', ''1536288162''),
(787, ''8181cbaaa8a71a3f1113ef8f961ac30e'', ''./application/routine/view/crmebN/images/unknown.png'', ''1536957089'', ''1536288162'', ''1536288162''),
(788, ''8a591b8d3e57797e455c4cf0b9e70d92'', ''./application/routine/view/crmebN/images/dsh.png'', ''1536957089'', ''1536288162'', ''1536288162''),
(789, ''ea21abcc5fcb863616140df20b6a50bc'', ''./application/routine/view/crmebN/images/3-2.png'', ''1536957089'', ''1536288162'', ''1536288162''),
(790, ''c7e3514c8889faf0b5de8ce412bf476f'', ''./application/routine/view/crmebN/images/3-1.png'', ''1536957089'', ''1536288162'', ''1536288162''),
(791, ''a609047b0f229fac3a05136a817d77ab'', ''./application/routine/view/crmebN/images/nav-04.png'', ''1536957089'', ''1536288162'', ''1536288162''),
(792, ''e4351d9c925ae7fab654b6b074e76e79'', ''./application/routine/view/crmebN/images/dfh.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(793, ''ba1d926e61cac9fb56dbaa1445c48b7a'', ''./application/routine/view/crmebN/images/error-icon.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(794, ''93b7af8f352513af978211a7a9303fc5'', ''./application/routine/view/crmebN/images/home.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(795, ''901e0c922aae4cd7c526508ade5d5f10'', ''./application/routine/view/crmebN/images/2-2.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(796, ''56920b06850e2ae96636f4510b98e972'', ''./application/routine/view/crmebN/images/nav-05.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(797, ''a808aa04b239d6184db0e0e2eb160f57'', ''./application/routine/view/crmebN/images/lie.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(798, ''4bd0a492a218ad2c967e49557eb57d15'', ''./application/routine/view/crmebN/images/nav-02.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(799, ''b9db4ba6c3ab2088a95f58b98e67e431'', ''./application/routine/view/crmebN/images/1-1.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(800, ''aec8beef040c111db95a7698954ad4f7'', ''./application/routine/view/crmebN/images/nav-01.png'', ''1536957090'', ''1536288162'', ''1536288162''),
(801, ''b666f0b2f755f4ffc57b5f7c89f00271'', ''./application/routine/view/crmebN/images/nav-03.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(802, ''b8d1ba7d97d61e7ad5c143872ac1391c'', ''./application/routine/view/crmebN/images/4-2.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(803, ''fe0a331bcfd9743d6f1cd3dfaa589396'', ''./application/routine/view/crmebN/images/4-1.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(804, ''2dbf9c4351f5f59b36e50e1db139e1fb'', ''./application/routine/view/crmebN/images/dpj.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(805, ''e1fabf362af42dfb78c2c19f10610581'', ''./application/routine/view/crmebN/images/dfk.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(806, ''5bea75f5e8f8e01fa6645d12fe29ed91'', ''./application/routine/view/crmebN/images/collect-shixiao.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(807, ''0da2178445c8831d1d9c7563e0f401a1'', ''./application/routine/view/crmebN/images/1-2.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(808, ''0131c6dbc9aab046acbda33bba53a3c5'', ''./application/routine/view/crmebN/images/2-1.png'', ''1536957091'', ''1536288162'', ''1536288162''),
(809, ''021253a7c2afa2c06c888a2d953cea98'', ''./application/routine/view/crmebN/images/long.gif'', ''1536957091'', ''1536288162'', ''1536288162''),
(810, ''55da32e9fa000df9a838c66dd1f456f5'', ''./application/routine/view/crmebN/project.config.json'', ''1536957092'', ''1536749677'', ''1536749677''),
(811, ''10b7a4d214922342c90a9f060a028253'', ''./application/routine/view/crmebN/config.json'', ''1536957092'', ''1536288162'', ''1536288162''),
(812, ''1cbf5b3830f9feccd22df7fd3720533e'', ''./application/routine/view/crmebN/app.js'', ''1536957092'', ''1536749677'', ''1536749677''),
(813, ''22ffc775004af9cdeb7f46dc5800600d'', ''./application/routine/view/crmebN/pages/new-con/new-con.json'', ''1536957092'', ''1536288162'', ''1536288162''),
(814, ''2e1109647b709fac7dc1d6e84a1c7520'', ''./application/routine/view/crmebN/pages/new-con/new-con.js'', ''1536957093'', ''1536288162'', ''1536288162''),
(815, ''d6b07fe162492b2d5d164d5f0c552ea4'', ''./application/routine/view/crmebN/pages/new-con/new-con.wxss'', ''1536957093'', ''1536288162'', ''1536288162''),
(816, ''718df1f54c3ea08128da93d16e7a6c0a'', ''./application/routine/view/crmebN/pages/new-con/new-con.wxml'', ''1536957093'', ''1536288162'', ''1536288162''),
(817, ''6a7bf17fcf52e0743462b75795fb357f'', ''./application/routine/view/crmebN/pages/cash/cash.wxss'', ''1536957094'', ''1536288162'', ''1536288162''),
(818, ''e2200219fded222a2442568c5afded6f'', ''./application/routine/view/crmebN/pages/cash/cash.wxml'', ''1536957094'', ''1536288162'', ''1536288162''),
(819, ''31ff6427269fb7ba01bf81299d281877'', ''./application/routine/view/crmebN/pages/cash/cash.json'', ''1536957094'', ''1536288162'', ''1536288162''),
(820, ''75a1980084d431a3f621d66137e1bc63'', ''./application/routine/view/crmebN/pages/cash/cash.js'', ''1536957094'', ''1536823545'', ''1536823545''),
(821, ''b58b6e4938eb0b88c2743d928b245c68'', ''./application/routine/view/crmebN/pages/spread/spread.wxml'', ''1536957095'', ''1536288162'', ''1536288162''),
(822, ''3869d55d779b41c3f1a78d70afbf7c35'', ''./application/routine/view/crmebN/pages/spread/spread.js'', ''1536957095'', ''1536288162'', ''1536288162''),
(823, ''ea29a9792e8ae8063fdb72251488acf0'', ''./application/routine/view/crmebN/pages/spread/spread.wxss'', ''1536957095'', ''1536288162'', ''1536288162''),
(824, ''22cca7e76da440525e36cd734b9cfa3a'', ''./application/routine/view/crmebN/pages/spread/spread.json'', ''1536957095'', ''1536288162'', ''1536288162''),
(825, ''f5fd0fa25f088c41aa39c274565c3bbf'', ''./application/routine/view/crmebN/pages/join-pink/index.js'', ''1536957096'', ''1536288162'', ''1536288162''),
(826, ''497b8ce0dbf88cd50ca8d231e307997d'', ''./application/routine/view/crmebN/pages/join-pink/index.wxml'', ''1536957096'', ''1536288162'', ''1536288162''),
(827, ''9e9f8c7aa9d62748453cb1c6fd03fe4c'', ''./application/routine/view/crmebN/pages/join-pink/index.json'', ''1536957096'', ''1536288162'', ''1536288162''),
(828, ''7d4693cff0e4b8319c52b4c2b2376ef4'', ''./application/routine/view/crmebN/pages/join-pink/index.wxss'', ''1536957096'', ''1536288162'', ''1536288162''),
(829, ''5e2a87168b0da55415ecf6c98cc5a838'', ''./application/routine/view/crmebN/pages/product-con/index.js'', ''1536957097'', ''1536288162'', ''1536288162''),
(830, ''418523dae9c354fdefeff0746286bfb0'', ''./application/routine/view/crmebN/pages/product-con/index.wxml'', ''1536957097'', ''1536749677'', ''1536749677''),
(831, ''bd2e07377f272e315fb713ac480f1fa7'', ''./application/routine/view/crmebN/pages/product-con/index.json'', ''1536957097'', ''1536288162'', ''1536288162''),
(832, ''3d2ec4ca6ddbc821fcfc2e13c9bea00e'', ''./application/routine/view/crmebN/pages/product-con/index.wxss'', ''1536957097'', ''1536288162'', ''1536288162''),
(833, ''d7d2f8da1dc7135c1c21ab44e926306f'', ''./application/routine/view/crmebN/pages/mycut/mycut.wxml'', ''1536957098'', ''1536288162'', ''1536288162''),
(834, ''191f50cf9f56684cbfd69164b2eedd1e'', ''./application/routine/view/crmebN/pages/mycut/mycut.wxss'', ''1536957098'', ''1536288162'', ''1536288162''),
(835, ''e256a1642cd4d85fe40ab6f5012544cb'', ''./application/routine/view/crmebN/pages/mycut/mycut.js'', ''1536957098'', ''1536288162'', ''1536288162''),
(836, ''50fc77ad7a0365730fba4768f2fa0ce8'', ''./application/routine/view/crmebN/pages/mycut/mycut.json'', ''1536957098'', ''1536288162'', ''1536288162''),
(837, ''1c8ea7898eb56a6fb6c272190154613b'', ''./application/routine/view/crmebN/pages/load/load.wxss'', ''1536957099'', ''1536288162'', ''1536288162''),
(838, ''bbd6a6362fd61a86eed07d8127dff1da'', ''./application/routine/view/crmebN/pages/load/load.json'', ''1536957099'', ''1536288162'', ''1536288162''),
(839, ''0e0b3e0c79f3d9cd69e78761a5fb6dbf'', ''./application/routine/view/crmebN/pages/load/load.wxml'', ''1536957099'', ''1536288162'', ''1536288162''),
(840, ''60818943e60e4846eed99a87ee25875e'', ''./application/routine/view/crmebN/pages/load/load.js'', ''1536957100'', ''1536288162'', ''1536288162''),
(841, ''bcf305ebbf783db77b009bc5904dbb09'', ''./application/routine/view/crmebN/pages/news-list/news-list.js'', ''1536957100'', ''1536288162'', ''1536288162''),
(842, ''1d662a76c7628af34519f8857375a645'', ''./application/routine/view/crmebN/pages/news-list/news-list.wxml'', ''1536957100'', ''1536288162'', ''1536288162''),
(843, ''b1c781a232c1466bd2f7b8ddec92d0a3'', ''./application/routine/view/crmebN/pages/news-list/news-list.json'', ''1536957100'', ''1536288162'', ''1536288162''),
(844, ''c9f5bc65d85a4958303dae19922f8b7e'', ''./application/routine/view/crmebN/pages/news-list/news-list.wxss'', ''1536957101'', ''1536288162'', ''1536288162''),
(845, ''bb01f547c86f775c9087e18bd2090ca5'', ''./application/routine/view/crmebN/pages/coupon-status/coupon-status.wxss'', ''1536957101'', ''1536288162'', ''1536288162''),
(846, ''5437f41c028aad32f60760280f852e17'', ''./application/routine/view/crmebN/pages/coupon-status/coupon-status.js'', ''1536957102'', ''1536288162'', ''1536288162''),
(847, ''4b8b571f231568a7a2da1ac9b87db71d'', ''./application/routine/view/crmebN/pages/coupon-status/coupon-status.json'', ''1536957102'', ''1536288162'', ''1536288162''),
(848, ''62c86130d35679ba7a6199c72297b005'', ''./application/routine/view/crmebN/pages/coupon-status/coupon-status.wxml'', ''1536957102'', ''1536288162'', ''1536288162''),
(849, ''99914b932bd37a50b983c5e7c90ae93b'', ''./application/routine/view/crmebN/pages/foo-tan/foo-tan.json'', ''1536957102'', ''1536288162'', ''1536288162''),
(850, ''8032b4727c80bd6ab93a07fa3af2c963'', ''./application/routine/view/crmebN/pages/foo-tan/foo-tan.wxss'', ''1536957103'', ''1536288162'', ''1536288162''),
(851, ''acd58e0d7d84466f20139a2d46675062'', ''./application/routine/view/crmebN/pages/foo-tan/foo-tan.js'', ''1536957103'', ''1536288162'', ''1536288162''),
(852, ''328ab94b848852a2d5b961c553dd5435'', ''./application/routine/view/crmebN/pages/foo-tan/foo-tan.wxml'', ''1536957103'', ''1536288162'', ''1536288162''),
(853, ''94089d971c09c8333b5eeacacdacdb56'', ''./application/routine/view/crmebN/pages/index/index.js'', ''1536957104'', ''1536288162'', ''1536288162''),
(854, ''6f5335568fa6da6367ea619f7d2ab9c5'', ''./application/routine/view/crmebN/pages/index/index.wxml'', ''1536957104'', ''1536288162'', ''1536288162''),
(855, ''5df8a4efef6659f904436a012516fca9'', ''./application/routine/view/crmebN/pages/index/index.json'', ''1536957104'', ''1536288162'', ''1536288162''),
(856, ''28b00be2ac60349467434fcd8947025c'', ''./application/routine/view/crmebN/pages/index/index.wxss'', ''1536957104'', ''1536288162'', ''1536288162''),
(857, ''c6d1a41dc470acdc77bbfbbc463e62bd'', ''./application/routine/view/crmebN/pages/address/address.json'', ''1536957105'', ''1536288162'', ''1536288162''),
(858, ''4a0b516236b89a8effdd165d972b4661'', ''./application/routine/view/crmebN/pages/address/address.js'', ''1536957105'', ''1536288162'', ''1536288162''),
(859, ''87d36cf71c770ca5e3374e2b6e927f9c'', ''./application/routine/view/crmebN/pages/address/address.wxml'', ''1536957105'', ''1536288162'', ''1536288162''),
(860, ''337aa2114e72e662f84a1b6e239cd15a'', ''./application/routine/view/crmebN/pages/address/address.wxss'', ''1536957105'', ''1536288162'', ''1536288162''),
(861, ''258262e3a51d2470301d6d25d323262c'', ''./application/routine/view/crmebN/pages/collect/collect.wxml'', ''1536957106'', ''1536288162'', ''1536288162''),
(862, ''45587d338c043be32601a9f340630ee4'', ''./application/routine/view/crmebN/pages/collect/collect.js'', ''1536957106'', ''1536288162'', ''1536288162''),
(863, ''912352f51871ff739fea5f9aa531b399'', ''./application/routine/view/crmebN/pages/collect/collect.json'', ''1536957106'', ''1536288162'', ''1536288162''),
(864, ''073836fd44a85132d56b58fcca279428'', ''./application/routine/view/crmebN/pages/collect/collect.wxss'', ''1536957106'', ''1536288162'', ''1536288162''),
(865, ''2bd779d80cd9592bb878bf4cf025ae25'', ''./application/routine/view/crmebN/pages/cut-one/cut-one.wxss'', ''1536957107'', ''1536288162'', ''1536288162''),
(866, ''8c87094265357b434bba7019a65f694d'', ''./application/routine/view/crmebN/pages/cut-one/cut-one.json'', ''1536957107'', ''1536288162'', ''1536288162''),
(867, ''4aa500b8f101229f155df9dd20f6a166'', ''./application/routine/view/crmebN/pages/cut-one/cut-one.js'', ''1536957107'', ''1536288162'', ''1536288162''),
(868, ''0c1cb761ad92417ea0b1c1e2e53d55c7'', ''./application/routine/view/crmebN/pages/cut-one/cut-one.wxml'', ''1536957107'', ''1536288162'', ''1536288162''),
(869, ''edc5ec14413bf4c6caee177a68983694'', ''./application/routine/view/crmebN/pages/new-list/new-list.js'', ''1536957108'', ''1536288162'', ''1536288162''),
(870, ''ae37540dcad5fa2547e836803ce3b747'', ''./application/routine/view/crmebN/pages/new-list/new-list.json'', ''1536957108'', ''1536288162'', ''1536288162''),
(871, ''3343c4f6867a76ec370cbf568e326b5d'', ''./application/routine/view/crmebN/pages/new-list/new-list.wxml'', ''1536957108'', ''1536288162'', ''1536288162''),
(872, ''2d681957f833494c1fa64922bafe9ab2'', ''./application/routine/view/crmebN/pages/new-list/new-list.wxss'', ''1536957108'', ''1536288162'', ''1536288162''),
(873, ''9bdeedee4cbca1273e34ea85571fd3cd'', ''./application/routine/view/crmebN/pages/product-countdown/index.js'', ''1536957109'', ''1536288162'', ''1536288162''),
(874, ''5bf19374e7303bcdaceccae5595f7db8'', ''./application/routine/view/crmebN/pages/product-countdown/index.wxml'', ''1536957109'', ''1536749677'', ''1536749677''),
(875, ''72745786c4aaa0462ba42042840a4a43'', ''./application/routine/view/crmebN/pages/product-countdown/index.json'', ''1536957109'', ''1536288162'', ''1536288162''),
(876, ''43cda2c8d17ade4c302cfda600b57a0d'', ''./application/routine/view/crmebN/pages/product-countdown/index.wxss'', ''1536957110'', ''1536749677'', ''1536749677''),
(877, ''ee87d1b7f58b9ec862436ea851d4ae78'', ''./application/routine/view/crmebN/pages/refund-order/refund-order.js'', ''1536957110'', ''1536749677'', ''1536749677''),
(878, ''963a731b0e2cd069e06a1f7598101010'', ''./application/routine/view/crmebN/pages/refund-order/refund-order.wxml'', ''1536957110'', ''1536288162'', ''1536288162''),
(879, ''5fc0d81c58e9e68538f6b11cc59df7a6'', ''./application/routine/view/crmebN/pages/refund-order/refund-order.wxss'', ''1536957110'', ''1536288162'', ''1536288162''),
(880, ''a35a49da4a76e6d205e1001a2877dac9'', ''./application/routine/view/crmebN/pages/refund-order/refund-order.json'', ''1536957111'', ''1536288162'', ''1536288162''),
(881, ''d75cd2b96a2a825eb43c8e06d8ea3054'', ''./application/routine/view/crmebN/pages/miao-list/miao-list.js'', ''1536957111'', ''1536288162'', ''1536288162''),
(882, ''17b4c70a7acec5b50dae5c08a1c6b1a1'', ''./application/routine/view/crmebN/pages/miao-list/miao-list.wxml'', ''1536957111'', ''1536288162'', ''1536288162''),
(883, ''0244e3ba2b64324ee77e33bcf921b13a'', ''./application/routine/view/crmebN/pages/miao-list/miao-list.wxss'', ''1536957111'', ''1536288162'', ''1536288162''),
(884, ''902ecfb1d1e70d195544195879047958'', ''./application/routine/view/crmebN/pages/miao-list/miao-list.json'', ''1536957112'', ''1536288162'', ''1536288162''),
(885, ''f01fd0eaf35e5c707d5c341eae534d9c'', ''./application/routine/view/crmebN/pages/extension/extension.wxss'', ''1536957112'', ''1536288162'', ''1536288162''),
(886, ''b6b22c131e2f561d46e3d0f4ef7e7324'', ''./application/routine/view/crmebN/pages/extension/extension.js'', ''1536957113'', ''1536288162'', ''1536288162''),
(887, ''e740a61dcddd4ac763a1721e65ea9a28'', ''./application/routine/view/crmebN/pages/extension/extension.json'', ''1536957113'', ''1536288162'', ''1536288162''),
(888, ''5866a3964d2dd3af92a34f583c036dfc'', ''./application/routine/view/crmebN/pages/extension/extension.wxml'', ''1536957113'', ''1536288162'', ''1536288162''),
(889, ''3a3237cf2fa7fff1dc1be0932f512c1c'', ''./application/routine/view/crmebN/pages/coupon/coupon.js'', ''1536957113'', ''1536288162'', ''1536288162''),
(890, ''4b8b571f231568a7a2da1ac9b87db71d'', ''./application/routine/view/crmebN/pages/coupon/coupon.json'', ''1536957114'', ''1536288162'', ''1536288162''),
(891, ''f11c15403867ffc3bf118d8a2eebf91b'', ''./application/routine/view/crmebN/pages/coupon/coupon.wxml'', ''1536957114'', ''1536288162'', ''1536288162''),
(892, ''5f83eada44d8f282a58fba1114a5bca0'', ''./application/routine/view/crmebN/pages/coupon/coupon.wxss'', ''1536957114'', ''1536288162'', ''1536288162''),
(893, ''720ccab705e2497e972e0161ce74db38'', ''./application/routine/view/crmebN/pages/buycar/buycar.js'', ''1536957115'', ''1536288162'', ''1536288162''),
(894, ''b2f4c36dea5d886eca4e36226b8e0df1'', ''./application/routine/view/crmebN/pages/buycar/buycar.wxml'', ''1536957115'', ''1536288162'', ''1536288162''),
(895, ''1f9a7a513d77c4d2972dc7d7a8209462'', ''./application/routine/view/crmebN/pages/buycar/buycar.wxss'', ''1536957115'', ''1536288162'', ''1536288162''),
(896, ''61980fb7bb4a0aa2355e8e4ebeb453d7'', ''./application/routine/view/crmebN/pages/buycar/buycar.json'', ''1536957115'', ''1536288162'', ''1536288162''),
(897, ''8d2f7221cf1765de26b0da92ef7dc6bc'', ''./application/routine/view/crmebN/pages/orders-con/orders-con.js'', ''1536957116'', ''1536749677'', ''1536749677''),
(898, ''4ac9cfafe0d4c82a8337f3946a8fe65d'', ''./application/routine/view/crmebN/pages/orders-con/orders-con.json'', ''1536957116'', ''1536288162'', ''1536288162''),
(899, ''83905b9635b925ee20ae4cb99f100fdc'', ''./application/routine/view/crmebN/pages/orders-con/orders-con.wxss'', ''1536957116'', ''1536288162'', ''1536288162''),
(900, ''f1433eb06864e9fc5c2becc79015d8ef'', ''./application/routine/view/crmebN/pages/orders-con/orders-con.wxml'', ''1536957116'', ''1536288162'', ''1536288162''),
(901, ''630b7d90da150bbd4f362ae1952cb9e1'', ''./application/routine/view/crmebN/pages/enter/enter.wxml'', ''1536957117'', ''1536288162'', ''1536288162''),
(902, ''de6a3c7e9cfe08759b0b10ae95fbfbe2'', ''./application/routine/view/crmebN/pages/enter/enter.js'', ''1536957117'', ''1536288162'', ''1536288162''),
(903, ''26d9044aae244f489e052483231e5443'', ''./application/routine/view/crmebN/pages/enter/enter.wxss'', ''1536957117'', ''1536288162'', ''1536288162''),
(904, ''7d6702e05c9f99b67d3e3f512d4d62f5'', ''./application/routine/view/crmebN/pages/enter/enter.json'', ''1536957117'', ''1536288162'', ''1536288162''),
(905, ''179fa76c2465198e59384aedb8fea948'', ''./application/routine/view/crmebN/pages/user/user.json'', ''1536957118'', ''1536288162'', ''1536288162''),
(906, ''25ec90a0cefe8b82ec4d8595d3109907'', ''./application/routine/view/crmebN/pages/user/user.js'', ''1536957118'', ''1536749677'', ''1536749677''),
(907, ''72a035ac01d9e4ba1f530f488d362b6b'', ''./application/routine/view/crmebN/pages/user/user.wxss'', ''1536957118'', ''1536288162'', ''1536288162''),
(908, ''9b689e29738447b8fd9f1f0cc4896c17'', ''./application/routine/view/crmebN/pages/user/user.wxml'', ''1536957118'', ''1536749677'', ''1536749677''),
(909, ''ace519da2f3bd600089c7d80cfe3d1f8'', ''./application/routine/view/crmebN/pages/payment/payment.wxss'', ''1536957119'', ''1536288162'', ''1536288162''),
(910, ''dbb33fb34dae15db13d4fd4e405842f9'', ''./application/routine/view/crmebN/pages/payment/payment.wxml'', ''1536957119'', ''1536749677'', ''1536749677''),
(911, ''ae08c1a100c47bc2d8266f571254eadb'', ''./application/routine/view/crmebN/pages/payment/payment.json'', ''1536957119'', ''1536288162'', ''1536288162''),
(912, ''28a3a4fcc5f1b6a6c2341980b8b6fd5f'', ''./application/routine/view/crmebN/pages/payment/payment.js'', ''1536957119'', ''1536749677'', ''1536749677''),
(913, ''3297e1c4f3654d49596a942cc0154393'', ''./application/routine/view/crmebN/pages/login-status/login-status.json'', ''1536957120'', ''1536288162'', ''1536288162''),
(914, ''50defca96df3629826363c02f853c3e6'', ''./application/routine/view/crmebN/pages/login-status/login-status.wxss'', ''1536957120'', ''1536288162'', ''1536288162''),
(915, ''b7190d808ed5e4846a14b58cb5fef67e'', ''./application/routine/view/crmebN/pages/login-status/login-status.wxml'', ''1536957120'', ''1536288162'', ''1536288162''),
(916, ''b69488408f02f4fd3a5be9ef4386daab'', ''./application/routine/view/crmebN/pages/login-status/login-status.js'', ''1536957121'', ''1536288162'', ''1536288162''),
(917, ''5ea7487b33f20935c9a8b5047b86204a'', ''./application/routine/view/crmebN/pages/promotion-order/promotion-order.js'', ''1536957121'', ''1536823545'', ''1536823545''),
(918, ''24b578f0936debc062150bdc9aac2c7e'', ''./application/routine/view/crmebN/pages/promotion-order/promotion-order.wxml'', ''1536957121'', ''1536288162'', ''1536288162''),
(919, ''666a9ef00908f9e8a84962015d4f35ce'', ''./application/routine/view/crmebN/pages/promotion-order/promotion-order.json'', ''1536957121'', ''1536288162'', ''1536288162''),
(920, ''2fb88f2ef4a1ebe32dddcb7e33e82a74'', ''./application/routine/view/crmebN/pages/promotion-order/promotion-order.wxss'', ''1536957122'', ''1536288162'', ''1536288162''),
(921, ''87927cc32810cf511afcb6cc90e609d8'', ''./application/routine/view/crmebN/pages/refunding/refunding.js'', ''1536957122'', ''1536288162'', ''1536288162''),
(922, ''8f34d5d23f6a18af6377637d6bee913f'', ''./application/routine/view/crmebN/pages/refunding/refunding.wxss'', ''1536957122'', ''1536288162'', ''1536288162''),
(923, ''9ce4eab506c8915f5cb3d9e0ff747b82'', ''./application/routine/view/crmebN/pages/refunding/refunding.json'', ''1536957122'', ''1536288162'', ''1536288162''),
(924, ''a0625db7abad48b9833c5f980919bfe3'', ''./application/routine/view/crmebN/pages/refunding/refunding.wxml'', ''1536957123'', ''1536288162'', ''1536288162''),
(925, ''9eef8d16d0ca9ba90dfcee89f20ccd87'', ''./application/routine/view/crmebN/pages/product-pinke/index.js'', ''1536957123'', ''1536288162'', ''1536288162''),
(926, ''d4aef67aa4417a6ec53f333c593caad7'', ''./application/routine/view/crmebN/pages/product-pinke/index.wxml'', ''1536957124'', ''1536749677'', ''1536749677''),
(927, ''75cf64aad56ed1fc59db38d88c6ee549'', ''./application/routine/view/crmebN/pages/product-pinke/index.json'', ''1536957124'', ''1536288162'', ''1536288162''),
(928, ''54df8a6e038626a4882a9acd0a990b92'', ''./application/routine/view/crmebN/pages/product-pinke/index.wxss'', ''1536957124'', ''1536749677'', ''1536749677''),
(929, ''0f4d5acfd2a9745fca314a71cd0b9ade'', ''./application/routine/view/crmebN/pages/feree-two/feree-two.json'', ''1536957125'', ''1536288162'', ''1536288162''),
(930, ''70c3b2820c3fc9f343ad711396c71a52'', ''./application/routine/view/crmebN/pages/feree-two/feree-two.js'', ''1536957125'', ''1536288162'', ''1536288162''),
(931, ''8394db650ad9b19f0ddcd88c86f0e815'', ''./application/routine/view/crmebN/pages/feree-two/feree-two.wxml'', ''1536957125'', ''1536288162'', ''1536288162''),
(932, ''88184fcc92ef56ceaaab8320a2ac7c98'', ''./application/routine/view/crmebN/pages/feree-two/feree-two.wxss'', ''1536957125'', ''1536288162'', ''1536288162''),
(933, ''d94212994e98bb56d59aa9b6a85ece73'', ''./application/routine/view/crmebN/pages/orderForm/orderForm.json'', ''1536957126'', ''1536288162'', ''1536288162''),
(934, ''34c97ed653f2ed176c07d5a4e1bffa06'', ''./application/routine/view/crmebN/pages/orderForm/orderForm.js'', ''1536957126'', ''1536288162'', ''1536288162''),
(935, ''4599576a6c456ddeb01f7cb3042150c7'', ''./application/routine/view/crmebN/pages/orderForm/orderForm.wxml'', ''1536957126'', ''1536288162'', ''1536288162''),
(936, ''add83dca6f17947f70f785d1b10fcef8'', ''./application/routine/view/crmebN/pages/orderForm/orderForm.wxss'', ''1536957126'', ''1536288162'', ''1536288162''),
(937, ''597d57cd999a34b373ba050b19a5a927'', ''./application/routine/view/crmebN/pages/integral-con/integral-con.json'', ''1536957127'', ''1536288162'', ''1536288162''),
(938, ''21c11ce6b5905f6dfd8ea0d8bebb271d'', ''./application/routine/view/crmebN/pages/integral-con/integral-con.js'', ''1536957127'', ''1536749677'', ''1536749677''),
(939, ''0b09721a434ccc4705df50e03949c0f3'', ''./application/routine/view/crmebN/pages/integral-con/integral-con.wxml'', ''1536957127'', ''1536749677'', ''1536749677''),
(940, ''003271a91f35a96e5b8df4b144815db2'', ''./application/routine/view/crmebN/pages/integral-con/integral-con.wxss'', ''1536957127'', ''1536288162'', ''1536288162''),
(941, ''8a261cdb4e6cb22baf17bd41ad50f1a0'', ''./application/routine/view/crmebN/pages/addaddress/addaddress.json'', ''1536957128'', ''1536288162'', ''1536288162''),
(942, ''52a6dd95605b32f72d5f97542ea65c57'', ''./application/routine/view/crmebN/pages/addaddress/addaddress.js'', ''1536957128'', ''1536288162'', ''1536288162''),
(943, ''a292b1feeaf0ccd256a7c354190df0c1'', ''./application/routine/view/crmebN/pages/addaddress/addaddress.wxml'', ''1536957128'', ''1536288162'', ''1536288162''),
(944, ''3ac908d6df7a27c6194132bc6f7cffee'', ''./application/routine/view/crmebN/pages/addaddress/addaddress.wxss'', ''1536957128'', ''1536288162'', ''1536288162''),
(945, ''1119ded62594c84f07b61cd4db9188d0'', ''./application/routine/view/crmebN/pages/unshop/unshop.wxss'', ''1536957129'', ''1536288162'', ''1536288162''),
(946, ''3a42120a3091c4a2544cc4ae8694d684'', ''./application/routine/view/crmebN/pages/unshop/unshop.json'', ''1536957129'', ''1536288162'', ''1536288162''),
(947, ''eaf8d7f7dec0dad2ecdd4926a6672325'', ''./application/routine/view/crmebN/pages/unshop/unshop.js'', ''1536957129'', ''1536288162'', ''1536288162''),
(948, ''54feadc53fcebfc64202c6977e6d25c9'', ''./application/routine/view/crmebN/pages/unshop/unshop.wxml'', ''1536957130'', ''1536288162'', ''1536288162''),
(949, ''7732f90d82d0f32b899d6df3cb3ea5b8'', ''./application/routine/view/crmebN/pages/comment-con/comment-con.json'', ''1536957130'', ''1536288162'', ''1536288162''),
(950, ''f41aaafe5fdd67f386e6adb4d498ed7e'', ''./application/routine/view/crmebN/pages/comment-con/comment-con.wxml'', ''1536957130'', ''1536288162'', ''1536288162''),
(951, ''b36a7cd613aad58dfd55f2382f3eac64'', ''./application/routine/view/crmebN/pages/comment-con/comment-con.wxss'', ''1536957130'', ''1536288162'', ''1536288162''),
(952, ''373442c77a6e4593c6095e765d2ea77e'', ''./application/routine/view/crmebN/pages/comment-con/comment-con.js'', ''1536957131'', ''1536823545'', ''1536823545''),
(953, ''8a94f94ef73d8d24bf5d518a963d0536'', ''./application/routine/view/crmebN/pages/comment/comment.wxss'', ''1536957131'', ''1536288162'', ''1536288162''),
(954, ''3844e241085f7e54c6b9dcf4896c1443'', ''./application/routine/view/crmebN/pages/comment/comment.js'', ''1536957131'', ''1536288162'', ''1536288162''),
(955, ''dea94175b56f8f32c212be0137c10c98'', ''./application/routine/view/crmebN/pages/comment/comment.wxml'', ''1536957131'', ''1536288162'', ''1536288162''),
(956, ''8513cfd702e17c0e6f80b1fdb2f315c7'', ''./application/routine/view/crmebN/pages/comment/comment.json'', ''1536957132'', ''1536288162'', ''1536288162''),
(957, ''ffdb1ab48ab95fcd0a0f02207757d151'', ''./application/routine/view/crmebN/pages/order-confirm/order-confirm.wxml'', ''1536957132'', ''1536288162'', ''1536288162''),
(958, ''5a4849d762d663df64ae3e97889e9b5b'', ''./application/routine/view/crmebN/pages/order-confirm/order-confirm.json'', ''1536957132'', ''1536288162'', ''1536288162''),
(959, ''f2537498cdbb48251af8f20a66eb0012'', ''./application/routine/view/crmebN/pages/order-confirm/order-confirm.js'', ''1536957133'', ''1536749677'', ''1536749677''),
(960, ''c7ea2492d59d4c07126c79fd0135c758'', ''./application/routine/view/crmebN/pages/order-confirm/order-confirm.wxss'', ''1536957133'', ''1536288162'', ''1536288162''),
(961, ''83abd2902ab3389bb94b2f105bb46dd5'', ''./application/routine/view/crmebN/pages/orders-list/orders-list.wxml'', ''1536957133'', ''1536288162'', ''1536288162''),
(962, ''3a6821be085d9d80229fdbfca8fd856a'', ''./application/routine/view/crmebN/pages/orders-list/orders-list.json'', ''1536957134'', ''1536288162'', ''1536288162''),
(963, ''42481167cd625eff9e03c13633c4d0b3'', ''./application/routine/view/crmebN/pages/orders-list/orders-list.wxss'', ''1536957134'', ''1536288162'', ''1536288162''),
(964, ''e10bb386991f7842ee227f6e3b45c77f'', ''./application/routine/view/crmebN/pages/orders-list/orders-list.js'', ''1536957134'', ''1536288162'', ''1536288162''),
(965, ''d5966cdb30c8fc027e959ea5d1a5ca26'', ''./application/routine/view/crmebN/pages/promotion-card/promotion-card.js'', ''1536957135'', ''1536288162'', ''1536288162''),
(966, ''3e8e914bd8813ae955ac3afa2267f6e8'', ''./application/routine/view/crmebN/pages/promotion-card/promotion-card.wxss'', ''1536957135'', ''1536288162'', ''1536288162''),
(967, ''1d382e751008cb33a115be87109da089'', ''./application/routine/view/crmebN/pages/promotion-card/promotion-card.wxml'', ''1536957135'', ''1536288162'', ''1536288162''),
(968, ''2a82b80bb66baa1ac50336bb40c67235'', ''./application/routine/view/crmebN/pages/promotion-card/promotion-card.json'', ''1536957135'', ''1536288162'', ''1536288162''),
(969, ''1bdc07b939db7b59041e4190cb83ca0e'', ''./application/routine/view/crmebN/pages/logistics/logistics.wxss'', ''1536957136'', ''1536288162'', ''1536288162''),
(970, ''8d8b3376929df702147f06ee98f127cf'', ''./application/routine/view/crmebN/pages/logistics/logistics.wxml'', ''1536957136'', ''1536288162'', ''1536288162''),
(971, ''de598d6f3e77d0147c91420ae443449f'', ''./application/routine/view/crmebN/pages/logistics/logistics.js'', ''1536957136'', ''1536288162'', ''1536288162''),
(972, ''25c278d5f53347c0797cc508894a2823'', ''./application/routine/view/crmebN/pages/logistics/logistics.json'', ''1536957136'', ''1536288162'', ''1536288162''),
(973, ''d0ae88dcb35949366a8be59adb496fbb'', ''./application/routine/view/crmebN/pages/refund-page/refund-page.js'', ''1536957137'', ''1536749677'', ''1536749677''),
(974, ''a188ba9eb58e8b9b7e9edeb5c0b7197a'', ''./application/routine/view/crmebN/pages/refund-page/refund-page.wxss'', ''1536957137'', ''1536288162'', ''1536288162''),
(975, ''34fc9f976d8da8d848e37e9bffbeb865'', ''./application/routine/view/crmebN/pages/refund-page/refund-page.wxml'', ''1536957137'', ''1536288162'', ''1536288162''),
(976, ''f7bb8e3fc1f5d1ac5ffe8000735f28cc'', ''./application/routine/view/crmebN/pages/refund-page/refund-page.json'', ''1536957137'', ''1536288162'', ''1536288162''),
(977, ''0bbbc7c7094e0df92ab09eeb57cda98a'', ''./application/routine/view/crmebN/pages/productSort/productSort.json'', ''1536957138'', ''1536288162'', ''1536288162''),
(978, ''7ca8c84058574f1aa77ff325befd57bf'', ''./application/routine/view/crmebN/pages/productSort/productSort.js'', ''1536957138'', ''1536288162'', ''1536288162''),
(979, ''6fa7d2c40a2010d73d1859ddc0d3b0a5'', ''./application/routine/view/crmebN/pages/productSort/productSort.wxml'', ''1536957138'', ''1536288162'', ''1536288162''),
(980, ''e88a176bb6bf5a7756d786f605af4384'', ''./application/routine/view/crmebN/pages/productSort/productSort.wxss'', ''1536957138'', ''1536288162'', ''1536288162''),
(981, ''e32b535666edf9b26bbc897cf562623f'', ''./application/routine/view/crmebN/pages/feree/feree.js'', ''1536957139'', ''1536288162'', ''1536288162''),
(982, ''e7a6f9a8edf7e1049c160c52350d7fbd'', ''./application/routine/view/crmebN/pages/feree/feree.wxss'', ''1536957139'', ''1536288162'', ''1536288162''),
(983, ''47990b15d4f560e58f6d7562125ca4f2'', ''./application/routine/view/crmebN/pages/feree/feree.json'', ''1536957139'', ''1536288162'', ''1536288162''),
(984, ''80b7ec751ab232d74ee58b21c3b59148'', ''./application/routine/view/crmebN/pages/feree/feree.wxml'', ''1536957139'', ''1536288162'', ''1536288162''),
(985, ''f7a0ea4c4a50195e19b1e747d8f93fba'', ''./application/routine/view/crmebN/pages/main/main.js'', ''1536957140'', ''1536288162'', ''1536288162''),
(986, ''ddbd2f5f6929f8f5ef6946b0e865ecfe'', ''./application/routine/view/crmebN/pages/main/main.json'', ''1536957140'', ''1536288162'', ''1536288162''),
(987, ''def18db2f2c23d4ab4ee1ee8263230e8'', ''./application/routine/view/crmebN/pages/main/main.wxml'', ''1536957140'', ''1536288162'', ''1536288162''),
(988, ''f3f03069467b6b859a369ebf267e0b49'', ''./application/routine/view/crmebN/pages/main/main.wxss'', ''1536957140'', ''1536288162'', ''1536288162''),
(989, ''73790d445f2d5ad3ed23e214c65341f3'', ''./application/routine/view/crmebN/app.wxss'', ''1536957142'', ''1536288162'', ''1536288162''),
(990, ''37fb64442c4fba796194fbd92cc139f4'', ''./application/routine/view/crmebN/utils/wxh.js'', ''1536957142'', ''1536288162'', ''1536288162''),
(991, ''6fdef4e502350a38d4eb5d59ef7ae608'', ''./application/routine/view/crmebN/utils/util.js'', ''1536957142'', ''1536288162'', ''1536288162''),
(992, ''6a4d82a082b34af61500f09a2cd0e689'', ''./application/routine/common.php'', ''1536957144'', ''1536288162'', ''1536288162''),
(993, ''1e373120fdd3ba2a00454836a5a1e462'', ''./application/routine/config.php'', ''1536957144'', ''1536288162'', ''1536288162''),
(994, ''cfad82cc9cbb33d331a361b910622e86'', ''./application/command.php'', ''1536957145'', ''1536288162'', ''1536288162''),
(995, ''da6ca537ba8a707b940fe20def28e44a'', ''./application/tags.php'', ''1536957145'', ''1536288162'', ''1536288162''),
(996, ''45299a86db3649b32eb1d87bb0f83883'', ''./extend/traits/CurdControllerTrait.php'', ''1536957146'', ''1536288162'', ''1536288162''),
(997, ''9c358738e3fb853e4032a59355cf1a29'', ''./extend/traits/ModelTrait.php'', ''1536957146'', ''1536541467'', ''1536541467''),
(998, ''018d1567fb76017eb0e401ec06964320'', ''./extend/basic/WapBasic.php'', ''1536957147'', ''1536288162'', ''1536288162''),
(999, ''24a37e5eb821b1b313282c1668f2a87c'', ''./extend/basic/ModelBasic.php'', ''1536957147'', ''1536288162'', ''1536288162''),
(1000, ''95f84ee443d24e87896310c9638192c0'', ''./extend/basic/WapException.php'', ''1536957147'', ''1536288162'', ''1536288162''),
(1001, ''e126159d2bbfd5f91d8f4f3e62c847b0'', ''./extend/basic/SystemBasic.php'', ''1536957147'', ''1536288162'', ''1536288162''),
(1002, ''8820cc471e62b1436f1abd80b68cd1b6'', ''./extend/behavior/wechat/PaymentBehavior.php'', ''1536957148'', ''1536288162'', ''1536288162''),
(1003, ''7f4fe202e13fb6598d4646ce63cc3986'', ''./extend/behavior/wechat/MaterialBehavior.php'', ''1536957150'', ''1536288162'', ''1536288162''),
(1004, ''bb66bb2b173b706db150ad5acf95d2bb'', ''./extend/behavior/wechat/MessageBehavior.php'', ''1536957152'', ''1536288162'', ''1536288162''),
(1005, ''d94ae1eaad78921602fb55b9bc81d8ed'', ''./extend/behavior/wechat/UserBehavior.php'', ''1536957156'', ''1536288162'', ''1536288162''),
(1006, ''8858b28e0660f5b02e041afd88aff26e'', ''./extend/behavior/wechat/QrcodeEventBehavior.php'', ''1536957157'', ''1536288162'', ''1536288162''),
(1007, ''dc8a72493615289f3fb062c6ad994775'', ''./extend/behavior/wap/StoreProductBehavior.php'', ''1536957157'', ''1536288162'', ''1536288162''),
(1008, ''62f98405042c8a66cc69f85c26822075'', ''./extend/behavior/wap/UserBehavior.php'', ''1536957157'', ''1536288162'', ''1536288162''),
(1009, ''a29216d97e14727aedf6e5bc99d9c885'', ''./extend/behavior/wap/WapBehavior.php'', ''1536957158'', ''1536288162'', ''1536288162''),
(1010, ''1ea4520c4191bf06847e47cc1f733da3'', ''./extend/behavior/routine/StoreProductBehavior.php'', ''1536957158'', ''1536541467'', ''1536541467''),
(1011, ''758ecc8ac3211c9dc1f59bbef5ec37f7'', ''./extend/behavior/system/SystemBehavior.php'', ''1536957159'', ''1536288162'', ''1536288162''),
(1012, ''4a35463060afe38b696edc4d09e3cf78'', ''./extend/service/GroupDataService.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1013, ''3e17941e4d3477ccc550fcdb0715262b'', ''./extend/service/HookService.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1014, ''6070393f904d096d86b8013a7eee47ee'', ''./extend/service/RoutineRefund.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1015, ''e31c176b4a9d7bfcee48243fc7c6596a'', ''./extend/service/WxpayService.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1016, ''0b0df513226daad980c4b53ba52e3c38'', ''./extend/service/FormBuilder.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1017, ''f8984c80868cc23da866e1029b08697e'', ''./extend/service/PHPExcelService.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1018, ''59cfcbc9a9ba5218a5cfbd50224d817c'', ''./extend/service/WxpayServiceNotify.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1019, ''e404b534bcf292b2447dce115cac6c8d'', ''./extend/service/ResultService.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1020, ''05082e177d0cba7a31d3478ade1242a2'', ''./extend/service/RoutineTemplateService.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1021, ''fdd75a3fd892a0af90e3045a6a75b29e'', ''./extend/service/RoutineService.php'', ''1536957161'', ''1536288162'', ''1536288162''),
(1022, ''e8fcd414cc1fb53afdc28b6a92386212'', ''./extend/service/JsonService.php'', ''1536957162'', ''1536288162'', ''1536288162''),
(1023, ''678c41301f3fdb08157457ddee1ed938'', ''./extend/service/ExportService.php'', ''1536957162'', ''1536288162'', ''1536288162''),
(1024, ''a826d8d6ac817255734adb63bd9a278b'', ''./extend/service/UpgradeService.php'', ''1536957162'', ''1536541467'', ''1536541467''),
(1025, ''ba3b2b9c2a93473c7f5d0c745db6a9e4'', ''./extend/service/FileService.php'', ''1536957162'', ''1536288162'', ''1536288162''),
(1026, ''9d9cb9c33119f734f557f0529da31e66'', ''./extend/service/UtilService.php'', ''1536957162'', ''1536288162'', ''1536288162''),
(1027, ''95711d986f3d17d499099599b2ad3321'', ''./extend/service/UploadService.php'', ''1536957162'', ''1536288162'', ''1536288162''),
(1028, ''b3de8a7a3914129935be454bad9bf61b'', ''./extend/service/HttpService.php'', ''1536957162'', ''1536288162'', ''1536288162''),
(1029, ''f36d2a674048e01ef8f97b2ba985c99c'', ''./extend/service/SystemConfigService.php'', ''1536957162'', ''1536288162'', ''1536288162''),
(1030, ''a496ef8510844d552695b6d1ad1aa8ce'', ''./extend/service/QrcodeService.php'', ''1536957163'', ''1536288162'', ''1536288162''),
(1031, ''51031cc5b67e69bb392935eacaf4a3d0'', ''./extend/service/WechatService.php'', ''1536957163'', ''1536541467'', ''1536541467''),
(1032, ''c74f9f7ed5df26544a9af12b09d62559'', ''./extend/service/WechatTemplateService.php'', ''1536957165'', ''1536288162'', ''1536288162''),
(1033, ''7d10971c45e50ce4cb9a1ba4a2c25865'', ''./extend/service/CacheService.php'', ''1536957165'', ''1536288162'', ''1536288162''),
(1034, ''8721fac360a8b4eddb3ebb132f4460db'', ''./extend/Api/Express.php'', ''1536957166'', ''1536288162'', ''1536288162''),
(1035, ''e3fddf7b1b142f4a3480b15000fc993e'', ''./public/static/css/reset.css'', ''1536871648'', ''1536288162'', ''1536288162''),
(1036, ''57db4a2811f951ff841fb4f77220d95b'', ''./public/static/css/animate.css'', ''1536871648'', ''1536288162'', ''1536288162'');
INSERT INTO `eb_system_file` (`id`, `cthash`, `filename`, `atime`, `mtime`, `ctime`) VALUES
(1037, ''9e3d8f3f64b99c8c036a7f9581a2f4e4'', ''./public/static/plug/reg-verify.js'', ''1536871651'', ''1536288162'', ''1536288162''),
(1038, ''6e4f3ef8152e174622be5ec910d4f249'', ''./public/static/plug/wxApi.js'', ''1536871651'', ''1536288162'', ''1536288162''),
(1039, ''17410722717c1e9395f02d0dd74c2590'', ''./public/static/plug/daterangepicker/daterangepicker.js'', ''1536871651'', ''1536288162'', ''1536288162''),
(1040, ''2fba2a36f4a0188d6ac539e97ac788c4'', ''./public/static/plug/daterangepicker/daterangepicker.css'', ''1536871652'', ''1536288162'', ''1536288162''),
(1041, ''d41d8cd98f00b204e9800998ecf8427e'', ''./public/static/plug/copy.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1042, ''3aa9b9ebda33033336cce574b6f148ed'', ''./public/static/plug/jquery-1.4.1.min.js'', ''1536871652'', ''1536288162'', ''1536288162''),
(1043, ''359a04c6af9d438c31908eef96e9a928'', ''./public/static/plug/iview/dist/iview.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1044, ''8ffbb5a88713c5d685d974a4fc839d40'', ''./public/static/plug/iview/dist/styles/iview.css'', ''1536871653'', ''1536288162'', ''1536288162''),
(1045, ''2c2ae068be3b089e0a5b59abb1831550'', ''./public/static/plug/iview/dist/styles/fonts/ionicons.eot'', ''1536871653'', ''1536288162'', ''1536288162''),
(1046, ''24712f6c47821394fba7942fbb52c3b2'', ''./public/static/plug/iview/dist/styles/fonts/ionicons.ttf'', ''1536871653'', ''1536288162'', ''1536288162''),
(1047, ''621bd386841f74e0053cb8e67f8a0604'', ''./public/static/plug/iview/dist/styles/fonts/ionicons.svg'', ''1536871653'', ''1536288162'', ''1536288162''),
(1048, ''05acfdb568b3df49ad31355b19495d4a'', ''./public/static/plug/iview/dist/styles/fonts/ionicons.woff'', ''1536871653'', ''1536288162'', ''1536288162''),
(1049, ''30173fd15782a5653e5c1b234521a959'', ''./public/static/plug/iview/dist/iview.min.js'', ''1536573396'', ''1536288162'', ''1536288162''),
(1050, ''ced8fbbbc266a42b4f20a0a909be2491'', ''./public/static/plug/iview/dist/locale/pt-PT.js'', ''1536871655'', ''1536288162'', ''1536288162''),
(1051, ''c052220f96815a15b1f68c1fd4f7ee12'', ''./public/static/plug/iview/dist/locale/id-ID.js'', ''1536871655'', ''1536288162'', ''1536288162''),
(1052, ''a147e39fc3f1fcc10d8d7b0d949ae123'', ''./public/static/plug/iview/dist/locale/ko-KR.js'', ''1536871655'', ''1536288162'', ''1536288162''),
(1053, ''5fad473ad29a2ca3005e81918eba4038'', ''./public/static/plug/iview/dist/locale/ja-JP.js'', ''1536871655'', ''1536288162'', ''1536288162''),
(1054, ''c0de3c748f7b067d4a08289176c38761'', ''./public/static/plug/iview/dist/locale/zh-CN.js'', ''1536871655'', ''1536288162'', ''1536288162''),
(1055, ''a4591f9285817b3096881112430e4505'', ''./public/static/plug/iview/dist/locale/de-DE.js'', ''1536871655'', ''1536288162'', ''1536288162''),
(1056, ''30c93bd10e9a17601a3120c633ce6e45'', ''./public/static/plug/iview/dist/locale/vi-VN.js'', ''1536871655'', ''1536288162'', ''1536288162''),
(1057, ''62c5469b2dc3d06e43775781fe345598'', ''./public/static/plug/iview/dist/locale/es-ES.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1058, ''7cd6a5caafff87f609ce78a076a44643'', ''./public/static/plug/iview/dist/locale/fr-FR.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1059, ''4333bc8adac238a5d020b396126bd42b'', ''./public/static/plug/iview/dist/locale/en-US.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1060, ''c10e6a171e98a3dac5f34f738c808a68'', ''./public/static/plug/iview/dist/locale/sv-SE.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1061, ''31ec5dbdd426ce6bfebc21c4c8858016'', ''./public/static/plug/iview/dist/locale/ru-RU.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1062, ''f5ea82d56a1e757e17b25f0ab1bd407e'', ''./public/static/plug/iview/dist/locale/tr-TR.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1063, ''c8b21eba8334d31d063d2eeb8ccea94d'', ''./public/static/plug/iview/dist/locale/zh-TW.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1064, ''522aa18fb0e6fd7370aea1e2888d6a4b'', ''./public/static/plug/iview/dist/locale/pt-BR.js'', ''1536871656'', ''1536288162'', ''1536288162''),
(1065, ''cecba37226e43433959c784bdbd15579'', ''./public/static/plug/lodash.js'', ''1536871658'', ''1536288162'', ''1536288162''),
(1066, ''db1b2af72a0284157cec60fb01bb362f'', ''./public/static/plug/vant/vant.min.js'', ''1536871658'', ''1536288162'', ''1536288162''),
(1067, ''9e6119f9c349ed8fde1af51a819a8fa3'', ''./public/static/plug/vant/vant.css'', ''1536871659'', ''1536288162'', ''1536288162''),
(1068, ''8a010634d0be8abb8370dc2aa45e065c'', ''./public/static/plug/jquery.downCount.js'', ''1536871659'', ''1536288162'', ''1536288162''),
(1069, ''8d9289aedbe96521f2f8123b9ea92068'', ''./public/static/plug/axios.min.js'', ''1536871659'', ''1536288162'', ''1536288162''),
(1070, ''358ae4a98630101b6ca9ffce3817a440'', ''./public/static/plug/ydui/province_city_id.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1071, ''c96fe283052715ba4edc7b9eb896863a'', ''./public/static/plug/ydui/ydui2.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1072, ''3c0cc77ad7e2a96a87a276486ea80202'', ''./public/static/plug/ydui/province_city_area2.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1073, ''a94db750a833f9c81d8075d57b8499b7'', ''./public/static/plug/ydui/ydui.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1074, ''5447db0969056561df63afafd475fe3a'', ''./public/static/plug/ydui/province_city.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1075, ''3cec33010d43dbcb051dd8da6ce03f7c'', ''./public/static/plug/ydui/province_city_area_id.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1076, ''58efd044759758ce13ead16ca41b3c62'', ''./public/static/plug/ydui/cityselect.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1077, ''efbd1c7a21cf71fa68fb2212e6c7424f'', ''./public/static/plug/ydui/province_city_area.js'', ''1536871660'', ''1536288162'', ''1536288162''),
(1078, ''5ff6a80730ca572d24352e6508f56040'', ''./public/static/plug/ydui/ydui-px.css'', ''1536871660'', ''1536288162'', ''1536288162''),
(1079, ''aee4e5aafca0f5c26c352c3d6e69b20a'', ''./public/static/plug/basket.js'', ''1536871661'', ''1536288162'', ''1536288162''),
(1080, ''aaf50b98a6bae537c2105fd125aa7fbf'', ''./public/static/plug/jquery-slide-up.js'', ''1536871661'', ''1536288162'', ''1536288162''),
(1081, ''cf2786de75573240473e4e56cef443f8'', ''./public/static/plug/better-scroll.js'', ''1536871661'', ''1536288162'', ''1536288162''),
(1082, ''55c17f69ae6c2d587a0e33dc0a3efcf4'', ''./public/static/plug/requirejs/require-compiled.js.map'', ''1536871661'', ''1536288162'', ''1536288162''),
(1083, ''b9c130e7f47765cadef3f4a80ad3b412'', ''./public/static/plug/requirejs/README.md'', ''1536871662'', ''1536288162'', ''1536288162''),
(1084, ''fa71c855757afa72f454060031db4159'', ''./public/static/plug/requirejs/require-compiled.js'', ''1536871662'', ''1536288162'', ''1536288162''),
(1085, ''61f8c2e37f77a591e0672b7721d8c758'', ''./public/static/plug/requirejs/require-basket-load-compiled.js'', ''1536871662'', ''1536288162'', ''1536288162''),
(1086, ''134360260efaa95a5c79a6fe4723167b'', ''./public/static/plug/requirejs/require-basket-load-compiled.js.map'', ''1536871662'', ''1536288162'', ''1536288162''),
(1087, ''f051459ded214178b064b37209d9398c'', ''./public/static/plug/requirejs/bin/r.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1088, ''02a914ed35fb011193120b96ef31e77d'', ''./public/static/plug/requirejs/require-basket-load.js'', ''1536871663'', ''1536288162'', ''1536288162''),
(1089, ''354cf34e88ac81f464adc56d5b4b6647'', ''./public/static/plug/requirejs/require.js'', ''1536871663'', ''1536288162'', ''1536288162''),
(1090, ''5bea7d748bd7cc0ffadd553cfb7d7df0'', ''./public/static/plug/requirejs/require-css.js'', ''1536871663'', ''1536288162'', ''1536288162''),
(1091, ''4bead4b1b0a417582825231dbfe121b1'', ''./public/static/plug/requirejs/package.json'', ''1536871663'', ''1536288162'', ''1536288162''),
(1092, ''037d997219804a79cea6540312fc8e0a'', ''./public/static/plug/vue/dist/vue.js'', ''1536871664'', ''1536288162'', ''1536288162''),
(1093, ''a64ac1319064e7e88d336ce95f667d52'', ''./public/static/plug/vue/dist/README.md'', ''1536871666'', ''1536288162'', ''1536288162''),
(1094, ''bd3852d9ff082206759b1d322eeccfe8'', ''./public/static/plug/vue/dist/vue.runtime.js'', ''1536871668'', ''1536288162'', ''1536288162''),
(1095, ''7f7d01342623404fe6dadc6bea8a5d22'', ''./public/static/plug/vue/dist/vue.esm.js'', ''1536871670'', ''1536288162'', ''1536288162''),
(1096, ''3901c2214f7cbdf0dc2c962e3cc1a5ad'', ''./public/static/plug/vue/dist/vue.runtime.min.js'', ''1536871672'', ''1536288162'', ''1536288162''),
(1097, ''917f70e72ec5e171ea46987517925f1e'', ''./public/static/plug/vue/dist/vue.runtime.common.js'', ''1536871673'', ''1536288162'', ''1536288162''),
(1098, ''9eb63dbfb2badb381a3b6892b4da9f04'', ''./public/static/plug/vue/dist/vue.runtime.esm.js'', ''1536871673'', ''1536288162'', ''1536288162''),
(1099, ''55aa848bc74dde42637d7ae96f38ec01'', ''./public/static/plug/vue/dist/vue.common.js'', ''1536871673'', ''1536288162'', ''1536288162''),
(1100, ''7e052e2850e70a8db1bd837e08ddda83'', ''./public/static/plug/vue/dist/vue.min.js'', ''1536871673'', ''1536288162'', ''1536288162''),
(1101, ''7b9d65cd421b833d3711523179fef3ec'', ''./public/static/plug/sweetalert2/sweetalert2.all.min.js'', ''1536871674'', ''1536288162'', ''1536288162''),
(1102, ''e034abd50a709532545aa63aae481bf8'', ''./public/static/plug/censorwords/CensorWords'', ''1536871675'', ''1536288162'', ''1536288162''),
(1103, ''a3f43bab5ace67f3616f5ebfc66744dc'', ''./public/static/plug/iscroll5.js'', ''1536871676'', ''1536288162'', ''1536288162''),
(1104, ''eec074113b5a647d6458fe6d665bbf1a'', ''./public/static/plug/city.js'', ''1536871676'', ''1536288162'', ''1536288162''),
(1105, ''dab5bfd4697220d0b52c93de5f5865e4'', ''./public/static/plug/helper.js'', ''1536871676'', ''1536288162'', ''1536288162''),
(1106, ''db0eb3e080078a53626d846636fad24b'', ''./public/static/plug/jquery-1.10.2.min.js'', ''1536871676'', ''1536288162'', ''1536288162''),
(1107, ''c5873fa3b95636deac5ba3d97c08601f'', ''./public/static/plug/echarts.common.min.js'', ''1536293102'', ''1536288162'', ''1536288162''),
(1108, ''7d070ed6f406312e21b6167d27e40ed5'', ''./public/static/plug/layui/lay/modules/upload.js'', ''1536871676'', ''1536288162'', ''1536288162''),
(1109, ''e46f083aa76d5b0279e9d14e8766911e'', ''./public/static/plug/layui/lay/modules/mobile.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1110, ''ca78528c719dcbc986035068ed0d98cb'', ''./public/static/plug/layui/lay/modules/laydate.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1111, ''dc515b85249eade92e056834e73c6e2e'', ''./public/static/plug/layui/lay/modules/util.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1112, ''8418139e5c969d791e025567d7aa9b58'', ''./public/static/plug/layui/lay/modules/layer.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1113, ''bda972d9c47463108b23b11883b15cf0'', ''./public/static/plug/layui/lay/modules/laypage.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1114, ''6c409f4fcb0934b282bc7efe06749221'', ''./public/static/plug/layui/lay/modules/rate.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1115, ''291b3d06a30a02bb942d845828498408'', ''./public/static/plug/layui/lay/modules/laytpl.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1116, ''71ff0b6170003297fb7dc0c95b1b551e'', ''./public/static/plug/layui/lay/modules/layedit.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1117, ''c5a3c4c44a45e6864c5aa4d9d681e067'', ''./public/static/plug/layui/lay/modules/form.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1118, ''56aa16085492ebe9259fd6b19a5a3840'', ''./public/static/plug/layui/lay/modules/tree.js'', ''1536871677'', ''1536288162'', ''1536288162''),
(1119, ''e6e49808f0eb611940d4e2cfb0b1f1cd'', ''./public/static/plug/layui/lay/modules/jquery.js'', ''1536871678'', ''1536288162'', ''1536288162''),
(1120, ''76d68fe30df84179e60d637d6d269a55'', ''./public/static/plug/layui/lay/modules/code.js'', ''1536871678'', ''1536288162'', ''1536288162''),
(1121, ''7af2cdf8bae45d82b3734261c706d2d4'', ''./public/static/plug/layui/lay/modules/flow.js'', ''1536871678'', ''1536288162'', ''1536288162''),
(1122, ''66887d043156b432f7dfebaedcb9703f'', ''./public/static/plug/layui/lay/modules/element.js'', ''1536871678'', ''1536288162'', ''1536288162''),
(1123, ''b55f1fe169510cf6719622d8be7110dc'', ''./public/static/plug/layui/lay/modules/carousel.js'', ''1536871678'', ''1536288162'', ''1536288162''),
(1124, ''2655876320dcada9737bf640dadba9f4'', ''./public/static/plug/layui/lay/modules/table.js'', ''1536871678'', ''1536288162'', ''1536288162''),
(1125, ''96c5d5d9c7a9bfd4c848c6f12d837989'', ''./public/static/plug/layui/layui.js'', ''1536871680'', ''1536288162'', ''1536288162''),
(1126, ''3327b7cab68d5c1a8ce9ca6d3a68d05c'', ''./public/static/plug/layui/font/iconfont.eot'', ''1536871680'', ''1536288162'', ''1536288162''),
(1127, ''471366a4a15b2afe4c0439d6d076f5d2'', ''./public/static/plug/layui/font/iconfont.svg'', ''1536871680'', ''1536288162'', ''1536288162''),
(1128, ''a965966baa59ad4410484159855c8367'', ''./public/static/plug/layui/font/iconfont.ttf'', ''1536871680'', ''1536288162'', ''1536288162''),
(1129, ''8dc46464fd93a0c95c2b754948966152'', ''./public/static/plug/layui/font/iconfont.woff'', ''1536871680'', ''1536288162'', ''1536288162''),
(1130, ''f9c7dc37ecdc5c90f7e73658dee68968'', ''./public/static/plug/layui/css/modules/laydate/default/laydate.css'', ''1536871681'', ''1536288162'', ''1536288162''),
(1131, ''189b6e1baa96d7ea60cf4f6cf2da51e1'', ''./public/static/plug/layui/css/modules/code.css'', ''1536871682'', ''1536288162'', ''1536288162''),
(1132, ''50c5e3e79b276c92df6cc52caeb464f0'', ''./public/static/plug/layui/css/modules/layer/default/loading-2.gif'', ''1536871683'', ''1536288162'', ''1536288162''),
(1133, ''a72011ccdc2bcd23ba440f104c416193'', ''./public/static/plug/layui/css/modules/layer/default/loading-0.gif'', ''1536871683'', ''1536288162'', ''1536288162''),
(1134, ''db6828ad63f4e173e653e4ce6630c7b2'', ''./public/static/plug/layui/css/modules/layer/default/layer.css'', ''1536871683'', ''1536288162'', ''1536288162''),
(1135, ''551539f873d9ebe0792b120a9867d399'', ''./public/static/plug/layui/css/modules/layer/default/icon.png'', ''1536871683'', ''1536288162'', ''1536288162''),
(1136, ''ba81b24c06e2e0eac1e219405b33766b'', ''./public/static/plug/layui/css/modules/layer/default/icon-ext.png'', ''1536871683'', ''1536288162'', ''1536288162''),
(1137, ''1140bc5c7863f8e54a3c2b179e640758'', ''./public/static/plug/layui/css/modules/layer/default/loading-1.gif'', ''1536871683'', ''1536288162'', ''1536288162''),
(1138, ''73f14a1266114a5a3975170f76186b09'', ''./public/static/plug/layui/css/layui.css'', ''1536871685'', ''1536288162'', ''1536288162''),
(1139, ''e359993bce9bebae57b8e307046e3028'', ''./public/static/plug/layui/css/layui.mobile.css'', ''1536871685'', ''1536288162'', ''1536288162''),
(1140, ''1cac11c540095dc58d46483bf3cf606e'', ''./public/static/plug/layui/layui.all.js'', ''1536871686'', ''1536288162'', ''1536288162''),
(1141, ''4e10558193648444470bb9a1dd7007da'', ''./public/static/plug/layui/images/face/27.gif'', ''1536871686'', ''1536288162'', ''1536288162''),
(1142, ''8621f66098b5c352706832525ca98430'', ''./public/static/plug/layui/images/face/13.gif'', ''1536871686'', ''1536288162'', ''1536288162''),
(1143, ''c94db1a006caf85ccb8a194335ce5621'', ''./public/static/plug/layui/images/face/40.gif'', ''1536871686'', ''1536288162'', ''1536288162''),
(1144, ''907b3e81d16afb9df5ef023ede0eddf1'', ''./public/static/plug/layui/images/face/63.gif'', ''1536871686'', ''1536288162'', ''1536288162''),
(1145, ''a1c8f21f98fc6eff21cb3e4d08baee2b'', ''./public/static/plug/layui/images/face/68.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1146, ''2a4c6936a09b0219d1c74f0167577b30'', ''./public/static/plug/layui/images/face/48.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1147, ''ff4d93b3615d5d975f0b6786670b702f'', ''./public/static/plug/layui/images/face/52.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1148, ''2f9e45312f49e02f32ce853db930295f'', ''./public/static/plug/layui/images/face/33.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1149, ''55565f0156566feef1e329177f6d83f1'', ''./public/static/plug/layui/images/face/41.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1150, ''435dbb7f69e76dbe51c64d0208478bd5'', ''./public/static/plug/layui/images/face/20.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1151, ''1e345b472299bbc72d854fea14377b19'', ''./public/static/plug/layui/images/face/17.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1152, ''dcc636d88b53e73852db566a4d9d2f20'', ''./public/static/plug/layui/images/face/31.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1153, ''9044159f1635cce276f79f2750e44bab'', ''./public/static/plug/layui/images/face/24.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1154, ''b72ecabbadc13888d4775ec53c9150f8'', ''./public/static/plug/layui/images/face/46.gif'', ''1536871687'', ''1536288162'', ''1536288162''),
(1155, ''f81ed31a2829c0609354f25e1da62006'', ''./public/static/plug/layui/images/face/18.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1156, ''c6c1e6c64c16d3747e251200e014f219'', ''./public/static/plug/layui/images/face/51.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1157, ''15bd343617ec5ea02eedfdfe575c656c'', ''./public/static/plug/layui/images/face/38.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1158, ''92bf7127158347196c4f9aef0d0ab301'', ''./public/static/plug/layui/images/face/9.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1159, ''55ff58341b23eb412186f8e89963f3ed'', ''./public/static/plug/layui/images/face/25.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1160, ''ed40b41d05a3020271be545a607d6d78'', ''./public/static/plug/layui/images/face/28.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1161, ''e37c81739515617cf5bc51232ad873b7'', ''./public/static/plug/layui/images/face/57.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1162, ''8ede6bd1d173ddeb6f3f4b241c8f3513'', ''./public/static/plug/layui/images/face/47.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1163, ''3880bad4694b3ef38e2e58be68b875ef'', ''./public/static/plug/layui/images/face/7.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1164, ''aec972de4da971327e4452c5b4b5fda1'', ''./public/static/plug/layui/images/face/2.gif'', ''1536871688'', ''1536288162'', ''1536288162''),
(1165, ''a27ed9871926a8e37c522f6c1542df9a'', ''./public/static/plug/layui/images/face/65.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1166, ''611117291370fea2ceac62c29e3895bd'', ''./public/static/plug/layui/images/face/10.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1167, ''d17783318c1587204b6155c2fa9986fe'', ''./public/static/plug/layui/images/face/45.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1168, ''4d2933c0445dc04bdaaad41f2b557137'', ''./public/static/plug/layui/images/face/4.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1169, ''21069e4a6b8a4f6a0af40c87a168f321'', ''./public/static/plug/layui/images/face/49.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1170, ''f81a7f4f93f254d58c0e943a07d2cc0b'', ''./public/static/plug/layui/images/face/34.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1171, ''7873504cf41c6bf81dc385e7a63e06c2'', ''./public/static/plug/layui/images/face/22.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1172, ''02709c83458b72b0c73585297cc291f3'', ''./public/static/plug/layui/images/face/66.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1173, ''bf97be0b15ace15dedf22f266a5c429c'', ''./public/static/plug/layui/images/face/3.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1174, ''bc06dab3c63c4e2300c5cd4524819370'', ''./public/static/plug/layui/images/face/53.gif'', ''1536871689'', ''1536288162'', ''1536288162''),
(1175, ''4fb6439d891b34c4936ae34a79725b59'', ''./public/static/plug/layui/images/face/29.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1176, ''1cb4f698dd69602dd6f6eff121663a17'', ''./public/static/plug/layui/images/face/59.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1177, ''aff05197849e8c8f55b2d5fe56bb56f7'', ''./public/static/plug/layui/images/face/54.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1178, ''0b7ff8f1bbb91be880ef190767774c78'', ''./public/static/plug/layui/images/face/30.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1179, ''8c6f0b4cdd0fa9d68205bab3d8df29e4'', ''./public/static/plug/layui/images/face/56.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1180, ''7e64a36433e1f756dafc74093e71c678'', ''./public/static/plug/layui/images/face/26.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1181, ''877ff95213ae5c45721761c540810053'', ''./public/static/plug/layui/images/face/69.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1182, ''c0ed0920ba0d752ad77aca762050b12d'', ''./public/static/plug/layui/images/face/62.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1183, ''55a42691f8560bf2fbfed7c19389e4cf'', ''./public/static/plug/layui/images/face/71.gif'', ''1536871690'', ''1536288162'', ''1536288162''),
(1184, ''89d291439d6eae4b2db6746e589f6134'', ''./public/static/plug/layui/images/face/58.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1185, ''485a20018f6fd278510c2953697ba65c'', ''./public/static/plug/layui/images/face/15.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1186, ''b7f69e6df691b1c885f541ec604c59df'', ''./public/static/plug/layui/images/face/70.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1187, ''96664d71d30c946747bcc651d085ed7a'', ''./public/static/plug/layui/images/face/50.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1188, ''4bde9630ca80c61063813274729af4ba'', ''./public/static/plug/layui/images/face/61.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1189, ''f24d53dee3bd1050b26d6d7cd5bca68d'', ''./public/static/plug/layui/images/face/42.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1190, ''8ae6b6ec8d2941a6adaee9555839e81c'', ''./public/static/plug/layui/images/face/5.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1191, ''71712fa565d89315b6ef2a45b3d581b5'', ''./public/static/plug/layui/images/face/60.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1192, ''959bacfef9ac0d3bb275504623c62375'', ''./public/static/plug/layui/images/face/64.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1193, ''d7bdca562fd0b53f816eedb23148e158'', ''./public/static/plug/layui/images/face/55.gif'', ''1536871691'', ''1536288162'', ''1536288162''),
(1194, ''4c9e106e702751cb61861778269e2b26'', ''./public/static/plug/layui/images/face/37.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1195, ''fa7da45a4cf11818476a64b11972beba'', ''./public/static/plug/layui/images/face/19.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1196, ''84b3bd065735379c7ebf902bd356eb24'', ''./public/static/plug/layui/images/face/23.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1197, ''21ed3c01c99c75162cc7d5c09e557468'', ''./public/static/plug/layui/images/face/21.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1198, ''cc8ff0383ce624c0721682aaa500b672'', ''./public/static/plug/layui/images/face/35.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1199, ''913e2f3dd1962d2a80b4ae66ddddceb3'', ''./public/static/plug/layui/images/face/1.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1200, ''c30514bf7d87214840be9409e0543aa9'', ''./public/static/plug/layui/images/face/14.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1201, ''a06a2e3ed6da3796fbb731367d039a0a'', ''./public/static/plug/layui/images/face/0.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1202, ''8bfcb8fe88a3b666b5460743fb2cdc49'', ''./public/static/plug/layui/images/face/8.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1203, ''ab1c7c7a43aa6f43963c7b0afc9ec787'', ''./public/static/plug/layui/images/face/67.gif'', ''1536871692'', ''1536288162'', ''1536288162''),
(1204, ''8d9e9cc9b52ca46e854480fa4900158e'', ''./public/static/plug/layui/images/face/32.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1205, ''edfe69e1b1f20cfa8110d9e9d2536c68'', ''./public/static/plug/layui/images/face/39.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1206, ''7621ac2727678687a5318762f580142e'', ''./public/static/plug/layui/images/face/12.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1207, ''9946d6232e406ba2c10b60221c5b698b'', ''./public/static/plug/layui/images/face/6.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1208, ''599c45d8a2832cc01617fb42091993df'', ''./public/static/plug/layui/images/face/43.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1209, ''645be9a08ae4d8aac61e6b5fb47215e5'', ''./public/static/plug/layui/images/face/36.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1210, ''3d4ee858ebba0e46a7850e13185b7c9b'', ''./public/static/plug/layui/images/face/16.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1211, ''b0f285b595f10a1277774bf6844da76b'', ''./public/static/plug/layui/images/face/44.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1212, ''199be3fbe9ed7e5191c8635e05bcc0e9'', ''./public/static/plug/layui/images/face/11.gif'', ''1536871693'', ''1536288162'', ''1536288162''),
(1213, ''8ba31474130566d0d42a0656b86d3c64'', ''./public/static/plug/swiper/swiper-3.4.1.jquery.min.js'', ''1536871695'', ''1536288162'', ''1536288162''),
(1214, ''0176bf1163b6f65f3c8cf11cd367e67c'', ''./public/static/plug/swiper/swiper-3.4.1.min.css'', ''1536871695'', ''1536288162'', ''1536288162''),
(1215, ''9571a2b9196123c124e88bf55a16ed90'', ''./public/static/plug/swiper/swiper.jquery.js'', ''1536871696'', ''1536288162'', ''1536288162''),
(1216, ''6e80f0cff749c82653b9cdde9eeab937'', ''./public/static/plug/layer/layer.js'', ''1536871696'', ''1536288162'', ''1536288162''),
(1217, ''5eed218554f21c8fcb6d0f53d75ec8b0'', ''./public/static/plug/layer/layer-compiled.js'', ''1536871696'', ''1536288162'', ''1536288162''),
(1218, ''50c5e3e79b276c92df6cc52caeb464f0'', ''./public/static/plug/layer/theme/default/loading-2.gif'', ''1536871697'', ''1536288162'', ''1536288162''),
(1219, ''a72011ccdc2bcd23ba440f104c416193'', ''./public/static/plug/layer/theme/default/loading-0.gif'', ''1536871697'', ''1536288162'', ''1536288162''),
(1220, ''3d2e0d91c5c0b96abb8dbdc2234aba77'', ''./public/static/plug/layer/theme/default/layer.css'', ''1536871697'', ''1536288162'', ''1536288162''),
(1221, ''551539f873d9ebe0792b120a9867d399'', ''./public/static/plug/layer/theme/default/icon.png'', ''1536871697'', ''1536288162'', ''1536288162''),
(1222, ''ba81b24c06e2e0eac1e219405b33766b'', ''./public/static/plug/layer/theme/default/icon-ext.png'', ''1536871697'', ''1536288162'', ''1536288162''),
(1223, ''1140bc5c7863f8e54a3c2b179e640758'', ''./public/static/plug/layer/theme/default/loading-1.gif'', ''1536871697'', ''1536288162'', ''1536288162''),
(1224, ''cd07461cfdea4bd644f0e0b0bfbf54a1'', ''./public/static/plug/layer/layer-compiled.js.map'', ''1536871699'', ''1536288162'', ''1536288162''),
(1225, ''2028e407c22ee7a12b05a35ee9c71882'', ''./public/static/plug/layer/mobile/layer.js'', ''1536871699'', ''1536288162'', ''1536288162''),
(1226, ''3859550db3293c2e669a440a80e081b3'', ''./public/static/plug/layer/mobile/layer-compiled.js'', ''1536871699'', ''1536288162'', ''1536288162''),
(1227, ''633915e62d14a714594b95b974ee0836'', ''./public/static/plug/layer/mobile/need/layer.css'', ''1536871699'', ''1536288162'', ''1536288162''),
(1228, ''f86c47baefd32fded3eea00b502e802f'', ''./public/static/plug/layer/mobile/layer-compiled.js.map'', ''1536871700'', ''1536288162'', ''1536288162''),
(1229, ''2a1f285b602aa76ca45d0bf045ea0ea4'', ''./public/static/plug/moment.js'', ''1536871701'', ''1536288162'', ''1536288162''),
(1230, ''524e37e86d1add9491b552e36cb66fe3'', ''./public/static/js/media.js'', ''1536871702'', ''1536288162'', ''1536288162''),
(1231, ''21806dcf07b06f561e83981879e991d0'', ''./public/wap/crmeb/module/refund-reason.js'', ''1536871731'', ''1536288162'', ''1536288162''),
(1232, ''dad622705b398bc7a01154388a41ab36'', ''./public/wap/crmeb/module/store.js'', ''1536871731'', ''1536288162'', ''1536288162''),
(1233, ''061beb21d3fe56f42db2db12f1fe6ab8'', ''./public/wap/crmeb/module/store/use-address.js'', ''1536871732'', ''1536288162'', ''1536288162''),
(1234, ''9e9c3925fe78b39f0f919a2478b08779'', ''./public/wap/crmeb/module/store/scroll-load.js'', ''1536871732'', ''1536288162'', ''1536288162''),
(1235, ''d78b17fec2c20006b8a3ff421c6185b0'', ''./public/wap/crmeb/module/store/seckill-card.js'', ''1536871732'', ''1536288162'', ''1536288162''),
(1236, ''3d093a2ed730902107a3324abf141044'', ''./public/wap/crmeb/module/store/use-coupon.js'', ''1536871732'', ''1536288162'', ''1536288162''),
(1237, ''abc3a549d19c6ff66315901780796f75'', ''./public/wap/crmeb/module/store/shop-card.js'', ''1536871732'', ''1536288162'', ''1536288162''),
(1238, ''68c20c7eed5ae2919667a4fcf0bd5cab'', ''./public/wap/crmeb/module/cart.js'', ''1536871733'', ''1536288162'', ''1536288162''),
(1239, ''cedfc2e0b18a63ab39383ee3c4b9dd15'', ''./public/wap/crmeb/module/store_service/mobile.js'', ''1536871733'', ''1536288162'', ''1536288162''),
(1240, ''3fd2f2e9ab37ba9e2b20d47fcd6ee39c'', ''./public/wap/crmeb/module/store_service/unslider.js'', ''1536871733'', ''1536288162'', ''1536288162''),
(1241, ''611bcada29dc1690bd362325e4e01484'', ''./public/wap/crmeb/module/store_service/jquery.touchSwipe.min.js'', ''1536871733'', ''1536288162'', ''1536288162''),
(1242, ''e8cdc98d1a718ec002f0ba18f2edac0c'', ''./public/wap/crmeb/module/store_service/moment.min.js'', ''1536871733'', ''1536288162'', ''1536288162''),
(1243, ''08ecb77133008a15f48e14f791e8fb4c'', ''./public/wap/crmeb/module/store_service/msn.js'', ''1536871733'', ''1536288162'', ''1536288162''),
(1244, ''2aa3bf97cd455499c2117dfeb6458c8c'', ''./public/wap/crmeb/js/base.js'', ''1536871735'', ''1536288162'', ''1536288162''),
(1245, ''ef0037be486a4947a22527585034ab01'', ''./public/system/module/widget/images.js'', ''1536871737'', ''1536288162'', ''1536288162''),
(1246, ''901fd7ec68940f7fe9d260adfa6b3a6b'', ''./public/system/module/error/css/style.css'', ''1536871738'', ''1536288162'', ''1536288162''),
(1247, ''ab66401d78013fb07912c7460c438411'', ''./public/system/module/error/css/reset-2.0.css'', ''1536871738'', ''1536288162'', ''1536288162''),
(1248, ''afaf0c6951fc2290ec2e0d6a72f3d0fd'', ''./public/system/module/error/images/refresh-iocn.png'', ''1536871739'', ''1536288162'', ''1536288162''),
(1249, ''b762076fd5aaaceec3e9515f15d2c3e8'', ''./public/system/module/error/images/03.png'', ''1536871739'', ''1536288162'', ''1536288162''),
(1250, ''3e9fb8ecd1a45951a971a95bc9a2c25f'', ''./public/system/module/error/images/02.png'', ''1536871739'', ''1536288162'', ''1536288162''),
(1251, ''499506ace3f5de5b42f478122c49e92a'', ''./public/system/module/error/images/failure-icon.png'', ''1536871739'', ''1536288162'', ''1536288162''),
(1252, ''cd64fd136d56db19dc6f5e0c279e6c56'', ''./public/system/module/error/images/01.png'', ''1536871739'', ''1536288162'', ''1536288162''),
(1253, ''e573789ff03ad5b05e1f9a5bf2e48ca8'', ''./public/system/module/error/images/01.jpg'', ''1536871739'', ''1536288162'', ''1536288162''),
(1254, ''79d81eb10e31cb868dcfc1c320e90a0d'', ''./public/system/module/error/images/04.png'', ''1536871739'', ''1536288162'', ''1536288162''),
(1255, ''0c654a24811c8ce32f964b9a32efff86'', ''./public/system/module/error/images/back-icon.png'', ''1536871739'', ''1536288162'', ''1536288162''),
(1256, ''0e3458c75b0f32c7c733c1eb1236d58f'', ''./public/system/module/exception/css/style.css'', ''1536871741'', ''1536288162'', ''1536288162''),
(1257, ''8671869d7112ee5f5bdd69e8f9e5f9b4'', ''./public/system/module/exception/images/errorPageBorder.png'', ''1536871742'', ''1536288162'', ''1536288162''),
(1258, ''ab66401d78013fb07912c7460c438411'', ''./public/system/module/success/css/reset-2.0.css'', ''1536871743'', ''1536288162'', ''1536288162''),
(1259, ''f62d65954ec04e247e1a4512c2c4a801'', ''./public/system/module/success/images/success-icon.png'', ''1536871744'', ''1536288162'', ''1536288162''),
(1260, ''e92a8b840286ecc78a6f31e4eb9c6017'', ''./public/system/module/index/index.js'', ''1536871745'', ''1536288162'', ''1536288162''),
(1261, ''7c41cfe020b00d938588bdcf3c719fb0'', ''./public/system/module/wechat/news_category/css/style.css'', ''1536871746'', ''1536288162'', ''1536288162''),
(1262, ''a03b020d23eb70f4b64b87d7a7bb91c7'', ''./public/system/module/wechat/news/css/index.css'', ''1537004373'', ''1536288162'', ''1536288162''),
(1263, ''35398d4c5e0dbcdda859a36d9a7401bf'', ''./public/system/module/wechat/news/css/style.css'', ''1536871748'', ''1536288162'', ''1536288162''),
(1264, ''6b00566e6a7a54df0b83fe8a1d8b9427'', ''./public/system/module/wechat/news/images/image.png'', ''1536871749'', ''1536288162'', ''1536288162''),
(1265, ''a13709ab6ab773723df82a3c2492c932'', ''./public/system/module/login/index.js'', ''1536871751'', ''1536288162'', ''1536288162''),
(1266, ''a6d4dc47c895aea5534176eef23b96d0'', ''./public/system/css/layui-admin.css'', ''1536871752'', ''1536288162'', ''1536288162''),
(1267, ''389336103c1ac9b5b6ca973ab5708c11'', ''./public/system/css/main.css'', ''1536871752'', ''1536288162'', ''1536288162''),
(1268, ''8c27656622789cbdd96000881a4e77c7'', ''./public/system/util/mpFrame.js'', ''1536871756'', ''1536288162'', ''1536288162''),
(1269, ''db1a2d97ae205b6ed496ec452a0b3ad6'', ''./public/system/util/mpTableBuilder-compiled.js'', ''1536871756'', ''1536288162'', ''1536288162''),
(1270, ''46661f839a096c9473a711c29bf21676'', ''./public/system/util/mpFormBuilder-compiled.js'', ''1536871756'', ''1536288162'', ''1536288162''),
(1271, ''a696954c65b196751a086615938844aa'', ''./public/system/util/mpVuePackage-compiled.js'', ''1536871756'', ''1536288162'', ''1536288162''),
(1272, ''eb72d949b566cd8c6e14fa6662d6c65a'', ''./public/system/util/mpVuePackage.js'', ''1536871756'', ''1536288162'', ''1536288162''),
(1273, ''184fcd1d7a8bad538700f1cf22431d8b'', ''./public/system/util/mpFrame-compiled.js'', ''1536871756'', ''1536288162'', ''1536288162''),
(1274, ''5a67078b85e5e39f7f6d0b06d8cad0d9'', ''./public/system/util/mpTableBuilder.js'', ''1536871756'', ''1536288162'', ''1536288162''),
(1275, ''6773515c82883df04709d41fa8301b66'', ''./public/system/util/mpBuilder-compiled.js'', ''1536871757'', ''1536288162'', ''1536288162''),
(1276, ''01c3bf7bde5629ba5bdf220dff1e8fd4'', ''./public/system/util/mpFormBuilder-bak-compiled.js'', ''1536871757'', ''1536288162'', ''1536288162''),
(1277, ''7c42b19c9f160641428694f2c6eabf0d'', ''./public/system/util/mpVueComponent.js'', ''1536871757'', ''1536288162'', ''1536288162''),
(1278, ''7722f9723b073b00de840f7010bc71bd'', ''./public/system/util/mpHelper.js'', ''1536871757'', ''1536288162'', ''1536288162''),
(1279, ''9c7ebd0f3f2b72e1dbc39b532a23b7a2'', ''./public/system/plug/form-create/form-create.js'', ''1536871758'', ''1536288162'', ''1536288162''),
(1280, ''bb756f307a8166dfc34df1e77291d713'', ''./public/system/plug/umeditor/umeditor.js'', ''1536871758'', ''1536288162'', ''1536288162''),
(1281, ''e0a1a76441b4da770097e1af0a650b93'', ''./public/system/plug/umeditor/lang/zh-cn/images/upload.png'', ''1536871759'', ''1536288162'', ''1536288162''),
(1282, ''c754e6ca1921cd639739499d3cf45875'', ''./public/system/plug/umeditor/lang/zh-cn/images/localimage.png'', ''1536871759'', ''1536288162'', ''1536288162''),
(1283, ''40644255bb10f102763cbce4a3a2f7d9'', ''./public/system/plug/umeditor/lang/zh-cn/images/copy.png'', ''1536871759'', ''1536288162'', ''1536288162''),
(1284, ''6d299069db6f24cf2ba1a90a64b49db7'', ''./public/system/plug/umeditor/lang/zh-cn/images/music.png'', ''1536871759'', ''1536288162'', ''1536288162''),
(1285, ''a4d92a42a41238e43e2874c2c31582f9'', ''./public/system/plug/umeditor/lang/zh-cn/images/imglabel.png'', ''1536871759'', ''1536288162'', ''1536288162''),
(1286, ''0536481fe18c84e2a393259bda2f381d'', ''./public/system/plug/umeditor/lang/zh-cn/zh-cn.js'', ''1536871760'', ''1536288162'', ''1536288162''),
(1287, ''66cd701eef0e71bce692429f0ca90c22'', ''./public/system/plug/umeditor/lang/en/en.js'', ''1536871761'', ''1536288162'', ''1536288162''),
(1288, ''34206a03b2459da6ad36ff6ad2998fa0'', ''./public/system/plug/umeditor/lang/en/images/rotaterightdisable.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1289, ''9da36dab96ef97bf14115b4bd5169e78'', ''./public/system/plug/umeditor/lang/en/images/upload.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1290, ''6cae1397f4ae4f052293ca7a42fdf16c'', ''./public/system/plug/umeditor/lang/en/images/rotateleftdisable.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1291, ''98b6c213a9b89b7959da7aeb7368c738'', ''./public/system/plug/umeditor/lang/en/images/localimage.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1292, ''9e6628c34db960d682a591bc24d4f557'', ''./public/system/plug/umeditor/lang/en/images/rotateleftenable.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1293, ''1eb887698a395ffb7f1a6175d05442af'', ''./public/system/plug/umeditor/lang/en/images/alldeletebtnhoverskin.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1294, ''6d7265b07429ceca1b03fce1e9266e14'', ''./public/system/plug/umeditor/lang/en/images/alldeletebtnupskin.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1295, ''88e7d05b61025278ff1b1230cfd21aa5'', ''./public/system/plug/umeditor/lang/en/images/addimage.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1296, ''b512aa9fa0ee7783ff516f9f0828b060'', ''./public/system/plug/umeditor/lang/en/images/copy.png'', ''1536871761'', ''1536288162'', ''1536288162''),
(1297, ''bfc1b0155bfe9e60373c6e7f131f2771'', ''./public/system/plug/umeditor/lang/en/images/rotaterightenable.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1298, ''2cd78f0b4eb01b8f00a44bfb029e3824'', ''./public/system/plug/umeditor/lang/en/images/music.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1299, ''b012453148feba7207940356f0db91e2'', ''./public/system/plug/umeditor/lang/en/images/deleteenable.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1300, ''dfa3aef5fe3087a5450753aa28529304'', ''./public/system/plug/umeditor/lang/en/images/button.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1301, ''3ad9255e6398f1694395b0e0c3d330a4'', ''./public/system/plug/umeditor/lang/en/images/listbackground.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1302, ''4c5b9e9ad29724e8a1296059523d56f5'', ''./public/system/plug/umeditor/lang/en/images/deletedisable.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1303, ''d3320c66e053049d1fed97de1422006b'', ''./public/system/plug/umeditor/lang/en/images/background.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1304, ''89afeb92719844076f19f20c03331226'', ''./public/system/plug/umeditor/lang/en/images/imglabel.png'', ''1536871762'', ''1536288162'', ''1536288162''),
(1305, ''0aad127afb5d3d45bc734158c83ad30c'', ''./public/system/plug/umeditor/themes/default/css/umeditor.css'', ''1536871765'', ''1536288162'', ''1536288162''),
(1306, ''4728fa5d5a548462b0df0cc3638fb02d'', ''./public/system/plug/umeditor/themes/default/css/umeditor.min.css'', ''1536871765'', ''1536288162'', ''1536288162''),
(1307, ''9b8e2f524d19c85e0026a33796182115'', ''./public/system/plug/umeditor/themes/default/images/close.png'', ''1536871765'', ''1536288162'', ''1536288162''),
(1308, ''45c75be70f44e5712f166d22f0a2504e'', ''./public/system/plug/umeditor/themes/default/images/ok.gif'', ''1536871766'', ''1536288162'', ''1536288162''),
(1309, ''c4f73e335fcc0b33db904909ae99475e'', ''./public/system/plug/umeditor/themes/default/images/icons.png'', ''1536871766'', ''1536288162'', ''1536288162''),
(1310, ''f857581368e75fcada43649be5de483b'', ''./public/system/plug/umeditor/themes/default/images/videologo.gif'', ''1536871766'', ''1536288162'', ''1536288162''),
(1311, ''5f8a63bf407967d5ac160c839d50aabe'', ''./public/system/plug/umeditor/themes/default/images/icons.gif'', ''1536871766'', ''1536288162'', ''1536288162''),
(1312, ''df3e567d6f16d040326c7a0ea29a4f41'', ''./public/system/plug/umeditor/themes/default/images/spacer.gif'', ''1536871766'', ''1536288162'', ''1536288162''),
(1313, ''84941c498e2abb7988d343d9df530077'', ''./public/system/plug/umeditor/themes/default/images/caret.png'', ''1536871766'', ''1536288162'', ''1536288162''),
(1314, ''fa743a8e18903aa44727fdda3dad4807'', ''./public/system/plug/umeditor/themes/default/images/pop-bg.png'', ''1536871766'', ''1536288162'', ''1536288162''),
(1315, ''258c0588e53f292f3bac2f4eb4253d39'', ''./public/system/plug/umeditor/dialogs/map/map.html'', ''1536871768'', ''1536288162'', ''1536288162''),
(1316, ''007b2f0a48f50424adb9729eea38ade5'', ''./public/system/plug/umeditor/dialogs/map/map.js'', ''1536871770'', ''1536288162'', ''1536288162''),
(1317, ''a31a4f477981ae56e36b9337455355f1'', ''./public/system/plug/umeditor/dialogs/formula/formula.html'', ''1536871771'', ''1536288162'', ''1536288162''),
(1318, ''b485ad9dca112e4cebe66de8923f59b2'', ''./public/system/plug/umeditor/dialogs/formula/formula.css'', ''1536871771'', ''1536288162'', ''1536288162''),
(1319, ''f11cc42b343d3dd137b23e4ef9de76d9'', ''./public/system/plug/umeditor/dialogs/formula/images/formula.png'', ''1536871771'', ''1536288162'', ''1536288162''),
(1320, ''a10cf46d604d95ceea7b27d4ca3bc1f5'', ''./public/system/plug/umeditor/dialogs/formula/formula.js'', ''1536871772'', ''1536288162'', ''1536288162''),
(1321, ''5c9d2e4f5d64f397b50715b7730268bf'', ''./public/system/plug/umeditor/dialogs/video/video.js'', ''1536871773'', ''1536288162'', ''1536288162''),
(1322, ''17e1af76de01403df026af28cc4aecda'', ''./public/system/plug/umeditor/dialogs/video/images/right_focus.jpg'', ''1536871773'', ''1536288162'', ''1536288162''),
(1323, ''85b08393f830bcc62c1376252b807f81'', ''./public/system/plug/umeditor/dialogs/video/images/none_focus.jpg'', ''1536871773'', ''1536288162'', ''1536288162''),
(1324, ''e6f556abcbe48e0115995bcc106a8531'', ''./public/system/plug/umeditor/dialogs/video/images/left_focus.jpg'', ''1536871773'', ''1536288162'', ''1536288162''),
(1325, ''13813ba01bf8267721a8a9d9ea56bf90'', ''./public/system/plug/umeditor/dialogs/video/images/center_focus.jpg'', ''1536871773'', ''1536288162'', ''1536288162''),
(1326, ''8ea7009849ed2eb55f6b2305e00d7350'', ''./public/system/plug/umeditor/dialogs/video/video.css'', ''1536871774'', ''1536288162'', ''1536288162''),
(1327, ''18571dbbe29b49ba038ce1d24c88674e'', ''./public/system/plug/umeditor/dialogs/link/link.js'', ''1536871775'', ''1536288162'', ''1536288162''),
(1328, ''2986171b3f9f5967e0faa144eaacd222'', ''./public/system/plug/umeditor/dialogs/emotion/emotion.css'', ''1536871776'', ''1536288162'', ''1536288162''),
(1329, ''43c43aada4dd1ec8bc352f092e39c7b0'', ''./public/system/plug/umeditor/dialogs/emotion/images/yface.gif'', ''1536871776'', ''1536288162'', ''1536288162''),
(1330, ''1085988d048e25ad630451eba57dc09d'', ''./public/system/plug/umeditor/dialogs/emotion/images/jxface2.gif'', ''1536871776'', ''1536288162'', ''1536288162''),
(1331, ''30e42f9792a388ea7b049ee8715ce8fa'', ''./public/system/plug/umeditor/dialogs/emotion/images/tface.gif'', ''1536871776'', ''1536288162'', ''1536288162''),
(1332, ''5d39be760e912b058a42fc59b3731bec'', ''./public/system/plug/umeditor/dialogs/emotion/images/cface.gif'', ''1536871776'', ''1536288162'', ''1536288162''),
(1333, ''a4fc234a5ca005ba8845b36a09004738'', ''./public/system/plug/umeditor/dialogs/emotion/images/fface.gif'', ''1536871777'', ''1536288162'', ''1536288162''),
(1334, ''4869b022d6ba52d8c4312e9f40564efd'', ''./public/system/plug/umeditor/dialogs/emotion/images/neweditor-tab-bg.png'', ''1536871777'', ''1536288162'', ''1536288162''),
(1335, ''629ccc774aed95b2c6bec91151f7292d'', ''./public/system/plug/umeditor/dialogs/emotion/images/0.gif'', ''1536871777'', ''1536288162'', ''1536288162''),
(1336, ''647a02b861c53e54d603db363aeec236'', ''./public/system/plug/umeditor/dialogs/emotion/images/wface.gif'', ''1536871777'', ''1536288162'', ''1536288162''),
(1337, ''6ea3533c3b0adbe19467ebccd1a7afa1'', ''./public/system/plug/umeditor/dialogs/emotion/images/bface.gif'', ''1536871777'', ''1536288162'', ''1536288162''),
(1338, ''bbba1bb2a8e3845a4da9dfc37e9041d4'', ''./public/system/plug/umeditor/dialogs/emotion/emotion.js'', ''1536871778'', ''1536288162'', ''1536288162''),
(1339, ''25f2465d1d9ec6b9c3d3d089b8bc7405'', ''./public/system/plug/umeditor/dialogs/image/image.css'', ''1536871778'', ''1536288162'', ''1536288162''),
(1340, ''7b23a0c7d197e0c5557b714bd7161162'', ''./public/system/plug/umeditor/dialogs/image/images/close.png'', ''1536871779'', ''1536288162'', ''1536288162''),
(1341, ''50745e5aea6a0dd22ac107dc0d8c2216'', ''./public/system/plug/umeditor/dialogs/image/images/upload2.png'', ''1536871779'', ''1536288162'', ''1536288162''),
(1342, ''58a9aef441d29f473da8266a5b44e389'', ''./public/system/plug/umeditor/dialogs/image/images/upload1.png'', ''1536871779'', ''1536288162'', ''1536288162''),
(1343, ''a6b5ceede5de10ccede5842caafbf445'', ''./public/system/plug/umeditor/dialogs/image/image.js'', ''1536871780'', ''1536288162'', ''1536288162''),
(1344, ''1decfd5bb39b92078468e96255e5b2f2'', ''./public/system/plug/umeditor/umeditor.min.js'', ''1536871781'', ''1536288162'', ''1536288162''),
(1345, ''63247d4e8047fc210dfa4e412d279bb7'', ''./public/system/plug/umeditor/third-party/template.min.js'', ''1536871781'', ''1536288162'', ''1536288162''),
(1346, ''628072e7212db1e8cdacb22b21752cda'', ''./public/system/plug/umeditor/third-party/jquery.min.js'', ''1536871781'', ''1536288162'', ''1536288162''),
(1347, ''4621fcfd9def63c694914f7ec5add610'', ''./public/system/plug/umeditor/third-party/mathquill/font/Symbola.otf'', ''1536288162'', ''1536288162'', ''1536288162''),
(1348, ''b1af7bbd3cea93a60bf68cf571ad6cab'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/STIXFontLicense2010.txt'', ''1536871782'', ''1536288162'', ''1536288162''),
(1349, ''dc8d21944741d366179418bb17515edb'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbolita-webfont.woff'', ''1536871782'', ''1536288162'', ''1536288162''),
(1350, ''3ead76b2082228a75edf84c00c5477c7'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneral-webfont.eot'', ''1536288162'', ''1536288162'', ''1536288162''),
(1351, ''30f0f43b350904c1e58186674cf46306'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneral-webfont.svg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1352, ''a1f259dc8fd8263c926559da16c1d1ce'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbolita-webfont.ttf'', ''1536871782'', ''1536288162'', ''1536288162''),
(1353, ''bc5dcd8fd5b0922f2d219c9640903929'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralitalic-webfont.svg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1354, ''aed55dccb65ef93aa9e2ae02e604d534'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralitalic-webfont.woff'', ''1536871782'', ''1536288162'', ''1536288162''),
(1355, ''edc5cda09ec11f6bb35a68993af422db'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralitalic-webfont.ttf'', ''1536871782'', ''1536288162'', ''1536288162''),
(1356, ''179645aaa47e148ec0ec78a89ae6c7a0'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralitalic-webfont.eot'', ''1536871782'', ''1536288162'', ''1536288162''),
(1357, ''827773c0af0c03b4c944c10f2534a405'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbol-webfont.eot'', ''1536871782'', ''1536288162'', ''1536288162''),
(1358, ''39f79995d4f5c15cfa7d1e8a4ca0b122'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbol-webfont.svg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1359, ''f8a321822630f4adfe89eef680b5e33f'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbol-webfont.ttf'', ''1536871783'', ''1536288162'', ''1536288162''),
(1360, ''727a7fb84c1db602e74d8fb20714791c'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbolita-webfont.eot'', ''1536871783'', ''1536288162'', ''1536288162''),
(1361, ''26e3c55cff231fcc0826b8cca003e7f9'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneral-webfont.woff'', ''1536871783'', ''1536288162'', ''1536288162''),
(1362, ''7bcbb2bf1e04b2458e0fbba8bb182f4e'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbolita-webfont.svg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1363, ''2ca57df1ad9421422eebb36b03aceea9'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneral-webfont.ttf'', ''1536288162'', ''1536288162'', ''1536288162''),
(1364, ''ce70b34a2fd253deac2b7a294cd566c6'', ''./public/system/plug/umeditor/third-party/mathquill/font/stixgeneral-bundle/stixgeneralbol-webfont.woff'', ''1536871783'', ''1536288162'', ''1536288162''),
(1365, ''20db57ba32a046dfea3c30519898b278'', ''./public/system/plug/umeditor/third-party/mathquill/font/Symbola.svg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1366, ''e4ae9ff7ac2476ae421fc4278e5d3806'', ''./public/system/plug/umeditor/third-party/mathquill/font/Symbola.eot'', ''1536288162'', ''1536288162'', ''1536288162''),
(1367, ''52a6aac18ae26b6ecbd4f3a0d9579c9f'', ''./public/system/plug/umeditor/third-party/mathquill/font/Symbola.ttf'', ''1536288162'', ''1536288162'', ''1536288162''),
(1368, ''72981090d0240678c5d0a964fe29f082'', ''./public/system/plug/umeditor/third-party/mathquill/font/Symbola.woff'', ''1536288162'', ''1536288162'', ''1536288162''),
(1369, ''aeddfdf8062e887e58ba144baa73ee95'', ''./public/system/plug/umeditor/third-party/mathquill/mathquill.min.js'', ''1536871785'', ''1536288162'', ''1536288162''),
(1370, ''a13a67d334416c042d5d4508b0044c1f'', ''./public/system/plug/umeditor/third-party/mathquill/mathquill.js'', ''1536871785'', ''1536288162'', ''1536288162''),
(1371, ''c3deba51bd3bf360e8e79e368fb8571e'', ''./public/system/plug/umeditor/third-party/mathquill/mathquill.css'', ''1536871785'', ''1536288162'', ''1536288162''),
(1372, ''a49cc3e20ba5fba4400bd207f3388899'', ''./public/system/plug/umeditor/umeditor.config.js'', ''1536871787'', ''1536288162'', ''1536288162''),
(1373, ''5abd584d7a3167cc1c9c4ff23d350aeb'', ''./public/system/plug/umeditor/php/Uploader.class.php'', ''1536871787'', ''1536288162'', ''1536288162''),
(1374, ''79b4687609be6e1f8c948f246c668a21'', ''./public/system/plug/umeditor/php/imageUp.php'', ''1536871787'', ''1536288162'', ''1536288162''),
(1375, ''ae2992f67384cafed0bf8e42a9e4b75f'', ''./public/system/plug/umeditor/php/getContent.php'', ''1536871787'', ''1536288162'', ''1536288162''),
(1376, ''359a04c6af9d438c31908eef96e9a928'', ''./public/system/plug/iview/dist/iview.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1377, ''8ffbb5a88713c5d685d974a4fc839d40'', ''./public/system/plug/iview/dist/styles/iview.css'', ''1536871789'', ''1536288162'', ''1536288162''),
(1378, ''2c2ae068be3b089e0a5b59abb1831550'', ''./public/system/plug/iview/dist/styles/fonts/ionicons.eot'', ''1536871789'', ''1536288162'', ''1536288162''),
(1379, ''24712f6c47821394fba7942fbb52c3b2'', ''./public/system/plug/iview/dist/styles/fonts/ionicons.ttf'', ''1536871789'', ''1536288162'', ''1536288162''),
(1380, ''621bd386841f74e0053cb8e67f8a0604'', ''./public/system/plug/iview/dist/styles/fonts/ionicons.svg'', ''1536871789'', ''1536288162'', ''1536288162''),
(1381, ''05acfdb568b3df49ad31355b19495d4a'', ''./public/system/plug/iview/dist/styles/fonts/ionicons.woff'', ''1536871789'', ''1536288162'', ''1536288162''),
(1382, ''30173fd15782a5653e5c1b234521a959'', ''./public/system/plug/iview/dist/iview.min.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1383, ''ced8fbbbc266a42b4f20a0a909be2491'', ''./public/system/plug/iview/dist/locale/pt-PT.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1384, ''c052220f96815a15b1f68c1fd4f7ee12'', ''./public/system/plug/iview/dist/locale/id-ID.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1385, ''a147e39fc3f1fcc10d8d7b0d949ae123'', ''./public/system/plug/iview/dist/locale/ko-KR.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1386, ''5fad473ad29a2ca3005e81918eba4038'', ''./public/system/plug/iview/dist/locale/ja-JP.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1387, ''c0de3c748f7b067d4a08289176c38761'', ''./public/system/plug/iview/dist/locale/zh-CN.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1388, ''a4591f9285817b3096881112430e4505'', ''./public/system/plug/iview/dist/locale/de-DE.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1389, ''30c93bd10e9a17601a3120c633ce6e45'', ''./public/system/plug/iview/dist/locale/vi-VN.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1390, ''62c5469b2dc3d06e43775781fe345598'', ''./public/system/plug/iview/dist/locale/es-ES.js'', ''1536871791'', ''1536288162'', ''1536288162''),
(1391, ''7cd6a5caafff87f609ce78a076a44643'', ''./public/system/plug/iview/dist/locale/fr-FR.js'', ''1536871792'', ''1536288162'', ''1536288162''),
(1392, ''4333bc8adac238a5d020b396126bd42b'', ''./public/system/plug/iview/dist/locale/en-US.js'', ''1536871792'', ''1536288162'', ''1536288162''),
(1393, ''c10e6a171e98a3dac5f34f738c808a68'', ''./public/system/plug/iview/dist/locale/sv-SE.js'', ''1536871792'', ''1536288162'', ''1536288162''),
(1394, ''31ec5dbdd426ce6bfebc21c4c8858016'', ''./public/system/plug/iview/dist/locale/ru-RU.js'', ''1536871792'', ''1536288162'', ''1536288162'');
INSERT INTO `eb_system_file` (`id`, `cthash`, `filename`, `atime`, `mtime`, `ctime`) VALUES
(1395, ''f5ea82d56a1e757e17b25f0ab1bd407e'', ''./public/system/plug/iview/dist/locale/tr-TR.js'', ''1536871792'', ''1536288162'', ''1536288162''),
(1396, ''c8b21eba8334d31d063d2eeb8ccea94d'', ''./public/system/plug/iview/dist/locale/zh-TW.js'', ''1536871792'', ''1536288162'', ''1536288162''),
(1397, ''522aa18fb0e6fd7370aea1e2888d6a4b'', ''./public/system/plug/iview/dist/locale/pt-BR.js'', ''1536871792'', ''1536288162'', ''1536288162''),
(1398, ''b9c130e7f47765cadef3f4a80ad3b412'', ''./public/system/plug/requirejs/README.md'', ''1536871794'', ''1536288162'', ''1536288162''),
(1399, ''f051459ded214178b064b37209d9398c'', ''./public/system/plug/requirejs/bin/r.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1400, ''e7199843dfd445bb66ec816e98a03214'', ''./public/system/plug/requirejs/require.js'', ''1536871795'', ''1536288162'', ''1536288162''),
(1401, ''4bead4b1b0a417582825231dbfe121b1'', ''./public/system/plug/requirejs/package.json'', ''1536871795'', ''1536288162'', ''1536288162''),
(1402, ''037d997219804a79cea6540312fc8e0a'', ''./public/system/plug/vue/dist/vue.js'', ''1536871796'', ''1536288162'', ''1536288162''),
(1403, ''a64ac1319064e7e88d336ce95f667d52'', ''./public/system/plug/vue/dist/README.md'', ''1536871796'', ''1536288162'', ''1536288162''),
(1404, ''bd3852d9ff082206759b1d322eeccfe8'', ''./public/system/plug/vue/dist/vue.runtime.js'', ''1536871796'', ''1536288162'', ''1536288162''),
(1405, ''7f7d01342623404fe6dadc6bea8a5d22'', ''./public/system/plug/vue/dist/vue.esm.js'', ''1536871796'', ''1536288162'', ''1536288162''),
(1406, ''3901c2214f7cbdf0dc2c962e3cc1a5ad'', ''./public/system/plug/vue/dist/vue.runtime.min.js'', ''1536871797'', ''1536288162'', ''1536288162''),
(1407, ''917f70e72ec5e171ea46987517925f1e'', ''./public/system/plug/vue/dist/vue.runtime.common.js'', ''1536871797'', ''1536288162'', ''1536288162''),
(1408, ''9eb63dbfb2badb381a3b6892b4da9f04'', ''./public/system/plug/vue/dist/vue.runtime.esm.js'', ''1536871797'', ''1536288162'', ''1536288162''),
(1409, ''55aa848bc74dde42637d7ae96f38ec01'', ''./public/system/plug/vue/dist/vue.common.js'', ''1536871797'', ''1536288162'', ''1536288162''),
(1410, ''7e052e2850e70a8db1bd837e08ddda83'', ''./public/system/plug/vue/dist/vue.min.js'', ''1536871797'', ''1536288162'', ''1536288162''),
(1411, ''e56d8571fcc081901ebc3eca11f6ac70'', ''./public/system/plug/validate/jquery.validate.js'', ''1536871798'', ''1536288162'', ''1536288162''),
(1412, ''02e8382fbd4d175817d43aeb99672c0a'', ''./public/system/images/bg-logo.jpg'', ''1536871800'', ''1536288162'', ''1536288162''),
(1413, ''0045fd55170554cbf71bf895cda72ff3'', ''./public/system/images/logo.png'', ''1537003225'', ''1536288162'', ''1536288162''),
(1414, ''993d2fdda8e32a49c8696549a42b8262'', ''./public/system/images/index.png'', ''1536871800'', ''1536288162'', ''1536288162''),
(1415, ''cfd4b538dc1d8b96a09310cab5fa44c9'', ''./public/system/images/head.gif'', ''1536871800'', ''1536288162'', ''1536288162''),
(1416, ''a7c10ef75327c9c51401b8acae1454bf'', ''./public/system/images/001.jpg'', ''1536871800'', ''1536288162'', ''1536288162''),
(1417, ''c3e4db490cc2e3de60290ffefb3c4127'', ''./public/system/images/admin_logo.png'', ''1536871800'', ''1536289570'', ''1536289570''),
(1418, ''1c93128220dc4933a1698b5101140b07'', ''./public/system/images/mobile_head.png'', ''1536871800'', ''1536288162'', ''1536288162''),
(1419, ''544fc30e6b6e9d334d81dac6a47cd65b'', ''./public/system/images/mobile_foot.png'', ''1536871801'', ''1536288162'', ''1536288162''),
(1420, ''4d5ad1ad526ac91ee497c2ccf5f5a7ba'', ''./public/system/images/error.png'', ''1536871801'', ''1536288162'', ''1536288162''),
(1421, ''9274f7e22858c923505a96d173d42a04'', ''./public/system/frame/plugins/fullavatareditor/scripts/swfobject.js.b'', ''1536871802'', ''1536288162'', ''1536288162''),
(1422, ''d9ca31ee1198e519a7425ac7edca24bf'', ''./public/system/frame/plugins/fullavatareditor/scripts/fullAvatarEditor.js.b'', ''1536871802'', ''1536288162'', ''1536288162''),
(1423, ''6df60685d380a26bd8ca2671ec564390'', ''./public/system/frame/plugins/fullavatareditor/scripts/jQuery.Cookie.js'', ''1536871802'', ''1536288162'', ''1536288162''),
(1424, ''64839e3a297f985c09c342ff4a36aef1'', ''./public/system/frame/plugins/fullavatareditor/scripts/demo.js'', ''1536871802'', ''1536288162'', ''1536288162''),
(1425, ''982fd962544bcab09f6c1d5ace746842'', ''./public/system/frame/plugins/fullavatareditor/scripts/test.js'', ''1536871802'', ''1536288162'', ''1536288162''),
(1426, ''d9ca31ee1198e519a7425ac7edca24bf'', ''./public/system/frame/plugins/fullavatareditor/scripts/fullAvatarEditor.js'', ''1536871802'', ''1536288162'', ''1536288162''),
(1427, ''9274f7e22858c923505a96d173d42a04'', ''./public/system/frame/plugins/fullavatareditor/scripts/swfobject.js'', ''1536871802'', ''1536288162'', ''1536288162''),
(1428, ''e57b57a54fa35dbb5a228e00d6c1c16e'', ''./public/system/frame/plugins/fullavatareditor/fullAvatarEditor.swf'', ''1536871803'', ''1536288162'', ''1536288162''),
(1429, ''204f4711f899c1aa766791daafc7a552'', ''./public/system/frame/plugins/fullavatareditor/expressInstall.swf'', ''1536871803'', ''1536288162'', ''1536288162''),
(1430, ''89514cd05fbd68ec5886d590dcc6b185'', ''./public/system/frame/css/plugins/sweetalert/sweetalert.css'', ''1536871804'', ''1536288162'', ''1536288162''),
(1431, ''545ccf313793f6389ddbc8f5c083b661'', ''./public/system/frame/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css'', ''1536871805'', ''1536288162'', ''1536288162''),
(1432, ''66e14215edc7e6532f9d2709c0945c1d'', ''./public/system/frame/css/plugins/colorpicker/css/bootstrap-colorpicker.min.css'', ''1536871806'', ''1536288162'', ''1536288162''),
(1433, ''df1e75c9de8c1b5b68f7144463afdb87'', ''./public/system/frame/css/plugins/colorpicker/img/bootstrap-colorpicker/hue-horizontal.png'', ''1536871807'', ''1536288162'', ''1536288162''),
(1434, ''de10f7b98e37a57ee81149a71d2c6106'', ''./public/system/frame/css/plugins/colorpicker/img/bootstrap-colorpicker/hue.png'', ''1536871807'', ''1536288162'', ''1536288162''),
(1435, ''512a83ac26d1574e25d742fe81cf531b'', ''./public/system/frame/css/plugins/colorpicker/img/bootstrap-colorpicker/saturation.png'', ''1536871807'', ''1536288162'', ''1536288162''),
(1436, ''10f4b956ec4d7e11c2b0c1cc11e18db1'', ''./public/system/frame/css/plugins/colorpicker/img/bootstrap-colorpicker/alpha.png'', ''1536871807'', ''1536288162'', ''1536288162''),
(1437, ''58fc83686953e32bce2b1e8d87438abc'', ''./public/system/frame/css/plugins/colorpicker/img/bootstrap-colorpicker/alpha-horizontal.png'', ''1536871807'', ''1536288162'', ''1536288162''),
(1438, ''f2f9e734fd10788522327d842386f625'', ''./public/system/frame/css/plugins/dropzone/basic.css'', ''1536871809'', ''1536288162'', ''1536288162''),
(1439, ''770cbf827f55a971a7b48c1b8f8d87fb'', ''./public/system/frame/css/plugins/dropzone/dropzone.css'', ''1536871810'', ''1536288162'', ''1536288162''),
(1440, ''88ebec8cd961f3559bce5faa8f9de79e'', ''./public/system/frame/css/plugins/chosen/chosen.css'', ''1536871810'', ''1536288162'', ''1536288162''),
(1441, ''25b9acb1b504c95c6b95c33986b7317e'', ''./public/system/frame/css/plugins/chosen/chosen-sprite.png'', ''1536871810'', ''1536288162'', ''1536288162''),
(1442, ''cb0d09c93b99c5cab6848147fdb3d7e4'', ''./public/system/frame/css/plugins/chosen/chosen-sprite_402x.png'', ''1536871811'', ''1536288162'', ''1536288162''),
(1443, ''a5f668eafe77e4972d39595522e9a123'', ''./public/system/frame/css/plugins/toastr/toastr.min.css'', ''1536871811'', ''1536288162'', ''1536288162''),
(1444, ''9ed4669f524bec38319be63a2ee4ba26'', ''./public/system/frame/css/plugins/jsTree/throbber.gif'', ''1536871812'', ''1536288162'', ''1536288162''),
(1445, ''1126cb51ec4bfcc2da03b55557e41d70'', ''./public/system/frame/css/plugins/jsTree/style.min.css'', ''1536871812'', ''1536288162'', ''1536288162''),
(1446, ''e86ef2ebbe960443d5dddcba6e398211'', ''./public/system/frame/css/plugins/jsTree/32px.png'', ''1536871812'', ''1536288162'', ''1536288162''),
(1447, ''52a229c8537ddc5b43f416599a142d95'', ''./public/system/frame/css/plugins/dataTables/dataTables.bootstrap.css'', ''1536871813'', ''1536288162'', ''1536288162''),
(1448, ''47a53108e8f4ac2e8546d8efc0ed0feb'', ''./public/system/frame/css/plugins/markdown/bootstrap-markdown.min.css'', ''1536871814'', ''1536288162'', ''1536288162''),
(1449, ''165ce368904f08e18472ea35a1bb74be'', ''./public/system/frame/css/plugins/treeview/bootstrap-treeview.css'', ''1536871815'', ''1536288162'', ''1536288162''),
(1450, ''bb00b8ef7102c035b361c1ec2f4e9dec'', ''./public/system/frame/css/plugins/blueimp/css/blueimp-gallery.min.css'', ''1536871816'', ''1536288162'', ''1536288162''),
(1451, ''a012413b54276e2eefd145c7aec60f93'', ''./public/system/frame/css/plugins/blueimp/img/play-pause.png'', ''1536871816'', ''1536288162'', ''1536288162''),
(1452, ''21dfa3149b274acb9c1819d342a6a169'', ''./public/system/frame/css/plugins/blueimp/img/play-pause.svg'', ''1536871816'', ''1536288162'', ''1536288162''),
(1453, ''19ee6b7e6642d75d6144b0c8209c93d6'', ''./public/system/frame/css/plugins/blueimp/img/error.svg'', ''1536871817'', ''1536288162'', ''1536288162''),
(1454, ''05992d3434d3589b38a3a5431842d38f'', ''./public/system/frame/css/plugins/blueimp/img/loading.gif'', ''1536871817'', ''1536288162'', ''1536288162''),
(1455, ''90901890fbf9b379405f47a23313e63b'', ''./public/system/frame/css/plugins/blueimp/img/error.png'', ''1536871817'', ''1536288162'', ''1536288162''),
(1456, ''9b737958b1644b46b23904e53afcac50'', ''./public/system/frame/css/plugins/blueimp/img/video-play.svg'', ''1536871817'', ''1536288162'', ''1536288162''),
(1457, ''288308b2037f409d293916c7a3913f20'', ''./public/system/frame/css/plugins/blueimp/img/video-play.png'', ''1536871817'', ''1536288162'', ''1536288162''),
(1458, ''371f99421c676859fba95267ad302398'', ''./public/system/frame/css/plugins/datapicker/datepicker3.css'', ''1536871819'', ''1536288162'', ''1536288162''),
(1459, ''ca70e29d4161ce4494199f2d088e98ca'', ''./public/system/frame/css/plugins/webuploader/webuploader.css'', ''1536871819'', ''1536288162'', ''1536288162''),
(1460, ''d71cae691ca0e97be4009049374124ae'', ''./public/system/frame/css/plugins/ionRangeSlider/ion.rangeSlider.css'', ''1536871820'', ''1536288162'', ''1536288162''),
(1461, ''b10247b2df69e4cc3bc8875cabb9a841'', ''./public/system/frame/css/plugins/ionRangeSlider/ion.rangeSlider.skinFlat.css'', ''1536871820'', ''1536288162'', ''1536288162''),
(1462, ''69d9cfa525284ae009eb9c797dc95cc1'', ''./public/system/frame/css/plugins/footable/footable.core.css'', ''1536871821'', ''1536288162'', ''1536288162''),
(1463, ''2256ee07c7a1a366e5bac1c3fcb8e216'', ''./public/system/frame/css/plugins/footable/fonts/footable.ttf'', ''1536871821'', ''1536288162'', ''1536288162''),
(1464, ''bdeee9eb7c9bc5677154b01df270924c'', ''./public/system/frame/css/plugins/footable/fonts/footable.svg'', ''1536871821'', ''1536288162'', ''1536288162''),
(1465, ''91c343856c56695b45993db2e1575519'', ''./public/system/frame/css/plugins/footable/fonts/footable.eot'', ''1536871821'', ''1536288162'', ''1536288162''),
(1466, ''634513dc791352157f12cb0a5ed8782b'', ''./public/system/frame/css/plugins/footable/fonts/footable.woff'', ''1536871821'', ''1536288162'', ''1536288162''),
(1467, ''873523781b369512e7d677adf2f0e083'', ''./public/system/frame/css/plugins/images/sort_desc.png'', ''1536871823'', ''1536288162'', ''1536288162''),
(1468, ''99cc9360eb5c063ee46655fd014ce2d4'', ''./public/system/frame/css/plugins/images/spritemap_402x.png'', ''1536871823'', ''1536288162'', ''1536288162''),
(1469, ''7b51acacc1feb8be0580e8467a4d306b'', ''./public/system/frame/css/plugins/images/spritemap.png'', ''1536871823'', ''1536288162'', ''1536288162''),
(1470, ''1ddf746a85707231f84d947637efd63a'', ''./public/system/frame/css/plugins/images/sort_asc.png'', ''1536871823'', ''1536288162'', ''1536288162''),
(1471, ''b363c10c480daebf2e9fab3572dfe325'', ''./public/system/frame/css/plugins/images/sprite-skin-flat.png'', ''1536871823'', ''1536288162'', ''1536288162''),
(1472, ''6a95ddda069c68ed5a334872b2f1dc9e'', ''./public/system/frame/css/plugins/switchery/switchery.css'', ''1536871824'', ''1536288162'', ''1536288162''),
(1473, ''33cf9e835e3b7132ea1098edd85cd7bb'', ''./public/system/frame/css/plugins/simditor/simditor.css'', ''1536871825'', ''1536288162'', ''1536288162''),
(1474, ''608c9427568fb77370a7bd87121a8d7f'', ''./public/system/frame/css/plugins/iCheck/custom.css'', ''1536871826'', ''1536288162'', ''1536288162''),
(1475, ''3c4bf794e3e2af3e68d2f4bd77d0baa7'', ''./public/system/frame/css/plugins/iCheck/green.png'', ''1536871826'', ''1536288162'', ''1536288162''),
(1476, ''a9949782f83fe749cf551b604619de9c'', ''./public/system/frame/css/plugins/iCheck/green_402x.png'', ''1536871826'', ''1536288162'', ''1536288162''),
(1477, ''c65e357d96162daabe78bca2dbdce79c'', ''./public/system/frame/css/plugins/codemirror/ambiance.css'', ''1536871827'', ''1536288162'', ''1536288162''),
(1478, ''e5c23905e29d3bfaac1e4d3601bb8b23'', ''./public/system/frame/css/plugins/codemirror/codemirror.css'', ''1536871827'', ''1536288162'', ''1536288162''),
(1479, ''01879eabf6ff8d70bad4710e91fe62a2'', ''./public/system/frame/css/plugins/summernote/summernote-bs3.css'', ''1536871828'', ''1536288162'', ''1536288162''),
(1480, ''49e7a2f972d7e021e814016f27a402ab'', ''./public/system/frame/css/plugins/summernote/summernote.css'', ''1536871828'', ''1536288162'', ''1536288162''),
(1481, ''e6cdd0ccbc5a05d6e054d8ec34e9609b'', ''./public/system/frame/css/plugins/plyr/plyr.css'', ''1536871829'', ''1536288162'', ''1536288162''),
(1482, ''d24055bb6d758127b71580008eebde97'', ''./public/system/frame/css/plugins/plyr/sprite.svg'', ''1536871829'', ''1536288162'', ''1536288162''),
(1483, ''0f36de6b99644f7418cb7606d3f4c28f'', ''./public/system/frame/css/plugins/clockpicker/clockpicker.css'', ''1536871830'', ''1536288162'', ''1536288162''),
(1484, ''66a5146a843f8e536a1da6af5db26b71'', ''./public/system/frame/css/plugins/steps/jquery.steps.css'', ''1536871830'', ''1536288162'', ''1536288162''),
(1485, ''dd835c75a131866a16470623a46e95bd'', ''./public/system/frame/css/plugins/cropper/cropper.min.css'', ''1536871831'', ''1536288162'', ''1536288162''),
(1486, ''c45ed1e9b43d738b6fad917f1fc76ce2'', ''./public/system/frame/css/plugins/bootstrap-table/bootstrap-table.min.css'', ''1536871832'', ''1536288162'', ''1536288162''),
(1487, ''59e265c06da0d7f4f617094d04a09645'', ''./public/system/frame/css/plugins/jasny/jasny-bootstrap.min.css'', ''1536871833'', ''1536288162'', ''1536288162''),
(1488, ''cf11a9e74fe0c72c1ca76b43b64879e3'', ''./public/system/frame/css/plugins/fullcalendar/fullcalendar.css'', ''1536871834'', ''1536288162'', ''1536288162''),
(1489, ''acf02ccc6bf59add6e0504f3d5ecba71'', ''./public/system/frame/css/plugins/fullcalendar/fullcalendar.print.css'', ''1536871834'', ''1536288162'', ''1536288162''),
(1490, ''247693b356502a1f18b403f10e4bca24'', ''./public/system/frame/css/plugins/jqgrid/ui.jqgrid.css'', ''1536871834'', ''1536288162'', ''1536288162''),
(1491, ''7caa6ec91fdb768f000d1a767907bb04'', ''./public/system/frame/css/plugins/morris/morris-0.4.3.min.css'', ''1536871835'', ''1536288162'', ''1536288162''),
(1492, ''4328ebe7fd79202cd2bd372c87a3fb11'', ''./public/system/frame/css/plugins/nouslider/jquery.nouislider.css'', ''1536871836'', ''1536288162'', ''1536288162''),
(1493, ''0647843fc410a3367afe7ef5344a897b'', ''./public/system/frame/css/bootstrap.min.css'', ''1536871837'', ''1536288162'', ''1536288162''),
(1494, ''393d1040f9005a199b9cde2b5adca2f3'', ''./public/system/frame/css/animate.min.css'', ''1536871838'', ''1536288162'', ''1536288162''),
(1495, ''5be57ee662d0aa4f1e300709700bc2b4'', ''./public/system/frame/css/M663 1125q-11 -1 -15.5 -10.5t-8.5 -9.5q-5 -1 -5 5q0 12 19 15h10zM750 1111q-4 -1 -11.5 6.5t-17.5 4.5q03F7DC5A5A'', ''1536871838'', ''1536288162'', ''1536288162''),
(1496, ''8bf71864fdf1ac4b9133cfc112b1fbd8'', ''./public/system/frame/css/demo/webuploader-demo.min.css'', ''1536871838'', ''1536288162'', ''1536288162''),
(1497, ''bf471ec3d4085883e061ca35006e86e8'', ''./public/system/frame/css/patterns/header-profile-skin-3.png'', ''1536871839'', ''1536288162'', ''1536288162''),
(1498, ''2a634a94d5b175c41a71fac233a52f53'', ''./public/system/frame/css/patterns/header-profile.png'', ''1536871839'', ''1536288162'', ''1536288162''),
(1499, ''ea2316224d45899c59bc285ba09dd920'', ''./public/system/frame/css/patterns/shattered.png'', ''1536871839'', ''1536288162'', ''1536288162''),
(1500, ''85efa900c0fc12fee15a5398deba06e8'', ''./public/system/frame/css/patterns/header-profile-skin-1.png'', ''1536871843'', ''1536288162'', ''1536288162''),
(1501, ''0be6af59f0115361ab3fabfc8bd862be'', ''./public/system/frame/css/style.min.css'', ''1536871844'', ''1536288162'', ''1536288162''),
(1502, ''ddc96c869583450ff45049968cef2c07'', ''./public/system/frame/css/login.min.css'', ''1536871846'', ''1536288162'', ''1536288162''),
(1503, ''e2c72bfae2b4e3a016d1bfeb1774f989'', ''./public/system/frame/css/font-awesome.min.css'', ''1536871846'', ''1536288162'', ''1536288162''),
(1504, ''c9ceb83c0a247ae47f54c3e1d3cb4bac'', ''./public/system/frame/img/icons.png'', ''1536871849'', ''1536288162'', ''1536288162''),
(1505, ''b80425bbf53402d499d54c86ca365870'', ''./public/system/frame/img/success.png'', ''1536871849'', ''1536288162'', ''1536288162''),
(1506, ''f6f30beb72f584e218bfec975eb1109d'', ''./public/system/frame/img/locked.png'', ''1536871851'', ''1536288162'', ''1536288162''),
(1507, ''4990787de11666d9d94edbc718ddf071'', ''./public/system/frame/img/a1.jpg'', ''1536871851'', ''1536288162'', ''1536288162''),
(1508, ''681dfebf3a20ec9c580d8dc248eb6a6e'', ''./public/system/frame/img/user.png'', ''1536871851'', ''1536288162'', ''1536288162''),
(1509, ''2da0807814ad64841cd597c4e8a653d1'', ''./public/system/frame/img/loading-upload.gif'', ''1536871851'', ''1536288162'', ''1536288162''),
(1510, ''b1d9bd8ff3834f780bc8aa565a73d306'', ''./public/system/frame/img/p2.jpg'', ''1536871851'', ''1536288162'', ''1536288162''),
(1511, ''aec2294728d596978c9e016774171521'', ''./public/system/frame/img/profile_small.jpg'', ''1536871851'', ''1536288162'', ''1536288162''),
(1512, ''f9f491385463e7ffa95af8f8c17aea2a'', ''./public/system/frame/img/p_big3.jpg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1513, ''f43a1ca7e9274d881d3b7ec00d102965'', ''./public/system/frame/img/p1.jpg'', ''1536871852'', ''1536288162'', ''1536288162''),
(1514, ''96660bd7d061e19f46a305390651f9e0'', ''./public/system/frame/img/pay.png'', ''1536871852'', ''1536288162'', ''1536288162''),
(1515, ''8feae3652f626ba9ec74d14792de6b95'', ''./public/system/frame/img/qr_code.png'', ''1536871852'', ''1536288162'', ''1536288162''),
(1516, ''4523359ec4ba32f807b1de8f213cf188'', ''./public/system/frame/img/a5.jpg'', ''1536871852'', ''1536288162'', ''1536288162''),
(1517, ''38c934d558c5b12766781553c6279a32'', ''./public/system/frame/img/profile_big.jpg'', ''1536871852'', ''1536288162'', ''1536288162''),
(1518, ''8f3abaa10b9a98880a0ba87a1744a255'', ''./public/system/frame/img/1.png'', ''1536871852'', ''1536288162'', ''1536288162''),
(1519, ''6326cd02ec7029ff4027da83ff09374f'', ''./public/system/frame/img/index.jpg'', ''1536871852'', ''1536288162'', ''1536288162''),
(1520, ''06773f8873a55483445586519da6aec5'', ''./public/system/frame/img/iconfont-logo.png'', ''1536871852'', ''1536288162'', ''1536288162''),
(1521, ''3c00f0f466522fbd0ef442917a71f55f'', ''./public/system/frame/img/a9.jpg'', ''1536871852'', ''1536288162'', ''1536288162''),
(1522, ''43c338754928ee7ed34b33b9e7c82dff'', ''./public/system/frame/img/p_big1.jpg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1523, ''cc608c637bec9117018cef0e4cc9c6dd'', ''./public/system/frame/img/a8.jpg'', ''1536871853'', ''1536288162'', ''1536288162''),
(1524, ''c435098ccf06e4248d918f91ed0992c8'', ''./public/system/frame/img/a6.jpg'', ''1536871853'', ''1536288162'', ''1536288162''),
(1525, ''dd5c23469d5041758ba1e774e91cce0f'', ''./public/system/frame/img/profile.jpg'', ''1536871853'', ''1536288162'', ''1536288162''),
(1526, ''c7f148b2a45d68e1f2baba730dca2c78'', ''./public/system/frame/img/a4.jpg'', ''1536871853'', ''1536288162'', ''1536288162''),
(1527, ''bd835163b61fa2dd11579b2de70023fc'', ''./public/system/frame/img/a7.jpg'', ''1536871853'', ''1536288162'', ''1536288162''),
(1528, ''61e4e8b958e12b2de6d125edadb99d38'', ''./public/system/frame/img/wenku_logo.png'', ''1536871853'', ''1536288162'', ''1536288162''),
(1529, ''5112881784000c6cf6d81e5a8aa10f3c'', ''./public/system/frame/img/index_4.jpg'', ''1536871853'', ''1536288162'', ''1536288162''),
(1530, ''aa6af324962786fac1fcd19d856bbf3a'', ''./public/system/frame/img/p3.jpg'', ''1536871853'', ''1536288162'', ''1536288162''),
(1531, ''8ddad23ce6dcc70bf111b23ae022f10c'', ''./public/system/frame/img/bg.png'', ''1536871853'', ''1536288162'', ''1536288162''),
(1532, ''31e21fdea575697a651cf4572562e398'', ''./public/system/frame/img/login-background.jpg'', ''1536871854'', ''1536288162'', ''1536288162''),
(1533, ''22508d7389277290584be5a09c16853e'', ''./public/system/frame/img/p_big2.jpg'', ''1536288162'', ''1536288162'', ''1536288162''),
(1534, ''46732e763f50c337fecabcc42150d842'', ''./public/system/frame/img/progress.png'', ''1536871854'', ''1536288162'', ''1536288162''),
(1535, ''9053bb860fb23722497df4bc2c25eaa3'', ''./public/system/frame/img/a2.jpg'', ''1536871854'', ''1536288162'', ''1536288162''),
(1536, ''9b6adf5c5f23ff87b3d9873809052831'', ''./public/system/frame/img/a3.jpg'', ''1536871854'', ''1536288162'', ''1536288162''),
(1537, ''e18bbf611f2a2e43afc071aa2f4e1512'', ''./public/system/frame/fonts/glyphicons-halflings-regular.ttf'', ''1536871855'', ''1536288162'', ''1536288162''),
(1538, ''b55e9e3c1edc958b950e9c8cefeb7910'', ''./public/system/frame/fonts/glyphicons-halflings-regular.svg'', ''1536871855'', ''1536288162'', ''1536288162''),
(1539, ''7c87870ab40d63cfb8870c1f183f9939'', ''./public/system/frame/fonts/fontawesome-webfont.ttf'', ''1536871855'', ''1536288162'', ''1536288162''),
(1540, ''dfb02f8f6d0cedc009ee5887cc68f1f3'', ''./public/system/frame/fonts/fontawesome-webfont.woff'', ''1536871855'', ''1536288162'', ''1536288162''),
(1541, ''4b5a84aaf1c9485e060c503a0ff8cadb'', ''./public/system/frame/fonts/fontawesome-webfont.woff2'', ''1536871855'', ''1536288162'', ''1536288162''),
(1542, ''45c73723862c6fc5eb3d6961db2d71fb'', ''./public/system/frame/fonts/fontawesome-webfont.eot'', ''1536871855'', ''1536288162'', ''1536288162''),
(1543, ''193ccf5d770f27d23ed84561b96d6375'', ''./public/system/frame/fonts/fontawesome-webfont.svg'', ''1536871855'', ''1536288162'', ''1536288162''),
(1544, ''fa2772327f55d8198301fdb8bcfc8158'', ''./public/system/frame/fonts/glyphicons-halflings-regular.woff'', ''1536871856'', ''1536288162'', ''1536288162''),
(1545, ''448c34a56d699c29117adc64c43affeb'', ''./public/system/frame/fonts/glyphicons-halflings-regular.woff2'', ''1536871856'', ''1536288162'', ''1536288162''),
(1546, ''f4769f9bdb7466be65088239c12046d1'', ''./public/system/frame/fonts/glyphicons-halflings-regular.eot'', ''1536871856'', ''1536288162'', ''1536288162''),
(1547, ''1a797dfa4b866bee5173b86f726dc310'', ''./public/system/frame/js/plugins/sweetalert/sweetalert.min.js'', ''1536871857'', ''1536288162'', ''1536288162''),
(1548, ''00b58cadd3c32bba75167210f7ba849d'', ''./public/system/frame/js/plugins/colorpicker/bootstrap-colorpicker.min.js'', ''1536871857'', ''1536288162'', ''1536288162''),
(1549, ''f2c71e382b38dd45396ce91f96a89888'', ''./public/system/frame/js/plugins/dropzone/dropzone.js'', ''1536871858'', ''1536288162'', ''1536288162''),
(1550, ''48e92f631c7c0794c741e356abe3da74'', ''./public/system/frame/js/plugins/chosen/chosen.jquery.js'', ''1536871859'', ''1536288162'', ''1536288162''),
(1551, ''70824a0e545011c32563c03f0761b70b'', ''./public/system/frame/js/plugins/easypiechart/jquery.easypiechart.js'', ''1536871860'', ''1536288162'', ''1536288162''),
(1552, ''c973ed7c4369cd5e84af541c5548f74f'', ''./public/system/frame/js/plugins/preetyTextDiff/jquery.pretty-text-diff.min.js'', ''1536871861'', ''1536288162'', ''1536288162''),
(1553, ''a19a47d8227e234c315fb0319803c1d9'', ''./public/system/frame/js/plugins/prettyfile/bootstrap-prettyfile.js'', ''1536871861'', ''1536288162'', ''1536288162''),
(1554, ''8fb1ea79bc5eee1328bf1ee39b514495'', ''./public/system/frame/js/plugins/suggest/data.json'', ''1536871862'', ''1536288162'', ''1536288162''),
(1555, ''35c1c4807033c980218374331755a8fa'', ''./public/system/frame/js/plugins/suggest/bootstrap-suggest.min.js'', ''1536871862'', ''1536288162'', ''1536288162''),
(1556, ''fbfb0c81631e4e58fe947463d7d48d87'', ''./public/system/frame/js/plugins/toastr/toastr.min.js'', ''1536871863'', ''1536288162'', ''1536288162''),
(1557, ''14e3eafa2631c7b68aaec169f729a2d8'', ''./public/system/frame/js/plugins/jsTree/jstree.min.js'', ''1536871864'', ''1536288162'', ''1536288162''),
(1558, ''342fc4d0d4ae048e6feeac804ef05f2c'', ''./public/system/frame/js/plugins/dataTables/jquery.dataTables.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1559, ''596bf800f9969586a117b1f70b8a5a4c'', ''./public/system/frame/js/plugins/dataTables/dataTables.bootstrap.js'', ''1536871865'', ''1536288162'', ''1536288162''),
(1560, ''e11228011d50b3f07e7284717cb376d8'', ''./public/system/frame/js/plugins/markdown/bootstrap-markdown.zh.js'', ''1536871866'', ''1536288162'', ''1536288162''),
(1561, ''50012c0493d4a62719e7074d7c09c9b8'', ''./public/system/frame/js/plugins/markdown/to-markdown.js'', ''1536871866'', ''1536288162'', ''1536288162''),
(1562, ''a0d34da5fd1cee5902be897cd11c539b'', ''./public/system/frame/js/plugins/markdown/bootstrap-markdown.js'', ''1536871866'', ''1536288162'', ''1536288162''),
(1563, ''c6462d13efd635c23cefd8c8150b4d40'', ''./public/system/frame/js/plugins/markdown/markdown.js'', ''1536871866'', ''1536288162'', ''1536288162''),
(1564, ''f08c3b5b9e12083ca35f9359733babd4'', ''./public/system/frame/js/plugins/staps/jquery.steps.min.js'', ''1536871867'', ''1536288162'', ''1536288162''),
(1565, ''23f5ceb1f26f5497cbbc7f6f37b82fe9'', ''./public/system/frame/js/plugins/fancybox/jquery.fancybox.css'', ''1536871867'', ''1536288162'', ''1536288162''),
(1566, ''783d4031fe50c3d83c960911e1fbc705'', ''./public/system/frame/js/plugins/fancybox/fancybox_sprite.png'', ''1536871868'', ''1536288162'', ''1536288162''),
(1567, ''ce0e799634e091e39ea6e49b12a95d14'', ''./public/system/frame/js/plugins/fancybox/jquery.fancybox.js'', ''1536871868'', ''1536288162'', ''1536288162''),
(1568, ''328cc0f6c78211485058d460e80f4fa8'', ''./public/system/frame/js/plugins/fancybox/fancybox_loading.gif'', ''1536871868'', ''1536288162'', ''1536288162''),
(1569, ''77aeaa52715b898b73c74d68c630330e'', ''./public/system/frame/js/plugins/fancybox/fancybox_overlay.png'', ''1536871868'', ''1536288162'', ''1536288162''),
(1570, ''325472601571f31e1bf00674c368d335'', ''./public/system/frame/js/plugins/fancybox/blank.gif'', ''1536871868'', ''1536288162'', ''1536288162''),
(1571, ''f92938639fa894a0e8ded1c3368abe98'', ''./public/system/frame/js/plugins/fancybox/fancybox_loading_402x.gif'', ''1536871868'', ''1536288162'', ''1536288162''),
(1572, ''ed9970ce22242421e66ff150aa97fe5f'', ''./public/system/frame/js/plugins/fancybox/fancybox_sprite_402x.png'', ''1536871868'', ''1536288162'', ''1536288162''),
(1573, ''5267e527a800d64e4ea4c4bf615661c9'', ''./public/system/frame/js/plugins/metisMenu/jquery.metisMenu.js'', ''1536871869'', ''1536288162'', ''1536288162''),
(1574, ''a068d565238e1d58fcf47b674a60e222'', ''./public/system/frame/js/plugins/treeview/bootstrap-treeview.js'', ''1536871870'', ''1536288162'', ''1536288162''),
(1575, ''913a7f8d4882bfb3826ef01c7ea07732'', ''./public/system/frame/js/plugins/jeditable/jquery.jeditable.js'', ''1536871871'', ''1536288162'', ''1536288162''),
(1576, ''038d9f982a802e6b19ef246d092175dc'', ''./public/system/frame/js/plugins/sparkline/jquery.sparkline.min.js'', ''1536871871'', ''1536288162'', ''1536288162''),
(1577, ''1a2edc99d8daa51d0479014018112bd5'', ''./public/system/frame/js/plugins/chartJs/Chart.min.js'', ''1536871872'', ''1536288162'', ''1536288162''),
(1578, ''d265fb1c3471e89602db44304eaa7f2a'', ''./public/system/frame/js/plugins/jquery-ui/jquery-ui.min.js'', ''1536871873'', ''1536288162'', ''1536288162''),
(1579, ''e1c1f37c8eac50220c31a2571ac9916f'', ''./public/system/frame/js/plugins/jsKnob/jquery.knob.js'', ''1536871874'', ''1536288162'', ''1536288162''),
(1580, ''e9f4e57a6184dfe2478260785e83015f'', ''./public/system/frame/js/plugins/pace/pace.min.js'', ''1536871877'', ''1536288162'', ''1536288162''),
(1581, ''56c29dd4e3c4961e76d71ddfb739d3c8'', ''./public/system/frame/js/plugins/flot/jquery.flot.tooltip.min.js'', ''1536871877'', ''1536288162'', ''1536288162''),
(1582, ''a50fe06da186c171e81473ed62c6bb15'', ''./public/system/frame/js/plugins/flot/jquery.flot.pie.js'', ''1536871879'', ''1536288162'', ''1536288162''),
(1583, ''022e8fd8f6b68a7c3f141a30e7eb9eaf'', ''./public/system/frame/js/plugins/flot/jquery.flot.symbol.js'', ''1536871880'', ''1536288162'', ''1536288162''),
(1584, ''204161f30a99defbf20fa10721ea45ea'', ''./public/system/frame/js/plugins/flot/jquery.flot.spline.js'', ''1536871880'', ''1536288162'', ''1536288162''),
(1585, ''931b6c2c413c91e716faca2461fe95cc'', ''./public/system/frame/js/plugins/flot/jquery.flot.resize.js'', ''1536871882'', ''1536288162'', ''1536288162''),
(1586, ''32e479a83d47391e87b4d3f4703f5561'', ''./public/system/frame/js/plugins/flot/jquery.flot.js'', ''1536871882'', ''1536288162'', ''1536288162''),
(1587, ''f4e8016f2fc749c4a66373d5a3f8fffa'', ''./public/system/frame/js/plugins/flot/curvedLines.js'', ''1536871884'', ''1536288162'', ''1536288162''),
(1588, ''fdf76420adcd9e1d70ed1cf2a117acae'', ''./public/system/frame/js/plugins/blueimp/jquery.blueimp-gallery.min.js'', ''1536871885'', ''1536288162'', ''1536288162''),
(1589, ''818fdc785f97fa3feb063697bffb97de'', ''./public/system/frame/js/plugins/datapicker/bootstrap-datepicker.js'', ''1536871888'', ''1536288162'', ''1536288162''),
(1590, ''db46172e66d5600ef9a53644131167c9'', ''./public/system/frame/js/plugins/webuploader/webuploader.min.js'', ''1536871888'', ''1536288162'', ''1536288162''),
(1591, ''732c0f827b48ea03c809c7dbfe67ff94'', ''./public/system/frame/js/plugins/ionRangeSlider/ion.rangeSlider.min.js'', ''1536871889'', ''1536288162'', ''1536288162''),
(1592, ''a4cf016eab71d5d71a162820bd751bf2'', ''./public/system/frame/js/plugins/gritter/jquery.gritter.min.js'', ''1536871890'', ''1536288162'', ''1536288162''),
(1593, ''e78d3fcf0bf309416d930a40c74ee500'', ''./public/system/frame/js/plugins/gritter/images/gritter-light.png'', ''1536871890'', ''1536288162'', ''1536288162''),
(1594, ''df3e567d6f16d040326c7a0ea29a4f41'', ''./public/system/frame/js/plugins/gritter/images/ie-spacer.gif'', ''1536871890'', ''1536288162'', ''1536288162''),
(1595, ''e6bf207bb4ab9d76d812353b9684e34e'', ''./public/system/frame/js/plugins/gritter/images/gritter.png'', ''1536871890'', ''1536288162'', ''1536288162''),
(1596, ''a119207c61dc56c06d21f85d5a4598dd'', ''./public/system/frame/js/plugins/gritter/jquery.gritter.css'', ''1536871891'', ''1536288162'', ''1536288162''),
(1597, ''3d987a6636af4f5c5c90cccfc68ceeb8'', ''./public/system/frame/js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js'', ''1536871892'', ''1536288162'', ''1536288162''),
(1598, ''e23a59cb47d6e6467c75652a5d6385cf'', ''./public/system/frame/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js'', ''1536871892'', ''1536288162'', ''1536288162''),
(1599, ''2855021f41f66d5c778338bbf896c742'', ''./public/system/frame/js/plugins/footable/footable.all.min.js'', ''1536871893'', ''1536288162'', ''1536288162''),
(1600, ''3b5a0f7718f1890f57ff772e4a09b567'', ''./public/system/frame/js/plugins/nestable/jquery.nestable.js'', ''1536871894'', ''1536288162'', ''1536288162''),
(1601, ''6bac981949809f47d45723d827fda733'', ''./public/system/frame/js/plugins/beautifyhtml/beautifyhtml.js'', ''1536871895'', ''1536288162'', ''1536288162''),
(1602, ''762cbe606b83b4b2e2136eef79716028'', ''./public/system/frame/js/plugins/switchery/switchery.js'', ''1536871895'', ''1536288162'', ''1536288162''),
(1603, ''72627ce0ff3d2c903f72befe45bf7ccf'', ''./public/system/frame/js/plugins/validate/jquery.validate.min.js'', ''1536871896'', ''1536288162'', ''1536288162''),
(1604, ''bcff7273495787e551175e6bf433cfdf'', ''./public/system/frame/js/plugins/validate/messages_zh.min.js'', ''1536871896'', ''1536288162'', ''1536288162''),
(1605, ''300029d63e86f68c6b426e4e0ca9bcf8'', ''./public/system/frame/js/plugins/slimscroll/jquery.slimscroll.min.js'', ''1536871897'', ''1536288162'', ''1536288162''),
(1606, ''1ecf36fa04eb5aa04becf05330961d26'', ''./public/system/frame/js/plugins/echarts/echarts-all.js'', ''1536288162'', ''1536288162'', ''1536288162''),
(1607, ''ee2f3a2ed3768d6be2ee9fab54cd53c2'', ''./public/system/frame/js/plugins/echarts/echarts-all.js.b1'', ''1536288162'', ''1536288162'', ''1536288162''),
(1608, ''25da5cf0c6d6775df02a958162a5cac7'', ''./public/system/frame/js/plugins/echarts/echarts-all.js.b'', ''1536288162'', ''1536288162'', ''1536288162''),
(1609, ''840a13f0080de1f702ca6426916d338b'', ''./public/system/frame/js/plugins/diff_match_patch/diff_match_patch.js'', ''1536871899'', ''1536288162'', ''1536288162''),
(1610, ''2464bb0e707726d95a7372c97fbdacf3'', ''./public/system/frame/js/plugins/simditor/uploader.js'', ''1536871900'', ''1536288162'', ''1536288162''),
(1611, ''e6c2f6b692e06fdc3abaaf79db055c4c'', ''./public/system/frame/js/plugins/simditor/hotkeys.js'', ''1536871900'', ''1536288162'', ''1536288162''),
(1612, ''bf3823e7105998f268282a813362507c'', ''./public/system/frame/js/plugins/simditor/module.js'', ''1536871900'', ''1536288162'', ''1536288162''),
(1613, ''ecc034a2f811eea394e7c202901569b9'', ''./public/system/frame/js/plugins/simditor/simditor.js'', ''1536871900'', ''1536288162'', ''1536288162''),
(1614, ''65144d3f977f76227bc360430e50a929'', ''./public/system/frame/js/plugins/iCheck/icheck.min.js'', ''1536871901'', ''1536288162'', ''1536288162''),
(1615, ''fb22a6e072bb774a7ec80a51f9b1124f'', ''./public/system/frame/js/plugins/codemirror/codemirror.js'', ''1536871902'', ''1536288162'', ''1536288162''),
(1616, ''895325adfe7a31c30ff89cc5c8826ccd'', ''./public/system/frame/js/plugins/codemirror/mode/javascript/default.htm'', ''1536871902'', ''1536288162'', ''1536288162''),
(1617, ''7eca31d26ccac39492d326b7188505da'', ''./public/system/frame/js/plugins/codemirror/mode/javascript/javascript.js'', ''1536871902'', ''1536288162'', ''1536288162''),
(1618, ''ade1bb7910f218552b3955e9e7c050d9'', ''./public/system/frame/js/plugins/codemirror/mode/default.htm'', ''1536871903'', ''1536288162'', ''1536288162''),
(1619, ''0a0f46abd088a0f53c35588c7d229eb7'', ''./public/system/frame/js/plugins/summernote/summernote-zh-CN.js'', ''1536871904'', ''1536288162'', ''1536288162''),
(1620, ''14c47e48a89c1dde0c9511d6113d46a0'', ''./public/system/frame/js/plugins/summernote/summernote.min.js'', ''1536871904'', ''1536288162'', ''1536288162''),
(1621, ''3d25ff8a5ea9bda1a7581a4cee0cef20'', ''./public/system/frame/js/plugins/rickshaw/rickshaw.min.js'', ''1536871905'', ''1536288162'', ''1536288162''),
(1622, ''cbec063c32d61e84726c2843b1634026'', ''./public/system/frame/js/plugins/rickshaw/vendor/d3.v3.js'', ''1536871905'', ''1536288162'', ''1536288162''),
(1623, ''1b4a204e70dd613c46328cab2de89103'', ''./public/system/frame/js/plugins/plyr/plyr.js'', ''1536871907'', ''1536288162'', ''1536288162''),
(1624, ''490f1015f398c3e71642ac82d0592b67'', ''./public/system/frame/js/plugins/clockpicker/clockpicker.js'', ''1536871907'', ''1536288162'', ''1536288162''),
(1625, ''8b45823bec0699d2cde8e9ca84e51a11'', ''./public/system/frame/js/plugins/cropper/cropper.min.js'', ''1536871908'', ''1536288162'', ''1536288162''),
(1626, ''1b8b2544030d53a6598f398fe0bb1299'', ''./public/system/frame/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js'', ''1536871909'', ''1536288162'', ''1536288162''),
(1627, ''8991a7aee75ceb61c4336d02c4196eb3'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/multiple-search/bootstrap-table-multiple-search.min.js'', ''1536871909'', ''1536288162'', ''1536288162''),
(1628, ''0c7697213072698641798b481d480714'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/multiple-search/bootstrap-table-multiple-search.js'', ''1536871909'', ''1536288162'', ''1536288162''),
(1629, ''107794319ba0aca60b20a5033aac0e78'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/resizable/bootstrap-table-resizable.js'', ''1536871910'', ''1536288162'', ''1536288162''),
(1630, ''acf572ea346a65a8412e0906671e39d4'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/resizable/bootstrap-table-resizable.min.js'', ''1536871910'', ''1536288162'', ''1536288162''),
(1631, ''bfce47a991d1b251004aec02b723a283'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/flat-json/bootstrap-table-flat-json.min.js'', ''1536871911'', ''1536288162'', ''1536288162''),
(1632, ''6e762a9b44a171e71695d0e67a148a2d'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/flat-json/bootstrap-table-flat-json.js'', ''1536871911'', ''1536288162'', ''1536288162''),
(1633, ''fbe59e9ab8e5acde666a34fc0c34973f'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/accent-neutralise/bootstrap-table-accent-neutralise.min.js'', ''1536871912'', ''1536288162'', ''1536288162''),
(1634, ''6d06b83243a74ea85d9ed121e0ec901d'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/accent-neutralise/bootstrap-table-accent-neutralise.js'', ''1536871912'', ''1536288162'', ''1536288162''),
(1635, ''b434b6e3b25ee6120165739359cd9da1'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/key-events/bootstrap-table-key-events.min.js'', ''1536871913'', ''1536288162'', ''1536288162''),
(1636, ''3589ad5db5d8732a3c09f6c640341c1a'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/key-events/bootstrap-table-key-events.js'', ''1536871913'', ''1536288162'', ''1536288162''),
(1637, ''32dcd5b0f0fa42eac7967407c804cd00'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/reorder-columns/bootstrap-table-reorder-columns.min.js'', ''1536871914'', ''1536288162'', ''1536288162''),
(1638, ''0e537d484b2a8ab11990e14a27518179'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/reorder-columns/bootstrap-table-reorder-columns.js'', ''1536871914'', ''1536288162'', ''1536288162''),
(1639, ''7d3b52c0dcbd18cf1ec97781e4f7b327'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/sticky-header/bootstrap-table-sticky-header.min.js'', ''1536871914'', ''1536288162'', ''1536288162''),
(1640, ''1930a042b88e3a036285e9c61873ff97'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/sticky-header/bootstrap-table-sticky-header.js'', ''1536871915'', ''1536288162'', ''1536288162''),
(1641, ''120c3b82d69957c3ef26c9c040ae7909'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/sticky-header/bootstrap-table-sticky-header.css'', ''1536871915'', ''1536288162'', ''1536288162''),
(1642, ''734dbd3e7ac174b39e85be73acf88032'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/editable/bootstrap-table-editable.js'', ''1536871915'', ''1536288162'', ''1536288162''),
(1643, ''66ec82ccbf244c9f5f1b9f03bc602a17'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/editable/bootstrap-table-editable.min.js'', ''1536871916'', ''1536288162'', ''1536288162''),
(1644, ''3e493ce1ae5a04dbb8972f8576c81994'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/filter/bootstrap-table-filter.min.js'', ''1536871916'', ''1536288162'', ''1536288162''),
(1645, ''bc505fa2780c9d0b2476df0981a84baf'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/filter/bootstrap-table-filter.js'', ''1536871917'', ''1536288162'', ''1536288162''),
(1646, ''fa78f24b899919d08542b69e7dcb9092'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/natural-sorting/bootstrap-table-natural-sorting.min.js'', ''1536871917'', ''1536288162'', ''1536288162''),
(1647, ''c71431e5248f80d3a3fd4726a12b85df'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/natural-sorting/bootstrap-table-natural-sorting.js'', ''1536871918'', ''1536288162'', ''1536288162''),
(1648, ''1f488fa31b851137d5ad4d400b6f9b17'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/reorder-rows/bootstrap-table-reorder-rows.js'', ''1536871918'', ''1536288162'', ''1536288162''),
(1649, ''8e7bfc5c9662ecb96c85ca3d66cce446'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/reorder-rows/bootstrap-table-reorder-rows.css'', ''1536871918'', ''1536288162'', ''1536288162''),
(1650, ''2b88033f70c96c9b80c696de475477fd'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/reorder-rows/bootstrap-table-reorder-rows.min.js'', ''1536871919'', ''1536288162'', ''1536288162''),
(1651, ''f87cad29b70e0b29688712a436296607'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.min.js'', ''1536871919'', ''1536288162'', ''1536288162''),
(1652, ''03e93b845d194472f07aef753ae98bd9'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js'', ''1536871919'', ''1536288162'', ''1536288162''),
(1653, ''3deda8b31d567502a0d3340577b7252f'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/export/bootstrap-table-export.min.js'', ''1536871920'', ''1536288162'', ''1536288162''),
(1654, ''859ea0f9b6bc99adc1ca0f4f3f0e0d95'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/export/bootstrap-table-export.js'', ''1536871920'', ''1536288162'', ''1536288162''),
(1655, ''4ce17661b584e1170af5f753f1990661'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/cookie/bootstrap-table-cookie.js'', ''1536871921'', ''1536288162'', ''1536288162''),
(1656, ''92f8919982fba18903c58a486b2a0b8c'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/cookie/bootstrap-table-cookie.min.js'', ''1536871921'', ''1536288162'', ''1536288162''),
(1657, ''57c9bbef39cd0bc2cd60efda47a64074'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/angular/bootstrap-table-angular.min.js'', ''1536871922'', ''1536288162'', ''1536288162''),
(1658, ''f3fdbbb637e4872c8f034ef0f1ff541e'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/angular/bootstrap-table-angular.js'', ''1536871922'', ''1536288162'', ''1536288162''),
(1659, ''eb864fc21ba084375661ecccc1f8d307'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/multiple-sort/bootstrap-table-multiple-sort.min.js'', ''1536871923'', ''1536288162'', ''1536288162''),
(1660, ''ec386a329d5cd7c39677192b61fb7b1a'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/multiple-sort/bootstrap-table-multiple-sort.js'', ''1536871923'', ''1536288162'', ''1536288162''),
(1661, ''e62a0b761ffdeffdf8c9fb277065b441'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/filter-control/bootstrap-table-filter-control.min.js'', ''1536871924'', ''1536288162'', ''1536288162''),
(1662, ''5a84bd00ef4db1463b016623a3ab789d'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/filter-control/bootstrap-table-filter-control.js'', ''1536871924'', ''1536288162'', ''1536288162''),
(1663, ''2a8acb8f24268434441c5f4cd4f32015'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js'', ''1536871925'', ''1536288162'', ''1536288162''),
(1664, ''258b3770ad6b3cc1a3b9d77bcd6b2470'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/mobile/bootstrap-table-mobile.js'', ''1536871925'', ''1536288162'', ''1536288162''),
(1665, ''4b1e8cc357c5658ce74cc0a07547e65e'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/group-by/bootstrap-table-group-by.js'', ''1536871926'', ''1536288162'', ''1536288162''),
(1666, ''6edc4faeda2b2845a5019dfebeda0e93'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/group-by/bootstrap-table-group-by.min.js'', ''1536871926'', ''1536288162'', ''1536288162''),
(1667, ''51bb99d3e39def05e2e48d8463ccfd34'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/group-by/bootstrap-table-group-by.css'', ''1536871926'', ''1536288162'', ''1536288162''),
(1668, ''0a552fb47e778ddb82edf86f2b21dc6d'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/group-by-v2/bootstrap-table-group-by.js'', ''1536871927'', ''1536288162'', ''1536288162''),
(1669, ''eea4fa106f718fc43b0bb8d18d19d26f'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/group-by-v2/bootstrap-table-group-by.min.js'', ''1536871927'', ''1536288162'', ''1536288162''),
(1670, ''ad8e50e4f2735cce3cce4f6d23fee47b'', ''./public/system/frame/js/plugins/bootstrap-table/extensions/group-by-v2/bootstrap-table-group-by.css'', ''1536871927'', ''1536288162'', ''1536288162''),
(1671, ''7c1e886331ef94025129e9ae5d502294'', ''./public/system/frame/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js'', ''1536871928'', ''1536288162'', ''1536288162''),
(1672, ''6ea8ea752d6ad4e93bb6f86f3eaf2e83'', ''./public/system/frame/js/plugins/bootstrap-table/bootstrap-table.min.js'', ''1536871929'', ''1536288162'', ''1536288162''),
(1673, ''f86c8ff055152281bbbee371b7df8f60'', ''./public/system/frame/js/plugins/layer/layim/layim.js'', ''1536871930'', ''1536288162'', ''1536288162''),
(1674, ''c06505e8e2ee6e8aa6e832588db32e6c'', ''./public/system/frame/js/plugins/layer/layim/loading.gif'', ''1536871930'', ''1536288162'', ''1536288162''),
(1675, ''12ffa9de2d261ee2ccadf559b89d4da9'', ''./public/system/frame/js/plugins/layer/layim/layim.css'', ''1536871930'', ''1536288162'', ''1536288162''),
(1676, ''6620c0cc04b8252e138f0cb71edc141d'', ''./public/system/frame/js/plugins/layer/layim/data/friend.json'', ''1536871930'', ''1536288162'', ''1536288162''),
(1677, ''6ab361115031a49a706ba19d9fc5814d'', ''./public/system/frame/js/plugins/layer/layim/data/group.json'', ''1536871930'', ''1536288162'', ''1536288162''),
(1678, ''f6c42901fc3b570fb9457a5433c5a7d7'', ''./public/system/frame/js/plugins/layer/layim/data/groups.json'', ''1536871930'', ''1536288162'', ''1536288162''),
(1679, ''da9a9ebdaf89cb94c774f22318f95a0f'', ''./public/system/frame/js/plugins/layer/layim/data/chatlog.json'', ''1536871931'', ''1536288162'', ''1536288162''),
(1680, ''801692590a634436f2628f74c4aa57e8'', ''./public/system/frame/js/plugins/layer/laydate/laydate.js'', ''1536871932'', ''1536288162'', ''1536288162''),
(1681, ''70ed32c378eee8be85851d1be7c591ad'', ''./public/system/frame/js/plugins/layer/laydate/need/laydate.css'', ''1536871932'', ''1536288162'', ''1536288162''),
(1682, ''f52788bd231e60084217c081778f0994'', ''./public/system/frame/js/plugins/layer/laydate/skins/default/laydate.css'', ''1536871933'', ''1536288162'', ''1536288162''),
(1683, ''117d7e4b7e0fe6774616872b1b1edf33'', ''./public/system/frame/js/plugins/layer/laydate/skins/default/icon.png'', ''1536871933'', ''1536288162'', ''1536288162''),
(1684, ''b5994e8bb01ba245fc86fb5f3349c0d7'', ''./public/system/frame/js/plugins/layer/layer.min.js'', ''1536871935'', ''1536288162'', ''1536288162''),
(1685, ''9d6b1dd70f124df9c322eb9683d045a7'', ''./public/system/frame/js/plugins/layer/skin/layer.ext.css'', ''1536871935'', ''1536288162'', ''1536288162''),
(1686, ''664c833b08c3ccf81fe2f7edc4fa456e'', ''./public/system/frame/js/plugins/layer/skin/layer.css'', ''1536871935'', ''1536288162'', ''1536288162''),
(1687, ''ebfcbd9139bac19c105506acf2842fd7'', ''./public/system/frame/js/plugins/layer/skin/moon/style.css'', ''1536871936'', ''1536288162'', ''1536288162''),
(1688, ''76f6f55f4c3464416b9561503af69dbb'', ''./public/system/frame/js/plugins/layer/extend/layer.ext.js'', ''1536871937'', ''1536288162'', ''1536288162''),
(1689, ''64d4b747381d6936ea815c249b440a03'', ''./public/system/frame/js/plugins/jasny/jasny-bootstrap.min.js'', ''1536871938'', ''1536288162'', ''1536288162''),
(1690, ''e22ba0c348f15d3d102642c993f92d61'', ''./public/system/frame/js/plugins/fullcalendar/fullcalendar.min.js'', ''1536871939'', ''1536288162'', ''1536288162''),
(1691, ''405914ccfceb685a15d0796c982e1915'', ''./public/system/frame/js/plugins/jqgrid/jquery.jqGrid.min.js'', ''1536871940'', ''1536288162'', ''1536288162''),
(1692, ''f92dd5dda33454fc5bdd42769f7cede1'', ''./public/system/frame/js/plugins/jqgrid/i18n/grid.locale-cn.js'', ''1536871940'', ''1536288162'', ''1536288162''),
(1693, ''3b33a2736848965593e3ed5e04206d48'', ''./public/system/frame/js/plugins/peity/jquery.peity.min.js'', ''1536871942'', ''1536288162'', ''1536288162''),
(1694, ''d995698f8d57da5bb3b17b209911be84'', ''./public/system/frame/js/plugins/morris/morris.js'', ''1536871942'', ''1536288162'', ''1536288162''),
(1695, ''ffd330bd214b7b0a8e14e613765b606e'', ''./public/system/frame/js/plugins/morris/raphael-2.1.0.min.js'', ''1536871943'', ''1536288162'', ''1536288162''),
(1696, ''29b50e25f419b20fe33240e6af9bd12b'', ''./public/system/frame/js/plugins/morris/morris.js.b'', ''1536871943'', ''1536288162'', ''1536288162''),
(1697, ''e42900b8af9a61da5be53e9eb9c5f6bc'', ''./public/system/frame/js/plugins/nouslider/jquery.nouislider.min.js'', ''1536871944'', ''1536288162'', ''1536288162''),
(1698, ''e40ec2161fe7993196f23c8a07346306'', ''./public/system/frame/js/jquery-2.1.1.min.js'', ''1536871945'', ''1536288162'', ''1536288162''),
(1699, ''4aceec735667f697e9e43f1e37d3702a'', ''./public/system/frame/js/contabs.min.js'', ''1536871945'', ''1536288162'', ''1536288162''),
(1700, ''d440e4099856e73bfa890e1068055360'', ''./public/system/frame/js/ajaxfileupload.js'', ''1536871945'', ''1536288162'', ''1536288162''),
(1701, ''d265fb1c3471e89602db44304eaa7f2a'', ''./public/system/frame/js/jquery-ui-1.10.4.min.js'', ''1536871945'', ''1536288162'', ''1536288162''),
(1702, ''e04519104cf32b7d927d3e835235eac6'', ''./public/system/frame/js/content.min.js'', ''1536871945'', ''1536288162'', ''1536288162''),
(1703, ''f9c7afd05729f10f55b689f36bb20172'', ''./public/system/frame/js/jquery.min.js'', ''1536871945'', ''1536288162'', ''1536288162''),
(1704, ''6b828f5051b07a50f99c19b3a53d0be4'', ''./public/system/frame/js/hplus.min.js'', ''1536871946'', ''1536288162'', ''1536288162''),
(1705, ''26412a9ee704fb23bb3d8cf69b353c29'', ''./public/system/frame/js/bootstrap.min.js'', ''1536871946'', ''1536288162'', ''1536288162''),
(1706, ''3cdc65ae49a0eeb2ec2b9aead627dab1'', ''./public/system/frame/js/welcome.min.js'', ''1536871946'', ''1536288162'', ''1536288162''),
(1707, ''6ae848a4a2b0f9aba906d6a136706fb8'', ''./public/system/frame/js/jquery-ui.custom.min.js'', ''1536871946'', ''1536288162'', ''1536288162''),
(1708, ''701a42212a46a5d8c0320dfdbb60f5a3'', ''./public/system/js/index.js'', ''1536871947'', ''1536288162'', ''1536288162''),
(1709, ''60765cc824257d8e718fb08c66375107'', ''./public/system/js/create.js'', ''1536871947'', ''1536288162'', ''1536288162''),
(1710, ''7bf82644096710242467526abbf241fd'', ''./public/system/js/layuiList.js'', ''1536871947'', ''1536288162'', ''1536288162'');

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_group`
--

CREATE TABLE IF NOT EXISTS `eb_system_group` (
  `id` int(11) NOT NULL COMMENT ''组合数据ID'',
  `name` varchar(50) NOT NULL COMMENT ''数据组名称'',
  `info` varchar(256) NOT NULL COMMENT ''数据提示'',
  `config_name` varchar(50) NOT NULL COMMENT ''数据字段'',
  `fields` text COMMENT ''数据组字段以及类型（json数据）''
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COMMENT=''组合数据表'';

--
-- 转存表中的数据 `eb_system_group`
--

INSERT INTO `eb_system_group` (`id`, `name`, `info`, `config_name`, `fields`) VALUES
(32, ''个人中心菜单'', ''个人中心菜单设置'', ''my_index_menu'', ''[{"name":"\\u540d\\u79f0","title":"name","type":"input","param":""},{"name":"\\u56fe\\u6807","title":"icon","type":"upload","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""}]''),
(34, ''商城首页banner'', ''商城首页banner设置'', ''store_home_banner'', ''[{"name":"\\u6807\\u9898","title":"title","type":"input","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""},{"name":"\\u56fe\\u7247","title":"pic","type":"upload","param":""}]''),
(35, ''商城首页分类按钮'', ''商城首页分类按钮'', ''store_home_menus'', ''[{"name":"\\u540d\\u79f0","title":"name","type":"input","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""},{"name":"\\u56fe\\u6807","title":"icon","type":"upload","param":""}]''),
(36, ''商城首页滚动新闻'', ''商城首页滚动新闻'', ''store_home_roll_news'', ''[{"name":"\\u6eda\\u52a8\\u6587\\u5b57","title":"info","type":"input","param":""},{"name":"\\u70b9\\u51fb\\u94fe\\u63a5","title":"url","type":"input","param":""}]''),
(37, ''小程序首页猜你喜欢banner'', ''小程序首页猜你喜欢banner'', ''routine_lovely'', ''[{"name":"\\u56fe\\u7247","title":"img","type":"upload","param":""}]''),
(47, ''小程序商城首页分类按钮'', ''小程序商城首页分类按钮'', ''routine_home_menus'', ''[{"name":"\\u5206\\u7c7b\\u540d\\u79f0","title":"name","type":"input","param":""},{"name":"\\u5206\\u7c7b\\u56fe\\u6807","title":"pic","type":"upload","param":""},{"name":"\\u8df3\\u8f6c\\u8def\\u5f84","title":"url","type":"input","param":""},{"name":"\\u5e95\\u90e8\\u83dc\\u5355","title":"show","type":"radio","param":"\\u662f-\\u5426"}]''),
(48, ''小程序商城首页banner'', ''小程序商城首页banner'', ''routine_home_banner'', ''[{"name":"\\u6807\\u9898","title":"name","type":"input","param":""},{"name":"\\u94fe\\u63a5","title":"url","type":"input","param":""},{"name":"\\u56fe\\u7247","title":"pic","type":"upload","param":""}]'');

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_group_data`
--

CREATE TABLE IF NOT EXISTS `eb_system_group_data` (
  `id` int(11) NOT NULL COMMENT ''组合数据详情ID'',
  `gid` int(11) NOT NULL COMMENT ''对应的数据组id'',
  `value` text NOT NULL COMMENT ''数据组对应的数据值（json数据）'',
  `add_time` int(10) NOT NULL COMMENT ''添加数据时间'',
  `sort` int(11) NOT NULL COMMENT ''数据排序'',
  `status` tinyint(1) NOT NULL DEFAULT ''1'' COMMENT ''状态（1：开启；2：关闭；）''
) ENGINE=MyISAM AUTO_INCREMENT=108 DEFAULT CHARSET=utf8 COMMENT=''组合数据详情表'';

--
-- 转存表中的数据 `eb_system_group_data`
--

INSERT INTO `eb_system_group_data` (`id`, `gid`, `value`, `add_time`, `sort`, `status`) VALUES
(52, 32, ''{"name":{"type":"input","value":"\\u6211\\u7684\\u79ef\\u5206"},"icon":{"type":"upload","value":"http:\\/\\/doemo.net\\/public\\/uploads\\/0\\/20180824\\/5b7f542607db7.png"},"url":{"type":"input","value":"\\/wap\\/my\\/integral.html"}}'', 1513846430, 1, 1),
(53, 32, ''{"name":{"type":"input","value":"\\u4f18\\u60e0\\u5238"},"icon":{"type":"upload","value":"http:\\/\\/doemo.net\\/public\\/uploads\\/0\\/20180824\\/5b7f54262f2a8.png"},"url":{"type":"input","value":"\\/wap\\/my\\/coupon.html"}}'', 1513846448, 1, 1),
(84, 34, ''{"title":{"type":"input","value":"banner3"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/11\\/20180817\\/5b7670c42f24b.jpg"}}'', 1522135667, 11, 1),
(56, 32, ''{"name":{"type":"input","value":"\\u5df2\\u6536\\u85cf\\u5546\\u54c1"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5abc57454d6c7.png"},"url":{"type":"input","value":"\\/wap\\/my\\/collect.html"}}'', 1513846605, 1, 1),
(57, 32, ''{"name":{"type":"input","value":"\\u5730\\u5740\\u7ba1\\u7406"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5abc574fc0570.png"},"url":{"type":"input","value":"\\/wap\\/my\\/address.html"}}'', 1513846618, 1, 1),
(87, 32, ''{"name":{"type":"input","value":"\\u6211\\u7684\\u4f59\\u989d"},"icon":{"type":"upload","value":"http:\\/\\/doemo.net\\/public\\/uploads\\/0\\/20180824\\/5b7f54257b159.png"},"url":{"type":"input","value":"\\/wap\\/my\\/balance.html"}}'', 1525330614, 1, 1),
(67, 32, ''{"name":{"type":"input","value":"\\u804a\\u5929\\u8bb0\\u5f55"},"icon":{"type":"upload","value":"\\/public\\/uploads\\/common\\/s_5abc576dba8a2.png"},"url":{"type":"input","value":"\\/wap\\/service\\/service_new.html"}}'', 1515570261, 1, 1),
(68, 34, ''{"title":{"type":"input","value":"banner1"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2e4b6cf2.jpg"}}'', 1515984801, 10, 1),
(69, 34, ''{"title":{"type":"input","value":"banner2"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2aaa33d8.jpg"}}'', 1515984809, 9, 2),
(71, 35, ''{"name":{"type":"input","value":"\\u780d\\u4ef7\\u6d3b\\u52a8"},"url":{"type":"input","value":"\\/wap\\/store\\/cut_list.html"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6c0c5fdaae5.png"}}'', 1515985418, 1, 1),
(72, 35, ''{"name":{"type":"input","value":"\\u9886\\u5238\\u4e2d\\u5fc3"},"url":{"type":"input","value":"\\/wap\\/store\\/issue_coupon.html"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6c0e995f6d4.png"}}'', 1515985426, 1, 1),
(74, 35, ''{"name":{"type":"input","value":"\\u6bcf\\u65e5\\u7b7e\\u5230"},"url":{"type":"input","value":"\\/wap\\/my\\/sign_in.html"},"icon":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6c0c5fe73e6.png"}}'', 1515985441, 1, 1),
(80, 36, ''{"info":{"type":"input","value":"CRMEB\\u7535\\u5546\\u7cfb\\u7edf V 2.5 \\u5373\\u5c06\\u4e0a\\u7ebf\\uff01"},"url":{"type":"input","value":"#"}}'', 1515985907, 1, 1),
(81, 36, ''{"info":{"type":"input","value":"CRMEB\\u5546\\u57ce V 2.5 \\u5c0f\\u7a0b\\u5e8f\\u516c\\u4f17\\u53f7\\u6570\\u636e\\u540c\\u6b65\\uff01"},"url":{"type":"input","value":"#"}}'', 1515985918, 1, 1),
(107, 37, ''{"img":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/8\\/20180817\\/5b768dfd6189a.jpg"}}'', 1534496260, 0, 1),
(88, 37, ''{"img":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2b0969d7.jpg"}}'', 1526699754, 2, 1),
(89, 38, ''{"banner":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2436876e.jpg"}}'', 1527153599, 1, 1),
(86, 32, ''{"name":{"type":"input","value":"\\u8054\\u7cfb\\u5ba2\\u670d"},"icon":{"type":"upload","value":"http:\\/\\/doemo.net\\/public\\/uploads\\/0\\/20180824\\/5b7f5424a1a59.png"},"url":{"type":"input","value":"\\/wap\\/index\\/about.html"}}'', 1522310836, 1, 1),
(90, 34, ''{"title":{"type":"input","value":"1"},"url":{"type":"input","value":"#"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2a4445a8.jpg"}}'', 1527823558, 1, 2),
(91, 37, ''{"img":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2deb5b20.jpg"}}'', 1528688012, 1, 1),
(92, 32, ''{"name":{"type":"input","value":"\\u63a8\\u5e7f\\u4f63\\u91d1"},"icon":{"type":"upload","value":"http:\\/\\/doemo.net\\/public\\/uploads\\/0\\/20180824\\/5b7f54262f2a8.png"},"url":{"type":"input","value":"\\/wap\\/my\\/user_pro.html"}}'', 1530688244, 1, 1),
(99, 47, ''{"name":{"type":"input","value":"\\u5206\\u7c7b"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180808\\/5b6ab28559200.png"},"url":{"type":"input","value":"\\/pages\\/productSort\\/productSort"},"show":{"type":"radio","value":"\\u662f"}}'', 1533721963, 1, 1),
(101, 47, ''{"name":{"type":"input","value":"\\u79d2\\u6740"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180808\\/5b6abda83dc2a.png"},"url":{"type":"input","value":"\\/pages\\/miao-list\\/miao-list"},"show":{"type":"radio","value":"\\u5426"}}'', 1533722037, 1, 1),
(103, 48, ''{"name":{"type":"input","value":"banenr1"},"url":{"type":"input","value":"\\/pages\\/miao-list\\/miao-list"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/editor\\/20180601\\/5b10b2d35dc37.jpg"}}'', 1533722245, 1, 1),
(104, 48, ''{"name":{"type":"input","value":"banenr2"},"url":{"type":"input","value":"\\/pages\\/pink-list\\/index"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/11\\/20180817\\/5b7670c42f24b.jpg"}}'', 1533722286, 10, 1),
(105, 47, ''{"name":{"type":"input","value":"\\u54a8\\u8be2"},"pic":{"type":"upload","value":"http:\\/\\/shop.crmeb.net\\/public\\/uploads\\/0\\/20180809\\/5b6bedbcb2f17.png"},"url":{"type":"input","value":"\\/pages\\/new-list\\/new-list"},"show":{"type":"radio","value":"\\u5426"}}'', 1533797064, 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_log`
--

CREATE TABLE IF NOT EXISTS `eb_system_log` (
  `id` int(10) unsigned NOT NULL COMMENT ''管理员操作记录ID'',
  `admin_id` int(10) unsigned NOT NULL COMMENT ''管理员id'',
  `admin_name` varchar(64) NOT NULL COMMENT ''管理员姓名'',
  `path` varchar(128) NOT NULL COMMENT ''链接'',
  `page` varchar(64) NOT NULL COMMENT ''行为'',
  `method` varchar(12) NOT NULL COMMENT ''访问类型'',
  `ip` varchar(16) NOT NULL COMMENT ''登录IP'',
  `type` varchar(32) NOT NULL COMMENT ''类型'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''操作时间'',
  `merchant_id` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''商户id''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''管理员操作记录表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_menus`
--

CREATE TABLE IF NOT EXISTS `eb_system_menus` (
  `id` smallint(5) unsigned NOT NULL COMMENT ''菜单ID'',
  `pid` smallint(5) unsigned NOT NULL DEFAULT ''0'' COMMENT ''父级id'',
  `icon` varchar(16) NOT NULL COMMENT ''图标'',
  `menu_name` varchar(32) NOT NULL DEFAULT '''' COMMENT ''按钮名'',
  `module` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '''' COMMENT ''模块名'',
  `controller` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT ''控制器'',
  `action` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '''' COMMENT ''方法名'',
  `params` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT ''[]'' COMMENT ''参数'',
  `sort` tinyint(3) NOT NULL DEFAULT ''1'' COMMENT ''排序'',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''是否显示'',
  `access` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''子管理员是否可用''
) ENGINE=MyISAM AUTO_INCREMENT=377 DEFAULT CHARSET=utf8 COMMENT=''菜单表'';

--
-- 转存表中的数据 `eb_system_menus`
--

INSERT INTO `eb_system_menus` (`id`, `pid`, `icon`, `menu_name`, `module`, `controller`, `action`, `params`, `sort`, `is_show`, `access`) VALUES
(1, 289, '''', ''系统设置'', ''admin'', ''setting.systemConfig'', ''index'', ''[]'', 90, 1, 1),
(2, 153, '''', ''权限规则'', ''admin'', ''setting.systemMenus'', ''index'', ''{"cate":"12"}'', 7, 1, 1),
(4, 153, '''', ''管理员列表'', ''admin'', ''setting.systemAdmin'', ''index'', ''[]'', 9, 1, 1),
(6, 1, '''', ''系统配置'', ''admin'', ''setting.systemConfig'', ''index'', ''{"tab_id":"1"}'', 1, 1, 1),
(7, 1, '''', ''配置分类'', ''admin'', ''setting.systemConfigTab'', ''index'', ''[]'', 1, 1, 1),
(8, 153, '''', ''身份管理'', ''admin'', ''setting.systemRole'', ''index'', ''[]'', 10, 1, 1),
(9, 1, '''', ''组合数据'', ''admin'', ''setting.systemGroup'', ''index'', ''[]'', 1, 1, 1),
(11, 0, ''wechat'', ''公众号'', ''admin'', ''wechat.wechat'', ''index'', ''[]'', 91, 1, 1),
(12, 354, '''', ''微信关注回复'', ''admin'', ''wechat.reply'', ''index'', ''{"key":"subscribe","title":"\\u7f16\\u8f91\\u65e0\\u914d\\u7f6e\\u9ed8\\u8ba4\\u56de\\u590d"}'', 86, 1, 1),
(17, 360, '''', ''微信菜单'', ''admin'', ''wechat.menus'', ''index'', ''[]'', 95, 1, 1),
(286, 0, ''paper-plane'', ''营销'', ''admin'', '''', '''', ''[]'', 105, 1, 1),
(19, 11, '''', ''图文管理'', ''admin'', ''wechat.wechatNewsCategory'', ''index'', ''[]'', 60, 1, 1),
(21, 0, ''magic'', ''维护'', ''admin'', ''system.system'', '''', ''[]'', 1, 1, 1),
(23, 0, ''laptop'', ''商品'', ''admin'', ''store.store'', ''index'', ''[]'', 110, 1, 1),
(24, 23, '''', ''商品管理'', ''admin'', ''store.storeProduct'', ''index'', ''{"type":"1"}'', 100, 1, 1),
(25, 23, '''', ''商品分类'', ''admin'', ''store.storeCategory'', ''index'', ''[]'', 1, 1, 1),
(26, 285, '''', ''订单管理'', ''admin'', ''order.storeOrder'', ''index'', ''[]'', 1, 1, 1),
(30, 354, '''', ''关键字回复'', ''admin'', ''wechat.reply'', ''keyword'', ''[]'', 85, 1, 1),
(31, 354, '''', ''无效关键词回复'', ''admin'', ''wechat.reply'', ''index'', ''{"key":"default","title":"\\u7f16\\u8f91\\u65e0\\u6548\\u5173\\u952e\\u5b57\\u9ed8\\u8ba4\\u56de\\u590d"}'', 84, 1, 1),
(33, 284, '''', ''附加权限'', ''admin'', ''article.articleCategory'', '''', ''[]'', 0, 0, 1),
(34, 33, '''', ''添加文章分类'', ''admin'', ''article.articleCategory'', ''create'', ''[]'', 0, 0, 1),
(35, 33, '''', ''编辑文章分类'', ''admin'', ''article.articleCategory'', ''edit'', ''[]'', 0, 0, 1),
(36, 33, '''', ''删除文章分类'', ''admin'', ''article.articleCategory'', ''delete'', ''[]'', 0, 0, 1),
(37, 31, '''', ''附加权限'', ''admin'', ''wechat.reply'', '''', ''[]'', 0, 0, 1),
(38, 283, '''', ''附加权限'', ''admin'', ''article.article'', '''', ''[]'', 0, 0, 1),
(39, 38, '''', ''添加文章'', ''admin'', ''article. article'', ''create'', ''[]'', 0, 0, 1),
(40, 38, '''', ''编辑文章'', ''admin'', ''article. article'', ''add_new'', ''[]'', 0, 0, 1),
(41, 38, '''', ''删除文章'', ''admin'', ''article. article'', ''delete'', ''[]'', 0, 0, 1),
(42, 19, '''', ''附加权限'', ''admin'', ''wechat.wechatNewsCategory'', '''', ''[]'', 0, 0, 1),
(43, 42, '''', ''添加图文消息'', ''admin'', ''wechat.wechatNewsCategory'', ''create'', ''[]'', 0, 0, 1),
(44, 42, '''', ''编辑图文消息'', ''admin'', ''wechat.wechatNewsCategory'', ''edit'', ''[]'', 0, 0, 1),
(45, 42, '''', ''删除图文消息'', ''admin'', ''wechat.wechatNewsCategory'', ''delete'', ''[]'', 0, 0, 1),
(46, 7, '''', ''配置分类附加权限'', ''admin'', ''setting.systemConfigTab'', '''', ''[]'', 0, 0, 1),
(47, 46, '''', ''添加配置分类'', ''admin'', ''setting.systemConfigTab'', ''create'', ''[]'', 0, 0, 1),
(48, 46, '''', ''添加配置'', ''admin'', ''setting.systemConfig'', ''create'', ''[]'', 0, 0, 1),
(49, 46, '''', ''编辑配置分类'', ''admin'', ''setting.systemConfigTab'', ''edit'', ''[]'', 0, 0, 1),
(50, 46, '''', ''删除配置分类'', ''admin'', ''setting.systemConfigTab'', ''delete'', ''[]'', 0, 0, 1),
(51, 46, '''', ''查看子字段'', ''admin'', ''system.systemConfigTab'', ''sonConfigTab'', ''[]'', 0, 0, 1),
(52, 9, '''', ''组合数据附加权限'', ''admin'', ''system.systemGroup'', '''', ''[]'', 0, 0, 1),
(53, 52, '''', ''添加数据'', ''admin'', ''system.systemGroupData'', ''create'', ''[]'', 0, 0, 1),
(54, 52, '''', ''编辑数据'', ''admin'', ''system.systemGroupData'', ''edit'', ''[]'', 0, 0, 1),
(55, 52, '''', ''删除数据'', ''admin'', ''system.systemGroupData'', ''delete'', ''[]'', 0, 0, 1),
(56, 52, '''', ''数据列表'', ''admin'', ''system.systemGroupData'', ''index'', ''[]'', 0, 0, 1),
(57, 52, '''', ''添加数据组'', ''admin'', ''system.systemGroup'', ''create'', ''[]'', 0, 0, 1),
(58, 52, '''', ''删除数据组'', ''admin'', ''system.systemGroup'', ''delete'', ''[]'', 0, 0, 1),
(59, 4, '''', ''管理员列表附加权限'', ''admin'', ''system.systemAdmin'', '''', ''[]'', 0, 0, 1),
(60, 59, '''', ''添加管理员'', ''admin'', ''system.systemAdmin'', ''create'', ''[]'', 0, 0, 1),
(61, 59, '''', ''编辑管理员'', ''admin'', ''system.systemAdmin'', ''edit'', ''[]'', 0, 0, 1),
(62, 59, '''', ''删除管理员'', ''admin'', ''system.systemAdmin'', ''delete'', ''[]'', 0, 0, 1),
(63, 8, '''', ''身份管理附加权限'', ''admin'', ''system.systemRole'', '''', ''[]'', 0, 0, 1),
(64, 63, '''', ''添加身份'', ''admin'', ''system.systemRole'', ''create'', ''[]'', 0, 0, 1),
(65, 63, '''', ''修改身份'', ''admin'', ''system.systemRole'', ''edit'', ''[]'', 0, 0, 1),
(66, 63, '''', ''删除身份'', ''admin'', ''system.systemRole'', ''delete'', ''[]'', 0, 0, 1),
(67, 8, '''', ''身份管理展示页'', ''admin'', ''system.systemRole'', ''index'', ''[]'', 0, 0, 1),
(68, 4, '''', ''管理员列表展示页'', ''admin'', ''system.systemAdmin'', ''index'', ''[]'', 0, 0, 1),
(69, 7, '''', ''配置分类展示页'', ''admin'', ''setting.systemConfigTab'', ''index'', ''[]'', 0, 0, 1),
(70, 9, '''', ''组合数据展示页'', ''admin'', ''system.systemGroup'', ''index'', ''[]'', 0, 0, 1),
(71, 284, '''', ''文章分类管理展示页'', ''admin'', ''article.articleCategory'', ''index'', ''[]'', 0, 0, 1),
(72, 283, '''', ''文章管理展示页'', ''admin'', ''article.article'', ''index'', ''[]'', 0, 0, 1),
(73, 19, '''', ''图文消息展示页'', ''admin'', ''wechat.wechatNewsCategory'', ''index'', ''[]'', 0, 0, 1),
(74, 2, '''', ''菜单管理附加权限'', ''admin'', ''system.systemMenus'', '''', ''[]'', 0, 0, 1),
(75, 74, '''', ''添加菜单'', ''admin'', ''system.systemMenus'', ''create'', ''[]'', 0, 0, 1),
(76, 74, '''', ''编辑菜单'', ''admin'', ''system.systemMenus'', ''edit'', ''[]'', 0, 0, 1),
(77, 74, '''', ''删除菜单'', ''admin'', ''system.systemMenus'', ''delete'', ''[]'', 0, 0, 1),
(78, 2, '''', ''菜单管理展示页'', ''admin'', ''system.systemMenus'', ''index'', ''[]'', 0, 0, 1),
(352, 148, '''', ''优惠券配置'', ''admin'', ''setting.systemConfig'', ''index'', ''{"type":"3","tab_id":"12"}'', 0, 1, 1),
(80, 0, ''leanpub'', ''内容'', ''admin'', ''wechat.wechatNews'', ''index'', ''[]'', 90, 1, 1),
(82, 151, '''', ''微信用户管理'', ''admin'', ''user'', ''list'', ''[]'', 5, 1, 1),
(84, 82, '''', ''用户标签'', ''admin'', ''wechat.wechatUser'', ''tag'', ''[]'', 0, 1, 1),
(89, 30, '''', ''关键字回复附加权限'', ''admin'', ''wechat.reply'', '''', ''[]'', 0, 0, 1),
(90, 89, '''', ''添加关键字'', ''admin'', ''wechat.reply'', ''add_keyword'', ''[]'', 0, 0, 1),
(91, 89, '''', ''修改关键字'', ''admin'', ''wechat.reply'', ''info_keyword'', ''[]'', 0, 0, 1),
(92, 89, '''', ''删除关键字'', ''admin'', ''wechat.reply'', ''delete'', ''[]'', 0, 0, 1),
(93, 30, '''', ''关键字回复展示页'', ''admin'', ''wechat.reply'', ''keyword'', ''[]'', 0, 0, 1),
(94, 31, '''', ''无效关键词回复展示页'', ''admin'', ''wechat.reply'', ''index'', ''[]'', 0, 0, 1),
(95, 31, '''', ''无效关键词回复附加权限'', ''admin'', ''wechat.reply'', '''', ''[]'', 0, 0, 1),
(96, 95, '''', ''无效关键词回复提交按钮'', ''admin'', ''wechat.reply'', ''save'', ''{"key":"default","title":"编辑无效关键字默认回复"}'', 0, 0, 1),
(97, 12, '''', ''微信关注回复展示页'', ''admin'', ''wechat.reply'', ''index'', ''[]'', 0, 0, 1),
(98, 12, '''', ''微信关注回复附加权限'', ''admin'', ''wechat.reply'', '''', ''[]'', 0, 0, 1),
(99, 98, '''', ''微信关注回复提交按钮'', ''admin'', ''wechat.reply'', ''save'', ''{"key":"subscribe","title":"编辑无配置默认回复"}'', 0, 0, 1),
(100, 74, '''', ''添加提交菜单'', ''admin'', ''system.systemMenus'', ''save'', ''[]'', 0, 0, 1),
(101, 74, '''', ''编辑提交菜单'', ''admin'', ''system.systemMenus'', ''update'', ''[]'', 0, 1, 1),
(102, 59, '''', ''提交添加管理员'', ''admin'', ''system.systemAdmin'', ''save'', ''[]'', 0, 0, 1),
(103, 59, '''', ''提交修改管理员'', ''admin'', ''system.systemAdmin'', ''update'', ''[]'', 0, 0, 1),
(104, 63, '''', ''提交添加身份'', ''admin'', ''system.systemRole'', ''save'', ''[]'', 0, 0, 1),
(105, 63, '''', ''提交修改身份'', ''admin'', ''system.systemRole'', ''update'', ''[]'', 0, 0, 1),
(106, 46, '''', ''提交添加配置分类'', ''admin'', ''setting.systemConfigTab'', ''save'', ''[]'', 0, 0, 1),
(107, 46, '''', ''提交修改配置分类'', ''admin'', ''setting.systemConfigTab'', ''update'', ''[]'', 0, 0, 1),
(108, 46, '''', ''提交添加配置列表'', ''admin'', ''setting.systemConfig'', ''save'', ''[]'', 0, 0, 1),
(109, 52, '''', ''提交添加数据组'', ''admin'', ''system.systemGroup'', ''save'', ''[]'', 0, 0, 1),
(110, 52, '''', ''提交修改数据组'', ''admin'', ''system.systemGroup'', ''update'', ''[]'', 0, 0, 1),
(111, 52, '''', ''提交添加数据'', ''admin'', ''system.systemGroupData'', ''save'', ''[]'', 0, 0, 1),
(112, 52, '''', ''提交修改数据'', ''admin'', ''system.systemGroupData'', ''update'', ''[]'', 0, 0, 1),
(113, 33, '''', ''提交添加文章分类'', ''admin'', ''article.articleCategory'', ''save'', ''[]'', 0, 0, 1),
(114, 33, '''', ''提交添加文章分类'', ''admin'', ''article.articleCategory'', ''update'', ''[]'', 0, 0, 1),
(115, 42, '''', ''提交添加图文消息'', ''admin'', ''wechat.wechatNewsCategory'', ''save'', ''[]'', 0, 0, 1),
(116, 42, '''', ''提交编辑图文消息'', ''admin'', ''wechat.wechatNewsCategory'', ''update'', ''[]'', 0, 0, 1),
(117, 6, '''', ''配置列表附加权限'', ''admin'', ''setting.systemConfig'', '''', ''[]'', 0, 0, 1),
(118, 6, '''', ''配置列表展示页'', ''admin'', ''setting.systemConfig'', ''index'', ''[]'', 0, 0, 1),
(119, 117, '''', ''提交保存配置列表'', ''admin'', ''setting.systemConfig'', ''save_basics'', ''[]'', 0, 0, 1),
(123, 89, '''', ''提交添加关键字'', ''admin'', ''wechat.reply'', ''save_keyword'', ''{"dis":"1"}'', 0, 0, 1),
(124, 89, '''', ''提交修改关键字'', ''admin'', ''wechat.reply'', ''save_keyword'', ''{"dis":"2"}'', 0, 0, 1),
(126, 17, '''', ''微信菜单展示页'', ''admin'', ''wechat.menus'', ''index'', ''[]'', 0, 0, 1),
(127, 17, '''', ''微信菜单附加权限'', ''admin'', ''wechat.menus'', '''', ''[]'', 0, 0, 1),
(128, 127, '''', ''提交微信菜单按钮'', ''admin'', ''wechat.menus'', ''save'', ''{"dis":"1"}'', 0, 0, 1),
(129, 82, '''', ''用户行为纪录'', ''admin'', ''wechat.wechatMessage'', ''index'', ''[]'', 0, 1, 1),
(130, 21, '''', ''系统日志'', ''admin'', ''system.systemLog'', ''index'', ''[]'', 5, 1, 1),
(131, 130, '''', ''管理员操作记录展示页'', ''admin'', ''system.systemLog'', ''index'', ''[]'', 0, 0, 1),
(132, 129, '''', ''微信用户行为纪录展示页'', ''admin'', ''wechat.wechatMessage'', ''index'', ''[]'', 0, 0, 1),
(133, 82, '''', ''微信用户'', ''admin'', ''wechat.wechatUser'', ''index'', ''[]'', 1, 1, 1),
(134, 133, '''', ''微信用户展示页'', ''admin'', ''wechat.wechatUser'', ''index'', ''[]'', 0, 0, 1),
(241, 273, '''', ''限时秒杀'', ''admin'', ''ump.storeSeckill'', ''index'', ''[]'', 0, 1, 1),
(137, 135, '''', ''添加通知模板'', ''admin'', ''system.systemNotice'', ''create'', ''[]'', 0, 0, 1),
(138, 135, '''', ''编辑通知模板'', ''admin'', ''system.systemNotice'', ''edit'', ''[]'', 0, 0, 1),
(139, 135, '''', ''删除辑通知模板'', ''admin'', ''system.systemNotice'', ''delete'', ''[]'', 0, 0, 1),
(140, 135, '''', ''提交编辑辑通知模板'', ''admin'', ''system.systemNotice'', ''update'', ''[]'', 0, 0, 1),
(141, 135, '''', ''提交添加辑通知模板'', ''admin'', ''system.systemNotice'', ''save'', ''[]'', 0, 0, 1),
(142, 25, '''', ''产品分类展示页'', ''admin'', ''store.storeCategory'', ''index'', ''[]'', 0, 0, 1),
(143, 25, '''', ''产品分类附加权限'', ''admin'', ''store.storeCategory'', '''', ''[]'', 0, 0, 1),
(144, 117, '''', ''获取配置列表上传文件的名称'', ''admin'', ''setting.systemConfig'', ''getimagename'', ''[]'', 0, 0, 1),
(145, 117, '''', ''配置列表上传文件'', ''admin'', ''setting.systemConfig'', ''view_upload'', ''[]'', 0, 0, 1),
(146, 24, '''', ''产品管理展示页'', ''admin'', ''store.storeProduct'', ''index'', ''[]'', 0, 0, 1),
(147, 24, '''', ''产品管理附加权限'', ''admin'', ''store.storeProduct'', '''', ''[]'', 0, 0, 1),
(148, 286, '''', ''优惠券'', '''', '''', '''', ''[]'', 10, 1, 1),
(149, 148, '''', ''优惠券制作'', ''admin'', ''ump.storeCoupon'', ''index'', ''[]'', 5, 1, 1),
(150, 148, '''', ''会员领取记录'', ''admin'', ''ump.storeCouponUser'', ''index'', ''[]'', 1, 1, 1),
(151, 0, ''user'', ''会员'', ''admin'', ''user.userList'', ''list'', ''[]'', 107, 1, 1),
(153, 289, '''', ''管理权限'', ''admin'', ''setting.systemAdmin'', '''', ''[]'', 100, 1, 1),
(155, 154, '''', ''商户产品展示页'', ''admin'', ''store.storeMerchant'', ''index'', ''[]'', 0, 0, 1),
(156, 154, '''', ''商户产品附加权限'', ''admin'', ''store.storeMerchant'', '''', ''[]'', 0, 0, 1),
(158, 157, '''', ''商户文章管理展示页'', ''admin'', ''wechat.wechatNews'', ''merchantIndex'', ''[]'', 0, 0, 1),
(159, 157, '''', ''商户文章管理附加权限'', ''admin'', ''wechat.wechatNews'', '''', ''[]'', 0, 0, 1),
(170, 290, '''', ''评论管理'', ''admin'', ''store.storeProductReply'', '''', ''[]'', 0, 1, 1),
(173, 21, '''', ''文件校验'', ''admin'', ''system.system_file'', ''index'', ''[]'', 1, 1, 1),
(174, 360, '''', ''微信模板消息'', ''admin'', ''wechat.wechatTemplate'', ''index'', ''[]'', 1, 1, 1),
(175, 11, '''', ''客服管理'', ''admin'', ''wechat.storeService'', ''index'', ''[]'', 70, 1, 1),
(176, 151, '''', ''站内通知'', ''admin'', ''user.user_notice'', ''index'', ''[]'', 8, 1, 1),
(177, 151, '''', ''会员管理'', ''admin'', ''user.user'', ''index'', ''[]'', 10, 1, 1),
(179, 307, '''', ''充值记录'', ''admin'', ''finance.userRecharge'', ''index'', ''[]'', 1, 1, 1),
(190, 26, '''', ''订单管理展示页'', ''admin'', ''store.storeOrder'', ''index'', ''[]'', 0, 0, 1),
(191, 26, '''', ''订单管理附加权限'', ''admin'', ''store.storeOrder'', '''', ''[]'', 0, 0, 1),
(192, 191, '''', ''订单管理去发货'', ''admin'', ''store.storeOrder'', ''deliver_goods'', ''[]'', 0, 0, 1),
(193, 191, '''', ''订单管理备注'', ''admin'', ''store.storeOrder'', ''remark'', ''[]'', 0, 0, 1),
(194, 191, '''', ''订单管理去送货'', ''admin'', ''store.storeOrder'', ''delivery'', ''[]'', 0, 0, 1),
(195, 191, '''', ''订单管理已收货'', ''admin'', ''store.storeOrder'', ''take_delivery'', ''[]'', 0, 0, 1),
(196, 191, '''', ''订单管理退款'', ''admin'', ''store.storeOrder'', ''refund_y'', ''[]'', 0, 0, 1),
(197, 191, '''', ''订单管理修改订单'', ''admin'', ''store.storeOrder'', ''edit'', ''[]'', 0, 0, 1),
(198, 191, '''', ''订单管理修改订单提交'', ''admin'', ''store.storeOrder'', ''update'', ''[]'', 0, 0, 1),
(199, 191, '''', ''订单管理退积分'', ''admin'', ''store.storeOrder'', ''integral_back'', ''[]'', 0, 0, 1),
(200, 191, '''', ''订单管理退积分提交'', ''admin'', ''store.storeOrder'', ''updateIntegralBack'', ''[]'', 0, 0, 1),
(201, 191, '''', ''订单管理立即支付'', ''admin'', ''store.storeOrder'', ''offline'', ''[]'', 0, 0, 1),
(202, 191, '''', ''订单管理退款原因'', ''admin'', ''store.storeOrder'', ''refund_n'', ''[]'', 0, 0, 1),
(203, 191, '''', ''订单管理退款原因提交'', ''admin'', ''store.storeOrder'', ''updateRefundN'', ''[]'', 0, 0, 1),
(204, 191, '''', ''订单管理修改配送信息'', ''admin'', ''store.storeOrder'', ''distribution'', ''[]'', 0, 0, 1),
(205, 191, '''', ''订单管理修改配送信息提交'', ''admin'', ''store.storeOrder'', ''updateDistribution'', ''[]'', 0, 0, 1),
(206, 191, '''', ''订单管理退款提交'', ''admin'', ''store.storeOrder'', ''updateRefundY'', ''[]'', 0, 0, 1),
(207, 191, '''', ''订单管理去发货提交'', ''admin'', ''store.storeOrder'', ''updateDeliveryGoods'', ''[]'', 0, 0, 1),
(208, 191, '''', ''订单管理去送货提交'', ''admin'', ''store.storeOrder'', ''updateDelivery'', ''[]'', 0, 0, 1),
(209, 175, '''', ''客服管理展示页'', ''admin'', ''store.storeService'', ''index'', ''[]'', 0, 0, 1),
(210, 175, '''', ''客服管理附加权限'', ''admin'', ''store.storeService'', '''', ''[]'', 0, 0, 1),
(211, 210, '''', ''客服管理添加'', ''admin'', ''store.storeService'', ''create'', ''[]'', 0, 0, 1),
(212, 210, '''', ''客服管理添加提交'', ''admin'', ''store.storeService'', ''save'', ''[]'', 0, 0, 1),
(213, 210, '''', ''客服管理编辑'', ''admin'', ''store.storeService'', ''edit'', ''[]'', 0, 0, 1),
(214, 210, '''', ''客服管理编辑提交'', ''admin'', ''store.storeService'', ''update'', ''[]'', 0, 0, 1),
(215, 210, '''', ''客服管理删除'', ''admin'', ''store.storeService'', ''delete'', ''[]'', 0, 0, 1),
(216, 179, '''', ''用户充值记录展示页'', ''admin'', ''user.userRecharge'', ''index'', ''[]'', 0, 0, 1),
(217, 179, '''', ''用户充值记录附加权限'', ''admin'', ''user.userRecharge'', '''', ''[]'', 0, 0, 1),
(218, 217, '''', ''用户充值记录退款'', ''admin'', ''user.userRecharge'', ''edit'', ''[]'', 0, 0, 1),
(219, 217, '''', ''用户充值记录退款提交'', ''admin'', ''user.userRecharge'', ''updaterefundy'', ''[]'', 0, 0, 1),
(220, 180, '''', ''预售卡管理批量修改预售卡金额'', ''admin'', ''presell.presellCard'', ''batch_price'', ''[]'', 0, 0, 1),
(221, 180, '''', ''预售卡管理批量修改预售卡金额提交'', ''admin'', ''presell.presellCard'', ''savebatch'', ''[]'', 0, 0, 1),
(222, 210, '''', ''客服管理聊天记录查询'', ''admin'', ''store.storeService'', ''chat_user'', ''[]'', 0, 0, 1),
(223, 210, '''', ''客服管理聊天记录查询详情'', ''admin'', ''store.storeService'', ''chat_list'', ''[]'', 0, 0, 1),
(224, 170, '''', ''评论管理展示页'', ''admin'', ''store.storeProductReply'', ''index'', ''[]'', 0, 0, 1),
(225, 170, '''', ''评论管理附加权限'', ''admin'', ''store.storeProductReply'', '''', ''[]'', 0, 0, 1),
(226, 225, '''', ''评论管理回复评论'', ''admin'', ''store.storeProductReply'', ''set_reply'', ''[]'', 0, 0, 1),
(227, 225, '''', ''评论管理修改回复评论'', ''admin'', ''store.storeProductReply'', ''edit_reply'', ''[]'', 0, 0, 1),
(228, 225, '''', ''评论管理删除评论'', ''admin'', ''store.storeProductReply'', ''delete'', ''[]'', 0, 0, 1),
(229, 149, '''', ''优惠券管理展示页'', ''admin'', ''store.storeCoupon'', ''index'', ''[]'', 0, 0, 1),
(230, 149, '''', ''优惠券管理附加权限'', ''admin'', ''store.storeCoupon'', '''', ''[]'', 0, 0, 1),
(231, 230, '''', ''优惠券管理添加'', ''admin'', ''store.storeCoupon'', ''create'', ''[]'', 0, 0, 1),
(232, 230, '''', ''优惠券管理添加提交'', ''admin'', ''store.storeCoupon'', ''save'', ''[]'', 0, 0, 1),
(233, 230, '''', ''优惠券管理删除'', ''admin'', ''store.storeCoupon'', ''delete'', ''[]'', 0, 0, 1),
(234, 230, '''', ''优惠券管理立即失效'', ''admin'', ''store.storeCoupon'', ''status'', ''[]'', 0, 0, 1),
(235, 148, '''', ''已发布管理'', ''admin'', ''ump.storeCouponIssue'', ''index'', ''[]'', 3, 1, 1),
(236, 82, '''', ''用户分组'', ''admin'', ''wechat.wechatUser'', ''group'', ''[]'', 0, 1, 1),
(237, 21, '''', ''刷新缓存'', ''admin'', ''system.clear'', ''index'', ''[]'', 0, 1, 1),
(239, 306, '''', ''提现申请'', ''admin'', ''finance.user_extract'', ''index'', ''[]'', 0, 1, 1),
(351, 349, '''', ''积分日志'', ''admin'', ''ump.userPoint'', ''index'', ''[]'', 0, 1, 1),
(244, 294, '''', ''财务报表'', ''admin'', ''record.storeStatistics'', ''index'', ''[]'', 0, 1, 1),
(245, 293, '''', ''商品统计'', ''admin'', ''store.storeProduct'', ''statistics'', ''[]'', 0, 0, 1),
(246, 295, '''', ''用户统计'', ''admin'', ''user.user'', ''user_analysis'', ''[]'', 0, 1, 1),
(247, 153, '''', ''个人资料'', ''admin'', ''setting.systemAdmin'', ''admininfo'', ''[]'', 0, 0, 1),
(248, 247, '''', ''个人资料附加权限'', ''admin'', ''system.systemAdmin'', '''', ''[]'', 0, 0, 1),
(249, 248, '''', ''个人资料提交保存'', ''admin'', ''system.systemAdmin'', ''setAdminInfo'', ''[]'', 0, 0, 1),
(250, 247, '''', ''个人资料展示页'', ''admin'', ''system.systemAdmin'', ''admininfo'', ''[]'', 0, 0, 1),
(251, 293, '''', ''订单统计'', ''admin'', ''order.storeOrder'', ''orderchart'', ''[]'', 0, 1, 1),
(252, 21, '''', ''在线更新'', ''admin'', ''system.system_upgradeclient'', ''index'', ''[]'', 0, 1, 1),
(253, 259, '''', ''添加更新包'', ''admin'', ''server.server_upgrade'', ''add_upgrade'', ''[]'', 0, 0, 1),
(255, 1, '''', ''后台通知'', ''admin'', ''setting.systemNotice'', ''index'', ''[]'', 0, 1, 1),
(256, 0, ''cloud'', ''服务器端'', ''admin'', ''upgrade'', ''index'', ''[]'', -100, 0, 1),
(257, 258, '''', ''IP白名单'', ''admin'', ''server.server_upgrade'', ''ip_hteb_list'', ''[]'', 0, 0, 1),
(258, 256, '''', ''站点管理'', ''admin'', ''server.server_upgrade'', ''index'', ''[]'', 0, 1, 1),
(259, 256, '''', ''版本管理'', ''admin'', ''server.server_upgrade'', ''versionlist'', ''[]'', 0, 1, 1),
(260, 256, '''', ''升级日志'', ''admin'', ''server.server_upgrade'', ''upgrade_log'', ''[]'', 0, 1, 1),
(261, 147, '''', ''编辑产品'', ''admin'', ''store.storeproduct'', ''edit'', ''[]'', 0, 0, 1),
(262, 147, '''', ''添加产品'', ''admin'', ''store.storeproduct'', ''create'', ''[]'', 0, 0, 1),
(263, 147, '''', ''编辑产品详情'', ''admin'', ''store.storeproduct'', ''edit_content'', ''[]'', 0, 0, 1),
(264, 147, '''', ''开启秒杀'', ''admin'', ''store.storeproduct'', ''seckill'', ''[]'', 0, 0, 1),
(265, 147, '''', ''开启秒杀'', ''admin'', ''store.store_product'', ''bargain'', ''[]'', 0, 0, 1),
(266, 147, '''', ''产品编辑属性'', ''admin'', ''store.storeproduct'', ''attr'', ''[]'', 0, 0, 1),
(267, 360, '''', ''公众号接口配置'', ''admin'', ''setting.systemConfig'', ''index'', ''{"type":"1","tab_id":"2"}'', 100, 1, 1),
(269, 0, ''cubes'', ''小程序'', ''admin'', ''setting.system'', '''', ''[]'', 92, 1, 1),
(270, 269, '''', ''小程序配置'', ''admin'', ''setting.systemConfig'', ''index_alone'', ''{"type":"2","tab_id":"7"}'', 0, 1, 1),
(273, 286, '''', ''秒杀管理'', ''admin'', '''', '''', ''[]'', 0, 1, 1),
(293, 288, '''', ''交易数据'', ''admin'', '''', '''', ''[]'', 100, 1, 1),
(276, 21, '''', ''附件管理'', ''admin'', ''widget.images'', ''index'', ''[]'', 0, 0, 1),
(278, 21, '''', ''清除数据'', ''admin'', ''system.system_cleardata'', ''index'', ''[]'', 0, 1, 1),
(363, 362, '''', ''上传图片'', ''admin'', ''widget.images'', ''upload'', ''[]'', 0, 1, 1),
(364, 362, '''', ''删除图片'', ''admin'', ''widget.images'', ''delete'', ''[]'', 0, 1, 1),
(362, 276, '''', ''附加权限'', '''', '''', '''', ''[]'', 0, 1, 1),
(285, 0, ''building-o'', ''订单'', ''admin'', '''', '''', ''[]'', 109, 1, 1),
(283, 80, '''', ''文章管理'', ''admin'', ''article.article'', ''index'', ''[]'', 0, 1, 1),
(284, 80, '''', ''文章分类'', ''admin'', ''article.article_category'', ''index'', ''[]'', 0, 1, 1),
(287, 0, ''money'', ''财务'', ''admin'', '''', '''', ''[]'', 103, 1, 1),
(288, 0, ''line-chart'', ''数据'', ''admin'', '''', '''', ''[]'', 100, 0, 1),
(289, 0, ''gear'', ''设置'', ''admin'', '''', '''', ''[]'', 90, 1, 1),
(290, 285, '''', ''售后服务'', ''admin'', '''', '''', ''[]'', 0, 1, 1),
(353, 337, '''', ''分销配置'', ''admin'', ''setting.systemConfig'', ''index'', ''{"type":"3","tab_id":"9"}'', 0, 1, 1),
(306, 287, '''', ''财务操作'', ''admin'', '''', '''', ''[]'', 100, 1, 1),
(307, 287, '''', ''财务记录'', ''admin'', '''', '''', ''[]'', 50, 1, 1),
(308, 287, '''', ''佣金记录'', ''admin'', '''', '''', ''[]'', 0, 1, 1),
(372, 269, '''', ''首页幻灯片'', ''admin'', ''setting.system_group_data'', ''index'', ''{"gid":"48"}'', 0, 1, 1),
(312, 307, '''', ''资金监控'', ''admin'', ''finance.finance'', ''bill'', ''[]'', 0, 1, 1),
(313, 308, '''', ''佣金记录'', ''admin'', ''finance.finance'', ''commission_list'', ''[]'', 0, 1, 1),
(329, 285, '''', ''营销订单'', ''admin'', ''user'', ''user'', ''[]'', 0, 0, 1),
(371, 337, '''', ''分销员管理'', ''admin'', ''agent.agentManage'', ''index'', ''[]'', 0, 1, 1),
(354, 11, '''', ''自动回复'', '''', '''', '''', ''[]'', 80, 1, 1),
(334, 329, '''', ''秒杀订单'', ''admin'', ''user'', '''', ''[]'', 0, 0, 1),
(335, 329, '''', ''积分兑换'', ''admin'', ''user'', '''', ''[]'', 0, 0, 1),
(336, 151, '''', ''会员等级'', ''admin'', ''user.user'', ''group'', ''[]'', 0, 0, 1),
(337, 0, ''users'', ''分销'', ''admin'', ''user'', ''user'', ''[]'', 106, 1, 1),
(349, 286, '''', ''积分'', ''admin'', ''userPoint'', ''index'', ''[]'', 0, 1, 1),
(350, 349, '''', ''积分配置'', ''admin'', ''setting.systemConfig'', ''index'', ''{"type":"3","tab_id":"11"}'', 0, 1, 1),
(355, 11, '''', ''页面设置'', '''', '''', '''', ''[]'', 90, 1, 1),
(356, 355, '''', ''个人中心菜单'', ''admin'', ''setting.system_group_data'', ''index'', ''{"gid":"32"}'', 0, 1, 1),
(357, 355, '''', ''商城首页banner'', ''admin'', ''setting.system_group_data'', ''index'', ''{"gid":"34"}'', 0, 1, 1),
(358, 355, '''', ''商城首页分类按钮'', ''admin'', ''setting.system_group_data'', ''index'', ''{"gid":"35"}'', 0, 1, 1),
(359, 355, '''', ''商城首页滚动新闻'', ''admin'', ''setting.system_group_data'', ''index'', ''{"gid":"36"}'', 0, 1, 1),
(360, 11, '''', ''公众号配置'', '''', '''', '''', ''[]'', 100, 1, 1),
(361, 360, '''', ''公众号支付配置'', ''admin'', ''setting.systemConfig'', ''index'', ''{"type":"1","tab_id":"4"}'', 0, 1, 1),
(365, 362, '''', ''附件管理'', ''admin'', ''widget.images'', ''index'', ''[]'', 0, 1, 1),
(369, 143, '''', ''添加产品分类'', ''admin'', ''store.storeCategory'', ''create'', ''[]'', 0, 0, 1),
(370, 143, '''', ''编辑产品分类'', ''admin'', ''store.storeCategory'', ''edit'', ''[]'', 0, 0, 1),
(373, 269, '''', ''首页导航按钮'', ''admin'', ''setting.system_group_data'', ''index'', ''{"gid":"47"}'', 0, 1, 1),
(374, 295, '''', ''分销会员业务'', ''admin'', ''record.record'', ''user_distribution_chart'', ''[]'', 0, 1, 1),
(375, 269, '''', ''小程序支付配置'', ''admin'', ''setting.systemConfig'', ''index_alone'', ''{"type":"2","tab_id":"14"}'', 0, 1, 1),
(376, 269, '''', ''小程序模板消息'', ''admin'', ''routine.routineTemplate'', ''index'', ''[]'', 0, 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_notice`
--

CREATE TABLE IF NOT EXISTS `eb_system_notice` (
  `id` int(10) unsigned NOT NULL COMMENT ''通知模板id'',
  `title` varchar(64) NOT NULL COMMENT ''通知标题'',
  `type` varchar(64) NOT NULL COMMENT ''通知类型'',
  `icon` varchar(16) NOT NULL COMMENT ''图标'',
  `url` varchar(64) NOT NULL COMMENT ''链接'',
  `table_title` varchar(256) NOT NULL COMMENT ''通知数据'',
  `template` varchar(64) NOT NULL COMMENT ''通知模板'',
  `push_admin` varchar(128) NOT NULL COMMENT ''通知管理员id'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''状态''
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT=''通知模板表'';

--
-- 转存表中的数据 `eb_system_notice`
--

INSERT INTO `eb_system_notice` (`id`, `title`, `type`, `icon`, `url`, `table_title`, `template`, `push_admin`, `status`) VALUES
(5, ''用户关注通知'', ''user_sub'', ''user-plus'', '''', ''[{"title":"openid","key":"openid"},{"title":"\\u5fae\\u4fe1\\u6635\\u79f0","key":"nickname"},{"title":"\\u5173\\u6ce8\\u4e8b\\u4ef6","key":"add_time"}]'', ''有%u个新用户关注了公众号'', ''1'', 1),
(7, ''新订单提醒'', ''订单提醒'', ''building-o'', '''', ''[{"title":"\\u8ba2\\u5355\\u53f7","key":"key1"}]'', ''新订单提醒'', ''1'', 1),
(9, ''测试通知中 '', ''测试'', ''buysellads'', '''', ''[{"title":"\\u8ba2\\u5355\\u53f7","key":"key1"}]'', ''测试'', ''20'', 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_notice_admin`
--

CREATE TABLE IF NOT EXISTS `eb_system_notice_admin` (
  `id` int(10) unsigned NOT NULL COMMENT ''通知记录ID'',
  `notice_type` varchar(64) NOT NULL COMMENT ''通知类型'',
  `admin_id` smallint(5) unsigned NOT NULL COMMENT ''通知的管理员'',
  `link_id` int(10) unsigned NOT NULL COMMENT ''关联ID'',
  `table_data` text NOT NULL COMMENT ''通知的数据'',
  `is_click` tinyint(3) unsigned NOT NULL DEFAULT ''0'' COMMENT ''点击次数'',
  `is_visit` tinyint(3) unsigned NOT NULL DEFAULT ''0'' COMMENT ''访问次数'',
  `visit_time` int(11) NOT NULL COMMENT ''访问时间'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''通知时间''
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT=''通知记录表'';

--
-- 转存表中的数据 `eb_system_notice_admin`
--

INSERT INTO `eb_system_notice_admin` (`id`, `notice_type`, `admin_id`, `link_id`, `table_data`, `is_click`, `is_visit`, `visit_time`, `add_time`) VALUES
(7, ''user_sub'', 1, 2, ''{"openid":2,"nickname":123,"change_time":1512444900}'', 0, 1, 1512525411, 1512444900),
(8, ''user_sub'', 2, 2, ''{"openid":2,"nickname":123,"change_time":1512444900}'', 0, 1, 1512459748, 1512444901),
(9, ''user_sub'', 1, 2, ''{"openid":2,"nickname":123,"change_time":1512454840}'', 0, 1, 1512525411, 1512454840),
(10, ''user_sub'', 2, 2, ''{"openid":2,"nickname":123,"change_time":1512454840}'', 0, 1, 1512459748, 1512454840);

-- --------------------------------------------------------

--
-- 表的结构 `eb_system_role`
--

CREATE TABLE IF NOT EXISTS `eb_system_role` (
  `id` int(10) unsigned NOT NULL COMMENT ''身份管理id'',
  `role_name` varchar(32) NOT NULL COMMENT ''身份管理名称'',
  `rules` text NOT NULL COMMENT ''身份管理权限(menus_id)'',
  `level` tinyint(3) unsigned NOT NULL DEFAULT ''0'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''状态''
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT=''身份管理表'';

--
-- 转存表中的数据 `eb_system_role`
--

INSERT INTO `eb_system_role` (`id`, `role_name`, `rules`, `level`, `status`) VALUES
(1, ''超级管理员'', ''85,86,11,174,17,127,128,126,80,32,71,33,36,35,34,113,114,19,73,42,43,44,45,115,116,18,72,38,41,40,39,79,31,37,95,96,94,30,89,124,123,90,91,92,93,12,98,99,97,23,240,238,148,150,149,229,230,234,233,232,231,235,175,210,223,222,215,214,213,212,211,209,170,225,228,227,226,224,160,162,161,26,190,191,192,193,194,206,195,205,204,203,202,201,200,199,198,197,207,208,196,25,142,143,24,147,146,21,237,130,131,22,136,135,139,138,137,140,141,1,173,5,9,70,52,58,57,56,55,54,53,112,111,110,109,7,69,46,51,50,49,48,47,108,107,106,6,118,117,145,144,119,2,74,75,76,101,100,77,78,153,4,59,62,61,60,103,102,68,8,63,64,65,66,105,104,67,151,177,176,239,179,217,219,218,216,82,129,132,133,134,236,84'', 0, 1),
(15, ''子管理员'', ''11,174,17,127,128,126,80,32,33,36,35,34,113,114,71,19,42,45,44,43,116,115,73,79,31,37,95,96,94,30,89,124,123,90,91,92,93,12,98,99,97,23,241,240,238,148,149,230,234,233,232,231,229,150,235,175,210,212,211,213,214,223,222,215,209,170,225,228,227,226,224,26,191,198,192,202,193,203,201,200,194,199,196,197,195,208,207,206,205,204,190,25,142,143,24,146,147,151,177,176,239,179,217,219,218,216,82,236,84,133,134,129,132,18,72,38,39,40,41,153,8,67,63,66,65,64,105,104,4,59,60,61,62,103,102,68,247,250,243,246,245,244,1,2,78,74,101,9,52,58,57,56,55,54,53,111,110,109,112,70,5,7,69,46,51,50,49,48,47,108,107,106,6,118,117,144,145,119,21,173,237,130,131,0'', 2, 1),
(10, ''客服'', ''23,241,240,238,148,150,149,229,230,231,234,233,232,235,26,191,197,196,195,194,193,192,198,207,206,205,204,203,202,201,200,208,199,190,175,209,210,223,222,215,214,213,212,211,170,225,228,227,226,224,25,142,143,24,146,147,151,177,176,239,179,217,219,218,216,82,133,134,129,132,236,84'', 1, 1),
(14, ''演示账号'', ''146,142,26,191,195,194,193,192,196,197,198,208,207,206,205,204,203,202,201,200,199,190,290,170,224,225,228,227,226,177,176,82,133,134,236,84,129,132,337,371,353,149,229,230,232,233,234,231,235,150,273,241,238,306,239,179,216,217,218,219,245,244,246,269,376,375,373,270,372,17,126,127,128,174,354,12,98,99,97,30,89,92,91,90,123,124,93,31,95,96,94,37,175,210,215,214,222,223,213,212,211,209,19,73,42,45,44,43,116,115,8,67,63,104,66,65,64,105,4,68,59,103,102,62,61,60,78,101,250,70,56,112,111,110,109,7,69,46,47,48,49,51,50,108,107,106,6,118,117,145,144,119,80,284,71,33,36,35,34,114,113,283,72,38,41,40,39,173,237,130,131,365,24,25,285,151,0,148,286,272,287,307,293,294,295,360,11,153,2,74,247,9,52,1,21,362'', 1, 1),
(16, ''三级身份'', ''11,174,17,127,128,126,80,32,33,36,35,34,113,114,71,19,42,45,44,43,116,115,73,79,31,37,95,96,94,30,89,124,123,90,91,92,93,12,98,99,97,23,148,149,230,234,233,232,231,229,150,235,175,210,212,211,213,214,223,222,215,209,170,225,228,227,226,224,26,191,198,192,202,193,203,201,200,194,199,196,197,195,208,207,206,205,204,190,25,142,143,24,146,147,241,240,238,151,179,217,219,218,216,177,176,239,82,133,134,129,132,236,84,18,38,39,40,41,72,153,8,67,63,66,65,64,105,104,4,59,60,61,62,103,102,68,247,250,243,246,245,244,9,52,58,57,56,55,54,53,111,110,109,112,70,5,7,69,46,51,50,49,48,47,108,107,106,6,118,117,144,145,119,21,130,131,173,237,0,1'', 3, 1),
(17, ''boss'', ''11,174,17,126,127,128,19,42,43,44,45,115,116,73,79,31,37,94,95,96,30,93,89,123,124,90,91,92,12,97,98,99,23,241,240,238,148,149,230,234,233,232,231,229,150,235,170,225,228,227,226,224,175,210,212,211,213,214,223,222,215,209,25,143,142,24,147,146,26,191,197,196,195,194,202,198,192,200,203,201,199,193,208,207,206,205,204,190,151,176,177,239,179,217,219,218,216,80,32,71,33,34,35,36,113,114,18,72,38,39,40,41,0'', 2, 1),
(18, ''管理员'', ''23,24,323,328,324,325,326,327,147,261,262,266,265,264,263,146,25,143,370,369,142,285,26,191,194,193,192,195,196,208,207,206,205,204,203,202,201,200,199,198,197,190,329,335,334,333,290,170,225,226,227,228,224,151,177,176,82,133,134,236,84,129,132,336,337,371,339,353,286,148,149,229,230,234,231,232,233,235,150,352,349,351,350,273,241,272,238,271,254,366,368,367,287,306,239,307,179,216,217,218,219,312,308,313,288,293,251,245,340,341,296,318,317,316,315,314,294,244,302,301,300,295,246,305,304,303,297,321,320,319,355,359,358,357,356,354,12,98,99,97,30,89,92,91,90,123,124,93,31,95,96,94,37,175,210,212,213,214,215,222,223,211,209,19,73,42,45,44,43,116,115,283,72,38,41,40,39,284,71,33,36,35,34,114,113,8,67,63,104,66,65,64,105,68,103,102,61,2,78,74,77,76,75,101,100,247,248,249,250,1,9,70,52,58,57,56,55,54,53,112,111,110,109,7,69,46,47,48,49,51,50,108,107,106,6,118,117,145,144,119,255,269,270,21,130,131,173,252,237,278,276,362,365,364,363,258,257,260,0,11,80,153,4,59,289,256'', 1, 1),
(19, ''公司内部'', ''23,24,323,328,324,325,326,327,147,261,262,263,266,265,264,146,25,143,370,369,142,285,26,191,195,194,193,192,196,197,208,207,206,205,204,203,202,201,200,199,198,190,329,335,334,333,290,170,224,225,228,227,226,177,176,82,133,134,236,84,129,132,337,371,353,286,148,149,229,230,231,232,233,234,235,150,352,349,351,350,273,241,272,238,271,254,366,368,367,306,239,179,216,217,218,219,340,341,296,318,317,316,315,314,302,301,300,246,305,304,303,297,321,320,319,270,11,360,267,17,126,127,128,174,361,355,359,358,357,356,354,12,98,99,97,30,89,92,91,90,123,124,93,31,95,96,94,37,175,210,213,214,215,222,223,212,211,209,19,73,42,45,44,43,116,115,289,153,8,67,63,104,66,65,64,105,4,68,59,103,102,62,61,60,2,78,74,77,76,75,101,100,247,250,248,249,1,9,70,52,58,57,56,55,54,53,112,111,110,109,7,69,46,47,48,49,51,50,108,107,106,6,118,117,145,144,119,255,283,72,38,41,40,39,284,71,33,36,35,34,114,113,130,131,173,252,237,276,362,365,364,363,0,151,287,307,293,288,294,295,269,80,21'', 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_user`
--

CREATE TABLE IF NOT EXISTS `eb_user` (
  `uid` int(10) unsigned NOT NULL COMMENT ''用户id'',
  `account` varchar(32) NOT NULL COMMENT ''用户账号'',
  `pwd` varchar(32) NOT NULL COMMENT ''用户密码'',
  `nickname` varchar(16) NOT NULL COMMENT ''用户昵称'',
  `avatar` varchar(256) NOT NULL COMMENT ''用户头像'',
  `phone` char(15) NOT NULL COMMENT ''手机号码'',
  `add_time` int(11) unsigned NOT NULL COMMENT ''添加时间'',
  `add_ip` varchar(16) NOT NULL COMMENT ''添加ip'',
  `last_time` int(11) unsigned NOT NULL COMMENT ''最后一次登录时间'',
  `last_ip` varchar(16) NOT NULL COMMENT ''最后一次登录ip'',
  `now_money` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''用户余额'',
  `integral` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''用户剩余积分'',
  `status` tinyint(1) NOT NULL DEFAULT ''1'' COMMENT ''1为正常，0为禁止'',
  `level` tinyint(2) unsigned NOT NULL DEFAULT ''0'' COMMENT ''等级'',
  `spread_uid` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''推广元id'',
  `user_type` varchar(32) NOT NULL COMMENT ''用户类型'',
  `is_promoter` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否为推广员'',
  `pay_count` int(11) unsigned DEFAULT ''0'' COMMENT ''用户购买次数''
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT=''用户表'';

--
-- 转存表中的数据 `eb_user`
--

INSERT INTO `eb_user` (`uid`, `account`, `pwd`, `nickname`, `avatar`, `phone`, `add_time`, `add_ip`, `last_time`, `last_ip`, `now_money`, `integral`, `status`, `level`, `spread_uid`, `user_type`, `is_promoter`, `pay_count`) VALUES
(1, ''liaofei'', ''e10adc3949ba59abbe56e057f20f883e'', ''等风来，随风去'', ''http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLBaQPPnbg52bgibia1CZDruib1RwibHbBbnfxH1MUwbyz3G0Xub1LNX0ib5RFd7nZvo88gzHwib0OPibyfZQ/132'', '''', 1528859304, ''140.207.54.80'', 1535070458, ''127.0.0.1'', ''0.00'', ''0.00'', 1, 0, 0, ''wechat'', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_address`
--

CREATE TABLE IF NOT EXISTS `eb_user_address` (
  `id` mediumint(8) unsigned NOT NULL COMMENT ''用户地址id'',
  `uid` int(10) unsigned NOT NULL COMMENT ''用户id'',
  `real_name` varchar(32) NOT NULL COMMENT ''收货人姓名'',
  `phone` varchar(16) NOT NULL COMMENT ''收货人电话'',
  `province` varchar(64) NOT NULL COMMENT ''收货人所在省'',
  `city` varchar(64) NOT NULL COMMENT ''收货人所在市'',
  `district` varchar(64) NOT NULL COMMENT ''收货人所在区'',
  `detail` varchar(256) NOT NULL COMMENT ''收货人详细地址'',
  `post_code` int(10) unsigned NOT NULL COMMENT ''邮编'',
  `longitude` varchar(16) NOT NULL DEFAULT ''0'' COMMENT ''经度'',
  `latitude` varchar(16) NOT NULL DEFAULT ''0'' COMMENT ''纬度'',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否默认'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否删除'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''添加时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户地址表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_bill`
--

CREATE TABLE IF NOT EXISTS `eb_user_bill` (
  `id` int(10) unsigned NOT NULL COMMENT ''用户账单id'',
  `uid` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''用户uid'',
  `link_id` varchar(32) NOT NULL DEFAULT ''0'' COMMENT ''关联id'',
  `pm` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''0 = 支出 1 = 获得'',
  `title` varchar(64) NOT NULL COMMENT ''账单标题'',
  `category` varchar(64) NOT NULL COMMENT ''明细种类'',
  `type` varchar(64) NOT NULL DEFAULT '''' COMMENT ''明细类型'',
  `number` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''明细数字'',
  `balance` decimal(8,2) unsigned NOT NULL DEFAULT ''0.00'' COMMENT ''剩余'',
  `mark` varchar(512) NOT NULL COMMENT ''备注'',
  `add_time` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''添加时间'',
  `status` tinyint(1) NOT NULL DEFAULT ''1'' COMMENT ''0 = 带确定 1 = 有效 -1 = 无效''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户账单表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_enter`
--

CREATE TABLE IF NOT EXISTS `eb_user_enter` (
  `id` int(10) unsigned NOT NULL COMMENT ''商户申请ID'',
  `uid` int(10) unsigned NOT NULL COMMENT ''用户ID'',
  `province` varchar(32) NOT NULL COMMENT ''商户所在省'',
  `city` varchar(32) NOT NULL COMMENT ''商户所在市'',
  `district` varchar(32) NOT NULL COMMENT ''商户所在区'',
  `address` varchar(256) NOT NULL COMMENT ''商户详细地址'',
  `merchant_name` varchar(256) NOT NULL COMMENT ''商户名称'',
  `link_user` varchar(32) NOT NULL,
  `link_tel` varchar(16) NOT NULL COMMENT ''商户电话'',
  `charter` varchar(512) NOT NULL COMMENT ''商户证书'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''添加时间'',
  `apply_time` int(10) unsigned NOT NULL COMMENT ''审核时间'',
  `success_time` int(11) NOT NULL COMMENT ''通过时间'',
  `fail_message` varchar(256) NOT NULL COMMENT ''未通过原因'',
  `fail_time` int(10) unsigned NOT NULL COMMENT ''未通过时间'',
  `status` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''-1 审核未通过 0未审核 1审核通过'',
  `is_lock` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''0 = 开启 1= 关闭'',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否删除''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''商户申请表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_extract`
--

CREATE TABLE IF NOT EXISTS `eb_user_extract` (
  `id` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  `real_name` varchar(64) DEFAULT NULL COMMENT ''名称'',
  `extract_type` varchar(32) DEFAULT ''bank'' COMMENT ''bank = 银行卡 alipay = 支付宝wx=微信'',
  `bank_code` varchar(32) DEFAULT ''0'' COMMENT ''银行卡'',
  `bank_address` varchar(256) DEFAULT '''' COMMENT ''开户地址'',
  `alipay_code` varchar(64) DEFAULT '''' COMMENT ''支付宝账号'',
  `extract_price` decimal(8,2) unsigned DEFAULT ''0.00'' COMMENT ''提现金额'',
  `mark` varchar(512) DEFAULT NULL,
  `balance` decimal(8,2) unsigned DEFAULT ''0.00'',
  `fail_msg` varchar(128) DEFAULT NULL COMMENT ''无效原因'',
  `fail_time` int(10) unsigned DEFAULT NULL,
  `add_time` int(10) unsigned DEFAULT NULL COMMENT ''添加时间'',
  `status` tinyint(2) DEFAULT ''0'' COMMENT ''-1 未通过 0 审核中 1 已提现'',
  `wechat` varchar(15) DEFAULT NULL COMMENT ''微信号''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户提现表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_group`
--

CREATE TABLE IF NOT EXISTS `eb_user_group` (
  `id` smallint(5) unsigned NOT NULL,
  `group_name` varchar(64) DEFAULT NULL COMMENT ''用户分组名称''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户分组表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_notice`
--

CREATE TABLE IF NOT EXISTS `eb_user_notice` (
  `id` int(11) NOT NULL,
  `uid` text NOT NULL COMMENT ''接收消息的用户id（类型：json数据）'',
  `type` tinyint(1) NOT NULL DEFAULT ''1'' COMMENT ''消息通知类型（1：系统消息；2：用户通知）'',
  `user` varchar(20) NOT NULL DEFAULT '''' COMMENT ''发送人'',
  `title` varchar(20) NOT NULL COMMENT ''通知消息的标题信息'',
  `content` varchar(500) NOT NULL COMMENT ''通知消息的内容'',
  `add_time` int(11) NOT NULL COMMENT ''通知消息发送的时间'',
  `is_send` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''是否发送（0：未发送；1：已发送）'',
  `send_time` int(11) NOT NULL COMMENT ''发送时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户通知表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_user_notice_see`
--

CREATE TABLE IF NOT EXISTS `eb_user_notice_see` (
  `id` int(11) NOT NULL,
  `nid` int(11) NOT NULL COMMENT ''查看的通知id'',
  `uid` int(11) NOT NULL COMMENT ''查看通知的用户id'',
  `add_time` int(11) NOT NULL COMMENT ''查看通知的时间''
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT=''用户通知发送记录表'';

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
  `id` int(10) unsigned NOT NULL,
  `uid` int(10) DEFAULT NULL COMMENT ''充值用户UID'',
  `order_id` varchar(32) DEFAULT NULL COMMENT ''订单号'',
  `price` decimal(8,2) DEFAULT NULL COMMENT ''充值金额'',
  `recharge_type` varchar(32) DEFAULT NULL COMMENT ''充值类型'',
  `paid` tinyint(1) DEFAULT NULL COMMENT ''是否充值'',
  `pay_time` int(10) DEFAULT NULL COMMENT ''充值支付时间'',
  `add_time` int(12) DEFAULT NULL COMMENT ''充值时间'',
  `refund_price` decimal(10,2) unsigned NOT NULL COMMENT ''退款金额''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户充值表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_media`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_media` (
  `id` int(10) unsigned NOT NULL COMMENT ''微信视频音频id'',
  `type` varchar(16) NOT NULL COMMENT ''回复类型'',
  `path` varchar(128) NOT NULL COMMENT ''文件路径'',
  `media_id` varchar(64) NOT NULL COMMENT ''微信服务器返回的id'',
  `url` varchar(256) NOT NULL COMMENT ''地址'',
  `temporary` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否永久或者临时 0永久1临时'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''添加时间''
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT=''微信回复表'';

--
-- 转存表中的数据 `eb_wechat_media`
--

INSERT INTO `eb_wechat_media` (`id`, `type`, `path`, `media_id`, `url`, `temporary`, `add_time`) VALUES
(12, ''image'', ''/public/uploads/wechat/image/5b042ca618139.jpg'', ''6sFx6PzPF2v_Lv4FGOMzzwcwmM2wuoA63ZMSxiN-7DY'', ''http://mmbiz.qpic.cn/mmbiz_jpg/xVkDhuiaGm78WOdUXuPE1oYLnU4J0LCEiaSuLhwwSrfdyINspibXsllaj8rOMSs5estAv0qhGuGniaqhb6HftecPuw/0?wx_fmt=jpeg'', 0, 1527000231);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_message`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_message` (
  `id` int(10) unsigned NOT NULL COMMENT ''用户行为记录id'',
  `openid` varchar(32) NOT NULL COMMENT ''用户openid'',
  `type` varchar(32) NOT NULL COMMENT ''操作类型'',
  `result` varchar(512) NOT NULL COMMENT ''操作详细记录'',
  `add_time` int(10) unsigned NOT NULL COMMENT ''操作时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''用户行为记录表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_news_category`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_news_category` (
  `id` int(10) unsigned NOT NULL COMMENT ''图文消息管理ID'',
  `cate_name` varchar(255) NOT NULL COMMENT ''图文名称'',
  `sort` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''排序'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''状态'',
  `new_id` varchar(255) NOT NULL COMMENT ''文章id'',
  `add_time` varchar(255) NOT NULL COMMENT ''添加时间''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=''图文消息管理表'';

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_qrcode`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_qrcode` (
  `id` int(10) unsigned NOT NULL COMMENT ''微信二维码ID'',
  `third_type` varchar(32) NOT NULL COMMENT ''二维码类型'',
  `third_id` int(11) unsigned NOT NULL COMMENT ''用户id'',
  `ticket` varchar(255) NOT NULL COMMENT ''二维码参数'',
  `expire_seconds` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''二维码有效时间'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''状态'',
  `add_time` varchar(255) NOT NULL COMMENT ''添加时间'',
  `url` varchar(255) NOT NULL COMMENT ''微信访问url'',
  `qrcode_url` varchar(255) NOT NULL COMMENT ''微信二维码url'',
  `scan` int(10) unsigned NOT NULL DEFAULT ''0'' COMMENT ''被扫的次数''
) ENGINE=MyISAM AUTO_INCREMENT=126 DEFAULT CHARSET=utf8 COMMENT=''微信二维码管理表'';

--
-- 转存表中的数据 `eb_wechat_qrcode`
--

INSERT INTO `eb_wechat_qrcode` (`id`, `third_type`, `third_id`, `ticket`, `expire_seconds`, `status`, `add_time`, `url`, `qrcode_url`, `scan`) VALUES
(1, ''spread'', 345, ''gQF78TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTjFNd2dXUDBiRy0xMDAwMHcwMzQAAgQUk1ZbAwQAAAAA'', 0, 1, ''1532406871'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF78TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTjFNd2dXUDBiRy0xMDAwMHcwMzQAAgQUk1ZbAwQAAAAA'', ''http://weixin.qq.com/q/02N1MwgWP0bG-10000w034'', 0),
(2, ''spread'', 344, ''gQEa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybVBsU2hIUDBiRy0xMDAwMGcwM3oAAgQUk1ZbAwQAAAAA'', 0, 1, ''1532406871'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybVBsU2hIUDBiRy0xMDAwMGcwM3oAAgQUk1ZbAwQAAAAA'', ''http://weixin.qq.com/q/02mPlShHP0bG-10000g03z'', 0),
(3, ''spread'', 343, ''gQGD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXYzd2dUUDBiRy0xMDAwMHcwMzgAAgQcdFZbAwQAAAAA'', 0, 1, ''1532406872'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXYzd2dUUDBiRy0xMDAwMHcwMzgAAgQcdFZbAwQAAAAA'', ''http://weixin.qq.com/q/021v3wgTP0bG-10000w038'', 0),
(4, ''spread'', 342, ''gQH97zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaFJ4ZmhnUDBiRy0xMDAwMGcwM3IAAgQddFZbAwQAAAAA'', 0, 1, ''1532406872'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH97zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaFJ4ZmhnUDBiRy0xMDAwMGcwM3IAAgQddFZbAwQAAAAA'', ''http://weixin.qq.com/q/02hRxfhgP0bG-10000g03r'', 0),
(5, ''spread'', 341, ''gQH28DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAya2RrTmdPUDBiRy0xMDAwMHcwM1QAAgRPgVVbAwQAAAAA'', 0, 1, ''1532406872'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH28DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAya2RrTmdPUDBiRy0xMDAwMHcwM1QAAgRPgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02kdkNgOP0bG-10000w03T'', 0),
(6, ''spread'', 340, ''gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR1oxamd0UDBiRy0xMDAwME0wM0sAAgRQgVVbAwQAAAAA'', 0, 1, ''1532406872'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR1oxamd0UDBiRy0xMDAwME0wM0sAAgRQgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02GZ1jgtP0bG-10000M03K'', 0),
(7, ''spread'', 339, ''gQEq8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaDZkNGhrUDBiRy0xMDAwMHcwM1QAAgRQgVVbAwQAAAAA'', 0, 1, ''1532406872'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEq8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaDZkNGhrUDBiRy0xMDAwMHcwM1QAAgRQgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02h6d4hkP0bG-10000w03T'', 0),
(8, ''spread'', 338, ''gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR3pGVWhaUDBiRy0xMDAwME0wM04AAgRQgVVbAwQAAAAA'', 0, 1, ''1532406872'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyR3pGVWhaUDBiRy0xMDAwME0wM04AAgRQgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02GzFUhZP0bG-10000M03N'', 0),
(9, ''spread'', 337, ''gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ2lVSmhQUDBiRy0xMDAwMDAwM1QAAgRQgVVbAwQAAAAA'', 0, 1, ''1532406873'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ2lVSmhQUDBiRy0xMDAwMDAwM1QAAgRQgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02CiUJhPP0bG-10000003T'', 0),
(10, ''spread'', 336, ''gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS3l0dGhuUDBiRy0xMDAwMGcwM3EAAgRQgVVbAwQAAAAA'', 0, 1, ''1532406873'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS3l0dGhuUDBiRy0xMDAwMGcwM3EAAgRQgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02KytthnP0bG-10000g03q'', 0),
(11, ''spread'', 335, ''gQE38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyOUlKcGd6UDBiRy0xMDAwMGcwM0IAAgRRgVVbAwQAAAAA'', 0, 1, ''1532406873'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyOUlKcGd6UDBiRy0xMDAwMGcwM0IAAgRRgVVbAwQAAAAA'', ''http://weixin.qq.com/q/029IJpgzP0bG-10000g03B'', 0),
(12, ''spread'', 334, ''gQEu8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybDJKUGd1UDBiRy0xMDAwMGcwM1QAAgRRgVVbAwQAAAAA'', 0, 1, ''1532406873'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEu8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybDJKUGd1UDBiRy0xMDAwMGcwM1QAAgRRgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02l2JPguP0bG-10000g03T'', 0),
(13, ''spread'', 333, ''gQEX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG1IZWc4UDBiRy0xMDAwMGcwM1YAAgRRgVVbAwQAAAAA'', 0, 1, ''1532406873'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG1IZWc4UDBiRy0xMDAwMGcwM1YAAgRRgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02pmHeg8P0bG-10000g03V'', 0),
(14, ''spread'', 332, ''gQEy8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaWhJSWdtUDBiRy0xMDAwMDAwM2QAAgRRgVVbAwQAAAAA'', 0, 1, ''1532406873'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEy8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaWhJSWdtUDBiRy0xMDAwMDAwM2QAAgRRgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02ihIIgmP0bG-10000003d'', 0),
(15, ''spread'', 331, ''gQHh8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyb3lfeWhWUDBiRy0xMDAwMDAwM24AAgRRgVVbAwQAAAAA'', 0, 1, ''1532406873'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHh8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyb3lfeWhWUDBiRy0xMDAwMDAwM24AAgRRgVVbAwQAAAAA'', ''http://weixin.qq.com/q/02oy_yhVP0bG-10000003n'', 0),
(16, ''spread'', 330, ''gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZGxwWmdWUDBiRy0xMDAwMHcwM2IAAgRLtFFbAwQAAAAA'', 0, 1, ''1532406874'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGk8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZGxwWmdWUDBiRy0xMDAwMHcwM2IAAgRLtFFbAwQAAAAA'', ''http://weixin.qq.com/q/02dlpZgVP0bG-10000w03b'', 0),
(17, ''spread'', 329, ''gQGc8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVTdmdGd4UDBiRy0xMDAwMHcwM2oAAgSsZVFbAwQAAAAA'', 0, 1, ''1532406874'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGc8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVTdmdGd4UDBiRy0xMDAwMHcwM2oAAgSsZVFbAwQAAAAA'', ''http://weixin.qq.com/q/02U7ftgxP0bG-10000w03j'', 0),
(18, ''spread'', 328, ''gQFz8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNmtKMmhaUDBiRy0xMDAwMDAwMzMAAgSOtC9ZAwQAAAAA'', 0, 1, ''1532406874'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFz8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNmtKMmhaUDBiRy0xMDAwMDAwMzMAAgSOtC9ZAwQAAAAA'', ''http://weixin.qq.com/q/026kJ2hZP0bG-100000033'', 0),
(19, ''spread'', 327, ''gQFM8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWEdTX2czUDBiRy0xMDAwME0wM0EAAgTHay9ZAwQAAAAA'', 0, 1, ''1532406874'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFM8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWEdTX2czUDBiRy0xMDAwME0wM0EAAgTHay9ZAwQAAAAA'', ''http://weixin.qq.com/q/02XGS_g3P0bG-10000M03A'', 0),
(20, ''spread'', 326, ''gQGU8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyci03d2hEUDBiRy0xMDAwMHcwM2kAAgQfay9ZAwQAAAAA'', 0, 1, ''1532406874'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGU8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyci03d2hEUDBiRy0xMDAwMHcwM2kAAgQfay9ZAwQAAAAA'', ''http://weixin.qq.com/q/02r-7whDP0bG-10000w03i'', 0),
(21, ''spread'', 3, ''gQHM8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybkRjQmhrUDBiRy0xMDAwMDAwM0wAAgRm9lZbAwQAAAAA'', 0, 1, ''1532425830'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHM8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybkRjQmhrUDBiRy0xMDAwMDAwM0wAAgRm9lZbAwQAAAAA'', ''http://weixin.qq.com/q/02nDcBhkP0bG-10000003L'', 0),
(22, ''spread'', 2, ''gQFq8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycXlweGgyUDBiRy0xMDAwME0wM08AAgRm9lZbAwQAAAAA'', 0, 1, ''1532425830'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFq8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycXlweGgyUDBiRy0xMDAwME0wM08AAgRm9lZbAwQAAAAA'', ''http://weixin.qq.com/q/02qypxh2P0bG-10000M03O'', 0),
(23, ''spread'', 1, ''gQFZ8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFJHMWg1UDBiRy0xeUs5cmhyMWgAAgSufHNbAwQAjScA'', 1536887214, 1, ''1532425830'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFZ8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFJHMWg1UDBiRy0xeUs5cmhyMWgAAgSufHNbAwQAjScA'', ''http://weixin.qq.com/q/02TRG1h5P0bG-1yK9rhr1h'', 1),
(24, ''spread'', 4, ''gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydWdhTmdrUDBiRy0xMDAwMGcwM08AAgQJA1dbAwQAAAAA'', 0, 1, ''1532429065'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydWdhTmdrUDBiRy0xMDAwMGcwM08AAgQJA1dbAwQAAAAA'', ''http://weixin.qq.com/q/02ugaNgkP0bG-10000g03O'', 0),
(25, ''spread'', 7, ''gQHb8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUU1YR2dsUDBiRy0xMDAwMGcwM0cAAgRwxFdbAwQAAAAA'', 0, 1, ''1532478576'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHb8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUU1YR2dsUDBiRy0xMDAwMGcwM0cAAgRwxFdbAwQAAAAA'', ''http://weixin.qq.com/q/02QMXGglP0bG-10000g03G'', 0),
(26, ''spread'', 6, ''gQEe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNnZKQmh0UDBiRy0xMDAwMDAwMzcAAgRwxFdbAwQAAAAA'', 0, 1, ''1532478576'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNnZKQmh0UDBiRy0xMDAwMDAwMzcAAgRwxFdbAwQAAAAA'', ''http://weixin.qq.com/q/026vJBhtP0bG-100000037'', 0),
(27, ''spread'', 5, ''gQEA8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXcxWWg0UDBiRy0xMDAwMDAwM2oAAgRxxFdbAwQAAAAA'', 0, 1, ''1532478577'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEA8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXcxWWg0UDBiRy0xMDAwMDAwM2oAAgRxxFdbAwQAAAAA'', ''http://weixin.qq.com/q/02aw1Yh4P0bG-10000003j'', 0),
(28, ''spread'', 8, ''gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXhxRWhpUDBiRy0xMDAwME0wM28AAgSN31dbAwQAAAAA'', 0, 1, ''1532485517'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFF8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYXhxRWhpUDBiRy0xMDAwME0wM28AAgSN31dbAwQAAAAA'', ''http://weixin.qq.com/q/02axqEhiP0bG-10000M03o'', 0),
(29, ''spread'', 11, ''gQGh8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX0M3R2c3UDBiRy0xMDAwMGcwM0UAAgSdJFhbAwQAAAAA'', 0, 1, ''1532503198'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGh8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX0M3R2c3UDBiRy0xMDAwMGcwM0UAAgSdJFhbAwQAAAAA'', ''http://weixin.qq.com/q/02_C7Gg7P0bG-10000g03E'', 0),
(30, ''spread'', 10, ''gQEi8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN3E1aWd1UDBiRy0xMDAwME0wM3kAAgSeJFhbAwQAAAAA'', 0, 1, ''1532503198'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEi8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN3E1aWd1UDBiRy0xMDAwME0wM3kAAgSeJFhbAwQAAAAA'', ''http://weixin.qq.com/q/027q5iguP0bG-10000M03y'', 0),
(31, ''spread'', 9, ''gQGH8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRW5zbGhfUDBiRy0xeW9Yck5yMVcAAgSYrnNbAwQAjScA'', 1536899992, 1, ''1532503198'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGH8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRW5zbGhfUDBiRy0xeW9Yck5yMVcAAgSYrnNbAwQAjScA'', ''http://weixin.qq.com/q/02Enslh_P0bG-1yoXrNr1W'', 0),
(32, ''spread'', 20, ''gQEY8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM1BRbWgzUDBiRy0xeG1vcXhyMWwAAgRWi3JbAwQAjScA'', 1536825430, 1, ''1532568916'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEY8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM1BRbWgzUDBiRy0xeG1vcXhyMWwAAgRWi3JbAwQAjScA'', ''http://weixin.qq.com/q/023PQmh3P0bG-1xmoqxr1l'', 0),
(33, ''spread'', 19, ''gQGC8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVGExcWh6UDBiRy0xMDAwMDAwM2cAAgRUJVlbAwQAAAAA'', 0, 1, ''1532568916'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGC8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVGExcWh6UDBiRy0xMDAwMDAwM2cAAgRUJVlbAwQAAAAA'', ''http://weixin.qq.com/q/02Ta1qhzP0bG-10000003g'', 0),
(34, ''spread'', 17, ''gQGl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFh0QmhGUDBiRy0xMDAwME0wM1AAAgRUJVlbAwQAAAAA'', 0, 1, ''1532568916'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVFh0QmhGUDBiRy0xMDAwME0wM1AAAgRUJVlbAwQAAAAA'', ''http://weixin.qq.com/q/02TXtBhFP0bG-10000M03P'', 0),
(35, ''spread'', 15, ''gQHE8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQWJ2YWdyUDBiRy0xeVlLNmhyMWMAAgS8oV5bAwQAjScA'', 1535520444, 1, ''1532568917'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHE8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQWJ2YWdyUDBiRy0xeVlLNmhyMWMAAgS8oV5bAwQAjScA'', ''http://weixin.qq.com/q/02AbvagrP0bG-1yYK6hr1c'', 1),
(36, ''spread'', 27, ''gQFR8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1dVWDlnQm5sWlcwY1E3ZjRsbW4zAAIEOPUXWAMEAAAAAA=='', 0, 1, ''1532662361'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFR8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1dVWDlnQm5sWlcwY1E3ZjRsbW4zAAIEOPUXWAMEAAAAAA=='', ''http://weixin.qq.com/q/WUX9gBnlZW0cQ7f4lmn3'', 0),
(37, ''spread'', 25, ''gQFV8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLVNBQmdVUDBiRy0xMDAwMGcwM2QAAgRZklpbAwQAAAAA'', 0, 1, ''1532662361'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFV8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLVNBQmdVUDBiRy0xMDAwMGcwM2QAAgRZklpbAwQAAAAA'', ''http://weixin.qq.com/q/02-SABgUP0bG-10000g03d'', 0),
(38, ''spread'', 23, ''gQHL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnljdGdVUDBiRy0xMDAwME0wM3QAAgRaklpbAwQAAAAA'', 0, 1, ''1532662362'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnljdGdVUDBiRy0xMDAwME0wM3QAAgRaklpbAwQAAAAA'', ''http://weixin.qq.com/q/02FyctgUP0bG-10000M03t'', 0),
(39, ''spread'', 21, ''gQEo8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyamtFamdqUDBiRy0xMDAwMDAwMzEAAgRaklpbAwQAAAAA'', 0, 1, ''1532662362'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEo8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyamtFamdqUDBiRy0xMDAwMDAwMzEAAgRaklpbAwQAAAAA'', ''http://weixin.qq.com/q/02jkEjgjP0bG-100000031'', 0),
(40, ''spread'', 38, ''gQED8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL05FWDlqV3JsRVcxb1VOcUJsbW4zAAIEaNkiWAMEAAAAAA=='', 0, 1, ''1532915675'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQED8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL05FWDlqV3JsRVcxb1VOcUJsbW4zAAIEaNkiWAMEAAAAAA=='', ''http://weixin.qq.com/q/NEX9jWrlEW1oUNqBlmn3'', 0),
(41, ''spread'', 37, ''gQGE8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BVVXBUTmZsMlcyZ3ZVdGNRbW4zAAIEpsgiWAMEAAAAAA=='', 0, 1, ''1532915676'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGE8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BVVXBUTmZsMlcyZ3ZVdGNRbW4zAAIEpsgiWAMEAAAAAA=='', ''http://weixin.qq.com/q/pUUpTNfl2W2gvUtcQmn3'', 0),
(42, ''spread'', 34, ''gQHL8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3ZVV001UWZsZG0wUDNGUC01Mm4zAAIEkaEiWAMEAAAAAA=='', 0, 1, ''1532915676'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHL8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3ZVV001UWZsZG0wUDNGUC01Mm4zAAIEkaEiWAMEAAAAAA=='', ''http://weixin.qq.com/q/vUWM5Qfldm0P3FP-52n3'', 0),
(43, ''spread'', 33, ''gQEX8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL09VWE1hOExsS20xVEU5ZHRwMm4zAAIE7KAiWAMEAAAAAA=='', 0, 1, ''1532915676'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEX8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL09VWE1hOExsS20xVEU5ZHRwMm4zAAIE7KAiWAMEAAAAAA=='', ''http://weixin.qq.com/q/OUXMa8LlKm1TE9dtp2n3'', 0),
(44, ''spread'', 32, ''gQEL8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2FVVnlrLWZsa0czcGpvZVJHV24zAAIEq1AcWAMEAAAAAA=='', 0, 1, ''1532915676'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEL8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2FVVnlrLWZsa0czcGpvZVJHV24zAAIEq1AcWAMEAAAAAA=='', ''http://weixin.qq.com/q/aUVyk-flkG3pjoeRGWn3'', 0),
(45, ''spread'', 31, ''gQGk8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3MwVnBvcG5saFczOEMxMnVBbW4zAAIEKJoZWAMEAAAAAA=='', 0, 1, ''1532915676'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGk8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3MwVnBvcG5saFczOEMxMnVBbW4zAAIEKJoZWAMEAAAAAA=='', ''http://weixin.qq.com/q/s0VpopnlhW38C12uAmn3'', 0),
(46, ''spread'', 30, ''gQF68DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2drWGhQQlhsZm0wSEtHeERpbW4zAAIEEpgZWAMEAAAAAA=='', 0, 1, ''1532915676'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF68DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2drWGhQQlhsZm0wSEtHeERpbW4zAAIEEpgZWAMEAAAAAA=='', ''http://weixin.qq.com/q/gkXhPBXlfm0HKGxDimn3'', 0),
(47, ''spread'', 44, ''gQE08ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1UwV2Nna0xsZTIwQ0VMMkY5Mm4zAAIE9_8zWAMEAAAAAA=='', 0, 1, ''1533036102'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE08ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1UwV2Nna0xsZTIwQ0VMMkY5Mm4zAAIE9_8zWAMEAAAAAA=='', ''http://weixin.qq.com/q/U0WcgkLle20CEL2F92n3'', 0),
(48, ''spread'', 43, ''gQFl8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzVrWHhteS1sRTIxcTBRaVptbW4zAAIE7ugzWAMEAAAAAA=='', 0, 1, ''1533036102'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFl8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzVrWHhteS1sRTIxcTBRaVptbW4zAAIE7ugzWAMEAAAAAA=='', ''http://weixin.qq.com/q/5kXxmy-lE21q0QiZmmn3'', 0),
(49, ''spread'', 42, ''gQEZ8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1JFVzdVcXJsU20welA2cEQwR24zAAIEgOQzWAMEAAAAAA=='', 0, 1, ''1533036102'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEZ8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1JFVzdVcXJsU20welA2cEQwR24zAAIEgOQzWAMEAAAAAA=='', ''http://weixin.qq.com/q/REW7UqrlSm0zP6pD0Gn3'', 0),
(50, ''spread'', 41, ''gQGb8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BrV3l3X1RsSzIxU0FraTYyV24zAAIEnx8lWAMEAAAAAA=='', 0, 1, ''1533036102'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGb8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3BrV3l3X1RsSzIxU0FraTYyV24zAAIEnx8lWAMEAAAAAA=='', ''http://weixin.qq.com/q/pkWyw_TlK21SAki62Wn3'', 0),
(51, ''spread'', 40, ''gQFy8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2VVV182NS1sV0cwaGpwZnQxV24zAAIEzkokWAMEAAAAAA=='', 0, 1, ''1533036102'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFy8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2VVV182NS1sV0cwaGpwZnQxV24zAAIEzkokWAMEAAAAAA=='', ''http://weixin.qq.com/q/eUW_65-lWG0hjpft1Wn3'', 0),
(52, ''spread'', 39, ''gQFE8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzkwVVcyMWZsOG0yTF9CbmZmV24zAAIEsN8iWAMEAAAAAA=='', 0, 1, ''1533036102'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFE8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzkwVVcyMWZsOG0yTF9CbmZmV24zAAIEsN8iWAMEAAAAAA=='', ''http://weixin.qq.com/q/90UW21fl8m2L_BnffWn3'', 0),
(53, ''spread'', 57, ''gQEP8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLW5JSmhOUDBiRy0xMDAwMDAwM2MAAgS7oEZYAwQAAAAA'', 0, 1, ''1533120232'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEP8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLW5JSmhOUDBiRy0xMDAwMDAwM2MAAgS7oEZYAwQAAAAA'', ''http://weixin.qq.com/q/02-nIJhNP0bG-10000003c'', 0),
(54, ''spread'', 55, ''gQEw8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS1dqZGd4UDBiRy0xMDAwMHcwM2sAAgRFPEVYAwQAAAAA'', 0, 1, ''1533120232'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEw8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyS1dqZGd4UDBiRy0xMDAwMHcwM2sAAgRFPEVYAwQAAAAA'', ''http://weixin.qq.com/q/02KWjdgxP0bG-10000w03k'', 0),
(55, ''spread'', 54, ''gQHI8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAySlRVT2h3UDBiRy0xMDAwMDAwMzAAAgReO0VYAwQAAAAA'', 0, 1, ''1533120233'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHI8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAySlRVT2h3UDBiRy0xMDAwMDAwMzAAAgReO0VYAwQAAAAA'', ''http://weixin.qq.com/q/02JTUOhwP0bG-100000030'', 0),
(56, ''spread'', 50, ''gQGr8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzdFWEJfM25sV1cwZzdRS0RxbW4zAAIEL8c3WAMEAAAAAA=='', 0, 1, ''1533120233'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGr8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzdFWEJfM25sV1cwZzdRS0RxbW4zAAIEL8c3WAMEAAAAAA=='', ''http://weixin.qq.com/q/7EXB_3nlWW0g7QKDqmn3'', 0),
(57, ''spread'', 49, ''gQHX8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0ZVV2FoeS1sWUcwWlB2dWQ4V24zAAIE0o83WAMEAAAAAA=='', 0, 1, ''1533120233'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHX8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0ZVV2FoeS1sWUcwWlB2dWQ4V24zAAIE0o83WAMEAAAAAA=='', ''http://weixin.qq.com/q/FUWahy-lYG0ZPvud8Wn3'', 0),
(58, ''spread'', 48, ''gQF78DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2tFV0tMT3ZsWkcwZDFINGk0V24zAAIETPQ0WAMEAAAAAA=='', 0, 1, ''1533120233'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF78DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2tFV0tMT3ZsWkcwZDFINGk0V24zAAIETPQ0WAMEAAAAAA=='', ''http://weixin.qq.com/q/kEWKLOvlZG0d1H4i4Wn3'', 0),
(59, ''spread'', 46, ''gQHo8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3lrV3paTTdsTm0xUDR5UUIyR24zAAIEk/IzWAMEAAAAAA=='', 0, 1, ''1533120233'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHo8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3lrV3paTTdsTm0xUDR5UUIyR24zAAIEk/IzWAMEAAAAAA=='', ''http://weixin.qq.com/q/ykWzZM7lNm1P4yQB2Gn3'', 0),
(60, ''spread'', 64, ''gQHD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybWNXVWc5UDBiRy0xMDAwME0wMzIAAgQUY1JYAwQAAAAA'', 0, 1, ''1533264284'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHD8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybWNXVWc5UDBiRy0xMDAwME0wMzIAAgQUY1JYAwQAAAAA'', ''http://weixin.qq.com/q/02mcWUg9P0bG-10000M032'', 0),
(61, ''spread'', 62, ''gQHl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVJwTmcyUDBiRy0xMDAwME0wM2gAAgRsr1BYAwQAAAAA'', 0, 1, ''1533264284'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHl8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVJwTmcyUDBiRy0xMDAwME0wM2gAAgRsr1BYAwQAAAAA'', ''http://weixin.qq.com/q/02yRpNg2P0bG-10000M03h'', 0),
(62, ''spread'', 61, ''gQEs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZXVieWhwUDBiRy0xMDAwMGcwMzQAAgQRc09YAwQAAAAA'', 0, 1, ''1533264284'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZXVieWhwUDBiRy0xMDAwMGcwMzQAAgQRc09YAwQAAAAA'', ''http://weixin.qq.com/q/02eubyhpP0bG-10000g034'', 0),
(63, ''spread'', 60, ''gQGL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmFkdWhzUDBiRy0xMDAwMGcwM2UAAgQx7ExYAwQAAAAA'', 0, 1, ''1533264284'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGL8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmFkdWhzUDBiRy0xMDAwMGcwM2UAAgQx7ExYAwQAAAAA'', ''http://weixin.qq.com/q/02RaduhsP0bG-10000g03e'', 0),
(64, ''spread'', 59, ''gQFt8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeEVRWmh0UDBiRy0xMDAwMDAwM1oAAgS/5kdYAwQAAAAA'', 0, 1, ''1533264284'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFt8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeEVRWmh0UDBiRy0xMDAwMDAwM1oAAgS/5kdYAwQAAAAA'', ''http://weixin.qq.com/q/02xEQZhtP0bG-10000003Z'', 0),
(65, ''spread'', 67, ''gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaHNxTWdyUDBiRy0xMDAwMDAwM00AAgRColhYAwQAAAAA'', 0, 1, ''1533295071'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaHNxTWdyUDBiRy0xMDAwMDAwM00AAgRColhYAwQAAAAA'', ''http://weixin.qq.com/q/02hsqMgrP0bG-10000003M'', 0),
(66, ''spread'', 66, ''gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyME15emdNUDBiRy0xMDAwMGcwM3IAAgSWQ1VYAwQAAAAA'', 0, 1, ''1533295071'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyME15emdNUDBiRy0xMDAwMGcwM3IAAgSWQ1VYAwQAAAAA'', ''http://weixin.qq.com/q/020MyzgMP0bG-10000g03r'', 0),
(67, ''spread'', 65, ''gQF/8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydmVWeWh4UDBiRy0xMDAwMGcwM2EAAgRKuFRYAwQAAAAA'', 0, 1, ''1533295072'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF/8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydmVWeWh4UDBiRy0xMDAwMGcwM2EAAgRKuFRYAwQAAAAA'', ''http://weixin.qq.com/q/02veVyhxP0bG-10000g03a'', 0),
(68, ''spread'', 69, ''gQHg8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycm9WS2hIUDBiRy0xMDAwMGcwM3EAAgQ6q1hYAwQAAAAA'', 0, 1, ''1533539635'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHg8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycm9WS2hIUDBiRy0xMDAwMGcwM3EAAgQ6q1hYAwQAAAAA'', ''http://weixin.qq.com/q/02roVKhHP0bG-10000g03q'', 0),
(69, ''spread'', 71, ''gQGD8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZEV5ZGhWUDBiRy0xMDAwME0wM0MAAgSM4GRYAwQAAAAA'', 0, 1, ''1533622617'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGD8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZEV5ZGhWUDBiRy0xMDAwME0wM0MAAgSM4GRYAwQAAAAA'', ''http://weixin.qq.com/q/02dEydhVP0bG-10000M03C'', 0),
(70, ''spread'', 74, ''gQE28TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ24yTGhTUDBiRy0xMDAwME0wM1kAAgQCz2xYAwQAAAAA'', 0, 1, ''1533779476'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE28TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ24yTGhTUDBiRy0xMDAwME0wM1kAAgQCz2xYAwQAAAAA'', ''http://weixin.qq.com/q/02gn2LhSP0bG-10000M03Y'', 0),
(71, ''spread'', 73, ''gQGF8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTlR4S2duUDBiRy0xMDAwMDAwM3EAAgTleGpYAwQAAAAA'', 0, 1, ''1533779476'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGF8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTlR4S2duUDBiRy0xMDAwMDAwM3EAAgTleGpYAwQAAAAA'', ''http://weixin.qq.com/q/02NTxKgnP0bG-10000003q'', 0),
(72, ''spread'', 82, ''gQHe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ0haS2dpUDBiRy0xMDAwMGcwM2MAAgTRr9laAwQAAAAA'', 0, 1, ''1533863539'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHe8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZ0haS2dpUDBiRy0xMDAwMGcwM2MAAgTRr9laAwQAAAAA'', ''http://weixin.qq.com/q/02gHZKgiP0bG-10000g03c'', 0),
(73, ''spread'', 81, ''gQHa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydEljVmdYUDBiRy0xMDAwME0wM28AAgTSr9laAwQAAAAA'', 0, 1, ''1533863539'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHa8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydEljVmdYUDBiRy0xMDAwME0wM28AAgTSr9laAwQAAAAA'', ''http://weixin.qq.com/q/02tIcVgXP0bG-10000M03o'', 0),
(74, ''spread'', 80, ''gQFr8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLUQ0bGg4UDBiRy0xMDAwMGcwM0gAAgTSr9laAwQAAAAA'', 0, 1, ''1533863539'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFr8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLUQ0bGg4UDBiRy0xMDAwMGcwM0gAAgTSr9laAwQAAAAA'', ''http://weixin.qq.com/q/02-D4lh8P0bG-10000g03H'', 0),
(75, ''spread'', 79, ''gQHw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1Z0SmdvUDBiRy0xMDAwME0wM1UAAgTSr9laAwQAAAAA'', 0, 1, ''1533863539'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1Z0SmdvUDBiRy0xMDAwME0wM1UAAgTSr9laAwQAAAAA'', ''http://weixin.qq.com/q/02OVtJgoP0bG-10000M03U'', 0),
(76, ''spread'', 77, ''gQFd8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycVNuemczUDBiRy0xMDAwME0wM3YAAgQ2sNlaAwQAAAAA'', 0, 1, ''1533863539'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFd8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycVNuemczUDBiRy0xMDAwME0wM3YAAgQ2sNlaAwQAAAAA'', ''http://weixin.qq.com/q/02qSnzg3P0bG-10000M03v'', 0),
(77, ''spread'', 76, ''gQGZ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycW5JOWdtUDBiRy0xMDAwME0wMzkAAgTYhnVYAwQAAAAA'', 0, 1, ''1533863540'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGZ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycW5JOWdtUDBiRy0xMDAwME0wMzkAAgTYhnVYAwQAAAAA'', ''http://weixin.qq.com/q/02qnI9gmP0bG-10000M039'', 0),
(78, ''spread'', 86, ''gQEF8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ080SGhoUDBiRy0xMDAwMHcwM3QAAgTPr9laAwQAAAAA'', 0, 1, ''1534145730'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEF8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ080SGhoUDBiRy0xMDAwMHcwM3QAAgTPr9laAwQAAAAA'', ''http://weixin.qq.com/q/02CO4HhhP0bG-10000w03t'', 0),
(79, ''spread'', 93, ''gQEG8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWWR5TWdjUDBiRy0xMDAwMDAwM04AAgTMr9laAwQAAAAA'', 0, 1, ''1534232932'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEG8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWWR5TWdjUDBiRy0xMDAwMDAwM04AAgTMr9laAwQAAAAA'', ''http://weixin.qq.com/q/02YdyMgcP0bG-10000003N'', 0),
(80, ''spread'', 92, ''gQHX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRE44bmdwUDBiRy0xMDAwMHcwM3MAAgTNr9laAwQAAAAA'', 0, 1, ''1534232932'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHX8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRE44bmdwUDBiRy0xMDAwMHcwM3MAAgTNr9laAwQAAAAA'', ''http://weixin.qq.com/q/02DN8ngpP0bG-10000w03s'', 0),
(81, ''spread'', 91, ''gQG78DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMkpydWh1UDBiRy0xQ3V1cjFyMUMAAgSe0XNbAwQAjScA'', 1536908958, 1, ''1534232933'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG78DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMkpydWh1UDBiRy0xQ3V1cjFyMUMAAgSe0XNbAwQAjScA'', ''http://weixin.qq.com/q/022JruhuP0bG-1Cuur1r1C'', 0),
(82, ''spread'', 90, ''gQEB8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYllPUmh1UDBiRy0xMDAwME0wM0kAAgTNr9laAwQAAAAA'', 0, 1, ''1534232933'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEB8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYllPUmh1UDBiRy0xMDAwME0wM0kAAgTNr9laAwQAAAAA'', ''http://weixin.qq.com/q/02bYORhuP0bG-10000M03I'', 0),
(83, ''spread'', 89, ''gQGT8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydG82MmctUDBiRy0xMDAwMDAwMy0AAgTOr9laAwQAAAAA'', 0, 1, ''1534232933'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGT8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydG82MmctUDBiRy0xMDAwMDAwMy0AAgTOr9laAwQAAAAA'', ''http://weixin.qq.com/q/02to62g-P0bG-10000003-'', 0),
(84, ''spread'', 88, ''gQGQ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWElybmh4UDBiRy0xMDAwMGcwM3cAAgTOr9laAwQAAAAA'', 0, 1, ''1534232933'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGQ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWElybmh4UDBiRy0xMDAwMGcwM3cAAgTOr9laAwQAAAAA'', ''http://weixin.qq.com/q/02XIrnhxP0bG-10000g03w'', 0),
(85, ''spread'', 87, ''gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaV9Lc2hlUDBiRy0xMDAwMDAwM1IAAgTPr9laAwQAAAAA'', 0, 1, ''1534232933'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyaV9Lc2hlUDBiRy0xMDAwMDAwM1IAAgTPr9laAwQAAAAA'', ''http://weixin.qq.com/q/02i_KsheP0bG-10000003R'', 0),
(86, ''spread'', 85, ''gQF08jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMUQyY2h2UDBiRy0xMDAwMDAwM0IAAgTPr9laAwQAAAAA'', 0, 1, ''1534232934'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF08jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMUQyY2h2UDBiRy0xMDAwMDAwM0IAAgTPr9laAwQAAAAA'', ''http://weixin.qq.com/q/021D2chvP0bG-10000003B'', 0),
(87, ''spread'', 84, ''gQEi8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZjZWVmdMUDBiRy0xMDAwMGcwM0gAAgTRr9laAwQAAAAA'', 0, 1, ''1534232934'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEi8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZjZWVmdMUDBiRy0xMDAwMGcwM0gAAgTRr9laAwQAAAAA'', ''http://weixin.qq.com/q/02f6VVgLP0bG-10000g03H'', 0),
(88, ''spread'', 83, ''gQHK8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyd2pBUGdsUDBiRy0xMDAwME0wM3AAAgTRr9laAwQAAAAA'', 0, 1, ''1534232934'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHK8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyd2pBUGdsUDBiRy0xMDAwME0wM3AAAgTRr9laAwQAAAAA'', ''http://weixin.qq.com/q/02wjAPglP0bG-10000M03p'', 0),
(89, ''spread'', 78, ''gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycURIeGdPUDBiRy0xMDAwME0wM3gAAgQ1sNlaAwQAAAAA'', 0, 1, ''1534232934'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycURIeGdPUDBiRy0xMDAwME0wM3gAAgQ1sNlaAwQAAAAA'', ''http://weixin.qq.com/q/02qDHxgOP0bG-10000M03x'', 0),
(90, ''spread'', 75, ''gQG68DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnpRSWdQUDBiRy0xMDAwMHcwM28AAgR_g3VYAwQAAAAA'', 0, 1, ''1534232934'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG68DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRnpRSWdQUDBiRy0xMDAwMHcwM28AAgR_g3VYAwQAAAAA'', ''http://weixin.qq.com/q/02FzQIgPP0bG-10000w03o'', 0),
(91, ''spread'', 72, ''gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVVOVWdhUDBiRy0xMDAwMHcwM3QAAgQB8GRYAwQAAAAA'', 0, 1, ''1534239978'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGu8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyeVVOVWdhUDBiRy0xMDAwMHcwM3QAAgQB8GRYAwQAAAAA'', ''http://weixin.qq.com/q/02yUNUgaP0bG-10000w03t'', 0),
(92, ''spread'', 70, ''gQGr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1FLVWhOUDBiRy0xMDAwMDAwMzcAAgQYr1hYAwQAAAAA'', 0, 1, ''1534239978'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyT1FLVWhOUDBiRy0xMDAwMDAwMzcAAgQYr1hYAwQAAAAA'', ''http://weixin.qq.com/q/02OQKUhNP0bG-100000037'', 0),
(93, ''spread'', 68, ''gQE68TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYnd6NWhlUDBiRy0xMDAwMHcwMzYAAgTpqlhYAwQAAAAA'', 0, 1, ''1534239978'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE68TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYnd6NWhlUDBiRy0xMDAwMHcwMzYAAgTpqlhYAwQAAAAA'', ''http://weixin.qq.com/q/02bwz5heP0bG-10000w036'', 0),
(94, ''spread'', 63, ''gQEr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU3M1RGhQUDBiRy0xMDAwMHcwM0IAAgTtDVJYAwQAAAAA'', 0, 1, ''1534239979'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEr8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU3M1RGhQUDBiRy0xMDAwMHcwM0IAAgTtDVJYAwQAAAAA'', ''http://weixin.qq.com/q/02Ss5DhPP0bG-10000w03B'', 0),
(95, ''spread'', 58, ''gQF18TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydTBPaWdYUDBiRy0xMDAwMDAwM08AAgRHiUdYAwQAAAAA'', 0, 1, ''1534239979'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF18TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydTBPaWdYUDBiRy0xMDAwMDAwM08AAgRHiUdYAwQAAAAA'', ''http://weixin.qq.com/q/02u0OigXP0bG-10000003O'', 0),
(96, ''spread'', 56, ''gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUDg3dGcxUDBiRy0xMDAwMGcwM08AAgTVPUVYAwQAAAAA'', 0, 1, ''1534239979'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFP8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUDg3dGcxUDBiRy0xMDAwMGcwM08AAgTVPUVYAwQAAAAA'', ''http://weixin.qq.com/q/02P87tg1P0bG-10000g03O'', 0),
(97, ''spread'', 13, ''gQHs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydlJFdmd1UDBiRy0xMDAwMGcwM24AAgTupHJbAwQAAAAA'', 0, 1, ''1534239982'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHs8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydlJFdmd1UDBiRy0xMDAwMGcwM24AAgTupHJbAwQAAAAA'', ''http://weixin.qq.com/q/02vREvguP0bG-10000g03n'', 0),
(98, ''spread'', 12, ''gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG9HQWg5UDBiRy0xMDAwMHcwM1oAAgTupHJbAwQAAAAA'', 0, 1, ''1534239983'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycG9HQWg5UDBiRy0xMDAwMHcwM1oAAgTupHJbAwQAAAAA'', ''http://weixin.qq.com/q/02poGAh9P0bG-10000w03Z'', 0),
(99, ''spread'', 94, ''gQEY8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU29hRWdHUDBiRy0xMDAwME0wM1YAAgTMr9laAwQAAAAA'', 0, 1, ''1534294997'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEY8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyU29hRWdHUDBiRy0xMDAwME0wM1YAAgTMr9laAwQAAAAA'', ''http://weixin.qq.com/q/02SoaEgGP0bG-10000M03V'', 0),
(100, ''spread'', 95, ''gQHW8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUjJUT2dSUDBiRy0xejdzcnhyMWMAAgTHj3NbAwQAjScA'', 1536892103, 1, ''1534295469'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHW8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUjJUT2dSUDBiRy0xejdzcnhyMWMAAgTHj3NbAwQAjScA'', ''http://weixin.qq.com/q/02R2TOgRP0bG-1z7srxr1c'', 2),
(101, ''spread'', 45, ''gQEH8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0lrVUFGSm5sN20yWExNd2FhMm4zAAIEhPAzWAMEAAAAAA=='', 0, 1, ''1534298167'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEH8ToAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0lrVUFGSm5sN20yWExNd2FhMm4zAAIEhPAzWAMEAAAAAA=='', ''http://weixin.qq.com/q/IkUAFJnl7m2XLMwaa2n3'', 0),
(102, ''spread'', 97, ''gQGz8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWlHamdJUDBiRy0xMDAwMHcwM3kAAgTLr9laAwQAAAAA'', 0, 1, ''1534301384'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGz8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWlHamdJUDBiRy0xMDAwMHcwM3kAAgTLr9laAwQAAAAA'', ''http://weixin.qq.com/q/02eiGjgIP0bG-10000w03y'', 0),
(103, ''spread'', 96, ''gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMEpCOWdoUDBiRy0xMDAwMHcwMzAAAgTLr9laAwQAAAAA'', 0, 1, ''1534301385'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMEpCOWdoUDBiRy0xMDAwMHcwMzAAAgTLr9laAwQAAAAA'', ''http://weixin.qq.com/q/020JB9ghP0bG-10000w030'', 0),
(104, ''spread'', 98, ''gQEN8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM2RrVGdnUDBiRy0xMDAwMDAwM3IAAgTKr9laAwQAAAAA'', 0, 1, ''1534312887'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEN8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyM2RrVGdnUDBiRy0xMDAwMDAwM3IAAgTKr9laAwQAAAAA'', ''http://weixin.qq.com/q/023dkTggP0bG-10000003r'', 0),
(105, ''spread'', 18, ''gQG48TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRjQ5YmhfUDBiRy0xMDAwMGcwMzEAAgTEwXNbAwQAAAAA'', 0, 1, ''1534312900'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG48TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyRjQ5YmhfUDBiRy0xMDAwMGcwMzEAAgTEwXNbAwQAAAAA'', ''http://weixin.qq.com/q/02F49bh_P0bG-10000g031'', 0),
(106, ''spread'', 16, ''gQGw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWnZuMmhCUDBiRy0xMDAwMDAwMzIAAgTEwXNbAwQAAAAA'', 0, 1, ''1534312900'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGw8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWnZuMmhCUDBiRy0xMDAwMDAwMzIAAgTEwXNbAwQAAAAA'', ''http://weixin.qq.com/q/02Zvn2hBP0bG-100000032'', 0),
(107, ''spread'', 14, ''gQEx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX2NUbWdTUDBiRy0xMDAwME0wM3EAAgTEwXNbAwQAAAAA'', 0, 1, ''1534312900'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyX2NUbWdTUDBiRy0xMDAwME0wM3EAAgTEwXNbAwQAAAAA'', ''http://weixin.qq.com/q/02_cTmgSP0bG-10000M03q'', 0),
(108, ''spread'', 24, ''gQFO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmJIX2dMUDBiRy0xMDAwMDAwM24AAgTd2XRbAwQAAAAA'', 0, 1, ''1534384605'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmJIX2dMUDBiRy0xMDAwMDAwM24AAgTd2XRbAwQAAAAA'', ''http://weixin.qq.com/q/02RbH_gLP0bG-10000003n'', 0),
(109, ''spread'', 99, ''gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN2lyN2dmUDBiRy0xMDAwMGcwM1AAAgTVsdlaAwQAAAAA'', 0, 1, ''1534403314'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH58TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyN2lyN2dmUDBiRy0xMDAwMGcwM1AAAgTVsdlaAwQAAAAA'', ''http://weixin.qq.com/q/027ir7gfP0bG-10000g03P'', 0),
(110, ''spread'', 103, ''gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQTV0QWhGUDBiRy0xMDAwMGcwMzUAAgSPrQxbAwQAAAAA'', 0, 1, ''1534405969'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFn8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQTV0QWhGUDBiRy0xMDAwMGcwMzUAAgSPrQxbAwQAAAAA'', ''http://weixin.qq.com/q/02A5tAhFP0bG-10000g035'', 0),
(111, ''spread'', 102, ''gQGJ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWZzUWdjUDBiRy0xMDAwME0wM2cAAgS62vtaAwQAAAAA'', 0, 1, ''1534405969'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGJ8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyZWZzUWdjUDBiRy0xMDAwME0wM2cAAgS62vtaAwQAAAAA'', ''http://weixin.qq.com/q/02efsQgcP0bG-10000M03g'', 0),
(112, ''spread'', 101, ''gQGp8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUlVQVGdfUDBiRy0xMDAwMHcwM00AAgRDht5aAwQAAAAA'', 0, 1, ''1534405969'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGp8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUlVQVGdfUDBiRy0xMDAwMHcwM00AAgRDht5aAwQAAAAA'', ''http://weixin.qq.com/q/02RUPTg_P0bG-10000w03M'', 0),
(113, ''spread'', 100, ''gQG38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNWJycWhsUDBiRy0xMDAwMDAwM0kAAgRDht5aAwQAAAAA'', 0, 1, ''1534405970'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG38TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyNWJycWhsUDBiRy0xMDAwMDAwM0kAAgRDht5aAwQAAAAA'', ''http://weixin.qq.com/q/025brqhlP0bG-10000003I'', 0),
(114, ''spread'', 22, ''gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMHZfQ2hBUDBiRy0xMDAwME0wM2wAAgSNMXVbAwQAAAAA'', 0, 1, ''1534407053'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFH8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMHZfQ2hBUDBiRy0xMDAwME0wM2wAAgSNMXVbAwQAAAAA'', ''http://weixin.qq.com/q/020v_ChAP0bG-10000M03l'', 0),
(115, ''spread'', 36, ''gQG18DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0NFWFg0RWpsUkcwOUlfYVR2R24zAAIEXrwiWAMEAAAAAA=='', 0, 1, ''1534407067'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG18DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0NFWFg0RWpsUkcwOUlfYVR2R24zAAIEXrwiWAMEAAAAAA=='', ''http://weixin.qq.com/q/CEXX4EjlRG09I_aTvGn3'', 0),
(116, ''spread'', 35, ''gQEa8joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzhFVy1IN3JsVTIwcTFoNFQxR24zAAIEmKEiWAMEAAAAAA=='', 0, 1, ''1534407067'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEa8joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzhFVy1IN3JsVTIwcTFoNFQxR24zAAIEmKEiWAMEAAAAAA=='', ''http://weixin.qq.com/q/8EW-H7rlU20q1h4T1Gn3'', 0),
(117, ''spread'', 29, ''gQF/8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL01rV1BrQ1BsRW0xckF0enQ1R24zAAIEy/YXWAMEAAAAAA=='', 0, 1, ''1534407067'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF/8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL01rV1BrQ1BsRW0xckF0enQ1R24zAAIEy/YXWAMEAAAAAA=='', ''http://weixin.qq.com/q/MkWPkCPlEm1rAtzt5Gn3'', 0),
(118, ''spread'', 28, ''gQGN7zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3drV29sVmpsWFcwa2pDeUF3Mm4zAAIEYPYXWAMEAAAAAA=='', 0, 1, ''1534407067'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGN7zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3drV29sVmpsWFcwa2pDeUF3Mm4zAAIEYPYXWAMEAAAAAA=='', ''http://weixin.qq.com/q/wkWolVjlXW0kjCyAw2n3'', 0),
(119, ''spread'', 26, ''gQEc8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycThwWmcwUDBiRy0xMDAwME0wM3AAAgSbMXVbAwQAAAAA'', 0, 1, ''1534407067'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEc8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycThwWmcwUDBiRy0xMDAwME0wM3AAAgSbMXVbAwQAAAAA'', ''http://weixin.qq.com/q/02q8pZg0P0bG-10000M03p'', 0),
(120, ''spread'', 53, ''gQF08DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybGd2Y2dKUDBiRy0xMDAwMHcwM00AAgQkKUVYAwQAAAAA'', 0, 1, ''1534407079'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQF08DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybGd2Y2dKUDBiRy0xMDAwMHcwM00AAgQkKUVYAwQAAAAA'', ''http://weixin.qq.com/q/02lgvcgJP0bG-10000w03M'', 0),
(121, ''spread'', 104, ''gQGx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydVJpR2hzUDBiRy0xMDAwMGcwM3IAAgSPrQxbAwQAAAAA'', 0, 1, ''1534408904'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGx8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydVJpR2hzUDBiRy0xMDAwMGcwM3IAAgSPrQxbAwQAAAAA'', ''http://weixin.qq.com/q/02uRiGhsP0bG-10000g03r'', 0),
(122, ''spread'', 108, ''gQE_8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMTdBdWdUUDBiRy0xMDAwME0wMzYAAgSL8wxbAwQAAAAA'', 0, 1, ''1534726889'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQE_8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMTdBdWdUUDBiRy0xMDAwME0wMzYAAgSL8wxbAwQAAAAA'', ''http://weixin.qq.com/q/0217AugTP0bG-10000M036'', 0),
(123, ''spread'', 107, ''gQH_8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTkFrX2dWUDBiRy0xMDAwME0wMzMAAgSX7QxbAwQAAAAA'', 0, 1, ''1534726889'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH_8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTkFrX2dWUDBiRy0xMDAwME0wMzMAAgSX7QxbAwQAAAAA'', ''http://weixin.qq.com/q/02NAk_gVP0bG-10000M033'', 0),
(124, ''spread'', 106, ''gQEh8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXdtaWczUDBiRy0xMDAwMDAwM3MAAgSPrQxbAwQAAAAA'', 0, 1, ''1534726889'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEh8jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyMXdtaWczUDBiRy0xMDAwMDAwM3MAAgSPrQxbAwQAAAAA'', ''http://weixin.qq.com/q/021wmig3P0bG-10000003s'', 0),
(125, ''spread'', 105, ''gQGO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycHR2RWdoUDBiRy0xMDAwMHcwM2kAAgSPrQxbAwQAAAAA'', 0, 1, ''1534726890'', ''https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGO8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAycHR2RWdoUDBiRy0xMDAwMHcwM2kAAgSPrQxbAwQAAAAA'', ''http://weixin.qq.com/q/02ptvEghP0bG-10000w03i'', 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_reply`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_reply` (
  `id` mediumint(8) unsigned NOT NULL COMMENT ''微信关键字回复id'',
  `key` varchar(64) NOT NULL COMMENT ''关键字'',
  `type` varchar(32) NOT NULL COMMENT ''回复类型'',
  `data` text NOT NULL COMMENT ''回复数据'',
  `status` tinyint(1) unsigned NOT NULL DEFAULT ''1'' COMMENT ''0=不可用  1 =可用'',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''是否隐藏''
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT=''微信关键字回复表'';

--
-- 转存表中的数据 `eb_wechat_reply`
--

INSERT INTO `eb_wechat_reply` (`id`, `key`, `type`, `data`, `status`, `hide`) VALUES
(1, ''subscribe'', ''text'', ''{"content":"\\u6b22\\u8fce\\u5173\\u6ce8\\u201cCRMEB\\u201d\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\\uff01\\n\\u540e\\u53f0\\u4f53\\u9a8c\\u5730\\u5740\\uff1ahttp:\\/\\/demo.crmeb.net\\n\\u8d26\\u53f7\\uff1ademo \\u5bc6\\u7801\\uff1acrmeb.com\\n\\u670d\\u52a1\\u7535\\u8bdd\\uff1a400-8888-794"}'', 1, 1),
(9, ''default'', ''text'', ''{"content":"\\u66f4\\u591a\\u54a8\\u8be2\\u8bf7\\u62e8\\u6253\\u70ed\\u7ebf\\u7535\\u8bdd\\uff1a400-8888-794"}'', 1, 1),
(21, ''源码'', ''text'', ''{"content":"\\u672a\\u7ecf\\u8fc7\\u5546\\u4e1a\\u6388\\u6743\\uff0c\\u4e0d\\u5f97\\u8fdb\\u884c\\u51fa\\u79df\\u3001\\u51fa\\u552e\\u3001\\u62b5\\u62bc\\u6216\\u53d1\\u653e\\u5b50\\u8bb8\\u53ef\\u8bc1\\u3002\\n\\u4e0b\\u8f7d\\u5730\\u5740\\uff1a\\n\\u94fe\\u63a5\\uff1ahttps:\\/\\/pan.baidu.com\\/s\\/1eMOoxWHvN7KuQTDLhIJjAg \\u5bc6\\u7801\\uff1a55RR"}'', 1, 0),
(20, ''演示'', ''text'', ''{"content":"\\u540e\\u53f0\\u6f14\\u793a\\u5730\\u5740\\uff1ahttp:\\/\\/demo.crmeb.net\\/admin\\n\\u6f14\\u793a\\u8d26\\u53f7\\uff1ademo\\n\\u5bc6\\u7801\\uff1acrmeb.com"}'', 1, 0),
(24, ''客户常见问题'', ''text'', ''{"content":"http:\\/\\/shop.crmeb.net\\/wap\\/article\\/index\\/cid\\/4"}'', 1, 0),
(25, ''开票信息'', ''text'', ''{"content":"\\u516c\\u53f8\\u540d\\u79f0\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u7eb3\\u7a0e\\u8bc6\\u522b\\u53f7\\uff1a9161010409666664X0\\n\\u5730\\u5740\\uff1a\\u897f\\u5b89\\u5e02\\u83b2\\u6e56\\u533a\\u9f99\\u6e56MOCO\\u56fd\\u9645\\u7b2c1\\u5e621\\u5355\\u514314\\u5c4211411\\u53f7\\u623f\\n\\u5f00\\u6237\\u884c\\u548c\\u8d26\\u53f7\\uff1a\\u6c11\\u751f\\u94f6\\u884c\\u897f\\u5927\\u8857\\u652f\\u884c 691040854"}'', 1, 0),
(22, ''微信配置'', ''text'', ''{"content":"\\u6388\\u6743\\u63a5\\u53e3 :\\n\\n\\/wechat\\/index\\/serve\\n\\u652f\\u4ed8api\\u63a5\\u53e3 :\\n\\n\\/wap\\/my\\/\\n\\/wap\\/my\\/order\\/uni\\/\\n\\/wap\\/store\\/confirm_order\\/cartId\\/\\n\\/wap\\/store\\/combination_order\\/\\n\\u5982\\u679c\\u670d\\u52a1\\u5668\\u914d\\u7f6e\\u6ca1\\u6709\\u9690\\u85cfindex.php,\\u8bf7\\u5728\\u63a5\\u53e3\\u524d\\u52a0\\u4e0aindex.php\\n\\u4f8b\\u5982\\uff1ahttp:\\/\\/www.abc.com\\/index.php\\/wechat\\/index\\/serve\\n\\u6a21\\u677f\\u6d88\\u606f\\n\\nIT\\u79d1\\u6280 | \\u4e92\\u8054\\u7f51|\\u7535\\u5b50\\u5546\\u52a1\\nIT\\u79d1\\u6280 | IT\\u8f6f\\u4ef6\\u4e0e\\u670d\\u52a1"}'', 1, 0),
(23, ''帮助'', ''text'', ''{"content":"\\u5fae\\u4fe1\\u914d\\u7f6e\\n\\u6f14\\u793a\\n\\u6e90\\u7801\\n\\u5ba2\\u670d\\u7535\\u8bdd\\uff1a400-8888-794"}'', 1, 0),
(26, ''对公账户'', ''text'', ''{"content":"\\u6cd5\\u4eba\\u8d26\\u53f7\\uff1a\\n\\u4e2d\\u56fd\\u519c\\u4e1a\\u94f6\\u884c\\u5361\\u53f7\\uff1a622848 0211 3030 15310  \\n\\u7528\\u6237\\u540d\\uff1a\\u8bb8\\u8363\\u8000  \\n\\u5f00\\u6237\\u884c\\u5730\\u5740\\uff1a\\u897f\\u5b89\\u52b3\\u52a8\\u8def\\u652f\\u884c\\n\\n\\n\\u516c\\u53f8\\u652f\\u4ed8\\u5b9d\\uff1a\\n\\u5e10\\u53f7\\uff1a1242777321@qq.com\\n\\u59d3\\u540d\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\n\\u5bf9\\u516c\\u8d26\\u6237\\uff1a\\n\\n\\u516c\\u53f8\\u540d\\u79f0\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u5f00\\u6237\\u884c\\uff1a\\u4e2d\\u56fd\\u6c11\\u751f\\u94f6\\u884c\\u80a1\\u4efd\\u6709\\u9650\\u516c\\u53f8\\u897f\\u5b89\\u897f\\u5927\\u8857\\u652f\\u884c\\n\\u5e10\\u53f7\\uff1a691040854 "}'', 1, 0),
(27, ''银行账号'', ''text'', ''{"content":"\\u6cd5\\u4eba\\u8d26\\u53f7\\uff1a\\n\\u4e2d\\u56fd\\u519c\\u4e1a\\u94f6\\u884c\\u5361\\u53f7\\uff1a622848 0211 3030 15310  \\n\\u7528\\u6237\\u540d\\uff1a\\u8bb8\\u8363\\u8000  \\n\\u5f00\\u6237\\u884c\\u5730\\u5740\\uff1a\\u897f\\u5b89\\u52b3\\u52a8\\u8def\\u652f\\u884c\\n\\u516c\\u53f8\\u652f\\u4ed8\\u5b9d\\uff1a\\n\\u5e10\\u53f7\\uff1a1242777321@qq.com\\n\\u59d3\\u540d\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u5bf9\\u516c\\u8d26\\u6237\\uff1a\\n\\u516c\\u53f8\\u540d\\u79f0\\uff1a\\u897f\\u5b89\\u4f17\\u90a6\\u7f51\\u7edc\\u79d1\\u6280\\u6709\\u9650\\u516c\\u53f8\\n\\u5f00\\u6237\\u884c\\uff1a\\u4e2d\\u56fd\\u6c11\\u751f\\u94f6\\u884c\\u80a1\\u4efd\\u6709\\u9650\\u516c\\u53f8\\u897f\\u5b89\\u897f\\u5927\\u8857\\u652f\\u884c\\n\\u5e10\\u53f7\\uff1a691040854 "}'', 1, 0),
(28, ''案例'', ''text'', ''{"content":"\\u832f\\u8336\\u9547\\u3001\\u7f8e\\u62fc\\u5427\\u3001"}'', 1, 0),
(29, ''公司电话'', ''text'', ''{"content":"400-8888-794\\n029-65610380\\n029-68507850"}'', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_template`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_template` (
  `id` int(10) unsigned NOT NULL COMMENT ''模板id'',
  `tempkey` char(50) NOT NULL COMMENT ''模板编号'',
  `name` char(100) NOT NULL COMMENT ''模板名'',
  `content` varchar(1000) NOT NULL COMMENT ''回复内容'',
  `tempid` char(100) DEFAULT NULL COMMENT ''模板ID'',
  `add_time` varchar(15) NOT NULL COMMENT ''添加时间'',
  `status` tinyint(4) NOT NULL DEFAULT ''0'' COMMENT ''状态''
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT=''微信模板'';

--
-- 转存表中的数据 `eb_wechat_template`
--

INSERT INTO `eb_wechat_template` (`id`, `tempkey`, `name`, `content`, `tempid`, `add_time`, `status`) VALUES
(3, ''OPENTM200565259'', ''订单发货提醒'', ''{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n物流公司：{{keyword2.DATA}}\n物流单号：{{keyword3.DATA}}\n{{remark.DATA}}'', ''RRsyuuWpCo81xCtfG-5qYnXXoeSQHY4mTVav0zzaZsM'', ''1515052638'', 1),
(4, ''OPENTM413386489'', ''订单收货通知'', ''{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n订单状态：{{keyword2.DATA}}\n收货时间：{{keyword3.DATA}}\n商品详情：{{keyword4.DATA}}\n{{remark.DATA}}'', ''caAhoWioDb2A8Ew1bTr4GTe6mdsDoM4kjp9XV5BC8hg'', ''1515052765'', 1),
(5, ''OPENTM410119152'', ''退款进度通知'', ''{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n订单金额：{{keyword2.DATA}}\n下单时间：{{keyword3.DATA}}\n{{remark.DATA}}'', ''-WH6gUzezKnX9OTam9VrQEVyNWfr1bUhT6FRuBMotZw'', ''1515053049'', 1),
(6, ''OPENTM405847076'', ''帐户资金变动提醒'', ''{{first.DATA}}\n变动类型：{{keyword1.DATA}}\n变动时间：{{keyword2.DATA}}\n变动金额：{{keyword3.DATA}}\n{{remark.DATA}}'', ''dTNjE5QY6FzXyH0jbXEkNeNTgFQeMxdvqZRvDBqgatE'', ''1515053127'', 1),
(7, ''OPENTM207707249'', ''订单发货提醒'', ''\n{{first.DATA}}\n商品明细：{{keyword1.DATA}}\n下单时间：{{keyword2.DATA}}\n配送地址：{{keyword3.DATA}}\n配送人：{{keyword4.DATA}}\n联系电话：{{keyword5.DATA}}\n{{remark.DATA}}'', ''hC9PFuxOKq6u5kNZyl6VdHGgAuA6h5I3ztpuDk1ioAk'', ''1515053313'', 1),
(8, ''OPENTM408237350'', ''服务进度提醒'', ''{{first.DATA}}\n服务类型：{{keyword1.DATA}}\n服务状态：{{keyword2.DATA}}\n服务时间：{{keyword3.DATA}}\n{{remark.DATA}}'', ''6Q7lusUXhX7HKcevHlEvMDC2qMuF2Yxtq52VTFNoNwg'', ''1515483915'', 1),
(9, ''OPENTM204431262'', ''客服通知提醒'', ''{{first.DATA}}\n客户名称：{{keyword1.DATA}}\n客服类型：{{keyword2.DATA}}\n提醒内容：{{keyword3.DATA}}\n通知时间：{{keyword4.DATA}}\n{{remark.DATA}}'', ''6xvvsrYPGyTbYPPecVI1WihNpvWSAUsW1vBWGwL8Je0'', ''1515484354'', 1),
(10, ''OPENTM407456411'', ''拼团成功通知'', ''{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n团购商品：{{keyword2.DATA}}\n{{remark.DATA}}'', ''8EI_FJ-h1UbIBYSXEm5BnV345fQs9s1eiELwlMUnbhk'', ''1520063823'', 1),
(11, ''OPENTM401113750'', ''拼团失败通知'', ''{{first.DATA}}\n拼团商品：{{keyword1.DATA}}\n商品金额：{{keyword2.DATA}}\n退款金额：{{keyword3.DATA}}\n{{remark.DATA}}'', ''BdO4l8H2p7OK8_f7Cx8DOqpE3D-mjDvjNdMeS05u2lI'', ''1520064059'', 1),
(12, ''OPENTM205213550'', ''订单生成通知'', ''{{first.DATA}}\n时间：{{keyword1.DATA}}\n商品名称：{{keyword2.DATA}}\n订单号：{{keyword3.DATA}}\n{{remark.DATA}}'', ''EY3j42ql2S6TBz5yK14iVjZqh4OSDOParZ9F_6e-56Q'', ''1528966701'', 1),
(13, ''OPENTM207791277'', ''订单支付成功通知'', ''{{first.DATA}}\n订单编号：{{keyword1.DATA}}\n支付金额：{{keyword2.DATA}}\n{{remark.DATA}}'', ''UL7rLuzUIlYHNx5X_whUaAHWZWMmhZL35pUnJHgz8eI'', ''1528966759'', 1);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_user`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_user` (
  `uid` int(10) unsigned NOT NULL COMMENT ''微信用户id'',
  `unionid` varchar(30) DEFAULT NULL COMMENT ''只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段'',
  `openid` varchar(30) DEFAULT NULL COMMENT ''用户的标识，对当前公众号唯一'',
  `routine_openid` varchar(32) DEFAULT NULL COMMENT ''小程序唯一身份ID'',
  `nickname` varchar(64) NOT NULL COMMENT ''用户的昵称'',
  `headimgurl` varchar(256) NOT NULL COMMENT ''用户头像'',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT ''0'' COMMENT ''用户的性别，值为1时是男性，值为2时是女性，值为0时是未知'',
  `city` varchar(64) NOT NULL COMMENT ''用户所在城市'',
  `language` varchar(64) NOT NULL COMMENT ''用户的语言，简体中文为zh_CN'',
  `province` varchar(64) NOT NULL COMMENT ''用户所在省份'',
  `country` varchar(64) NOT NULL COMMENT ''用户所在国家'',
  `remark` varchar(256) DEFAULT NULL COMMENT ''公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注'',
  `groupid` smallint(5) unsigned DEFAULT ''0'' COMMENT ''用户所在的分组ID（兼容旧的用户分组接口）'',
  `tagid_list` varchar(256) DEFAULT NULL COMMENT ''用户被打上的标签ID列表'',
  `subscribe` tinyint(3) unsigned DEFAULT ''1'' COMMENT ''用户是否订阅该公众号标识'',
  `subscribe_time` int(10) unsigned DEFAULT NULL COMMENT ''关注公众号时间'',
  `add_time` int(10) unsigned DEFAULT NULL COMMENT ''添加时间'',
  `stair` int(11) unsigned DEFAULT NULL COMMENT ''一级推荐人'',
  `second` int(11) unsigned DEFAULT NULL COMMENT ''二级推荐人'',
  `order_stair` int(11) DEFAULT NULL COMMENT ''一级推荐人订单'',
  `order_second` int(11) unsigned DEFAULT NULL COMMENT ''二级推荐人订单'',
  `now_money` int(11) unsigned DEFAULT NULL COMMENT ''佣金'',
  `session_key` varchar(32) DEFAULT NULL COMMENT ''小程序用户会话密匙'',
  `user_type` varchar(32) DEFAULT ''wechat'' COMMENT ''用户类型''
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT=''微信用户表'';

--
-- 转存表中的数据 `eb_wechat_user`
--

INSERT INTO `eb_wechat_user` (`uid`, `unionid`, `openid`, `routine_openid`, `nickname`, `headimgurl`, `sex`, `city`, `language`, `province`, `country`, `remark`, `groupid`, `tagid_list`, `subscribe`, `subscribe_time`, `add_time`, `stair`, `second`, `order_stair`, `order_second`, `now_money`, `session_key`, `user_type`) VALUES
(1, '''', ''odbx_0X9rjARwCMUY6xqvJBMuWA8'', NULL, ''等风来，随风去'', ''http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLBaQPPnbg52bgibia1CZDruib1RwibHbBbnfxH1MUwbyz3G0Xub1LNX0ib5RFd7nZvo88gzHwib0OPibyfZQ/132'', 1, '''', ''zh_CN'', ''杜兰戈'', ''墨西哥'', '''', 0, '''', 1, 1528858386, 1528859304, NULL, NULL, NULL, NULL, NULL, NULL, ''wechat'');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eb_article`
--
ALTER TABLE `eb_article`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_article_category`
--
ALTER TABLE `eb_article_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_article_content`
--
ALTER TABLE `eb_article_content`
  ADD UNIQUE KEY `nid` (`nid`) USING BTREE;

--
-- Indexes for table `eb_cache`
--
ALTER TABLE `eb_cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `eb_express`
--
ALTER TABLE `eb_express`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`) USING BTREE,
  ADD KEY `is_show` (`is_show`) USING BTREE;

--
-- Indexes for table `eb_routine_access_token`
--
ALTER TABLE `eb_routine_access_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_routine_form_id`
--
ALTER TABLE `eb_routine_form_id`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_routine_template`
--
ALTER TABLE `eb_routine_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tempkey` (`tempkey`) USING BTREE;

--
-- Indexes for table `eb_store_bargain`
--
ALTER TABLE `eb_store_bargain`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_store_bargain_user`
--
ALTER TABLE `eb_store_bargain_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_store_bargain_user_help`
--
ALTER TABLE `eb_store_bargain_user_help`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_store_cart`
--
ALTER TABLE `eb_store_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`uid`) USING BTREE,
  ADD KEY `goods_id` (`product_id`) USING BTREE,
  ADD KEY `uid` (`uid`,`is_pay`) USING BTREE,
  ADD KEY `uid_2` (`uid`,`is_del`) USING BTREE,
  ADD KEY `uid_3` (`uid`,`is_new`) USING BTREE,
  ADD KEY `type` (`type`) USING BTREE;

--
-- Indexes for table `eb_store_category`
--
ALTER TABLE `eb_store_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`) USING BTREE,
  ADD KEY `is_base` (`is_show`) USING BTREE,
  ADD KEY `sort` (`sort`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE;

--
-- Indexes for table `eb_store_combination`
--
ALTER TABLE `eb_store_combination`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_store_combination_attr`
--
ALTER TABLE `eb_store_combination_attr`
  ADD KEY `store_id` (`product_id`) USING BTREE;

--
-- Indexes for table `eb_store_combination_attr_result`
--
ALTER TABLE `eb_store_combination_attr_result`
  ADD UNIQUE KEY `product_id` (`product_id`) USING BTREE;

--
-- Indexes for table `eb_store_combination_attr_value`
--
ALTER TABLE `eb_store_combination_attr_value`
  ADD UNIQUE KEY `unique` (`unique`,`suk`) USING BTREE,
  ADD KEY `store_id` (`product_id`,`suk`) USING BTREE;

--
-- Indexes for table `eb_store_coupon`
--
ALTER TABLE `eb_store_coupon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state` (`status`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `coupon_time` (`coupon_time`) USING BTREE,
  ADD KEY `is_del` (`is_del`) USING BTREE;

--
-- Indexes for table `eb_store_coupon_issue`
--
ALTER TABLE `eb_store_coupon_issue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cid` (`cid`) USING BTREE,
  ADD KEY `start_time` (`start_time`,`end_time`) USING BTREE,
  ADD KEY `remain_count` (`remain_count`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE,
  ADD KEY `is_del` (`is_del`) USING BTREE;

--
-- Indexes for table `eb_store_coupon_issue_user`
--
ALTER TABLE `eb_store_coupon_issue_user`
  ADD UNIQUE KEY `uid` (`uid`,`issue_coupon_id`) USING BTREE;

--
-- Indexes for table `eb_store_coupon_user`
--
ALTER TABLE `eb_store_coupon_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cid` (`cid`) USING BTREE,
  ADD KEY `uid` (`uid`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `end_time` (`end_time`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE,
  ADD KEY `is_fail` (`is_fail`) USING BTREE;

--
-- Indexes for table `eb_store_order`
--
ALTER TABLE `eb_store_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id_2` (`order_id`,`uid`) USING BTREE,
  ADD UNIQUE KEY `unique` (`unique`) USING BTREE,
  ADD KEY `uid` (`uid`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `pay_price` (`pay_price`) USING BTREE,
  ADD KEY `paid` (`paid`) USING BTREE,
  ADD KEY `pay_time` (`pay_time`) USING BTREE,
  ADD KEY `pay_type` (`pay_type`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE,
  ADD KEY `is_del` (`is_del`) USING BTREE,
  ADD KEY `coupon_id` (`coupon_id`) USING BTREE;

--
-- Indexes for table `eb_store_order_cart_info`
--
ALTER TABLE `eb_store_order_cart_info`
  ADD UNIQUE KEY `oid` (`oid`,`unique`) USING BTREE,
  ADD KEY `cart_id` (`cart_id`) USING BTREE,
  ADD KEY `product_id` (`product_id`) USING BTREE;

--
-- Indexes for table `eb_store_order_status`
--
ALTER TABLE `eb_store_order_status`
  ADD KEY `oid` (`oid`) USING BTREE,
  ADD KEY `change_type` (`change_type`) USING BTREE;

--
-- Indexes for table `eb_store_pink`
--
ALTER TABLE `eb_store_pink`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_store_product`
--
ALTER TABLE `eb_store_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cate_id` (`cate_id`) USING BTREE,
  ADD KEY `is_hot` (`is_hot`) USING BTREE,
  ADD KEY `is_benefit` (`is_benefit`) USING BTREE,
  ADD KEY `is_best` (`is_best`) USING BTREE,
  ADD KEY `is_new` (`is_new`) USING BTREE,
  ADD KEY `toggle_on_sale, is_del` (`is_del`) USING BTREE,
  ADD KEY `price` (`price`) USING BTREE,
  ADD KEY `is_show` (`is_show`) USING BTREE,
  ADD KEY `sort` (`sort`) USING BTREE,
  ADD KEY `sales` (`sales`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `is_postage` (`is_postage`) USING BTREE;

--
-- Indexes for table `eb_store_product_attr`
--
ALTER TABLE `eb_store_product_attr`
  ADD KEY `store_id` (`product_id`) USING BTREE;

--
-- Indexes for table `eb_store_product_attr_result`
--
ALTER TABLE `eb_store_product_attr_result`
  ADD UNIQUE KEY `product_id` (`product_id`) USING BTREE;

--
-- Indexes for table `eb_store_product_attr_value`
--
ALTER TABLE `eb_store_product_attr_value`
  ADD UNIQUE KEY `unique` (`unique`,`suk`) USING BTREE,
  ADD KEY `store_id` (`product_id`,`suk`) USING BTREE;

--
-- Indexes for table `eb_store_product_relation`
--
ALTER TABLE `eb_store_product_relation`
  ADD UNIQUE KEY `uid` (`uid`,`product_id`,`type`) USING BTREE,
  ADD KEY `type` (`type`) USING BTREE,
  ADD KEY `category` (`category`) USING BTREE;

--
-- Indexes for table `eb_store_product_reply`
--
ALTER TABLE `eb_store_product_reply`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `order_id_2` (`oid`,`unique`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `parent_id` (`reply_type`) USING BTREE,
  ADD KEY `is_del` (`is_del`) USING BTREE,
  ADD KEY `product_score` (`product_score`) USING BTREE,
  ADD KEY `service_score` (`service_score`) USING BTREE;

--
-- Indexes for table `eb_store_seckill`
--
ALTER TABLE `eb_store_seckill`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `start_time` (`start_time`,`stop_time`),
  ADD KEY `is_del` (`is_del`),
  ADD KEY `is_hot` (`is_hot`),
  ADD KEY `is_show` (`status`),
  ADD KEY `add_time` (`add_time`),
  ADD KEY `sort` (`sort`),
  ADD KEY `is_postage` (`is_postage`);

--
-- Indexes for table `eb_store_seckill_attr`
--
ALTER TABLE `eb_store_seckill_attr`
  ADD KEY `store_id` (`product_id`) USING BTREE;

--
-- Indexes for table `eb_store_seckill_attr_result`
--
ALTER TABLE `eb_store_seckill_attr_result`
  ADD UNIQUE KEY `product_id` (`product_id`) USING BTREE;

--
-- Indexes for table `eb_store_seckill_attr_value`
--
ALTER TABLE `eb_store_seckill_attr_value`
  ADD UNIQUE KEY `unique` (`unique`,`suk`) USING BTREE,
  ADD KEY `store_id` (`product_id`,`suk`) USING BTREE;

--
-- Indexes for table `eb_store_service`
--
ALTER TABLE `eb_store_service`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_store_service_log`
--
ALTER TABLE `eb_store_service_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_store_visit`
--
ALTER TABLE `eb_store_visit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_system_admin`
--
ALTER TABLE `eb_system_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account` (`account`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE;

--
-- Indexes for table `eb_system_attachment`
--
ALTER TABLE `eb_system_attachment`
  ADD PRIMARY KEY (`att_id`);

--
-- Indexes for table `eb_system_attachment_category`
--
ALTER TABLE `eb_system_attachment_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `eb_system_config`
--
ALTER TABLE `eb_system_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_system_config_tab`
--
ALTER TABLE `eb_system_config_tab`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_system_file`
--
ALTER TABLE `eb_system_file`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_system_group`
--
ALTER TABLE `eb_system_group`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_name` (`config_name`) USING BTREE;

--
-- Indexes for table `eb_system_group_data`
--
ALTER TABLE `eb_system_group_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_system_log`
--
ALTER TABLE `eb_system_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `type` (`type`) USING BTREE;

--
-- Indexes for table `eb_system_menus`
--
ALTER TABLE `eb_system_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`) USING BTREE,
  ADD KEY `is_show` (`is_show`) USING BTREE,
  ADD KEY `access` (`access`) USING BTREE;

--
-- Indexes for table `eb_system_notice`
--
ALTER TABLE `eb_system_notice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE;

--
-- Indexes for table `eb_system_notice_admin`
--
ALTER TABLE `eb_system_notice_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`,`notice_type`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `is_visit` (`is_visit`) USING BTREE,
  ADD KEY `is_click` (`is_click`) USING BTREE;

--
-- Indexes for table `eb_system_role`
--
ALTER TABLE `eb_system_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`) USING BTREE;

--
-- Indexes for table `eb_user`
--
ALTER TABLE `eb_user`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `account` (`account`) USING BTREE,
  ADD KEY `spreaduid` (`spread_uid`) USING BTREE,
  ADD KEY `level` (`level`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE,
  ADD KEY `is_promoter` (`is_promoter`) USING BTREE;

--
-- Indexes for table `eb_user_address`
--
ALTER TABLE `eb_user_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`) USING BTREE,
  ADD KEY `is_default` (`is_default`) USING BTREE,
  ADD KEY `is_del` (`is_del`) USING BTREE;

--
-- Indexes for table `eb_user_bill`
--
ALTER TABLE `eb_user_bill`
  ADD PRIMARY KEY (`id`),
  ADD KEY `openid` (`uid`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `pm` (`pm`) USING BTREE,
  ADD KEY `type` (`category`,`type`,`link_id`) USING BTREE;

--
-- Indexes for table `eb_user_enter`
--
ALTER TABLE `eb_user_enter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE,
  ADD KEY `province` (`province`,`city`,`district`) USING BTREE,
  ADD KEY `is_lock` (`is_lock`) USING BTREE,
  ADD KEY `is_del` (`is_del`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE;

--
-- Indexes for table `eb_user_extract`
--
ALTER TABLE `eb_user_extract`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extract_type` (`extract_type`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `openid` (`uid`) USING BTREE,
  ADD KEY `fail_time` (`fail_time`);

--
-- Indexes for table `eb_user_group`
--
ALTER TABLE `eb_user_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_user_notice`
--
ALTER TABLE `eb_user_notice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_user_notice_see`
--
ALTER TABLE `eb_user_notice_see`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_user_recharge`
--
ALTER TABLE `eb_user_recharge`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  ADD KEY `uid` (`uid`) USING BTREE,
  ADD KEY `recharge_type` (`recharge_type`) USING BTREE,
  ADD KEY `paid` (`paid`) USING BTREE;

--
-- Indexes for table `eb_wechat_media`
--
ALTER TABLE `eb_wechat_media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`,`media_id`) USING BTREE,
  ADD KEY `type_2` (`type`) USING BTREE;

--
-- Indexes for table `eb_wechat_message`
--
ALTER TABLE `eb_wechat_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `openid` (`openid`) USING BTREE,
  ADD KEY `type` (`type`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE;

--
-- Indexes for table `eb_wechat_news_category`
--
ALTER TABLE `eb_wechat_news_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eb_wechat_qrcode`
--
ALTER TABLE `eb_wechat_qrcode`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `third_type` (`third_type`,`third_id`) USING BTREE,
  ADD KEY `ticket` (`ticket`) USING BTREE;

--
-- Indexes for table `eb_wechat_reply`
--
ALTER TABLE `eb_wechat_reply`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`) USING BTREE,
  ADD KEY `type` (`type`) USING BTREE,
  ADD KEY `status` (`status`) USING BTREE,
  ADD KEY `hide` (`hide`) USING BTREE;

--
-- Indexes for table `eb_wechat_template`
--
ALTER TABLE `eb_wechat_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tempkey` (`tempkey`) USING BTREE;

--
-- Indexes for table `eb_wechat_user`
--
ALTER TABLE `eb_wechat_user`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `openid` (`openid`) USING BTREE,
  ADD KEY `groupid` (`groupid`) USING BTREE,
  ADD KEY `subscribe_time` (`subscribe_time`) USING BTREE,
  ADD KEY `add_time` (`add_time`) USING BTREE,
  ADD KEY `subscribe` (`subscribe`) USING BTREE,
  ADD KEY `unionid` (`unionid`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eb_article`
--
ALTER TABLE `eb_article`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''文章管理ID'';
--
-- AUTO_INCREMENT for table `eb_article_category`
--
ALTER TABLE `eb_article_category`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''文章分类id'';
--
-- AUTO_INCREMENT for table `eb_express`
--
ALTER TABLE `eb_express`
  MODIFY `id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''快递公司id'',AUTO_INCREMENT=426;
--
-- AUTO_INCREMENT for table `eb_routine_access_token`
--
ALTER TABLE `eb_routine_access_token`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''小程序access_token表ID'',AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `eb_routine_form_id`
--
ALTER TABLE `eb_routine_form_id`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''表单ID表ID'',AUTO_INCREMENT=138;
--
-- AUTO_INCREMENT for table `eb_routine_template`
--
ALTER TABLE `eb_routine_template`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''模板id'',AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `eb_store_bargain`
--
ALTER TABLE `eb_store_bargain`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''砍价产品ID'';
--
-- AUTO_INCREMENT for table `eb_store_bargain_user`
--
ALTER TABLE `eb_store_bargain_user`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''用户参与砍价表ID'';
--
-- AUTO_INCREMENT for table `eb_store_bargain_user_help`
--
ALTER TABLE `eb_store_bargain_user_help`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''砍价用户帮助表ID'';
--
-- AUTO_INCREMENT for table `eb_store_cart`
--
ALTER TABLE `eb_store_cart`
  MODIFY `id` bigint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT ''购物车表ID'';
--
-- AUTO_INCREMENT for table `eb_store_category`
--
ALTER TABLE `eb_store_category`
  MODIFY `id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT ''商品分类表ID'';
--
-- AUTO_INCREMENT for table `eb_store_combination`
--
ALTER TABLE `eb_store_combination`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eb_store_coupon`
--
ALTER TABLE `eb_store_coupon`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''优惠券表ID'';
--
-- AUTO_INCREMENT for table `eb_store_coupon_issue`
--
ALTER TABLE `eb_store_coupon_issue`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eb_store_coupon_user`
--
ALTER TABLE `eb_store_coupon_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT ''优惠券发放记录id'';
--
-- AUTO_INCREMENT for table `eb_store_order`
--
ALTER TABLE `eb_store_order`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ''订单ID'';
--
-- AUTO_INCREMENT for table `eb_store_pink`
--
ALTER TABLE `eb_store_pink`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eb_store_product`
--
ALTER TABLE `eb_store_product`
  MODIFY `id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT ''商品id'',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `eb_store_product_reply`
--
ALTER TABLE `eb_store_product_reply`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT ''评论ID'';
--
-- AUTO_INCREMENT for table `eb_store_seckill`
--
ALTER TABLE `eb_store_seckill`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''商品秒杀产品表id'';
--
-- AUTO_INCREMENT for table `eb_store_service`
--
ALTER TABLE `eb_store_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT ''客服id'',AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `eb_store_service_log`
--
ALTER TABLE `eb_store_service_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT ''客服用户对话记录表ID'',AUTO_INCREMENT=46;
--
-- AUTO_INCREMENT for table `eb_store_visit`
--
ALTER TABLE `eb_store_visit`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `eb_system_admin`
--
ALTER TABLE `eb_system_admin`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT ''后台管理员表ID'',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `eb_system_attachment`
--
ALTER TABLE `eb_system_attachment`
  MODIFY `att_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `eb_system_attachment_category`
--
ALTER TABLE `eb_system_attachment_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT for table `eb_system_config`
--
ALTER TABLE `eb_system_config`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''配置id'',AUTO_INCREMENT=96;
--
-- AUTO_INCREMENT for table `eb_system_config_tab`
--
ALTER TABLE `eb_system_config_tab`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''配置分类id'',AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `eb_system_file`
--
ALTER TABLE `eb_system_file`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''文件对比ID'',AUTO_INCREMENT=1711;
--
-- AUTO_INCREMENT for table `eb_system_group`
--
ALTER TABLE `eb_system_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT ''组合数据ID'',AUTO_INCREMENT=49;
--
-- AUTO_INCREMENT for table `eb_system_group_data`
--
ALTER TABLE `eb_system_group_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT ''组合数据详情ID'',AUTO_INCREMENT=108;
--
-- AUTO_INCREMENT for table `eb_system_log`
--
ALTER TABLE `eb_system_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''管理员操作记录ID'';
--
-- AUTO_INCREMENT for table `eb_system_menus`
--
ALTER TABLE `eb_system_menus`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT ''菜单ID'',AUTO_INCREMENT=377;
--
-- AUTO_INCREMENT for table `eb_system_notice`
--
ALTER TABLE `eb_system_notice`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''通知模板id'',AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `eb_system_notice_admin`
--
ALTER TABLE `eb_system_notice_admin`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''通知记录ID'',AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `eb_system_role`
--
ALTER TABLE `eb_system_role`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''身份管理id'',AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `eb_user`
--
ALTER TABLE `eb_user`
  MODIFY `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''用户id'',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `eb_user_address`
--
ALTER TABLE `eb_user_address`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT ''用户地址id'';
--
-- AUTO_INCREMENT for table `eb_user_bill`
--
ALTER TABLE `eb_user_bill`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''用户账单id'';
--
-- AUTO_INCREMENT for table `eb_user_enter`
--
ALTER TABLE `eb_user_enter`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''商户申请ID'';
--
-- AUTO_INCREMENT for table `eb_user_extract`
--
ALTER TABLE `eb_user_extract`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eb_user_group`
--
ALTER TABLE `eb_user_group`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eb_user_notice`
--
ALTER TABLE `eb_user_notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eb_user_notice_see`
--
ALTER TABLE `eb_user_notice_see`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `eb_user_recharge`
--
ALTER TABLE `eb_user_recharge`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eb_wechat_media`
--
ALTER TABLE `eb_wechat_media`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''微信视频音频id'',AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `eb_wechat_message`
--
ALTER TABLE `eb_wechat_message`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''用户行为记录id'';
--
-- AUTO_INCREMENT for table `eb_wechat_news_category`
--
ALTER TABLE `eb_wechat_news_category`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''图文消息管理ID'';
--
-- AUTO_INCREMENT for table `eb_wechat_qrcode`
--
ALTER TABLE `eb_wechat_qrcode`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''微信二维码ID'',AUTO_INCREMENT=126;
--
-- AUTO_INCREMENT for table `eb_wechat_reply`
--
ALTER TABLE `eb_wechat_reply`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT ''微信关键字回复id'',AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `eb_wechat_template`
--
ALTER TABLE `eb_wechat_template`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''模板id'',AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `eb_wechat_user`
--
ALTER TABLE `eb_wechat_user`
  MODIFY `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT ''微信用户id'',AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
