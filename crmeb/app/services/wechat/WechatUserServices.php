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

namespace app\services\wechat;

use app\services\BaseServices;
use app\dao\wechat\WechatUserDao;
use app\services\user\LoginServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\AuthException;
use crmeb\services\app\WechatService;
use think\facade\Log;

/**
 *
 * Class WechatUserServices
 * @package app\services\wechat
 * @method delete($id, ?string $key = null)  删除
 * @method update($id, array $data, ?string $key = null) 更新数据
 * @method getColumn(array $where, string $field, string $key = '') 获取某个字段数组
 * @method get($id, ?array $field = []) 用主键获取一条数据
 * @method getOne(array $where, ?string $field = '*', array $with = []) 获得一条数据
 * @method value(array $value, string $key) 获取一条数据
 * @method getWechatTrendData($time, $where, $timeType, $key)
 * @method getWechatOpenid(int $uid, string $userType = 'wechat') 获取微信公众号openid
 */
class WechatUserServices extends BaseServices
{

    /**
     * WechatUserServices constructor.
     * @param WechatUserDao $dao
     */
    public function __construct(WechatUserDao $dao)
    {
        $this->dao = $dao;
    }

    public function getColumnUser($user_ids, $column, $key, string $user_type = 'wechat')
    {
        return $this->dao->getColumn([['uid', 'IN', $user_ids], ['user_type', '=', $user_type]], $column, $key);
    }

    /**
     * 获取单个微信用户
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getWechatUserInfo(array $where, $field = '*')
    {
        return $this->dao->getOne($where, $field);
    }

    /**
     * 用uid获得 微信openid
     * @param $uid
     * @return mixed
     */
    public function uidToOpenid(int $uid, string $userType = 'wechat')
    {
        return $this->dao->value(['uid' => $uid, 'user_type' => $userType], 'openid');
    }


    /**
     * TODO 用openid获得uid
     * @param $openid
     * @param string $openidType
     * @return mixed
     */
    public function openidTouid($openid, $openidType = 'openid')
    {
        $uid = $this->dao->value([[$openidType, '=', $openid], ['user_type', '<>', 'h5']], 'uid');
        if (!$uid)
            throw new AdminException(400710);
        return $uid;
    }

    /**
     * 用户取消关注
     * @param $openid
     * @return bool
     */
    public function unSubscribe($openid)
    {
        if (!$this->dao->update($openid, ['subscribe' => 0, 'subscribe_time' => time()], 'openid'))
            throw new AdminException(400711);
        return true;
    }

