<?php

namespace crmeb\services\invoice\storage;

use crmeb\services\invoice\BaseInvoice;

class Yihaotong extends BaseInvoice
{
    /**
     * 获取发票开具页面iframe地址
     */
    const INVOICE_ISSUANCE_URL = 'v2/invoice/invoice_issuance_url';

    /**
     * 下载发票
     */
    const DOWNLOAD_INVOICE = 'v2/invoice/download_invoice';

    /**
     * 查看发票详情
     */
    const INVOICE_INFO = 'v2/invoice/invoice_info';

    /**
     * 获取商品类目
     */
    const CATEGORY = 'v2/invoice/category';

    /**
     * 发票开具
     */
    const INVOICE_ISSUANCE = 'v2/invoice/invoice_issuance';

    /**
     * 申请红字发票
     */
    const APPLY_RED_INVOICE = 'v2/invoice/apply_red_invoice';

    /**
     * 开具负数发票
     */
    const RED_INVOICE_ISSUANCE = 'v2/invoice/red_invoice_issuance';


    /**
     * @param array $config
     * @return mixed|void
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/13
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
    }

    /**
     * 获取发票开具页面iframe地址
     * @param array $params
     * @return array|mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/13
     */
    public function invoiceIssuanceUrl(array $params = [])
    {
        return $this->accessToken->httpRequest(self::INVOICE_ISSUANCE_URL, $params);
    }

    /**
     * 下载发票
     * @param string $invoiceNum
     * @return array|mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/13
     */
    public function downloadInvoice(string $invoiceNum = '')
    {
        return $this->accessToken->httpRequest(self::DOWNLOAD_INVOICE . '/' . $invoiceNum, [], 'GET');
    }

    /**
     * 查看发票详情
     * @param string $invoiceNum
     * @return array|mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/14
     */
    public function invoiceInfo(string $invoiceNum = '')
    {
        return $this->accessToken->httpRequest(self::INVOICE_INFO . '/' . $invoiceNum, [], 'GET');
    }

    /**
     * 获取商品类目
     * @return array|mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/15
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
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/15
     */
    public function invoiceIssuance(string $unique = '', array $params = [])
    {
        return $this->accessToken->httpRequest(self::INVOICE_ISSUANCE . '/' . $unique, $params);
    }

    /**
     * 申请红字发票
     * @param array $params
     * @return array|mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/16
     */
    public function applyRedInvoice(array $params = [])
    {
        return $this->accessToken->httpRequest(self::APPLY_RED_INVOICE, $params);
    }

    /**
     * 开具负数发票
     * @param array $params
     * @return array|mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/16
     */
    public function redInvoiceIssuance(array $params = [])
    {
        return $this->accessToken->httpRequest(self::RED_INVOICE_ISSUANCE . '/' . $params['invoiceNum'], $params);
    }
}