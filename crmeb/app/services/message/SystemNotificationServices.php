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
declare (strict_types=1);

namespace app\services\message;

use app\dao\system\SystemNotificationDao;
use app\services\BaseServices;
use app\services\serve\ServeServices;
use crmeb\services\CacheService;
use crmeb\services\template\Template;
use think\facade\Cache;
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
        /** @var ServeServices $ServeServices */
        $ServeServices = app()->make(ServeServices::class);
        /** @var TemplateMessageServices $TemplateMessageServices */
        $TemplateMessageServices = app()->make(TemplateMessageServices::class);

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
                $wechat = $TemplateMessageServices->getOne(['id' => $info['wechat_id'], 'type' => 1]);
                $info['templage_message_id'] = $wechat['id'] ?? '';
                $info['tempkey'] = $wechat['tempkey'] ?? '';
                $info['tempid'] = $wechat['tempid'] ?? '';
                $info['content'] = $wechat['content'] ?? '';
                break;
            case 'is_routine':
                $wechat = $TemplateMessageServices->getOne(['id' => $info['routine_id'], 'type' => 0]);
                $info['templage_message_id'] = $wechat['id'] ?? '';
                $info['tempkey'] = $wechat['tempkey'] ?? '';
                $info['tempid'] = $wechat['tempid'] ?? '';
                $info['content'] = $wechat['content'] ?? '';
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
        /** @var TemplateMessageServices $TemplateMessageServices */
        $TemplateMessageServices = app()->make(TemplateMessageServices::class);
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
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_wechat'] = $data['is_wechat'];
                $res1 = $this->dao->update((int)$id, $update);
                $res2 = $TemplateMessageServices->update(['notification_id' => $id, 'type' => 1], ['tempid' => $data['tempid']]);
                $res = $res1 && $res2;
                break;
            case 'is_routine':
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_routine'] = $data['is_routine'];
                $res1 = $this->dao->update((int)$id, $update);
                $res2 = $TemplateMessageServices->update(['notification_id' => $id, 'type' => 0], ['tempid' => $data['tempid']]);
                $res = $res1 && $res2;
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
}
