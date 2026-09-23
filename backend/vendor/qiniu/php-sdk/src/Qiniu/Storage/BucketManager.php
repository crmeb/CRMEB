<?php

namespace Qiniu\Storage;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Http\Error;
use Qiniu\Http\Client;
use Qiniu\Http\Proxy;
use Qiniu\Http\Response;

/**
 * 主要涉及了空间资源管理及批量操作接口的实现，具体的接口规格可以参考
 *
 * @link https://developer.qiniu.com/kodo/api/1274/rs
 */
final class BucketManager
{
    private $auth;
    private $config;
    private $proxy;

    public function __construct(
        Auth $auth,
        Config $config = null,
        $proxy = null,
        $proxy_auth = null,
        $proxy_user_password = null
    ) {
        $this->auth = $auth;
        if ($config == null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }
        $this->proxy = new Proxy($proxy, $proxy_auth, $proxy_user_password);
    }

    /**
     * 获取指定账号下所有的空间名
     *
     * @param bool $shared 指定共享空间，rw：读写权限空间，rd：读权限空间
     * @return array 包含所有空间名
     */
    public function buckets($shared = true)
    {
        $includeShared = "false";
        if ($shared === true) {
            $includeShared = "true";
        }
        return $this->getV2($this->config->getUcHost() . '/buckets?shared=' . $includeShared);
    }

    /**
     * 列举空间，返回bucket列表
     *
     * @param string $region 区域
     * @param string $line
     * @param string $shared 指定共享空间，rw：读写权限空间，rd：读权限空间
     * @return array
     */
    public function listbuckets(
        $region = null,
        $line = 'false',
        $shared = 'false'
    ) {
        $path = '/v3/buckets?region=' . $region . '&line=' . $line . '&shared=' . $shared;
        return $this->ucPost($path);
    }

    /**
     * 创建空间
     *
     * @param string $name 创建的空间名
     * @param string $region 创建的区域，默认华东
     *
     * @return array
     * @link https://developer.qiniu.com/kodo/api/1382/mkbucketv3
     */
    public function createBucket($name, $region = 'z0')
    {
        $path = '/mkbucketv3/' . $name . '/region/' . $region;
        return $this->postV2($this->config->getUcHost() . $path, null);
    }

    /**
     * 删除空间
     *
     * @param string $name 需要删除的目标空间名
     *
     * @return array
     * @link https://developer.qiniu.com/kodo/api/1601/drop-bucket
     */
    public function deleteBucket($name)
    {
        $path = '/drop/' . $name;
        return $this->postV2($this->config->getUcHost() . $path, null);
    }

    /**
     * 获取指定空间绑定的所有的域名
     *
     * @param string $bucket 空间名称
     * @return array
     */
    public function domains($bucket)
    {
        return $this->ucGet('/v2/domains?tbl=' . $bucket);
    }

    /**
     * 获取指定空间的相关信息
     *
     * @param string $bucket 空间名称
     * @return array
     */
    public function bucketInfo($bucket)
    {
        $path = '/v2/bucketInfo?bucket=' . $bucket;
        return $this->ucPost($path);
    }

    /**
     * 获取指定zone的空间信息列表
     *
     * @param string $region 区域
     * @param string $shared 指定共享空间，rw：读写权限空间，rd：读权限空间
     * @param string $fs 如果为 true，会返回每个空间当前的文件数和存储量（实时数据）
     * @return array
     */
    public function bucketInfos($region = null, $shared = 'false', $fs = 'false')
    {
        $path = '/v2/bucketInfos?region=' . $region . '&shared=' . $shared . '&fs=' . $fs;
        return $this->ucPost($path);
    }

    /**
     * 列取空间的文件列表
     *
     * @param string $bucket 空间名
     * @param string $prefix 列举前缀
     * @param string $marker 列举标识符
     * @param int $limit 单次列举个数限制
     * @param string $delimiter 指定目录分隔符
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1284/list
     */
    public function listFiles(
        $bucket,
        $prefix = null,
        $marker = null,
        $limit = 1000,
        $delimiter = null
    ) {
        $query = array('bucket' => $bucket);
        \Qiniu\setWithoutEmpty($query, 'prefix', $prefix);
        \Qiniu\setWithoutEmpty($query, 'marker', $marker);
        \Qiniu\setWithoutEmpty($query, 'limit', $limit);
        \Qiniu\setWithoutEmpty($query, 'delimiter', $delimiter);
        return $this->rsfGet($bucket, '/list?' . http_build_query($query));
    }

