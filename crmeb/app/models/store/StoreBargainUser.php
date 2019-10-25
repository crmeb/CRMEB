<?php
namespace app\models\store;

use app\models\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 参与砍价Model
 * Class StoreBargainUser
 * @package app\models\store
 */
class StoreBargainUser extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_bargain_user';

    use ModelTrait;

    /**
     * TODO 根据砍价产品获取正在参与的用户头像
     * @param array $bargain
     * @param int $limit
     * @return array
     */
    public static function getUserList($bargain = array(),$limit = 10){
         if(count($bargain) < 1) return [];
         foreach ($bargain as $k=>$v){
             if(is_array($v)){
                 $uid = self::getUserIdList($v['id']);
                 if(count($uid) > 0) {
                     $userInfo = User::where('uid','IN',implode(',',$uid))->limit($limit)->column('avatar','uid');
                     $bargain[$k]['userInfo'] = $userInfo;
                     $bargain[$k]['userInfoCount'] = count($userInfo);
                 }
                 else {
                     $bargain[$k]['userInfo'] = [];
                     $bargain[$k]['userInfoCount'] = 0;
                 }
             }else{
                 $uid = self::getUserIdList($bargain['id']);
                 if(count($uid) > 0) $bargain['userInfo'] = User::where('uid','IN',implode(',',$uid))->column('avatar','uid');
                 else $bargain['userInfo'] = [];
             }
         }
         return $bargain;
    }

    /**
     * TODO 根据砍价产品编号获取正在参与人的编号
     * @param int $bargainId $bargainId  砍价产品ID
     * @param int $status   $status  状态  1 进行中  2 结束失败  3结束成功
     * @return array
     */
    public static function getUserIdList($bargainId = 0,$status = 1){
        if(!$bargainId) return [];
        return self::where('bargain_id',$bargainId)->where('status',$status)->column('uid','id');
    }

    /**
     * TODO 添加一条砍价记录
     * @param int $bargainId  $bargainId 砍价产品编号
     * @param int $bargainUserUid  $bargainUserUid 开启砍价用户编号
     * @return bool|object
     */
    public static function setBargain($bargainId = 0,$bargainUserUid = 0){
        if(!$bargainId || !$bargainUserUid || !StoreBargain::validBargain($bargainId) || self::be(['bargain_id'=>$bargainId,'uid'=>$bargainUserUid,'status'=>1,'is_del'=>0])) return false;
        $data['bargain_id'] = $bargainId;
        $data['uid'] = $bargainUserUid;
        $data['bargain_price_min'] = StoreBargain::where('id',$bargainId)->value('min_price');
        $data['bargain_price'] = StoreBargain::where('id',$bargainId)->value('price');
        $data['price'] = 0;
        $data['status'] = 1;
        $data['is_del'] = 0;
        $data['add_time'] = time();
        return self::create($data);
    }

    /**
     * TODO 判断当前人是否已经参与砍价
     * @param int $bargainId  $bargainId 砍价产品编号
     * @param int $bargainUserUid  $bargainUserUid 开启砍价用户编号
     * @return bool|int|string
     * @throws \think\Exception
     */
    public static function isBargainUser($bargainId = 0,$bargainUserUid = 0){
        if(!$bargainId || !$bargainUserUid || !StoreBargain::validBargain($bargainId)) return false;
        return self::where('bargain_id',$bargainId)->where('uid',$bargainUserUid)->where('is_del',0)->count();
    }

    /**
     * TODO 获取用户砍掉的价格
     * @param int $id  $id 用户参与砍价表编号
     * @return float
     */
    public static function getBargainUserPrice($id = 0){
        return (float)self::where('id',$id)->value('price');
    }

    /**
     * 获取砍掉用户当前状态
     * @param int $id  $id 用户参与砍价表编号
     * @return int
     */
    public static function getBargainUserStatusEnd($id = 0){
        return (int)self::where('id',$id)->value('status');
    }

    /**
     * TODO 获取用户可以砍掉的价格
     * @param $id $id 用户参与砍价表编号
     * @return float
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getBargainUserDiffPriceFloat($id){
        $price = self::where('id',$id)->field('bargain_price,bargain_price_min')->find();
        return (float)bcsub($price['bargain_price'],$price['bargain_price_min'],2);
    }

    /**
     * TODO 获取砍价表ID
     * @param int $bargainId $bargainId 砍价产品
     * @param int $bargainUserUid  $bargainUserUid  开启砍价用户编号
     * @param int $status $status  砍价状态 1参与中 2 活动结束参与失败 3活动结束参与成功
     * @return mixed
     */
    public static function getBargainUserTableId($bargainId = 0,$bargainUserUid = 0){
        return self::where('bargain_id',$bargainId)->where('uid',$bargainUserUid)->where('is_del',0)->value('id');
    }

    /**
     * TODO 修改砍价价格
     * @param $id $id 用户参与砍价表编号
     * @param array $price  砍掉的价格
     * @return bool
     */
    public static function setBargainUserPrice($id, $price = array()){
        if(!$id) return false;
        return self::edit($price,$id,'id');
    }

    /**
     * TODO 获取用户的砍价产品
     * @param int $bargainUserUid  $bargainUserUid  开启砍价用户编号
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getBargainUserAll($bargainUserUid = 0,$page = 0,$limit = 20){
       if(!$bargainUserUid) return [];
       $model = new self;
       $model = $model->alias('u');
       $model = $model->field('u.uid,u.is_del,u.bargain_price - u.price as residue_price,u.id,u.bargain_id,u.bargain_price,u.bargain_price_min,u.price,u.status,b.title,b.image,b.stop_time as datatime');
       $model = $model->join('StoreBargain b','b.id=u.bargain_id');
       $model = $model->where('u.uid',$bargainUserUid);
       $model = $model->where('u.is_del',0);
       $model = $model->order('u.id desc');
       if($page) $model = $model->page($page,$limit);
       $list = $model->select();
       if($list) return $list->toArray();
       else return [];
    }

    /**
     * TODO 修改用户砍价状态  支付订单
     * @param int $bargainId $bargainId 砍价产品
     * @param int $bargainUserUid  $bargainUserUid  开启砍价用户编号
     * @return StoreBargainUser|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setBargainUserStatus($bargainId = 0, $bargainUserUid = 0){
        if(!$bargainId || !$bargainUserUid) return false;
        $bargainUserTableId = self::getBargainUserTableId($bargainId,$bargainUserUid);//TODO 获取用户参与砍价表编号  下订单
        if(!$bargainUserTableId) return false;
        $count = self::where('id',$bargainUserTableId)->where('status',1)->count();
        if(!$count) return false;
        $userPrice = (float)self::where('id',$bargainUserTableId)->where('status',1)->value('price');
        $price = self::getBargainUserDiffPriceFloat($bargainUserTableId);//TODO 用户可以砍掉的金额  下订单
        if(bcsub($price,$userPrice,2) > 0) return false;
        return self::where('id',$bargainUserTableId)->where('status',1)->update(['status'=>3]);
    }

    /**
     * 批量修改砍价状态为 砍价失败
     * @return StoreBargainUser|bool
     */
    public static function startBargainUserStatus()
    {
        $currentBargain = self::getBargainUserCurrent(0); //TODO 获取当前用户正在砍价的产品
        $bargainProduct = StoreBargain::validBargainNumber(); //TODO 获取正在开启的砍价产品编号
        $closeBargain = [];
        foreach ($currentBargain as $key=>&$item) { if(!in_array($item,$bargainProduct)) { $closeBargain[] = $item; } }// TODO 获取已经结束的砍价产品
        if(count($closeBargain)) return self::where('status',1)->where('bargain_id','IN',implode(',',$closeBargain))->update(['status'=>2]);
        return true;
    }

    /**
     * TODO 修改砍价状态为 砍价失败
     * @param $uid $uid 当前用户编号
     * @return StoreBargainUser|bool
     */
    public static function editBargainUserStatus($uid){
        $currentBargain = self::getBargainUserCurrent($uid); //TODO 获取当前用户正在砍价的产品
        $bargainProduct = StoreBargain::validBargainNumber(); //TODO 获取正在开启的砍价产品编号
        $closeBargain = [];
        foreach ($currentBargain as $key=>&$item) { if(!in_array($item,$bargainProduct)) { $closeBargain[] = $item; } }// TODO 获取已经结束的砍价产品
        if(count($closeBargain)) return self::where('uid',$uid)->where('status',1)->where('bargain_id','IN',implode(',',$closeBargain))->update(['status'=>2]);
        return true;
    }

    /**
     * TODO 获取当前用户正在砍价的产品
     * @param $uid  $uid 当前用户编号
     * @return array
     */
    public static function getBargainUserCurrent($uid){
        if($uid) return self::where('uid',$uid)->where('is_del',0)->where('status',1)->column('bargain_id');
        else return self::where('is_del',0)->where('status',1)->column('bargain_id');
    }

    /**
     * TODO 获取砍价成功的用户信息
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getBargainUserStatusSuccess(){
        $bargainUser = self::where('status',3)->order('id desc')->field('uid,bargain_price_min,bargain_id')->select();
        if($bargainUser) {
            $bargainUser = $bargainUser->toArray();
            foreach ($bargainUser as $k=>$v){
                $bargainUser[$k]['info'] = User::where('uid',$v['uid'])->value('nickname').'砍价成功了'.$v['bargain_price_min'].'砍到了'.StoreBargain::where('id',$v['bargain_id'])->value('title');
            }
        }
        else{
            $bargainUser[]['info'] = '砍价上线了，快邀请您的好友来砍价';
        }
        return $bargainUser;
    }

    /**
     * TODO  获取用户砍价产品状态
     * @param int $bargainId $bargainId 砍价产品
     * @param int $bargainUserUid  $bargainUserUid  开启砍价用户编号
     * @return bool|mixed
     */
    public static function getBargainUserStatus($bargainId,$bargainUserUid){
        if(!$bargainId || !$bargainUserUid) return false;
        //TODO status  砍价状态 1参与中 2 活动结束参与失败 3活动结束参与成功
        return self::where('bargain_id',$bargainId)->where('uid',$bargainUserUid)->order('add_time DESC')->value('status');
    }

    /**
     * 获取参与的ID
     * @param int $bargainId
     * @param int $uid
     * @param int $status
     * @return array|mixed
     */
    public static function setUserBargain($bargainId = 0,$uid = 0,$status = 1){
        if(!$bargainId || !$uid) return [];
        $bargainIdUserTableId = self::where('bargain_id',$bargainId)->where('uid',$uid)->where('status',$status)->value('id');
        return $bargainIdUserTableId;
    }
}