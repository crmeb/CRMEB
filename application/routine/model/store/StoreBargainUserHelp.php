<?php
namespace app\routine\model\store;

use app\routine\model\user\User;
use basic\ModelBasic;
use traits\ModelTrait;

/**
 * 砍价帮砍Model
 * Class StoreBargainUser
 * @package app\routine\model\store
 */
class StoreBargainUserHelp extends ModelBasic
{
    use ModelTrait;

    /**
     * 获取砍价帮
     * @param int $bargainUserId
     * @return array
     */
    public static function getList($bargainUserId = 0,$limit = 15){
         if(!$bargainUserId) return [];
         $list = self::where('bargain_user_id',$bargainUserId)->limit($limit)->column('uid,price','id');
         if($list){
             foreach ($list as $k=>$v){
                 $userInfo = self::getBargainUserHelpUserInfo($v['uid']);
                 $list[$k]['nickname'] = $userInfo[$v['uid']]['nickname'];
                 $list[$k]['avatar'] = $userInfo[$v['uid']]['avatar'];
             }
         }
         return $list;
    }

    /**
     * 获取用的昵称和头像
     * @param int $uid
     * @return array
     */
    public static function getBargainUserHelpUserInfo($uid = 0){
          if(!$uid) return [];
          $userInfo = User::where('uid',$uid)->column('nickname,avatar','uid');
          return $userInfo;
    }

    /**
     * 帮忙砍价
     * @param int $bargainId
     * @param int $bargainUserId
     * @param int $uid
     * @return bool|object
     */
    public static function setBargainUserHelp($bargainId = 0,$bargainUserId = 0,$uid = 0){
        if(!self::isBargainUserHelpCount($bargainId,$bargainUserId,$uid) || !$bargainId || !$bargainUserId || !$uid || !StoreBargain::validBargain($bargainId) || !StoreBargainUser::be(['id'=>$bargainId,'uid'=>$bargainUserId,'status'=>1])) return false;
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
        $priceSection = StoreBargain::getBargainMaxMinPrice($bargainId); //获取砍价的价格区间
        $coverPrice = StoreBargainUser::getBargainUserDiffPrice($bargainId,$bargainUserId);//用户可以砍掉的金额
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//用户已经砍掉的价格
        $surplusPrice = (float)bcsub($coverPrice,$alreadyPrice,2);//用户剩余要砍掉的价格
        $data['uid'] = $uid;
        $data['bargain_id'] = $bargainId;
        $data['bargain_user_id'] = $bargainUserTableId;
        $data['price'] = mt_rand($priceSection['bargain_min_price'],$priceSection['bargain_max_price']);
        $data['add_time'] = time();
        if($data['price'] > $surplusPrice) $data['price'] = $surplusPrice;
        $price = bcadd($alreadyPrice,$data['price'],0);
        $bargainUserData['price'] = $price;
        self::beginTrans();
        $res1 = StoreBargainUser::setBargainUserPrice($bargainUserTableId,$bargainUserData);
        $res2 = self::set($data);
        $res = $res1 && $res2;
        self::checkTrans($res);
        if($res) return $data;
        else return $res;
    }

    /**
     * 判断用户是否还可以砍价
     * @param int $bargainId
     * @param int $bargainUserUid
     * @param int $bargainUserHelpUid
     * @return bool
     */
    public static function isBargainUserHelpCount($bargainId = 0,$bargainUserUid = 0,$bargainUserHelpUid = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid);
        $bargainNum = StoreBargain::getBargainNum($bargainUserTableId);
        $count = self::where('bargain_id',$bargainId)->where('bargain_user_id',$bargainUserTableId)->where('uid',$bargainUserHelpUid)->count();
        if($count < $bargainNum) return true;
        else return false;

    }

    /**
     * 获取砍价帮总人数
     * @param int $bargainId
     * @param int $bargainUserId
     * @return int|string
     */
    public static function getBargainUserHelpPeopleCount($bargainId = 0,$bargainUserId = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
        if($bargainUserTableId) return self::where('bargain_user_id',$bargainUserTableId)->where('bargain_id',$bargainId)->count();
        else return 0;
    }

    /**
     * 获取用户还剩余的砍价金额
     * @param int $bargainId
     * @param int $bargainUserId
     * @return float
     */
    public static function getSurplusPrice($bargainId = 0,$bargainUserId = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
        $coverPrice = StoreBargainUser::getBargainUserDiffPrice($bargainId,$bargainUserId);//用户可以砍掉的金额
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//用户已经砍掉的价格
        $surplusPrice = (float)bcsub($coverPrice,$alreadyPrice,2);//用户剩余要砍掉的价格
        return $surplusPrice;
    }

    /**
     * 获取砍价进度条
     * @param int $bargainId
     * @param int $bargainUserId
     * @return string
     */
    public static function getSurplusPricePercent($bargainId = 0,$bargainUserId = 0){
        $coverPrice = StoreBargainUser::getBargainUserDiffPrice($bargainId,$bargainUserId);//用户可以砍掉的金额
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//用户已经砍掉的价格
        return bcmul(bcdiv($alreadyPrice,$coverPrice,2),100,0);
    }
}

