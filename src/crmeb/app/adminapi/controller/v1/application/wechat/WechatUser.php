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

use app\adminapi\business\wechat\WechatUserBusiness;
use app\adminapi\controller\AuthController;
use app\adminapi\model\order\StoreOrder;
use app\adminapi\model\user\{User, UserBill};
use app\models\wechat\WechatUser as UserModel;
use crmeb\services\{FormBuilder as Form, WechatService};
use think\Collection;
use think\facade\Route as Url;

/**
 * 微信用户管理
 * Class WechatUser
 * @package app\admin\controller\wechat
 */
class WechatUser extends AuthController

{
    /**
     * 显示操作记录
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['data', ''],
            ['tagid_list', ''],
            ['groupid', '-1'],
            ['sex', ''],
            ['export', ''],
            ['subscribe', '']
        ]);
        $tagidList = explode(',', $where['tagid_list']);
        foreach ($tagidList as $k => $v) {
            if (!$v) {
                unset($tagidList[$k]);
            }
        }
        $tagidList = array_unique($tagidList);
        $where['tagid_list'] = implode(',', $tagidList);
        $list = app()->make(WechatUserBusiness::class)->systemPage($where);
        return app('json')->success($list);
    }

    /**
     * 获取标签和分组
     * @return mixed
     */
    public function get_tag_group()
    {
        try {
            $groupList = UserModel::getUserGroup();
            $tagList = UserModel::getUserTag();
        } catch (\Exception $e) {
            $groupList = [];
            $tagList = [];
        }
        return app('json')->success(compact('groupList', 'tagList'));
    }

    /**
     * 修改用户标签表单
     * @param $openid
     * @return mixed|string
     */
    public function edit_user_tag($openid)
    {
        if (!$openid) return app('json')->fail('参数错误!');
        $list = Collection::make(UserModel::getUserTag())->each(function ($item) {
            return ['value' => $item['id'], 'label' => $item['name']];
        });
        $tagList = UserModel::where('openid', $openid)->value('tagid_list');

        $tagList = explode(',', $tagList) ?: [];
        $f = [Form::select('tag_id', '用户标签', $tagList)->setOptions($list->toArray())->multiple(1)];
        return app('json')->success(create_form('编辑用户标签', $f, Url::buildUrl('/app/wechat/user_tag/' . $openid), 'PUT'));
    }

    /**
     * 修改用户标签
     * @param $openid
     * @return mixed
     */
    public function update_user_tag($openid)
    {
        if (!$openid) return app('json')->fail('参数错误!');
        $tagId = request()->post('tag_id/a', []);
        if (!$tagId) return app('json')->fail('请选择用户标签!');
        $tagList = explode(',', UserModel::where('openid', $openid)->value('tagid_list')) ?: [];
        UserModel::edit(['tagid_list' => $tagId], $openid, 'openid');
        if (!$tagId[0]) unset($tagId[0]);
        UserModel::edit(['tagid_list' => $tagId], $openid, 'openid');
        try {
            foreach ($tagList as $tag) {
                if ($tag) WechatService::userTagService()->batchUntagUsers([$openid], $tag);
            }
            foreach ($tagId as $tag) {
                WechatService::userTagService()->batchTagUsers([$openid], $tag);
            }
        } catch (\Exception $e) {
            UserModel::rollbackTrans();
            return app('json')->fail($e->getMessage());
        }
        UserModel::commitTrans();
        return app('json')->success('修改成功!');
    }

    /**
     * 修改用户分组表单
     * @param $openid
     * @return mixed|string
     */
    public function edit_user_group($openid)
    {
        if (!$openid) return app('json')->fail('参数错误!');
        $list = Collection::make(UserModel::getUserGroup())->each(function ($item) {
            return ['value' => $item['id'], 'label' => $item['name']];
        });
        $groupId = UserModel::where('openid', $openid)->value('groupid');
        $f = [Form::select('group_id', '用户分组', (string)$groupId)->setOptions($list->toArray())];
        return app('json')->success(create_form('编辑用户标签', $f, Url::buildUrl('/app/wechat/user_group/' . $openid), 'PUT'));
    }

    /**
     * 修改用户分组
     * @param $openid
     * @return mixed
     */
    public function update_user_group($openid)
    {
        if (!$openid) return app('json')->fail('参数错误!');
        $groupId = request()->post('group_id');
//        if(!$groupId) return app('json')->fail('请选择用户分组!');
        UserModel::beginTrans();
        UserModel::edit(['groupid' => $groupId], $openid, 'openid');
        try {
            WechatService::userGroupService()->moveUser($openid, $groupId);
        } catch (\Exception $e) {
            UserModel::rollbackTrans();
            return app('json')->fail($e->getMessage());
        }
        UserModel::commitTrans();
        return app('json')->success('修改成功!');
    }

    /**
     * 用户标签列表
     */
    public function tag($refresh = 0)
    {
        $list = [];
        if ($refresh == 1) {
            UserModel::clearUserTag();
            $this->redirect(Url::buildUrl('tag'));
        }
        try {
            $list = UserModel::getUserTag();
        } catch (\Exception $e) {
        }
        return app('json')->success(compact('list'));
    }

