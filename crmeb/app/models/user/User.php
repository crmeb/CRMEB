<?php


namespace app\models\user;

use app\models\store\StoreOrder;
use app\models\store\StoreProduct;
use crmeb\services\SystemConfigService;
use think\facade\Session;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;

/**
 * TODO 用户Model
 * Class User
 * @package app\models\user
 */
class User extends BaseModel
{
    use JwtAuthModelTrait;
    use ModelTrait;

    protected $pk = 'uid';

    protected $name = 'user';

    protected $insert = ['add_time', 'add_ip', 'last_time', 'last_ip'];

    protected $hidden = [
        'add_ip', 'add_time', 'account', 'clean_time', 'last_ip', 'last_time', 'pwd', 'status', 'mark', 'pwd'
    ];

    protected function setAddTimeAttr($value)
    {
        return time();
    }

    protected function setAddIpAttr($value)
    {
        return app('request')->ip();
    }

    protected function setLastTimeAttr($value)
    {
        return time();
    }

    protected function setLastIpAttr($value)
    {
        return app('request')->ip();
    }

    public static function setWechatUser($wechatUser, $spread_uid = 0)
    {
        return self::create([
            'account' => 'wx' . $wechatUser['uid'] . time(),
            'pwd' => md5(123456),
            'nickname' => $wechatUser['nickname'] ?: '',
            'avatar' => $wechatUser['headimgurl'] ?: '',
            'spread_uid' => $spread_uid,
            'add_time' => time(),
            'add_ip' => app('request')->ip(),
            'last_time' => time(),
            'last_ip' => app('request')->ip(),
            'uid' => $wechatUser['uid'],
            'user_type' => 'wechat'
        ]);
    }


    /**
     * TODO 获取会员是否被清除过的时间
     * @param $uid
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCleanTime($uid)
    {
        $user = self::where('uid', $uid)->field(['add_time', 'clean_time'])->find();
        if (!$user) return 0;
        return $user['clean_time'] ? $user['clean_time'] : $user['add_time'];
    }

    /**
     * 更新用户信息
     * @param $wechatUser 用户信息
     * @param $uid 用户uid
     * @return bool|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function updateWechatUser($wechatUser, $uid)
    {
        $userInfo = self::where('uid', $uid)->find();
        if (!$userInfo) return;
        //增加成为分销权限
        if (!$userInfo->is_promoter) {
            $price = StoreOrder::where(['paid' => 1, 'refund_status' => 0, 'uid' => $uid])->sum('pay_price');
            $status = is_brokerage_statu($price);
        } else {
            $status = false;
        }
        if ($userInfo->spread_uid) {
            return self::edit([
                'nickname' => $wechatUser['nickname'] ?: '',
                'avatar' => $wechatUser['headimgurl'] ?: '',
                'is_promoter' => $status ? 1 : $userInfo->is_promoter,
                'login_type' => isset($wechatUser['login_type']) ? $wechatUser['login_type'] : $userInfo->login_type,
            ], $uid, 'uid');
        } else {
            $data = [
                'nickname' => $wechatUser['nickname'] ?: '',
                'avatar' => $wechatUser['headimgurl'] ?: '',
                'is_promoter' => $status ? 1 : $userInfo->is_promoter,
                'login_type' => isset($wechatUser['login_type']) ? $wechatUser['login_type'] : $userInfo->login_type,
                'spread_uid' => 0,
                'spread_time' => 0,
                'last_time' => time(),
                'last_ip' => request()->ip(),
            ];
            //TODO 获取后台分销类型
            $storeBrokerageStatus = sys_config('store_brokerage_statu');
            $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
            if (isset($wechatUser['code']) && $wechatUser['code'] && $wechatUser['code'] != $uid && $uid != self::where('uid', $wechatUser['code'])->value('spread_uid')) {
                if ($storeBrokerageStatus == 1) {
                    $spreadCount = self::where('uid', $wechatUser['code'])->count();
                    if ($spreadCount) {
                        $spreadInfo = self::where('uid', $wechatUser['code'])->find();
                        if ($spreadInfo->is_promoter) {
                            //TODO 只有扫码才可以获得推广权限
//                            if(isset($wechatUser['isPromoter'])) $data['is_promoter'] = $wechatUser['isPromoter'] ? 1 : 0;
                        }
                    }
                }
                $data['spread_uid'] = $wechatUser['code'];
                $data['spread_time'] = time();
            }
            return self::edit($data, $uid, 'uid');
        }
    }

    /**
     * 设置推广关系
     * @param $spread
     * @param $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setSpread($spread, $uid)
    {
        //当前用户信息
        $userInfo = self::where('uid', $uid)->find();
        if (!$userInfo) return true;
        //当前用户有上级直接返回
        if ($userInfo->spread_uid) return true;
        //没有推广编号直接返回
        if (!$spread) return true;
        if ($spread == $uid) return true;
        if ($uid == self::where('uid', $spread)->value('spread_uid')) return true;
        //TODO 获取后台分销类型
        $storeBrokerageStatus = sys_config('store_brokerage_statu');
        $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
        if ($storeBrokerageStatus == 1) {
            $spreadCount = self::where('uid', $spread)->count();
            if ($spreadCount) {
                $spreadInfo = self::where('uid', $spread)->find();
                if ($spreadInfo->is_promoter) {
                    //TODO 只有扫码才可以获得推广权限
//                            if(isset($wechatUser['isPromoter'])) $data['is_promoter'] = $wechatUser['isPromoter'] ? 1 : 0;
                }
            }
        }
        $data['spread_uid'] = $spread;
        $data['spread_time'] = time();
        return self::edit($data, $uid, 'uid');
    }

    /**
     * 小程序用户添加
     * @param $routineUser
     * @param int $spread_uid
     * @return object
     */
    public static function setRoutineUser($routineUser, $spread_uid = 0)
    {
        self::beginTrans();
        $res1 = true;
        if ($spread_uid) $res1 = self::where('uid', $spread_uid)->inc('spread_count', 1)->update();
//        $storeBrokerageStatu = sys_config('store_brokerage_statu') ? : 1;//获取后台分销类型
        $res2 = self::create([
            'account' => 'rt' . $routineUser['uid'] . time(),
            'pwd' => md5(123456),
            'nickname' => $routineUser['nickname'] ?: '',
            'avatar' => $routineUser['headimgurl'] ?: '',
            'spread_uid' => $spread_uid,
//            'is_promoter'=>$spread_uid || $storeBrokerageStatu != 1 ? 1: 0,
            'spread_time' => $spread_uid ? time() : 0,
            'uid' => $routineUser['uid'],
            'add_time' => $routineUser['add_time'],
            'add_ip' => request()->ip(),
            'last_time' => time(),
            'last_ip' => request()->ip(),
            'user_type' => $routineUser['user_type']
        ]);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res2;
    }