    /**
     * 列取空间的文件列表
     *
     * @deprecated API 可能返回仅包含 marker，不包含 item 或 dir 的项，请使用 {@link listFiles}
     *
     * @param string $bucket 空间名
     * @param string $prefix 列举前缀
     * @param string $marker 列举标识符
     * @param int $limit 单次列举个数限制
     * @param string $delimiter 指定目录分隔符
     * @param bool $skipconfirm 是否跳过已删除条目的确认机制
     *
     * @return array
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/list.html
     */
    public function listFilesv2(
        $bucket,
        $prefix = null,
        $marker = null,
        $limit = 1000,
        $delimiter = null,
        $skipconfirm = true
    ) {
        $query = array('bucket' => $bucket);
        \Qiniu\setWithoutEmpty($query, 'prefix', $prefix);
        \Qiniu\setWithoutEmpty($query, 'marker', $marker);
        \Qiniu\setWithoutEmpty($query, 'limit', $limit);
        \Qiniu\setWithoutEmpty($query, 'delimiter', $delimiter);
        \Qiniu\setWithoutEmpty($query, 'skipconfirm', $skipconfirm);
        $path = '/v2/list?' . http_build_query($query);

        list($host, $err) = $this->config->getRsfHostV2(
            $this->auth->getAccessKey(),
            $bucket,
            $this->proxy->makeReqOpt()
        );

        if ($err != null) {
            return array(null, $err);
        }

        $url = $host . $path;
        $headers = $this->auth->authorizationV2($url, 'POST', null, 'application/x-www-form-urlencoded');
        $ret = Client::post($url, null, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = explode("\n", $ret->body);
        array_pop($r);
        return array($r, null);
    }

    /**
     * 增加bucket生命规则
     *
     * @param string $bucket
     * 空间名
     * @param string $name
     * 规则名称 bucket 内唯一，长度小于50，不能为空，只能为字母、数字、下划线
     * @param string $prefix
     * 同一个 bucket 里面前缀不能重复
     * @param int $delete_after_days
     * 指定上传文件多少天后删除，指定为0表示不删除，大于0表示多少天后删除。
     * 需大于 to_line_after_days
     * @param int $to_line_after_days
     * 指定文件上传多少天后转低频存储。指定为0表示不转低频存储
     * @param int $to_archive_ir_after_days
     * 指定文件上传多少天后转归档直读。指定为0表示不转归档直读
     * @param int $to_archive_after_days
     * 指定文件上传多少天后转归档存储。指定为0表示不转归档存储
     * @param int $to_deep_archive_after_days
     * 指定文件上传多少天后转深度归档存储。指定为0表示不转深度归档存储
     * @return array
     */
    public function bucketLifecycleRule(
        $bucket,
        $name,
        $prefix,
        $delete_after_days = null,
        $to_line_after_days = null,
        $to_archive_after_days = null,
        $to_deep_archive_after_days = null,
        $to_archive_ir_after_days = null
    ) {
        $path = '/rules/add';
        $params = array();
        if ($bucket) {
            $params['bucket'] = $bucket;
        }
        if ($name) {
            $params['name'] = $name;
        }
        if ($prefix) {
            $params['prefix'] = $prefix;
        }
        if ($delete_after_days) {
            $params['delete_after_days'] = $delete_after_days;
        }
        if ($to_line_after_days) {
            $params['to_line_after_days'] = $to_line_after_days;
        }
        if ($to_archive_ir_after_days) {
            $params['to_archive_ir_after_days'] = $to_archive_ir_after_days;
        }
        if ($to_archive_after_days) {
            $params['to_archive_after_days'] = $to_archive_after_days;
        }
        if ($to_deep_archive_after_days) {
            $params['to_deep_archive_after_days'] = $to_deep_archive_after_days;
        }
        $data = http_build_query($params);
        $info = $this->ucPost($path, $data);
        return $info;
    }

    /**
     * 更新bucket生命规则
     *
     * @param string $bucket
     * 空间名
     * @param string $name
     * 规则名称 bucket 内唯一，长度小于50，不能为空，只能为字母、数字、下划线
     * @param string $prefix
     * 同一个 bucket 里面前缀不能重复
     * @param int $delete_after_days
     * 指定上传文件多少天后删除，指定为0表示不删除，大于0表示多少天后删除
     * 需大于 to_line_after_days
     * @param int $to_line_after_days
     * 指定文件上传多少天后转低频存储。指定为0表示不转低频存储
     * @param int $to_archive_ir_after_days
     * 指定文件上传多少天后转归档只读。指定为0表示不转归档只读
     * @param int $to_archive_after_days
     * 指定文件上传多少天后转归档存储。指定为0表示不转归档存储
     * @param int $to_deep_archive_after_days
     * 指定文件上传多少天后转深度归档存储。指定为0表示不转深度归档存储
     * @return array
     */
    public function updateBucketLifecycleRule(
        $bucket,
        $name,
        $prefix,
        $delete_after_days = null,
        $to_line_after_days = null,
        $to_archive_after_days = null,
        $to_deep_archive_after_days = null,
        $to_archive_ir_after_days = null
    ) {
        $path = '/rules/update';
        $params = array();
        if ($bucket) {
            $params['bucket'] = $bucket;
        }
        if ($name) {
            $params['name'] = $name;
        }
        if ($prefix) {
            $params['prefix'] = $prefix;
        }
        if ($delete_after_days) {
            $params['delete_after_days'] = $delete_after_days;
        }
        if ($to_line_after_days) {
            $params['to_line_after_days'] = $to_line_after_days;
        }
        if ($to_archive_ir_after_days) {
            $params['to_archive_ir_after_days'] = $to_archive_ir_after_days;
        }
        if ($to_archive_after_days) {
            $params['to_archive_after_days'] = $to_archive_after_days;
        }
        if ($to_deep_archive_after_days) {
            $params['to_deep_archive_after_days'] = $to_deep_archive_after_days;
        }
        $data = http_build_query($params);
        return $this->ucPost($path, $data);
    }

    /**
     * 获取bucket生命规则
     *
     * @param string $bucket 空间名
     * @return array
     */
    public function getBucketLifecycleRules($bucket)
    {
        $path = '/rules/get?bucket=' . $bucket;
        $info = $this->ucGet($path);
        return $info;
    }

    /**
     * 删除bucket生命规则
     *
     * @param string $bucket 空间名
     * @param string $name 规则名称 bucket 内唯一，长度小于50，不能为空，
     * 只能为字母、数字、下划线（）
     * @return array
     */
    public function deleteBucketLifecycleRule($bucket, $name)
    {
        $path = '/rules/delete';
        $params = array();
        if ($bucket) {
            $params['bucket'] = $bucket;
        }
        if ($name) {
            $params['name'] = $name;
        }
        $data = http_build_query($params);
        $info = $this->ucPost($path, $data);
        return $info;
    }

    /**
     * 增加bucket事件通知规则
     *
     * @param string $bucket 空间名
     * @param string $name 规则名称 bucket 内唯一，长度小于50，不能为空，
     * 只能为字母、数字、下划线()
     * @param string $prefix 同一个 bucket 里面前缀不能重复
     * @param string $suffix 可选，文件配置的后缀
     * @param array $event 事件类型，可以指定多个，包括 put,mkfile,delete,copy,move,append,
     * disable,enable,deleteMarkerCreate
     * @param string $callbackURL 通知URL，可以指定多个，失败依次重试
     * @param string $access_key 可选，设置的话会对通知请求用对应的ak、sk进行签名
     * @param string $host 可选，通知请求的host
     *
     * @return array
     */
    public function putBucketEvent(
        $bucket,
        $name,
        $prefix,
        $suffix,
        $event,
        $callbackURL,
        $access_key = null,
        $host = null
    ) {
        $path = '/events/add';
        $params = array();
        if (!empty($bucket)) {
            $params['bucket'] = $bucket;
        }
        if (!empty($name)) {
            $params['name'] = $name;
        }
        if (!empty($prefix)) {
            $params['prefix'] = $prefix;
        }
        if (!empty($suffix)) {
            $params['suffix'] = $suffix;
        }
        if (!empty($callbackURL)) {
            $params['callbackURL'] = $callbackURL;
        }
        if (!empty($access_key)) {
            $params['access_key'] = $access_key;
        }
        if (!empty($host)) {
            $params['host'] = $host;
        }
        $data = http_build_query($params);
        if (!empty($event)) {
            $eventpath = "";
            foreach ($event as $key => $value) {
                $eventpath .= "&event=$value";
            }
            $data .= $eventpath;
        }
        $info = $this->ucPost($path, $data);
        return $info;
    }

    /**
     * 更新bucket事件通知规则
     *
     * @param string $bucket 空间名
     * @param string $name 规则名称 bucket 内唯一，长度小于50，不能为空，
     * 只能为字母、数字、下划线()
     * @param string $prefix 同一个 bucket 里面前缀不能重复
     * @param string $suffix 可选，文件配置的后缀
     * @param array $event 事件类型，可以指定多个，包括 put,mkfile,delete,copy,move,append,disable,
     * enable,deleteMarkerCreate
     * @param string $callbackURL 通知URL，可以指定多个，失败依次重试
     * @param string $access_key 可选，设置的话会对通知请求用对应的ak、sk进行签名
     * @param string $host 可选，通知请求的host
     *
     * @return array
     */
    public function updateBucketEvent(
        $bucket,
        $name,
        $prefix,
        $suffix,
        $event,
        $callbackURL,
        $access_key = null,
        $host = null
    ) {
        $path = '/events/update';
        $params = array();
        if (!empty($bucket)) {
            $params['bucket'] = $bucket;
        }
        if (!empty($name)) {
            $params['name'] = $name;
        }
        if (!empty($prefix)) {
            $params['prefix'] = $prefix;
        }
        if ($suffix) {
            $params['suffix'] = $suffix;
        }
        if (!empty($event)) {
            $params['event'] = $event;
        }
        if (!empty($callbackURL)) {
            $params['callbackURL'] = $callbackURL;
        }
        if (!empty($access_key)) {
            $params['access_key'] = $access_key;
        }
        if (!empty($host)) {
            $params['host'] = $host;
        }
        $data = http_build_query($params);
        if (!empty($event)) {
            $eventpath = "";
            foreach ($event as $key => $value) {
                $eventpath .= "&event=$value";
            }
            $data .= $eventpath;
        }
        return $this->ucPost($path, $data);
    }

    /**
     * 获取bucket事件通知规则
     *
     * @param string $bucket 空间名
     * @return array
     */
    public function getBucketEvents($bucket)
    {
        $path = '/events/get?bucket=' . $bucket;
        return $this->ucGet($path);
    }

    /**
     * 删除bucket事件通知规则
     *
     * @param string $bucket 空间名
     * @param string $name 规则名称bucket内唯一,长度小于50,不能为空,只能为字母、数字、下划线
     * @return array
     */
    public function deleteBucketEvent($bucket, $name)
    {
        $path = '/events/delete';
        $params = array();
        if ($bucket) {
            $params['bucket'] = $bucket;
        }
        if ($name) {
            $params['name'] = $name;
        }
        $data = http_build_query($params);
        return $this->ucPost($path, $data);
    }

    /**
     * 获取bucket的跨域信息
     *
     * @param string $bucket 空间名
     * @return array
     */
    public function getCorsRules($bucket)
    {
        $path = '/corsRules/get/' . $bucket;
        return $this->ucGet($path);
    }

    /**
     * 开关原图保护
     *
     * @param string $bucket 空间名称
     * @param int $mode mode 为1表示开启原图保护，0表示关闭
     * @return array
     */
    public function putBucketAccessStyleMode($bucket, $mode)
    {
        $path = '/accessMode/' . $bucket . '/mode/' . $mode;
        return $this->ucPost($path, null);
    }

    /**
     * 设置私有属性
     *
     * @param string $bucket 空间名称
     * @param int $private private为0表示公开，为1表示私有
     * @return array
     */
    public function putBucketAccessMode($bucket, $private)
    {
        $path = "/private?bucket=$bucket&private=$private";
        return $this->ucPost($path, null);
    }

    /**
     * 设置 referer 防盗链
     *
     * @param string $bucket 空间名称
     * @param int $mode 0:关闭Referer(使用此选项将会忽略以下参数并将恢复默认值);
     *                  1:设置Referer白名单; 2:设置Referer黑名单
     * @param string $norefer 0:不允许空 Refer 访问; 1:表示允许空Refer访问
     * @param string $pattern 规则字符串
     * @param int $enabled 源站是否支持，默认为0只给CDN配置, 设置为1表示开启源站防盗链
     * @return array
     * @link https://developer.qiniu.com/kodo/manual/6093/set-the-hotlinking-prevention
     */
    public function putReferAntiLeech($bucket, $mode, $norefer, $pattern, $enabled = 1)
    {
        $path = "/referAntiLeech?bucket=$bucket&mode=$mode&norefer=$norefer&pattern=$pattern&source_enabled=$enabled";
        return $this->ucPost($path, null);
    }

    /**
     * 设置Bucket的maxAge
     *
     * @param string $bucket 空间名称
     * @param int $maxAge maxAge为0或者负数表示为默认值（31536000）
     * @return array
     */
    public function putBucketMaxAge($bucket, $maxAge)
    {
        $path = '/maxAge?bucket=' . $bucket . '&maxAge=' . $maxAge;
        return $this->ucPost($path, null);
    }

    /**
     * 设置空间配额
     *
     * @param string $bucket 空间名称，不支持授权空间
     * @param string $size 空间存储量配额,参数传入0或不传表示不更改当前配置，传入-1表示取消限额，新创建的空间默认没有限额
     * @param string $count 空间文件数配额，参数含义同<size>
     * @return array
     */
    public function putBucketQuota($bucket, $size, $count)
    {
        $path = '/setbucketquota/' . $bucket . '/size/' . $size . '/count/' . $count;
        return $this->apiPost($bucket, $path);
    }

    /**
     * 获取空间配额
     *
     * @param string $bucket 空间名称
     * @return array
     */
    public function getBucketQuota($bucket)
    {
        $path = '/getbucketquota/' . $bucket;
        return $this->apiPost($bucket, $path);
    }

    /**
     * 获取资源的元信息，但不返回文件内容
     *
     * @param string $bucket 待获取信息资源所在的空间
     * @param string $key 待获取资源的文件名
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1308/stat
     */
    public function stat($bucket, $key)
    {
        $path = '/stat/' . \Qiniu\entry($bucket, $key);
        return $this->rsGet($bucket, $path);
    }

    /**
     * 删除指定资源
     *
     * @param string $bucket 待删除资源所在的空间
     * @param string $key 待删除资源的文件名
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1257/delete
     */
    public function delete($bucket, $key)
    {
        $path = '/delete/' . \Qiniu\entry($bucket, $key);
        return $this->rsPost($bucket, $path);
    }

    /**
     * 给资源进行重命名，本质为move操作。
     *
     * @param string $bucket 待操作资源所在空间
     * @param string $oldname 待操作资源文件名
     * @param string $newname 目标资源文件名
     *
     * @return array
     */
    public function rename($bucket, $oldname, $newname)
    {
        return $this->move($bucket, $oldname, $bucket, $newname);
    }

    /**
     * 对资源进行复制。
     *
     * @param string $from_bucket 待操作资源所在空间
     * @param string $from_key 待操作资源文件名
     * @param string $to_bucket 目标资源空间名
     * @param string $to_key 目标资源文件名
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1254/copy
     */
    public function copy($from_bucket, $from_key, $to_bucket, $to_key, $force = false)
    {
        $from = \Qiniu\entry($from_bucket, $from_key);
        $to = \Qiniu\entry($to_bucket, $to_key);
        $path = '/copy/' . $from . '/' . $to;
        if ($force === true) {
            $path .= '/force/true';
        }
        return $this->rsPost($from_bucket, $path);
    }

    /**
     * 将资源从一个空间到另一个空间
     *
     * @param string $from_bucket 待操作资源所在空间
     * @param string $from_key 待操作资源文件名
     * @param string $to_bucket 目标资源空间名
     * @param string $to_key 目标资源文件名
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1288/move
     */
    public function move($from_bucket, $from_key, $to_bucket, $to_key, $force = false)
    {
        $from = \Qiniu\entry($from_bucket, $from_key);
        $to = \Qiniu\entry($to_bucket, $to_key);
        $path = '/move/' . $from . '/' . $to;
        if ($force) {
            $path .= '/force/true';
        }
        return $this->rsPost($from_bucket, $path);
    }

    /**
     * 主动修改指定资源的文件元信息
     *
     * @param string $bucket 待操作资源所在空间
     * @param string $key 待操作资源文件名
     * @param string $mime 待操作文件目标mimeType
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1252/chgm
     */
    public function changeMime($bucket, $key, $mime)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $encode_mime = \Qiniu\base64_urlSafeEncode($mime);
        $path = '/chgm/' . $resource . '/mime/' . $encode_mime;
        return $this->rsPost($bucket, $path);
    }


