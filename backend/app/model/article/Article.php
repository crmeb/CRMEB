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
namespace app\model\article;

use app\model\product\CenterProductModel;
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
        return $this->hasOne(CenterProductModel::class, 'id', 'product_id')
            // 中台商品表 eb_product_center 商品名字段为 name，此处别名为 store_name 保持旧版数据契约
            ->field('name as store_name,image,price,id,original_price as ot_price');
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
        return $this->hasOne(ArticleCategory::class, 'id', 'cid')->bind(['catename' => 'title']);
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
     * 添加时间获取器
     * add_time 列为 varchar 存储的秒级时间戳，统一格式化为日期字符串输出，
     * 避免前端 new Date(纯数字字符串) 解析为 Invalid Date 显示 NaN
     * @param $value
     * @return string
     */
    protected function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', (int)$value) : '';
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
            if (is_array($value)) {
                $query->whereIn('cid', $value);
            } else {
                $query->where('cid', $value);
            }
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
        if ($value !== '') {
            $query->where('title', 'like', '%' . $value . '%');
        }
    }

    /**
     * 热门文章搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsHotAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('is_hot', $value);
        }
    }

    /**
     * 轮播文章搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsBannerAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('is_banner', $value);
        }
    }

}
