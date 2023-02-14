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

namespace app\services\wechat;


use app\dao\wechat\WechatMessageDao;
use app\services\BaseServices;
use crmeb\exceptions\ApiException;
use think\facade\Cache;

class WechatMessageServices extends BaseServices
{
    /**
     * 构造方法
     * WechatMessageServices constructor.
     * @param WechatMessageDao $dao
     */
    public function __construct(WechatMessageDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param $result
     * @param $openid
     * @param $type
     * @return \think\Model
     */
    public function setMessage($result, $openid, $type)
    {
        if (is_object($result) || is_array($result)) $result = json_encode($result);
        $add_time = time();
        $data = compact('result', 'openid', 'type', 'add_time');
        return $this->dao->save($data);
    }

    public function setOnceMessage($result, $openid, $type, $unique, $cacheTime = 172800)
    {
        $cacheName = 'wechat_message_' . $type . '_' . $unique;
        if (Cache::has($cacheName)) return true;
        $res = $this->setMessage($result, $openid, $type);
        if ($res) Cache::set($cacheName, 1, $cacheTime);
        return $res;
    }

    /**
     * 微信消息前置操作
     * @param $message
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechatMessageBefore($message)
    {
        //后台开启，用户直接关注公众号才会生成用户
        if (intval(sys_config('create_wechat_user', 0))) {
            /** @var WechatUserServices $wechatUser */
            $wechatUser = app()->make(WechatUserServices::class);
            $wechatUser->saveUser($message->FromUserName);
        }
        $event = isset($message->Event) ?
            $message->MsgType . (
            $message->Event == 'subscribe' && isset($message->EventKey) ? '_scan' : ''
            ) . '_' . $message->Event : $message->MsgType;
        $result = json_encode($message);
        $openid = $message->FromUserName;
        $type = strtolower($event);
        $add_time = time();
        if (!$this->dao->save(compact('result', 'openid', 'type', 'add_time'))) {
            throw new ApiException(410080);
        }
        return true;
    }
}
