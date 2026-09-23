<?php

namespace Qiniu\Rtc;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Http\Error;
use Qiniu\Http\Client;
use Qiniu\Http\Proxy;

class AppClient
{
    private $auth;
    private $baseURL;
    private $proxy;

    public function __construct(Auth $auth, $proxy = null, $proxy_auth = null, $proxy_user_password = null)
    {
        $this->auth = $auth;
        $this->baseURL = sprintf("%s/%s/apps", Config::RTCAPI_HOST, Config::RTCAPI_VERSION);
        $this->proxy = new Proxy($proxy, $proxy_auth, $proxy_user_password);
    }

    /**
     * 创建应用
     *
     * @param string $hub 绑定的直播 hub
     * @param string $title app 的名称  注意，Title 不是唯一标识，重复 create 动作将生成多个 app
     * @param int $maxUsers 连麦房间支持的最大在线人数
     * @param bool $noAutoKickUser 禁止自动踢人（抢流），默认为 false
     * @return array
     * @link  https://doc.qnsdk.com/rtn/docs/server_overview#2_1
     */
    public function createApp($hub, $title, $maxUsers = null, $noAutoKickUser = null)
    {
        $params = array();
        $params['hub'] = $hub;
        $params['title'] = $title;
        if (!empty($maxUsers)) {
            $params['maxUsers'] = $maxUsers;
        }
        if ($noAutoKickUser !== null) {
            $params['noAutoKickUser'] = $noAutoKickUser;
        }
        $body = json_encode($params);
        return $this->post($this->baseURL, $body);
    }

    /**
     * 更新一个应用的配置信息
     *
     * @param string $appId app 的唯一标识，创建的时候由系统生成
     * @param string $hub app 的名称，可选
     * @param string $title 绑定的直播 hub，可选，用于合流后 rtmp 推流
     * @param int $maxUsers 连麦房间支持的最大在线人数，可选
     * @param bool $noAutoKickUser 禁止自动踢人，可选
     * @param null $mergePublishRtmp 连麦合流转推 RTMP 的配置，可选择。其详细配置可以参考文档
     * @return array
     * @link  https://doc.qnsdk.com/rtn/docs/server_overview#2_1
     */
    public function updateApp($appId, $hub, $title, $maxUsers = null, $noAutoKickUser = null, $mergePublishRtmp = null)
    {
        $url = $this->baseURL . '/' . $appId;
        $params = array();
        $params['hub'] = $hub;
        $params['title'] = $title;
        if (!empty($maxUsers)) {
            $params['maxUsers'] = $maxUsers;
        }
        if ($noAutoKickUser !== null) {
            $params['noAutoKickUser'] = $noAutoKickUser;
        }
        if (!empty($mergePublishRtmp)) {
            $params['mergePublishRtmp'] = $mergePublishRtmp;
        }
        $body = json_encode($params);
        return $this->post($url, $body);
    }

    /**
     * 获取应用信息
     *
     * @param string $appId
     * @return array
     * @link  https://doc.qnsdk.com/rtn/docs/server_overview#2_1
     */
    public function getApp($appId)
    {
        $url = $this->baseURL . '/' . $appId;
        return $this->get($url);
    }

    /**
     * 删除应用
     *
     * @param string $appId app 的唯一标识，创建的时候由系统生成
     * @return array
     * @link  https://doc.qnsdk.com/rtn/docs/server_overview#2_1
     */
    public function deleteApp($appId)
    {
        $url = $this->baseURL . '/' . $appId;
        return $this->delete($url);
    }

    /**
     * 获取房间内用户列表
     *
     * @param string $appId app 的唯一标识，创建的时候由系统生成
     * @param string $roomName 操作所查询的连麦房间
     * @return array
     * @link https://doc.qnsdk.com/rtn/docs/server_overview#2_2
     */
    public function listUser($appId, $roomName)
    {
        $url = sprintf("%s/%s/rooms/%s/users", $this->baseURL, $appId, $roomName);
        return $this->get($url);
    }

