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

namespace app\kefuapi\controller;

use app\Request;
use think\facade\App;
use app\services\kefu\KefuServices;
use app\services\other\CategoryServices;
use app\kefuapi\validate\SpeechcraftValidate;
use app\services\message\service\StoreServiceSpeechcraftServices;

/**
 * Class Service
 * @package app\kefuapi\controller
 */
class Service extends AuthController
{
    /**
     * Service constructor.
     * @param App $app
     * @param KefuServices $services
     */
    public function __construct(App $app, KefuServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 转接客服列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getServiceList(Request $request, $uid = 0)
    {
        $where = $request->getMore([
            ['nickname', ''],
        ]);
        return $this->success($this->services->getServiceList($where, [$this->kefuInfo['uid'], $uid]));
    }

    /**
     * 话术列表
     * @param Request $request
     * @param StoreServiceSpeechcraftServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSpeechcraftList(Request $request, StoreServiceSpeechcraftServices $services)
    {
        $where = $request->getMore([
            ['title', ''],
            ['cate_id', ''],
            ['type', 0]
        ]);
        if ($where['type']) {
            $where['kefu_id'] = $this->kefuId;
        } else {
            $where['kefu_id'] = 0;
        }
        $data = $services->getSpeechcraftList($where);
        return $this->success($data['list']);
    }

    /**
     * 添加分类
     * @param Request $request
     * @param CategoryServices $services
     * @return mixed
     */
    public function saveCate(Request $request, CategoryServices $services)
    {
        $data = $request->postMore([
            ['name', ''],
            [['sort', 'd'], 0],
        ]);

        if (!$data['name']) {
            return $this->fail('分类名称不能为空');
        }
        $data['add_time'] = time();
        $data['owner_id'] = $this->kefuId;
        $data['type'] = 1;

        $services->save($data);
        return $this->success('添加成功');
    }

    /**
     * 修改分类
     * @param Request $request
     * @param CategoryServices $services
     * @param $id
     * @return mixed
     */
    public function editCate(Request $request, CategoryServices $services, $id)
    {
        $data = $request->postMore([
            ['name', ''],
            [['sort', 'd'], 0],
        ]);

        if (!$data['name']) {
            return $this->fail('分类不能为空');
        }

        $cateInfo = $services->get($id);
        if (!$cateInfo) {
            return $this->fail('分类没有查到无法删除');
        }
        $cateInfo->name = $data['name'];
        $cateInfo->sort = $data['sort'];

        if ($cateInfo->save()) {
            return $this->success('分类修改成功');
        } else {
            return $this->fail('分类没有查到无法删除');
        }
    }

    /**
     * 删除分类
     * @param CategoryServices $services
     * @param $id
     * @return mixed
     */
    public function deleteCate(CategoryServices $services, $id)
    {
        $cateInfo = $services->get($id);
        if (!$cateInfo) {
            return $this->fail('分类不存在');
        }

        if ($cateInfo->delete()) {
            return $this->success('删除成功');
        } else {
            return $this->fail('删除失败');
        }
    }

    /**
     * 获取当前客服分类
     * @param CategoryServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCateList(CategoryServices $services, $type)
    {
        return $this->success($services->getCateList(['owner_id' => $type ? $this->kefuId : 0, 'type' => 1], ['id', 'name', 'sort']));
    }

    /**
     * 添加话术
     * @param Request $request
     * @param StoreServiceSpeechcraftServices $services
     * @return mixed
     */
    public function saveSpeechcraft(Request $request, StoreServiceSpeechcraftServices $services, CategoryServices $categoryServices)
    {
        $data = $request->postMore([
            ['title', ''],
            ['cate_id', 0],
            ['message', ''],
            ['sort', 0]
        ]);

        validate(SpeechcraftValidate::class)->check($data);

        if (!$categoryServices->count(['owner_id' => $this->kefuId, 'type' => 1, 'id' => $data['cate_id']])) {
            return $this->fail('您选择的分类不存在');
        }
        if ($services->count(['message' => $data['message']])) {
            return $this->fail('添加的内容重复');
        }
        $data['add_time'] = time();
        $data['kefu_id'] = $this->kefuId;

        $res = $services->save($data);
        if ($res) {
            return $this->success('添加话术成功', $res->toArray());
        } else {
            return $this->fail('添加话术失败');
        }
    }

    /**
     * 修改话术
     * @param Request $request
     * @param StoreServiceSpeechcraftServices $services
     * @param $id
     * @return mixed
     */
    public function editSpeechcraft(Request $request, StoreServiceSpeechcraftServices $services, CategoryServices $categoryServices, $id)
    {
        $data = $request->postMore([
            ['title', ''],
            ['cate_id', 0],
            ['message', ''],
        ]);

        if (!$data['message']) {
            return $this->fail('话术标题内容不能为空');
        }
        if (!$categoryServices->count(['owner_id' => $this->kefuId, 'type' => 1, 'id' => $data['cate_id']])) {
            return $this->fail('您选择的分类不存在');
        }
        $speechcraft = $services->get($id);
        if (!$speechcraft) {
            return $this->fail('话术没有被查到');
        }
        if (!$speechcraft->kefu_id) {
            return $this->fail('公共话术不能修改');
        }
        $speechcraft->title = $data['title'];
        if ($data['cate_id']) {
            $speechcraft->cate_id = $data['cate_id'];
        }
        $speechcraft->message = $data['message'];

        if ($speechcraft->save()) {
            return $this->success('修改成功');
        } else {
            return $this->fail('修改失败');
        }
    }

    /**
     * 删除话术
     * @param StoreServiceSpeechcraftServices $services
     * @param $id
     * @return mixed
     */
    public function deleteSpeechcraft(StoreServiceSpeechcraftServices $services, $id)
    {
        $speechcraft = $services->get($id);
        if (!$speechcraft) {
            return $this->fail('话术没有被查到');
        }
        if ($speechcraft->delete()) {
            return $this->success('删除成功');
        } else {
            return $this->fail('删除失败');
        }
    }

    /**
     * 聊天记录
     * @param $uid
     * @return mixed
     */
    public function getChatList(Request $request)
    {
        [$uid, $upperId, $is_tourist] = $request->postMore([
            ['uid', 0],
            ['upperId', 0],
            ['is_tourist', 0],
        ], true);
        if (!$uid) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->services->getChatList($this->kefuInfo['uid'], $uid, (int)$upperId, $is_tourist));
    }

    /**
     * 当前客服详细信息
     * @return mixed
     */
    public function getServiceInfo()
    {
        $this->kefuInfo['site_name'] = sys_config('site_name');
        return $this->success($this->kefuInfo->toArray());
    }

    /**
     * 客服转接
     * @return mixed
     */
    public function transfer()
    {
        [$kefuToUid, $uid] = $this->request->postMore([
            ['kefuToUid', 0],
            ['uid', 0]
        ], true);
        if (!$kefuToUid || !$uid) {
            return $this->fail('缺少转接人id');
        }
        $this->services->setTransfer($this->kefuInfo['uid'], (int)$uid, (int)$kefuToUid);
        return $this->success('转接成功');
    }
}