    /**
     * 获得当前登陆用户UID
     * @return int $uid
     */
    public static function getActiveUid()
    {
        $uid = null;
        $uid = Session::get('LoginUid');
        if ($uid) return $uid;
        else return 0;
    }

    /**
     * TODO 查询当前用户信息
     * @param $uid $uid 用户编号
     * @param string $field $field 查询的字段
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserInfo($uid, $field = '')
    {
        if (strlen(trim($field))) $userInfo = self::where('uid', $uid)->field($field)->find();
        else  $userInfo = self::where('uid', $uid)->find();
        if (!$userInfo) return [];
        return $userInfo->toArray();
    }

    /**
     * 判断当前用户是否推广员
     * @param int $uid
     * @return bool
     */
    public static function isUserSpread($uid = 0)
    {
        if (!$uid) return false;
        $status = (int)sys_config('store_brokerage_statu');
        $isPromoter = true;
        if ($status == 1) $isPromoter = self::where('uid', $uid)->value('is_promoter');
        if ($isPromoter) return true;
        else return false;
    }


    /**
     * TODO 一级返佣
     * @param $orderInfo
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function backOrderBrokerage($orderInfo, bool $open = true)
    {
        //TODO 营销产品不返佣金
        if (isset($orderInfo['combination_id']) && $orderInfo['combination_id']) return true;
        if (isset($orderInfo['seckill_id']) && $orderInfo['seckill_id']) return true;
        if (isset($orderInfo['bargain_id']) && $orderInfo['bargain_id']) return true;

        $userInfo = User::getUserInfo($orderInfo['uid']);
        //TODO 当前用户不存在 没有上级 或者 当用用户上级时自己  直接返回
        if (!$userInfo || !$userInfo['spread_uid'] || $userInfo['spread_uid'] == $orderInfo['uid']) return true;
        if (!User::be(['uid' => $userInfo['spread_uid'], 'is_promoter' => 1])) return self::backOrderBrokerageTwo($orderInfo, $open);
        $cartId = is_string($orderInfo['cart_id']) ? json_decode($orderInfo['cart_id'], true) : $orderInfo['cart_id'];
        $brokeragePrice = StoreProduct::getProductBrokerage($cartId);
        //TODO 返佣金额小于等于0 直接返回不返佣金
        if ($brokeragePrice <= 0) return true;
        //TODO 获取上级推广员信息
        $spreadUserInfo = User::getUserInfo($userInfo['spread_uid']);
        //TODO 上级推广员返佣之后的金额
        $balance = bcadd($spreadUserInfo['brokerage_price'], $brokeragePrice, 2);
        $mark = $userInfo['nickname'] . '成功消费' . floatval($orderInfo['pay_price']) . '元,奖励推广佣金' . floatval($brokeragePrice);
        $open && self::beginTrans();
        //TODO 添加推广记录
        $res1 = UserBill::income('获得推广佣金', $userInfo['spread_uid'], 'now_money', 'brokerage', $brokeragePrice, $orderInfo['id'], $balance, $mark);
        //TODO 添加用户余额
        $res2 = self::bcInc($userInfo['spread_uid'], 'brokerage_price', $brokeragePrice, 'uid');
        //TODO 一级返佣成功 跳转二级返佣
        $res = $res1 && $res2 && self::backOrderBrokerageTwo($orderInfo, $open);
        $open && self::checkTrans($res);
//        if($res) return self::backOrderBrokerageTwo($orderInfo);
        return $res;
    }

    /**
     * TODO 二级推广
     * @param $orderInfo
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function backOrderBrokerageTwo($orderInfo, bool $open = true)
    {
        //TODO 获取购买商品的用户
        $userInfo = User::getUserInfo($orderInfo['uid']);
        //TODO 获取上推广人
        $userInfoTwo = User::getUserInfo($userInfo['spread_uid']);
        //TODO 上推广人不存在 或者 上推广人没有上级  或者 当用用户上上级时自己  直接返回
        if (!$userInfoTwo || !$userInfoTwo['spread_uid'] || $userInfoTwo['spread_uid'] == $orderInfo['uid']) return true;
        //TODO 获取后台分销类型  1 指定分销 2 人人分销
        if (!User::be(['uid' => $userInfoTwo['spread_uid'], 'is_promoter' => 1])) return true;
        $cartId = is_string($orderInfo['cart_id']) ? json_decode($orderInfo['cart_id'], true) : $orderInfo['cart_id'];
        $brokeragePrice = StoreProduct::getProductBrokerage($cartId, false);
        //TODO 返佣金额小于等于0 直接返回不返佣金
        if ($brokeragePrice <= 0) return true;
        //TODO 获取上上级推广员信息
        $spreadUserInfoTwo = User::getUserInfo($userInfoTwo['spread_uid']);
        //TODO 获取上上级推广员返佣之后余额
        $balance = bcadd($spreadUserInfoTwo['brokerage_price'], $brokeragePrice, 2);
        $mark = '二级推广人' . $userInfo['nickname'] . '成功消费' . floatval($orderInfo['pay_price']) . '元,奖励推广佣金' . floatval($brokeragePrice);
        $open && self::beginTrans();
        //TODO 添加返佣记录
        $res1 = UserBill::income('获得推广佣金', $userInfoTwo['spread_uid'], 'now_money', 'brokerage', $brokeragePrice, $orderInfo['id'], $balance, $mark);
        //TODO 添加用户余额
        $res2 = self::bcInc($userInfoTwo['spread_uid'], 'brokerage_price', $brokeragePrice, 'uid');
        $res = $res1 && $res2;
        $open && self::checkTrans($res);
        return $res;
    }

    /**
     * 获取推荐人 暂无使用
     * @param $uid
     * @param $page
     * @param $limit
     * @return mixed
     */
    public static function getSpreadList($uid, $page, $limit)
    {
        $list = self::where('spread_uid', $uid)->field('uid,nickname,avatar,add_time')->page($page, $limit)->order('add_time DESC')->select();
        foreach ($list as $k => $user) {
            $list[$k]['add_time'] = date('Y/m/d', $user['add_time']);
            $list[$k]['price'] = StoreOrder::getUserPrice($user['uid']);
        }
        $count = self::where('spread_uid', $uid)->field('uid,nickname,avatar,add_time')->count();
        $data['count'] = $count;
        $data['list'] = $list;
        return $data;
    }

