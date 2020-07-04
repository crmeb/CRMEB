<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/18
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\db\Where;

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

    public static function getIssueCouponList($uid, $limit, $page = 0, $type = 0, $product_id = 0)
    {
        $model1 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title')
            ->order('B.sort DESC,A.id DESC');
        $model2 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title')
            ->order('B.sort DESC,A.id DESC');
        $model3 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title')
            ->order('B.sort DESC,A.id DESC');

        if ($uid) {
            $model1->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model2->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model3->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);
        }

        $lst1 = $lst2 = $lst3 = [];
        if ($type) {
            if ($product_id) {
                //商品券
                $lst1 = $model1->where('B.type', 2)
                    ->where('is_give_subscribe', 0)
                    ->where('is_full_give', 0)
                    ->whereFindinSet('B.product_id', $product_id)
                    ->select()
                    ->hidden(['is_del', 'status'])
                    ->toArray();
                //品类券
                $cate_id = StoreProduct::where('id', $product_id)->value('cate_id');
                $category = explode(',', $cate_id);
                foreach ($category as $value) {
                    $temp[] = StoreCategory::where('id', $value)->value('pid');
                }
                $temp = array_unique($temp);
                $cate_id = $cate_id . ',' . implode(',', $temp);

                $lst2 = $model2->where('B.type', 1)
                    ->where('is_give_subscribe', 0)
                    ->where('is_full_give', 0)
                    ->where('B.category_id', 'in', $cate_id)
                    ->select()
                    ->hidden(['is_del', 'status'])
                    ->toArray();
            }
        } else {
            //通用券
            $lst3 = $model3->where('B.type', 0)
                ->where('is_give_subscribe', 0)
                ->where('is_full_give', 0)
                ->select()
                ->hidden(['is_del', 'status'])
                ->toArray();
        }
        $list = array_merge($lst1, $lst2, $lst3);
        $list = array_unique_fb($list);
        if ($page) $list = array_slice($list, ((int)$page - 1) * $limit, $limit);
        foreach ($list as $k => $v) {
            $v['is_use'] = $uid ? isset($v['used']) : false;
            if (!$v['end_time']) {
                $v['start_time'] = '';
                $v['end_time'] = '不限时';
            } else {
                $v['start_time'] = date('Y/m/d', $v['start_time']);
                $v['end_time'] = $v['end_time'] ? date('Y/m/d', $v['end_time']) : date('Y/m/d', time() + 86400);
            }
            $v['coupon_price'] = $v['coupon_price'];
            $list[$k] = $v;
        }

        if ($list)
            return $list;
        else
            return [];
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
        return StoreCoupon::where('id', $cid)->value('title');
    }


}