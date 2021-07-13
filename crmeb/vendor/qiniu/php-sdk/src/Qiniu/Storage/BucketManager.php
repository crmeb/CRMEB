<?php
namespace Qiniu\Storage;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Zone;
use Qiniu\Http\Client;
use Qiniu\Http\Error;

/**
 * 主要涉及了空间资源管理及批量操作接口的实现，具体的接口规格可以参考
 *
 * @link https://developer.qiniu.com/kodo/api/1274/rs
 */
final class BucketManager
{
    private $auth;
    private $config;

    public function __construct(Auth $auth, Config $config = null)
    {
        $this->auth = $auth;
        if ($config == null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }
    }

    /**
     * 获取指定账号下所有的空间名。
     *
     * @return string[] 包含所有空间名
     */
    public function buckets($shared = true)
    {
        $includeShared = "false";
        if ($shared === true) {
            $includeShared = "true";
        }
        return $this->rsGet('/buckets?shared=' . $includeShared);
    }

    /**
     * 列举空间，返回bucket列表
     * region 指定区域，global 指定全局空间。
     * 在指定了 region 参数时，
     * 如果指定 global 为 true，那么忽略 region 参数指定的区域，返回所有区域的全局空间。
     * 如果没有指定 global 为 true，那么返回指定区域中非全局空间。
     * 在没有指定 region 参数时（包括指定为空""），
     * 如果指定 global 为 true，那么返回所有区域的全局空间。
     * 如果没有指定 global 为 true，那么返回指定区域中所有的空间，包括全局空间。
     * 在指定了line为 true 时，只返回 Line 空间；否则，只返回非 Line 空间。
     * share 参数用于指定共享空间。
     */

    public function listbuckets(
        $region = null,
        $line = 'false',
        $shared = 'false'
    ) {
        $path = '/v3/buckets?region=' . $region . '&line=' . $line . '&shared=' . $share;
        $info = $this->ucPost($path);
        return $info;
    }

    /**
     * 创建空间
     *
     * @param $name     创建的空间名
     * @param $region    创建的区域，默认华东
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function createBucket($name, $region = 'z0')
    {
        $path = '/mkbucketv2/'.$name.'/region/' . $region;
        return $this->rsPost($path, null);
    }

    /**
     * 删除空间
     *
     * @param $name     删除的空间名
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function deleteBucket($name)
    {
        $path = '/drop/'.$name;
        return $this->rsPost($path, null);
    }

    /**
     * 获取指定空间绑定的所有的域名
     *
     * @return string[] 包含所有空间域名
     */
    public function domains($bucket)
    {
        return $this->apiGet('/v6/domain/list?tbl=' . $bucket);
    }

    /**
     * 获取指定空间的相关信息
     *
     * @return string[] 包含空间信息
     */
    public function bucketInfo($bucket)
    {
        $path = '/v2/bucketInfo?bucket=' . $bucket;
        $info = $this->ucPost($path);
        return $info;
    }

    /**
     * 获取指定zone的空间信息列表
     * 在Region 未指定且Global 不为 true 时(包含未指定的情况,下同)，返回用户的所有空间。
     * 在指定了 region 参数且 global 不为 true 时，只列举非全局空间。
     * shared 不指定shared参数或指定shared为rw或false时，返回包含具有读写权限空间，
     * 指定shared为rd或true时，返回包含具有读权限空间。
     * fs：如果为 true，会返回每个空间当前的文件数和存储量（实时数据）。
     * @return string[] 包含空间信息
     */
    public function bucketInfos($region = null, $shared = 'false', $fs = 'false')
    {
        $path = '/v2/bucketInfos?region=' . $region . '&shared=' . $shared . '&fs=' . $fs;
        $info = $this->ucPost($path);
        return $info;
    }

    /**
     * 获取空间绑定的域名列表
     * @return string[] 包含空间绑定的所有域名
     */

