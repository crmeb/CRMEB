<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\copyproduct\storage;

use crmeb\services\copyproduct\BaseCopyProduct;


/**
 * Class Copy
 * @package crmeb\services\product\storage
 */
class Copy extends BaseCopyProduct
{

    /**
     * 是否开通
     */
    const PRODUCT_OPEN = 'copy/open';
    /**
     * 获取详情
     */
    const PRODUCT_GOODS = 'copy/goods';

    /** 初始化
     * @param array $config
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
    }

    /** 是否开通复制
     * @return mixed
     */
    public function open()
    {
        return $this->accessToken->httpRequest(self::PRODUCT_OPEN, []);
    }

    /** 复制商品
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public function goods(string $url, array $options = [])
    {
        $param['url'] = $url;
        return $this->accessToken->httpRequest(self::PRODUCT_GOODS, $param);
    }


}
