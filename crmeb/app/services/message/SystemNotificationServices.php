<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\message;

use app\dao\system\SystemNotificationDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * 消息管理类
 * Class SystemNotificationServices
 * @package app\services\system
 * @method value($where, $value) 条件获取某个字段的值
 */
class SystemNotificationServices extends BaseServices
{

    /**
     * SystemNotificationServices constructor.
     * @param SystemNotificationDao $dao
     */
    public function __construct(SystemNotificationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 单个配置
     * @param int $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOneNotce(array $where)
    {
        return $this->dao->getOne($where);
    }

    /**
     * 后台获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNotList(array $where)
    {
        return $this->dao->getList($where);
    }

    /**
     * 获取单条数据
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNotInfo(array $where)
    {
        $type = $where['type'];
        unset($where['type']);
        $info = $this->dao->getOne($where);
        if (!$info) return [];
        $info = $info->toArray();
        switch ($type) {
            case 'is_system':
                $info['content'] = $info['system_text'] ?? '';
                break;
            case 'is_sms':
                $info['content'] = $info['sms_text'];
                break;
            case 'is_wechat':
                $info['tempkey'] = $info['wechat_tempkey'] ?? '';
                $info['tempid'] = $info['wechat_tempid'] ?? '';
                $info['content'] = $info['wechat_content'] ?? '';
                break;
            case 'is_routine':
                $info['tempkey'] = $info['routine_tempkey'] ?? '';
                $info['tempid'] = $info['routine_tempid'] ?? '';
                $info['content'] = $info['routine_content'] ?? '';
                break;
        }
        return $info;
    }

    /**
     * 保存数据
     * @param array $data
     * @return bool|\crmeb\basic\BaseModel|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveData(array $data)
    {
        $type = $data['type'];
        $id = $data['id'];
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException(100026);
        }
        $res = null;
        switch ($type) {
            case 'is_system':
                $update = [];
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_system'] = $data['is_system'];
                $update['is_app'] = $data['is_app'];
                $update['system_title'] = $data['system_title'];
                $update['system_text'] = $data['system_text'];
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_sms':
                $update = [];
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_sms'] = $data['is_sms'];
                $update['sms_id'] = $data['sms_id'];
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_wechat':
                $update['is_wechat'] = $data['is_wechat'];
                $update['wechat_tempid'] = $data['tempid'];
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_routine':
                $update['is_routine'] = $data['is_routine'];
                $update['routine_tempid'] = $data['tempid'];
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_ent_wechat':
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_ent_wechat'] = $data['is_ent_wechat'];
                $update['ent_wechat_text'] = $data['ent_wechat_text'];
                $update['url'] = $data['url'];
                $res = $this->dao->update((int)$id, $update);
                break;
        }
        return $res;
    }

    /**
     * 获取tempid
     * @param $type
     * @return array
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/16
     */
    public function getTempId($type)
    {
        return $this->dao->getTempId($type);
    }

    /**
     * 获取tempkey
     * @param $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/16
     */
    public function getTempKey($type)
    {
        return $this->dao->getTempKey($type);
    }
}
