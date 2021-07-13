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

namespace app\dao\system\config;

use app\dao\BaseDao;
use app\model\system\config\SystemConfig;

/**
 * 系统配置
 * Class SystemConfigDao
 * @package app\dao\system\config
 */
class SystemConfigDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemConfig::class;
    }

    /**
     * 获取某个系统配置
     * @param string $configNmae
     * @return mixed
     */
    public function getConfigValue(string $configNmae)
    {
        return $this->withSearchSelect(['menu_name'], ['menu_name' => $configNmae])->value('value');
    }

    /**
     * 获取所有配置
     * @return array
     */
    public function getConfigAll(array $configName = [])
    {
        if ($configName) {
            return $this->withSearchSelect(['menu_name'], ['menu_name' => $configName])->column('value', 'menu_name');
        } else {
            return $this->getModel()->column('value', 'menu_name');
        }
    }

    /**
     * 获取配置列表分页
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigList(array $where, int $page, int $limit)
    {
        return $this->search($where)->page($page, $limit)->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * 获取某些分类配置下的配置列表
     * @param int $tabId
     * @param int $status
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigTabAllList(int $tabId, int $status = 1)
    {
        $where['tab_id'] = $tabId;
        if ($status == 1) $where['status'] = $status;
        return $this->search($where)->order('sort desc')->select()->toArray();
    }

    /**
     * 获取上传配置中的上传类型
     * @param string $configName
     * @return array
     */
    public function getUploadTypeList(string $configName)
    {
        return $this->search(['menu_name' => $configName])->column('upload_type', 'type');
    }
}
