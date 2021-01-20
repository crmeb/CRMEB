<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\serve\storage;


use crmeb\basic\BaseStorage;
use crmeb\services\AccessTokenServeService;
use think\exception\ValidateException;

/**
 * Class Crmeb
 * @package crmeb\services\serve\storage
 */
class Crmeb extends BaseStorage
{

    protected $accessToken;

    /**
     * Crmeb constructor.
     * @param string $name
     * @param AccessTokenServeService $service
     * @param string|null $configFile
     */
    public function __construct(string $name, AccessTokenServeService $service, string $configFile = null)
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
        return $this->accessToken->httpRequest('user/info');
    }

    /**
     * 用户登录
     * @param string $account
     * @param string $secret
     * @return array|mixed
     */
    public function login(string $account, string $secret)
    {
        return $this->accessToken->httpRequest('user/login', ['account' => $account, 'secret' => $secret], 'POST', false);
    }

    /**
     * 注册平台
     * @param array $data
     * @return array|mixed
     */
    public function register(array $data)
    {
        return $this->accessToken->httpRequest('user/register', $data, 'POST', false);
    }

    /**
     * 平台用户消息记录
     * @param int $page
     * @param int $limit
     * @return array|mixed
     */
    public function userBill(int $page, int $limit)
    {
        return $this->accessToken->httpRequest('user/bill', ['page' => $page, 'limit' => $limit]);
    }

    /**
     * 找回账号
     * @param array $data
     * @return array|mixed
     */
    public function forget(array $data)
    {
        return $this->accessToken->httpRequest('user/forget', $data, 'POST', false);
    }

    /**
     * 修改密码
     * @param array $data
     * @return array|mixed
     */
    public function modify(array $data)
    {
        return $this->accessToken->httpRequest('user/modify', $data, 'POST', false);
    }

    /**
     * 获取验证码
     * @param string $phone
     * @return array|mixed
     */
    public function code(string $phone)
    {
        return $this->accessToken->httpRequest('user/code', ['phone' => $phone], 'POST', false);
    }

    /**
     * 套餐列表
     * @param string $type 套餐类型：sms,短信；query,物流查询；dump,电子面单；copy,产品复制
     * @return array|mixed
     */
    public function mealList(string $type)
    {
        return $this->accessToken->httpRequest('meal/list', ['type' => $type]);
    }

    /**
     * 套餐支付
     * @param array $data
     * @return array|mixed
     */
    public function payMeal(array $data)
    {
        return $this->accessToken->httpRequest('meal/code', $data);
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
            throw new ValidateException('参数类型不正确');
        }
        $data = ['page' => $page, 'limit' => $limit, 'type' => $typeContent[$type]];
        if ($type == 1 && $status != '') {
            $data['status'] = $status;
        }
        return $this->accessToken->httpRequest('user/record', $data);
    }

    /**
     * 修改手机号
     * @param array $data
     * @return array|mixed
     */
    public function modifyPhone(array $data)
    {
        return $this->accessToken->httpRequest('user/modify/phone', $data);
    }
}
