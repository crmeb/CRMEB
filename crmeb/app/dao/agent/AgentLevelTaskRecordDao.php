<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\dao\agent;

use app\dao\BaseDao;
use app\model\agent\AgentLevelTaskRecord;

/**
 * Class AgentLevelTaskRecordDao
 * @package app\dao\agent
 */
class AgentLevelTaskRecordDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return AgentLevelTaskRecord::class;
    }

    /**
     * 获取所有的分销员等级
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where = [], string $field = '*')
    {
        return $this->search($where + ['is_del' => 0, 'status' => 1])->field($field)->order('sort desc,id desc')->select()->toArray();
    }
}
