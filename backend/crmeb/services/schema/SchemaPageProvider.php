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
declare(strict_types=1);

namespace crmeb\services\schema;

/**
 * 插件管理页面提供者契约
 *
 * 插件通过实现本接口，用纯 PHP 声明后台管理页面（页面结构 + 数据 + 动作），
 * 由管理端通用页面容器（SchemaPage）按配置自动渲染，插件无需编写任何前端代码。
 *
 * 页面访问地址约定：/admin/plugin/{插件目录名}/{pageKey}
 * 网关接口约定：/adminapi/schema/plugin/{插件目录名}/{pageKey}/{端点}
 *
 * 使用方式：在模块目录下创建 schema/{Xxx}Page.php，实现本接口，
 * 注册中心（SchemaPageRegistry）会按约定自动扫描注册，无需手动注册。
 */
interface SchemaPageProvider
{
    /**
     * 页面标识（同插件内唯一，如 list/stats/setting）
     *
     * 静态方法：注册中心扫描阶段免实例化即可取得标识，完成注册
     *
     * @return string
     */
    public static function pageKey(): string;

    /**
     * 页面结构配置（type/title/search/columns/actions/row_actions 等）
     *
     * 配置只描述页面结构，不含业务数据；网关下发前会经过 SchemaConfigValidator 校验
     *
     * @return array
     */
    public function config(): array;

    /**
     * 列表数据
     *
     * @param array $where 搜索条件（来自搜索栏提交，键值对）
     * @param int $page 页码
     * @param int $limit 每页条数
     * @param array $order 排序（如 ['add_time' => 'desc']），空时使用插件默认排序
     * @return array ['list' => array 行数据, 'count' => int 总条数]
     *               行数据必须携带唯一 id：前端行动作（详情/表单/动作）固定取 row.id 回查，
     *               数据库记录用主键，内存拼装的列表用稳定唯一标识（排序/筛选不得改变 id 语义）
     */
    public function list(array $where, int $page, int $limit, array $order = []): array;

    /**
     * 表单规则（新增/编辑抽屉表单，复用 create_form() 标准结构）
     *
     * @param int $id 0=新增；>0=编辑（回填数据）
     * @return array {rules: array, title: string, action: string, method: string}
     *               action 为空时前端默认提交到网关 save 端点
     */
    public function form(int $id = 0): array;

    /**
     * 保存表单（新增/编辑统一入口）
     *
     * @param int $id 0=新增；>0=更新
     * @param array $data 表单数据（已剥离 id）
     * @return void
     */
    public function save(int $id, array $data): void;

    /**
     * 详情数据（详情抽屉用）
     *
     * @param int $id 数据主键
     * @return array {fields: [{label, value, type?, map?, theme?}, ...]}
     */
    public function detail(int $id): array;

    /**
     * 统计数据（统计页用）
     *
     * @return array {cards: {指标key: 值}, charts: {图表key: {x: [], series: [{name, data}]}}}
     */
    public function stats(): array;

    /**
     * 通用动作（删除/启停/导出等非表单操作）
     *
     * @param string $action 动作标识（如 delete/status）
     * @param array $payload 动作参数（如 ['id' => 1, 'value' => 1]）
     * @return array 响应指令：{msg: string, refresh?: bool, redirect?: string, download?: string}
     *               默认行为（仅 msg）：前端提示后自动刷新列表
     */
    public function action(string $action, array $payload): array;
}
