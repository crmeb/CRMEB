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
declare(strict_types=1);

namespace app\services\channel;

use think\App as ThinkApp;

/**
 * 渠道基类
 * 所有端渠道继承此类，实现各自的授权登录、消息发送等逻辑
 * 通用业务方法（注册、Token、用户创建等）在基类中实现，各端直接调用
 *
 * 位于 app 层，可直接调用 app 层用户/Token 等服务，
 * 各端渠道处理器（modules/channel/{端}/）继承本类实现各端差异逻辑。
 * @package app\services\channel
 */
abstract class BaseChannel
{
    /**
     * 应用实例
     */
    protected $app;

    /**
     * 端标识（wechat_mp/wechat_mini/pc/h5/app/wechat）
     */
    protected $channelName;

    /**
     * 用户类型（对应 User 表 user_type 字段）
     */
    protected $userType;

    public function __construct(ThinkApp $app)
    {
        $this->app = $app;
    }

    /**
     * 获取端标识
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    /**
     * 获取用户类型
     * @return string
     */
    public function getUserType(): string
    {
        return $this->userType;
    }

    /* ===================== 抽象方法（各端实现） ===================== */

    /**
     * 授权登录
     * 各端实现自己的授权逻辑（公众号网页授权、小程序code换session、PC开放平台扫码等）
     * @param mixed ...$args
     * @return array
     */
    abstract public function authLogin(...$args): array;

    /**
     * 绑定手机号
     * @param mixed ...$args
     * @return array
     */
    abstract public function bindingPhone(...$args): array;

    /**
     * 发送消息
     * 各端实现自己的消息发送逻辑（公众号模板消息、小程序订阅消息等）
     * @param string $openId 接收者openid
     * @param string $templateId 模板ID
     * @param array $data 模板数据
     * @param string|null $url 跳转链接
     * @return bool
     */
    abstract public function sendMessage(string $openId, string $templateId, array $data = [], ?string $url = null): bool;

    /* ===================== 通用业务方法（基类实现，各端调用） ===================== */

    /**
     * 用户注册（写入 User 表）
     * 注册后自动触发：送余额、送积分、送优惠券、绑定推广关系、注册事件
     * @param array $userInfo 用户信息（nickname, avatar/headimgurl, phone, openid等）
     * @param int $spreadUid 推广人uid
     * @return mixed User模型实例
     */
    protected function register(array $userInfo, int $spreadUid = 0)
    {
        return app()->make(\app\services\user\UserServices::class)
            ->setUserInfo($userInfo, $spreadUid, $this->userType);
    }

    /**
     * 微信用户创建/绑定（写入 User 表 + WechatUser 表）
     * 通用流程：处理推广关系 → 处理昵称 → 查找已有用户 → 创建或更新
     * 通过事件解耦：由 modules/channel/wechat_mp 监听器处理，避免 app 层直接引用 modules
     * @param array $data [openid, wechatInfo, spreadId, agent_id, login_type, userType]
     * @return mixed
     */
    protected function wechatOauthAfter(array $data)
    {
        return event('WechatUserOauth', ['data' => $data], true);
    }

    /**
     * 生成Token
     * @param int $uid 用户ID
     * @return array
     */
    protected function createToken(int $uid): array
    {
        return app()->make(\app\services\BaseService::class)->createToken($uid, 'api');
    }

    /**
     * 生成客服Token
     * @param int $kefuId 客服ID
     * @return array
     */
    protected function createKefuToken(int $kefuId): array
    {
        return app()->make(\app\services\BaseService::class)->createToken($kefuId, 'kefu');
    }

    /**
     * 检查是否需要强制绑定手机号
     * @param mixed $user 用户信息（已有用户时传入，新用户传null）
     * @return bool
     */
    protected function needBindPhone($user = null): bool
    {
        if (!sys_config('store_user_mobile')) {
            return false;
        }
        if ($user && $user['phone']) {
            return false;
        }
        return true;
    }

    /**
     * 缓存授权中间数据（两阶段授权用）
     * @param string $openid
     * @param array $createData
     * @return string 缓存key
     */
    protected function cacheAuthData(string $openid, array $createData): string
    {
        $key = md5($openid . '_' . time() . '_' . $this->channelName);
        \crmeb\services\CacheService::set($key, $createData, 7200);
        return $key;
    }

    /**
     * 获取缓存的授权数据
     * @param string $key
     * @return array
     */
    protected function getAuthData(string $key): array
    {
        return \crmeb\services\CacheService::get($key);
    }

    /**
     * 记录登录访问
     * @param mixed $user 用户信息
     * @return void
     */
    protected function loginSaveVisit($user): void
    {
        app()->make(\app\services\user\UserVisitServices::class)->loginSaveVisit($user);
    }
}