    /**
     * 修改指定资源的存储类型
     *
     * @param string $bucket 待操作资源所在空间
     * @param string $key 待操作资源文件名
     * @param int $fileType 对象存储类型
     *   0 表示标准存储；
     *   1 表示低频存储；
     *   2 表示归档存储；
     *   3 表示深度归档存储；
     *   4 表示归档直读存储；
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/3710/chtype
     */
    public function changeType($bucket, $key, $fileType)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/chtype/' . $resource . '/type/' . $fileType;
        return $this->rsPost($bucket, $path);
    }

    /**
     * 解冻指定资源的存储类型
     *
     * @param string $bucket 待操作资源所在空间
     * @param string $key 待操作资源文件名
     * @param int $freezeAfterDays 解冻有效时长，取值范围 1~7
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/6380/restore-archive
     */
    public function restoreAr($bucket, $key, $freezeAfterDays)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/restoreAr/' . $resource . '/freezeAfterDays/' . $freezeAfterDays;
        return $this->rsPost($bucket, $path);
    }

    /**
     * 修改文件的存储状态，即禁用状态和启用状态间的的互相转换
     *
     * @param string $bucket 待操作资源所在空间
     * @param string $key 待操作资源文件名
     * @param int $status 0表示启用；1表示禁用
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/4173/modify-the-file-status
     */
    public function changeStatus($bucket, $key, $status)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/chstatus/' . $resource . '/status/' . $status;
        return $this->rsPost($bucket, $path);
    }

    /**
     * 从指定URL抓取资源，并将该资源存储到指定空间中
     *
     * @param string $url 指定的URL
     * @param string $bucket 目标资源空间
     * @param string $key 目标资源文件名
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1263/fetch
     */
    public function fetch($url, $bucket, $key = null)
    {

        $resource = \Qiniu\base64_urlSafeEncode($url);
        $to = \Qiniu\entry($bucket, $key);
        $path = '/fetch/' . $resource . '/to/' . $to;

        $ak = $this->auth->getAccessKey();


        list($ioHost, $err) = $this->config->getIovipHostV2($ak, $bucket, $this->proxy->makeReqOpt());
        if ($err != null) {
            return array(null, $err);
        }

        $url = $ioHost . $path;
        return $this->postV2($url, null);
    }

    /**
     * 从指定URL异步抓取资源，并将该资源存储到指定空间中
     *
     * @param string $url 需要抓取的url
     * @param string $bucket 所在区域的bucket
     * @param string $host 从指定url下载数据时使用的Host
     * @param string $key 文件存储的key
     * @param string $md5 文件md5
     * @param string $etag 文件etag
     * @param string $callbackurl 回调URL
     * @param string $callbackbody 回调Body
     * @param string $callbackbodytype 回调Body内容类型,默认为"application/x-www-form-urlencoded"
     * @param string $callbackhost 回调时使用的Host
     * @param int $file_type 存储文件类型
     *   0:标准存储(默认)
     *   1:低频存储
     *   2:归档存储
     *   3:深度归档存储
     *   4:归档直读存储
     * @param bool $ignore_same_key 如果空间中已经存在同名文件则放弃本次抓取
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/4097/asynch-fetch
     */
    public function asynchFetch(
        $url,
        $bucket,
        $host = null,
        $key = null,
        $md5 = null,
        $etag = null,
        $callbackurl = null,
        $callbackbody = null,
        $callbackbodytype = 'application/x-www-form-urlencoded',
        $callbackhost = null,
        $file_type = 0,
        $ignore_same_key = false
    ) {
        $path = '/sisyphus/fetch';

        $params = array('url' => $url, 'bucket' => $bucket);
        \Qiniu\setWithoutEmpty($params, 'host', $host);
        \Qiniu\setWithoutEmpty($params, 'key', $key);
        \Qiniu\setWithoutEmpty($params, 'md5', $md5);
        \Qiniu\setWithoutEmpty($params, 'etag', $etag);
        \Qiniu\setWithoutEmpty($params, 'callbackurl', $callbackurl);
        \Qiniu\setWithoutEmpty($params, 'callbackbody', $callbackbody);
        \Qiniu\setWithoutEmpty($params, 'callbackbodytype', $callbackbodytype);
        \Qiniu\setWithoutEmpty($params, 'callbackhost', $callbackhost);
        \Qiniu\setWithoutEmpty($params, 'file_type', $file_type);
        \Qiniu\setWithoutEmpty($params, 'ignore_same_key', $ignore_same_key);
        $data = json_encode($params);

        return $this->apiPost($bucket, $path, $data);
    }


    /**
     * 查询异步第三方资源抓取任务状态
     *
     * @param string $zone
     * @param string $id
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/4097/asynch-fetch
     */
    public function asynchFetchStatus($zone, $id)
    {
        $scheme = "http://";

        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }

        $url = $scheme . "api-" . $zone . ".qiniuapi.com/sisyphus/fetch?id=" . $id;

        list($ret, $err) = $this->getV2($url);

        if ($err != null) {
            return array(null, $err);
        }
        return array($ret, null);
    }


    /**
     * 从镜像源站抓取资源到空间中，如果空间中已经存在，则覆盖该资源
     *
     * @param string $bucket 待获取资源所在的空间
     * @param string $key 代获取资源文件名
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1293/prefetch
     */
    public function prefetch($bucket, $key)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/prefetch/' . $resource;

        $ak = $this->auth->getAccessKey();
        list($ioHost, $err) = $this->config->getIovipHostV2($ak, $bucket, $this->proxy->makeReqOpt());

        if ($err != null) {
            return array(null, $err);
        }

        $url = $ioHost . $path;
        return $this->postV2($url, null);
    }

    /**
     * 在单次请求中进行多个资源管理操作
     *
     * @param array $operations 资源管理操作数组
     *
     * @return array 每个资源的处理情况，结果类似：
     *              [
     *                   { "code" => <HttpCode int>, "data" => <Data> },
     *                   { "code" => <HttpCode int> },
     *                   { "code" => <HttpCode int> },
     *                   { "code" => <HttpCode int> },
     *                   { "code" => <HttpCode int>, "data" => { "error": "<ErrorMessage string>" } },
     *                   ...
     *               ]
     * @link http://developer.qiniu.com/docs/v6/api/reference/rs/batch.html
     */
    public function batch($operations)
    {
        $scheme = "http://";
        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $params = 'op=' . implode('&op=', $operations);
        $errResp = new Response(0, 0);
        if (count($operations) <= 0) {
            $errResp->error = 'empty operations';
            return array(null, new Error($scheme . '/batch', $errResp));
        }
        $bucket = '';
        foreach ($operations as $op) {
            $segments = explode('/', $op);
            if (count($segments) < 3) {
                continue;
            }
            list($bucket,) = \Qiniu\decodeEntry($segments[2]);
        }
        return $this->rsPost($bucket, '/batch', $params);
    }

    /**
     * 设置文件的生命周期
     *
     * @param string $bucket 设置文件生命周期文件所在的空间
     * @param string $key 设置文件生命周期文件的文件名
     * @param int $days 设置该文件多少天后删除，当$days设置为0时表示取消该文件的生命周期
     *
     * @return array
     * @link https://developer.qiniu.com/kodo/api/update-file-lifecycle
     */
    public function deleteAfterDays($bucket, $key, $days)
    {
        $entry = \Qiniu\entry($bucket, $key);
        $path = "/deleteAfterDays/$entry/$days";
        return $this->rsPost($bucket, $path);
    }

    /**
     * 更新 object 生命周期
     *
     * @param string $bucket 空间名
     * @param string $key 目标资源
     * @param int $to_line_after_days 多少天后将文件转为低频存储。
     *   -1 表示取消已设置的转低频存储的生命周期规则；
     *   0 表示不修改转低频生命周期规则。
     * @param int $to_archive_ir_after_days 多少天后转为归档直读存储。
     *   -1 表示取消已设置的转归档直读存储的生命周期规则；
     *   0 表示不修改转归档直读生命周期规则。
     * @param int $to_archive_after_days 多少天后将文件转为归档存储。
     *   -1 表示取消已设置的转归档存储的生命周期规则；
     *   0 表示不修改转归档生命周期规则。
     * @param int $to_deep_archive_after_days 多少天后将文件转为深度归档存储。
     *   -1 表示取消已设置的转深度归档存储的生命周期规则；
     *   0 表示不修改转深度归档生命周期规则。
     * @param int $delete_after_days 多少天后将文件删除。
     *   -1 表示取消已设置的删除存储的生命周期规则；
     *   0 表示不修改删除存储的生命周期规则。
     * @return array
     */
    public function setObjectLifecycle(
        $bucket,
        $key,
        $to_line_after_days = 0,
        $to_archive_after_days = 0,
        $to_deep_archive_after_days = 0,
        $delete_after_days = 0,
        $to_archive_ir_after_days = 0
    ) {
        return $this->setObjectLifecycleWithCond(
            $bucket,
            $key,
            null,
            $to_line_after_days,
            $to_archive_after_days,
            $to_deep_archive_after_days,
            $delete_after_days,
            $to_archive_ir_after_days
        );
    }

    /**
     * 更新 object 生命周期
     *
     * @param string $bucket 空间名
     * @param string $key 目标资源
     * @param int $to_line_after_days 多少天后将文件转为低频存储。
     *   设置为 -1 表示取消已设置的转低频存储的生命周期规则；
     *   0 表示不修改转低频生命周期规则。
     * @param int $to_archive_ir_after_days 多少天后将文件转为归档直读存储。
     *   设置为 -1 表示取消已设置的转归档直读存储的生命周期规则；
     *   0 表示不修改转归档直读生命周期规则。
     * @param int $to_archive_after_days 多少天后将文件转为归档存储。
     *   -1 表示取消已设置的转归档存储的生命周期规则；
     *   0 表示不修改转归档生命周期规则。
     * @param int $to_deep_archive_after_days 多少天后将文件转为深度归档存储。
     *   -1 表示取消已设置的转深度归档存储的生命周期规则；
     *   0 表示不修改转深度归档生命周期规则。
     * @param int $delete_after_days 多少天后将文件删除。
     *   -1 表示取消已设置的删除存储的生命周期规则；
     *   0 表示不修改删除存储的生命周期规则。
     * @param array<string, mixed> $cond 匹配条件，只有条件匹配才会设置成功。
     *   目前支持：hash、mime、fsize、putTime
     * @return array
     */
    public function setObjectLifecycleWithCond(
        $bucket,
        $key,
        $cond = null,
        $to_line_after_days = 0,
        $to_archive_after_days = 0,
        $to_deep_archive_after_days = 0,
        $delete_after_days = 0,
        $to_archive_ir_after_days = 0
    ) {
        $encodedEntry = \Qiniu\entry($bucket, $key);
        $path = '/lifecycle/' . $encodedEntry .
            '/toIAAfterDays/' . $to_line_after_days .
            '/toArchiveIRAfterDays/' . $to_archive_ir_after_days .
            '/toArchiveAfterDays/' . $to_archive_after_days .
            '/toDeepArchiveAfterDays/' . $to_deep_archive_after_days .
            '/deleteAfterDays/' . $delete_after_days;
        if ($cond != null) {
            $condStrArr = array();
            foreach ($cond as $key => $value) {
                array_push($condStrArr, $key . '=' . $value);
            }
            $condStr = implode('&', $condStrArr);
            $path .= '/cond' . \Qiniu\base64_urlSafeEncode($condStr);
        }
        return $this->rsPost($bucket, $path);
    }

    private function rsfGet($bucket, $path)
    {
        list($host, $err) = $this->config->getRsfHostV2(
            $this->auth->getAccessKey(),
            $bucket,
            $this->proxy->makeReqOpt()
        );

        if ($err != null) {
            return array(null, $err);
        }

        return $this->getV2($host . $path);
    }

    private function rsGet($bucket, $path)
    {
        list($host, $err) = $this->config->getRsHostV2(
            $this->auth->getAccessKey(),
            $bucket,
            $this->proxy->makeReqOpt()
        );

        if ($err != null) {
            return array(null, $err);
        }

        return $this->getV2($host . $path);
    }

    private function rsPost($bucket, $path, $body = null)
    {
        list($host, $err) = $this->config->getRsHostV2(
            $this->auth->getAccessKey(),
            $bucket,
            $this->proxy->makeReqOpt()
        );

        if ($err != null) {
            return array(null, $err);
        }

        return $this->postV2($host . $path, $body);
    }

    private function apiGet($bucket, $path)
    {
        list($host, $err) = $this->config->getApiHostV2(
            $this->auth->getAccessKey(),
            $bucket,
            $this->proxy->makeReqOpt()
        );

        if ($err != null) {
            return array(null, $err);
        }

        return $this->getV2($host . $path);
    }

    private function apiPost($bucket, $path, $body = null)
    {

        list($host, $err) = $this->config->getApiHostV2(
            $this->auth->getAccessKey(),
            $bucket,
            $this->proxy->makeReqOpt()
        );

        if ($err != null) {
            return array(null, $err);
        }

        return $this->postV2($host . $path, $body);
    }

    private function ucGet($path)
    {
        $url = $this->config->getUcHost() . $path;
        return $this->getV2($url);
    }

    private function ucPost($path, $body = null)
    {
        $url = $this->config->getUcHost() . $path;
        return $this->postV2($url, $body);
    }

    private function getV2($url)
    {
        $headers = $this->auth->authorizationV2($url, 'GET', null, 'application/x-www-form-urlencoded');
        $ret = Client::get($url, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function postV2($url, $body)
    {
        $headers = $this->auth->authorizationV2($url, 'POST', $body, 'application/x-www-form-urlencoded');
        $ret = Client::post($url, $body, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }

    public static function buildBatchCopy($source_bucket, $key_pairs, $target_bucket, $force)
    {
        return self::twoKeyBatch('/copy', $source_bucket, $key_pairs, $target_bucket, $force);
    }


    public static function buildBatchRename($bucket, $key_pairs, $force)
    {
        return self::buildBatchMove($bucket, $key_pairs, $bucket, $force);
    }


    public static function buildBatchMove($source_bucket, $key_pairs, $target_bucket, $force)
    {
        return self::twoKeyBatch('/move', $source_bucket, $key_pairs, $target_bucket, $force);
    }


    public static function buildBatchDelete($bucket, $keys)
    {
        return self::oneKeyBatch('/delete', $bucket, $keys);
    }


    public static function buildBatchStat($bucket, $keys)
    {
        return self::oneKeyBatch('/stat', $bucket, $keys);
    }

    public static function buildBatchDeleteAfterDays($bucket, $key_day_pairs)
    {
        $data = array();
        foreach ($key_day_pairs as $key => $day) {
            array_push($data, '/deleteAfterDays/' . \Qiniu\entry($bucket, $key) . '/' . $day);
        }
        return $data;
    }

    /**
     * @param string $bucket 空间名
     * @param array<string> $keys 目标资源
     * @param int $to_line_after_days 多少天后将文件转为低频存储。
     *   -1 表示取消已设置的转低频存储的生命周期规则；
     *   0 表示不修改转低频生命周期规则。
     * @param int $to_archive_ir_after_days 多少天后将文件转为归档直读。
     *    -1 表示取消已设置的转归档只读的生命周期规则；
     *    0 表示不修改转归档只读周期规则。
     * @param int $to_archive_after_days 多少天后将文件转为归档存储。
     *   -1 表示取消已设置的转归档存储的生命周期规则；
     *   0 表示不修改转归档生命周期规则。
     * @param int $to_deep_archive_after_days 多少天后将文件转为深度归档存储。
     *   -1 表示取消已设置的转深度归档存储的生命周期规则；
     *   0 表示不修改转深度归档生命周期规则。
     * @param int $delete_after_days 多少天后将文件删除。
     *   -1 表示取消已设置的删除存储的生命周期规则；
     *   0 表示不修改删除存储的生命周期规则。
     *
     * @retrun array<string>
     */
    public static function buildBatchSetObjectLifecycle(
        $bucket,
        $keys,
        $to_line_after_days,
        $to_archive_after_days,
        $to_deep_archive_after_days,
        $delete_after_days,
        $to_archive_ir_after_days = 0
    ) {
        $result = array();
        foreach ($keys as $key) {
            $encodedEntry = \Qiniu\entry($bucket, $key);
            $op = '/lifecycle/' . $encodedEntry .
                '/toIAAfterDays/' . $to_line_after_days .
                '/toArchiveIRAfterDays/' . $to_archive_ir_after_days .
                '/toArchiveAfterDays/' . $to_archive_after_days .
                '/toDeepArchiveAfterDays/' . $to_deep_archive_after_days .
                '/deleteAfterDays/' . $delete_after_days;
            array_push($result, $op);
        }
        return $result;
    }

    public static function buildBatchChangeMime($bucket, $key_mime_pairs)
    {
        $data = array();
        foreach ($key_mime_pairs as $key => $mime) {
            array_push($data, '/chgm/' . \Qiniu\entry($bucket, $key) . '/mime/' . base64_encode($mime));
        }
        return $data;
    }

    public static function buildBatchChangeType($bucket, $key_type_pairs)
    {
        $data = array();
        foreach ($key_type_pairs as $key => $type) {
            array_push($data, '/chtype/' . \Qiniu\entry($bucket, $key) . '/type/' . $type);
        }
        return $data;
    }

    public static function buildBatchRestoreAr($bucket, $key_restore_days_pairs)
    {
        $data = array();
        foreach ($key_restore_days_pairs as $key => $restore_days) {
            array_push($data, '/restoreAr/' . \Qiniu\entry($bucket, $key) . '/freezeAfterDays/' . $restore_days);
        }
        return $data;
    }

    private static function oneKeyBatch($operation, $bucket, $keys)
    {
        $data = array();
        foreach ($keys as $key) {
            array_push($data, $operation . '/' . \Qiniu\entry($bucket, $key));
        }
        return $data;
    }

    private static function twoKeyBatch($operation, $source_bucket, $key_pairs, $target_bucket, $force)
    {
        if ($target_bucket === null) {
            $target_bucket = $source_bucket;
        }
        $data = array();
        $forceOp = "false";
        if ($force) {
            $forceOp = "true";
        }
        foreach ($key_pairs as $from_key => $to_key) {
            $from = \Qiniu\entry($source_bucket, $from_key);
            $to = \Qiniu\entry($target_bucket, $to_key);
            array_push($data, $operation . '/' . $from . '/' . $to . "/force/" . $forceOp);
        }
        return $data;
    }
}
