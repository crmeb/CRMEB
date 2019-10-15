<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/18
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 发布优惠券Model
 * Class StoreCouponIssue
 * @package app\models\store
 */
class StoreCouponIssue extends BaseModel
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
    protected $name = 'store_coupon_issue';

    use ModelTrait;

    public function used()
    {
        return $this->hasOne(StoreCouponIssueUser::class, 'issue_coupon_id', 'id')->field('issue_coupon_id');
    }

    public static function getIssueCouponList($uid, $limit, $page = 0)
    {
        $model = self::validWhere('A')->alias('A')->join('__store_coupon__ B', 'A.cid = B.id')
            ->field('A.*,B.coupon_price,B.use_min_price')->order('B.sort DESC,A.id DESC');
        if ($page) $list = $model->page((int)$page, (int)$limit);
        else $list = $model->limit($limit);

        if ($uid)
            $model->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

        $list = $list->select();
        foreach ($list as &$v) {
            $v['is_use'] = $uid ? isset($v->used) : false;
//            if (!$v['is_use']) {
//                $v['is_use'] = $v['remain_count'] <= 0 && !$v['is_permanent'] ? false : $v['is_use'];
//            }
            if ($v['end_time']) {
                $v['start_time'] = date('Y/m/d', $v['start_time']);
                $v['end_time'] = $v['end_time'] ? date('Y/m/d', $v['end_time']) : date('Y/m/d', time() + 86400);
            }
            $v['coupon_price'] = (float)$v['coupon_price'];
        }

        return $list->hidden(['is_del', 'status', 'used', 'add_time'])->toArray();
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public static function validWhere($prefix = '')
    {
        $model = new self;
        if ($prefix) {
            $model->alias($prefix);
            $prefix .= '.';
        }
        $newTime = time();
        return $model->where("{$prefix}status", 1)
            ->where(function ($query) use ($newTime, $prefix) {
                $query->where(function ($query) use ($newTime, $prefix) {
                    $query->where("{$prefix}start_time", '<', $newTime)->where("{$prefix}end_time", '>', $newTime);
                })->whereOr(function ($query) use ($prefix) {
                    $query->where("{$prefix}start_time", 0)->where("{$prefix}end_time", 0);
                });
            })->where("{$prefix}is_del", 0)->where("{$prefix}remain_count > 0 OR {$prefix}is_permanent = 1");
    }


    public static function issueUserCoupon($id, $uid)
    {
        $issueCouponInfo = self::validWhere()->where('id', $id)->find();
        if (!$issueCouponInfo) return self::setErrorInfo('领取的优惠劵已领完或已过期!');
        if (StoreCouponIssueUser::be(['uid' => $uid, 'issue_coupon_id' => $id]))
            return self::setErrorInfo('已领取过该优惠劵!');
        if ($issueCouponInfo['remain_count'] <= 0 && !$issueCouponInfo['is_permanent']) return self::setErrorInfo('抱歉优惠卷已经领取完了！');
        self::beginTrans();
        $res1 = false != StoreCouponUser::addUserCoupon($uid, $issueCouponInfo['cid']);
        $res2 = false != StoreCouponIssueUser::addUserIssue($uid, $id);
        $res3 = true;
        if ($issueCouponInfo['total_count'] > 0) {
            $issueCouponInfo['remain_count'] -= 1;
            $res3 = false !== $issueCouponInfo->save();
        }
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 优惠券名称
     * @param $id
     * @return mixed
     */
    public static function getIssueCouponTitle($id)
    {
        $cid = self::where('id', $id)->value('cid');
        return StoreCoupon::where('id',$cid)->value('title');
    }

}