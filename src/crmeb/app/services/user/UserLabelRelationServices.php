<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserLabelRelationDao;
use crmeb\exceptions\AdminException;

/**
 *
 * Class UserLabelRelationServices
 * @package app\services\user
 * @method getColumn(array $where, string $field, string $key = '') 获取某个字段数组
 * @method saveAll(array $data) 批量保存数据
 */
class UserLabelRelationServices extends BaseServices
{

    /**
     * UserLabelRelationServices constructor.
     * @param UserLabelRelationDao $dao
     */
    public function __construct(UserLabelRelationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某个用户标签ids
     * @param int $uid
     * @return array
     */
    public function getUserLabels(int $uid)
    {
        return $this->dao->getColumn(['uid' => $uid], 'label_id', '');
    }

    /**
     * 用户设置标签
     * @param int $uid
     * @param array $labels
     */
    public function setUserLable($uids, array $labels)
    {
        if (!count($labels)) {
            return true;
        }
        if (!is_array($uids)) {
            $uids = [$uids];
        }
        $re = $this->dao->delete([
            ['uid', 'in', $uids],
            ['label_id', 'in', $labels],
        ]);
        if ($re === false) {
            throw new AdminException('清空用户标签失败');
        }
        $data = [];
        foreach ($uids as $uid) {
            foreach ($labels as $label) {
                $data[] = ['uid' => $uid, 'label_id' => $label];
            }
        }
        if ($data) {
            if (!$this->dao->saveAll($data))
                throw new AdminException('设置标签失败');
        }
        return true;
    }

    /**
     * 取消用户标签
     * @param int $uid
     * @param array $labels
     * @return mixed
     */
    public function unUserLabel(int $uid, array $labels)
    {
        if (!count($labels)) {
            return true;
        }
        $this->dao->delete([
            ['uid', '=', $uid],
            ['label_id', 'in', $labels],
        ]);
        return true;
    }

    /**
     * 获取用户标签
     * @param array $uids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserLabelList(array $uids)
    {
        return $this->dao->getLabelList($uids);
    }
}
