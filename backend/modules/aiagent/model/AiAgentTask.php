<?php
declare(strict_types=1);

namespace modules\aiagent\model;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * AI 自动化任务模型（eb_ai_agent_task）
 */
class AiAgentTask extends BaseModel
{
    use ModelTrait;

    /** @var string 主键 */
    protected $pk = 'id';

    /** @var string 表名（不含前缀） */
    protected $name = 'ai_agent_task';
}
