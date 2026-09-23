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
namespace app\services\diy;

use app\dao\diy\ThemeModuleDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * 主题组件服务类
 */
class ThemeModuleServices extends BaseService
{
    /**
     * @var ThemeModuleDao
     */
    protected $dao;

    /**
     * 构造方法
     * @param ThemeModuleDao $dao
     */
    public function __construct(ThemeModuleDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取组件列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getModuleList(array $where)
    {
        $list = $this->dao->themeModuleList($where, '*', 1, 100, 'id desc');
        $count = $this->dao->themeModuleCount($where);
        return compact('list', 'count');
    }

    /**
     * 获取组件详情
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getModuleInfo(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('数据不存在');
        }
        $info = $info->toArray();
        $info['data'] = $info['data'] ? json_decode($info['data'], true) : [];
        return $info;
    }

    /**
     * 保存组件数据（新增/编辑）
     * @param int $id
     * @param array $data
     * @return int
     */
    public function saveModule(int $id, array $data)
    {
        $save = [];
        if (isset($data['type'])) {
            $save['type'] = $data['type'];
        }
        if (isset($data['data'])) {
            $value = is_string($data['data']) ? $data['data'] : json_encode($data['data'], JSON_UNESCAPED_UNICODE);
            $save['data'] = $value;
        }

        if (!$save) {
            throw new AdminException('保存数据不能为空');
        }

        if ($id) {
            $this->dao->update($id, $save);
        } else {
            $id = $this->dao->insertGetId($save);
        }

        return $id;
    }

    /**
     * 删除组件
     * @param int $id
     * @return bool
     */
    public function deleteModule(int $id)
    {
        if (!$this->dao->get($id)) {
            throw new AdminException('数据不存在');
        }
        return (bool)$this->dao->delete($id);
    }
}