    /**
     * 获取某个用户的下级uid
     * @param $uid
     * @return array
     */
    public static function getOneSpreadUid($uid)
    {
        return self::where('spread_uid', $uid)->column('uid');
    }

    /**
     * 修改个人信息
     * @param $avatar 头像
     * @param $nickname 昵称
     * @param $uid 用户uid
     * @return bool
     */
    public static function editUser($avatar, $nickname, $uid)
    {
        return self::edit(['avatar' => $avatar, 'nickname' => $nickname], $uid, 'uid');
    }

    /**
     * TODO 获取推广人数 一级
     * @param int $uid
     * @return bool|int|string
     */
    public static function getSpreadCount($uid = 0)
    {
        if (!$uid) return false;
        return self::where('spread_uid', $uid)->count('uid');
    }

    /**
     * 修改当前用户的推广人数
     * @param $uid
     */
    public static function setUserSpreadCount($uid)
    {
        self::where('uid', $uid)->update(['spread_count' => self::getSpreadCount($uid)]);
    }

    /**
     * TODO 获取推广人数 二级
     * @param int $uid
     * @return bool|int|string
     */
    public static function getSpreadLevelCount($uid = 0)
    {
        if (!$uid) return false;
        $uidSubordinate = self::where('spread_uid', $uid)->column('uid');
        if (!count($uidSubordinate)) return 0;
        return self::where('spread_uid', 'IN', implode(',', $uidSubordinate))->count('uid');
    }

