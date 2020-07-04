<?php

namespace app\admin\controller\wechat;

use app\admin\controller\AuthController;
use app\admin\model\article\Article;
use app\models\article\ArticleContent;
use think\facade\Route as Url;
use app\admin\model\article\Article as ArticleModel;
use app\admin\model\wechat\{WechatReply, WechatUser, WechatNewsCategory as WechatNewsCategoryModel};
use crmeb\services\{FormBuilder as Form, UtilService as Util, JsonService as Json, UtilService, WechatService};

/**
 * 图文信息
 * Class WechatNewsCategory
 * @package app\admin\controller\wechat
 *
 */
class WechatNewsCategory extends AuthController
{
    public function select($callback = '_selectNews$eb')
    {
        $where = Util::getMore([
            ['cate_name', ''],
            ['type', 1]
        ], $this->request);
        $this->assign('where', $where);
        $this->assign('callback', $callback);
        if ($where['type'] == 1)
            $list = WechatNewsCategoryModel::getSendAll($where);
        else
            $list = WechatNewsCategoryModel::getAll($where);
        $newList = $list['list']->toArray();
        $newList = $newList['data'];
        $this->assign($list);
        $this->assign('type',$where['type']);
        $this->assign('newList', $newList);
        return $this->fetch();
    }

    public function index()
    {
        $where = Util::getMore([
            ['cate_name', ''],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(WechatNewsCategoryModel::getAll($where));
        return $this->fetch();
    }

    public function create()
    {
        $f = array();
        $f[] = Form::input('cate_name', '分类名称')->autofocus(1);
        $f[] = Form::select('new_id', '图文列表')->setOptions(function () {
            $list = ArticleModel::getNews();
            $options = [];
            foreach ($list as $id => $roleName) {
                $options[] = ['label' => $roleName, 'value' => $id];
            }
            return $options;
        })->filterable(1)->multiple(1);
        $form = Form::make_post_form('编辑菜单', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save()
    {
        $data = Util::postMore([
            'cate_name',
            ['new_id', []],
            ['sort', 0],
            ['add_time', time()],
            ['status', 1],]);
        if (!$data['cate_name']) return Json::fail('请输入图文名称');
        if (empty($data['new_id'])) return Json::fail('请选择图文列表');
        $data['new_id'] = array_unique($data['new_id']);
        if (count($data['new_id']) > 8) {
            $data['new_id'] = array_slice($data['new_id'], 0, 8);
        };
        $data['new_id'] = implode(',', $data['new_id']);
        WechatNewsCategoryModel::create($data);
        return Json::successful('添加菜单成功!');
    }

    public function edit($id)
    {
        $menu = WechatNewsCategoryModel::get($id);
        if (!$menu) return Json::fail('数据不存在!');
        $arr_new_id = array_unique(explode(',', $menu->new_id));
        foreach ($arr_new_id as $k => $v) {
            $arr_new_id[$k] = intval($v);
        }
        $f = array();
        $f[] = Form::input('cate_name', '分类名称', $menu['cate_name'])->autofocus(1);
        $f[] = Form::select('new_id', '图文列表', $arr_new_id)->setOptions(function () {
            $list = ArticleModel::getNews();
            $options = [];
            foreach ($list as $id => $roleName) {
                $options[] = ['label' => $roleName, 'value' => $id];
            }
            return $options;
        })->filterable(1)->multiple(1);
        $form = Form::make_post_form('编辑图文', $f, Url::buildUrl('update', compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function update($id)
    {
        $data = Util::postMore([
            'cate_name',
            ['new_id', []],
            ['sort', 0],
            ['status', 1],]);
        if (!$data['cate_name']) return Json::fail('请输入图文名称');
        if (empty($data['new_id'])) return Json::fail('请选择图文列表');
        if (count($data['new_id']) > 8) {
            $data['new_id'] = array_slice($data['new_id'], 0, 8);
        };
        $data['new_id'] = implode(',', $data['new_id']);;
        if (!WechatNewsCategoryModel::get($id)) return Json::fail('编辑的记录不存在!');
        WechatNewsCategoryModel::edit($data, $id);
        return Json::successful('修改成功!');
    }

    public function delete($id)
    {
        if (!WechatNewsCategoryModel::del($id))
            return Json::fail(WechatNewsCategoryModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }


    /**
     * 发送消息
     * @param int $id
     * @param string $wechat
     * $wechat  不为空  发消息  /  空 群发消息
     */
    public function push($id = 0, $new_id = 0, $wechat = '')
    {
        if ((!$id) || (!$new_id)) return Json::fail('参数错误');
        $list = WechatNewsCategoryModel::getWechatNewsItem($id, $new_id);
        $wechatNews = [];
        if ($list) {
            if ($list['new'] && is_array($list['new'])) {
                foreach ($list['new'] as $kk => $vv) {
                    $wechatNews[$kk]['title'] = $vv['title'];
                    $wechatNews[$kk]['image'] = $vv['image_input'];
                    $wechatNews[$kk]['date'] = date('m月d日', time());
                    $wechatNews[$kk]['description'] = $vv['synopsis'];
                    $wechatNews[$kk]['id'] = $vv['id'];
                }
            }
        }
        if ($wechat != '') {//客服消息
            $wechatNews = WechatReply::tidyNews($wechatNews);
            $message = WechatService::newsMessage($wechatNews);
            $errorLog = [];//发送失败的用户
            $user = WechatUser::where('uid', 'IN', $wechat)->column('nickname,subscribe,openid', 'uid');
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
            } else return Json::fail('发送失败，参数不正确');
            if (!count($errorLog)) return Json::successful('全部发送成功');
            else return Json::successful(implode(',', $errorLog) . '，剩余的发送成功');
        }
    }

    public function send_news($id = '')
    {
        if ($id == '') return $this->failed('参数错误');
        $where = Util::getMore([
            ['cate_name', ''],
            ['type', 0]
        ], $this->request);
        $this->assign('where', $where);
        $this->assign('wechat', $id);
        $this->assign(WechatNewsCategoryModel::getSendAll($where));
        return $this->fetch();
    }

    public function append()
    {
        $this->assign('list', [
            [
                'id' => 0,
                'title' => '',
                'author' => $this->adminInfo->real_name,
                'content' => '',
                'image_input' => '/system/module/wechat/news/images/image.png',
                'synopsis' => '',
            ]
        ]);
        $this->assign('id', 0);
        $this->assign('author', $this->adminInfo->real_name);
        return $this->fetch();
    }

    public function append_save()
    {
        $data = UtilService::postMore([
            ['list', []],
            ['id', 0]
        ]);
        $id = [];
        $countList = count($data['list']);
        if (!$countList) return Json::fail('请添加图文');
        Article::beginTrans();
        foreach ($data['list'] as $k => $v) {
            if ($v['title'] == '') return Json::fail('标题不能为空');
            if ($v['author'] == '') return Json::fail('作者不能为空');
            if ($v['content'] == '') return Json::fail('正文不能为空');
            if ($v['synopsis'] == '') return Json::fail('摘要不能为空');
            $v['status'] = 1;
            $v['add_time'] = time();
            if ($v['id']) {
                $idC = $v['id'];
                unset($v['id']);
                Article::edit($v, $idC);
                ArticleContent::where('nid', $idC)->update(['content' => $v['content']]);
                $data['list'][$k]['id'] = $idC;
                $id[] = $idC;
            } else {
                unset($v['id']);
                $res = Article::create($v)->toArray();
                $id[] = $res['id'];
                $data['list'][$k]['id'] = $res['id'];
                ArticleContent::insert(['content' => $v['content'], 'nid' => $res['id']]);
            }
        }
        $countId = count($id);
        if ($countId != $countList) {
            Article::checkTrans(false);
            if ($data['id']) return Json::fail('修改失败');
            else return Json::fail('添加失败');
        } else {
            Article::checkTrans(true);
            $newsCategory['cate_name'] = $data['list'][0]['title'];
            $newsCategory['new_id'] = implode(',', $id);
            $newsCategory['sort'] = 0;
            $newsCategory['add_time'] = time();
            $newsCategory['status'] = 1;
            if ($data['id']) {
                WechatNewsCategoryModel::edit($newsCategory, $data['id']);
                return Json::successful('修改成功');
            } else {
                WechatNewsCategoryModel::create($newsCategory);
                return Json::successful('添加成功');
            }
        }
    }

    public function modify($id)
    {
        if (!strlen(trim($id)) || !$id) return $this->failed('参数错误');
        $wechatNews = WechatNewsCategoryModel::get($id);
        if (!$wechatNews) return $this->failed('参数错误');
        $wechatNews = $wechatNews->toArray();
        $list = Article::getArticleList($wechatNews['new_id']);
        $this->assign('list', $list);
        $this->assign('id', $id);
        $this->assign('author', $this->adminInfo->real_name);
        return $this->fetch('append');
    }

}