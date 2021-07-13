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

namespace crmeb\utils;

class QRcode extends \dh2y\qrcode\QRcode
{
    public function setCacheDir(string $cache_dir)
    {
        $this->cache_dir = $cache_dir;
        if (!file_exists($this->cache_dir)) {
            mkdir($this->cache_dir, 0775, true);
        }
        return $this;
    }

}
