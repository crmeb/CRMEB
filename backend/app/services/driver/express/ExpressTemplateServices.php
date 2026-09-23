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

namespace app\services\driver\express;

use app\dao\driver\express\ExpressTemplateDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\logistics\Express;

/**
 * Class ExpressTemplateServices
 * @package app\services\driver\express
 */
class ExpressTemplateServices extends BaseService
{
    /**
     * @var ExpressTemplateDao
     */
    protected $dao;

    /**
     * ExpressTemplateServices constructor.
     * @param ExpressTemplateDao $dao
     */
    public function __construct(ExpressTemplateDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, ['*'], $page, $limit, 'id desc');
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 保存面单模板数据
     * @param array $data
     * @return bool|\think\Model
     */
    public function saveExpress(array $data)
    {
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException('保存失败');
        return $res;
    }

    /**
     * 获取单个模板信息
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDetail(int $id)
    {
        $del = $this->dao->get($id);
        if (!$del) throw new AdminException('数据不存在');
        return $del->toArray();
    }

    /**
     * 修改电子面单模板信息
     * @param int $id
     * @param array $data
     * @return \think\Model
     */
    public function editExpress(int $id, array $data)
    {
        $model = $this->dao->get($id);
        if (!$model) throw new AdminException('数据不存在');

        foreach ($data as $key => $value) {
            $model->$key = $value;
        }
        $model->save();

        return $model;
    }

    /**
     * 删除面单模板
     * @param int $id
     * @return bool
     */
    public function delExpress(int $id)
    {
        $res = $this->dao->delete($id);
        if (!$res) throw new AdminException('删除失败');
        return true;
    }

    /**
     * 修改状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function setStatus(int $id, int $status)
    {
        $res = $this->dao->update($id, ['status' => $status]);
        if (!$res) throw new AdminException('修改失败');
        return true;
    }

    /**
     * 获取所有可用的面单模板列表
     * @param string $com
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExpressList(string $com = '')
    {
        $where = ['status' => 1];
        if ($com) $where['com'] = $com;
        return $this->dao->getList($where);
    }

    /**
     * 从一号通同步指定快递公司的模板
     * 一号通 temp 接口经 httpRequest 剥离外层后返回 {data: [...模板列表...]}，模板名字段为 title
     * @param Express $express
     * @param string $com
     * @param string $comName
     * @return array
     */
    public function syncTemplates(Express $express, string $com, string $comName): array
    {
        $result = $express->temp($com);
        // 兼容包装层结构 {data: [...]} 与直接返回列表两种形态
        $list = $result['data'] ?? $result;
        if (!is_array($list)) {
            return [];
        }
        $saved = [];
        foreach ($list as $item) {
            if (!is_array($item) || empty($item['temp_id'])) {
                continue;
            }
            $data = [
                'com' => $com,
                'com_name' => $comName,
                'temp_id' => $item['temp_id'],
                'temp_name' => $item['title'] ?? ($item['temp_name'] ?? ''),
                'status' => 1,
            ];
            $exist = $this->dao->getOne(['com' => $com, 'temp_id' => $data['temp_id']]);
            if ($exist) {
                $this->dao->update($exist['id'], $data);
            } else {
                $this->dao->save($data);
            }
            $saved[] = $data;
        }
        return $saved;
    }
}
