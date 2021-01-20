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
use crmeb\services\WechatService;
use think\facade\App;
use app\services\wechat\WechatNewsCategoryServices;
use app\services\article\ArticleServices;

/**
 * 图文信息
 * Class WechatNewsCategory
 * @package app\admin\controller\wechat
 *
 */
class WechatNewsCategory extends AuthController
{
    /**
     * 构造方法
     * Menus constructor.
     * @param App $app
     * @param WechatNewsCategoryServices $services
     */
    public function __construct(App $app, WechatNewsCategoryServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 图文消息列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['cate_name', '']
        ]);
        $list = $this->services->getAll($where);
        return app('json')->success($list);
    }

    /**
     * 图文详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        $info = $this->services->get($id);
        /** @var ArticleServices $services */
        $services = app()->make(ArticleServices::class);
        $new = $services->articlesList($info['new_id']);
        if ($new) $new = $new->toArray();
        $info['new'] = $new;
        return app('json')->success(compact('info'));
    }

    /**
     * 删除图文
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$this->services->delete($id))
            return app('json')->fail('删除失败,请稍候再试!');
        else
            return app('json')->success('删除成功!');
    }

    /**
     * 新增或编辑保存
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['list', []],
            ['id', 0]
        ]);
        try {
            $id = [];
            $countList = count($data['list']);
            if (!$countList) return app('json')->fail('请添加图文');
            /** @var ArticleServices $services */
            $services = app()->make(ArticleServices::class);
            foreach ($data['list'] as $k => $v) {
                if ($v['title'] == '') return app('json')->fail('标题不能为空');
                if ($v['author'] == '') return app('json')->fail('作者不能为空');
                if ($v['content'] == '') return app('json')->fail('正文不能为空');
                if ($v['synopsis'] == '') return app('json')->fail('摘要不能为空');
                $v['status'] = 1;
                $v['add_time'] = time();
                if ($v['id']) {
                    $idC = $v['id'];
                    $services->save($v);
                    unset($v['id']);
                    $data['list'][$k]['id'] = $idC;
                    $id[] = $idC;
                } else {
                    $res = $services->save($v);
                    unset($v['id']);
                    $id[] = $res['id'];
                    $data['list'][$k]['id'] = $res['id'];
                }
            }
            $countId = count($id);
            if ($countId != $countList) {
                if ($data['id']) return app('json')->fail('修改失败');
                else return app('json')->fail('添加失败');
            } else {
                $newsCategory['cate_name'] = $data['list'][0]['title'];
                $newsCategory['new_id'] = implode(',', $id);
                $newsCategory['sort'] = 0;
                $newsCategory['add_time'] = time();
                $newsCategory['status'] = 1;
                if ($data['id']) {
                    $this->services->update($data['id'], $newsCategory, 'id');
                    return app('json')->success('修改成功');
                } else {
                    $this->services->save($newsCategory);
                    return app('json')->success('添加成功');
                }
            }
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 发送消息
     * @param int $id
     * @param string $wechat
     * $wechat  不为空  发消息  /  空 群发消息
     */
    public function push()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['user_ids', '']
        ]);
        if (!$data['id']) return app('json')->fail('参数错误');
        $list = $this->services->getWechatNewsItem($data['id']);
        $wechatNews = [];
        if ($list) {
            if (is_array($list['new']) && count($list['new'])) {
                $wechatNews['title'] = $list['new'][0]['title'];
                $wechatNews['image_input'] = $list['new'][0]['image_input'];
                $wechatNews['date'] = date('m月d日', time());
                $wechatNews['description'] = $list['new'][0]['synopsis'];
                $wechatNews['id'] = $list['new'][0]['id'];
            }
        }
        if ($data['user_ids'] != '') {//客服消息
            $wechatNews = $this->services->wechatPush($wechatNews);
            $message = WechatService::newsMessage($wechatNews);
            $errorLog = [];//发送失败的用户
            $user = $this->services->getWechatUser($data['user_ids'], 'nickname,subscribe,openid', 'uid');
            if ($user) {
                foreach ($user as $v) {
                    if ($v['subscribe'] && $v['openid']) {
                        try {
                            WechatService::staffService()->message($message)->to($v['openid'])->send();
                        } catch (\Exception $e) {
                            $errorLog[] = $v['nickname'] . '发送失败';
                        }
                    } else {
                        $errorLog[] = $v['nickname'] . '没有关注发送失败(不是微信公众号用户)';
                    }
                }
            } else return app('json')->fail('发送失败，参数不正确');
            if (!count($errorLog)) return app('json')->success('全部发送成功');
            else return app('json')->success(implode(',', $errorLog) . '，剩余的发送成功');
        }

    }

    /**
     * 发送消息图文列表
     * @return mixed
     */
    public function send_news()
    {
        $where = $this->request->getMore([
            ['cate_name', ''],
            ['page', 1],
            ['limit', 10]
        ]);
        return app('json')->success($this->services->list($where));
    }

}
