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
namespace app\adminapi\controller\v1\application\wechat;

use app\adminapi\controller\AuthController;
use app\services\other\TemplateMessageServices;
use crmeb\services\{FormBuilder as Form, template\Template};
use think\facade\App;
use think\facade\Route as Url;
use think\Request;
use think\facade\Cache;

/**
 * 微信模板消息
 * Class WechatTemplate
 * @package app\adminapi\controller\v1\application\wechat
 */
class WechatTemplate extends AuthController
{
    protected $cacheTag = '_system_wechat';

    /**
     * 构造方法
     * WechatTemplate constructor.
     * @param App $app
     * @param TemplateMessageServices $services
     */
    public function __construct(App $app, TemplateMessageServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['name', ''],
            ['status', '']
        ]);
        $where['type'] = 1;
        $data = $this->services->getTemplateList($where);
        $industry = Cache::tag($this->cacheTag)->remember('_wechat_industry', function () {
            try {
                $cache = (new Template('wechat'))->getIndustry();
                if (!$cache) return [];
                Cache::tag($this->cacheTag, ['_wechat_industry']);
                return $cache->toArray();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }, 0) ?: [];
        !is_array($industry) && $industry = [];
        $industry['primary_industry'] = isset($industry['primary_industry']) ? $industry['primary_industry']['first_class'] . ' | ' . $industry['primary_industry']['second_class'] : '未选择';
        $industry['secondary_industry'] = isset($industry['secondary_industry']) ? $industry['secondary_industry']['first_class'] . ' | ' . $industry['secondary_industry']['second_class'] : '未选择';
        $lst = array(
            'industry' => $industry,
            'count' => $data['count'],
            'list' => $data['list']
        );
        return app('json')->success($lst);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $f = array();
        $f[] = Form::input('tempkey', '模板编号');
        $f[] = Form::input('tempid', '模板ID');
        $f[] = Form::input('name', '模板名');
        $f[] = Form::input('content', '回复内容')->type('textarea');
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return app('json')->success(create_form('添加模板消息', $f, Url::buildUrl('/app/wechat/template'), 'POST'));
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = $this->request->postMore([
            'tempkey',
            'tempid',
            'name',
            'content',
            ['status', 0],
            ['type', 1]
        ]);
        if ($data['tempkey'] == '') return app('json')->fail('请输入模板编号');
        if ($data['tempkey'] != '' && $this->services->getOne(['tempkey' => $data['tempkey']]))
            return app('json')->fail('请输入模板编号已存在,请重新输入');
        if ($data['tempid'] == '') return app('json')->fail('请输入模板ID');
        if ($data['name'] == '') return app('json')->fail('请输入模板名');
        if ($data['content'] == '') return app('json')->fail('请输入回复内容');
        $data['add_time'] = time();
        $this->services->save($data);
        return app('json')->success('添加模板消息成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) return app('json')->fail('数据不存在');
        $product = $this->services->get($id);
        if (!$product) return app('json')->fail('数据不存在!');
        $f = array();
        $f[] = Form::input('tempkey', '模板编号', $product->getData('tempkey'))->disabled(1);
        $f[] = Form::input('name', '模板名', $product->getData('name'))->disabled(1);
        $f[] = Form::input('tempid', '模板ID', $product->getData('tempid'));
        $f[] = Form::radio('status', '状态', $product->getData('status'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return app('json')->success(create_form('编辑模板消息', $f, Url::buildUrl('/app/wechat/template/' . $id), 'PUT'));
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = $this->request->postMore([
            'tempid',
            ['status', 0],
            ['type', 1]
        ]);
        if ($data['tempid'] == '') return app('json')->fail('请输入模板ID');
        if (!$id) return app('json')->fail('数据不存在');
        $product = $this->services->get($id);
        if (!$product) return app('json')->fail('数据不存在!');
        $this->services->update($id, $data, 'id');
        return app('json')->success('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail('数据不存在!');
        if (!$this->services->delete($id))
            return app('json')->fail('删除失败,请稍候再试!');
        else
            return app('json')->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return app('json')->fail('参数错误');
        $this->services->update($id, ['status' => $status], 'id');
        return app('json')->success($status == 0 ? '关闭成功' : '开启成功');
    }
}
