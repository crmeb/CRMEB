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

namespace driver\logistics\Yihaotong;

use crmeb\exceptions\ApiException;
use crmeb\services\logistics\BaseExpress;
use crmeb\services\yihaotong\AccessTokenServeService;
use crmeb\exceptions\AdminException;
use think\facade\Db;

/**
 * Class Driver
 * @package driver\logistics\Yihaotong
 */
class Driver extends BaseExpress
{
    /**
     * 驱动初始化时传入的配置。
     * 一号通正式配置通常来自系统配置表，保留传入值可支持命令行/测试等无应用上下文场景。
     * @var array
     */
    protected array $driverConfig = [];

    const EXPRESS_OPEN = 'v2/expr/open';
    const EXPRESS_TEMP = 'v2/expr_dump/temp';
    const EXPRESS_LIST = 'v2/expr/express';
    const EXPRESS_QUERY = 'v2/expr/query';
    const EXPRESS_DUMP = 'v2/expr/dump';
    const SHIPMENT_KUAIDI_NUMS = 'v2/shipment/get_kuaidi_coms';
    const SHIPMENT_CREATE_ORDER = 'v2/shipment/create_order';
    const SHIPMENT_CANCEL_ORDER = 'v2/shipment/cancel_order';
    const SHIPMENT_INDEX = 'v2/shipment/index';
    const SHIPMENT_PRICE = 'v2/shipment/price';

    /**
     * 初始化
     * 从系统配置读取一号通账号信息，覆盖父类传入的空 AccessTokenServeService
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config = [])
    {
        $this->driverConfig = $config;
        // 一号通驱动需要从系统配置中读取 appid / appsecret，而非从 Express 配置文件
        // 因为 Express::invokeClass() 按 account/secret key 读取，与一号通 key 名不匹配
        [$account, $secret] = $this->getCredentials();
        if ($account !== '' && $secret !== '') {
            $this->accessToken = new AccessTokenServeService($account, $secret);
        }
    }

    /**
     * 读取一号通凭证。
     * 系统配置优先，驱动构造参数作为无系统配置上下文时的回退值。
     * @return array{0:string,1:string}
     */
    protected function getCredentials(): array
    {
        return [
            $this->getCredential('yihaotong_appid'),
            $this->getCredential('yihaotong_appsecret'),
        ];
    }

    /**
     * 读取单个凭证并归一化为非空字符串。
     * @param string $key
     * @return string
     */
    protected function getCredential(string $key): string
    {
        $value = '';
        if (function_exists('sys_config')) {
            $configured = sys_config($key, '');
            if (is_scalar($configured)) {
                $value = trim((string)$configured);
            }
        }

        if ($value !== '') {
            return $value;
        }

        $configured = $this->driverConfig[$key] ?? '';
        return is_scalar($configured) ? trim((string)$configured) : '';
    }

    /**
     * 获取一号通 API 服务。
     *
     * 一号通凭证由系统配置统一维护。驱动允许在配置缺失时完成实例化，
     * 但在真正发起请求前给出明确的业务错误，避免对空 accessToken 调用方法。
     *
     * @return AccessTokenServeService
     * @throws \RuntimeException 一号通凭证缺失时抛出
     */
    protected function getAccessToken(): AccessTokenServeService
    {
        if (!$this->accessToken) {
            [$account, $secret] = $this->getCredentials();
            if ($account === '' || $secret === '') {
                throw new \RuntimeException('缺少一号通凭证，请先在后台完成一号通登录/设置');
            }
            $this->accessToken = new AccessTokenServeService($account, $secret);
        }

        return $this->accessToken;
    }

    /**
     * 商家寄件获取快递公司
     * @return array|mixed
     */
    public function getKuaidiComs()
    {
        $list = $this->getAccessToken()->httpRequest(self::SHIPMENT_KUAIDI_NUMS, [], 'GET');
        foreach ($list as &$item) {
            $item['code'] = $item['value'];
            $item['value'] = $item['label'];
            $num = 1;
            foreach ($item['list'] as &$value) {
                $value['title'] = $item['label'] . '模板' . $num;
                $num++;
            }
        }
        return $list;
    }

