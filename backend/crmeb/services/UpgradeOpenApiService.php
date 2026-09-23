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

namespace crmeb\services;

/**
 * 升级服务
 * Class UpgradeOpenApiService
 */
class UpgradeOpenApiService
{
    const BASE_URL = 'http://shop.crmeb.net/';

    /**
     * 系统提供的appid，不要修改
     * @var string
     */
    protected $appid = 'ze7x9rxsv09l6pvsyo';

    /**
     * @var array
     */
    protected $baseHeader = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->baseHeader = [
            'X-APP-ID'       => $this->appid,
            'System-Version' => get_crmeb_version(),
            'Auth-Host'      => request()->host(),
            'Auth-Label'     => 19
        ];
    }

    /**
     * 请求接口
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $header
     * @return array|mixed
     * @throws \Exception
     */
    public function client(string $url, string $method = 'POST', array $data = [], array $header = [])
    {
        $baseHeader = array_merge($this->baseHeader, $header);
        if ($baseHeader) {
            $header = [];
            foreach ($baseHeader as $key => $value) {
                $header[] = $key . ':' . $value;
            }
        }

        $res = Http::request(self::BASE_URL . $url, $method, $data, $header);

        $res = json_decode($res, true);
        if (json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }

        if (isset($res['status']) && $res['status'] != 200) {
            throw new \Exception($res['msg']);
        }

        return $res['data'] ?? [];
    }

    /**
     * 获取升级列表
     * @param int $page
     * @param int $limit
     * @return array|mixed
     * @throws \Exception
     */
    public function getUpgradeVersionList(int $page = 1, int $limit = 10)
    {
        return $this->client('api/open/version_list', 'GET', ['page' => $page, 'limit' => $limit]);
    }

    /**
     * 保存升级日志
     * @param array $data
     * @return array|mixed
     * @throws \Exception
     */
    public function saveUpgradeLog(array $data)
    {
        return $this->client('api/open/upgrade_log', 'POST', $data);
    }

    /**
     * 获取指定升级详情
     * @param int $id
     * @return array|mixed
     * @throws \Exception
     */
    public function getUpgradeVersionInfo(int $id)
    {
        return $this->client('api/open/version_info', 'GET', ['id' => $id]);
    }

    /**
     * 获取当前版本后可以升级数量
     * @return array|mixed
     * @throws \Exception
     */
    public function getUpgradeNewVersionCount()
    {
        return $this->client('api/open/new_version_count', 'GET');
    }

    /**
     * 获取当前域名的升级权限
     * @return array|mixed
     * @throws \Exception
     */
    public function getUpgradeVersionAuth()
    {
        return $this->client('api/open/get_version_auth', 'GET');
    }

    /**
     * 检查远程文件是否存在,并进行下载
     * @param $url
     * @param $savefile
     * @return false|mixed
     */
    public function checkRemoteFileExists(string $url, string $savefile)
    {
        //阿里云oss地址检测不到直接进行下载
        if (strpos($url, 'aliyuncs.com') !== false || strpos($url, 'oss.') !== false) {
            return File::downRemoteFile($url, $savefile);
        }

        $url = str_replace('\\', '/', $url);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        // 发送请求
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败
        if ($result !== false) {
            // 再检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                curl_close($curl);
                $zip = File::downRemoteFile($url, $savefile);
                if ($zip['error'] > 0)
                    return false;
                if (!isset($zip['save_path']) && empty($zip['save_path']))
                    return false;
                if (!file_exists($zip['save_path']))
                    return false;
                return $zip['save_path'];
            }
        }
        curl_close($curl);
        return $found;
    }
}