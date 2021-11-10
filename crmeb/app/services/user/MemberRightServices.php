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

namespace app\services\user;

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
     * @param MemberCardDao $memberCardDao
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
        foreach ($list as &$item){
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
        if (!$data['right_type']) throw new AdminException("会员权益类型缺失");
        if (!$id) throw new AdminException("id参数缺失");
        if (!$data['title'] || !$data['show_title']) throw new AdminException("请设置权益名称");
        if (!$data['image']) throw new AdminException("请上传会员权益图标");
        switch ($data['right_type']) {
            case "integral":
                if (!$data['number']) throw new AdminException("请设置返还积分倍数");
                if ($data['number'] < 0) throw new AdminException("返还积分倍数不能为负数");
                $save['number'] = abs($data['number']);
                break;
            case "express" :
                if (!$data['number']) throw new AdminException("请设置运费折扣");
                if ($data['number'] < 0) throw new AdminException("运费折扣不能为负数");
                $save['number'] = abs($data['number']);
                break;
            case "sign" :
                if (!$data['number']) throw new AdminException("请设置签到积分倍数");
                if ($data['number'] < 0) throw new AdminException("签到积分倍数不能为负数");
                $save['number'] = abs($data['number']);
                break;
            case "offline" :
                if (!$data['number']) throw new AdminException("请设置线下付款折扣");
                if ($data['number'] < 0) throw new AdminException("线下付款不能为负数");
                $save['number'] = abs($data['number']);
        }
        $save['show_title'] = $data['show_title'];
        $save['image'] = $data['image'];
        $save['status'] = $data['status'];
        $save['sort'] = $data['sort'];
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
