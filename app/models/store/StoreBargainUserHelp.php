<?php
namespace app\models\store;

use app\models\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 砍价帮砍Model
 * Class StoreBargainUserHelp
 * @package app\models\store
 */
class StoreBargainUserHelp extends BaseModel
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
    protected $name = 'store_bargain_user_help';

    use ModelTrait;

    /**
     * TODO 获取砍价帮
     * @param $bargainUserTableId
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getList($bargainUserTableId,$page = 1,$limit = 15){
         if(!$bargainUserTableId) return [];
         if($page) $list = self::where('bargain_user_id',$bargainUserTableId)->order('add_time desc')->page($page,$limit)->column("uid,price,from_unixtime(add_time,'%Y-%m-%d %H:%i:%s') as add_time",'id');
         else $list = self::where('bargain_user_id',$bargainUserTableId)->order('add_time desc')->column("uid,price,from_unixtime(add_time,'%Y-%m-%d %H:%i:%s') as add_time",'id');
         if($list){
             foreach ($list as $key=>&$value){
                 $userInfo = User::getUserInfo($value['uid'],'nickname,avatar');
                 if($userInfo){
                     $value['nickname'] = $userInfo['nickname'];
                     $value['avatar'] = $userInfo['avatar'];
                 }else{
                     $value['nickname'] = '此用户已失效';
                     $value['avatar'] = '';
                 }
                 unset($value['uid']);
                 unset($value['id']);
             }
         }
         return array_values($list);
    }

    /**
     * TODO 帮忙砍价
     * @param int $bargainId
     * @param int $bargainUserUid
     * @param int $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setBargainUserHelp($bargainId = 0,$bargainUserUid = 0,$uid = 0){
        if(!$bargainId || !$bargainUserUid || !$uid || !StoreBargain::validBargain($bargainId) || !StoreBargainUser::be(['bargain_id'=>$bargainId,'uid'=>$bargainUserUid,'status'=>1,'is_del'=>0])) return false;
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid);//TODO 获取 用户参与砍价表编号
        $priceSection = StoreBargain::getBargainMaxMinPrice($bargainId); //TODO 获取随机砍掉的价格区间
        $coverPrice = StoreBargainUser::getBargainUserDiffPriceFloat($bargainUserTableId);//TODO 用户可以砍掉的金额 好友砍价之前获取可以砍价金额
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//TODO 用户已经砍掉的价格
        $surplusPrice = (float)bcsub($coverPrice,$alreadyPrice,2);//TODO 用户剩余要砍掉的价格
        if(0.00 === (float)bcsub($surplusPrice,0,2)) return false;
        $data['uid'] = $uid;
        $data['bargain_id'] = $bargainId;
        $data['bargain_user_id'] = $bargainUserTableId;
        $data['price'] = self::randomFloat($priceSection['bargain_min_price'],$priceSection['bargain_max_price']);
        $data['add_time'] = time();
        if(0.00 === (float)$data['price']) $data['price'] = self::randomFloat($priceSection['bargain_min_price'],$priceSection['bargain_max_price']);
        if($data['price'] > $surplusPrice) $data['price'] = $surplusPrice;
        $price = bcadd($alreadyPrice,$data['price'],2);
        $bargainUserData['price'] = $price;
        self::beginTrans();
        $res1 = StoreBargainUser::setBargainUserPrice($bargainUserTableId,$bargainUserData);
        $res2 = self::create($data);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

    /**
     * TODO 获取俩个数之间的随机数
     * @param int $min  $min 最小数
     * @param int $max $max 最大数
     * @return string
     */
    public static function randomFloat($min = 0,$max = 1){
        $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return sprintf("%.2f",$num);
    }

    /**
     * TODO 判断用户是否还可以砍价
     * @param int $bargainId $bargainId 砍价产品编号
     * @param int $bargainUserUid $bargainUserUid 开启砍价用户编号
     * @param int $bargainUserHelpUid $bargainUserUid 当前用户编号
     * @return bool
     * @throws \think\Exception
     */
    public static function isBargainUserHelpCount($bargainId = 0,$bargainUserUid = 0,$bargainUserHelpUid = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid);
        $bargainNum = StoreBargain::getBargainNum($bargainId);//TODO 获取每个人可以砍价几次
        $count = self::where('bargain_id',$bargainId)->where('bargain_user_id',$bargainUserTableId)->where('uid',$bargainUserHelpUid)->count();//TODO 获取当前用户砍价了几次
        if($count < $bargainNum) return true;
        else return false;
    }

    /**
     * TODO 获取砍价帮总人数
     * @param int $bargainId  $bargainId 砍价产品编号
     * @param int $bargainUserUid  $bargainUserUid 开启砍价用户编号
     * @return int|string
     * @throws \think\Exception
     */
    public static function getBargainUserHelpPeopleCount($bargainId = 0,$bargainUserUid = 0){
        if(!$bargainId || !$bargainUserUid) return 0;
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid);//TODO 获取用户参与砍价表编号
        if($bargainUserTableId) return self::where('bargain_user_id',$bargainUserTableId)->where('bargain_id',$bargainId)->count();
        else return 0;
    }

    /**
     * TODO 获取用户还剩余的砍价金额
     * @param int $bargainId  $bargainId 砍价产品编号
     * @param int $bargainUserUid $bargainUserUid 开启砍价用户编号
     * @return float
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSurplusPrice($bargainId = 0,$bargainUserUid = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid);// TODO 获取用户参与砍价表编号
        $coverPrice = StoreBargainUser::getBargainUserDiffPriceFloat($bargainUserTableId);//TODO 获取用户可以砍掉的金额  好友砍价之后获取砍价金额
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//TODO 用户已经砍掉的价格 好友砍价之后获取用户已经砍掉的价格
        $surplusPrice = (float)bcsub($coverPrice,$alreadyPrice,2);//TODO 用户剩余要砍掉的价格
        return $surplusPrice;
    }

    /**
     * TODO 获取砍价进度条
     * @param int $bargainId  $bargainId 砍价产品编号
     * @param int $bargainUserUid  $bargainUserUid 开启砍价用户编号
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSurplusPricePercent($bargainId = 0,$bargainUserUid = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid); // TODO  获取用户参与砍价表编号 计算进度条
        $coverPrice = StoreBargainUser::getBargainUserDiffPriceFloat($bargainUserTableId);//TODO 用户可以砍掉的金额 计算进度条
        $alreadyPrice = StoreBargainUser::getBargainUserPrice($bargainUserTableId);//TODO 用户已经砍掉的价格 计算进度条
        if($alreadyPrice) return (int)bcmul(bcdiv($alreadyPrice,$coverPrice,2),100,0);
        else return 100;
    }

    /**
     * TODO 获取用户砍掉的金额
     * @param int $bargainId $bargainId 砍价产品编号
     * @param int $bargainUserTableId  $bargainUserTableId 用户参与砍价表编号
     * @param int $uid $uid 帮忙砍价人编号
     * @param string $field
     * @return bool|mixed
     */
    public static function getBargainUserBargainPrice($bargainId = 0,$bargainUserTableId = 0,$uid = 0,$field = 'price'){
       if(!$bargainId || !$bargainUserTableId || !$uid) return false;
       return self::where('uid',$uid)->where('bargain_id',$bargainId)->where('bargain_user_id',$bargainUserTableId)->value($field);
    }

    /**
     * TODO 获取用的昵称和头像
     * @param int $uid
     * @return array
     */
    public static function getBargainUserHelpUserInfo($uid = 0){
        if(!$uid) return [];
        $userInfo = User::where('uid',$uid)->column('nickname,avatar','uid');
        return $userInfo;
    }
}

