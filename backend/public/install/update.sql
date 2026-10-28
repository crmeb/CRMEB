-- ============================================================
-- CRMEB 数据库增量更新脚本
-- 用途：已安装环境升级到当前版本时执行；全新安装直接导入
--       crmeb_table.sql，无需本文件。
-- 注意：MySQL 8 不支持 ADD COLUMN IF NOT EXISTS，请勿重复执行。
-- ============================================================

-- 2026-10-09 订单中台删除标记拆分：is_del=用户删除（用户端隐藏），
-- is_system_del=后台删除（后台列表隐藏）；后台列表与统计改按
-- is_system_del 过滤，用户删除的订单后台仍可见，后台删除以用户已删为前提。
ALTER TABLE `eb_order_center`
    ADD COLUMN `is_system_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '后台删除标记（后台列表隐藏）' AFTER `is_del`,
    MODIFY COLUMN `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户删除标记（用户端隐藏）';

-- 2026-10-24 用户注册设置新增「邀新海报背景图」配置项（invite_poster_bg），
-- 供分享邀新海报作为底图使用；按 menu_name 判重，重复执行不会插入多条。
INSERT INTO `eb_system_config`
    (`id`, `menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`, `level`, `link_id`, `link_value`)
SELECT 520, 'invite_poster_bg', 'upload', 'input', 105, '', 1, '', 0, 0, '""', '邀新海报背景图', '分享邀新海报的底图，建议尺寸 750*1334（9:16）；未配置时回退「组合数据-个人中心分销海报」，两者都没有则邀新页无海报', 99, 1, 0, 0, 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `eb_system_config` WHERE `menu_name` = 'invite_poster_bg');

-- 2026-10-28 下线佣金提现后清理残留菜单权限（父级 36/39 已不存在，仅剩孤儿按钮/接口权限）
DELETE FROM `eb_system_menus`
WHERE `id` IN (2965, 2966, 2967, 2968, 2969, 2970)
   OR `unique_auth` IN (
        'extract-status',
        'extract-edit',
        'finance-extract-refuse',
        'finance-extract-adopt',
        'finance-extract',
        'finance-extract-edit'
   )
   OR (`api_url` LIKE 'finance/extract%' AND `api_url` <> '');
