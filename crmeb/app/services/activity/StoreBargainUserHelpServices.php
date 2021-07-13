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
declare (strict_types=1);

namespace app\services\activity;

use app\Request;
use app\services\BaseServices;
use app\dao\activity\StoreBargainUserHelpDao;
use app\services\user\UserServices;
use think\exception\ValidateException;

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

    /**
     * TODO 获取用户还剩余的砍价金额
     * @param int $bargainId $bargainId 砍价商品编号
     * @param int $bargainUserUid $bargainUserUid 开启砍价用户编号
     * @return float
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSurplusPrice($bargainId = 0, $bargainUserUid = 0)
    {
        /** @var StoreBargainServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainServices::class);
        $bargainUserTableId = $bargainUserService->getBargainUserTableId($bargainId, $bargainUserUid);// TODO 获取用户参与砍价表编号
        $coverPrice = $bargainUserService->getBargainUserDiffPriceFloat($bargainUserTableId);//TODO 获取用户可以砍掉的金额  好友砍价之后获取砍价金额
        $alreadyPrice = $bargainUserService->getBargainUserPrice($bargainUserTableId);//TODO 用户已经砍掉的价格 好友砍价之后获取用户已经砍掉的价格
        $surplusPrice = (float)bcsub($coverPrice, $alreadyPrice, 2);//TODO 用户剩余要砍掉的价格
        return $surplusPrice;
    }

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
     * 获取砍价金额
     * @param Request $request
     * @param $bargainId
     * @param $bargainUserUid
     * @return array
     */
    public function getPrice(Request $request, $bargainId, $bargainUserUid)
    {
        if (!$bargainId || !$bargainUserUid) throw new ValidateException('参数错误');
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $bargainUserTableId = $bargainUserService->value(['bargain_id' => $bargainId, 'uid' => $bargainUserUid, 'is_del' => 0]);//TODO 获取用户参与砍价表编号
        $price = $this->dao->value(['uid' => $request->uid(), 'bargain_id' => $bargainId, 'bargain_user_id' => $bargainUserTableId], 'price');
        return ['price' => $price];
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
//        /** @var StoreBargainServices $bargainService */
//        $bargainService = app()->make(StoreBargainServices::class);
//        $bargainNum = $bargainService->value(['id' => $bargainId], 'bargain_num');//TODO 获取每个人可以砍价几次
        $count = $this->dao->count(['bargain_id' => $bargainId, 'bargain_user_id' => $bargainUserTableId, 'uid' => $uid]);
        if (!$count) return true;
        else return false;
    }

    /**
     * TODO 帮忙砍价
     * @param int $bargainId
     * @param int $bargainUserTableId
     * @param int $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setBargainUserHelp($bargainId, $bargainUserTableId, $uid)
    {
        /** @var StoreBargainServices $bargainService */
        $bargainService = app()->make(StoreBargainServices::class);
        $bargainInfo = $bargainService->get($bargainId);

        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $alreadyPrice = $bargainUserService->value(['id' => $bargainUserTableId], 'price');//TODO 用户已经砍掉的价格

        $people = $this->dao->count(['bargain_user_id' => $bargainUserTableId]);//已经参与砍价的人数

        $coverPrice = bcsub((string)$bargainInfo->price, (string)$bargainInfo->min_price, 2);
        $surplusPrice = bcsub((string)$coverPrice, (string)$alreadyPrice, 2);//TODO 用户剩余要砍掉的价格
        if (0.00 === (float)$surplusPrice) return false;

        $data['uid'] = $uid;
        $data['bargain_id'] = $bargainId;
        $data['bargain_user_id'] = $bargainUserTableId;
        if (($bargainInfo->people_num - $people) == 1) {
            $data['price'] = $surplusPrice;
        } else {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->get($uid);
            $data['price'] = $this->randomFloat($surplusPrice, $userInfo->add_time == $userInfo->last_time && !$this->dao->count(['uid' => $uid]));
        }
        $data['add_time'] = time();
        if ($bargainUserService->value(['id' => $bargainUserTableId], 'uid') == $uid) {
            $data['type'] = 1;
        } else {
            //帮砍次数限制
            $count = $this->dao->count(['uid' => $uid, 'bargain_id' => $bargainId, 'type' => 0]);
            if ($count >= $bargainInfo->bargain_num) throw new ValidateException('您不能再帮砍此件商品');
            $data['type'] = 0;
        }

        $price = bcadd((string)$alreadyPrice, (string)$data['price'], 2);
        $bargainUserData['price'] = $price;
        return $this->transaction(function () use ($bargainUserService, $bargainUserTableId, $bargainUserData, $data) {
            $res1 = $bargainUserService->update($bargainUserTableId, $bargainUserData);
            $res2 = $this->dao->save($data);
            $res = $res1 && $res2;
            if (!$res) throw new ValidateException('砍价失败');
            return $res;
        });

    }

    /**
     * 随机金额
     * @param $price
     * @param $type
     * @return string
     */
    public function randomFloat($price, $type = false)
    {
        if ($type) {
            $percent = 0.5;
        } else {
            $percent = bcdiv((string)mt_rand(20, 50), '100', 2);
        }
        return bcmul((string)$price, (string)$percent, 2);
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
