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

namespace app\model\ai;

use crmeb\basic\BaseModel;

/**
 * AI 模型登记模型（系统内置 AI 能力的模型清单）
 *
 * 每条记录 = 一个可调用的大模型，protocol 字段决定由哪个内置协议适配器
 * （crmeb\services\ai\AiProtocol）发起请求，同协议族内新增厂商只需加记录，零代码。
 */
class AiModel extends BaseModel
{
    /**
     * 表名（不含 eb_ 前缀）
     * @var string
     */
    protected $name = 'ai_model';

    /**
     * 主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', (int) $value);
        }
    }

    /**
     * 协议族搜索器
     * @param $query
     * @param $value
     */
    public function searchProtocolAttr($query, $value)
    {
        if ($value) {
            $query->where('protocol', (string) $value);
        }
    }

    /**
     * 关键字搜索器（名称/模型标识/厂商）
     * @param $query
     * @param $value
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value) {
            $keyword = (string) $value;
            $query->where(function ($q) use ($keyword) {
                $q->whereOr('name', 'like', '%' . $keyword . '%')
                    ->whereOr('model', 'like', '%' . $keyword . '%')
                    ->whereOr('provider', 'like', '%' . $keyword . '%');
            });
        }
    }

    /**
     * 是否支持工具调用搜索器
     * @param $query
     * @param $value
     */
    public function searchSupportsToolsAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('supports_tools', (int) $value);
        }
    }
}
