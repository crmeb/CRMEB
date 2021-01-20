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

//declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserDao;
use app\services\coupon\StoreCouponIssueServices;
use app\services\coupon\StoreCouponUserServices;
use app\services\order\StoreOrderServices;
use app\services\product\product\StoreProductRelationServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\FormBuilder;
use crmeb\services\WechatService;
use crmeb\utils\Queue;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Route as Url;

/**
 *
 * Class UserServices
 * @package app\services\user
 * @method array getUserInfoArray(array $where, string $field, string $key) 根据条件查询对应的用户信息以数组形式返回
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method get($id, ?array $field = [], ?array $with = []) 获取一条数据
 * @method count(array $where) 获取指定条件下的数量
 * @method value(array $where, string $field) 获取指定的键值
 * @method bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2) 高精度加法
 * @method bcDec($key, string $incField, string $inc, string $keyField = null, int $acc = 2) 高精度减法
 * @method getTrendData($time, $type, $timeType)
 */
class UserServices extends BaseServices
{

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
     * @param $id
     * @param $field
     */
    public function getUserInfo(int $uid, $field = '*')
    {
        if (is_string($field)) $field = explode(',', $field);
        return $this->dao->get($uid, $field);
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
     */
    public function setUserInfo($user, string $userType = 'wechat')
    {
        $res = $this->dao->save([
            'account' => $user['account'] ?? 'wx' . rand(1, 9999) . time(),
            'pwd' => $user['pwd'] ?? md5('123456'),
            'nickname' => $user['nickname'] ?? '',
            'avatar' => $user['headimgurl'] ?? '',
            'phone' => $user['phone'] ?? '',
            'add_time' => time(),
            'add_ip' => app()->request->ip(),
            'last_time' => time(),
            'last_ip' => app()->request->ip(),
            'user_type' => $userType
        ]);
        if (!$res)
            throw new AdminException('保存用户信息失败');
        return $res;
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
     * 查找多个uid信息
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
     * 写入用户信息
     * @param array $data
     * @return bool
     */
    public function create(array $data)
    {
        if (!$this->dao->save($data))
            throw new AdminException('写入失败');
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
     * 设置用户登录类型
     * @param int $uid
     * @param string $type
     * @return bool
     * @throws Exception
     */
    public function setLoginType(int $uid, string $type = 'h5')
    {
        if (!$this->dao->update($uid, ['login_type' => $type]))
            throw new Exception('设置登录类型失败');
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
     * 增加用户余额
     * @param int $uid
     * @param float $old_now_money
     * @param float $now_money
     * @return bool
     * @throws Exception
     */
    public function addNowMoney(int $uid, $old_now_money, $now_money)
    {
        if (!$this->dao->update($uid, ['now_money' => bcadd($old_now_money, $now_money, 2)]))
            throw new Exception('增加用户余额失败');
        return true;
    }

    /**
     * 减少用户余额
     * @param int $uid
     * @param float $old_now_money
     * @param float $now_money
     * @return bool
     * @throws Exception
     */
    public function cutNowMoney(int $uid, $old_now_money, $now_money)
    {
        if ($old_now_money > $now_money) {
            if (!$this->dao->update($uid, ['now_money' => bcsub($old_now_money, $now_money, 2)]))
                throw new Exception('减少用户余额失败');
        }
        return true;
    }

    /**
     * 增加用户积分
     * @param int $uid
     * @param float $old_integral
     * @param float $integral
     * @return bool
     * @throws Exception
     */
    public function addIntegral(int $uid, $old_integral, $integral)
    {
        if (!$this->dao->update($uid, ['integral' => bcadd($old_integral, $integral, 2)]))
            throw new Exception('增加用户积分失败');
        return true;
    }

    /**
     * 减少用户积分
     * @param int $uid
     * @param float $old_integral
     * @param float $integral
     * @return bool
     * @throws Exception
     */
    public function cutIntegral(int $uid, $old_integral, $integral)
    {
        if (!$this->dao->update($uid, ['integral' => bcsub($old_integral, $integral, 2)]))
            throw new Exception('减少用户积分失败');
        return true;
    }

    /**
     * 获取用户标签
     * @param $uid
     * @return \think\Collection
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
     * 显示资源列表头部
     * @return array[]
     */
    public function typeHead()
    {
        //全部会员
        $all = $this->getCount([]);
        /** @var UserWechatuserServices $userWechatUser */
        $userWechatUser = app()->make(UserWechatuserServices::class);
        //小程序会员
        $routine = $userWechatUser->getCount([['w.user_type', '=', 'routine']]);
        //公众号会员
        $wechat = $userWechatUser->getCount([['w.user_type', '=', 'wechat']]);
        //H5会员
        $h5 = $userWechatUser->getCount(['w.openid' => '', 'u.user_type' => 'h5']);
        //pc会员
        $pc = $userWechatUser->getCount(['w.openid' => '', 'u.user_type' => 'pc']);
        return [
            ['user_type' => '', 'name' => '全部会员', 'count' => $all],
            ['user_type' => 'routine', 'name' => '小程序会员', 'count' => $routine],
            ['user_type' => 'wechat', 'name' => '公众号会员', 'count' => $wechat],
            ['user_type' => 'h5', 'name' => 'H5会员', 'count' => $h5],
            ['user_type' => 'pc', 'name' => 'PC会员', 'count' => $pc],
        ];
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
        $fields = 'u.*,w.country,w.province,w.city,w.sex,w.unionid,w.openid,w.user_type as w_user_type,w.groupid,w.tagid_list,w.subscribe,w.subscribe_time';
        [$list, $count] = $userWechatUser->getWhereUserList($where, $fields);
        if ($list) {
            $uids = array_column($list, 'uid');
            $userlabel = $this->getUserLablel($uids);
            $userGroup = app()->make(UserGroupServices::class)->getUsersGroupName(array_unique(array_column($list, 'group_id')));
            foreach ($list as &$item) {
                if (empty($item['addres'])) {
                    if (!empty($item['country']) || !empty($item['province']) || !empty($item['city'])) {
                        $item['addres'] = $item['country'] . $item['province'] . $item['city'];
                    }
                }
                $item['status'] = ($item['status'] == 1) ? '正常' : '禁止';
                $item['birthday'] = $item['birthday'] ? date('Y-m-d', (int)$item['birthday']) : '';
                //用户类型
                if ($item['user_type'] == 'routine') {
                    $item['user_type'] = '小程序';
                } else if ($item['user_type'] == 'wechat') {
                    $item['user_type'] = '公众号';
                } else if ($item['user_type'] == 'h5') {
                    $item['user_type'] = 'H5';
                } else if ($item['user_type'] == 'pc') {
                    $item['user_type'] = 'PC';
                } else $item['user_type'] = '其他';
                if ($item['sex'] == 1) {
                    $item['sex'] = '男';
                } else if ($item['sex'] == 2) {
                    $item['sex'] = '女';
                } else $item['sex'] = '保密';
                //分组名称
                $item['group_id'] = $userGroup[$item['group_id']] ?? '无';
                $item['vip_name'] = false;
                $item['labels'] = $userlabel[$item['uid']] ?? '';
                $item['isMember'] = $item['is_money_level'] > 0 ? 1 : 0;
            }
        }
        return compact('count', 'list');
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
        if ($user->getData('phone')) {
            $f[] = Form::input('phone', '手机号码', $user->getData('phone'))->disabled(true);
        } else {
            $f[] = Form::input('phone', '手机号码', $user->getData('phone'));
        }
        $f[] = Form::date('birthday', '生日', $user->getData('birthday') ? date('Y-m-d', $user->getData('birthday')) : '');
        $f[] = Form::input('card_id', '身份证号', $user->getData('card_id'));
        $f[] = Form::input('addres', '用户地址', $user->getData('addres'));
        $f[] = Form::textarea('mark', '用户备注', $user->getData('mark'));
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
        $f[] = Form::radio('status', '状态', $user->getData('status'))->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '锁定']]);
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
        $f[] = Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '锁定']]);
        return create_form('添加用户', $f, $this->url('/user/user'), 'POST');
    }

    /**
     * 修改提交处理
     * @param $id
     * @return mixed
     */
    public function updateInfo(int $id, array $data)
    {
        $user = $this->getUserInfo($id);
        if (!$user) {
            throw new AdminException('数据不存在!');
        }
        $res1 = false;
        $res2 = false;
        $edit = array();
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        if ($data['money_status'] && $data['money']) {//余额增加或者减少
            $bill_data = ['link_id' => $data['adminId'] ?? 0, 'number' => $data['money'], 'balance' => $user['now_money']];
            if ($data['money_status'] == 1) {//增加
                $edit['now_money'] = bcadd($user['now_money'], $data['money'], 2);
                $bill_data['title'] = '系统增加余额';
                $bill_data['mark'] = '系统增加了' . floatval($data['money']) . '余额';
                $res1 = $userBill->incomeNowMoney($user['uid'], 'system_add', $bill_data);
            } else if ($data['money_status'] == 2) {//减少
                $edit['now_money'] = bcsub($user['now_money'], $data['money'], 2);
                $bill_data['title'] = '系统减少余额';
                $bill_data['mark'] = '系统扣除了' . floatval($data['money']) . '余额';
                $res1 = $userBill->expendNowMoney($user['uid'], 'system_sub', $bill_data);
            }
        } else {
            $res1 = true;
        }
        if ($data['integration_status'] && $data['integration']) {//积分增加或者减少
            $integral_data = ['link_id' => $data['adminId'] ?? 0, 'number' => $data['integration'], 'balance' => $user['integral']];
            if ($data['integration_status'] == 1) {//增加
                $edit['integral'] = bcadd($user['integral'], $data['integration'], 2);
                $integral_data['title'] = '系统增加积分';
                $integral_data['mark'] = '系统增加了' . floatval($data['integration']) . '积分';
                $res2 = $userBill->incomeIntegral($user['uid'], 'system_add', $integral_data);
            } else if ($data['integration_status'] == 2) {//减少
                $edit['integral'] = bcsub($user['integral'], $data['integration'], 2);
                $integral_data['title'] = '系统减少积分';
                $integral_data['mark'] = '系统扣除了' . floatval($data['integration']) . '积分';
                $res2 = $userBill->expendIntegral($user['uid'], 'system_sub', $integral_data);
            }
        } else {
            $res2 = true;
        }
        //修改基本信息
        if (!isset($data['is_other']) || !$data['is_other']) {
            app()->make(UserLabelRelationServices::class)->setUserLable([$id], $data['label_id']);
            $edit['status'] = $data['status'];
            $edit['real_name'] = $data['real_name'];
            $edit['card_id'] = $data['card_id'];
            $edit['birthday'] = strtotime($data['birthday']);
            $edit['mark'] = $data['mark'];
            $edit['level'] = $data['level'];
            $edit['phone'] = $data['phone'];
            $edit['addres'] = $data['addres'];
            $edit['group_id'] = $data['group_id'];
        }
        if ($edit) $res3 = $this->dao->update($id, $edit);

        else $res3 = true;
        if ($res1 && $res2 && $res3)
            return true;
        else throw new AdminException('修改失败');
    }

    /**
     * 编辑其他
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function editOther($id)
    {
        $user = $this->getUserInfo($id);
        if (!$user) {
            throw new AdminException('数据不存在!');
        }
        $f = array();
        $f[] = Form::radio('money_status', '修改余额', 1)->options([['value' => 1, 'label' => '增加'], ['value' => 2, 'label' => '减少']]);
        $f[] = Form::number('money', '余额', 0)->min(0);
        $f[] = Form::radio('integration_status', '修改积分', 1)->options([['value' => 1, 'label' => '增加'], ['value' => 2, 'label' => '减少']]);
        $f[] = Form::number('integration', '积分', 0)->min(0)->precision(0);
        return create_form('修改其他', $f, Url::buildUrl('/user/update_other/' . $id), 'PUT');
    }

    /**
     * 设置会员分组
     * @param $id
     * @return mixed
     */
    public function setGroup($uids)
    {
        $userGroup = app()->make(UserGroupServices::class)->getGroupList();
        if (count($uids) == 1) {
            $user = $this->getUserInfo($uids[0], ['group_id']);
            $setOptionUserGroup = function () use ($userGroup) {
                $menus = [];
                foreach ($userGroup as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
                }
                return $menus;
            };
            $field[] = Form::select('group_id', '用户分组', $user->getData('group_id'))->setOptions(FormBuilder::setOptions($setOptionUserGroup))->filterable(true);
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
        $userLabel = app()->make(UserLabelServices::class)->getLabelList();
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
    public function saveSetLabel($uids, $lable_id)
    {
        foreach ($lable_id as $id) {
            if (!app()->make(UserLabelServices::class)->getLable((int)$id)) {
                throw new AdminException('有标签不存在或被删除');
            }
        }
        /** @var UserLabelRelationServices $services */
        $services = app()->make(UserLabelRelationServices::class);
        if (!$services->setUserLable($uids, $lable_id)) {
            throw new AdminException('设置标签失败');
        }
        return true;
    }

    /**
     * 用户详细信息
     * @param $uid
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
            ['name' => '账户余额', 'value' => $userInfo['now_money']],
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
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $where = ['uid' => $uid, 'paid' => 1, 'refund_status' => 0, 'is_del' => 0, 'is_system_del' => 0];
        return [
            [
                'title' => '余额',
                'value' => $userInfo['now_money'] ?? 0,
                'key' => '元',
            ],
            [
                'title' => '总计订单',
                'value' => $orderServices->count($where),
                'key' => '笔',
            ],
            [
                'title' => '总消费金额',
                'value' => $orderServices->together($where, 'total_price'),
                'key' => '元',
            ],
            [
                'title' => '积分',
                'value' => $userInfo['integral'] ?? 0,
                'key' => '',
            ],
            [
                'title' => '本月订单',
                'value' => $orderServices->count($where + ['time' => 'month']),
                'key' => '笔',
            ],
            [
                'title' => '本月消费金额',
                'value' => $orderServices->together($where + ['time' => 'month'], 'total_price'),
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

    public function read(int $uid)
    {
        $userInfo = $this->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException('数据不存在');
        }
        $info = [
            'uid' => $uid,
            'userinfo' => $this->getUserDetailed($uid, $userInfo),
            'headerList' => $this->getHeaderList($uid, $userInfo),
            'count' => $this->getUserBillCountData($uid),
            'ps_info' => $userInfo
        ];
        return $info;
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
                /** @var UserFriendsServices $services */
                $services = app()->make(UserFriendsServices::class);
                return $services->getFriendList(['uid' => $id], ['level', 'nickname']);
                break;
            case 'order':
                /** @var StoreOrderServices $services */
                $services = app()->make(StoreOrderServices::class);
                return $services->getUserOrderList($id);
                break;
            case 'integral':
                /** @var UserBillServices $services */
                $services = app()->make(UserBillServices::class);
                return $services->getIntegralList($id, [], 'title,number,balance,mark,add_time');
                break;
            case 'coupon':
                /** @var StoreCouponUserServices $services */
                $services = app()->make(StoreCouponUserServices::class);
                return $services->getUserCouponList($id);
                break;
            case 'balance_change':
                /** @var UserBillServices $services */
                $services = app()->make(UserBillServices::class);
                return $services->getBrokerageList($id, [], 'title,type,number,balance,mark,pm,status,add_time');
                break;
            default:
                throw new AdminException('type参数错误');
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
        $yesterday = date('Y-m-d');

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
        return $info;
    }

    /**
     * 个人中心
     * @param array $user
     */
    public function personalHome(array $user, $tokenData)
    {
        $userInfo = $user;
        $uid = (int)$user['uid'];
        /** @var StoreCouponUserServices $storeCoupon */
        $storeCoupon = app()->make(StoreCouponUserServices::class);
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        /** @var StoreOrderServices $storeOrder */
        $storeOrder = app()->make(StoreOrderServices::class);
        /** @var SystemAttachmentServices $systemAttachment */
        $systemAttachment = app()->make(SystemAttachmentServices::class);
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);
        /** @var UserInvoiceServices $userInvoice */
        $userInvoice = app()->make(UserInvoiceServices::class);
        /** @var StoreCouponIssueServices $couponService */
        $couponService = app()->make(StoreCouponIssueServices::class);
        /** @var StoreProductRelationServices $collect */
        $collect = app()->make(StoreProductRelationServices::class);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        //会员领取优惠券
        // $couponService->sendMemberCoupon($uid);
        $wechatUserInfo = $wechatUser->getOne(['uid' => $uid, 'user_type' => $tokenData['type']]);
        $user['is_complete'] = $wechatUserInfo['is_complete'] ?? 0;
        $user['couponCount'] = $storeCoupon->getUserValidCouponCount((int)$uid);
        $user['like'] = app()->make(StoreProductRelationServices::class)->getUserCollectCount($user['uid']);
        $user['orderStatusNum'] = $storeOrder->getOrderData($uid);
        $user['notice'] = 0;
        $user['recharge'] = $userBill->getRechargeSum($uid);//累计充值
        $user['orderStatusSum'] = $storeOrder->sum(['uid' => $uid, 'paid' => 1, 'is_del' => 0], 'pay_price');//累计消费
        $user['extractPrice'] = $user['brokerage_price'];//可提现
        $user['statu'] = (int)sys_config('store_brokerage_statu');
        if (!$user['is_promoter'] && $user['statu'] == 2) {
            $price = $storeOrder->sum(['paid' => 1, 'refund_status' => 0, 'uid' => $user['uid']], 'pay_price');
            $status = is_brokerage_statu($price);
            if ($status) {
                $this->dao->update($uid, ['is_promoter' => 1], 'uid');
                $user['is_promoter'] = 1;
            } else {
                $storeBrokeragePrice = sys_config('store_brokerage_price', 0);
                $user['promoter_price'] = bcsub((string)$storeBrokeragePrice, (string)$price, 2);
            }
        }
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
        $user['balance_func_status'] = (int)sys_config('balance_func_status', 0);
        $invoice_func = $userInvoice->invoiceFuncStatus();
        $user['invioce_func'] = $invoice_func['invoice_func'];
        $user['special_invoice'] = $invoice_func['special_invoice'];
        $user['collectCount'] = $collect->count(['uid' => $uid]);
        $user['spread_status'] = sys_config('brokerage_func_status') && $this->checkUserPromoter($user['uid']);
        $user['pay_vip_status'] = $user['is_ever_level'] || ($user['is_money_level'] && $user['overdue_time'] > time());
        return $user;
    }

    /**
     * 用户资金统计
     * @param int $uid
     */
    public function balance(int $uid)
    {
        $userInfo = $this->getUserInfo($uid);
        if (!$userInfo) {
            throw new ValidateException('数据不存在');
        }
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        /** @var StoreOrderServices $storeOrder */
        $storeOrder = app()->make(StoreOrderServices::class);
        $user['now_money'] = $userInfo['now_money'];//当前总资金
        $user['recharge'] = $userBill->getRechargeSum($uid);//累计充值
        $user['orderStatusSum'] = $storeOrder->sum(['uid' => $uid, 'paid' => 1, 'is_del' => 0], 'pay_price');//累计消费
        return $user;
    }

    /**
     * 用户修改信息
     * @param Request $request
     * @return mixed
     */
    public function eidtNickname(int $uid, array $data)
    {
        if (!$this->getUserInfo($uid)) {
            throw new ValidateException('用户不存在');
        }
        if (!$this->dao->update($uid, $data, 'uid')) {
            throw new ValidateException('修改失败');
        }
        return true;
    }


    /**
     * 添加访问记录
     * @param Request $request
     * @return mixed
     */
    public function setVisit(array $data)
    {
        $userInfo = $this->getUserInfo($data['uid']);
        if (!$userInfo) {
            throw new ValidateException('数据不存在');
        }
        $data['channel_type'] = $userInfo['user_type'];
        $data['add_time'] = time();
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $wechatUser = $wechatUserServices->get(['uid' => $userInfo['uid'], 'user_type' => $userInfo['user_type']]);
        if (!$wechatUser) {
            $wechatUser = $wechatUserServices->get(['uid' => $userInfo['uid']]);
        }
        if ($wechatUser) {
            $data['province'] = $wechatUser['province'];
        }
        /** @var UserVisitServices $userVisit */
        $userVisit = app()->make(UserVisitServices::class);
        if ($userVisit->save($data)) {
            return true;
        } else {
            throw new ValidateException('添加访问记录失败');
        }
    }


    /**
     * 同步微信粉丝用户(后台接口)
     * @return bool
     */
    public function syncWechatUsers()
    {
        $key = md5('sync_wechat_users');
        //一天点击一次
        if (CacheService::get($key)) {
            return true;
        }
        $next_openid = null;
        do {
            $result = WechatService::getUsersList($next_openid);
            $userOpenids = $result['data'];
            //拆分大数组
            $opemidArr = array_chunk($userOpenids, 100);
            foreach ($opemidArr as $openids) {
                //加入同步|更新用户队列
                Queue::instance()->job(\crmeb\jobs\UserJob::class)->data($openids)->push();
            }
            $next_openid = $result['next_openid'];
        } while ($next_openid != null);
        CacheService::set($key, 1, 3600 * 24);
        return true;
    }

    /**
     * 导入微信粉丝用户
     * @param array $openids
     * @return bool
     */
    public function importUser(array $noBeOpenids)
    {
        if (!$noBeOpenids) {
            return true;
        }
        $dataAll = $data = [];
        $time = time();
        foreach ($noBeOpenids as $openid) {
            try {
                $info = WechatService::getUserInfo($openid);
            } catch (\Throwable $e) {
                $info = [];
            }
            if (!$info) continue;
            if ($info['subscribe'] == 1) {
                $data['nickname'] = $info['nickname'] ?? '';
                $data['headimgurl'] = $info['headimgurl'] ?? '';
                $userInfoData = $this->setUserInfo($data);
                if (!$userInfoData) {
                    throw new AdminException('用户信息储存失败!');
                }
                $data['uid'] = $userInfoData['uid'];
                $data['subscribe'] = $info['subscribe'];
                $data['unionid'] = $info['unionid'] ?? '';
                $data['openid'] = $info['openid'] ?? '';
                $data['sex'] = $info['sex'] ?? 0;
                $data['language'] = $info['language'] ?? '';
                $data['city'] = $info['city'] ?? '';
                $data['province'] = $info['province'] ?? '';
                $data['country'] = $info['country'] ?? '';
                $data['subscribe_time'] = $info['subscribe_time'] ?? '';
                $data['groupid'] = $info['groupid'] ?? 0;
                $data['remark'] = $info['remark'] ?? '';
                $data['tagid_list'] = isset($info['tagid_list']) && $info['tagid_list'] ? implode(',', $info['tagid_list']) : '';
                $data['add_time'] = $time;
                $data['is_complete'] = 1;
                $dataAll[] = $data;
            }
        }
        if ($dataAll) {
            /** @var WechatUserServices $wechatUser */
            $wechatUser = app()->make(WechatUserServices::class);
            if (!$wechatUser->saveAll($dataAll)) {
                throw new ValidateException('保存用户信息失败');
            }
        }
        return true;
    }
}
