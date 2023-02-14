<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\activity\bargain;

use app\services\BaseServices;
use app\dao\activity\bargain\StoreBargainUserHelpDao;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;

/**
 *
 * Class StoreBargainUserHelpServices
 * @package app\services\activity
 * @method getHelpAllCount(array $where)
 * @method count(array $where)
 */
class StoreBargainUserHelpServices extends BaseServices
{

    /**
     * StoreBargainUserHelpServices constructor.
     * @param StoreBargainUserHelpDao $dao
     */
    public function __construct(StoreBargainUserHelpDao $dao)
    {
        $this->dao = $dao;
    }

//    /**
//     * TODO 获取用户还剩余的砍价金额
//     * @param int $bargainId $bargainId 砍价商品编号
//     * @param int $bargainUserUid $bargainUserUid 开启砍价用户编号
//     * @return float
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\ModelNotFoundException
//     * @throws \think\exception\DbException
//     */
//    public function getSurplusPrice($bargainId = 0, $bargainUserUid = 0)
//    {
//        /** @var StoreBargainServices $bargainUserService */
//        $bargainUserService = app()->make(StoreBargainServices::class);
//        $bargainUserTableId = $bargainUserService->getBargainUserTableId($bargainId, $bargainUserUid);// TODO 获取用户参与砍价表编号
//        $coverPrice = $bargainUserService->getBargainUserDiffPriceFloat($bargainUserTableId);//TODO 获取用户可以砍掉的金额  好友砍价之后获取砍价金额
//        $alreadyPrice = $bargainUserService->getBargainUserPrice($bargainUserTableId);//TODO 用户已经砍掉的价格 好友砍价之后获取用户已经砍掉的价格
//        $surplusPrice = (float)bcsub((string)$coverPrice, (string)$alreadyPrice, 2);//TODO 用户剩余要砍掉的价格
//        return $surplusPrice;
//    }

    /**
     * 获取砍价帮列表
     * @param int $bid
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getHelpList(int $bid, int $page = 0, int $limit = 0)
    {
        $list = $this->dao->getHelpList($bid, $page, $limit);
        if ($list) {
            $ids = array_unique(array_column($list, 'uid'));
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfos = $userService->getColumn([['uid', 'in', $ids]], 'nickname,avatar', 'uid');
            foreach ($list as $key => &$value) {
                $userInfo = $userInfos[$value['uid']] ?? [];
                if ($userInfo) {
                    $value['nickname'] = $userInfo['nickname'];
                    $value['avatar'] = $userInfo['avatar'];
                } else {
                    $value['nickname'] = '此用户已失效';
                    $value['avatar'] = '';
                }
                unset($value['id']);
            }
        }
        return array_values($list);
    }

    /**
     * 判断是否能砍价
     * @param $bargainId
     * @param $bargainUserTableId
     * @param $uid
     * @return bool
     */
    public function isBargainUserHelpCount($bargainId, $bargainUserTableId, $uid)
    {
        $count = $this->dao->count(['bargain_id' => $bargainId, 'bargain_user_id' => $bargainUserTableId, 'uid' => $uid]);
        if (!$count) return true;
        else return false;
    }

    /**
     * 用户砍价，写入砍价记录
     * @param $uid
     * @param $bargainUserInfo
     * @param $bargainInfo
     * @return false|string
     */
    public function setBargainRecord($uid, $bargainUserInfo, $bargainInfo)
    {
        //已经参与砍价的人数
        $people = $this->dao->count(['bargain_user_id' => $bargainUserInfo['id']]);
        //剩余砍价金额
        $coverPrice = bcsub((string)$bargainUserInfo['bargain_price'], (string)$bargainUserInfo['bargain_price_min'], 2);
        $surplusPrice = bcsub((string)$coverPrice, (string)$bargainUserInfo['price'], 2);//TODO 用户剩余要砍掉的价格
        if (0.00 === (float)$surplusPrice) throw new ApiException(410299);
        if (($bargainInfo['people_num'] - $people) == 1) {
            $price = $surplusPrice;
        } else {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->get($uid);
            $price = $this->randomFloat($surplusPrice, $bargainInfo['people_num'] - $people, $userInfo->add_time == $userInfo->last_time && !$this->dao->count(['uid' => $uid]));
        }
        $allPrice = bcadd((string)$bargainUserInfo['price'], (string)$price, 2);
        if ($bargainUserInfo['uid'] == $uid) {
            $type = 1;
        } else {
            //帮砍次数限制
            $count = $this->dao->count(['uid' => $uid, 'bargain_id' => $bargainInfo['id'], 'type' => 0]);
            if ($count >= $bargainInfo['bargain_num']) throw new ApiException(410310);
            $type = 0;
        }
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $res1 = $bargainUserService->update($bargainUserInfo['id'], ['price' => $allPrice]);
        $res2 = $this->dao->save([
            'uid' => $uid,
            'bargain_id' => $bargainInfo['id'],
            'bargain_user_id' => $bargainUserInfo['id'],
            'price' => $price,
            'add_time' => time(),
            'type' => $type,
        ]);
        $res = $res1 && $res2;
        if (!$res) throw new AdminException(410307);
        return $price;
    }


    /**
     * 随机金额
     * @param $price
     * @param $people
     * @param $type
     * @return string
     */
    public function randomFloat($price, $people, $type = false)
    {
        //按照人数计算保留金额
        $retainPrice = bcmul((string)$people, '0.01', 2);
        //实际剩余金额
        $price = bcsub((string)$price, $retainPrice, 2);
        //计算比例
        if ($type) {
            $percent = '0.5';
        } else {
            $percent = bcdiv((string)mt_rand(20, 50), '100', 2);
        }
        //实际砍掉金额
        $cutPrice = bcmul($price, $percent, 2);
        //如果计算出来为0，默认砍掉0.01
        return $cutPrice != '0.00' ? $cutPrice : '0.01';
    }

    /**
     * 获取砍价商品已砍人数
     * @return array
     */
    public function getNums()
    {
        $nums = $this->dao->getNums();
        $dat = [];
        foreach ($nums as $item) {
            $dat[$item['bargain_user_id']] = $item['num'];
        }
        return $dat;
    }
}
