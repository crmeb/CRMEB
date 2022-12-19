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

namespace app\model\user;

use app\model\other\Category;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class UserLabel
 * @package app\model\user
 */
class UserLabel extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_label';

    /**
     * 标签分类
     * @param \think\Model $query
     * @param $value
     */
    public function searchLabelCateAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('label_cate', $value);
        }
    }

    /**
     * 关联标签分类
     * @return \think\model\relation\HasOne
     */
    public function cateName()
    {
        return $this->hasOne(Category::class, 'id', 'label_cate')->where('type', 0)->field(['id', 'name'])->bind(['cate_name' => 'name']);
    }

    /**
     * ids搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIdsAttr($query, $value)
    {
        if ($value) $query->whereIn('id', $value);
    }
}
