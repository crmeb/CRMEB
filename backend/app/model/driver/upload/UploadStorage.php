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

namespace app\model\driver\upload;


use crmeb\basic\BaseModel;

/**
 * 云存储
 * Class UploadStorage
 * @package app\model\driver\upload
 */
class UploadStorage extends BaseModel
{

    /**
     * @var string
     */
    protected $name = 'upload_storage';

    /**
     * @var string
     */
    protected $pk = 'id';

    /**
     * @var bool
     */
    protected $autoWriteTimestamp = false;

    /**
     * 按驱动隔离存储空间查询
     * @param mixed $query
     * @param string $value
     * @return void
     */
    public function searchDriverKeyAttr($query, $value)
    {
        if ($value !== '') $query->where('driver_key', $value);
    }

    /**
     * @param $query
     * @param $value
     */
    public function searchNameAttr($query, $value)
    {
        $query->where('name', $value);
    }

    /**
     * 类型搜索器
     * @param $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value) $query->where('type', $value);
    }

    /**
     * 状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') $query->where('status', $value);
    }
}