    /**
     * 商家寄件创建订单
     * @param array $data
     * @return array|mixed
     */
    public function shippmentCreateOrder(array $data)
    {
        $siid = sys_config('config_export_siid');
        $param = [
            'kuaidicom' => $data['kuaidicom'],
            'man_name' => $data['man_name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'send_real_name' => $data['send_real_name'],
            'send_phone' => $data['send_phone'],
            'send_address' => $data['send_address'],
            'call_back_url' => sys_config('site_url') . '/api/order_call_back',
            'return_type' => $siid ? '10' : '20',
            'siid' => $siid,
            'tempid' => $data['temp_id'],
            'cargo' => $data['cargo'],
            'weight' => $data['weight'],
            'day_type' => $data['day_type'],
            'pickup_start_time' => $data['pickup_start_time'],
            'pickup_end_time' => $data['pickup_end_time'],
        ];
        return $this->getAccessToken()->httpRequest(self::SHIPMENT_CREATE_ORDER, $param, 'post');
    }

    /**
     * 取消商家寄件订单
     * @param array $data
     * @return array|mixed
     */
    public function shipmentCancelOrder(array $data)
    {
        $param = [
            'task_id' => $data['task_id'],
            'order_id' => $data['order_id'],
            'cancel_msg' => $data['cancel_msg'],
        ];
        return $this->getAccessToken()->httpRequest(self::SHIPMENT_CANCEL_ORDER, $param);
    }

    /**
     * 获取商家寄件订单列表
     * @param array $data
     * @return array|mixed
     */
    public function getShipmentOrderList(array $data)
    {
        $param = [
            'kuaidi_num' => $data['kuaidi_num'] ?? '',
            'courier_name' => $data['courier_name'] ?? '',
            'page' => $data['page'] ?? 1,
            'limit' => $data['limit'] ?? 10,
        ];

        return $this->getAccessToken()->httpRequest(self::SHIPMENT_INDEX, $param, 'GET');
    }

    /**
     * @param array $data
     * @return array|mixed
     */
    public function getPrice(array $data)
    {
        if (empty($data['kuaidicom'])) {
            throw new ApiException('快递编码必须填写');
        }
        if (empty($data['send_address'])) {
            throw new ApiException('寄件地址必须填写');
        }
        if (empty($data['address'])) {
            throw new ApiException('收件地址必须填写');
        }
        $param = [
            'kuaidicom' => $data['kuaidicom'],
            'send_address' => $data['send_address'],
            'address' => $data['address'] ?? '',
            'weight' => $data['weight'] ?? '',
            'service_type' => $data['service_type'] ?? '',
        ];

        return $this->getAccessToken()->httpRequest(self::SHIPMENT_PRICE, $param);
    }

    /**
     * 开通物流服务
     * @return bool|mixed
     */
    public function open()
    {
        return $this->getAccessToken()->httpRequest(self::EXPRESS_OPEN, []);
    }

    /**
     * 获取电子面单模板
     * @param $com 快递公司编号
     * @param int $page
     * @param int $limit
     * @return bool|mixed
     */
    public function temp(string $com)
    {
        $param = [
            'com' => $com
        ];

        $header = [];
        if (!sys_config('config_export_siid')) {
            $header = ['version:v1.1'];
        }
        return $this->getAccessToken()->httpRequest(self::EXPRESS_TEMP, $param, 'GET', true, $header);
    }

    /**
     * 获取物流公司列表
     * @param int $type 快递类型：1，国内运输商；2，国际运输商；3，国际邮政
     * @return bool|mixed
     */
    public function express(int $type = 0, int $page = 0, int $limit = 20)
    {
        if ($type) {
            $param = [
                'type' => $type,
                'page' => $page,
                'limit' => $limit
            ];
        } else {
            $param = [];
        }

        return $this->getAccessToken()->httpRequest(self::EXPRESS_LIST, $param, 'GET');
    }

    /**
     * 查询物流信息
     * @param string $num 物流单号
     * @param string|null $com 快递公司编码
     * @param string|null $phone 收件人手机号
     * @return bool|mixed
     */
    public function query(string $num, ?string $com = '', $phone = '')
    {
        $com = (string)($com ?? '');
        $phone = (string)($phone ?? '');
        $param = [
            'com' => $com,
            'num' => $num,
            'phone' => $phone
        ];
        // 一号通支持根据单号自动识别快递公司；空编码不应以空值覆盖该行为。
        if ($com === '') {
            unset($param['com']);
        }
        return $this->getAccessToken()->httpRequest(self::EXPRESS_QUERY, $param, 'post');
    }

    /**
     * 电子面单打印
     * @param array $data
     * @return bool|mixed
     */
    public function dump($data)
    {
        $param = $data;
        $param['com'] = $data['com'] ?? '';
        if (!$param['com']) throw new AdminException('快递公司编码缺失');
        $param['to_name'] = $data['to_name'] ?? '';
        $param['to_tel'] = $data['to_tel'] ?? '';
        $param['order_id'] = $data['order_id'] ?? '';
        $param['to_addr'] = $data['to_addr'] ?? '';
        if (!$param['to_addr'] || !$param['to_tel'] || !$param['to_name']) throw new AdminException('寄件人信息缺失');
        $param['from_name'] = $data['from_name'] ?? '';
        $param['from_tel'] = $data['from_tel'] ?? '';
        $param['from_addr'] = $data['from_addr'] ?? '';
        if (!$param['from_name'] || !$param['from_tel'] || !$param['from_addr']) throw new AdminException('收件人信息缺失');
        $param['temp_id'] = $data['temp_id'] ?? '';
        if (!$param['temp_id']) {
            throw new AdminException('电子面单模板ID缺失');
        }
        $param['siid'] = sys_config('config_export_siid');
        $param['count'] = $data['count'] ?? '';
        $param['cargo'] = $data['cargo'] ?? '';
        if (!$param['count']) {
            throw new AdminException('商品数量缺失');
        }
        // 架构豁免说明：driver 层禁止调用 app 层 ExpressServices，直接查库（同 crmeb\services\AliPayService::getPemPath 模式）
        $expressData = Db::name('express')->where('code', $param['com'])->find() ?? [];
        if (isset($data['cargo'])) $param['cargo'] = $data['cargo'];
        if ($expressData['partner_id'] == 1) $param['partner_id'] = $expressData['account'];
        if ($expressData['partner_key'] == 1) $param['partner_key'] = $expressData['key'];
        if ($expressData['net'] == 1) $param['net'] = $expressData['net_name'];
        if ($expressData['check_man'] == 1) $param['checkMan'] = $expressData['courier_name'];
        if ($expressData['partner_name'] == 1) $param['partnerName'] = $expressData['customer_name'];
        if ($expressData['is_code'] == 1) $param['code'] = $expressData['code_name'];
        if (!$data['siid']) {
            $param['print_type'] = 'IMAGE';
        }
        $header = [];
        if (!sys_config('config_export_siid')) {
            $header = ['version:v1.1'];
        }
        return $this->getAccessToken()->httpRequest(self::EXPRESS_DUMP, $param, 'POST', true, $header);
    }

}
