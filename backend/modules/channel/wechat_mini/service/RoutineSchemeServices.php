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
namespace modules\channel\wechat_mini\service;

use modules\channel\wechat_mini\dao\RoutineSchemeDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use modules\channel\wechat_mini\sdk\MiniProgramService;
use crmeb\utils\FormBuilder as Form;
use think\facade\Route as Url;

class RoutineSchemeServices extends BaseService
{
    /**
    * @var RoutineSchemeDao
    */
    protected $dao;

    public function __construct(RoutineSchemeDao $dao)
    {
        $this->dao = $dao;
    }

    public function schemeList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList($where, '*', $page, $limit, 'id desc', [], true);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['expire_time'] = $item['expire_time'] == 0 ? '永久' : date('Y-m-d H:i:s', $item['expire_time']);
            $item['http_url'] = sys_config('site_url') . '/surl/' . $item['id'];
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    public function schemeForm($id = 0)
    {
        if ($id) {
            $info = $this->dao->get($id);
            if ($info) $info = $info->toArray();
        } else {
            $info = [];
        }
        $field = [];
        $field[] = Form::input('title', '名称', $info['title'] ?? '')->placeholder('请填写链接名称');
        $field[] = Form::input('path', '小程序页面', $info['path'] ?? '')->placeholder('请填写小程序页面地址，可以携带参数，例：/pages/index/index?a=1&b=2');
        $field[] = Form::radio('expire_type', '到期类型', $info['expire_type'] ?? -1)->appendControl(0, [
            Form::dateTime('expire_num', '到期时间', $info['expire_time'] ?? 0)->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'domProps' => ['innerHTML' => '有效期限必须在当前时间1分钟之后，30天之前']
            ]),
        ])->appendControl(1, [
            Form::input('expire_num', '有效天数', $info['expire_interval'] ?? 0),
        ])->options([['label' => '永久', 'value' => -1], ['label' => '到期时间', 'value' => 0], ['label' => '有效天数', 'value' => 1]]);
        return create_form('小程序链接', $field, Url::buildUrl('/app/routine/scheme_save/' . $id), 'POST');
    }

    public function schemeSave($id, $data)
    {
        $path = explode('?', $data['path']);
        $jumpWxa = [
            'path' => $path[0],
            'query' => $path[1] ?? '',
        ];
        $expireNum = $data['expire_type'] == 0 ? strtotime($data['expire_num']) : $data['expire_num'];
        $url = MiniProgramService::getUrlScheme($jumpWxa, $data['expire_type'], $expireNum);
//        $url = MiniProgramService::getUrlLink($jumpWxa);
        $saveData = [];
        $saveData['title'] = $data['title'];
        $saveData['path'] = $data['path'];
        $saveData['url'] = $url;
        $saveData['add_time'] = time();
        $saveData['expire_type'] = $data['expire_type'];
        if ($data['expire_type'] == -1) {
            $saveData['expire_interval'] = $saveData['expire_time'] = 0;
        } elseif ($data['expire_type'] == 0) {
            $saveData['expire_interval'] = 0;
            $saveData['expire_time'] = $expireNum;
        } else {
            $saveData['expire_interval'] = $expireNum;
            $saveData['expire_time'] = time() + ($expireNum * 86400);
        }
        if ($id) {
            $res = $this->dao->update($id, $saveData);
        } else {
            $res = $this->dao->save($saveData);
        }
        if ($res) return true;
        throw new AdminException('小程序链接生成失败');
    }
}
