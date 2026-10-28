<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\services\user;

use app\dao\user\UserWechatIdentityDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\AuthException;

/**
 * 微信渠道通用认证服务。
 *
 * 公众号与小程序共用用户身份映射，但认证入口之间互不依赖；渠道只负责获取
 * 自己的 openid/session，用户创建和绑定由核心服务统一完成。
 */
class UserWechatAuthServices extends BaseService
{
    /**
     * @var UserWechatIdentityDao
     */
    protected $dao;

    public function __construct(UserWechatIdentityDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 根据用户 ID 获取指定渠道的 openid。
     */
    public function uidToOpenid(int $uid, string $userType = 'wechat'): string
    {
        return (string)($this->dao->value([
            'uid' => $uid,
            'user_type' => $userType,
            'is_del' => 0,
        ], 'openid') ?: '');
    }

    /**
     * 根据 openid 获取用户 ID。
     */
    public function openidToUid(string $openid, string $openidType = 'openid', string $userType = 'routine'): int
    {
        $uid = $this->dao->value([
            $openidType => $openid,
            'user_type' => $userType,
            'is_del' => 0,
        ], 'uid');
        if (!$uid) {
            throw new ApiException('对应的uid不存在');
        }
        return (int)$uid;
    }

    /**
     * 授权前查询当前渠道是否已经绑定商城用户。
     */
    public function getAuthUserInfo(string $openid, string $userType): array
    {
        $uids = $this->dao->getColumn([
            'unionid|openid' => $openid,
            'is_del' => 0,
        ], 'uid,user_type', 'user_type');
        if (!$uids) {
            return [];
        }

        $uid = (int)($uids[$userType]['uid'] ?? 0);
        if (!$uid) {
            $uid = (int)(array_column($uids, 'uid')[0] ?? 0);
        }
        if (!$uid) {
            return [];
        }

        $user = app()->make(UserServices::class)->getUserInfo($uid);
        return $user ? (is_object($user) ? $user->toArray() : $user) : [];
    }

    /**
     * 微信授权成功后创建或更新商城用户及渠道身份映射。
     *
     * @param array $data [$openid, $wechatInfo, $spreadId, $agentId, $loginType, $userType]
     * @return array
     */
    public function wechatOauthAfter(array $data): array
    {
        if (count($data) < 6) {
            throw new ApiException('用户信息获取失败，请刷新页面重试');
        }

        [$openid, $wechatInfo, $spreadId, $agentId, $loginType, $userType] = $data;
        if (!is_array($wechatInfo)) {
            $wechatInfo = [];
        }
        $userServices = app()->make(UserServices::class);
        if (!$userServices->getUserInfo((int)$spreadId)) {
            $spreadId = 0;
        }

        unset($wechatInfo['subscribe_scene'], $wechatInfo['qr_scene'], $wechatInfo['qr_scene_str']);
        if ($loginType) {
            $wechatInfo['login_type'] = $loginType;
        }
        if (!isset($wechatInfo['nickname'])) {
            $wechatInfo['nickname'] = !empty($wechatInfo['phone'])
                ? substr_replace($wechatInfo['phone'], '****', 3, 4)
                : 'wx' . rand(100000, 999999);
        } else {
            $wechatInfo['is_complete'] = 1;
            $wechatInfo['nickname'] = filter_emoji($wechatInfo['nickname']);
        }

        $userInfo = [];
        $uid = 0;
        if (!empty($wechatInfo['phone'])) {
            $userInfo = $userServices->getOne(['phone' => $wechatInfo['phone'], 'is_del' => 0]);
        }
        if (!$userInfo && !empty($wechatInfo['unionid'])) {
            $uid = (int)$this->dao->value(['unionid' => $wechatInfo['unionid'], 'is_del' => 0], 'uid');
            if ($uid) {
                $userInfo = $userServices->getOne(['uid' => $uid, 'is_del' => 0]);
            }
        }
        if (!$userInfo) {
            $userInfo = $this->getAuthUserInfo((string)$openid, (string)$userType);
        }
        if ($userInfo) {
            $uid = (int)$userInfo['uid'];
            $userInfo['new_user'] = 0;
        }

        $wechatInfo['openid'] = (string)$openid;
        $wechatInfo['user_type'] = (string)$userType;
        if ($userInfo) {
            $wechatUser = $this->dao->getOne([
                'uid' => $uid,
                'user_type' => $userType,
                'is_del' => 0,
            ]);
            if ($wechatUser && $wechatUser['openid'] !== $wechatInfo['openid']) {
                return is_object($userInfo) ? $userInfo->toArray() : $userInfo;
            }

            $this->transaction(function () use ($userServices, $wechatInfo, $userInfo, $uid, $spreadId, $agentId, $wechatUser) {
                if ($agentId) {
                    $wechatInfo['code'] = $agentId;
                    $wechatInfo['is_staff'] = 1;
                } else {
                    $wechatInfo['code'] = $spreadId;
                }
                app()->make(LoginServices::class)->updateUserInfo($wechatInfo, $userInfo);
                if ($wechatUser) {
                    if (!$this->dao->update($wechatUser['id'], $wechatInfo, 'id')) {
                        throw new ApiException('修改失败');
                    }
                } else {
                    $wechatInfo['uid'] = $uid;
                    if (!$this->dao->save($wechatInfo)) {
                        throw new ApiException('修改失败');
                    }
                }
            });
            return is_object($userInfo) ? $userInfo->toArray() : $userInfo;
        }

        $userInfo = $this->transaction(function () use ($userServices, $wechatInfo, $spreadId, $userType) {
            $user = $userServices->setUserInfo($wechatInfo, (int)$spreadId, (string)$userType);
            if (!$user) {
                throw new AuthException('新增用户失败');
            }
            $wechatInfo['uid'] = $user->uid;
            $wechatInfo['add_time'] = $user->add_time;
            if (!$this->dao->save($wechatInfo)) {
                throw new AuthException('新增用户失败');
            }
            $user['new_user'] = (int)sys_config('get_avatar', 0);
            return $user;
        });

        return is_object($userInfo) ? $userInfo->toArray() : $userInfo;
    }
}
