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

namespace app\services\user\member;

use app\dao\user\MemberRightDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * Class MemberRightServices
 * @package app\services\user
 */
class MemberRightServices extends BaseServices
{
    /**
     * MemberCardServices constructor.
     * @param MemberRightDao $memberCardDao
     */
    public function __construct(MemberRightDao $memberRightDao)
    {
        $this->dao = $memberRightDao;
    }

    /**
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSearchList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getSearchList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['image'] = set_file_url($item['image']);
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');

    }

    /**
     * 编辑保存
     * @param int $id
     * @param array $data
     */
    public function save(int $id, array $data)
    {
        if (!$data['right_type']) throw new AdminException(400630);
        if (!$id) throw new AdminException(100100);
        if (!$data['title'] || !$data['show_title']) throw new AdminException(400631);
        if (!$data['image']) throw new AdminException(400632);
        if (mb_strlen($data['show_title']) > 6) throw new AdminException(400755);
        if (mb_strlen($data['explain']) > 8) throw new AdminException(400752);
        switch ($data['right_type']) {
            case "integral":
                if (!$data['number']) throw new AdminException(400633);
                if ($data['number'] < 0) throw new AdminException(400634);
                $save['number'] = abs($data['number']);
                break;
            case "express" :
                if (!$data['number']) throw new AdminException(400635);
                if ($data['number'] < 0) throw new AdminException(400636);
                $save['number'] = abs($data['number']);
                break;
            case "sign" :
                if (!$data['number']) throw new AdminException(400637);
                if ($data['number'] < 0) throw new AdminException(400638);
                $save['number'] = abs($data['number']);
                break;
            case "offline" :
                if (!$data['number']) throw new AdminException(400639);
                if ($data['number'] < 0) throw new AdminException(400640);
                $save['number'] = abs($data['number']);
        }
        $save['show_title'] = $data['show_title'];
        $save['image'] = $data['image'];
        $save['status'] = $data['status'];
        $save['sort'] = $data['sort'];
        //TODO $save没有使用
        return $this->dao->update($id, $data);
    }

    /**
     * 获取单条信息
     * @param array $where
     * @return array|bool|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne(array $where)
    {
        if (!$where) return false;
        return $this->dao->getOne($where);
    }

    /**
     * 查看某权益是否开启
     * @param $rightType
     * @return bool
     */
    public function getMemberRightStatus($rightType)
    {
        if (!$rightType) return false;
        $status = $this->dao->value(['right_type' => $rightType], 'status');
        if ($status) return true;
        return false;
    }

}
