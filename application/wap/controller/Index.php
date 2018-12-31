<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/11
 */

namespace app\wap\controller;

use app\wap\model\store\StoreCombination;
use app\wap\model\store\StoreSeckill;
use app\wap\model\store\StoreCategory;
use app\wap\model\store\StoreOrder;
use app\wap\model\store\StorePink;
use app\wap\model\store\StoreProduct;
use app\wap\model\user\User;
use app\wap\model\user\UserNotice;
use app\wap\model\user\WechatUser;
use basic\WapBasic;
use service\GroupDataService;
use service\QrcodeService;
use service\SystemConfigService;
use service\WechatService;
use think\Db;
use think\Url;

class Index extends AuthController
//class Index extends WapBasic
{
    public function index()
    {
        try{
            $uid = User::getActiveUid();
            $notice = UserNotice::getNotice($uid);
        }catch (\Exception $e){
            $notice = 0;
        }
        $storePink = StorePink::where('p.add_time','GT',time()-86300)->alias('p')->where('p.status',1)->join('User u','u.uid=p.uid')->field('u.nickname,u.avatar as src,p.add_time')->order('p.add_time desc')->limit(20)->select();
        if($storePink){
            foreach ($storePink as $k=>$v){
                $remain = $v['add_time']%86400;
                $hour = floor($remain/3600);
                $storePink[$k]['nickname'] = $v['nickname'].$hour.'小时之前拼单';
            }
        }
        $seckillnum=(int)GroupDataService::getData('store_seckill');
        $storeSeckill=StoreSeckill::where('is_del',0)->where('status',1)
               ->where('start_time','<',time())->where('stop_time','>',time())
               ->limit($seckillnum)->order('sort desc')->select()->toArray();
        foreach($storeSeckill as $key=>$value){
            if($value['stock']>0)
            $round = round($value['sales']/$value['stock'],2)*100;
            else $round = 100;
            if($round<100){
                $storeSeckill[$key]['round']=$round;
            }else{
                $storeSeckill[$key]['round']=100;
            }
        }
        $this->assign([
            'banner'=>GroupDataService::getData('store_home_banner')?:[],
            'menus'=>GroupDataService::getData('store_home_menus')?:[],
            'roll_news'=>GroupDataService::getData('store_home_roll_news')?:[],
            'category'=>StoreCategory::pidByCategory(0,'id,cate_name'),
            'pinkImage'=>SystemConfigService::get('store_home_pink'),
            'notice'=>$notice,
            'storeSeckill'=>$storeSeckill,
            'storePink'=>$storePink,
        ]);
        return $this->fetch();
    }

    public function about()
    {

        return $this->fetch();
    }

    public function spread($uni = '')
    {
        if(!$uni || $uni == 'now') $this->redirect(Url::build('spread',['uni'=>$this->oauth()]));
        $wechatUser = WechatUser::getWechatInfo($uni);
        $statu = (int)SystemConfigService::get('store_brokerage_statu');
        if($statu == 1){
            if(!User::be(['uid'=>$this->userInfo['uid'],'is_promoter'=>1]))
                return $this->failed('没有权限访问!');
        }
        $qrInfo = QrcodeService::getTemporaryQrcode('spread',$wechatUser['uid']);
        $this->assign([
            'qrInfo'=>$qrInfo,
            'wechatUser'=>$wechatUser
        ]);
        return $this->fetch();
    }

}