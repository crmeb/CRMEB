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

namespace app\dao\activity\lottery;

use app\dao\BaseDao;
use app\model\activity\lottery\LuckPrize;

/**
 *
 * Class LuckPrizeDao
 * @package app\dao\activity\lottery
 */
class LuckPrizeDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return LuckPrize::class;
    }

    /**
     * 获取某个活动所有奖品
     * @param int $lottery_id
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPrizeList(int $lottery_id, string $field = '*')
    {
        $where = ['is_del' => 0, 'status' => 1];
        return $this->search($where + ['lottery_id' => $lottery_id])->field($field)->order('sort desc,id desc')->select()->toArray();
    }
}
