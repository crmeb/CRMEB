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
namespace app\model\article;

use app\model\product\product\StoreProduct;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\Model;

/**
 * TODO 文章Model
 * Class Article
 * @package app\model\article
 */
class Article extends BaseModel
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
    protected $name = 'article';

    /**
     * 商品一对一关联
     * @return \think\model\relation\HasOne
     */
    public function storeInfo()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')
            ->field('store_name,image,price,id,ot_price');
    }

    /**
     * 文章详情一对一关联
     * @return \think\model\relation\HasOne
     */
    public function content()
    {
        return $this->hasOne(ArticleContent::class, 'nid', 'id')->bind(['content']);
    }

    /**
     * 文章详情一对一关联
     * @return \think\model\relation\HasOne
     */
    public function cateName()
    {
        return $this->hasOne(ArticleCategory::class, 'id', 'cid')->bind(['catename'=>'title']);
    }

    /**
     * 文章图片获取器
     * @param $value
     * @return array|false|string[]
     */
    protected function getImageInputAttr($value)
    {
        return explode(',', $value) ?: [];
    }

    /**
     * 文章分类搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCidAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('cid', $value);
        }
    }

    /**
     * 文章标题搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTitleAttr($query, $value, $data)
    {
        $query->where('title', 'like', '%' . $value . '%');
    }

    /**
     * 热门文章搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsHotAttr($query, $value, $data)
    {
        $query->where('is_hot', $value);
    }

    /**
     * 轮播文章搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsBannerAttr($query, $value, $data)
    {
        $query->where('is_banner', $value);
    }

}
