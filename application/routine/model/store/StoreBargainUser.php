<?php
namespace app\routine\model\store;

use app\routine\model\user\User;
use basic\ModelBasic;
use traits\ModelTrait;

/**
 * 参与砍价Model
 * Class StoreBargainUser
 * @package app\routine\model\store
 */
class StoreBargainUser extends ModelBasic
{
    use ModelTrait;

    /**
     * 根据砍价产品获取正在参与的用户头像
     * @param array $bargain
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
     * 根据砍价产品ID获取正在参与人的uid
     * @param int $bargainId $bargainId  砍价产品ID
     * @param int $status   $status  状态  1 进行中  2 结束失败  3结束成功
     * @return array
     */
    public static function getUserIdList($bargainId = 0,$status = 1){
        if(!$bargainId) return [];
        return self::where('bargain_id',$bargainId)->where('status',$status)->column('uid','id');
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



    /**
     * 添加一条砍价记录
     * @param int $bargainId
     * @param int $uid
     * @return bool|object
     */
    public static function setBargain($bargainId = 0,$uid = 0){
        if(!$bargainId || !$uid || !StoreBargain::validBargain($bargainId) || self::be(['bargain_id'=>$bargainId,'uid'=>$uid,'status'=>1])) return false;
        $data['bargain_id'] = $bargainId;
        $data['uid'] = $uid;
        $data['bargain_price_min'] = StoreBargain::where('id',$bargainId)->value('min_price');
        $data['bargain_price'] = StoreBargain::where('id',$bargainId)->value('price');
        $data['price'] = 0;
        $data['status'] = 1;
        $data['add_time'] = time();
        return self::set($data);
    }


    /**
     * 判断当前人是否已经参与砍价
     * @param int $bargainId
     * @param int $uid
     * @return bool|mixed
     */
    public static function isBargainUser($bargainId = 0,$uid = 0){
        if(!$bargainId || !$uid || !StoreBargain::validBargain($bargainId)) return false;
        return self::where('bargain_id',$bargainId)->where('uid',$uid)->value('uid');
    }

    /**
     * 获取用户砍掉的价格
     * @param int $bargainUserId
     * @return mixed
     */
    public static function getBargainUserPrice($bargainUserId = 0){
        return (float)self::where('id',$bargainUserId)->value('price');
    }


    /**
     * 获取用户可以砍掉的价格
     * @param int $bargainUserId
     * @return string
     */
    public static function getBargainUserDiffPrice($bargainId = 0,$bargainUserId = 0){
        $price = self::where('bargain_id',$bargainId)->where('uid',$bargainUserId)->field('bargain_price,bargain_price_min')->find()->toArray();
        return (float)bcsub($price['bargain_price'],$price['bargain_price_min'],0);
    }

    /**
     * 获取砍价表ID
     * @param int $bargainId
     * @param int $bargainUserId
     * @return mixed
     */
    public static function getBargainUserTableId($bargainId = 0,$bargainUserId = 0,$status = 1){
        return self::where('bargain_id',$bargainId)->where('uid',$bargainUserId)->where('status',$status)->value('id');
    }

    /**
     * 修改砍价价格
     * @param int $bargainUserTableId
     * @param array $price
     * @return $this|bool
     */
    public static function setBargainUserPrice($bargainUserTableId = 0, $price = array()){
        if(!$bargainUserTableId) return false;
        return self::where('id',$bargainUserTableId)->update($price);
    }

    /**
     * 获取用户的砍价产品
     * @param $uid
     * @return array
     */
    public static function getBargainUserAll($uid = 0){
       if(!$uid) return [];
       $model = new self;
       $model = $model->alias('u');
       $model = $model->field('u.id,u.bargain_id,u.bargain_price,u.bargain_price_min,u.price,u.status,b.title,b.image,b.stop_time');
       $model = $model->join('StoreBargain b','b.id=u.bargain_id');
        $model = $model->where('u.uid',$uid);
       $model = $model->order('u.id desc');
       $list = $model->select();
       if($list) return $list->toArray();
       else return [];
    }

    /**
     * 修改用户砍价状态  支付订单
     * @param int $bargainId
     * @param int $uid
     * @return $this|bool
     */
    public static function setBargainUserStatus($bargainId = 0, $uid = 0){
        $count = self::where('uid',$uid)->where('bargain_id',$bargainId)->where('status',1)->count();
        if(!$count) return false;
        $userPrice = (float)self::where('uid',$uid)->where('bargain_id',$bargainId)->where('status',1)->value('price');
        $price = self::getBargainUserDiffPrice($bargainId,$uid);
        if(bcsub($price,$userPrice,0) > 0) return false;
        return self::where('uid',$uid)->where('bargain_id',$bargainId)->where('status',1)->update(['status'=>3]);
    }

    /**
     * 修改砍价状态为 砍价失败
     * @param int $bargainUserTableId
     * @return $this|bool
     */
    public static function editBargainUserStatus($bargainUserTableId = 0){
        if(!$bargainUserTableId) return false;
        return self::where('id',$bargainUserTableId)->update(['status'=>2]);
    }

    /**
     * 获取砍价成功的用户信息
     * @return array|false|\PDOStatement|string|\think\Collection
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
}