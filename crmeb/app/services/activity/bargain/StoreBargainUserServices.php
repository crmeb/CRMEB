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

use app\Request;
use app\services\BaseServices;
use app\dao\activity\bargain\StoreBargainUserDao;

/**
 *
 * Class StoreBargainUserServices
 * @package app\services\activity
 * @method getAllCount(array $where)
 * @method count(array $where)
 * @method value(array $where, ?string $field)
 * @method getBargainUserTableId(int $bargainId, int $bargainUserUid)
 * @method update(int $bargainId, array $data)
 * @method getOne(array $where, ?string $field = '*', array $with = [])
 * @method updateBargainStatus(int $id, ?int $status = 3)
 */
class StoreBargainUserServices extends BaseServices
{

    /**
     * StoreBargainUserServices constructor.
     * @param StoreBargainUserDao $dao
     */
    public function __construct(StoreBargainUserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * TODO 根据砍价商品编号获取正在参与人的编号
     * @param int $bargainId $bargainId  砍价商品ID
     * @param int $status $status  状态  1 进行中  2 结束失败  3结束成功
     * @return array
     */
    public function getUserIdList($bargainId = 0, $status = 1)
    {
        $ids = $this->dao->getColumn(['bargain_id' => $bargainId], 'id');
        /** @var StoreBargainUserHelpServices $bargainHelp */
        $bargainHelp = app()->make(StoreBargainUserHelpServices::class);
        return $bargainHelp->getCount([['bargain_user_id', 'in', $ids], ['bargain_id', '=', $bargainId], ['type', '=', 1]]);
    }

    /**
     * 获取砍价
     * @param Request $request
     * @param int $bargainId
     * @param int $bargainUserUid
     * @return mixed
     */
    public function helpCount(Request $request, int $bargainId, int $bargainUserUid)
    {
        $bargainUserTableId = $this->dao->value(['bargain_id' => $bargainId, 'uid' => $bargainUserUid, 'is_del' => 0, 'status' => 1]);//TODO 获取用户参与砍价表编号
        $data['userBargainStatus'] = $this->isBargainUserHelpCount($bargainId, $request->uid(), $bargainUserTableId);
        /** @var StoreBargainUserHelpServices $helpService */
        $helpService = app()->make(StoreBargainUserHelpServices::class);
        if ($bargainUserTableId) {
            $count = $helpService->count(['bargain_user_id' => $bargainUserTableId, 'bargain_id' => $bargainId]);//TODO 获取砍价帮总人数
            $price = $this->getSurplusPrice($bargainUserTableId, 1);//TODO 获取砍价剩余金额
            $alreadyPrice = $this->dao->value(['id' => $bargainUserTableId], 'price');//TODO 用户已经砍掉的价格 好友砍价之后获取用户已经砍掉的价格
            $pricePercent = $this->getSurplusPrice($bargainUserTableId, 2);//TODO 获取砍价进度条
            $data['count'] = $count;
            $data['price'] = $price;
            $data['status'] = $this->dao->value(['id' => $bargainUserTableId], 'status') ?? 0;
            $data['alreadyPrice'] = $alreadyPrice;
            $data['pricePercent'] = $pricePercent > 10 ? $pricePercent : 10;
        } else {
            /** @var StoreBargainServices $bargainService */
            $bargainService = app()->make(StoreBargainServices::class);
            $data['count'] = 0;
            $data['price'] = $bargainService->value(['id' => $bargainId], 'price - min_price');
            $data['status'] = $this->dao->value(['id' => $bargainUserTableId], 'status') ?? 0;
            $data['alreadyPrice'] = 0;
            $data['pricePercent'] = 0;
        }
        return $data;
    }

    /**
     * 获取砍价状态
     * @param int $bargainId
     * @param int $bargainUserUid
     * @param int $bargainUserHelpUid
     * @param $bargainUserTableId
     * @return bool
     */
    public function isBargainUserHelpCount($bargainId, $bargainUserHelpUid, $bargainUserTableId)
    {
        /** @var StoreBargainUserHelpServices $userHelp */
        $userHelp = app()->make(StoreBargainUserHelpServices::class);
        $count = $userHelp->count(['bargain_id' => $bargainId, 'bargain_user_id' => $bargainUserTableId, 'uid' => $bargainUserHelpUid]);
        if (!$count) return true;
        else return false;
    }

    /**
     * 获取砍价剩余金额 或者 砍价百分比
     * @param $bargainUserTableId
     * @param $type
     * @return float
     */
    public function getSurplusPrice($bargainUserTableId, $type)
    {
        $coverPrice = $this->getBargainUserDiffPriceFloat($bargainUserTableId);//TODO 获取用户可以砍掉的金额  好友砍价之后获取砍价金额
        $alreadyPrice = $this->dao->value(['id' => $bargainUserTableId], 'price');//TODO 用户已经砍掉的价格 好友砍价之后获取用户已经砍掉的价格
        if ($type == 1) {
            return (float)bcsub((string)$coverPrice, (string)$alreadyPrice, 2);//TODO 用户剩余要砍掉的价格
        } else {
            if ($alreadyPrice) return (int)bcmul((string)bcdiv((string)$alreadyPrice, (string)$coverPrice, 2), '100', 0);
            else return 100;
        }
    }

    /**
     * 获取用户可以砍掉的金额  好友砍价之后获取砍价金额
     * @param $id
     * @return float
     */
    public function getBargainUserDiffPriceFloat($id)
    {
        $price = $this->dao->get($id);
        return (float)bcsub((string)$price['bargain_price'], (string)$price['bargain_price_min'], 2);
    }

    /**
     * 添加砍价信息
     * @param int $bargainId
     * @param int $bargainUserUid
     * @param array $bargainInfo
     * @return mixed
     */
    public function setBargain(int $bargainId, int $bargainUserUid, array $bargainInfo)
    {
        $data['bargain_id'] = $bargainId;
        $data['uid'] = $bargainUserUid;
        $data['bargain_price_min'] = $bargainInfo['min_price'];
        $data['bargain_price'] = $bargainInfo['price'];
        $data['price'] = 0;
        $data['status'] = 1;
        $data['is_del'] = 0;
        $data['add_time'] = time();
        return $this->dao->save($data);
    }


    /**
     * 修改砍价状态
     * @param $uid
     * @return bool
     */
    public function editBargainUserStatus($uid)
    {
        $currentBargain = $this->dao->getColumn(['uid' => $uid, 'is_del' => 0, 'status' => 1], 'bargain_id');
        /** @var StoreBargainServices $bargainService */
        $bargainService = app()->make(StoreBargainServices::class);
        $bargainProduct = $bargainService->validWhere()->column('id');
        $closeBargain = [];
        foreach ($currentBargain as $key => &$item) {
            if (!in_array($item, $bargainProduct)) {
                $closeBargain[] = $item;
            }
        }// TODO 获取已经结束的砍价商品
        if (count($closeBargain)) $this->dao->update([['uid', '=', $uid], ['status', '=', 1], ['bargain_id', 'in', implode(',', $closeBargain)]], ['status' => 2]);
    }


    /**
     * TODO 获取用户的砍价商品
     * @param int $bargainUserUid $bargainUserUid  开启砍价用户编号
     * @return array
     */
    public function getBargainUserAll(int $bargainUserUid)
    {
        if (!$bargainUserUid) return [];
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->userAll($bargainUserUid, $page, $limit);
        foreach ($list as &$item) {
            $item['residue_price'] = bcsub((string)$item['bargain_price'], (string)$item['price'], 2);
        }
        return $list;
    }

    /**
     * 取消砍价
     * @param $bargainId
     * @param $uid
     * @return mixed
     */
    public function cancelBargain($bargainId, $uid)
    {
        $status = $this->dao->getBargainUserStatus($bargainId, $uid);
        if ($status != 1) return app('json')->fail(100020);
        $id = $this->dao->value(['bargain_id' => $bargainId, 'uid' => $uid, 'is_del' => 0], 'id');
        return $this->dao->update($id, ['is_del' => 1, 'status' => 2]);
    }

    /**
     * 下架删除砍价时修改砍价状态 砍价失败
     * @param $bargain_id
     */
    public function userBargainStatusFail($bargain_id, $is_true)
    {
        if ($is_true) {
            $this->dao->delete(['bargain_id' => $bargain_id, 'status' => 1]);
            /** @var StoreBargainUserHelpServices $service */
            $service = app()->make(StoreBargainUserHelpServices::class);
            $service->delete(['bargain_id' => $bargain_id]);
        } else {
            $this->dao->update(['bargain_id' => $bargain_id, 'status' => 1], ['status' => 2]);
        }

    }

    /**
     * 砍价列表
     * @param $where
     * @return array
     */
    public function bargainUserList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->bargainUserList($where, $page, $limit);
        $count = $this->dao->count($where);
        /** @var StoreBargainUserHelpServices $bargainUserHelpService */
        $bargainUserHelpService = app()->make(StoreBargainUserHelpServices::class);
        $nums = $bargainUserHelpService->getNums();
        foreach ($list as &$item) {
            $item['num'] = $item['people_num'] - ($nums[$item['id']] ?? 0);
            $item['already_num'] = $nums[$item['id']] ?? 0;
            $item['now_price'] = bcsub((string)$item['bargain_price'], (string)$item['price'], 2);
            $item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', (int)$item['add_time']) : '';
            $item['datatime'] = $item['datatime'] ? date('Y-m-d H:i:s', (int)$item['datatime']) : '';
        }
        return compact('list', 'count');
    }
}
