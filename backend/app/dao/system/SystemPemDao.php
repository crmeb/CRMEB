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
namespace app\dao\system;

use crmeb\basic\BaseDao;
use app\model\system\SystemPem;

class SystemPemDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemPem::class;
    }

    public function savePem($data)
    {
        $info = $this->getModel()->where('name', $data['name'])->find();
        if ($info) {
            $info = $info->toArray();
            $this->getModel()->where('id', $info['id'])->update($data);
        } else {
            $data['add_time'] = time();
            $this->getModel()->save($data);
        }
        return true;
    }
}