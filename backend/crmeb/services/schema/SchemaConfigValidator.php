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

use crmeb\exceptions\AdminException;

/**
 * 页面配置校验器
 *
 * 对插件返回的页面配置做结构校验与规范化：
 * - 页面类型必须在封闭集合内（非法直接抛异常）
 * - 表格列类型/搜索字段类型/行操作模式超出白名单时降级（text/input，不中断渲染）
 * - 缺失关键结构（columns）时抛异常，帮助插件开发者尽早发现配置错误
 */
class SchemaConfigValidator
{
    /**
     * 支持的页面类型
     * @var array<string>
     */
    public const PAGE_TYPES = ['list', 'form', 'stat', 'detail'];

    /**
     * 支持的表格列类型（未知类型降级为 text）
     * @var array<string>
     */
    public const COLUMN_TYPES = ['text', 'number', 'amount', 'datetime', 'date', 'tag', 'image', 'switch', 'link', 'ellipsis'];

    /**
     * 支持的搜索字段类型（未知类型降级为 input）
     * @var array<string>
     */
    public const SEARCH_TYPES = ['input', 'number', 'select', 'date', 'daterange'];

    /**
     * 支持的行操作模式（未知模式直接剔除）
     * @var array<string>
     */
    public const ACTION_MODES = ['drawer_form', 'drawer_detail', 'confirm_action', 'direct_action', 'navigate'];

    /**
     * 支持的批量操作模式（批量场景仅允许确认与直接执行）
     * @var array<string>
     */
    public const BATCH_ACTION_MODES = ['confirm_action', 'direct_action'];

    /**
     * 支持的统计卡片值类型（未知类型降级为 text）
     * @var array<string>
     */
    public const CARD_TYPES = ['text', 'number', 'amount'];

    /**
     * 支持的行操作显示条件运算符（visible 条件声明，未知运算符整条剔除）
     * @var array<string>
     */
    public const VISIBLE_OPS = ['eq', 'neq', 'gt', 'lt', 'gte', 'lte', 'empty', 'not_empty'];

    /**
     * 校验并规范化页面配置
     *
     * @param array $config 插件返回的原始配置
     * @return array 规范化后的配置
     * @throws AdminException 页面类型非法或列表页缺少列定义
     */
    public static function validate(array $config): array
    {
        $type = (string)($config['type'] ?? '');
        if (!in_array($type, self::PAGE_TYPES, true)) {
            throw new AdminException('页面配置 type 非法，仅支持: ' . implode('/', self::PAGE_TYPES));
        }

        if ($type === 'list') {
            $config = self::validateList($config);
        } elseif ($type === 'detail') {
            $config = self::validateDetail($config);
        }

        return $config;
    }

    /**
     * 校验详情页配置（单例只读内容页：提示条 + 内容字段 + 页面级动作）
     *
     * @param array $config 原始配置
     * @return array 规范化后的配置
     */
    protected static function validateDetail(array $config): array
    {
        // tip：顶部信息提示条文案（可选，非字符串时丢弃）
        if (isset($config['tip']) && !is_string($config['tip'])) {
            $config['tip'] = '';
        }
        // actions：页面级动作（模式白名单与行操作同规则）
        $config['actions'] = self::normalizeActions($config['actions'] ?? []);
        return $config;
    }

    /**
     * 动作数组规范化（模式白名单校验，非法项剔除）
     *
     * @param mixed $actions 动作声明列表
     * @return array
     */
    protected static function normalizeActions($actions): array
    {
        $normalized = [];
        if (!empty($actions) && is_array($actions)) {
            foreach ($actions as $action) {
                if (!is_array($action) || empty($action['action']) || !isset($action['label'])) {
                    continue;
                }
                $mode = (string)($action['mode'] ?? 'direct_action');
                if (!in_array($mode, self::ACTION_MODES, true)) {
                    continue;
                }
                $action['mode'] = $mode;
                // 行级显示条件：[{field, value, op}] 全部满足才显示（结构非法整条剔除）
                if (isset($action['visible'])) {
                    $action['visible'] = self::normalizeVisible($action['visible']);
                    if (empty($action['visible'])) {
                        unset($action['visible']);
                    }
                }
                $normalized[] = $action;
            }
        }
        return $normalized;
    }