    /**
     * 添加标签表单
     * @return mixed
     */
    public function create_tag()
    {
        $f = [Form::input('name', '标签名称')];
        return app('json')->success(create_form('添加标签', $f, Url::buildUrl('/app/wechat/tag'), 'POST'));
    }

    /**
     * 添加
     */
    public function save_tag()
    {
        $tagName = request()->post('name');
        if (!$tagName) return app('json')->fail('请输入标签名称!');
        try {
            WechatService::userTagService()->create($tagName);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
        UserModel::clearUserTag();
        return app('json')->success('添加标签成功!');
    }

    /**
     * 修改标签表单
     * @param $id
     * @return mixed
     */
    public function edit_tag($id)
    {
        $f = [Form::input('name', '标签名称')];
        return app('json')->success(create_form('编辑标签', $f, Url::buildUrl('/app/wechat/tag/' . $id), 'PUT'));
    }

    /**
     * 修改标签
     * @param $id
     */
    public function update_tag($id)
    {
        $tagName = request()->post('name');
        if (!$tagName) return app('json')->fail('请输入标签名称!');
        try {
            WechatService::userTagService()->update($id, $tagName);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
        UserModel::clearUserTag();
        return app('json')->success('修改标签成功!');
    }

    /**
     * 删除标签
     * @param $id
     * @return \think\response\Json
     */
    public function delete_tag($id)
    {
        try {
            WechatService::userTagService()->delete($id);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
        UserModel::clearUserTag();
        return app('json')->success('删除标签成功!');
    }

    /**
     * 用户分组列表
     */

    public function group($refresh = 0)
    {
        $list = [];
        try {
            if ($refresh == 1) {
                UserModel::clearUserGroup();
                $this->redirect(Url::buildUrl('group'));
            }
            $list = UserModel::getUserGroup();
        } catch (\Exception $e) {
        }
        return app('json')->success(compact('list'));
    }

    /**
     * 添加分组表单
     * @return mixed
     */
    public function create_group()
    {
        $f = [Form::input('name', '分组名称')];
        return create_form('添加分组', $f, Url::buildUrl('/app/wechat/group'), 'POST');
    }

    /**
     * 添加
     */
    public function save_group()
    {
        $tagName = request()->post('name');
        if (!$tagName) return app('json')->fail('请输入分组名称!');
        try {
            WechatService::userGroupService()->create($tagName);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
        UserModel::clearUserGroup();
        return app('json')->success('添加分组成功!');
    }

    /**
     * 修改分组表单
     * @param $id
     * @return mixed
     */
    public function edit_group($id)
    {
        $f = [Form::input('name', '分组名称')];
        return create_form('编辑分组', $f, Url::buildUrl('/app/wechat/group/' . $id), 'PUT');
    }

    /**
     * 修改分组
     * @param $id
     */
    public function update_group($id)
    {
        $tagName = request()->post('name');
        if (!$tagName) return app('json')->fail('请输入分组名称!');
        try {
            WechatService::userGroupService()->update($id, $tagName);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
        UserModel::clearUserGroup();
        return app('json')->success('修改分组成功!');
    }

    /**
     * 删除分组
     * @param $id
     * @return \think\response\Json
     */
    public function delete_group($id)
    {
        try {
            WechatService::userTagService()->delete($id);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
        UserModel::clearUserGroup();
        return app('json')->success('删除分组成功!');
    }

    /**
     * 同步标签
     * @param $openid
     * @return mixed
     */
    public function syn_tag($openid)
    {
        if (!$openid) return app('json')->fail('参数错误!');
        $data = array();
        if (UserModel::be($openid, 'openid')) {
            try {
                $tag = WechatService::userTagService()->userTags($openid)->toArray();
            } catch (\Exception $e) {
                return app('json')->fail($e->getMessage());
            }
            if ($tag['tagid_list']) $data['tagid_list'] = implode(',', $tag['tagid_list']);
            else $data['tagid_list'] = '';
            $res = UserModel::edit($data, $openid, 'openid');
            if ($res) return app('json')->success('同步成功');
            else return app('json')->fail('同步失败!');
        } else  return app('json')->fail('参数错误!');
    }

    /**
     * 一级推荐人页面
     * @return mixed
     */
    public function stair($uid = '')
    {
        if ($uid == '') return app('json')->fail('参数错误');
        $list = (new User())->alias('u')
            ->where('u.spread_uid', $uid)
            ->field('u.avatar,u.nickname,u.now_money,u.add_time,u.uid')
            ->where('u.status', 1)
            ->order('u.add_time DESC')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) $list[$key]['orderCount'] = (new StoreOrder())->getOrderCount($value['uid']);
        return app('json')->success(compact('list'));
    }

    /**
     * 个人资金详情页面
     * @return mixed
     */
    public function now_money($uid = '')
    {
        if ($uid == '') return app('json')->fail('参数错误');
        $list = (new UserBill())->where('uid', $uid)->where('category', 'now_money')
            ->field('mark,pm,number,add_time')
            ->where('status', 1)->order('add_time DESC')->select()->toArray();
        foreach ($list as &$v) {
            $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        return app('json')->success(compact('list'));
    }

}

