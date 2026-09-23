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

namespace app\services\user\member;

use app\dao\user\MemberRightDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * 会员权益服务类
 * 负责会员权益的增删改查及状态管理
 */
class MemberRightServices extends BaseService
{
    /**
     * @var MemberRightDao
     */
    protected $dao;

    /**
     * 初始化DAO层实例
     * MemberRightServices constructor.
     * @param MemberRightDao $memberRightDao
     */
    public function __construct(MemberRightDao $memberRightDao)
    {
        $this->dao = $memberRightDao;
    }

    /**
     * 获取会员权益列表
     * 支持分页查询，返回权益列表及数量
     * @param array $where 查询条件
     * @return array 返回分页后的权益列表数据
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
     * 保存会员权益
     * 编辑会员权益信息，包含权益类型、名称、图标等验证
     * @param int $id 权益ID
     * @param array $data 权益数据
     * @throws AdminException 参数验证失败时抛出异常
     */
    public function save(int $id, array $data)
    {
        if (!$data['right_type']) throw new AdminException('会员权益类型缺失');
        if (!$data['title'] || !$data['show_title']) throw new AdminException('请设置权益名称');
        if (!$data['image']) throw new AdminException('请上传会员权益图标');
        if (mb_strlen($data['show_title']) > 6) throw new AdminException('展示名称不能大于6个字');
        if (mb_strlen($data['explain']) > 8) throw new AdminException('权益简介不能大于8个字');
        //根据权益类型进行不同的参数验证
        switch ($data['right_type']) {
            case "integral":
                if (!$data['number']) throw new AdminException('请设置返还积分倍数');
                if ($data['number'] < 0) throw new AdminException('返还积分倍数不能为负数');
                $save['number'] = abs($data['number']);
                break;
            case "express" :
                if (!$data['number']) throw new AdminException('请设置运费折扣');
                if ($data['number'] < 0) throw new AdminException('运费折扣不能为负数');
                $save['number'] = abs($data['number']);
                break;
            case "sign" :
                if (!$data['number']) throw new AdminException('请设置签到积分倍数');
                if ($data['number'] < 0) throw new AdminException('签到积分倍数不能为负数');
                $save['number'] = abs($data['number']);
                break;
            case "offline" :
                if (!$data['number']) throw new AdminException('请设置线下付款折扣');
                if ($data['number'] < 0) throw new AdminException('线下付款不能为负数');
                $save['number'] = abs($data['number']);
        }
        $save['show_title'] = $data['show_title'];
        $save['image'] = $data['image'];
        $save['status'] = $data['status'];
        $save['sort'] = $data['sort'];
        if ($id) {
            return $this->dao->update($id, $save);
        } else {
            $save['title'] = $data['title'];
            $save['right_type'] = $data['right_type'];
            $save['explain'] = $data['explain'];
            return $this->dao->save($save);
        }
    }

    /**
     * 获取单条权益信息
     * 根据条件查询单个会员权益详情
     * @param array $where 查询条件
     * @return array|bool|\think\Model|null 返回权益数据或false
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
     * 根据权益类型查询其状态是否为启用
     * @param string $rightType 权益类型
     * @return bool 返回权益是否开启
     */
    public function getMemberRightStatus($rightType)
    {
        if (!$rightType) return false;
        $status = $this->dao->value(['right_type' => $rightType], 'status');
        if ($status) return true;
        return false;
    }

}