    /**
     * 获取用户下级推广人
     * @param int $uid 当前用户
     * @param int $grade 等级  0  一级 1 二级
     * @param string $orderBy 排序
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @return array|bool
     */
    public static function getUserSpreadGrade($uid = 0, $grade = 0, $orderBy = '', $keyword = '', $page = 0, $limit = 20)
    {
        if (!$uid) return [];
        $gradeGroup = [0, 1];
        if (!in_array($grade, $gradeGroup)) return self::setErrorInfo('等级错误');
        $userStair = self::where('spread_uid', $uid)->column('uid');
        if (!count($userStair)) return [];
        if ($grade == 0) return self::getUserSpreadCountList(implode(',', $userStair), $orderBy, $keyword, $page, $limit);
        $userSecondary = self::where('spread_uid', 'in', implode(',', $userStair))->column('uid');
        return self::getUserSpreadCountList(implode(',', $userSecondary), $orderBy, $keyword, $page, $limit);
    }

    /**
     * 获取团队信息
     * @param $uid
     * @param string $orderBy
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getUserSpreadCountList($uid, $orderBy = '', $keyword = '', $page = 0, $limit = 20)
    {
        $model = new self;
        if ($orderBy === '') $orderBy = 'u.add_time desc';
        $model = $model->alias(' u');
        $sql = StoreOrder::where('o.paid', 1)->group('o.uid')->field(['SUM(o.pay_price) as numberCount', 'o.uid', 'o.order_id'])
            ->where('o.is_del', 0)->where('o.refund_status', 'in', [0,1])->where('o.is_system_del', 0)->alias('o')->fetchSql(true)->select();
        $model = $model->join("(" . $sql . ") p", 'u.uid = p.uid', 'LEFT');
        $model = $model->where('u.uid', 'IN', $uid);
        $model = $model->field("u.uid,u.nickname,u.avatar,from_unixtime(u.add_time,'%Y/%m/%d') as time,u.spread_count as childCount,u.pay_count as orderCount,p.numberCount");
        if (strlen(trim($keyword))) $model = $model->where('u.nickname|u.phone', 'like', "%$keyword%");
        $model = $model->group('u.uid');
        $model = $model->order($orderBy);
        $model = $model->page($page, $limit);
        $list = $model->select();
        if ($list) return $list->toArray();
        else return [];
    }

    /**
     * 设置用户的上级关系
     * @param $uid 用户uid
     * @param $spreadUid 上级用户uid
     * @return User|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setSpreadUid($uid, $spreadUid)
    {
        // 自己不能绑定自己为上级
        if ($uid == $spreadUid) return false;
        //TODO 获取后台分销类型
        $storeBrokerageStatus = sys_config('store_brokerage_statu');
        $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
        if ($storeBrokerageStatus == 1) {
            $spreadCount = self::where('uid', $spreadUid)->count();
            if ($spreadCount) {
                $spreadInfo = self::where('uid', $spreadUid)->find();
                if ($spreadInfo->is_promoter) {
                    //TODO 只有扫码才可以获得推广权限
                    if (isset($wechatUser['isPromoter'])) $data['is_promoter'] = 1;
                }
            }
        }
        return self::where('uid', $uid)->update(['spread_uid' => $spreadUid, 'spread_time' => time()]);
    }

    /**
     * 判断上下级关系是否存在
     * @param $uid
     * @param $spreadUid
     * @return bool|int
     */
    public static function validSpread($uid, $spreadUid)
    {
        if (!$uid || !$spreadUid) return false;
        return self::where('uid', $uid)->where('spread_uid', $spreadUid)->count();
    }

