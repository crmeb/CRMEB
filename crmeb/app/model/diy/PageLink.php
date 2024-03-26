<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\diy;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

class PageLink extends BaseModel
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
    protected $name = 'page_link';

    /**
     * 分类搜索器
     * @param Model $query
     * @param $value
     */
    public function searchCateIdAttr($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                $query->whereIn('cate_id', $value);
            } else {
                $query->where('cate_id', $value);
            }
        }
    }

    /**
     * 是否使用搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value != '') $query->where('status', $value);
    }


    /**
     * 模块检测
     * @param Model $query
     * @param $value
     */
    public function searchNoModelAttr($query, $value)
    {
        $query->when(!in_array('seckill', $value), function ($q1) {
            $q1->whereNotLike('name', '%秒杀%');
        })->when(!in_array('bargain', $value), function ($q2) {
            $q2->whereNotLike('name', '%砍价%');
        })->when(!in_array('combination', $value), function ($q3) {
            $q3->whereNotLike('name', '%拼团%');
        });
    }
}
