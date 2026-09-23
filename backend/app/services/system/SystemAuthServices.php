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

namespace app\services\system;


use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\utils\Http;

/**
 * 商业授权
 * Class SystemAuthServices
 * @package app\services\system
 */
class SystemAuthServices extends BaseService
{

    /**
     * 申请授权
     * @param array $data
     * @return bool
     */
    public function authApply(array $data)
    {
        $res = Http::postRequest('http://authorize.crmeb.net/api/auth_apply', $data);
        if ($res === false) {
            throw new AdminException('申请失败');
        }
        $res = json_decode($res, true);
        if (isset($res['status'])) {
            if ($res['status'] == 400) {
                throw new AdminException('申请失败');
            } else {
                return true;
            }
        }
    }
}
