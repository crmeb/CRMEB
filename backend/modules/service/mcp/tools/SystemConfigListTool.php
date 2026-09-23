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

namespace modules\service\mcp\tools;

use app\dao\system\config\SystemConfigDao;
use app\services\system\config\SystemConfigTabServices;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：系统配置列表
 *
 * 查询系统全部设置项，返回每个配置项的变量名（menu_name）、
 * 配置名称、当前值、类型及所属分组，支持关键词搜索与分页。
 */
class SystemConfigListTool implements McpToolInterface
{
    /**
     * 系统配置 DAO
     * @var SystemConfigDao
     */
    private SystemConfigDao $dao;

    /**
     * 构造函数
     * @param SystemConfigDao $dao 系统配置 DAO
     */
    public function __construct(SystemConfigDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_config_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询系统设置列表，返回配置变量名、名称、当前值、类型和所属分组；凭证、密钥及 Webhook 类值只显示是否已配置，不返回明文；选项类型返回允许的选项映射。修改前先确认变量名和类型';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '系统配置查询';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => '搜索关键词，匹配配置名称/配置变量名'],
                'tab_id'  => ['type' => 'number', 'description' => '按配置分组ID筛选（来源为本工具返回的 tab_id）'],
                'page'    => ['type' => 'number', 'description' => '页码，默认1'],
                'limit'   => ['type' => 'number', 'description' => '每页数量，默认20，最大100'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（keyword/tab_id/page/limit）
     * @return array 配置列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 20)));

        $where = [];
        // 关键词走模型搜索器（匹配配置名称/变量名），转义通配符防止注入
        if (!empty($args['keyword'])) {
            $where['config_name'] = addcslashes((string)$args['keyword'], '%_');
        }
        if (!empty($args['tab_id'])) {
            $where['tab_id'] = (int)$args['tab_id'];
        }

        $list = $this->dao->search($where)
            ->field('id,menu_name,info,desc,value,type,input_type,upload_type,parameter,config_tab_id,status')
            ->order('sort desc,id asc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->dao->search($where)->count();

        $tabNames = app()->make(SystemConfigTabServices::class)->getColumn([], 'title', 'id');
        foreach ($list as &$item) {
            // 数据库存的是 json_encode 后的字符串，还原失败时保留原值
            $decoded = json_decode((string)$item['value'], true);
            $item['value'] = SensitiveData::mask(
                $decoded === null ? $item['value'] : $decoded,
                (string)$item['menu_name'],
                (string)($item['input_type'] ?? '')
            );
            $item['tab_id'] = (int)$item['config_tab_id'];
            $item['tab_name'] = (string)($tabNames[$item['config_tab_id']] ?? '');
            unset($item['config_tab_id']);
            // 选项类配置解析 parameter 为可选项映射（选项值=>选项名），供修改前准确取值
            if (!SensitiveData::isCredential((string)$item['menu_name']) && in_array($item['type'], ['radio', 'checkbox', 'select'], true)) {
                $item['options'] = $this->parseOptions((string)($item['parameter'] ?? ''));
            }
            // 仅保留对当前类型有意义的子类型字段，减少无关输出
            if ($item['type'] !== 'text') {
                unset($item['input_type']);
            }
            if ($item['type'] !== 'upload') {
                unset($item['upload_type']);
            }
            unset($item['parameter']);
        }

        return ['list' => $list, 'count' => $count, 'page' => $page, 'limit' => $limit];
    }

    /**
     * 解析配置参数为可选项映射（parameter 存储格式：每行「选项值=>选项名」）
     * @param string $parameter 原始参数文本
     * @return array<int, array{value:string,label:string}>
     */
    private function parseOptions(string $parameter): array
    {
        $options = [];
        foreach (explode("\n", str_replace("\r\n", "\n", $parameter)) as $line) {
            $parts = explode('=>', $line, 2);
            if (count($parts) === 2 && trim($parts[0]) !== '') {
                $options[] = ['value' => trim($parts[0]), 'label' => trim($parts[1])];
            }
        }
        return $options;
    }
}
