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

use app\dao\user\MemberShipDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * 会员类型服务类
 * 负责会员类型的增删改查及状态管理
 */
class MemberShipServices extends BaseService
{
    /**
     * @var MemberShipDao
     */
    protected $dao;

    /**
     * 初始化DAO层实例
     * MemberShipServices constructor.
     * @param MemberShipDao $memberShipDao
     */
    public function __construct(MemberShipDao $memberShipDao)
    {
        $this->dao = $memberShipDao;
    }

    /**
     * 获取会员类型列表（后台管理）
     * 支持分页查询，用于会员类型管理页面展示
     * @param array $where 查询条件
     * @return array 返回分页后的会员类型列表数据
     */
    public function getSearchList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getSearchList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');

    }

    /**
     * 获取会员类型列表（API接口）
     * 用于前端API调用，查询会员类型数据
     * @param array $where 查询条件
     * @return mixed 返回会员类型列表
     */
    public function getApiList(array $where)
    {
        return $this->dao->getApiList($where);
    }

    /**
     * 保存会员类型
     * 添加或编辑会员类型信息，包含名称、价格、有效期等验证
     * @param int $id 会员类型ID，0表示新增
     * @param array $data 会员类型数据
     * @throws AdminException 参数验证失败时抛出异常
     */
    public function save(int $id, array $data)
    {
        if (!$data['title']) throw new AdminException('请填写会员卡名称');
        if (!$data['type']) throw new AdminException('会员卡类型缺失');
        //永久会员有效期设为-1
        if ($data['type'] == "ever") {
            $data['vip_day'] = -1;
        } else {
            if (!$data['vip_day']) throw new AdminException('请填写有效期（天）');
            if ($data['vip_day'] < 0) throw new AdminException('有效期（天）不能为负数');
        }
        //免费会员价格设为0
        if ($data['type'] == "free") {
            $data['pre_price'] = 0.00;
        } else {
            if ($data['pre_price'] == 0 || $data['price'] == 0) throw new AdminException('请填写价格');
        }
        if ($data['pre_price'] < 0 || $data['price'] < 0) throw new AdminException('价格不能为负数');
        if ($data['pre_price'] > $data['price']) throw new AdminException('优惠价不能大于原价');
        if ($id){
            return $this->dao->update($id, $data);
        }else{
            return $this->dao->save($data);
        }

    }

    /**
     * 获取会员类型有效期天数
     * 根据条件查询会员类型的有效期天数
     * @param array $where 查询条件
     * @return mixed 返回有效期天数
     */
    public function getVipDay(array $where)
    {
        return $this->dao->value($where, 'vip_day');
    }

    /**
     * 修改会员类型状态
     * 更新会员类型的启用/禁用状态
     * @param int $id 会员类型ID
     * @param int $is_del 状态值，0=启用，1=禁用
     * @return bool 返回修改结果
     */
    public function setStatus($id, $is_del)
    {
        $res = $this->dao->update($id, ['is_del' => $is_del]);
        if ($res) return true;
        return false;
    }
}