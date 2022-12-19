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

namespace app\dao\activity\bargain;

use app\dao\BaseDao;
use app\model\activity\bargain\StoreBargainUserHelp;

/**
 *
 * Class StoreBargainUserHelpDao
 * @package app\dao\activity
 */
class StoreBargainUserHelpDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreBargainUserHelp::class;
    }

    /**
     * 获取帮砍人数
     * @return array
     */
    public function getHelpAllCount(array $where = [])
    {
        return $this->getModel()->where($where)->group('bargain_id')->column('count(*)', 'bargain_id');
    }

    /**
     * 获取帮砍列表
     * @param int $bid
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getHelpList(int $bid, int $page = 0, int $limit = 0)
    {
        return $this->getModel()
            ->where('bargain_user_id', $bid)
            ->order('add_time desc')
            ->when($page, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->column("uid,price,from_unixtime(add_time,'%Y-%m-%d %H:%i:%s') as add_time", 'id');
    }

    /**
     * 获取砍价商品已砍人数
     * @return array
     */
    public function getNums()
    {
        return $this->getModel()->field('count(id) as num,bargain_user_id')->group('bargain_user_id')->select()->toArray();
    }
}