    /**
     * 列取空间的文件列表
     *
     * @param $bucket     空间名
     * @param $prefix     列举前缀
     * @param $marker     列举标识符
     * @param $limit      单次列举个数限制
     * @param $delimiter  指定目录分隔符
     *
     * @return array    包含文件信息的数组，类似：[
*                                              {
*                                                 "hash" => "<Hash string>",
*                                                  "key" => "<Key string>",
*                                                  "fsize" => "<file size>",
*                                                  "putTime" => "<file modify time>"
*                                              },
*                                              ...
*                                            ]
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/list.html
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
        $url = $this->getRsfHost() . '/list?' . http_build_query($query);
        return $this->get($url);
    }

    /**
     * 列取空间的文件列表
     *
     * @param $bucket     空间名
     * @param $prefix     列举前缀
     * @param $marker     列举标识符
     * @param $limit      单次列举个数限制
     * @param $delimiter  指定目录分隔符
     * @param $skipconfirm  是否跳过已删除条目的确认机制
     *
     * @return array    包含文件信息的数组，类似：[
*                                              {
*                                                 "hash" => "<Hash string>",
*                                                  "key" => "<Key string>",
*                                                  "fsize" => "<file size>",
*                                                  "putTime" => "<file modify time>"
*                                              },
*                                              ...
*                                            ]
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
        $url = $this->getRsfHost() . $path;
        $headers = $this->auth->authorization($url, null, 'application/x-www-form-urlencoded');
        $ret = Client::post($url, null, $headers);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = explode("\n", $ret->body);
        $pop = array_pop($r);
        return array($r, null);
    }

    /**
     * 设置Referer防盗链
     *
     * @param $bucket     空间名
     * @param $mode     0: 表示关闭Referer(使用此选项将会忽略以下参数并将恢复默认值);
     * 1: 表示设置Referer白名单; 2:表示设置Referer黑名单
     * @param $norefer     0: 表示不允许空 Refer 访问; 1: 表示允许空 Refer 访问
     * @param $pattern      规则字符串, 当前允许格式分为三种: 一种为空主机头域名,
     * 比如 foo.com; 一种是泛域名,比如 *.bar.com; 一种是完全通配符,
     * 即一个 *; 多个规则之间用;隔开, 比如: foo.com;*.bar.com;sub.foo.com;*.sub.bar.com
     * @param $source_enabled  源站是否支持，默认为0只给CDN配置, 设置为1表示开启源站防盗链
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    // public function referAntiLeech(){

    // }

    /**
     * 增加bucket生命规则
     *
     * @param $bucket     空间名
     * @param $name     规则名称 bucket 内唯一，长度小于50，不能为空，只能为
     * 字母、数字、下划线
     * @param $prefix     同一个 bucket 里面前缀不能重复
     * @param $delete_after_days      指定上传文件多少天后删除，指定为0表示不删除,
     * 大于0表示多少天后删除,需大于 to_line_after_days
     * @param $to_line_after_days  指定文件上传多少天后转低频存储。指定为0表示
     * 不转低频存储，小于0表示上传的文件立即变低频存储
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function bucketLifecycleRule(
        $bucket,
        $name,
        $prefix,
        $delete_after_days,
        $to_line_after_days
    ) {
        $path = '/rules/add';
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
        $data = http_build_query($params);
        $info = $this->ucPost($path, $data);
        return $info;
    }

    /**
     * 更新bucket生命规则
     *
     * @param $bucket     空间名
     * @param $name     规则名称 bucket 内唯一，长度小于50，不能为空，只能为字母、
     * 数字、下划线
     * @param $prefix     同一个 bucket 里面前缀不能重复
     * @param $delete_after_days      指定上传文件多少天后删除，指定为0表示不删除，
     * 大于0表示多少天后删除，需大于 to_line_after_days
     * @param $to_line_after_days  指定文件上传多少天后转低频存储。指定为0表示不
     * 转低频存储，小于0表示上传的文件立即变低频存储
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function updateBucketLifecycleRule(
        $bucket,
        $name,
        $prefix,
        $delete_after_days,
        $to_line_after_days
    ) {
        $path = '/rules/update';
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
        $data = http_build_query($params);
        $info = $this->ucPost($path, $data);
        return $info;
    }

    /**
     * 获取bucket生命规则
     *
     * @param $bucket     空间名
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
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
     * @param $bucket     空间名
     * @param $name     规则名称 bucket 内唯一，长度小于50，不能为空，
     * 只能为字母、数字、下划线（）
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function deleteBucketLifecycleRule($bucket, $name)
    {
        $path = '/rules/delete';
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
     * @param $bucket     空间名
     * @param $name     规则名称 bucket 内唯一，长度小于50，不能为空，
     * 只能为字母、数字、下划线()
     * @param $prefix     同一个 bucket 里面前缀不能重复
     * @param $suffix      可选，文件配置的后缀
     * @param $event  事件类型，可以指定多个，包括 put,mkfile,delete,copy,move,append,
     * disable,enable,deleteMarkerCreate
     * @param $callbackURL 通知URL，可以指定多个，失败依次重试
     * @param $access_key 可选，设置的话会对通知请求用对应的ak、sk进行签名
     * @param $host 可选，通知请求的host
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
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
        if ($bucket) {
            $params['bucket'] = $bucket;
        }
        if ($name) {
            $params['name'] = $name;
        }
        if ($prefix) {
            $params['prefix'] = $prefix;
        }
        if ($suffix) {
            $params['suffix'] = $suffix;
        }
        if ($callbackURL) {
            $params['callbackURL'] = $callbackURL;
        }
        if ($access_key) {
            $params['access_key'] = $access_key;
        }
        if ($host) {
            $params['host'] = $host;
        }
        $data = http_build_query($params);
        if ($event) {
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
     * @param $bucket     空间名
     * @param $name     规则名称 bucket 内唯一，长度小于50，不能为空，
     * 只能为字母、数字、下划线()
     * @param $prefix     同一个 bucket 里面前缀不能重复
     * @param $suffix      可选，文件配置的后缀
     * @param $event  事件类型，可以指定多个，包括 put,mkfile,delete,copy,move,append,disable,
     * enable,deleteMarkerCreate
     * @param $callbackURL 通知URL，可以指定多个，失败依次重试
     * @param $access_key 可选，设置的话会对通知请求用对应的ak、sk进行签名
     * @param $host 可选，通知请求的host
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
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
        if ($bucket) {
            $params['bucket'] = $bucket;
        }
        if ($name) {
            $params['name'] = $name;
        }
        if ($prefix) {
            $params['prefix'] = $prefix;
        }
        if ($suffix) {
            $params['suffix'] = $suffix;
        }
        if ($event) {
            $params['event'] = $event;
        }
        if ($callbackURL) {
            $params['callbackURL'] = $callbackURL;
        }
        if ($access_key) {
            $params['access_key'] = $access_key;
        }
        if ($host) {
            $params['host'] = $host;
        }
        $data = http_build_query($params);
        $info = $this->ucPost($path, $data);
        return $info;
    }

    /**
     * 获取bucket事件通知规则
     *
     * @param $bucket     空间名
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function getBucketEvents($bucket)
    {
        $path = '/events/get?bucket=' . $bucket;
        $info = $this->ucGet($path);
        return $info;
    }

    /**
     * 删除bucket事件通知规则
     *
     * @param $bucket     空间名
     * @param $name     规则名称 bucket 内唯一，长度小于50，不能为空，
     * 只能为字母、数字、下划线
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function deleteBucketEvent($bucket, $name)
    {
        $path = '/events/delete';
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
     * 设置bucket的跨域信息，最多允许设置10条跨域规则。
     * 对于同一个域名如果设置了多条规则，那么按顺序使用第一条匹配的规则去生成返回值。
     * 对于简单跨域请求，只匹配 Origin；
     * allowed_orgin: 允许的域名。必填；支持通配符*；*表示全部匹配；只有第一个*生效；
     * 需要设置"Scheme"；大小写敏感。例如
     * 规则：http://*.abc.*.com 请求："http://test.abc.test.com" 结果：不通过
     * 规则："http://abc.com" 请求："https://abc.com"/"abc.com" 结果：不通过
     * 规则："abc.com" 请求："http://abc.com" 结果：不通过
     * allowed_method: 允许的方法。必填；不支持通配符；大小写不敏感；
     * allowed_header: 允许的header。选填；支持通配符*，
     * 但只能是单独的*，表示允许全部header，其他*不生效；
     * 空则不允许任何header；大小写不敏感；
     * exposed_header: 暴露的header。选填；不支持通配符；
     * X-Log, X-Reqid是默认会暴露的两个header；
     * 其他的header如果没有设置，则不会暴露；大小写不敏感；
     * max_age: 结果可以缓存的时间。选填；空则不缓存；
     * allowed_credentials：该配置不支持设置，默认为true。
     * 备注：如果没有设置任何corsRules，那么默认允许所有的跨域请求
     */
    // public function putCorsRules(string $bucket, array $params)
    // {
    //     $path = '/corsRules/set/' . $bucket;
    //     $data = json_encode($params);
    //     $info = $this->ucPost($path, $data);
    //     return $info;
    // }

