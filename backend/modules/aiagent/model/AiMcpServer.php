<?php
declare(strict_types=1);

namespace modules\aiagent\model;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 远程 MCP Server 登记模型（eb_ai_mcp_server）
 */
class AiMcpServer extends BaseModel
{
    use ModelTrait;

    /** @var string 主键 */
    protected $pk = 'id';

    /** @var string 表名（不含前缀） */
    protected $name = 'ai_mcp_server';
}
