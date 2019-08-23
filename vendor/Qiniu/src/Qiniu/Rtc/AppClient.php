<?php
namespace Qiniu\Rtc;

use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Config;
use Qiniu\Auth;

class AppClient
{
    private $auth;
    private $baseURL;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;

        $this->baseURL = sprintf("%s/%s/apps", Config::RTCAPI_HOST, Config::RTCAPI_VERSION);
    }

    /*
     * hub: 直播空间名
     * title: app 的名称  注意，Title 不是唯一标识，重复 create 动作将生成多个 app
     * maxUsers：人数限制
     * NoAutoKickUser: bool 类型，可选，禁止自动踢人（抢流）。默认为 false ，
       即同一个身份的 client (app/room/user) ，新的连麦请求可以成功，旧连接被关闭。
     */
    public function createApp($hub, $title, $maxUsers = null, $noAutoKickUser = null)
    {
        $params['hub'] = $hub;
        $params['title'] = $title;
        if (!empty($maxUsers)) {
            $params['maxUsers'] = $maxUsers;
        }
        if (!empty($noAutoKickUser)) {
            $params['noAutoKickUser'] = $noAutoKickUser;
        }
        $body = json_encode($params);
        $ret = $this->post($this->baseURL, $body);
        return $ret;
    }

    /*
     * appId: app 的唯一标识，创建的时候由系统生成。
     * Title: app 的名称， 可选。
     * Hub: 绑定的直播 hub，可选，用于合流后 rtmp 推流。
     * MaxUsers: int 类型，可选，连麦房间支持的最大在线人数。
     * NoAutoKickUser: bool 类型，可选，禁止自动踢人。
     * MergePublishRtmp: 连麦合流转推 RTMP 的配置，可选择。其详细配置包括如下
            Enable: 布尔类型，用于开启和关闭所有房间的合流功能。
            AudioOnly: 布尔类型，可选，指定是否只合成音频。
            Height, Width: int64，可选，指定合流输出的高和宽，默认为 640 x 480。
            OutputFps: int64，可选，指定合流输出的帧率，默认为 25 fps 。
            OutputKbps: int64，可选，指定合流输出的码率，默认为 1000 。
            URL: 合流后转推旁路直播的地址，可选，支持魔法变量配置按照连麦房间号生成不同的推流地址。如果是转推到七牛直播云，不建议使用该配置。
            StreamTitle: 转推七牛直播云的流名，可选，支持魔法变量配置按照连麦房间号生成不同的流名。例如，配置 Hub 为 qn-zhibo ，配置 StreamTitle 为 $(roomName) ，则房间 meeting-001 的合流将会被转推到 rtmp://pili-publish.qn-zhibo.***.com/qn-zhibo/meeting-001地址。详细配置细则，请咨询七牛技术支持。
     */
    public function updateApp($appId, $hub, $title, $maxUsers = null, $mergePublishRtmp = null, $noAutoKickUser = null)
    {
        $url = $this->baseURL . '/' . $appId;
        $params['hub'] = $hub;
        $params['title'] = $title;
        if (!empty($maxUsers)) {
            $params['maxUsers'] = $maxUsers;
        }
        if (!empty($noAutoKickUser)) {
            $params['noAutoKickUser'] = $noAutoKickUser;
        }
        if (!empty($mergePublishRtmp)) {
            $params['mergePublishRtmp'] = $mergePublishRtmp;
        }
        $body = json_encode($params);
        $ret = $this->post($url, $body);
        return $ret;
    }

    /*
     * appId: app 的唯一标识，创建的时候由系统生成。
     */
    public function getApp($appId)
    {
        $url = $this->baseURL . '/' . $appId;
        $ret  = $this->get($url);
        return $ret;
    }

    /*
     * appId: app 的唯一标识，创建的时候由系统生成
     */
    public function deleteApp($appId)
    {
        $url = $this->baseURL . '/' . $appId;
        list(, $err)  = $this->delete($url);
        return $err;
    }

    /*
     * 获取房间的人数
     * appId: app 的唯一标识，创建的时候由系统生成。
     * roomName: 操作所查询的连麦房间。
     */
    public function listUser($appId, $roomName)
    {
        $url = sprintf("%s/%s/rooms/%s/users", $this->baseURL, $appId, $roomName);
        $ret  = $this->get($url);
        return $ret;
    }

   /*
    * 踢出玩家
    * appId: app 的唯一标识，创建的时候由系统生成。
    * roomName: 连麦房间
    * userId: 请求加入房间的用户ID
    */
    public function kickUser($appId, $roomName, $userId)
    {
        $url = sprintf("%s/%s/rooms/%s/users/%s", $this->baseURL, $appId, $roomName, $userId);
        list(, $err)  = $this->delete($url);
        return $err;
    }

    /*
     * 获取房间的人数
     * appId: app 的唯一标识，创建的时候由系统生成。
     * prefix: 所查询房间名的前缀索引，可以为空。
     * offset: int 类型，分页查询的位移标记。
     * limit: int 类型，此次查询的最大长度。
     * GET /v3/apps/<AppID>/rooms?prefix=<RoomNamePrefix>&offset=<Offset>&limit=<Limit>
     */
    public function listActiveRooms($appId, $prefix = null, $offset = null, $limit = null)
    {
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
        $ret  = $this->get($url);
        return $ret;
    }

    /*
     * appId: app 的唯一标识，创建的时候由系统生成。
     * roomName: 房间名称，需满足规格 ^[a-zA-Z0-9_-]{3,64}$
     * userId: 请求加入房间的用户 ID，需满足规格 ^[a-zA-Z0-9_-]{3,50}$
     * expireAt: int64 类型，鉴权的有效时间，传入以秒为单位的64位Unix
       绝对时间，token 将在该时间后失效。
     * permission: 该用户的房间管理权限，"admin" 或 "user"，默认为 "user" 。
       当权限角色为 "admin" 时，拥有将其他用户移除出房间等特权.
     */
    public function appToken($appId, $roomName, $userId, $expireAt, $permission)
    {
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
        $ret = Client::get($url, $rtcToken);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function delete($url, $contentType = 'application/json')
    {
        $rtcToken = $this->auth->authorizationV2($url, "DELETE", null, $contentType);
        $rtcToken['Content-Type'] = $contentType;
        $ret = Client::delete($url, $rtcToken);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function post($url, $body, $contentType = 'application/json')
    {
        $rtcToken = $this->auth->authorizationV2($url, "POST", $body, $contentType);
        $rtcToken['Content-Type'] = $contentType;
        $ret = Client::post($url, $body, $rtcToken);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }
}