    /**
     * 获取bucket的跨域信息
     * $bucket 空间名
     */
    public function getCorsRules($bucket)
    {
        $path = '/corsRules/get/' . $bucket;
        $info = $this->ucGet($path);
        return $info;
    }

    /**
     * 设置回源规则
     * 使用该API设置源站优先级高于/image设置的源站，即IO优先读取source接口设置的源站配置,
     * 如果存在会忽略/image设置的源站
     * Bucket 空间名
     * Host(可选)回源Host
     * RetryCodes(可选),镜像回源时源站返回Code可以重试，最多指定3个，当前只支持4xx错误码重试
     * SourceQiniuAK,SourceQiniuSK(可选)如果存在将在回源时对URL进行签名，客户源站可以验证
     * 以保证请求来自Qiniu服务器
     * Expires(可选) 签名过期时间，如果不设置默认为1小时
     * Addr 回源地址，不可重复。
     * Weight 权重,范围限制1-100,不填默认为1,回源时会根据所有源的权重值进行源站选择,
     * 主备源会分开计算.
     * Backup 是否备用回源,回源优先尝试主源
     */
    // public function putBucktSourceConfig(array $params)
    // {
    //     $path = '/mirrorConfig/set';
    //     $data = json_encode($params);
    //     $info = $this->ucPostV2($path, $data);
    //     return $info;
    // }

