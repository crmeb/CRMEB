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

use app\dao\system\SystemPemDao;
use crmeb\basic\BaseService;

class SystemPemServices extends BaseService
{
    /**
     * @var SystemPemDao
     */
    protected $dao;

    public function __construct(SystemPemDao $dao)
    {
        $this->dao = $dao;
    }

    public function savePem($data)
    {
        $this->dao->savePem($data);
        return true;
    }

    public function getPemPath($name)
    {
        $path = '';
        $info = $this->dao->get(['name' => $name]);
        if ($info) {
            $path = root_path('runtime/pem') . $info['path'] . '.pem';
            if (!file_exists($path)) {
                // 如果runtime/pem文件夹不存在，创建文件夹
                if (!file_exists(root_path('runtime/pem'))) {
                    mkdir(root_path('runtime/pem'), 0777, true);
                }
                file_put_contents($path, $info['content']);
            }
        }
        return $path;
    }
}