    /**
     * 用户存在就更新 不存在就添加
     * @param $openid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveUser($openid)
    {
        if ($this->getWechatUserInfo(['openid' => $openid])) {
            $this->updateUser($openid);
            return false;
        } else {
            $this->setNewUser($openid);
            return true;
        }
    }

    /**
     * 更新用户信息
     * @param $openid
     * @return bool
     */
    public function updateUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        $userInfo = is_object($userInfo) ? $userInfo->toArray() : $userInfo;
        if (isset($userInfo['nickname']) && $userInfo['nickname']) {
            $userInfo['nickname'] = filter_emoji($userInfo['nickname']);
        } else {
            mt_srand();
            $userInfo['nickname'] = 'wx' . rand(100000, 999999);
            $userInfo['avatar'] = sys_config('h5_avatar');
        }
        if (isset($userInfo['tagid_list'])) {
            $userInfo['tagid_list'] = implode(',', $userInfo['tagid_list']);
        }
        if (!$this->dao->update($openid, $userInfo, 'openid'))
            throw new AdminException(100013);
        return true;
    }

    /**
     * .添加新用户
     * @param $openid
     * @return object
     */
    public function setNewUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        if (!isset($userInfo['openid']))
            throw new AdminException(410082);
        $userInfo = is_object($userInfo) ? $userInfo->toArray() : $userInfo;
        if (isset($userInfo['nickname']) && $userInfo['nickname']) {
            $userInfo['nickname'] = filter_emoji($userInfo['nickname']);
        } else {
            mt_srand();
            $userInfo['nickname'] = 'wx' . rand(100000, 999999);
            $userInfo['headimgurl'] = sys_config('h5_avatar');
        }
        if (isset($userInfo['tagid_list'])) {
            $userInfo['tagid_list'] = implode(',', $userInfo['tagid_list']);
        }
        $wechatInfo = [];
        $uid = 0;
        $userInfoData = null;
        if (isset($userInfo['unionid'])) {
            $wechatInfo = $this->getWechatUserInfo(['unionid' => $userInfo['unionid']]);
        }
        if (!$wechatInfo) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfoData = $userServices->setUserInfo($userInfo);
            if (!$userInfoData) {
                throw new AdminException(400703);
            }
            $uid = $userInfoData->uid;
        } else {
            $uid = $wechatInfo['uid'];
        }
        $userInfo['user_type'] = 'wechat';
        $userInfo['add_time'] = time();
        $userInfo['uid'] = $uid;
        if (!$this->dao->save($userInfo)) {
            throw new AdminException(400703);
        }
        //TODO 这个返回值待完善
        return $userInfoData;
    }

    /**
     * 授权后获取用户信息
     * @param $openid
     * @param $user_type
     */
    public function getAuthUserInfo($openid, $user_type)
    {
        $user = [];
        //兼容老用户
        $uids = $this->dao->getColumn(['unionid|openid' => $openid, 'is_del' => 0], 'uid,user_type', 'user_type');
        if ($uids) {
            $uid = $uids[$user_type]['uid'] ?? 0;
            if (!$uid) {
                $ids = array_column($uids, 'uid');
                $uid = $ids[0];
            }
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $user = $userServices->getUserInfo($uid);
        }
        return $user;
    }

    /**
     * 更新微信用户信息
     * @param $event
     * @return bool
     */
    public function wechatUpdata($data)
    {
        [$uid, $userData] = $data;
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userInfo = $userServices->getUserInfo($uid)) {
            return false;
        }
        /** @var LoginServices $loginService */
        $loginService = app()->make(LoginServices::class);
        $loginService->updateUserInfo($userData, $userInfo);
        //更新用户信息
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);

        $wechatUserInfo = [];
        if (isset($userData['nickname']) && $userData['nickname']) $wechatUserInfo['nickname'] = filter_emoji($userData['nickname'] ?? '');//姓名
        if (isset($userData['headimgurl']) && $userData['headimgurl']) $wechatUserInfo['headimgurl'] = $userData['avatarUrl'] ?? '';//头像
        if (isset($userData['sex']) && $userData['sex']) $wechatUserInfo['sex'] = $userData['gender'] ?? '';//性别
        if (isset($userData['language']) && $userData['language']) $wechatUserInfo['language'] = $userData['language'] ?? '';//语言
        if (isset($userData['city']) && $userData['city']) $wechatUserInfo['city'] = $userData['city'] ?? '';//城市
        if (isset($userData['province']) && $userData['province']) $wechatUserInfo['province'] = $userData['province'] ?? '';//省份
        if (isset($userData['country']) && $userData['country']) $wechatUserInfo['country'] = $userData['country'] ?? '';//国家
        if (isset($wechatUserInfo['nickname']) || isset($wechatUserInfo['headimgurl'])) $wechatUserInfo['is_complete'] = 1;
        if ($wechatUserInfo) {
            if (isset($userData['openid']) && $userData['openid'] && false === $wechatUser->update(['uid' => $userInfo['uid'], 'openid' => $userData['openid']], $wechatUserInfo)) {
                throw new ApiException(100013);
            }
        }
        return true;
    }

    /**
     * 微信授权成功后
     * @param $event
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function wechatOauthAfter(array $data)
    {
        [$openid, $wechatInfo, $spreadId, $login_type, $userType] = $data;
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $spreadInfo = $userServices->getUserInfo((int)$spreadId);
        if (!$spreadInfo) {
            $spreadId = 0;
            $wechatInfo['staff_id'] = 0;
            $wechatInfo['agent_id'] = 0;
            $wechatInfo['division_id'] = 0;
        } else {
            $wechatInfo['staff_id'] = $spreadInfo['staff_id'];
            $wechatInfo['agent_id'] = $spreadInfo['agent_id'];
            $wechatInfo['division_id'] = $spreadInfo['division_id'];
        }
        if (isset($wechatInfo['subscribe_scene'])) {
            unset($wechatInfo['subscribe_scene']);
        }
        if (isset($wechatInfo['qr_scene'])) {
            unset($wechatInfo['qr_scene']);
        }
        if (isset($wechatInfo['qr_scene_str'])) {
            unset($wechatInfo['qr_scene_str']);
        }
        if ($login_type) {
            $wechatInfo['login_type'] = $login_type;
        }
        if (!isset($wechatInfo['nickname'])) {
            if (isset($wechatInfo['phone']) && $wechatInfo['phone']) {
                $wechatInfo['nickname'] = substr_replace($wechatInfo['phone'], '****', 3, 4);
            } else {
                $wechatInfo['nickname'] = 'wx' . rand(100000, 999999);
            }
        } else {
            $wechatInfo['is_complete'] = 1;
            $wechatInfo['nickname'] = filter_emoji($wechatInfo['nickname']);
        }

        $userInfo = [];
        $uid = 0;
        if (isset($wechatInfo['phone']) && $wechatInfo['phone']) {
            $userInfo = $userServices->getOne(['phone' => $wechatInfo['phone'], 'is_del' => 0]);
        }
        if (!$userInfo) {
            if (isset($wechatInfo['unionid']) && $wechatInfo['unionid']) {
                $uid = $this->dao->value(['unionid' => $wechatInfo['unionid'], 'is_del' => 0], 'uid');
                if ($uid) {
                    $userInfo = $userServices->getOne(['uid' => $uid, 'is_del' => 0]);
                }
            } else {
                $userInfo = $this->getAuthUserInfo($openid, $userType);
            }
        }
        if ($userInfo) {
            $uid = (int)$userInfo['uid'];
            $userInfo['new_user'] = 0;
        }
        $wechatInfo['user_type'] = $userType;
        //user表存在和wechat_user表同时存在
        if ($userInfo) {
            //更新用户表和wechat_user表
            //判断该类性用户在wechatUser中是否存在
            $wechatUser = $this->dao->getOne(['uid' => $uid, 'user_type' => $userType, 'is_del' => 0]);
            /** @var LoginServices $loginService */
            $loginService = app()->make(LoginServices::class);
            $this->transaction(function () use ($loginService, $wechatInfo, $userInfo, $uid, $userType, $spreadId, $wechatUser) {
                $wechatInfo['code'] = $spreadId;
                $loginService->updateUserInfo($wechatInfo, $userInfo);
                if ($wechatUser) {
                    if (!$this->dao->update($wechatUser['id'], $wechatInfo, 'id')) {
                        throw new ApiException(100007);
                    }
                } else {
                    $wechatInfo['uid'] = $uid;
                    if (!$this->dao->save($wechatInfo)) {
                        throw new ApiException(100007);
                    }
                }
            });
        } else {
            //user表没有用户,wechat_user表没有用户创建新用户
            //不存在则创建用户
            $userInfo = $this->transaction(function () use ($userServices, $wechatInfo, $spreadId, $userType) {
                Log::error($wechatInfo);
                $userInfo = $userServices->setUserInfo($wechatInfo, (int)$spreadId, $userType);
                if (!$userInfo) {
                    throw new AuthException(410083);
                }
                $wechatInfo['uid'] = $userInfo->uid;
                $wechatInfo['add_time'] = $userInfo->add_time;
                if (!$this->dao->save($wechatInfo)) {
                    throw new AuthException(410083);
                }
                $userInfo['new_user'] = (int)sys_config('get_avatar', 0);
                return $userInfo;
            });
        }
        return $userInfo;
    }

    /**
     * 更新用户信息（同步）
     * @param array $openids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function syncWechatUser(array $openids)
    {
        if (!$openids) {
            return [];
        }
        $wechatUser = $this->dao->getList([['openid', 'in', $openids]]);
        $noBeOpenids = $openids;
        if ($wechatUser) {
            $beOpenids = array_column($wechatUser, 'openid');
            $noBeOpenids = array_diff($openids, $beOpenids);
//            $beWechatUserInfo = WechatService::getUserInfo($beOpenids);
            if ($beOpenids) {
                $data = [];
                foreach ($beOpenids as $openid) {
                    try {
                        $info = WechatService::getUserInfo($openid);
                        $info = is_object($info) ? $info->toArray() : $info;
                    } catch (\Throwable $e) {
                        $info = [];
                    }
                    if (!$info) continue;
                    $data['subscribe'] = $info['subscribe'] ?? 1;
                    if ($info['subscribe'] == 1) {
                        $data['unionid'] = $info['unionid'] ?? '';
                        $data['nickname'] = $info['nickname'] ?? '';
                        $data['sex'] = $info['sex'] ?? 0;
                        $data['language'] = $info['language'] ?? '';
                        $data['city'] = $info['city'] ?? '';
                        $data['province'] = $info['province'] ?? '';
                        $data['country'] = $info['country'] ?? '';
                        $data['headimgurl'] = $info['headimgurl'] ?? '';
                        $data['subscribe_time'] = $info['subscribe_time'] ?? '';
                        $data['groupid'] = $info['groupid'] ?? 0;
                        $data['remark'] = $info['remark'] ?? '';
                        $data['tagid_list'] = isset($info['tagid_list']) && $info['tagid_list'] ? implode(',', $info['tagid_list']) : '';
                    }
                    $this->dao->update(['openid' => $info['openid']], $data);
                }
            }
        }
        return $noBeOpenids;
    }

    /**
     * 用户关注
     * @param $openid
     * @return bool
     */
    public function subscribe($openid): bool
    {
        if (!$this->dao->update($openid, ['subscribe' => 1, 'subscribe_time' => time()], 'openid'))
            throw new AdminException(410084);
        return true;
    }
}
