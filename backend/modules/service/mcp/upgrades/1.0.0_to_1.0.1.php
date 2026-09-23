<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
/**
 * MCP 服务升级脚本：1.0.0 -> 1.0.1
 *
 * 1. 移除写入密钥功能：删除 mcp_write_key 配置行（写工具不再要求
 *    X-Mcp-Write-Key 独立授权，页面「重置写入密钥」按钮一并下线）。
 * 2. 服务总开关 mcp_enabled 改为安装默认「开启」：为「MCP服务」配置分组
 *    补建总开关并统一置为开启，站点开箱即可通过 /api/mcp 接入；如需关闭，
 *    在「系统配置 → MCP服务」中切换（与 Installer::seed() 同口径，全部幂等）。
 *
 * 注意：本脚本在升级包暂存目录中被加载执行，不得引用新版 Installer 类。
 */

use think\facade\Db;

return function () {
    $prefix = (string) config('database.connections.mysql.prefix', 'eb_');
    $configTable = $prefix . 'system_config';
    $tabTable = $prefix . 'system_config_tab';
    $count = 0;

    // 1. 退役配置项：mcp_write_key（写入密钥）
    $count += (int) Db::table($configTable)
        ->where('menu_name', 'mcp_write_key')
        ->delete();

    // 2. 「MCP服务」配置分组（eng_title=mcp，幂等）
    $tab = Db::table($tabTable)->where('eng_title', 'mcp')->find();
    if (!$tab) {
        $tabId = (int) Db::table($tabTable)->insertGetId([
            'pid'       => 0,
            'title'     => 'MCP服务',
            'eng_title' => 'mcp',
            'status'    => 1,
            'info'      => 0,
            'icon'      => '',
            'type'      => 3,
            'sort'      => 0,
            'menus_id'  => 0,
        ]);
        $count++;
    } else {
        $tabId = (int) $tab['id'];
    }

    // 3. 服务总开关：缺失则补建，已存在则统一置为「开启」（默认开启语义）
    $switch = Db::table($configTable)->where('menu_name', 'mcp_enabled')->find();
    if (!$switch) {
        Db::table($configTable)->insert([
            'menu_name'     => 'mcp_enabled',
            'config_tab_id' => $tabId,
            'type'          => 'radio',
            'input_type'    => 'radio',
            'parameter'     => "1=>开启\n0=>关闭",
            'value'         => '1',
            'info'          => '是否开启MCP服务',
            'desc'          => '开启后 AI 客户端可通过 MCP 协议访问本系统',
            'upload_type'   => 1,
            'required'      => '',
            'width'         => 0,
            'high'          => 0,
            'status'        => 1,
            'sort'          => 0,
            'level'         => 0,
            'link_id'       => 0,
            'link_value'    => 0,
        ]);
        $count++;
    } elseif ((string)(json_decode((string) $switch['value'], true) ?: '') !== '1') {
        Db::table($configTable)
            ->where('menu_name', 'mcp_enabled')
            ->update(['value' => '1']);
        $count++;
    }

    return $count;
};
