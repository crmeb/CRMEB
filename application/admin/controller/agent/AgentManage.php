<?php

namespace app\admin\controller\agent;

use app\admin\controller\AuthController;
use app\admin\model\order\StoreOrder;
use app\admin\model\user\User;
use app\admin\model\wechat\WechatUser as UserModel;
use app\admin\library\FormBuilder;
use app\wap\model\user\UserBill;
use service\UtilService as Util;

/**
 * 分销商管理控制器
 * Class AgentManage
 * @package app\admin\controller\agent
 */
class AgentManage extends AuthController
{

    /**
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['nickname',''],
            ['data',''],
            ['tagid_list',''],
            ['groupid','-1'],
            ['sex',''],
            ['export',''],
            ['stair',''],
            ['second',''],
            ['order_stair',''],
            ['order_second',''],
            ['subscribe',''],
            ['now_money',''],
            ['is_promoter',1],
        ],$this->request);
        $this->assign([
            'where'=>$where,
        ]);
        $limitTimeList = [
            'today'=>implode(' - ',[date('Y/m/d'),date('Y/m/d',strtotime('+1 day'))]),
            'week'=>implode(' - ',[
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month'=>implode(' - ',[date('Y/m').'/01',date('Y/m').'/'.date('t')]),
            'quarter'=>implode(' - ',[
                date('Y').'/'.(ceil((date('n'))/3)*3-3+1).'/01',
                date('Y').'/'.(ceil((date('n'))/3)*3).'/'.date('t',mktime(0,0,0,(ceil((date('n'))/3)*3),1,date('Y')))
            ]),
            'year'=>implode(' - ',[
                date('Y').'/01/01',date('Y/m/d',strtotime(date('Y').'/01/01 + 1year -1 day'))
            ])
        ];
        $uidAll = UserModel::getAll($where);
        $this->assign(compact('limitTimeList','uidAll'));
        $this->assign(UserModel::agentSystemPage($where));
        return $this->fetch();
    }

    /**
     * 一级推荐人页面
     * @return mixed
     */
    public function stair($uid = ''){
        if($uid == '') return $this->failed('参数错误');
        $list = User::alias('u')
            ->where('u.spread_uid',$uid)
            ->field('u.avatar,u.nickname,u.now_money,u.add_time,u.uid')
            ->where('u.status',1)
            ->order('u.add_time DESC')
            ->select()
            ->toArray();
        foreach ($list as $key=>$value) $list[$key]['orderCount'] = StoreOrder::getOrderCount($value['uid']);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 个人资金详情页面
     * @return mixed
     */
    public function now_money($uid = ''){
        if($uid == '') return $this->failed('参数错误');
        $list = UserBill::where('uid',$uid)->where('category','now_money')
            ->field('mark,pm,number,add_time')
            ->where('status',1)->order('add_time DESC')->select()->toArray();
        foreach ($list as &$v){
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
}
