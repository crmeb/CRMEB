<?php

namespace app\services\product\product;

use app\dao\product\product\StoreProductParamDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * 商品参数
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2024/12/17
 */
class StoreProductParamServices extends BaseServices
{
    /**
     * 设置dao层
     * @param StoreProductParamDao $dao
     */
    public function __construct(StoreProductParamDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 商品参数列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getParamList($where, 'id,name,sort,add_time,status', $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        $count = $this->dao->getParamCount($where);
        return compact('list', 'count');
    }

    /**
     * 商品参数详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamInfo($id)
    {
        $info = $this->dao->get(['id' => $id]);
        if (!$info) throw new AdminException('数据不存在');
        $info = $info->toArray();
        $info['value'] = json_decode($info['value'], true);
        return $info;
    }

    /**
     * 获取商品参数值
     * @param $id
     * @return mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamValue($id)
    {
        $value = $this->dao->value(['id' => $id], 'value');
        return json_decode($value, true);
    }

    /**
     * 商品参数保存
     * @param $id
     * @param $data
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function saveParamData($id, $data)
    {
        $data['value'] = json_encode($data['value']);
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $this->dao->save($data);
        }
        return true;
    }

    /**
     * 商品参数状态修改
     * @param $id
     * @param $status
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function setParamStatus($id, $status)
    {
        $this->dao->update($id, ['status' => $status]);
        return true;
    }

    /**
     * 商品参数删除
     * @param $id
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function delParamData($id)
    {
        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }
}
