<?php
declare(strict_types=1);

namespace modules\aiagent\model;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * AI Agent 会话模型（eb_ai_agent_conversation）
 */
class AiAgentConversation extends BaseModel
{
    use ModelTrait;

    /** @var string 主键 */
    protected $pk = 'id';

    /** @var string 表名（不含前缀） */
    protected $name = 'ai_agent_conversation';
}