    /**
     * 指定一个用户踢出房间
     *
     * @param string $appId app 的唯一标识，创建的时候由系统生成
     * @param string $roomName 连麦房间
     * @param string $userId 操作所剔除的用户
     * @return mixed
     * @link https://doc.qnsdk.com/rtn/docs/server_overview#2_2
     */
    public function kickUser($appId, $roomName, $userId)
    {
        $url = sprintf("%s/%s/rooms/%s/users/%s", $this->baseURL, $appId, $roomName, $userId);
        return $this->delete($url);
    }

    /**
     * 停止一个房间的合流转推
     *
     * @param string $appId
     * @param string $roomName
     * @return array
     * @link https://doc.qnsdk.com/rtn/docs/server_overview#2_2
     */
    public function stopMerge($appId, $roomName)
    {
        $url = sprintf("%s/%s/rooms/%s/merge", $this->baseURL, $appId, $roomName);
        return $this->delete($url);
    }

    /**
     * 获取应用中活跃房间
     *
     * @param string $appId 连麦房间所属的 app
     * @param null $prefix 所查询房间名的前缀索引，可以为空。
     * @param int $offset 分页查询的位移标记
     * @param int $limit 此次查询的最大长度
     * @return array
     * @link https://doc.qnsdk.com/rtn/docs/server_overview#2_2
     */
    public function listActiveRooms($appId, $prefix = null, $offset = null, $limit = null)
    {
        $query = array();
        if (isset($prefix)) {
            $query['prefix'] = $prefix;
        }
        if (isset($offset)) {
            $query['offset'] = $offset;
        }
        if (isset($limit)) {
            $query['limit'] = $limit;
        }
        if (isset($query) && !empty($query)) {
            $query = '?' . http_build_query($query);
            $url = sprintf("%s/%s/rooms%s", $this->baseURL, $appId, $query);
        } else {
            $url = sprintf("%s/%s/rooms", $this->baseURL, $appId);
        }
        return $this->get($url);
    }

    /**
     * 生成加入房间的令牌
     *
     * @param string $appId app 的唯一标识，创建的时候由系统生成
     * @param string $roomName 房间名称，需满足规格 ^[a-zA-Z0-9_-]{3,64}$
     * @param string $userId 请求加入房间的用户 ID，需满足规格 ^[a-zA-Z0-9_-]{3,50}$
     * @param int $expireAt 鉴权的有效时间，传入以秒为单位的64位 Unix 绝对时间
     * @param string $permission 该用户的房间管理权限，"admin" 或 "user"，默认为 "user"
     * @return string
     * @link https://doc.qnsdk.com/rtn/docs/server_overview#1
     */
    public function appToken($appId, $roomName, $userId, $expireAt, $permission)
    {
        $params = array();
        $params['appId'] = $appId;
        $params['userId'] = $userId;
        $params['roomName'] = $roomName;
        $params['permission'] = $permission;
        $params['expireAt'] = $expireAt;
        $appAccessString = json_encode($params);
        return $this->auth->signWithData($appAccessString);
    }

    private function get($url, $cType = null)
    {
        $rtcToken = $this->auth->authorizationV2($url, "GET", null, $cType);
        $rtcToken['Content-Type'] = $cType;
        $ret = Client::get($url, $rtcToken, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function delete($url, $contentType = 'application/json')
    {
        $rtcToken = $this->auth->authorizationV2($url, "DELETE", null, $contentType);
        $rtcToken['Content-Type'] = $contentType;
        $ret = Client::delete($url, $rtcToken, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function post($url, $body, $contentType = 'application/json')
    {
        $rtcToken = $this->auth->authorizationV2($url, "POST", $body, $contentType);
        $rtcToken['Content-Type'] = $contentType;
        $ret = Client::post($url, $body, $rtcToken, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }
}
