<?php
namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use crmeb\services\UtilService as Util;
use app\admin\model\user\UserPoint AS UserPointModel;
use think\facade\Route as Url;
use crmeb\services\JsonService;

/**
 * 优惠券控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class UserPoint extends AuthController
{

    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign([
//            'sum_point'=>UserBill::where(['category'=>'integral','type'=>'system_add'])->sum('number'),
//            'count'=>UserBill::where(['category'=>'integral','type'=>'sign'])->group('uid')->count(),
//            'song_point'=>UserBill::where(['category'=>'integral','type'=>'sign'])->group('uid')->sum('number'),
            'is_layui'=>true,
            'year'=>getMonth(),
        ]);
        return $this->fetch();
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $this->assign(['title'=>'添加优惠券','action'=>Url::buildUrl('save'),'rules'=>$this->rules()->getContent()]);
        return $this->fetch('public/common_form');
    }
    //异步获取积分列表
    public function getponitlist(){
        $where = Util::getMore([
            ['start_time',''],
            ['end_time',''],
            ['nickname',''],
            ['page',1],
            ['limit',10],
        ]);
        return JsonService::successlayui(UserPointModel::getpointlist($where));
    }
    //导出Excel表格
    public function export(){
        $where = Util::getMore([
            ['start_time',''],
            ['end_time',''],
            ['nickname',''],
        ]);
        UserPointModel::SaveExport($where);
    }
    //获取积分日志头部信息
    public function getuserpointbadgelist(){
        $where = Util::getMore([
            ['start_time',''],
            ['end_time',''],
            ['nickname',''],
        ]);
        return JsonService::successful(UserPointModel::getUserpointBadgelist($where));
    }

}
