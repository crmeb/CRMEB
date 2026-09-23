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

namespace modules\service\mcp\model;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * MCP 服务账号模型
 *
 * 每个账号持有独立访问密钥与工具白名单：仅能调用 tools 字段内勾选的工具，
 * 与全局密钥（system_config 的 mcp_appkey，全量权限）并存。
 */
class McpAccount extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称（表名不含前缀）
     * @var string
     */
    protected $name = 'service_mcp_account';

    /**
     * JSON 字段：授权工具名列表（读写自动编解码，jsonAssoc 保证解码为数组）
     * @var array
     */
    protected $json = ['tools'];

    /**
     * JSON 字段解码为关联数组（避免 stdClass）
     * @var bool
     */
    protected $jsonAssoc = true;

    /**
     * 关键词搜索器（备注模糊匹配）
     * @param mixed $query 查询对象
     * @param mixed $value 关键词
     * @return void
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value !== '') {
            $query->whereLike('remark', '%' . $value . '%');
        }
    }
}
