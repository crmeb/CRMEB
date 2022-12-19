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

namespace app\services\system;

use app\dao\system\AppVersionDao;
use app\services\BaseServices;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * Class AppVersionServices
 * @package app\services\system
 */
class AppVersionServices extends BaseServices
{
    /**
     * DiyServices constructor.
     * @param AppVersionDao $dao
     */
    public function __construct(AppVersionDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 版本列表
     * @param $platform
     * @return array
     */
    public function versionList($platform)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->versionList($platform, $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        $count = $this->dao->count(['platform' => $platform]);
        return compact('list', 'count');
    }

    /**
     * 添加版本表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm($id = 0)
    {
        if ($id) {
            $info = $this->dao->get($id);
        }
        $field[] = Form::hidden('id', $info['id'] ?? 0);
        $field[] = Form::input('version', '版本号', $info['version'] ?? '')->col(24);
        $field[] = Form::radio('platform', '平台类型', $info['platform'] ?? 1)->options([['label' => 'Android', 'value' => 1], ['label' => 'IOS', 'value' => 2]]);
        $field[] = Form::input('info', '版本介绍', $info['info'] ?? '')->type('textarea');
        $field[] = Form::input('url', '下载链接', $info['url'] ?? '');
        $field[] = Form::radio('is_force', '强制', $info['is_force'] ?? 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $field[] = Form::radio('is_new', '是否最新', $info['is_new'] ?? 1)->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]]);
        return create_form('添加版本信息', $field, Url::buildUrl('/system/version_save'), 'POST');

    }

    /**
     * 保存数据
     * @param $id
     * @param $data
     * @return mixed
     */
    public function versionSave($id, $data)
    {
        if ($id) {
            return $this->transaction(function () use ($data, $id) {
                if ($data['is_new']) {
                    $this->dao->update(['platform' => $data['platform']], ['is_new' => 0]);
                }
                return $this->dao->update($id, $data);
            });
        } else {
            $data['is_del'] = 0;
            $data['add_time'] = time();
            return $this->transaction(function () use ($data) {
                $this->dao->update(['platform' => $data['platform']], ['is_new' => 0]);
                return $this->dao->save($data);
            });
        }
    }

    /**
     * 获取系统下最新的版本信息
     * @param $platform
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNewInfo($platform)
    {
        $res = $this->dao->get(['platform' => $platform, 'is_new' => 1]);
        if ($res) {
            $res = $res->toArray();
            $res['time'] = date('Y-m-d H:i:s', $res['add_time']);
            return $res;
        } else {
            return [];
        }
    }
}
