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

namespace app\dao\system\config;


use app\dao\BaseDao;
use app\model\system\config\SystemStorage;
use crmeb\traits\SearchDaoTrait;

/**
 * Class SystemStorageDao
 * @package app\dao\system\config
 */
class SystemStorageDao extends BaseDao
{

    use SearchDaoTrait;

    /**
     * @return string
     */
    protected function setModel(): string
    {
        return SystemStorage::class;
    }

    /**
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        return parent::search($where)->when(isset($where['type']), function ($query) use ($where) {
            $query->where('type', $where['type']);
        })->where('is_delete', 0)->when(isset($where['access_key']), function ($query) use ($where) {
            $query->where('access_key', $where['access_key']);
        });
    }
}