    /**
     * H5用户注册
     * @param $account
     * @param $password
     * @param $spread
     * @return User|\think\Model
     */
    public static function register($account, $password, $spread)
    {
        if (self::be(['account' => $account])) return self::setErrorInfo('用户已存在');
        $phone = $account;
        $data['account'] = $account;
        $data['pwd'] = md5($password);
        $data['phone'] = $phone;
        if ($spread) {
            $data['spread_uid'] = $spread;
            $data['spread_time'] = time();
        }
        $data['real_name'] = '';
        $data['birthday'] = 0;
        $data['card_id'] = '';
        $data['mark'] = '';
        $data['addres'] = '';
        $data['user_type'] = 'h5';
        $data['add_time'] = time();
        $data['add_ip'] = app('request')->ip();
        $data['last_time'] = time();
        $data['last_ip'] = app('request')->ip();
        $data['nickname'] = substr(md5($account . time()), 0, 12);
        $data['avatar'] = $data['headimgurl'] = sys_config('h5_avatar');
        $data['city'] = '';
        $data['language'] = '';
        $data['province'] = '';
        $data['country'] = '';
        self::beginTrans();
        $res2 = WechatUser::create($data);
        $data['uid'] = $res2->uid;
        $res1 = self::create($data);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 密码修改
     * @param $account
     * @param $password
     * @return User|bool
     */
    public static function reset($account, $password)
    {
        if (!self::be(['account' => $account])) return self::setErrorInfo('用户不存在');
        $count = self::where('account', $account)->where('pwd', md5($password))->count();
        if ($count) return true;
        return self::where('account', $account)->update(['pwd' => md5($password)]);
    }

    /**
     * 获取手机号是否注册
     * @param $phone
     * @return bool
     */
    public static function checkPhone($phone)
    {
        return self::be(['account' => $phone]);
    }


    /**
     * 获取推广人
     * @param $data 查询条件
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRankList($data)
    {
        switch ($data['type']) {
            case 'week':
                $startTime = strtotime('this week');
                $endTime = time();
                break;
            case 'month':
                $startTime = strtotime('last month');
                $endTime = time();
                break;
        }
        $list = self::alias('t0')
            ->field('t0.uid,t0.spread_uid,count(t1.spread_uid) AS count,t0.add_time,t0.nickname,t0.avatar')
            ->join('user t1', 't0.uid = t1.spread_uid', 'LEFT')
            ->where('t1.spread_uid', '<>', 0)
            ->order('count desc')
            ->order('t0.uid desc')
            ->where('t1.add_time', 'BETWEEN', [$startTime, $endTime])
            ->page($data['page'], $data['limit'])
            ->group('t0.uid')
            ->select();
        return count($list) ? $list->toArray() : [];
    }

    /**
     * 获取佣金排行
     * @param $data
     * @return array
     */
    public static function brokerageRank($data)
    {
        $model = UserBill::alias('b')->join('user u', 'b.uid = u.uid');
        $model = $model->where('b.category', 'now_money')->where('b.type', 'brokerage');
        switch ($data['type']) {
            case 'week':
                $model = $model->whereWeek('b.add_time');
                break;
            case 'month':
                $model = $model->whereMonth('b.add_time');
                break;
        }
        $users = $model->group('b.uid')
            ->field('b.uid,u.nickname,u.avatar,SUM(IF(pm=1,`number`,-`number`)) as brokerage_price')
            ->order('brokerage_price desc')
            ->page((int)$data['page'], (int)$data['limit'])
            ->select();
        return count($users) ? $users->toArray() : [];
    }

    /**
     * 获取当前用户的佣金排行位置
     * @param $uid
     * @return int
     */
    public static function currentUserRank($type, $brokerage_price)
    {
        $model = self::where('status', 1);
        switch ($type) {
            case 'week':
                $model = $model->whereIn('uid', function ($query) {
                    $query->name('user_bill')->where('category', 'now_money')->where('type', 'brokerage')
                        ->whereWeek('add_time')->field('uid');
                });
                break;
            case 'month':
                $model = $model->whereIn('uid', function ($query) {
                    $query->name('user_bill')->where('category', 'now_money')->where('type', 'brokerage')
                        ->whereMonth('add_time')->field('uid');
                });
                break;
        }
        return $model->where('brokerage_price', '>', $brokerage_price)->count('uid');
    }
}