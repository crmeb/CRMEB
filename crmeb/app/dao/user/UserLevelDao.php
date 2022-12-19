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

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\UserLevel;

/**
 *
 * Class UserLevelDao
 * @package app\dao\user
 */
class UserLevelDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserLevel::class;
    }

    /**
     * 根据uid 获取用户会员等级详细信息
     * @param int $uid
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserLevel(int $uid, string $field = '*')
    {
        return $this->getModel()->where('uid', $uid)->where('is_del', 0)->where('status', 1)->field($field)->with(['levelInfo'])->order('grade desc,add_time desc')->find();
    }

    /**
     * 获取用户等级折扣
     * @param int $uid
     * @return mixed
     */
    public function getDiscount(int $uid)
    {
        $level = $this->getModel()->where(['uid' => $uid, 'is_del' => 0, 'status' => 1])->with(['levelInfo' => function ($query) {
            $query->field('id,discount')->bind(['discount_num' => 'discount']);
        }])->order('id desc')->find();
        return $level ? $level->toArray()['discount_num'] : NULL;
    }
}