    /**
     * 行操作显示条件规范化
     *
     * @param mixed $visible 条件声明列表
     * @return array
     */
    protected static function normalizeVisible($visible): array
    {
        $normalized = [];
        if (!empty($visible) && is_array($visible)) {
            foreach ($visible as $condition) {
                if (!is_array($condition) || !isset($condition['field'])) {
                    continue;
                }
                $op = (string)($condition['op'] ?? 'eq');
                if (!in_array($op, self::VISIBLE_OPS, true)) {
                    continue;
                }
                $condition['op'] = $op;
                $normalized[] = $condition;
            }
        }
        return $normalized;
    }

    /**
     * 校验列表页配置
     *
     * @param array $config 原始配置
     * @return array 规范化后的配置
     * @throws AdminException 缺少列定义
     */
    protected static function validateList(array $config): array
    {
        if (empty($config['columns']) || !is_array($config['columns'])) {
            throw new AdminException('列表页配置缺少 columns 列定义');
        }

        // 列类型白名单校验，未知类型降级为 text；switch 列未声明动作时规范化为通用 status 动作
        foreach ($config['columns'] as $index => $column) {
            if (!is_array($column) || empty($column['prop']) || !isset($column['label'])) {
                throw new AdminException('列表页 columns[' . $index . '] 缺少 prop 或 label');
            }
            $columnType = (string)($column['type'] ?? 'text');
            $config['columns'][$index]['type'] = in_array($columnType, self::COLUMN_TYPES, true) ? $columnType : 'text';
            if ($columnType === 'switch' && empty($config['columns'][$index]['action'])) {
                $config['columns'][$index]['action'] = 'status';
            }
        }

        // 搜索字段类型白名单校验，未知类型降级为 input
        if (!empty($config['search']) && is_array($config['search'])) {
            foreach ($config['search'] as $index => $field) {
                if (!is_array($field) || empty($field['field']) || !isset($field['label'])) {
                    throw new AdminException('列表页 search[' . $index . '] 缺少 field 或 label');
                }
                $fieldType = (string)($field['type'] ?? 'input');
                $config['search'][$index]['type'] = in_array($fieldType, self::SEARCH_TYPES, true) ? $fieldType : 'input';
            }
        } else {
            $config['search'] = [];
        }

        // 行操作模式白名单校验，未知模式剔除（与页面级动作同规则）
        $config['row_actions'] = self::normalizeActions($config['row_actions'] ?? []);

        // 批量操作：结构走行操作规则，模式仅允许 confirm_action/direct_action（其余剔除）
        $config['batch_actions'] = self::normalizeBatchActions($config['batch_actions'] ?? []);

        // 统计卡片行：field+label 必填（缺失剔除），值类型白名单降级
        $config['cards'] = self::normalizeCards($config['cards'] ?? []);

        // 状态分类头：field 与 options 必填（结构不完整时整体剔除），with_count 规范化为布尔
        $config['tabs'] = self::normalizeTabs($config['tabs'] ?? []);

        return $config;
    }

    /**
     * 统计卡片数组规范化
     *
     * 数据由 list() 响应的 summary 键按 field 下发；缺 field/label 的项剔除，
     * type 超出白名单时降级为 text。
     *
     * @param mixed $cards 卡片声明列表
     * @return array
     */
    protected static function normalizeCards($cards): array
    {
        $normalized = [];
        if (!empty($cards) && is_array($cards)) {
            foreach ($cards as $card) {
                if (!is_array($card) || empty($card['field']) || !isset($card['label'])) {
                    continue;
                }
                $cardType = (string)($card['type'] ?? 'text');
                $card['type'] = in_array($cardType, self::CARD_TYPES, true) ? $cardType : 'text';
                $normalized[] = $card;
            }
        }
        return $normalized;
    }

    /**
     * 状态分类头规范化
     *
     * 选中项的 value 以 where[field] 提交；with_count 为 true 时计数由 list() 响应
     * 的 tabs_count 键下发（键为选项 value 的字符串形式）。
     *
     * @param mixed $tabs 分类头声明
     * @return array
     */
    protected static function normalizeTabs($tabs): array
    {
        if (!empty($tabs) && is_array($tabs) && !empty($tabs['field']) && !empty($tabs['options']) && is_array($tabs['options'])) {
            $tabs['with_count'] = !empty($tabs['with_count']);
            return $tabs;
        }
        return [];
    }

    /**
     * 批量操作数组规范化（结构同行操作，模式限定在批量白名单内）
     *
     * @param mixed $batchActions 批量操作声明列表
     * @return array
     */
    protected static function normalizeBatchActions($batchActions): array
    {
        return array_values(array_filter(
            self::normalizeActions($batchActions),
            fn(array $action): bool => in_array($action['mode'], self::BATCH_ACTION_MODES, true)
        ));
    }
}
