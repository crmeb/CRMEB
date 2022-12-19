<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\easywechat\v3pay;


use crmeb\exceptions\PayException;
use think\facade\Cache;

/**
 * Class Certficates
 * @package crmeb\services\easywechat\v3pay
 */
trait Certficates
{

    /**
     * @param string|null $key
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getCertficatescAttr(string $key = null)
    {
        $driver = Cache::store('file');
        $cacheKey = '_wx_v3' . $this->app['config']['v3_payment']['serial_no'];
        if ($driver->has($cacheKey)) {
            $res = $driver->get($cacheKey);
            if ($key && $res) {
                return $res[$key] ?? null;
            } else {
                return $res;
            }
        }
        $certficates = $this->getCertficates();
        $driver->set($cacheKey, $certficates, 3600 * 24 * 30);
        if ($key && $certficates) {
            return $certficates[$key] ?? null;
        }
        return $certficates;
    }

    /**
     * get certficates.
     *
     * @return array
     */
    public function getCertficates()
    {
        $response = $this->request('v3/certificates', 'GET', [], false);
        if (isset($response['code'])) {
            throw new PayException($response['message']);
        }
        $certificates = $response['data'][0];
        $certificates['certificates'] = $this->decrypt($certificates['encrypt_certificate']);
        unset($certificates['encrypt_certificate']);
        return $certificates;
    }
}
