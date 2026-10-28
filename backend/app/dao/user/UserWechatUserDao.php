<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\dao\user;

use think\model;
use crmeb\basic\BaseDao;
use app\model\user\User;

/**
 *
 * Class UserWechatUserDao
 * @package app\dao\user
 */
class UserWechatUserDao extends BaseDao
{
    /**
     * @var string
     */
    protected $alias = '';

    /**
     * @var string
     */
    protected $join_alis = '';

    /**
     * 精确搜索白名单
     * @var string[]
     */
    protected $withField = ['uid', 'nickname', 'user_type', 'phone'];

    /**
     * 微信用户模型类名（由 ChannelServiceProxy 事件代理获取）
     * 为空字符串表示 wechat_mp 模块未安装/未启用，查询需降级为纯用户表
     * @var string|null
     */
    protected $wechatUserClass = null;

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return User::class;
    }

    /**
     * 获取微信用户模型类名
     *
     * 通过 ChannelServiceProxy 事件代理向 wechat_mp 模块获取，避免 app 层直接引用 modules。
     * 结果缓存到属性，避免同一请求中多次触发事件；模块未安装/未启用时返回空字符串。
     *
     * @return string
     */
    public function joinModel(): string
    {
        if ($this->wechatUserClass !== null) {
            return $this->wechatUserClass;
        }
        $results = event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUser', 'action' => 'getClass', 'args' => []]);
        return $this->wechatUserClass = proxy_event_result($results, '');
    }

    /**
     * 判断当前是否可关联微信用户表
     * 仅当 wechat_mp 模块已安装/启用并返回了有效模型类名时才可 JOIN
     * @return bool
     */
    public function canJoinWechatUser(): bool
    {
        return $this->joinModel() !== '';
    }

    /**
     * 关联模型
     *
     * wechat_mp 模块未安装/未启用（joinModel 为空）时降级为纯用户主表查询，
     * 避免 app()->make('') 抛"类不存在"异常以及 JOIN 不存在的表。
     *
     * @param string $alias
     * @param string $join_alias
     * @param string $join
     * @return \crmeb\basic\BaseModel
     */
    public function getModel(string $alias = 'u', string $join_alias = 'w', $join = 'left')
    {
        $this->alias = $alias;
        $this->join_alis = $join_alias;
        $wechatUserClass = $this->joinModel();
        if ($wechatUserClass === '') {
            return parent::getModel()->alias($alias);
        }
        //joinModel 返回的是模型类名，通过容器解析为模型实例
        $wechcatUser = app()->make($wechatUserClass);
        $table = $wechcatUser->getName();
        return parent::getModel()->alias($alias)->join($table . ' ' . $join_alias, $alias . '.uid = ' . $join_alias . '.uid', $join);
    }

    public function getList(array $where = [], $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->getModel()->where($where)->field($field)->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取总数
     * @param array $where
     * @return int
     */
    public function getCount(array $where): int
    {
        return $this->getModel()->where($where)->count();
    }

    /**
     * 组合条件模型条数
     * @param Model $model
     * @return int
     */
    public function getCountByWhere(array $where): int
    {
        return $this->searchWhere($where)->group($this->alias . '.uid')->count();
    }

    /**
     * 组合条件模型查询列表
     * @param Model $model
     * @return array
     */
    public function getListByModel(array $where = [], string $field = '', string $order = '', int $page = 0, int $limit = 0): array
    {
        return $this->searchWhere($where)->field($field)->page($page, $limit)->group($this->alias . '.uid')->order(($order ? $order . ' ,' : '') . $this->alias . '.uid desc')->select()->toArray();
    }

    /**
     * 设置搜索条件
     * @param $where array 条件数组
     * @param array|null $field 需要查询的字段
     * @return \crmeb\basic\BaseModel
     */
    public function searchWhere($where, ?array $field = [])
    {
        $model = $this->getModel();
        $userAlias = $this->alias . '.';
        $wechatUserAlias = $this->join_alis . '.';
        
        // --- 时间筛选模块 ---
        // 筛选类型：visitno(未访问), visit(访问时间), add_time(注册时间)
        if (isset($where['user_time_type']) && isset($where['user_time'])) {
            // 筛选指定时间内未访问的用户
            if ($where['user_time_type'] == 'visitno' && $where['user_time'] !== '') {
                list($startTime, $endTime) = explode('-', $where['user_time']);
                if ($startTime && $endTime) {
                    $endTime = strtotime($endTime) + 24 * 3600;
                    $startTime = strtotime($startTime);
                    // last_time 不在指定时间段内（结束日期按当天 24:00 处理）
                    $model = $model->where(function ($query) use ($userAlias, $startTime, $endTime) {
                        $query->where($userAlias . 'last_time', '<', $startTime)
                            ->whereOr($userAlias . 'last_time', '>=', $endTime);
                    });
                }
            }
            // 筛选指定时间内访问过的用户
            if ($where['user_time_type'] == 'visit' && $where['user_time'] !== '') {
                list($startTime, $endTime) = explode('-', $where['user_time']);
                if ($startTime && $endTime) {
                    $model = $model->where($userAlias . 'last_time', '>=', strtotime($startTime));
                    $model = $model->where($userAlias . 'last_time', '<', strtotime($endTime) + 24 * 3600);
                }
            }
            // 筛选指定时间内注册的用户
            if ($where['user_time_type'] == 'add_time' && $where['user_time'] !== '') {
                list($startTime, $endTime) = explode('-', $where['user_time']);
                if ($startTime && $endTime) {
                    $model = $model->where($userAlias . 'add_time', '>=', strtotime($startTime));
                    $model = $model->where($userAlias . 'add_time', '<', strtotime($endTime) + 24 * 3600);
                }
            }
        }

        // --- 上次消费时间筛选 ---
        // 仅统计已支付且未退款订单，并按用户最近一次消费时间筛选。
        if (isset($where['before_pay_time']) && $where['before_pay_time'] !== '') {
            $timeRange = explode('-', $where['before_pay_time'], 2);
            if (count($timeRange) === 2 && $timeRange[0] !== '' && $timeRange[1] !== '') {
                $startTime = strtotime($timeRange[0]);
                $endTime = strtotime($timeRange[1]) + 24 * 3600;
                if ($startTime !== false && $endTime !== false) {
                    $model = $model->whereIn($userAlias . 'uid', function ($query) use ($startTime, $endTime) {
                        $query->name('order_center')
                            ->where('pay_status', 1)
                            ->where('refund_status', 0)
                            ->field('uid')
                            ->group('uid')
                            ->having('MAX(pay_time) >= ' . (int)$startTime . ' AND MAX(pay_time) < ' . (int)$endTime);
                    });
                }
            }
        }

        // --- 购买次数筛选 (单一数值) ---
        // pay_count: -1(0次), 其他数值(大于该数值)
        if (isset($where['pay_count']) && $where['pay_count'] !== '' && $where['pay_count'] !== null) {
            if ($where['pay_count'] == '-1') {
                $model = $model->where($userAlias . 'pay_count', 0);
            } else {
                $model = $model->where($userAlias . 'pay_count', '>', $where['pay_count']);
            }
        }

        // --- 购买次数筛选 (区间) ---
        // pay_count_num: [min, max]
        if (isset($where['pay_count_num']) && count($where['pay_count_num']) == 2) {
            if ($where['pay_count_num'][0] !== '' && $where['pay_count_num'][1] !== '') {
                // 区间查询
                $model = $model->whereBetween($userAlias . 'pay_count', $where['pay_count_num']);
            } elseif ($where['pay_count_num'][0] !== '' && $where['pay_count_num'][1] === '') {
                // 大于等于最小值
                $model = $model->where($userAlias . 'pay_count', '>=', $where['pay_count_num'][0]);
            } elseif ($where['pay_count_num'][0] === '' && $where['pay_count_num'][1] !== '') {
                // 小于等于最大值
                $model = $model->where($userAlias . 'pay_count', '<=', $where['pay_count_num'][1]);
            }
        }

        // --- 消费金额筛选 (区间) ---
        // pay_count_money: [min, max] 统计 order_center 表中实际支付金额
        if (isset($where['pay_count_money']) && count($where['pay_count_money']) == 2) {
            $min = $where['pay_count_money'][0];
            $max = $where['pay_count_money'][1];
            
            if ($min !== '' || $max !== '') {
                $model = $model->where(function ($query) use ($userAlias, $min, $max) {
                    // 子查询：在 order_center 表中查找满足条件的记录
                    $query->whereExists(function ($q) use ($userAlias, $min, $max) {
                        $q->name('order_center')
                            ->whereColumn('uid', $userAlias . 'uid')
                            ->where('pay_status', 1) // 已支付
                            ->where('refund_status', 0); // 未退款
                        
                        // 根据区间条件筛选 pay_price
                        if ($min !== '' && $max !== '') {
                            $q->whereBetween('pay_price', [$min, $max]);
                        } elseif ($min !== '' && $max === '') {
                            $q->where('pay_price', '>=', $min);
                        } elseif ($min === '' && $max !== '') {
                            $q->where('pay_price', '<=', $max);
                        }
                    });
                    
                    // 特殊处理：如果最小值为0或空，需要包含没有订单记录的用户（即消费金额为0的用户）
                    if ($min === '' || $min == 0) {
                        $query->whereOr(function ($q) use ($userAlias) {
                            $q->whereNotExists(function ($sub) use ($userAlias) {
                                $sub->name('order_center')
                                    ->whereColumn('uid', $userAlias . 'uid')
                                    ->where('pay_status', 1)
                                    ->where('refund_status', 0);
                            });
                        });
                    }
                });
            }
        }

        // --- 余额筛选 (区间) ---
        // balance: [min, max]
        if (isset($where['balance']) && count($where['balance']) == 2) {
            if ($where['balance'][0] !== '' && $where['balance'][1] !== '') {
                $model = $model->whereBetween($userAlias . 'now_money', $where['balance']);
            } elseif ($where['balance'][0] !== '' && $where['balance'][1] === '') {
                $model = $model->where($userAlias . 'now_money', '>=', $where['balance'][0]);
            } elseif ($where['balance'][0] === '' && $where['balance'][1] !== '') {
                $model = $model->where($userAlias . 'now_money', '<=', $where['balance'][1]);
            }
        }

        // --- 积分筛选 (区间) ---
        // integral: [min, max]
        if (isset($where['integral']) && count($where['integral']) == 2) {
            if ($where['integral'][0] !== '' && $where['integral'][1] !== '') {
                $model = $model->whereBetween($userAlias . 'integral', $where['integral']);
            } elseif ($where['integral'][0] !== '' && $where['integral'][1] === '') {
                $model = $model->where($userAlias . 'integral', '>=', $where['integral'][0]);
            } elseif ($where['integral'][0] === '' && $where['integral'][1] !== '') {
                $model = $model->where($userAlias . 'integral', '<=', $where['integral'][1]);
            }
        }

        // --- 基础属性筛选 ---
        // 用户等级
        if (isset($where['level']) && $where['level']) {
            $model = $model->where($userAlias . 'level', $where['level']);
        }
        // 用户分组
        if (isset($where['group_id']) && $where['group_id']) {
            $model = $model->where($userAlias . 'group_id', $where['group_id']);
        }
        // 用户状态
        if (isset($where['status']) && $where['status'] !== '' && $where['status'] !== null) {
            $model = $model->where($userAlias . 'status', $where['status']);
        }
        // --- 标签筛选 ---
        // label_id: 单个ID或ID数组/逗号分隔字符串
        if (isset($where['label_id']) && $where['label_id']) {
            $model = $model->whereIn($userAlias . 'uid', function ($query) use ($where) {
                if (is_array($where['label_id'])) {
                    $label_ids = array_map('intval', $where['label_id']);
                    $query->name('user_label_relation')->whereIn('label_id', $label_ids)->field('uid')->select();
                } else {
                    if (strpos($where['label_id'], ',') !== false) {
                        $label_ids = array_map('intval', explode(',', $where['label_id']));
                        $query->name('user_label_relation')->whereIn('label_id', $label_ids)->field('uid')->select();
                    } else {
                        $query->name('user_label_relation')->where('label_id', (int)$where['label_id'])->field('uid')->select();
                    }
                }
            });
        }
        
        // --- 会员状态筛选 ---
        // isMember: 0(非会员), 1(会员)
        if (isset($where['isMember']) && $where['isMember'] !== '' && $where['isMember'] !== null) {
            if ($where['isMember'] == 0) {
                $model = $model->where($userAlias . 'is_money_level', 0);
            } else {
                $model = $model->where($userAlias . 'is_money_level', '>', 0);
            }
        }

        // --- 关键字搜索 ---
        // field_key: 指定搜索字段 (nickname, phone, uid)
        // nickname: 搜索关键词
        $fieldKey = $where['field_key'] ?? '';
        $nickname = $where['nickname'] ?? '';
        if ($fieldKey && $nickname && in_array($fieldKey, $this->withField)) {
            switch ($fieldKey) {
                case "nickname":
                case "phone":
                    $model = $model->where($userAlias . trim($fieldKey), 'like', "%" . trim($nickname) . "%");
                    break;
                case "uid":
                    $model = $model->where($userAlias . trim($fieldKey), trim($nickname));
                    break;
            }
        } else if (!$fieldKey && $nickname) {
            // 未指定字段时，模糊搜索昵称、UID或手机号
            $model = $model->where($userAlias . 'nickname|' . $userAlias . 'uid|' . $userAlias . 'phone', 'LIKE', "%$where[nickname]%");
        }

        // --- 地域筛选 ---
        // country: domestic(国内), abroad(国外)
        // 以下 country/province/city/sex 筛选字段位于微信用户表（w.*），无 JOIN 时需跳过
        if ($this->canJoinWechatUser() && isset($where['country']) && $where['country']) {
            if ($where['country'] == 'domestic') {
                $model = $model->where($wechatUserAlias . 'country', 'in', ['中国', 'China']);
            } else if ($where['country'] == 'abroad') {
                $model = $model->where($wechatUserAlias . 'country', 'not in', ['中国', '']);
            }
        }
        
        // --- 客户端类型筛选 ---
        // user_type: app, wechat, routine 等
        if (isset($where['user_type']) && $where['user_type']) {
            if ($where['user_type'] == 'app') {
                $model = $model->whereIn($userAlias . 'user_type', ['app', 'apple']);
            } else {
                $model = $model->where($userAlias . 'user_type', $where['user_type']);
            }
        }

        // --- 性别筛选 ---
        // sex: 1(男), 2(女), 0(未知)
        if ($this->canJoinWechatUser() && isset($where['sex']) && $where['sex'] !== '' && in_array($where['sex'], [0, 1, 2])) {
            $model = $model->where($wechatUserAlias . 'sex', $where['sex']);
        }
        
        // --- 省份筛选 ---
        if ($this->canJoinWechatUser() && isset($where['province']) && $where['province']) {
            $model = $model->where($wechatUserAlias . 'province', $where['province']);
        }
        
        // --- 城市筛选 ---
        if ($this->canJoinWechatUser() && isset($where['city']) && $where['city']) {
            $model = $model->where($wechatUserAlias . 'city', $where['city']);
        }

        // --- 通用时间筛选 ---
        // 使用模型搜索器 time
        if (isset($where['time'])) {
            $model->withSearch(['time'], ['time' => $where['time'], 'timeKey' => 'u.add_time']);
        }

        // --- 删除状态筛选 ---
        if (isset($where['is_del'])) {
            $model->where($userAlias . 'is_del', $where['is_del']);
        }

        // --- 指定ID筛选 ---
        if (isset($where['ids']) && count($where['ids'])) {
            $model->whereIn($userAlias . 'uid', $where['ids']);
        }

        return $field ? $model->field($field) : $model;
    }

    /**
     * 获取用户性别
     * @param $time
     * @param $userType
     * @return mixed
     */
    public function getSex($time, $userType)
    {
        return $this->getModel()->when($userType !== '' && $userType !== null, function ($query) use ($userType) {
            $query->where($this->join_alis . '.user_type', $userType);
        })->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDay($this->join_alis . '.add_time', $time[0]);
            } else {
                $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                $query->whereTime($this->join_alis . '.add_time', 'between', $time);
            }
        })->field('count(' . $this->alias . '.uid) as value,' . $this->join_alis . '.sex as name')
            ->group($this->join_alis . '.sex')->select()->toArray();
    }
}
