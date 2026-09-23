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
declare(strict_types=1);

namespace app\dao\order;

use crmeb\basic\BaseDao;
use app\model\order\ServiceFulfillment;

/**
 * 服务履约单 DAO
 * 提供 eb_service_fulfillment 表的基础数据库操作
 */
class ServiceFulfillmentDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return ServiceFulfillment::class;
    }

    /**
     * 创建服务履约记录
     * @param array $data 履约记录数据
     * @return int 返回新记录主键 ID
     */
    public function create(array $data): int
    {
        $model = $this->getModel()::create($data);
        return $model->id ?? 0;
    }
}