    /**
     * 获取空间回源配置
     */
    public function getBucktSourceConfig(array $params)
    {
        $path = '/mirrorConfig/get';
        $data = json_encode($params);
        $info = $this->ucPostV2($path, $data);
        return $info;
    }

    /**
     * 开关原图保护
     * mode 为1表示开启原图保护，0表示关闭
     */
    public function putBucketAccessStyleMode($bucket, $mode)
    {
        $path = '/accessMode/' . $bucket . '/mode/' . $mode;
        $info = $this->ucPost($path, null);
        return $info;
    }

    /**
     * 设置私有属性
     * private为0表示公开，为1表示私有
     */
    public function putBucketAccessMode($bucket, $private)
    {
        $path = '/bucket/' . $bucket . '/private/' . $private;
        $info = $this->ucPost($path, null);
        return $info;
    }

    /**
     * 设置referer防盗链
     * bucket=<BucketName>: bucket 名
     * mode=<AntiLeechMode>:
     * 0: 表示关闭Referer(使用此选项将会忽略以下参数并将恢复默认值);
     * 1: 表示设置Referer白名单; 2: 表示设置Referer黑名单
     * norefer=<NoRefer>: 0: 表示不允许空 Refer 访问;
     * 1: 表示允许空 Refer 访问
     * pattern=<Pattern>: 规则字符串, 当前允许格式分为三种:
     * 一种为空主机头域名, 比如 foo.com;
     * 一种是泛域名, 比如 *.bar.com; 一种是完全通配符, 即一个 *;
     * 多个规则之间用;隔开, 比如: foo.com;*.bar.com;sub.foo.com;*.sub.bar.com
     * 空主机头域名可以是多级域名，比如 foo.bar.com。
     * 多个域名之间不允许夹带空白字符。
     * source_enabled=:1
     */
    public function putReferAntiLeech($bucket, $mode, $norefer, $pattern, $enabled = 1)
    {
        $path = "/referAntiLeech?bucket=$bucket&mode=$mode&norefer=$norefer&pattern=$pattern&source_enabled=$enabled";
        $info = $this->ucPost($path, null);
        return $info;
    }

    /**
     * 设置Bucket的maxAge
     * maxAge为0或者负数表示为默认值（31536000）
     */
    public function putBucketMaxAge($bucket, $maxAge)
    {
        $path = '/maxAge?bucket=' . $bucket . '&maxAge=' . $maxAge;
        $info = $this->ucPost($path, null);
        return $info;
    }

