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
namespace driver\express\Yihaotong;

use crmeb\exceptions\AdminException;
use crmeb\services\logistics\BaseExpress;
use crmeb\services\yihaotong\AccessTokenServeService;
use think\facade\Db;

class Driver extends BaseExpress
{
    const EXPRESS_OPEN = 'v2/expr/open';
    const EXPRESS_TEMP = 'v2/expr_dump/temp';
    const EXPRESS_LIST = 'v2/expr/express';
    const EXPRESS_QUERY = 'v2/expr/query';
    const EXPRESS_DUMP = 'v2/expr/dump';

    /**
     * 初始化
     * 从系统配置读取一号通账号信息，覆盖父类传入的空 AccessTokenServeService
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config = [])
    {
        // 一号通驱动需要从系统配置中读取 appid / appsecret
        // 因为 Express::invokeClass() 按 account/secret key 读取，与一号通 key 名不匹配
        $account = sys_config('yihaotong_appid', '');
        $secret = sys_config('yihaotong_appsecret', '');
        if ($account && $secret) {
            $this->accessToken = new AccessTokenServeService($account, $secret);
        }
    }

    /**
     * 开通物流服务
     * @return bool|mixed
     */
    public function open()
    {
        return $this->accessToken->httpRequest(self::EXPRESS_OPEN, []);
    }

    /**
     * 获取电子面单模版
     * @param string $com 快递公司编号
     * @return bool|mixed
     */
    public function temp(string $com)
    {
        $param = [
            'com' => $com
        ];

        $header = [];
        if (!($this->config['siid'] ?? '')) {
            $header = ['version:v1.1'];
        }
        return $this->accessToken->httpRequest(self::EXPRESS_TEMP, $param, 'GET', true, $header);
    }

    /**
     * 获取物流公司列表
     * @param int $type 快递类型：1，国内运输商；2，国际运输商；3，国际邮政
     * @param int $page
     * @param int $limit
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

        return $this->accessToken->httpRequest(self::EXPRESS_LIST, $param, 'GET');
    }

    /**
     * 查询物流信息
     * @param string $num 物流单号
     * @param string $com 快递公司编码
     * @param string $phone 手机号
     * @return bool|mixed
     */
    public function query(string $num, string $com = '', $phone = '')
    {
        $param = [
            'com' => $com,
            'num' => $num,
            'phone' => $phone
        ];
        if ($com === null) {
            unset($param['com']);
        }
        return $this->accessToken->httpRequest(self::EXPRESS_QUERY, $param, 'post');
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
        $param['siid'] = $data['siid'] ?? $this->config['siid'] ?? '';
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
        if (!($this->config['siid'] ?? '')) {
            $header = ['version:v1.1'];
        }
        return $this->accessToken->httpRequest(self::EXPRESS_DUMP, $param, 'POST', true, $header);
    }
}
