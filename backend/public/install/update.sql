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
