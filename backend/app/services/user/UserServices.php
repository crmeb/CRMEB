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

namespace app\services\user;

use crmeb\basic\BaseService;
use app\dao\user\UserDao;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\PlatformDetector;
use app\services\diy\DiyServices;
use app\services\user\MemberOrderServices;
use app\services\order\OrderCenterServices;
use app\dao\order\OrderCenterDao;
use app\services\order\OrderTakeServices;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreProductLogServices;
use app\services\product\product\StoreProductRelationServices;

use app\services\system\SystemUserLevelServices;
use app\services\user\member\MemberCardServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\utils\FormBuilder as Form;
use crmeb\utils\FormBuilder;
use think\Exception;
use think\facade\Route as Url;

/**
 * Class UserServices
 * @package app\services\user
 * @method array getUserInfoArray(array $where, string $field, string $key) 根据条件查询对应的用户信息以数组形式返回
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method get($id, ?array $field = [], ?array $with = []) 获取一条数据
 * @method count(array $where) 获取指定条件下的数量
 * @method value(array $where, string $field) 获取指定的键值
 * @method bcInc($key, string $incField, string $inc, ?string $keyField = null, int $acc = 2) 高精度加法
 * @method bcDec($key, string $incField, string $inc, ?string $keyField = null, int $acc = 2) 高精度减法
 * @method getTrendData($time, $type, $timeType)
 * @method incPayCount(int $uid) 用户支付成功个数增加
 * @mixin UserDao
 */
class UserServices extends BaseService
{
    /**
     * @var UserDao
     */
    protected $dao;

    /**
     * UserServices constructor.
     * @param UserDao $dao
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户信息
     * @param int $uid
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function getUserInfo(int $uid, $field = '*')
    {
        if (is_string($field)) $field = explode(',', $field);
        return $this->dao->get($uid, $field);
    }

    /**
     * 乐观锁扣减余额（now_money 字段为 decimal(10,2) 类型）
     * 使用数据库原子操作实现乐观锁，避免并发超扣
     * @param int $uid 用户ID（必须大于0）
     * @param int|string $number 扣减金额（支持小数）
     * @return bool
     */
    public function decrMoney(int $uid, $number): bool
    {
        if ($uid <= 0) {
            return false;
        }
        
        $result = $this->dao->search(['uid' => $uid])
            ->where('now_money', '>=', number_format((float)$number, 2, '.', ''))
            ->dec('now_money', number_format((float)$number, 2, '.', ''))
            ->update();
        
        return $result !== false && $result > 0;
    }

    /**
     * 乐观锁扣减积分（integral 字段为 int 类型）
     * 使用数据库原子操作实现乐观锁，避免并发超扣
     * @param int $uid 用户ID（必须大于0）
     * @param int|string $number 扣减积分数量
     * @return bool
     */
    public function decrIntegral(int $uid, $number): bool
    {
        if ($uid <= 0) {
            return false;
        }
        
        $number = (int)$number;
        if ($number <= 0) {
            return false;
        }
        
        $result = $this->dao->search(['uid' => $uid])
            ->where('integral', '>=', $number)
            ->dec('integral', $number)
            ->update();
        
        return $result !== false && $result > 0;
    }

    /**
     * 乐观锁扣减经验（exp 字段为 int 类型）
     * 使用数据库原子操作实现乐观锁，避免并发超扣
     * @param int $uid 用户ID（必须大于0）
     * @param int|string $number 扣减经验数量
     * @return bool
     */
    public function decrExp(int $uid, $number): bool
    {
        if ($uid <= 0) {
            return false;
        }
        
        $result = $this->dao->search(['uid' => $uid])
            ->where('exp', '>=', (int)$number)
            ->dec('exp', (int)$number)
            ->update();
        
        return $result !== false && $result > 0;
    }

    /**
     * 获取用户列表
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserList(array $where, string $field): array
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $field, $page, $limit);
        $count = $this->getCount($where);
        return compact('list', 'count');
    }

    /**
     * 列表条数
     * @param array $where
     * @return int
     */
    public function getCount(array $where, bool $is_list = false)
    {
        return $this->dao->getCount($where, $is_list);
    }

