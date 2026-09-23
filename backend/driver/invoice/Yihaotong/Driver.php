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
namespace driver\invoice\Yihaotong;

use crmeb\services\invoice\BaseInvoice;
use crmeb\services\yihaotong\AccessTokenServeService;

class Driver extends BaseInvoice
{
    const INVOICE_ISSUANCE_URL = 'v2/invoice/invoice_issuance_url';
    const DOWNLOAD_INVOICE = 'v2/invoice/download_invoice';
    const INVOICE_INFO = 'v2/invoice/invoice_info';
    const CATEGORY = 'v2/invoice/category';
    const INVOICE_ISSUANCE = 'v2/invoice/invoice_issuance';
    const APPLY_RED_INVOICE = 'v2/invoice/apply_red_invoice';
    const RED_INVOICE_ISSUANCE = 'v2/invoice/red_invoice_issuance';

    /**
     * 初始化
     * 从系统配置读取一号通账号信息，覆盖父类传入的空 AccessTokenServeService
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
        // 一号通驱动需要从系统配置中读取 appid / appsecret
        // 因为 Invoice::invokeClass() 按 account/secret key 读取，与一号通 key 名不匹配
        $account = sys_config('yihaotong_appid', '');
        $secret = sys_config('yihaotong_appsecret', '');
        if ($account && $secret) {
            $this->accessToken = new AccessTokenServeService($account, $secret);
        }
    }

    /**
     * 获取发票开具页面iframe地址
     * @param array $params
     * @return array|mixed
     */
    public function invoiceIssuanceUrl(array $params = [])
    {
        return $this->accessToken->httpRequest(self::INVOICE_ISSUANCE_URL, $params);
    }

    /**
     * 下载发票
     * @param string $invoiceNum
     * @return array|mixed
     */
    public function downloadInvoice(string $invoiceNum = '')
    {
        return $this->accessToken->httpRequest(self::DOWNLOAD_INVOICE . '/' . $invoiceNum, [], 'GET');
    }

    /**
     * 查看发票详情
     * @param string $invoiceNum
     * @return array|mixed
     */
    public function invoiceInfo(string $invoiceNum = '')
    {
        return $this->accessToken->httpRequest(self::INVOICE_INFO . '/' . $invoiceNum, [], 'GET');
    }

    /**
     * 获取商品类目
     * @return array|mixed
     */
    public function category(array $params = [])
    {
        return $this->accessToken->httpRequest(self::CATEGORY, $params, 'GET');
    }

    /**
     * 发票开具
     * @param string $unique
     * @param array $params
     * @return array|mixed
     */
    public function invoiceIssuance(string $unique = '', array $params = [])
    {
        return $this->accessToken->httpRequest(self::INVOICE_ISSUANCE . '/' . $unique, $params);
    }

    /**
     * 申请红字发票
     * @param array $params
     * @return array|mixed
     */
    public function applyRedInvoice(array $params = [])
    {
        return $this->accessToken->httpRequest(self::APPLY_RED_INVOICE, $params);
    }

    /**
     * 开具负数发票
     * @param array $params
     * @return array|mixed
     */
    public function redInvoiceIssuance(array $params = [])
    {
        return $this->accessToken->httpRequest(self::RED_INVOICE_ISSUANCE . '/' . $params['invoiceNum'], $params);
    }
}