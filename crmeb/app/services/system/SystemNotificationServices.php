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
declare (strict_types = 1);

namespace app\services\system;

use app\dao\system\SystemNotificationDao;
use app\services\BaseServices;
use app\services\other\TemplateMessageServices;
use app\services\serve\ServeServices;
use crmeb\basic\BaseManager;
use crmeb\services\CacheService;
use crmeb\services\template\Template;
use think\facade\Cache;
use crmeb\exceptions\AdminException;

/**
 *
 * Class SystemNotificationServices
 * @package app\services\system
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
     * @param $where
     */
    public function getNotList(array $where)
    {
        $industry = CacheService::get('wechat_industry', function () {
            try {
                $cache = (new Template('wechat'))->getIndustry();
                if ($cache['primary_industry']['first_class'] != 'IT科技' || $cache['primary_industry']['second_class'] != '互联网|电子商务' || $cache['secondary_industry']['first_class'] != 'IT科技' || $cache['secondary_industry']['second_class'] != 'IT软件与服务') {
                    (new Template('wechat'))->setIndustry(1, 2);
                }
                return $cache->toArray();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }, 0) ?: [];
        !is_array($industry) && $industry = [];
        $industry['primary_industry'] = isset($industry['primary_industry']) ? $industry['primary_industry']['first_class'] . ' | ' . $industry['primary_industry']['second_class'] : '未选择';
        $industry['secondary_industry'] = isset($industry['secondary_industry']) ? $industry['secondary_industry']['first_class'] . ' | ' . $industry['secondary_industry']['second_class'] : '未选择';
        $list = [
            'industry' => $industry,
            'list' => $this->dao->getList($where),
        ];
        return $list;
//        return $this->dao->getList($where);
    }

    /**
     * 获取单条数据
     * @param array $where
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
//        var_dump($info);
        if (!$info) return [];
        $info = $info->toArray();
        switch ($type) {
            case 'is_system':
                $info['content'] = $info['system_text'] ?? '';
                break;
            case 'is_sms':
                $snsCacheName = 'sms_template_list';
                $smsTem = [];
                if (Cache::has($snsCacheName)) {
                    $smsTem = Cache::get($snsCacheName);
                } else {
                    $list = $ServeServices->sms()->temps(1, 30, 0);
                    if (isset($list['data']) && $list['data']) {
                        foreach ($list['data'] as $item) {
                            $smsTem[$item['temp_id']] = $item['content'];
                        }
                    }
                    if ($smsTem) Cache::set($snsCacheName, $smsTem, 172800);
                }
                $info['content'] = isset($smsTem[$info['sms_id']]) ? $smsTem[$info['sms_id']] : '';
                break;
            case 'is_wechat':
                $wechat = $TemplateMessageServices->getOne(['notification_id' => $info['id'], 'type' => 1]);
                $info['templage_message_id'] = $wechat['id'] ?? '';
                $info['tempkey'] = $wechat['tempkey'] ?? '';
                $info['tempid'] = $wechat['tempid'] ?? '';
                $info['content'] = $wechat['content'] ?? '';
                break;
            case 'is_routine':
                $wechat = $TemplateMessageServices->getOne(['notification_id' => $info['id'], 'type' => 0]);
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
     */
    public function saveData(array $data)
    {
        $type = $data['type'];
        $id = $data['id'];
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('数据不存在');
        }
        /** @var TemplateMessageServices $TemplateMessageServices */
        $TemplateMessageServices = app()->make(TemplateMessageServices::class);
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