    /**
     * 保存用户信息
     * @param $user
     * @param int $spreadUid
     * @param string $userType
     * @return User|\think\Model
     * @throws Exception
     */
    public function setUserInfo($user, int $spreadUid = 0, string $userType = 'wechat')
    {
        $data = [
            'account' => $user['account'] ?? 'wx' . rand(1, 9999) . time(),
            'pwd' => $user['pwd'] ?? md5('123456'),
            'nickname' => $user['nickname'] ?? '',
            'avatar' => $user['headimgurl'] ?? '',
            'phone' => $user['phone'] ?? '',
            'add_time' => time(),
            'add_ip' => app()->request->ip(),
            'last_time' => time(),
            'last_ip' => app()->request->ip(),
            'user_type' => $userType,
            'staff_id' => $user['staff_id'] ?? 0,
            'agent_id' => $user['agent_id'] ?? 0,
            'division_id' => $user['division_id'] ?? 0,
        ];
        if ($spreadUid) {
            $data['spread_uid'] = $spreadUid;
            $data['spread_time'] = time();
        }
        $agentData = array_intersect_key($data, array_flip(['staff_id', 'agent_id', 'division_id']));
        $agentAvailable = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices', 'action' => 'profile', 'args' => [0, []],
        ]);
        if (is_array($agentAvailable[0] ?? null)) {
            unset($data['staff_id'], $data['agent_id'], $data['division_id'], $data['spread_uid'], $data['spread_time']);
        }
        $res = $this->dao->save($data);
        if (!$res)
            throw new AdminException('保存用户信息失败');

        if (is_array($agentAvailable[0] ?? null) && $agentData) {
            event('DistributionServiceProxy', [
                'service' => 'DistributionUserServices', 'action' => 'saveProfile',
                'args' => [(int)$res->uid, $agentData],
            ]);
        }
        //用户生成后置事件
        event('UserRegister', [$spreadUid, $userType, $user['nickname'], $res->uid, 1]);

        return $res;
    }

    /**
     * 某些条件用户佣金总和
     * @param array $where
     * @return mixed
     */
    public function getSumBrokerage(array $where)
    {
        return $this->dao->getWhereSumField($where, 'brokerage_price');
    }

    /**
     * 根据条件获取用户指定字段列表
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getColumn(array $where, string $field = '*', string $key = '')
    {
        return $this->dao->getColumn($where, $field, $key);
    }

    /**
     * 获取某个用户的推广下线
     */
    public function getSpreadList($uid)
    {
        $one_uids = $this->getUserSpredadUids((int)$uid, 1);
        $two_uids = $this->getUserSpredadUids((int)$uid, 2);
        $uids = array_merge($one_uids, $two_uids);
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList(['uid' => $uids], 'uid,nickname,real_name,avatar,add_time', $page, $limit);
        foreach ($list as $k => $user) {
            $list[$k]['type'] = in_array($user['uid'], $one_uids) ? '一级' : '二级';
            $list[$k]['add_time'] = date('Y-m-d', $user['add_time']);
        }
        $count = count($uids);
        return compact('count', 'list');
    }

    /**查找多个uid信息
     * @param $uids
     * @param bool $field
     * @return UserDao|bool|\crmeb\basic\BaseModel|mixed|\think\Collection
     */
    public function getUserListByUids($uids, $field = false)
    {
        if (!$uids || !is_array($uids)) return false;
        return $this->dao->getUserListByUids($uids, $field);
    }

    /**
     * 获取分销用户
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgentUserList(array $where = [], string $field = '*', $is_page = true)
    {
        $where_data['status'] = 1;
        $where_data['is_promoter'] = 1;
        $where_data['spread_open'] = 1;
        //人人分销时  去除分销员字段的限制
        $store_brokerage_statu = sys_config('store_brokerage_statu');
        if ($store_brokerage_statu == 2) unset($where_data['is_promoter']);
        if (isset($where['nickname']) && $where['nickname'] !== '') {
            $where_data['like'] = $where['nickname'];
        }
        if (isset($where['data']) && $where['data']) {
            $where_data['time'] = $where['data'];
        }
        [$page, $limit] = $this->getPageValue($is_page);
        $list = $this->dao->getAgentUserList($where_data, $field, $page, $limit);
        $count = $this->dao->count($where_data);
        return compact('count', 'list');
    }

    /**
     * 获取分销员ids
     * @param array $where
     * @return array
     * @throws \ReflectionException
     */
    public function getAgentUserIds(array $where)
    {
        $where['status'] = 1;
        if (sys_config('store_brokerage_statu') != 2) $where['is_promoter'] = 1;
        $where['spread_open'] = 1;
        if (isset($where['nickname']) && $where['nickname'] !== '') {
            $where['like'] = $where['nickname'];
            unset($where['nickname']);
        }
        if (isset($where['data']) && $where['data']) {
            $where['time'] = $where['data'];
        }
        return $this->dao->getAgentUserIds($where);
    }

    /**
     * 获取推广人列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSairList(array $where, string $field = '*')
    {
        $where_data = [];
        if (isset($where['uid'])) {
            if (isset($where['type'])) {
                $type = (int)$where['type'];
                $type = in_array($type, [1, 2]) ? $type : 0;
                $uids = $this->getUserSpredadUids((int)$where['uid'], $type, $where['data'] ?? '');
                $where_data['uid'] = count($uids) > 0 ? $uids : 0;
            }
            if (isset($where['data']) && $where['data'] && !$this->agentRelationAvailable()) {
                $where_data['time'] = $where['data'];
            }
            if (isset($where['nickname']) && $where['nickname']) {
                $where_data['like'] = $where['nickname'];
            }
            $where_data['status'] = 1;
        }
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getSairList($where_data, '*', $page, $limit);
        $count = $this->dao->count($where_data);
        return compact('list', 'count');
    }

    /**
     * 获取推广人统计
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSairCount(array $where)
    {
        $where_data = [];
        if (isset($where['uid'])) {
            if (isset($where['type'])) {
                switch ((int)$where['type']) {
                    case 1:
                        $uids = $this->getUserSpredadUids((int)$where['uid'], 1, $where['data'] ?? '');
                        $where_data['uid'] = count($uids) > 0 ? $uids : 0;
                        break;
                    case 2:
                        $spread_uid_two = $this->getUserSpredadUids((int)$where['uid'], 2, $where['data'] ?? '');
                        $where_data['uid'] = count($spread_uid_two) > 0 ? $spread_uid_two : 0;
                        break;
                    default:
                        $uids = $this->getUserSpredadUids((int)$where['uid'], 0, $where['data'] ?? '');
                        $where_data['uid'] = count($uids) > 0 ? $uids : 0;
                        break;
                }
            }
            if (isset($where['data']) && $where['data'] && !$this->agentRelationAvailable()) {
                $where_data['time'] = $where['data'];
            }
            if (isset($where['nickname']) && $where['nickname']) {
                $where_data['like'] = $where['nickname'];
            }
            $where_data['status'] = 1;
        }
        return $this->dao->count($where_data);
    }

    /**
     * 写入用户信息
     * @param array $data
     * @return bool
     */
    public function create(array $data)
    {
        if (!$this->dao->save($data))
            throw new AdminException('保存成功');
        return true;
    }

    /**
     * 重置密码
     * @param $id
     * @param string $password
     * @return mixed
     */
    public function resetPwd(int $uid, string $password)
    {
        if (!$this->dao->update($uid, ['pwd' => $password]))
            throw new AdminException('密码重置失败');
        return true;
    }

    /**
     * 增加推广人数
     * @param int $uid
     * @param int $num
     * @return bool
     * @throws Exception
     */
    public function incSpreadCount(int $uid, int $num = 1)
    {
        $handled = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'changeSpreadCount',
            'args' => [$uid, $num],
        ]);
        if (is_array($handled) && ($handled[0] ?? null) !== null) {
            return (bool)$handled[0];
        }
        if (!$this->dao->incField($uid, 'spread_count', $num))
            throw new AdminException('增加推广人数失败');
        return true;
    }


    /**
     * 设置用户登录类型
     * @param int $uid
     * @param string $type
     * @return bool
     * @throws Exception
     */
    public function setLoginType(int $uid, string $type = 'h5')
    {
        if (!$this->dao->update($uid, ['login_type' => $type]))
            throw new AdminException('设置登录类型失败');
        return true;
    }

    /**
     * 设置推广员
     * @param int $uid
     * @param int $is_promoter
     * @return bool
     * @throws Exception
     */
    public function setIsPromoter(int $uid, $is_promoter = 1)
    {
        $handled = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'saveProfile',
            'args' => [$uid, ['is_promoter' => (int)$is_promoter]],
        ]);
        if (is_array($handled) && ($handled[0] ?? null) !== null) {
            return (bool)$handled[0];
        }
        if (!$this->dao->update($uid, ['is_promoter' => $is_promoter]))
            throw new AdminException('设置推广员失败');
        return true;
    }

    /**
     * 设置用户分组
     * @param $uids
     * @param int $group_id
     */
    public function setUserGroup($uids, int $group_id)
    {
        return $this->dao->batchUpdate($uids, ['group_id' => $group_id], 'uid');
    }

    /**
     * 减少用户佣金
     * @param int $uid
     * @param float $brokerage_price
     * @param float $price
     * @return bool
     * @throws Exception
     */
    public function cutBrokeragePrice(int $uid, $brokerage_price, $price)
    {
        $balance = bcsub((string)$brokerage_price, (string)$price, 2);
        $handled = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'saveProfile',
            'args' => [$uid, ['brokerage_price' => $balance]],
        ]);
        if (is_array($handled) && ($handled[0] ?? null) !== null) {
            return (bool)$handled[0];
        }
        if (!$this->dao->update($uid, ['brokerage_price' => $balance]))
            throw new AdminException('减少用户佣金失败');
        return true;
    }


    /**
     * 获取用户标签
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserLablel(array $uids)
    {
        /** @var UserLabelRelationServices $services */
        $services = app()->make(UserLabelRelationServices::class);
        $userlabels = $services->getUserLabelList($uids);
        $data = [];
        foreach ($uids as $uid) {
            $labels = array_filter($userlabels, function ($item) use ($uid) {
                if ($item['uid'] == $uid) {
                    return true;
                }
            });
            $data[$uid] = implode(',', array_column($labels, 'label_name'));
        }
        return $data;
    }

    /**
     * 会员列表
     * @param array $where
     * @return array
     */
    public function index(array $where)
    {
        /** @var UserWechatuserServices $userWechatUser */
        $userWechatUser = app()->make(UserWechatuserServices::class);
        // wechat_mp 模块可用时关联微信用户表字段（country/sex/openid 等），否则降级为纯用户主表字段
        $fields = 'u.*';
        if ($userWechatUser->canJoinWechatUser()) {
            $fields .= ',w.country,w.province,w.city,w.sex,w.unionid,w.openid,w.user_type as w_user_type,w.groupid,w.tagid_list,w.subscribe,w.subscribe_time';
        }
        [$list, $count] = $userWechatUser->getWhereUserList($where, $fields);
        if ($list) {
            $uids = array_column($list, 'uid');
            $userlabel = $this->getUserLablel($uids);
            $userGroup = app()->make(UserGroupServices::class)->getUsersGroupName(array_unique(array_column($list, 'group_id')));
            $userExtract = app()->make(UserExtractServices::class)->getUsersSumList($uids);
            $levelName = app()->make(SystemUserLevelServices::class)->getUsersLevel(array_unique(array_column($list, 'level')));
            $userLevel = app()->make(UserLevelServices::class)->getUsersLevelInfo($uids);
            $spread_names = $this->dao->getColumn([['uid', 'in', array_unique(array_column($list, 'spread_uid'))]], 'nickname', 'uid');
            foreach ($list as &$item) {
                if (empty($item['addres'])) {
                    if (!empty($item['country']) || !empty($item['province']) || !empty($item['city'])) {
                        $item['addres'] = $item['country'] . $item['province'] . $item['city'];
                    }
                }
                $item['status'] = ($item['status'] == 1) ? '正常' : '禁止';
                $item['birthday'] = $item['birthday'] ? date('Y-m-d', (int)$item['birthday']) : '';
                $item['extract_count_price'] = $userExtract[$item['uid']] ?? 0;//累计提现
                $item['spread_uid_nickname'] = $item['spread_uid'] ? ($spread_names[$item['spread_uid']] ?? '') . '(' . $item['spread_uid'] . ')' : '无';
                //用户类型
                if ($item['user_type'] == 'routine') {
                    $item['user_type'] = '小程序';
                } else if ($item['user_type'] == 'wechat') {
                    $item['user_type'] = '公众号';
                } else if ($item['user_type'] == 'h5') {
                    $item['user_type'] = 'H5';
                } else if ($item['user_type'] == 'app' || $item['user_type'] == 'apple') {
                    $item['user_type'] = 'APP';
                } else $item['user_type'] = '其他';
                // 微信用户表不可用（wechat_mp 模块未安装）时 sex 字段不存在，兜底为"保密"
                $item['sex'] = $item['sex'] ?? 0;
                if ($item['sex'] == 1) {
                    $item['sex'] = '男';
                } else if ($item['sex'] == 2) {
                    $item['sex'] = '女';
                } else $item['sex'] = '保密';
                //等级名称
                $item['level'] = $levelName[$item['level']] ?? '无';
                //分组名称
                $item['group_id'] = $userGroup[$item['group_id']] ?? '无';
                //用户等级
                $item['vip_name'] = false;
                $levelInfo = $userLevel[$item['uid']] ?? null;
                if ($levelInfo) {
                    if ($levelInfo && ($levelInfo['is_forever'] || time() < $levelInfo['valid_time'])) {
                        $item['vip_name'] = $item['level'] != '无' ? $item['level'] : false;
                    }
                }
                $item['agent_level_name'] = '无';
                $item['labels'] = $userlabel[$item['uid']] ?? '';
                $item['isMember'] = $item['is_money_level'] > 0 ? 1 : 0;
                $item['svip_over_day'] = $item['overdue_time'] ? ceil(($item['overdue_time'] - time()) / 86400) : '';
                $item['svip_overdue_time'] = date('Y-m-d', $item['overdue_time']);
                if (strpos($item['avatar'], '/statics/system_images/') !== false) {
                    $item['avatar'] = set_file_url($item['avatar']);
                }
            }
        }

        event('UserListExtend', ['list' => &$list]);

        // 渠道筛选 Tab：仅展示已安装且已启用的渠道模块
        $tabs = $this->getChannelTabs();

        return compact('count', 'list', 'tabs');
    }

    /**
     * 获取用户列表渠道筛选 Tab
     * 仅展示已安装且已启用的渠道模块（modules/channel/{端}/ 声明 type=channel），
     * 渠道标识与名称取各端 App::getFormType()/getName()，未安装的渠道不展示。
     * @return array
     */
    public function getChannelTabs(): array
    {
        $tabs = [['type' => '', 'name' => '全部']];
        $platforms = PlatformDetector::getRegisteredPlatforms();
        foreach ($platforms as $info) {
            $class = $info['class'] ?? '';
            if (!$class || !method_exists($class, 'getFormType') || !method_exists($class, 'getName')) {
                continue;
            }
            $tabs[] = ['type' => $class::getFormType(), 'name' => $class::getName()];
        }
        return $tabs;
    }

    /**
     * 获取修改页面数据
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit(int $id)
    {
        $user = $this->getUserInfo($id);
        if (!$user)
            throw new AdminException('数据不存在');
        $f = array();
        $f[] = Form::input('uid', '用户编号', $user->getData('uid'))->disabled(true);
        $f[] = Form::input('real_name', '真实姓名', $user->getData('real_name'));
        $f[] = Form::input('phone', '手机号码', $user->getData('phone'));

        $f[] = Form::date('birthday', '生日', $user->getData('birthday') ? date('Y-m-d', $user->getData('birthday')) : '');
        $f[] = Form::input('card_id', '身份证号', $user->getData('card_id'));
        $f[] = Form::input('addres', '用户地址', $user->getData('addres'));
        $f[] = Form::textarea('mark', '用户备注', $user->getData('mark'));
        $f[] = Form::input('pwd', '登录密码')->type('password')->placeholder('不改密码请留空');
        $f[] = Form::input('true_pwd', '确认密码')->type('password')->placeholder('不改密码请留空');

        //查询高于当前会员的所有会员等级
//        $grade = app()->make(UserLevelServices::class)->getUerLevelInfoByUid($id, 'grade');
        $systemLevelList = app()->make(SystemUserLevelServices::class)->getWhereLevelList([], 'id,name');
        $setOptionLevel = function () use ($systemLevelList) {
            $menus = [];
            foreach ($systemLevelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        };
        $f[] = Form::select('level', '用户等级', (int)$user->getData('level'))->setOptions(FormBuilder::setOptions($setOptionLevel))->filterable(true);
        $systemGroupList = app()->make(UserGroupServices::class)->getGroupList();
        $setOptionGroup = function () use ($systemGroupList) {
            $menus = [];
            foreach ($systemGroupList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
            }
            return $menus;
        };
        $f[] = Form::select('group_id', '用户分组', $user->getData('group_id'))->setOptions(FormBuilder::setOptions($setOptionGroup))->filterable(true);
        $systemLabelList = app()->make(UserLabelServices::class)->getLabelList();
        $labels = app()->make(UserLabelRelationServices::class)->getUserLabels($user['uid']);
        $setOptionLabel = function () use ($systemLabelList) {
            $menus = [];
            foreach ($systemLabelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['label_name']];
            }
            return $menus;
        };
        $f[] = Form::select('label_id', '用户标签', $labels)->setOptions(FormBuilder::setOptions($setOptionLabel))->filterable(true)->multiple(true);
        $f[] = Form::radio('status', '用户状态', $user->getData('status'))->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '锁定']]);
        return create_form('编辑', $f, Url::buildUrl('/user/user/' . $id), 'PUT');
    }

    /**
     * 添加用户表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function saveForm()
    {
        $f = array();
        $f[] = Form::input('real_name', '真实姓名', '')->placeholder('请输入真实姓名');
        $f[] = Form::input('phone', '手机号码', '')->placeholder('请输入手机号码')->required();
        $f[] = Form::date('birthday', '生日', '')->placeholder('请选择生日');
        $f[] = Form::input('card_id', '身份证号', '')->placeholder('请输入身份证号');
        $f[] = Form::input('addres', '用户地址', '')->placeholder('请输入用户地址');
        $f[] = Form::textarea('mark', '用户备注', '')->placeholder('请输入用户备注');
        $f[] = Form::input('pwd', '登录密码')->type('password')->placeholder('请输入登录密码');
        $f[] = Form::input('true_pwd', '确认密码')->type('password')->placeholder('请再次确认密码');
        $systemLevelList = app()->make(SystemUserLevelServices::class)->getWhereLevelList([], 'id,name');
        $setOptionLevel = function () use ($systemLevelList) {
            $menus = [];
            foreach ($systemLevelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        };
        $f[] = Form::select('level', '用户等级', '')->setOptions(FormBuilder::setOptions($setOptionLevel))->filterable(true);
        $systemGroupList = app()->make(UserGroupServices::class)->getGroupList();
        $setOptionGroup = function () use ($systemGroupList) {
            $menus = [];
            foreach ($systemGroupList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
            }
            return $menus;
        };
        $f[] = Form::select('group_id', '用户分组', '')->setOptions(FormBuilder::setOptions($setOptionGroup))->filterable(true);
        $systemLabelList = app()->make(UserLabelServices::class)->getLabelList();
        $setOptionLabel = function () use ($systemLabelList) {
            $menus = [];
            foreach ($systemLabelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['label_name']];
            }
            return $menus;
        };
        $f[] = Form::select('label_id', '用户标签', '')->setOptions(FormBuilder::setOptions($setOptionLabel))->filterable(true)->multiple(true);
        $f[] = Form::radio('status', '用户状态', 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '锁定']]);
        return create_form('添加用户', $f, $this->url('/user/user'), 'POST');
    }

    /**
     * 修改提交处理
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateInfo(int $id, array $data)
    {
        $user = $this->getUserInfo($id);
        if (!$user) {
            throw new AdminException('数据不存在');
        }
        $res2 = true;
        $edit = array();
        if ($data['money_status'] && $data['money']) {//余额增加或者减少
            /** @var UserMoneyServices $userMoneyServices */
            $userMoneyServices = app()->make(UserMoneyServices::class);
            if ($data['money_status'] == 1) {//增加
                $now_money = bcadd($user['now_money'], $data['money'], 2);
                $res1 = $userMoneyServices->moneyIncome('system_add', $user['uid'], $data['money'], $data['adminId'] ?? 0, $data['mark']);
                // moneyIncome 方法已包含：增加流水记录 + 更新余额，无需将 now_money 放入 $edit 数组
            } else if ($data['money_status'] == 2) {//减少
                if ($user['now_money'] > $data['money']) {
                    $now_money = bcsub($user['now_money'], $data['money'], 2);
                } else {
                    throw new AdminException('余额不足');
                }
                $res1 = $userMoneyServices->moneyIncome('system_sub', $user['uid'], $data['money'], $data['adminId'] ?? 0, $data['mark']);
            }
            // 推送订单
            event('OutPush', ['user_update_push', ['uid' => $id, 'type' => 'money', 'value' => $data['money_status'] == 2 ? -floatval($data['money']) : $data['money']]]);
            return true;
        } 
        if ($data['integration_status'] && $data['integration']) {//积分增加或者减少
            /** @var UserBillServices $userBill */
            $userBill = app()->make(UserBillServices::class);
            $integral_data = ['link_id' => $data['adminId'] ?? 0, 'number' => $data['integration']];
            if ($data['integration_status'] == 1) {//增加
                 $res2 = $userBill->billIntegralIncome('system_add', $user['uid'], $data['integration'],$data['adminId'] ?? 0,$data['mark']);
            } else if ($data['integration_status'] == 2) {//减少
                 $res2 = $userBill->billIntegralIncome('system_sub', $user['uid'], $data['integration'],$data['adminId'] ?? 0,$data['mark']);
            }
            // 推送订单
            event('OutPush', ['user_update_push', ['uid' => $id, 'type' => 'point', 'value' => $data['integration_status'] == 2 ? -intval($data['integration']) : $data['integration']]]);
            return true;
        }
        //修改基本信息
        if (!isset($data['is_other']) || !$data['is_other']) {
            app()->make(UserLabelRelationServices::class)->setUserLabel([$id], $data['label_id']);
            // pwd 在 User 模型 $hidden 中，$user 被 toArray() 转数组后该键不存在，需空值保护
            if (isset($data['pwd']) && $data['pwd'] && $data['pwd'] != ($user['pwd'] ?? '')) {
                $edit['pwd'] = $data['pwd'];
            }
            $edit['status'] = $data['status'];
            $edit['real_name'] = $data['real_name'];
            $edit['card_id'] = $data['card_id'];
            $edit['birthday'] = strtotime($data['birthday']);
            $edit['mark'] = $data['mark'];
            $edit['level'] = $data['level'];
            $edit['phone'] = $data['phone'];
            $edit['addres'] = $data['addres'];
            $edit['email'] = $data['email'] ?? '';
            $edit['group_id'] = $data['group_id'];
            if ($user['level'] != $data['level']) {
                /** @var UserLevelServices $userLevelService */
                $userLevelService = app()->make(UserLevelServices::class);
                $userLevelService->setUserLevel((int)$user['uid'], (int)$data['level']);
            }
        }
        if ($edit) {
            $res3 = $this->dao->update($id, $edit);
        } else {
            $res3 = true;
        }
        if ($res2 && $res3)
            return true;
        else throw new AdminException('修改失败');
    }

    /**
     * 编辑其他
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function editOther($id, $type)
    {
        $user = $this->getUserInfo($id);
        if (!$user) {
            throw new AdminException('数据不存在');
        }
        $f = array();
        if ($type == 'money') {
            $f[] = Form::radio('money_status', '修改余额', 1)->options([['value' => 1, 'label' => '增加'], ['value' => 2, 'label' => '减少']]);
            $f[] = Form::number('money', '余额', 0)->min(0)->max(999999.99);
        } else {
            $f[] = Form::radio('integration_status', '修改积分', 1)->options([['value' => 1, 'label' => '增加'], ['value' => 2, 'label' => '减少']]);
            $f[] = Form::number('integration', '积分', 0)->min(0)->precision(0)->max(999999);
        }
        $f[] = Form::input('mark', '备注', '')->type('textarea')->required();
        return create_form($type == 'money' ? '修改余额' : '修改积分', $f, Url::buildUrl('/user/update_other/' . $id), 'PUT');
    }

    /**
     * 设置会员分组
     * @param $id
     * @return mixed
     */
    public function setGroup($uids)
    {
        /** @var UserGroupServices $groupServices */
        $groupServices = app()->make(UserGroupServices::class);
        $userGroup = $groupServices->getGroupList();
        if (count($uids) == 1) {
            $user = $this->getUserInfo($uids[0], ['group_id']);
            $setOptionUserGroup = function () use ($userGroup) {
                $menus = [];
                foreach ($userGroup as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
                }
                return $menus;
            };
            $field[] = Form::select('group_id', '用户分组', $user->getData('group_id') != 0 ? $user->getData('group_id') : '')->setOptions(FormBuilder::setOptions($setOptionUserGroup))->filterable(true);
        } else {
            $setOptionUserGroup = function () use ($userGroup) {
                $menus = [];
                foreach ($userGroup as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
                }
                return $menus;
            };
            $field[] = Form::select('group_id', '用户分组')->setOptions(FormBuilder::setOptions($setOptionUserGroup))->filterable(true);
        }
        $field[] = Form::hidden('uids', implode(',', $uids));
        return create_form('设置用户分组', $field, Url::buildUrl('/user/save_set_group'), 'PUT');
    }

    /**
     * 保存会员分组
     * @param $id
     * @return mixed
     */
    public function saveSetGroup($uids, int $group_id)
    {
        /** @var UserGroupServices $userGroup */
        $userGroup = app()->make(UserGroupServices::class);
        if (!$userGroup->getGroup($group_id)) {
            throw new AdminException('该分组不存在');
        }
        if (!$this->setUserGroup($uids, $group_id)) {
            throw new AdminException('设置分组失败或无改动');
        }
        return true;
    }

    /**
     * 设置用户标签
     * @param $uids
     * @return mixed
     */
    public function setLabel($uids)
    {
        /** @var UserLabelServices $labelServices */
        $labelServices = app()->make(UserLabelServices::class);
        $userLabel = $labelServices->getLabelList();
        if (count($uids) == 1) {
            $lids = app()->make(UserLabelRelationServices::class)->getUserLabels($uids[0]);
            $setOptionUserLabel = function () use ($userLabel) {
                $menus = [];
                foreach ($userLabel as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['label_name']];
                }
                return $menus;
            };
            $field[] = Form::select('label_id', '用户标签', $lids)->setOptions(FormBuilder::setOptions($setOptionUserLabel))->filterable(true)->multiple(true);
        } else {
            $setOptionUserLabel = function () use ($userLabel) {
                $menus = [];
                foreach ($userLabel as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['label_name']];
                }
                return $menus;
            };
            $field[] = Form::select('label_id', '用户标签')->setOptions(FormBuilder::setOptions($setOptionUserLabel))->filterable(true)->multiple(true);
        }
        $field[] = Form::hidden('uids', implode(',', $uids));
        return create_form('设置用户标签', $field, Url::buildUrl('/user/save_set_label'), 'PUT');
    }

    /**
     * 保存用户标签
     * @return mixed
     */
    public function saveSetLabel($uids, $labels, $label_type = 0)
    {
        foreach ($labels as $id) {
            if (!app()->make(UserLabelServices::class)->getLable((int)$id)) {
                throw new AdminException('有标签不存在或被删除');
            }
        }
        /** @var UserLabelRelationServices $services */
        $services = app()->make(UserLabelRelationServices::class);
        if (!$services->setUserLabel($uids, $labels, $label_type)) {
            throw new AdminException('设置标签失败');
        }
        return true;
    }

    /**
     * 赠送会员等级
     * @param int $uid
     * @return mixed
     * */
    public function giveLevel($id)
    {
        if (!$this->getUserInfo($id)) {
            throw new AdminException('用户不存在');
        }
        //查询高于当前会员的所有会员等级
        $grade = app()->make(UserLevelServices::class)->getUerLevelInfoByUid($id, 'grade');
        $systemLevelList = app()->make(SystemUserLevelServices::class)->getWhereLevelList(['grade', '>', $grade ?? 0], 'id,name');

        $setOptionlevel = function () use ($systemLevelList) {
            $menus = [];
            foreach ($systemLevelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        };
        $field[] = Form::select('level_id', '用户等级')->setOptions(FormBuilder::setOptions($setOptionlevel))->filterable(true);
        return create_form('赠送等级', $field, Url::buildUrl('/user/save_give_level/' . $id), 'PUT');
    }

    /**
     * 执行赠送会员等级
     * @param int $id
     * @param int $level_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGiveLevel(int $id, int $level_id)
    {
        if (!$this->getUserInfo($id)) {
            throw new AdminException('用户不存在');
        }
        /** @var SystemUserLevelServices $systemLevelServices */
        $systemLevelServices = app()->make(SystemUserLevelServices::class);
        /** @var UserLevelServices $userLevelServices */
        $userLevelServices = app()->make(UserLevelServices::class);
        //查询当前选择的会员等级
        $systemLevel = $systemLevelServices->getLevel($level_id);
        if (!$systemLevel) throw new AdminException('等级不存在或被删除');
        //检查是否拥有此会员等级
        $level = $userLevelServices->getWhereLevel(['uid' => $id, 'level_id' => $level_id], 'valid_time,is_forever');
        if ($level && $level['status'] == 1 && $level['is_del'] == 0) {
            throw new AdminException('此用户已有该用户等级，无法再次赠送');
        }
        //保存会员信息
        if (!$userLevelServices->setUserLevel($id, $level_id, $systemLevel)) {
            throw new AdminException('赠送失败');
        }
        return true;
    }

    /**
     * 赠送付费会员时长
     * @param $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function giveLevelTime($id)
    {
        $userInfo = $this->getUserInfo($id);
        if (!$userInfo) {
            throw new AdminException('用户不存在');
        }
        if ($userInfo['is_ever_level'] == 1) {
            $timeDiff = '永久';
        } else {
            $timeDiff = $userInfo['overdue_time'] < time() ? '无' : date('Y-m-d H:i:s', $userInfo['overdue_time']);
        }
        $dayDiff = $userInfo['overdue_time'] > time() ? intval(($userInfo['overdue_time'] - time()) / 86400) : 0;
        $field[] = Form::input('time_diff', '到期时间', $timeDiff)->readonly(true);
        if ($userInfo['is_ever_level'] == 0) {
            $field[] = Form::input('day_diff', '剩余天数', $dayDiff)->readonly(true);
            $field[] = Form::number('days', '增加时长(天)')->precision(0)->required();
        }
        return create_form('赠送付费会员时长', $field, Url::buildUrl('/user/save_give_level_time/' . $id), 'PUT');
    }

    /**
     * 执行赠送付费会员时长
     * @param int $id
     * @param int $days
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGiveLevelTime(int $id, int $days)
    {
        $userInfo = $this->getUserInfo($id);
        if ($userInfo->is_ever_level == 1) {
            return true;
        }
        if (!$userInfo) {
            throw new AdminException('用户不存在');
        }
        if ($days == 0) throw new AdminException('赠送天数不能为0');
        if ($days < -1) throw new AdminException('天数输入错误');
        if ($userInfo->is_money_level == 0) {
            $userInfo->is_money_level = 3;
            if ($days == -1) {
                $userInfo->is_ever_level = 1;
                $time = 0;
            } else {
                $userInfo->overdue_time = $time = time() + ($days * 86400);
            }
        } else {
            if ($days == -1) {
                $userInfo->is_ever_level = 1;
                $time = 0;
            } else {
                $userInfo->overdue_time = $time = $userInfo->overdue_time + ($days * 86400);
            }
        }
        $userInfo->save();
        /** @var OrderCenterServices $orderCenterService */
        $orderCenterService = app()->make(OrderCenterServices::class);
        $orderInfo = [
            'uid' => $id,
            'order_id' => $orderCenterService->getNewOrderId(),
            'type' => 3,
            'member_type' => 0,
            'pay_type' => 'admin',
            'paid' => 1,
            'pay_time' => time(),
            'is_free' => 1,
            'overdue_time' => $time,
            'vip_day' => $days,
            'add_time' => time()
        ];
        /** @var MemberOrderServices $memberOrder */
        $memberOrder = app()->make(MemberOrderServices::class);
        $memberOrder->save($orderInfo);
        return true;
    }

    /**
     * 清除会员等级
     * @paran int $uid
     * @paran boolean
     * */
    public function cleanUpLevel($uid)
    {
        if (!$this->getUserInfo($uid))
            throw new AdminException('用户不存在');
        /** @var UserLevelServices $services */
        $services = app()->make(UserLevelServices::class);
        return $this->transaction(function () use ($uid, $services) {
            $res = $services->delUserLevel($uid);
            $res1 = $this->dao->update($uid, ['clean_time' => time(), 'level' => 0, 'exp' => 0], 'uid');
            if (!$res && !$res1)
                throw new AdminException('清除失败');
            return true;
        });
    }

    /**
     * 用户详细信息
     * @param int $uid
     * @param array $userIfno
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserDetailed(int $uid, $userIfno = [])
    {
        /** @var UserAddressServices $userAddress */
        $userAddress = app()->make(UserAddressServices::class);
        $field = 'real_name,phone,province,city,district,detail,post_code';
        $address = $userAddress->getUserDefaultAddress($uid, $field);
        if (!$address) {
            $address = $userAddress->getUserAddressList($uid, $field);
            $address = $address[0] ?? [];
        }
        $userInfo = $this->getUserInfo($uid);
        return [
            ['name' => '默认收货地址', 'value' => $address ? '收货人:' . $address['real_name'] . '邮编:' . $address['post_code'] . ' 收货人电话:' . $address['phone'] . ' 地址:' . $address['province'] . ' ' . $address['city'] . ' ' . $address['district'] . ' ' . $address['detail'] : ''],
            ['name' => '手机号码', 'value' => $userInfo['phone']],
            ['name' => '姓名', 'value' => ''],
            ['name' => '微信昵称', 'value' => $userInfo['nickname']],
            ['name' => '头像', 'value' => $userInfo['avatar']],
            ['name' => '邮箱', 'value' => ''],
            ['name' => '生日', 'value' => ''],
            ['name' => '积分', 'value' => $userInfo['integral']],
            ['name' => '上级推广人', 'value' => $userInfo['spread_uid'] ? $this->getUserInfo($userInfo['spread_uid'], ['nickname'])['nickname'] ?? '' : ''],
            ['name' => '账户余额', 'value' => $userInfo['now_money']],
            ['name' => '佣金总收入', 'value' => app()->make(UserBillServices::class)->getBrokerageSum($uid)],
            ['name' => '提现总金额', 'value' => app()->make(UserExtractServices::class)->getUserExtract($uid)],
        ];

    }

    /**
     * 获取用户详情里面的用户消费能力和用户余额积分等
     * @param $uid
     * @return array[]
     */
    public function getHeaderList(int $uid, $userInfo = [])
    {
        if (!$userInfo) {
            $userInfo = $this->getUserInfo($uid);
        }
        /** @var OrderCenterDao $orderServices */
        $orderServices = app()->make(OrderCenterDao::class);
        $where = ['uid' => $uid, 'pay_status' => 1, 'refund_status' => 0];
        // 本月时间范围，中台聚合按 pay_time 过滤
        $month_start_time = date('Y-m-01 00:00:00');
        $whereMonth = $where + ['timeKey' => [
            'start_time' => strtotime($month_start_time),
            'end_time' => strtotime(date('Y-m-d 23:59:59', strtotime("$month_start_time +1 month -1 day"))),
            'days' => 30,
        ]];
        return [
            [
                'title' => '余额',
                'value' => $userInfo['now_money'] ?? 0,
                'key' => '元',
            ],
            [
                'title' => '总计订单',
                'value' => $orderServices->getOrderCountByWhere($where),
                'key' => '笔',
            ],
            [
                'title' => '总消费金额',
                'value' => $orderServices->getWhereSumField($where, 'pay_price'),
                'key' => '元',
            ],
            [
                'title' => '积分',
                'value' => $userInfo['integral'] ?? 0,
                'key' => '',
            ],
            [
                'title' => '本月订单',
                'value' => $orderServices->getOrderCountByWhere($whereMonth),
                'key' => '笔',
            ],
            [
                'title' => '本月消费金额',
                'value' => $orderServices->getWhereSumField($whereMonth, 'pay_price'),
                'key' => '元',
            ]
        ];
    }


    /**
     * 获取用户记录里的积分总数和签到总数和余额变动总数
     * @param $uid
     * @return array
     */
    public function getUserBillCountData($uid)
    {
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        $integral_count = $userBill->getIntegralCount($uid);
        $sign_count = $userBill->getSignCount($uid);
        $balanceChang_count = $userBill->getBrokerageCount($uid);
        return [$integral_count, $sign_count, $balanceChang_count];
    }

    /**
     * 用户详情
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read(int $uid)
    {
        $userInfo = $this->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException('数据不存在');
        }
        $userInfo['avatar'] = strpos($userInfo['avatar'], 'http') === false ? (sys_config('site_url') . $userInfo['avatar']) : $userInfo['avatar'];
        $userInfo['overdue_time'] = date('Y-m-d H:i:s', $userInfo['overdue_time']);
        $userInfo['birthday'] = $userInfo['birthday'] < 0 ? 0 : $userInfo['birthday'];
        if ($userInfo['addres'] === '') {
            $defaultAddressInfo = app()->make(UserAddressServices::class)->getUserDefaultAddress($uid);
            if ($defaultAddressInfo) {
                $userInfo['addres'] = $defaultAddressInfo['province'] . $defaultAddressInfo['city'] . $defaultAddressInfo['district'] . $defaultAddressInfo['detail'];
            } else {
                $userInfo['addres'] = '';
            }
        }
        $userInfo['vip_name'] = app()->make(SystemUserLevelServices::class)->value(['id' => $userInfo['level']], 'name');
        $userInfo['group_name'] = app()->make(UserGroupServices::class)->value(['id' => $userInfo['group_id']], 'group_name');
        $userInfo['spread_uid_nickname'] = $this->dao->value(['uid' => $userInfo['spread_uid']], 'nickname') . '/' . $userInfo['spread_uid'];
        $userInfo['label_list'] = implode(',', array_column(app()->make(UserLabelRelationServices::class)->getUserLabelList([$uid]), 'label_name'));
        return [
            'uid' => $uid,
            'userinfo' => $this->getUserDetailed($uid, $userInfo),
            'headerList' => $this->getHeaderList($uid, $userInfo),
            'count' => $this->getUserBillCountData($uid),
            'ps_info' => $userInfo
        ];
    }

    /**
     * 获取好友
     * @param int $id
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFriendList(int $id, string $field = 'uid,nickname,level,add_time,spread_time')
    {
        [$page, $limit] = $this->getPageValue();
        $uids = $this->getUserSpredadUids($id, 1);
        $list = $uids ? $this->dao->getList(['uid' => $uids], $field, $page, $limit) : [];
        /** @var SystemUserLevelServices $systemLevelServices */
        $systemLevelServices = app()->make(SystemUserLevelServices::class);
        $systemLevelList = $systemLevelServices->getWhereLevelList([], 'id,name');
        if ($systemLevelServices) $systemLevelServices = array_combine(array_column($systemLevelList, 'id'), $systemLevelList);
        foreach ($list as &$item) {
            $relation = event('DistributionServiceProxy', ['service' => 'DistributionRelationServices', 'action' => 'relation', 'args' => [(int)$item['uid'], $item]]);
            if (is_array($relation[0] ?? null)) {
                $item = array_replace($item, $relation[0]);
            }
            $item['type'] = $systemLevelServices[$item['level']]['name'] ?? '暂无';
            $item['add_time'] = $item['spread_time'] && is_numeric($item['spread_time']) ? date('Y-m-d H:i:s', $item['spread_time']) : '';
        }
        $count = count($uids);
        return compact('list', 'count');
    }

    /**
     * 获取单个用户信息
     * @param $id 用户id
     * @return mixed
     */
    public function oneUserInfo(int $id, string $type)
    {
        switch ($type) {
            case 'spread':
//                /** @var UserFriendsServices $services */
//                $services = app()->make(UserFriendsServices::class);
//                return $services->getFriendList(['uid' => $id], ['level', 'nickname']);
                return $this->getFriendList($id);
            case 'order':
                /** @var \app\services\order\OrderCenterServices $services */
                $services = app()->make(\app\services\order\OrderCenterServices::class);
                [$page, $limit] = $this->getPageValue();
                return $services->getUserList($id, [], $page, $limit);
            case 'integral':
                /** @var UserBillServices $services */
                $services = app()->make(UserBillServices::class);
                return $services->getIntegralList($id, [], 'title,number,balance,mark,add_time,frozen_time,pm');
            case 'sign':
                /** @var UserBillServices $services */
                $services = app()->make(UserBillServices::class);
                return $services->getSignList($id, [], 'title,number,mark,add_time');
            case 'coupon':
                /** @var StoreCouponUserServices $services */
                $services = app()->make(StoreCouponUserServices::class);
                return $services->getUserCouponList($id);
            case 'balance_change':
                /** @var UserMoneyServices $services */
                $services = app()->make(UserMoneyServices::class);
                return $services->balanceList(['uid' => $id]);
            default:
                throw new AdminException('参数错误');
        }
    }

    /**获取特定时间用户访问量
     * @param $time
     * @param $week
     * @return int
     */
    public function todayLastVisits($time, $week)
    {
        return $this->dao->todayLastVisit($time, $week);
    }

    /**获取特定时间新增用户
     * @param $time
     * @param $week
     * @return int
     */
    public function todayAddVisits($time, $week)
    {
        return $this->dao->todayAddVisit($time, $week);
    }

    /**
     * 用户图表
     */
    public function userChart()
    {
        $starday = date('Y-m-d', strtotime('-30 day'));
        $yesterday = date('Y-m-d', strtotime('+1 day'));

        $user_list = $this->dao->userList($starday, $yesterday);
        $chartdata = [];
        $data = [];
        $chartdata['legend'] = ['用户数'];//分类
        $chartdata['yAxis']['maxnum'] = 0;//最大值数量
        $chartdata['xAxis'] = [date('m-d')];//X轴值
        $chartdata['series'] = [0];//分类1值
        if (!empty($user_list)) {
            foreach ($user_list as $k => $v) {
                $data['day'][] = $v['day'];
                $data['count'][] = $v['count'];
                if ($chartdata['yAxis']['maxnum'] < $v['count'])
                    $chartdata['yAxis']['maxnum'] = $v['count'];
            }
            $chartdata['xAxis'] = $data['day'];//X轴值
            $chartdata['series'] = $data['count'];//分类1值
        }
        $chartdata['bing_xdata'] = ['未消费用户', '消费一次用户', '留存客户', '回流客户'];
        $color = ['#5cadff', '#b37feb', '#19be6b', '#ff9900'];
        $pay[0] = $this->dao->count(['pay_count' => 0]);
        $pay[1] = $this->dao->count(['pay_count' => 1]);
        $pay[2] = $this->dao->userCount(1);
        $pay[3] = $this->dao->userCount(2);
        foreach ($pay as $key => $item) {
            $bing_data[] = ['name' => $chartdata['bing_xdata'][$key], 'value' => $pay[$key], 'itemStyle' => ['color' => $color[$key]]];
        }
        $chartdata['bing_data'] = $bing_data;
        return $chartdata;
    }

    /***********************************************/
    /************ 前端api services *****************/
    /***********************************************/

    /**
     * 用户信息
     * @param $info
     * @return mixed
     */
    public function userInfo($info)
    {
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        $uid = (int)$info['uid'];
        $agentProfile = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'profile',
            'args' => [$uid, (array)$info],
        ]);
        if (is_array($agentProfile) && is_array($agentProfile[0] ?? null)) {
            $info = array_replace((array)$info, $agentProfile[0]);
        }
        $broken_time = intval(sys_config('extract_time'));
        $search_time = time() - 86400 * $broken_time;
        //改造时间
        $search_time = '1970/01/01' . ' - ' . date('Y/m/d H:i:s', $search_time);
        //可提现佣金
        //返佣 +
        $brokerage_commission = (string)$userBill->getUsersBokerageSum(['uid' => $uid, 'pm' => 1], $search_time);
        //退款退的佣金 -
        $refund_commission = (string)$userBill->getUsersBokerageSum(['uid' => $uid, 'pm' => 0], $search_time);
        $info['broken_commission'] = bcsub($brokerage_commission, $refund_commission, 2);
        if ($info['broken_commission'] < 0)
            $info['broken_commission'] = 0;
        $info['commissionCount'] = bcsub($info['brokerage_price'], $info['broken_commission'], 2);
        if ($info['commissionCount'] < 0)
            $info['commissionCount'] = 0;
        return $info;
    }

    /**
     * 个人中心
     * @param array $user
     */
    public function personalHome(array $user, $tokenData)
    {
        $uid = (int)$user['uid'];
        $agentProfile = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'profile',
            'args' => [$uid, $user],
        ]);
        if (is_array($agentProfile) && is_array($agentProfile[0] ?? null)) {
            $user = array_replace($user, $agentProfile[0]);
        }
        $userInfo = $user;
        /** @var StoreCouponUserServices $storeCoupon */
        $storeCoupon = app()->make(StoreCouponUserServices::class);
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        /** @var UserExtractServices $userExtract */
        $userExtract = app()->make(UserExtractServices::class);
        /** @var OrderCenterDao $orderCenterDao */
        $orderCenterDao = app()->make(OrderCenterDao::class);
        /** @var UserLevelServices $userLevel */
        $userLevel = app()->make(UserLevelServices::class);
        /** @var UserInvoiceServices $userInvoice */
        $userInvoice = app()->make(UserInvoiceServices::class);
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        /** @var StoreProductRelationServices $collect */
        $collect = app()->make(StoreProductRelationServices::class);
        /** @var \app\services\message\NoticeServices $noticeService */
        $noticeService = app()->make(\app\services\message\NoticeServices::class);
        /** @var DiyServices $diyServices */
        $diyServices = app()->make(DiyServices::class);
        //看付费会员是否开启
        $isOpenMember = $memberCardService->isOpenMemberCard();
        $user['is_open_member'] = $isOpenMember;
        $user['agent_level_name'] = '';
        //会员领取优惠券
        // $couponService->sendMemberCoupon($uid);
        //看是否会员过期
        $this->offMemberLevel($uid, $userInfo);
        $user['couponCount'] = $storeCoupon->getUserValidCouponCount((int)$uid);
        $user['like'] = app()->make(StoreProductRelationServices::class)->getUserCollectCount($user['uid']);
        $user['orderStatusNum'] = $this->getOrderStatusNum($orderCenterDao, $uid);
        $user['notice'] = 0;
        /** @var UserMoneyServices $userMoney */
        $userMoney = app()->make(UserMoneyServices::class);

        $user['recharge'] = $userMoney->sum([
            ['uid', '=', $uid], ['pm', '=', 1], ['type', 'in', ['recharge', 'system_add', 'extract', 'register_system_add', 'lottery_add']]
        ], 'number');
        $user['orderStatusSum'] = bcsub((string)$user['recharge'], (string)$user['now_money'], 2);
        $user['extractTotalPrice'] = $userExtract->getExtractSum(['uid' => $uid, 'status' => 1]);//累计提现
        $user['extractPrice'] = $user['brokerage_price'];//可提现
        $user['statu'] = (int)sys_config('store_brokerage_statu');
        if (!$user['is_promoter']) {
            $price = $orderCenterDao->sum([['pay_status', '>', 0], ['refund_status', '=', 0], ['uid', '=', $user['uid']]], 'pay_price');
            if (is_brokerage_statu($price)) {
                $this->setIsPromoter((int)$uid, 1);
                $user['is_promoter'] = 1;
            }
        }
        /** @var UserBrokerageServices $frozenPrices */
        $frozenPrices = app()->make(UserBrokerageServices::class);
        $user['broken_commission'] = $frozenPrices->getUserFrozenPrice($uid);
        if ($user['broken_commission'] < 0)
            $user['broken_commission'] = 0;
        $user['commissionCount'] = bcsub((string)$user['brokerage_price'], (string)$user['broken_commission'], 2);
        if ($user['commissionCount'] < 0)
            $user['commissionCount'] = 0;
        if (!sys_config('member_func_status'))
            $user['vip'] = false;
        else {
            $userLevel = $userLevel->getUerLevelInfoByUid($user['uid']);
            $user['vip'] = (bool)$userLevel;
            if ($user['vip']) {
                $user['vip_id'] = $userLevel['id'] ?? 0;
                $user['vip_icon'] = set_file_url($userLevel['icon']) ?? '';
                $user['vip_name'] = $userLevel['name'] ?? '';
            }
        }
        $user['yesterDay'] = $frozenPrices->getUsersBokerageSum(['uid' => $uid, 'pm' => 1], 'yesterday');
        $user['recharge_switch'] = (int)sys_config('recharge_switch');//充值开关
        $user['adminid'] = (bool)(event('KefuConfigQuery', ['uid' => $uid, 'customer' => 1])[0] ?? false);
        if ($user['phone'] && $user['user_type'] != 'h5') {
            $user['switchUserInfo'][] = $userInfo;
            $h5UserInfo = $this->dao->getOne(['account' => $user['phone'], 'user_type' => 'h5']);
            if ($h5UserInfo) {
                $user['switchUserInfo'][] = $h5UserInfo;
            }
        } else if ($user['phone'] && $user['user_type'] == 'h5') {
            $wechatUserInfo = $this->getOne([['phone', '=', $user['phone']], ['user_type', '<>', 'h5']]);
            if ($wechatUserInfo) {
                $user['switchUserInfo'][] = $wechatUserInfo;
            }
            $user['switchUserInfo'][] = $userInfo;
        } else if (!$user['phone']) {
            $user['switchUserInfo'][] = $userInfo;
        }
        $user['broken_day'] = (int)sys_config('extract_time');//佣金冻结时间
        $user['balance_func_status'] = (int)sys_config('balance_func_status', 0);
        $invoice_func = $userInvoice->invoiceFuncStatus();
        $user['invioce_func'] = $invoice_func['invoice_func'];
        $user['special_invoice'] = $invoice_func['special_invoice'];
        $user['collectCount'] = $collect->count(['uid' => $uid]);
        $user['spread_status'] = $this->checkUserPromoter($user['uid']);
        $user['pay_vip_status'] = $user['is_ever_level'] || ($user['is_money_level'] && $user['overdue_time'] > time());
        $user['member_style'] = (int)$diyServices->getColorChange('member');
        if ($user['is_ever_level']) {
            $user['vip_status'] = 1;//永久会员
        } else {
            if (!$user['is_money_level'] && $user['overdue_time'] && $user['overdue_time'] < time()) {
                $user['vip_status'] = -1;//开通过已过期
            } else if (!$user['overdue_time'] && !$user['is_money_level']) {
                $user['vip_status'] = 2;//没有开通过
            } else if ($user['is_money_level'] && $user['overdue_time'] && $user['overdue_time'] > time()) {
                $user['vip_status'] = 3;//开通了，没有到期
            }
        }
        $user['svip_open'] = (bool)sys_config('member_card_status');
        // 客服聊天未读消息数，通过客服插件事件代理查询，避免 app 层引用客服服务
        $service_num = 0;
        $serviceNumResults = event('KefuServiceProxy', ['service' => 'StoreServiceRecordServices', 'action' => 'sum', 'args' => [['user_id' => $uid], 'mssage_num']]);
        if (is_array($serviceNumResults)) {
            foreach ($serviceNumResults as $r) {
                if ($r !== null) {
                    $service_num = (int)$r;
                    break;
                }
            }
        }
        $message = $noticeService->getUnreadCount($uid);
        $user['service_num'] = $service_num + $message;
        $user['spread_level_count'] = 0;
        $user['extract_type'] = sys_config('extract_type');
        $user['integral'] = intval($user['integral']);
        $user['is_agent_level'] = 0;
        $user['division_open'] = (int)sys_config('division_status', 0);
        $user['agent_apply_open'] = (int)sys_config('agent_apply_open', 0);
        $user['is_default_avatar'] = $user['avatar'] == sys_config('h5_avatar') ? 1 : 0;
        $user['avatar'] = strpos($user['avatar'], '/statics/system_images/') !== false ? set_file_url($user['avatar']) : $user['avatar'];
        $user['member_func_status'] = (int)sys_config('member_func_status');
        $user['visit_num'] = app()->make(StoreProductLogServices::class)->getCountByUser($uid);
        
        event('UserDetailExtend', ['user' => &$user]);
        
        return $user;
    }

    /**
     * 获取用户各状态订单数量（基于 eb_order_center 表）
     * @param OrderCenterDao $orderCenterDao 订单中台DAO
     * @param int $uid 用户ID
     * @return array 返回各状态订单数
     */
    private function getOrderStatusNum(OrderCenterDao $orderCenterDao, int $uid): array
    {
        $data = [];
        $baseWhere = ['is_del' => 0];
        if ($uid) {
            $baseWhere['uid'] = $uid;
        }

        // 全部订单数（不含退款中/已退款/已取消）
        $data['order_count'] = (string)$orderCenterDao->count($baseWhere + [
            'status_code' => ['pending_payment', 'processing', 'completed'],
        ]);

        // 累计消费金额（已支付且未退款的）
        $data['sum_price'] = (string)$orderCenterDao->sum([
            ['uid', '=', $uid],
            ['pay_status', '>', 0],
            ['refund_status', '=', 0],
        ], 'pay_price');

        // 待付款
        $data['unpaid_count'] = (string)$orderCenterDao->count($baseWhere + ['status_code' => 'pending_payment']);
        // 待发货：processing 且 delivery_time 为空
        $data['unshipped_count'] = (string)$orderCenterDao->count($baseWhere + [
            'status_code' => 'processing',
            ['delivery_time', 'null', ''],
        ]);
        // 待收货：processing 且 delivery_time 不为空、take_time 为空
        $data['received_count'] = (string)$orderCenterDao->count($baseWhere + [
            'status_code' => 'processing',
            ['delivery_time', 'not null', ''],
            ['take_time', 'null', ''],
        ]);
        // 待评价/已完成：status_code = completed，需 JOIN 扩展表才能精确区分，此处统一取值
        $data['evaluated_count'] = (string)$orderCenterDao->count($baseWhere + ['status_code' => 'completed']);
        $data['complete_count'] = $data['evaluated_count'];

        // 退款相关统计（售后引擎状态分组统计）
        /** @var \app\services\order\AfterSaleServices $afterSaleServices */
        $afterSaleServices = app()->make(\app\services\order\AfterSaleServices::class);
        $data['refunding_count'] = (string)$afterSaleServices->getCountByStatusGroup($uid ?: null, 'refunding');
        $data['no_refund_count'] = (string)$afterSaleServices->getCountByStatusGroup($uid ?: null, 'rejected');
        $data['refunded_count'] = (string)$afterSaleServices->getCountByStatusGroup($uid ?: null, 'refunded');
        $data['refund_count'] = bcadd(bcadd($data['refunding_count'], $data['refunded_count'], 0), $data['no_refund_count'], 0);

        // 支付方式相关
        $payChannelServices = app()->make(\app\services\driver\pay\PayChannelServices::class);
        $data['yue_pay_status'] = $payChannelServices->getChannelStatus('yue') ? (int)1 : (int)2;
        $data['pc_order_count'] = $data['order_count'] + $data['refunding_count'] + $data['refunded_count'];
        $data['pay_weixin_open'] = $payChannelServices->getChannelStatus('weixin') || $payChannelServices->getChannelStatus('weixin_v3');
        $data['ali_pay_status'] = $payChannelServices->getChannelStatus('alipay');
        $data['friend_pay_status'] = $payChannelServices->getChannelStatus('friend') ? 1 : 0;

        return $data;
    }

    /**
     * 用户资金统计
     * @param int $uid
     */
    public function balance(int $uid)
    {
        $userInfo = $this->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException('用户不存在');
        }
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        /** @var OrderCenterDao $storeOrder */
        $storeOrder = app()->make(OrderCenterDao::class);
        $user['now_money'] = $userInfo['now_money'];//当前总资金
        $user['recharge'] = $userBill->getRechargeSum($uid);//累计充值
        $user['orderStatusSum'] = $storeOrder->sum(['uid' => $uid, 'pay_status' => 1, 'is_del' => 0], 'pay_price');//累计消费
        return $user;
    }

    /**
     * 用户修改信息
     * @param Request $request
     * @return mixed
     */
    public function eidtNickname(int $uid, array $data)
    {
        $info = $this->dao->get(['uid' => $uid]);
        if (!$info) {
            throw new ApiException('用户不存在');
        }
        if (!$this->dao->update($uid, $data, 'uid')) {
            throw new ApiException('修改失败');
        }

        return true;
    }

    /**
     * 获取推广人排行
     * @param $data 查询条件
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRankList(array $data)
    {
        $startTime = strtotime('this week Monday');
        $endTime = time();
        switch ($data['type']) {
            case 'week':
                $startTime = strtotime('this week Monday');
                break;
            case 'month':
                $startTime = strtotime('last month');
                break;
        }
        [$page, $limit] = $this->getPageValue();
        $agentRank = event('DistributionServiceProxy', [
            'service' => 'DistributionRelationServices', 'action' => 'rankList',
            'args' => [$startTime, $endTime, $page, $limit],
        ]);
        if (is_array($agentRank[0] ?? null)) {
            return $agentRank[0];
        }
        $field = 't0.uid,t0.spread_uid,count(t1.spread_uid) AS count,t0.add_time,t0.nickname,t0.avatar';
        return $this->dao->getAgentRankList([$startTime, $endTime], $field, $page, $limit);
    }

    /**
     * 静默绑定推广人
     * @param int $uid
     * @param int $spreadUid
     * @param $code
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function spread(int $uid, int $spreadUid, $code, $agent_id)
    {
        $userInfo = $this->dao->getOne(['uid' => $uid]);
        if (!$userInfo) {
            throw new ApiException('数据不存在');
        }
        $userArray = $userInfo->toArray();
        $profile = event('DistributionServiceProxy', ['service' => 'DistributionUserServices', 'action' => 'profile', 'args' => [$uid, $userArray]]);
        $relation = event('DistributionServiceProxy', ['service' => 'DistributionRelationServices', 'action' => 'relation', 'args' => [$uid, $userArray]]);
        $pluginAgentEnabled = is_array($profile[0] ?? null) && is_array($relation[0] ?? null);
        if ($pluginAgentEnabled) {
            $userArray = array_replace($userArray, $profile[0], $relation[0]);
        }
        if ($code && !$spreadUid) {
            /** @var QrcodeServices $qrCode */
            $qrCode = app()->make(QrcodeServices::class);
            if ($info = $qrCode->getOne(['id' => $code, 'status' => 1])) {
                if ($info['third_type'] == 'agent') {
                    $agent_id = $info['third_id'];
                } else {
                    $spreadUid = $info['third_id'];
                }
            }
        }
        if ($agent_id) {
            $spreadInfo = $this->dao->getOne(['uid' => $agent_id]);
            if ($agent_id == $uid) {
                return '自己不能推荐自己';
            } else if (!$userInfo) {
                return '用户不存在';
            } else if (!$spreadInfo) {
                return '上级用户不存在';
            } else if (!empty($userArray['is_division'])) {
                return '您是事业部,不能绑定成为别人的员工';
            } else if (!empty($userArray['is_agent'])) {
                return '您是代理商,不能绑定成为别人的员工';
            } else if (app()->make(LoginServices::class)->updateUserInfo(['code' => $agent_id, 'is_staff' => 1], $userInfo, false)) {
                return '绑定店员成功!';
            }
        }
        if ($spreadUid == 0) return '不绑定';
        $upperRelation = event('DistributionServiceProxy', ['service' => 'DistributionRelationServices', 'action' => 'relation', 'args' => [$spreadUid, []]]);
        $userSpreadUid = (int)($upperRelation[0]['spread_uid'] ?? $this->dao->value(['uid' => $spreadUid], 'spread_uid'));
        //记录好友关系
        if ($spreadUid && $uid && $spreadUid != $uid) {
            /** @var UserFriendsServices $serviceFriend */
            $serviceFriend = app()->make(UserFriendsServices::class);
            $serviceFriend->saveFriend([
                'uid' => $uid,
                'friends_uid' => $spreadUid,
            ]);
        }
        $check = false;
        if (sys_config('brokerage_bindind') == 1) {
            if (sys_config('store_brokerage_binding_status') == 1) {
                if (!$userArray['spread_uid']) {
                    $check = true;
                }
            } elseif (sys_config('store_brokerage_binding_status') == 2 && (($userArray['spread_time'] + (sys_config('store_brokerage_binding_time') * 86400)) < time())) {
                $check = true;
            } elseif (sys_config('store_brokerage_binding_status') == 3) {
                $check = true;
            }
        } elseif (sys_config('brokerage_bindind') == 2) {
            if ($userArray['add_time'] == $userArray['last_time'] && $userArray['spread_uid'] == 0) {
                $check = true;
            }
        }
        if ($userArray['uid'] == $spreadUid || $userArray['uid'] == $userSpreadUid) $check = false;
        if ($check) {
            $spreadLegacy = $this->dao->get($spreadUid);
            $spreadProfile = event('DistributionServiceProxy', ['service' => 'DistributionUserServices', 'action' => 'profile', 'args' => [$spreadUid, $spreadLegacy ? $spreadLegacy->toArray() : []]]);
            $spreadInfo = is_array($spreadProfile[0] ?? null) ? $spreadProfile[0] : ($spreadLegacy ? $spreadLegacy->toArray() : []);
            $data = [];
            $data['spread_uid'] = $spreadUid;
            $data['spread_time'] = time();
            $data['division_id'] = $spreadInfo['division_id'];
            $data['agent_id'] = $spreadInfo['agent_id'];
            $data['staff_id'] = $spreadInfo['staff_id'];
            $handledProfile = event('DistributionServiceProxy', ['service' => 'DistributionUserServices', 'action' => 'saveProfile', 'args' => [$uid, [
                'division_id' => (int)($spreadInfo['division_id'] ?? 0),
                'agent_id' => (int)($spreadInfo['agent_id'] ?? 0),
                'staff_id' => (int)($spreadInfo['staff_id'] ?? 0),
            ]] ]);
            $handledRelation = event('DistributionServiceProxy', ['service' => 'DistributionRelationServices', 'action' => 'bind', 'args' => [$uid, $spreadUid, $data['spread_time']]]);
            if ((!is_array($handledProfile) || ($handledProfile[0] ?? null) === null
                    || !is_array($handledRelation) || ($handledRelation[0] ?? null) === null)
                && !$this->dao->update($uid, $data, 'uid')) {
                throw new ApiException('绑定推广关系失败');
            }
            return '绑定上级成功，上级uid为' . $spreadUid;
        } else {
            return '不绑定';
        }
    }

    /**
     * 添加访问记录
     * @param Request $request
     * @return mixed
     */
    public function setVisit(array $data)
    {
        $userInfo = $this->getUserInfo($data['uid'], 'uid,user_type');
        if (!$userInfo) {
            throw new ApiException('数据不存在');
        }
        $data['channel_type'] = $userInfo['user_type'];
        $data['add_time'] = time();
        //通过事件向渠道查询省份，避免 app 层直接引用 modules
        $province = event('UserVisitExtend', ['uid' => $userInfo['uid'], 'user_type' => $userInfo['user_type']]);
        $data['province'] = !empty($province) ? ($province[0] ?? '') : '';
        /** @var UserVisitServices $userVisit */
        $userVisit = app()->make(UserVisitServices::class);
        if ($userVisit->save($data)) {
            return true;
        } else {
            throw new ApiException('设置失败');
        }
    }

    /**
     * 获取活动状态
     * @return mixed
     */
    public function activity()
    {
        //通过事件聚合各交易模式的活动状态，避免 app 层直接引用 modules
        $data = ['is_bargin' => false, 'is_pink' => false, 'is_seckill' => false];
        $results = event('ActivityStatusQuery');
        if (is_array($results)) {
            foreach ($results as $result) {
                if (!is_array($result)) {
                    continue;
                }
                //合并各监听器返回的活动状态字段
                $data = array_merge($data, $result);
            }
        }
        return $data;
    }

    /**
     * 获取用户下级推广人
     * @param int $uid 当前用户
     * @param int $grade 等级  0  一级 1 二级
     * @param string $orderBy 排序
     * @param string $keyword
     * @return array|bool
     */
    public function getUserSpreadGrade(int $uid = 0, $grade = 0, $orderBy = '', $keyword = '')
    {
        $user = $this->getUserInfo($uid);
        if (!$user) {
            throw new AdminException('用户不存在');
        }
        $spread_one_ids = $this->getUserSpredadUids($uid, 1);
        $spread_two_ids = $this->getUserSpredadUids($uid, 2);
        $data = [
            'total' => count($spread_one_ids),
            'totalLevel' => count($spread_two_ids),
            'list' => []
        ];
        if (sys_config('brokerage_level', 2) == 1) {
            $data['count'] = $data['total'];
        } else {
            $data['count'] = $data['total'] + $data['totalLevel'];
        }
        /** @var UserStoreOrderServices $userStoreOrder */
        $userStoreOrder = app()->make(UserStoreOrderServices::class);
        $list = [];
        if ($grade == 0) {
            if ($spread_one_ids) $list = $userStoreOrder->getUserSpreadCountList($spread_one_ids, $orderBy, $keyword);
        } else {
            if ($spread_two_ids) $list = $userStoreOrder->getUserSpreadCountList($spread_two_ids, $orderBy, $keyword);
        }
        foreach ($list as &$item) {
            if (isset($item['spread_time']) && $item['spread_time']) {
                $item['time'] = date('Y/m/d', $item['spread_time']);
            }
        }
        $data['list'] = $list;
        $data['brokerage_level'] = (int)sys_config('brokerage_level', 2);
        return $data;
    }

    /**
     * 获取推广人uids
     * @param int $uid
     * @param bool $one
     * @return array
     */
    public function getUserSpredadUids(int $uid, int $type = 0, $time = '')
    {
        $firstTime = $type === 2 ? '' : $time;
        $children = event('DistributionServiceProxy', ['service' => 'DistributionRelationServices', 'action' => 'childUids', 'args' => [$uid, $firstTime]]);
        $pluginEnabled = is_array($children) && is_array($children[0] ?? null);
        $uids = $pluginEnabled ? $children[0] : $this->dao->getColumn(['spread_uid' => $uid, 'is_del' => 0], 'uid');
        if ($type === 1) {
            return $uids;
        }
        if ($uids) {
            if ($pluginEnabled) {
                $uidsTwo = [];
                foreach ($uids as $childUid) {
                    $second = event('DistributionServiceProxy', ['service' => 'DistributionRelationServices', 'action' => 'childUids', 'args' => [(int)$childUid, $time]]);
                    $uidsTwo = array_merge($uidsTwo, is_array($second[0] ?? null) ? $second[0] : []);
                }
                $uidsTwo = array_values(array_unique(array_map('intval', $uidsTwo)));
            } else {
                $uidsTwo = $this->dao->getColumn([['spread_uid', 'in', $uids], ['is_del', '=', 0]], 'uid');
            }
            if ($type === 2) {
                return $uidsTwo;
            }
            if ($uidsTwo) {
                $uids = array_merge($uids, $uidsTwo);
            }
        }
        return $uids;
    }

    /** 分销关系代理是否可用。 */
    private function agentRelationAvailable(): bool
    {
        $result = event('DistributionServiceProxy', [
            'service' => 'DistributionRelationServices',
            'action' => 'childUids',
            'args' => [0],
        ]);
        return is_array($result) && is_array($result[0] ?? null);
    }


    /**
     * 检测用户是否是推广员
     * @param int $uid
     * @param $user
     * @return bool
     */
    public function checkUserPromoter(int $uid, $user = [])
    {
        if (!$user) {
            $user = $this->getUserInfo($uid, 'spread_open,is_promoter');
        }
        if (!$user) {
            return false;
        }
        $profile = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'profile',
            'args' => [$uid, is_object($user) && method_exists($user, 'toArray') ? $user->toArray() : (array)$user],
        ]);
        if (is_array($profile) && is_array($profile[0] ?? null)) {
            $user = $profile[0];
        }
        //分销是否开启
        if (!sys_config('brokerage_func_status')) {
            return false;
        }
        if (isset($user['spread_open']) && !$user['spread_open']) {
            return false;
        }
        /** @var OrderCenterDao $storeOrder */
        $storeOrder = app()->make(OrderCenterDao::class);
        $sumPrice = $storeOrder->sum(['uid' => $uid, 'pay_status' => 1], 'pay_price');//累计消费
        $store_brokerage_statu = sys_config('store_brokerage_statu');
        $store_brokerage_price = sys_config('store_brokerage_price');
        if ($user['is_promoter'] || $store_brokerage_statu == 2 || ($store_brokerage_statu == 3 && $sumPrice > $store_brokerage_price)) {
            if (!$user['is_promoter']) {
                $this->setIsPromoter($uid, 1);
            }
            return true;
        }
        return false;
    }

    /** 修改会员的时间及是否会员状态
     * @param int $vip_day 会员天数
     * @param array $user_id 用户id
     * @param int $is_money_level 会员来源途径
     * @param bool $member_type 会员卡类型
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setMemberOverdueTime($vip_day, int $user_id, int $is_money_level, $member_type = false)
    {
        if ($vip_day == 0) throw new ApiException('天数不能为0');
        $user_info = $this->getUserInfo($user_id, 'is_money_level,overdue_time');
        if (!$user_info) throw new ApiException('用户不存在');
        if (!$member_type) $member_type = "month";
        if ($member_type == 'ever') {
            $overdue_time = 0;
            $is_ever_level = 1;
        } else {
            if ($user_info['is_money_level'] == 0) {
                $overdue_time = bcadd(bcmul((string)$vip_day, '86400', 0), (string)time(), 0);
            } else {
                $overdue_time = bcadd(bcmul((string)$vip_day, '86400', 0), (string)$user_info['overdue_time'], 0);
            }
            $is_ever_level = 0;
        }
        $setData['overdue_time'] = $overdue_time;
        $setData['is_ever_level'] = $is_ever_level;
        $setData['is_money_level'] = $is_money_level ?: 0;
        // if ($user_info['level'] == 0) $setData['level'] = 1;
        return $this->dao->update(['uid' => $user_id], $setData);
    }

    /**
     * 会员过期改变状态，变为普通会员
     * @param $uid
     * @param $userInfo
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function offMemberLevel($uid, $userInfo = [])
    {
        if (!$uid) return false;
        if (!$userInfo) {
            $userInfo = $this->dao->get($uid, ['is_ever_level', 'is_money_level', 'overdue_time']);
        }
        if (!$userInfo) return false;
        if ($userInfo['is_ever_level'] == 0 && $userInfo['is_money_level'] > 0 && $userInfo['overdue_time'] < time()) {
            $this->dao->update(['uid' => $uid], ['is_money_level' => 0/*, 'overdue_time' => 0*/]);
            return false;
        }
        return $userInfo;
    }

    /**
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserInfoList(array $where, $field = "*")
    {
        return $this->dao->getUserInfoList($where, $field);
    }

    /**
     * 增加推广用户佣金
     * @param int $uid
     * @param int $spread_uid
     * @param array $userInfo
     * @param array $spread_user
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addBrokeragePrice(int $uid, int $spread_uid, array $userInfo = [], array $spread_user = [])
    {
        if (!$uid || !$spread_uid) {
            return false;
        }
        //商城分销功能是否开启 0关闭1开启
        if (!sys_config('brokerage_func_status')) return true;
        if (!sys_config('brokerage_user_status')) return true;
        //获取设置推广佣金单价
        $brokerage_price = sys_config('uni_brokerage_price', 0);
        //获取推广佣金当日限额
        $day_brokerage_price_upper = sys_config('day_brokerage_price_upper', 0);
        if (!floatval($brokerage_price) || !floatval($day_brokerage_price_upper)) {
            return true;
        }
        if (!$userInfo) {
            $userInfo = $this->getUserInfo($uid);
        }
        if (!$userInfo) {
            return false;
        }

        //根据手机号码查询此用户注销过，不反推广佣金
        if ($userInfo['phone'] !== '' && $this->dao->getCount(['phone' => $userInfo['phone'], 'is_del' => 1])) {
            return false;
        }
        //根据渠道事件查询此用户是否注销过，不反推广佣金
        $canceled = event('UserCancelStatusQuery', ['uid' => $uid]);
        if (!empty($canceled) && ($canceled[0] ?? false)) {
            return false;
        }

        if (!$spread_user) {
            $spread_user = $this->dao->getOne(['uid' => $spread_uid, 'status' => 1]);
        }
        if (!$spread_user) {
            return false;
        }
        if (!$this->checkUserPromoter($spread_uid, $spread_user)) {
            return false;
        }
        /** @var UserBrokerageServices $userBrokerageServices */
        $userBrokerageServices = app()->make(UserBrokerageServices::class);
        // -1不限制
        if ($day_brokerage_price_upper != -1) {
            if ($day_brokerage_price_upper <= 0) {
                return true;
            } else {
                //获取上级用户今日获取推广用户佣金
                $spread_day_brokerage = $userBrokerageServices->getUserBrokerageSum($spread_uid, ['brokerage_user'], 'today');
                //超过上限
                if (($spread_day_brokerage + $brokerage_price) > $day_brokerage_price_upper) {
                    return true;
                }
            }
        }

        return $this->transaction(function () use ($uid, $spread_uid, $brokerage_price, $userInfo, $userBrokerageServices) {
            // 添加返佣记录（事务内自动增加余额并记录流水）
            $res1 = $userBrokerageServices->brokerageIncome('get_user_brokerage', $spread_uid, [
                'nickname' => $userInfo['nickname'],
                'number' => floatval($brokerage_price)
            ], $uid);
            //给上级发送获得佣金的模板消息
            /** @var OrderTakeServices $storeOrderTakeServices */
            $storeOrderTakeServices = app()->make(OrderTakeServices::class);
            $storeOrderTakeServices->sendBackOrderBrokerage([], $spread_uid, $brokerage_price, 'user');
            return $res1;
        });
    }

    /**
     * 获取上级uid
     * @param int $uid
     * @param array $userInfo
     * @param bool $is_spread
     * @return int|mixed
     */
    public function getSpreadUid(int $uid, $userInfo = [], $is_spread = true)
    {
        if (!$uid) {
            return 0;
        }
        //商城分销功能是否开启 0关闭1开启
        if (!sys_config('brokerage_func_status')) return -1;
        if (!$userInfo) {
            $userInfo = $this->getUserInfo($uid);
        }
        if (!$userInfo) {
            return 0;
        }
        $relation = event('DistributionServiceProxy', [
            'service' => 'DistributionRelationServices',
            'action' => 'relation',
            'args' => [$uid, is_object($userInfo) && method_exists($userInfo, 'toArray') ? $userInfo->toArray() : (array)$userInfo],
        ]);
        if (is_array($relation) && is_array($relation[0] ?? null)) {
            $userInfo['spread_uid'] = $relation[0]['spread_uid'];
            $userInfo['spread_time'] = $relation[0]['spread_time'];
        }
        //上级的上级不需要检测自购
        if ($is_spread) {
            //开启自购
            $is_self_brokerage = sys_config('is_self_brokerage', 0);
            if ($is_self_brokerage && $is_spread) {
                return $uid;
            }
        }

        //绑定类型
        $store_brokergae_binding_status = sys_config('store_brokerage_binding_status', 1);
        if ($store_brokergae_binding_status == 1 || $store_brokergae_binding_status == 3) {
            return $userInfo['spread_uid'];
        }
        //分销绑定类型为时间段且没过期
        $store_brokerage_binding_time = sys_config('store_brokerage_binding_time', 30);
        if ($store_brokergae_binding_status == 2 && ($userInfo['spread_time'] + $store_brokerage_binding_time * 24 * 3600) > time()) {
            return $userInfo['spread_uid'];
        }
        return -1;
    }

    /**
     * 获取事业部/代理/员工列表
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDivisionList(array $where = [], string $field = '*')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $field, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 添加编辑用户信息时候的信息
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserSaveInfo($uid)
    {
        /** @var UserLabelServices $userLabelServices */
        $userLabelServices = app()->make(UserLabelServices::class);
        /** @var UserLabelRelationServices $userLabelRelationServices */
        $userLabelRelationServices = app()->make(UserLabelRelationServices::class);
        /** @var UserLabelCateServices $userLabelCateServices */
        $userLabelCateServices = app()->make(UserLabelCateServices::class);
        /** @var UserGroupServices $userGroupServices */
        $userGroupServices = app()->make(UserGroupServices::class);
        /** @var SystemUserLevelServices $systemUserLevelServices */
        $systemUserLevelServices = app()->make(SystemUserLevelServices::class);
        $userInfo = $this->dao->get($uid);
        if ($userInfo) {
            $label_ids = $userLabelRelationServices->getUserLabels($uid);
            $userInfo['label_id'] = !empty($label_ids) ? $userLabelServices->getLabelList(['ids' => $label_ids], ['id', 'label_name']) : [];
            $userInfo['birthday'] = (int)$userInfo['birthday'] ? date('Y-m-d', (int)$userInfo['birthday']) : '';
            $userInfo['level'] = $userInfo['level'] != 0 ? $userInfo['level'] : '';
            $userInfo['group_id'] = $userInfo['group_id'] != 0 ? $userInfo['group_id'] : '';
            if ($userInfo['addres'] === '') {
                $defaultAddressInfo = app()->make(UserAddressServices::class)->getUserDefaultAddress($uid);
                if ($defaultAddressInfo) {
                    $userInfo['addres'] = $defaultAddressInfo['province'] . $defaultAddressInfo['city'] . $defaultAddressInfo['district'] . $defaultAddressInfo['detail'];
                } else {
                    $userInfo['addres'] = '';
                }
            }
        }
        $levelInfo = $systemUserLevelServices->getWhereLevelList([], 'id,name');
        $groupInfo = $userGroupServices->getGroupList();
        $labelInfo = $userLabelCateServices->getUserLabel($uid);
        return compact('userInfo', 'levelInfo', 'groupInfo', 'labelInfo');
    }


    /**
     * 推送用户信息
     * @param $data
     * @param $pushUrl
     * @return bool
     */
    public function userUpdate($data, $pushUrl)
    {
        return out_push($pushUrl, $data, '更新用户信息');
    }
}
