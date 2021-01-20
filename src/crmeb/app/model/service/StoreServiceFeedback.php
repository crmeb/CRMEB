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

namespace app\model\service;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 客服留言反馈
 * Class StoreServiceFeedback
 * @package app\model\service
 */
class StoreServiceFeedback extends BaseModel
{

    use ModelTrait;

    /**
     * @var string
     */
    protected $name = 'store_service_feedback';

    /**
     * @var string
     */
    protected $pk = 'id';

    /**
     * @param $value
     * @return false|string
     */
    public function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 标题搜索
     * @param Model $query
     * @param $value
     */
    public function searchTitleAttr($query, $value)
    {
        $value && $query->whereLike('rela_name|phone|content|uid', "%" . $value . "%");
    }

}
