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
declare (strict_types=1);

namespace app\model\service;

use app\model\other\Category;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 客服话术
 * @mixin Model
 */
class StoreServiceSpeechcraft extends BaseModel
{
    use ModelTrait;

    /**
     * 表名
     * @var string
     */
    protected $name = 'store_service_speechcraft';

    /**
     * 主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 时间格式化
     * @param $value
     * @param $data
     * @return false|string
     */
    public function getAddTimeAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 关联标签分类
     * @return \think\model\relation\HasOne
     */
    public function cateName()
    {
        return $this->hasOne(Category::class, 'id', 'cate_id')->where('type', 1)->field(['id', 'name'])->bind(['cate_name' => 'name']);
    }

    /**
     * 话术搜索
     * @param Model $query
     * @param $value
     */
    public function searchTitleAttr($query, $value)
    {
        if ($value !== '') $query->whereLike('title', '%' . $value . '%');
    }

    /**
     * 归属客服搜索
     * @param Model $query
     * @param $value
     */
    public function searchKefuIdAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('kefu_id', $value);
        }
    }

    /**
     * 分类搜索
     * @param Model $query
     * @param $value
     */
    public function searchCateIdAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('cate_id', $value);
        }
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchMessageAttr($query, $value)
    {
        if ($value !== '') $query->where('message', $value);

    }
}
