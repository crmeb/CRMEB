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

use app\dao\driver\express\ExpressDumpLogDao;
use crmeb\basic\BaseService;

/**
 * 电子面单打印记录服务
 * Class ExpressDumpLogServices
 * @package app\services\driver\express
 */
class ExpressDumpLogServices extends BaseService
{
    /**
     * @var ExpressDumpLogDao
     */
    protected $dao;

    /**
     * ExpressDumpLogServices constructor.
     * @param ExpressDumpLogDao $dao
     */
    public function __construct(ExpressDumpLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取打印记录列表
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
     * 记录打印日志
     * @param array $data
     * @return int
     */
    public function record(array $data)
    {
        $data['add_time'] = time();
        $res = $this->dao->save($data);
        return $res->id ?? 0;
    }

    /**
     * 获取记录详情
     * @param int $id
     * @return array|\think\Model|null
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getById(int $id)
    {
        return $this->dao->get($id);
    }
}