    /**
     * 设置配额
     * <bucket>: 空间名称，不支持授权空间
     * <size>: 空间存储量配额,参数传入0或不传表示不更改当前配置，传入-1表示取消限额，
     * 新创建的空间默认没有限额。
     * <count>: 空间文件数配额，参数含义同<size>
     */
    public function putBucketQuota($bucket, $size, $count)
    {
        $path = '/setbucketquota/' . $bucket . '/size/' . $size . '/count/' . $count;
        $info = $this->apiPost($path, null);
        return $info;
    }

    /**
     * 获取配额
     * bucket 空间名称
     */
    public function getBucketQuota($bucket)
    {
        $path = '/getbucketquota/' . $bucket;
        $info = $this->apiPost($path, null);
        return $info;
    }

    /**
     * 获取资源的元信息，但不返回文件内容
     *
     * @param $bucket     待获取信息资源所在的空间
     * @param $key        待获取资源的文件名
     *
     * @return array    包含文件信息的数组，类似：
*                                              [
*                                                  "hash" => "<Hash string>",
*                                                  "key" => "<Key string>",
*                                                  "fsize" => <file size>,
*                                                  "putTime" => "<file modify time>"
*                                                  "fileType" => <file type>
*                                              ]
     *
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/stat.html
     */
    public function stat($bucket, $key)
    {
        $path = '/stat/' . \Qiniu\entry($bucket, $key);
        return $this->rsGet($path);
    }

    /**
     * 删除指定资源
     *
     * @param $bucket     待删除资源所在的空间
     * @param $key        待删除资源的文件名
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/delete.html
     */
    public function delete($bucket, $key)
    {
        $path = '/delete/' . \Qiniu\entry($bucket, $key);
        list(, $error) = $this->rsPost($path);
        return $error;
    }


    /**
     * 给资源进行重命名，本质为move操作。
     *
     * @param $bucket     待操作资源所在空间
     * @param $oldname    待操作资源文件名
     * @param $newname    目标资源文件名
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     */
    public function rename($bucket, $oldname, $newname)
    {
        return $this->move($bucket, $oldname, $bucket, $newname);
    }

    /**
     * 对资源进行复制。
     *
     * @param $from_bucket     待操作资源所在空间
     * @param $from_key        待操作资源文件名
     * @param $to_bucket       目标资源空间名
     * @param $to_key          目标资源文件名
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/copy.html
     */
    public function copy($from_bucket, $from_key, $to_bucket, $to_key, $force = false)
    {
        $from = \Qiniu\entry($from_bucket, $from_key);
        $to = \Qiniu\entry($to_bucket, $to_key);
        $path = '/copy/' . $from . '/' . $to;
        if ($force === true) {
            $path .= '/force/true';
        }
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 将资源从一个空间到另一个空间
     *
     * @param $from_bucket     待操作资源所在空间
     * @param $from_key        待操作资源文件名
     * @param $to_bucket       目标资源空间名
     * @param $to_key          目标资源文件名
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/move.html
     */
    public function move($from_bucket, $from_key, $to_bucket, $to_key, $force = false)
    {
        $from = \Qiniu\entry($from_bucket, $from_key);
        $to = \Qiniu\entry($to_bucket, $to_key);
        $path = '/move/' . $from . '/' . $to;
        if ($force) {
            $path .= '/force/true';
        }
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 主动修改指定资源的文件元信息
     *
     * @param $bucket     待操作资源所在空间
     * @param $key        待操作资源文件名
     * @param $mime       待操作文件目标mimeType
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/chgm.html
     */
    public function changeMime($bucket, $key, $mime)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $encode_mime = \Qiniu\base64_urlSafeEncode($mime);
        $path = '/chgm/' . $resource . '/mime/' . $encode_mime;
        list(, $error) = $this->rsPost($path);
        return $error;
    }


    /**
     * 修改指定资源的存储类型
     *
     * @param $bucket     待操作资源所在空间
     * @param $key        待操作资源文件名
     * @param $fileType       待操作文件目标文件类型
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  https://developer.qiniu.com/kodo/api/3710/modify-the-file-type
     */
    public function changeType($bucket, $key, $fileType)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/chtype/' . $resource . '/type/' . $fileType;
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 修改文件的存储状态，即禁用状态和启用状态间的的互相转换
     *
     * @param $bucket     待操作资源所在空间
     * @param $key        待操作资源文件名
     * @param $status       待操作文件目标文件类型
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  https://developer.qiniu.com/kodo/api/4173/modify-the-file-status
     */
    public function changeStatus($bucket, $key, $status)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/chstatus/' . $resource . '/status/' . $status;
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 从指定URL抓取资源，并将该资源存储到指定空间中
     *
     * @param $url        指定的URL
     * @param $bucket     目标资源空间
     * @param $key        目标资源文件名
     *
     * @return array    包含已拉取的文件信息。
     *                         成功时：  [
     *                                          [
     *                                              "hash" => "<Hash string>",
     *                                              "key" => "<Key string>"
     *                                          ],
     *                                          null
     *                                  ]
     *
     *                         失败时：  [
     *                                          null,
     *                                         Qiniu/Http/Error
     *                                  ]
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/fetch.html
     */
    public function fetch($url, $bucket, $key = null)
    {

        $resource = \Qiniu\base64_urlSafeEncode($url);
        $to = \Qiniu\entry($bucket, $key);
        $path = '/fetch/' . $resource . '/to/' . $to;

        $ak = $this->auth->getAccessKey();
        $ioHost = $this->config->getIovipHost($ak, $bucket);

        $url = $ioHost . $path;
        return $this->post($url, null);
    }

    /**
     * 从镜像源站抓取资源到空间中，如果空间中已经存在，则覆盖该资源
     *
     * @param $bucket     待获取资源所在的空间
     * @param $key        代获取资源文件名
     *
     * @return mixed      成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/prefetch.html
     */
    public function prefetch($bucket, $key)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/prefetch/' . $resource;

        $ak = $this->auth->getAccessKey();
        $ioHost = $this->config->getIovipHost($ak, $bucket);

        $url = $ioHost . $path;
        list(, $error) = $this->post($url, null);
        return $error;
    }

