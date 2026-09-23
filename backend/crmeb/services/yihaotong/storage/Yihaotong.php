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

namespace crmeb\services\yihaotong\storage;


use crmeb\basic\BaseStorage;
use crmeb\exceptions\AdminException;
use crmeb\services\yihaotong\AccessTokenServeService;

/**
 * Class Yihaotong
 * @package crmeb\services\yihaotong\storage
 */
class Yihaotong extends BaseStorage
{
    protected $accessToken;

    /**
     * Yihaotong constructor.
     * @param string $name
     * @param AccessTokenServeService $service
     * @param string|null $configFile
     */
    public function __construct(string $name, AccessTokenServeService $service, ?string $configFile = null)
    {
        $this->accessToken = $service;
    }

    protected function initialize(array $config)
    {
        // TODO: Implement initialize() method.
    }

    /**
     * 获取用户信息
     * @param string $account
     * @param string $secret
     * @return array|mixed
     */
    public function getUser()
    {
        return $this->accessToken->httpRequest('v2/user/info');
    }

    /**
     * 用量记录
     * @param int $page
     * @param int $limit
     * @param int $type
     * @return array|mixed
     */
    public function record(int $page, int $limit, int $type, $status = '')
    {
        $typeContent = [1 => 'sms', 2 => 'expr_dump', 3 => 'expr_query', 4 => 'copy'];
        if (!isset($typeContent[$type])) {
            throw new AdminException('参数错误');
        }
        $data = ['page' => $page, 'limit' => $limit, 'type' => $typeContent[$type]];
        if ($type == 1 && $status !== '' && $status !== null) {
            $data['status'] = $status;
        }
        return $this->accessToken->httpRequest('user/record', $data);
    }

    /**
     * 发送验证码
     * @param string $phone
     * @return mixed
     */
    public function code(string $phone)
    {
        return $this->accessToken->httpRequest('user/code', ['phone' => $phone]);
    }

    /**
     * 验证验证码
     * @param string $phone
     * @param string $verify_code
     * @return mixed
     */
    public function checkCode(string $phone, string $verify_code)
    {
        return $this->accessToken->httpRequest('user/checkCode', ['phone' => $phone, 'verify_code' => $verify_code]);
    }

    /**
     * 注册
     * @param array $data
     * @return mixed
     */
    public function register(array $data)
    {
        return $this->accessToken->httpRequest('user/register', $data);
    }

    /**
     * 登录
     * @param string $account
     * @param string $password
     * @return mixed
     */
    public function login(string $account, string $password)
    {
        return $this->accessToken->httpRequest('v2/user/login', ['access_key' => $account, 'secret_key' => $password]);
    }
}
