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

use app\services\user\UserLabelCateServices;
use app\services\user\UserLabelServices;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：用户标签列表
 *
 * 标签字典查询：返回全部用户标签及所属分类，供 crmeb_user_list 按标签筛选时取标签 ID。
 */
class UserLabelListTool implements McpToolInterface
{
    /**
     * 用户标签服务
     * @var UserLabelServices
     */
    private UserLabelServices $services;

    /**
     * 标签分类服务
     * @var UserLabelCateServices
     */
    private UserLabelCateServices $cateServices;

    /**
     * 构造函数
     * @param UserLabelServices $services 用户标签服务
     * @param UserLabelCateServices $cateServices 标签分类服务
     */
    public function __construct(UserLabelServices $services, UserLabelCateServices $cateServices)
    {
        $this->services = $services;
        $this->cateServices = $cateServices;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_label_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询全部用户标签字典，返回标签 ID、标签名称、所属分类 ID 与分类名，并附全部标签分类列表（cates 字段）；'
            . 'label_cate 可选，按标签分类 ID 筛选标签，0 或不传返回全部分类下的标签；'
            . '不分页全量返回，不包含各标签下的打标用户数；'
            . '标签 ID 用于 crmeb_user_list 的 label_id 参数按标签筛选用户。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '标签列表';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '用户';
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
                'label_cate' => ['type' => 'integer', 'minimum' => 0, 'description' => '标签分类 ID，按分类筛选标签；0 或不传返回全部'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（label_cate）
     * @return array 标签列表、分类列表与总数
     */
    public function execute(array $args): array
    {
        $where = [];
        $cateId = (int)($args['label_cate'] ?? 0);
        if ($cateId > 0) {
            $where['label_cate'] = $cateId;
        }

        $list = $this->services->getLabelList($where, ['id', 'label_name', 'label_cate']);

        $cates = [];
        foreach ($this->cateServices->getLabelCateAll() as $cate) {
            $cates[] = ['id' => (int)($cate['id'] ?? 0), 'name' => (string)($cate['name'] ?? '')];
        }

        return ['list' => $list, 'count' => count($list), 'cates' => $cates];
    }
}