    /**
     * 在单次请求中进行多个资源管理操作
     *
     * @param $operations     资源管理操作数组
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
        $params = 'op=' . implode('&op=', $operations);
        return $this->rsPost('/batch', $params);
    }

    /**
     * 设置文件的生命周期
     *
     * @param $bucket 设置文件生命周期文件所在的空间
     * @param $key    设置文件生命周期文件的文件名
     * @param $days   设置该文件多少天后删除，当$days设置为0时表示取消该文件的生命周期
     *
     * @return Mixed
     * @link https://developer.qiniu.com/kodo/api/update-file-lifecycle
     */
    public function deleteAfterDays($bucket, $key, $days)
    {
        $entry = \Qiniu\entry($bucket, $key);
        $path = "/deleteAfterDays/$entry/$days";
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    private function getRsfHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::RSF_HOST;
    }

    private function getRsHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::RS_HOST;
    }

    private function getApiHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::API_HOST;
    }

    private function getUcHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::UC_HOST;
    }

    private function rsPost($path, $body = null)
    {
        $url = $this->getRsHost() . $path;
        return $this->post($url, $body);
    }

    private function apiPost($path, $body = null)
    {
        $url = $this->getApiHost() . $path;
        return $this->post($url, $body);
    }

    private function ucPost($path, $body = null)
    {
        $url = $this->getUcHost() . $path;
        return $this->post($url, $body);
    }

    private function ucGet($path)
    {
        $url = $this->getUcHost() . $path;
        return $this->get($url);
    }

    private function apiGet($path)
    {
        $url = $this->getApiHost() . $path;
        return $this->get($url);
    }

    private function rsGet($path)
    {
        $url = $this->getRsHost() . $path;
        return $this->get($url);
    }

    private function get($url)
    {
        $headers = $this->auth->authorization($url);
        $ret = Client::get($url, $headers);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function post($url, $body)
    {
        $headers = $this->auth->authorization($url, $body, 'application/x-www-form-urlencoded');
        $ret = Client::post($url, $body, $headers);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }

    private function ucPostV2($path, $body)
    {
        $url = $this->getUcHost() . $path;
        return $this->postV2($url, $body);
    }

    private function postV2($url, $body)
    {
        $headers = $this->auth->authorizationV2($url, 'POST', $body, 'application/json');
        $headers["Content-Type"] = 'application/json';
        $ret = Client::post($url, $body, $headers);
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
