<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\user;

use app\model\agent\AgentLevel;
use app\model\order\StoreOrder;
use app\model\system\SystemUserLevel;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class User
 * @package app\model\user
 */
class User extends BaseModel
{
    use ModelTrait;

    /**
     * @var string
     */
    protected $pk = 'uid';

    protected $name = 'user';

    protected $insert = ['add_time', 'add_ip', 'last_time', 'last_ip'];

    protected $hidden = [
        'add_ip', 'account', 'clean_time', 'last_ip', 'pwd'
    ];

    /**
     * 自动转类型
     * @var string[]
     */
    protected $type = [
        'birthday' => 'int'
    ];

    protected $updateTime = false;

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

//    protected function getPhoneAttr($value)
//    {
//        return $value && app('request')->hasMacro('adminInfo') && app('request')->adminInfo()['level'] != 0 ? substr_replace($value, '****', 3, 4) : $value;
//    }

    /**
     * 链接会员登陆设置表
     * @return \think\model\relation\HasOne
     */
    public function systemUserLevel()
    {
        return $this->hasOne(SystemUserLevel::class, 'id', 'level');
    }

    /**
     * 关联用户分组
     * @return \think\model\relation\HasOne
     */
    public function userGroup()
    {
        return $this->hasOne(UserGroup::class, 'id', 'group_id');
    }

    /**
     * 关联自己
     * @return \think\model\relation\HasOne
     */
    public function spreadUser()
    {
        return $this->hasOne(self::class, 'uid', 'spread_uid');
    }

    /**
     * 关联自己
     * @return \think\model\relation\HasOne
     */
    public function spreadCount()
    {
        return $this->hasMany(User::class, 'spread_uid', 'uid');
    }

    /**
     * 关联用户标签关系
     * @return \think\model\relation\HasMany
     */
    public function LabelRelation()
    {
        return $this->hasMany(UserLabelRelation::class, 'uid', 'uid');
    }

    /**
     * 关联用户标签
     * @return \think\model\relation\HasManyThrough
     */
    public function label()
    {
        return $this->hasManyThrough(UserLabel::class, UserLabelRelation::class, 'uid', 'id', 'uid', 'label_id');
    }

    /**
     * 关联用户地址
     * @return \think\model\relation\HasMany
     */
    public function address()
    {
        return $this->hasMany(UserAddress::class, 'uid', 'uid');
    }

    /**
     * 关联提现
     * @return \think\model\relation\HasMany
     */
    public function extract()
    {
        return $this->hasMany(UserExtract::class, 'uid', 'uid');
    }

    /**
     * 关联订单
     * @return User|\think\model\relation\HasMany
     */
    public function order()
    {
        return $this->hasMany(StoreOrder::class, 'uid', 'uid');
    }

    /**
     * 关联分销等级
     * @return \think\model\relation\HasOne
     */
    public function agentLevel()
    {
        return $this->hasOne(AgentLevel::class, 'id', 'agent_level')->where('is_del', 0)->where('status', 1);
    }

    /**
     * 关联佣金数据
     * @return \think\model\relation\HasMany
     */
    public function bill()
    {
        return $this->hasMany(UserBill::class, 'uid', 'uid');
    }

    /**
     * 用户uid
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('uid', $value);
        else
            $query->where('uid', $value);
    }

    /**
     * 账号搜索器
     * @param Model $query
     * @param $value
     */
    public function searchAccountAttr($query, $value)
    {
        $query->where('account', $value);
    }

    /**
     * 密码搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPwdAttr($query, $value)
    {
        $query->where('pwd', $value);
    }

    /**
     * uid范围查询搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidsAttr($query, $value)
    {
        $query->whereIn('uid', $value);
    }

    /**
     * 模糊条件搜索器
     * @param Model $query
     * @param $value
     */
    public function searchLikeAttr($query, $value)
    {
        $query->where('account|nickname|phone|real_name|uid', 'like', '%' . $value . '%');
    }

    /**
     * 手机号搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPhoneAttr($query, $value)
    {
        $query->where('phone', $value);
    }

    /**
     * 分组搜索器
     * @param Model $query
     * @param $value
     */
    public function searchGroupIdAttr($query, $value)
    {
        $query->where('group_id', $value);
    }

    /**
     * 是否推广人搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsPromoterAttr($query, $value)
    {
        $query->where('is_promoter', $value);
    }

    /**
     * 状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 会员等级搜索器
     * @param Model $query
     * @param $value
     */
    public function searchLevelAttr($query, $value)
    {
        $query->where('level', $value);
    }

    /**
     * 推广人uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchSpreadUidAttr($query, $value)
    {
        $query->where('spread_uid', $value);
    }

    /**
     * 推广人uid不等于搜索器
     * @param Model $query
     * @param $value
     */
    public function searchNotSpreadUidAttr($query, $value)
    {
        $query->where('spread_uid', '<>', $value);
    }

    /**
     * 推广人时间搜索器
     * @param Model $query
     * @param $value
     */
    public function searchSpreadTimeAttr($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                if (count($value) == 2) $query->where('spread_time', $value[0], $value[1]);
            } else {
                $query->where('spread_time', $value);
            }
        }
    }

    /**
     * 用户类型搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUserTypeAttr($query, $value)
    {
        if ($value != '') $query->where('user_type', $value);
    }

    /**
     * 购买次数搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPayCountAttr($query, $value)
    {
        $query->where('pay_count', $value);
    }

    /**
     * 用户推广资格
     * @param Model $query
     * @param $value
     */
    public function searchSpreadOpenAttr($query, $value)
    {
        if ($value != '') $query->where('spread_open', $value);
    }

    /**
     * nickname搜索器
     * @param $query
     * @param $value
     */
    public function searchNicknameAttr($query, $value)
    {
        $query->where('nickname', "like", "%" . $value . "%");
    }

    /**
     * division_type搜索器
     * @param $query
     * @param $value
     */
    public function searchDivisionTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('division_type', $value);
    }

    /**
     * division_id搜索器
     * @param $query
     * @param $value
     */
    public function searchDivisionIdAttr($query, $value)
    {
        if ((int)$value !== 0) $query->where('division_id', $value);
    }

    /**
     * agent_id搜索器
     * @param $query
     * @param $value
     */
    public function searchAgentIdAttr($query, $value)
    {
        if ($value !== '') $query->where('agent_id', $value);
    }

    /**
     * staff_id搜索器
     * @param $query
     * @param $value
     */
    public function searchStaffIdAttr($query, $value)
    {
        if ($value !== '') $query->where('staff_id', $value);
    }

    /**
     * is_division搜索器
     * @param $query
     * @param $value
     */
    public function searchIsDivisionAttr($query, $value)
    {
        if ($value !== '') $query->where('is_division', $value);
    }

    /**
     * is_agent搜索器
     * @param $query
     * @param $value
     */
    public function searchIsAgentAttr($query, $value)
    {
        if ($value !== '') $query->where('is_agent', $value);
    }

    /**
     * is_staff搜索器
     * @param $query
     * @param $value
     */
    public function searchIsStaffAttr($query, $value)
    {
        if ($value !== '') $query->where('is_staff', $value);
    }

    /**
     * @param $query
     * @param $value
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value !== '') $query->where('uid|nickname', 'like', '%' . $value . '%');
    }

    /**
     * 注销搜索器
     * @param $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }

    /**
     * 不等于uid搜索器
     * @param $query
     * @param $value
     */
    public function searchNotUidAttr($query, $value)
    {
        if ($value !== '') $query->where('uid', '<>', $value);
    }
}
