<?php
namespace app\models\article;

use crmeb\services\SystemConfigService;
use think\facade\Db;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 文章Model
 * Class Article
 * @package app\models\article
 */
class Article extends BaseModel
{
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

    use ModelTrait;

    protected function getImageInputAttr($value)
    {
        return explode(',',$value)?:[];
    }


    /**
     * TODO 获取一条新闻
     * @param int $id
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleOne($id = 0){
        if(!$id) return [];
        $list = self::where('status',1)->where('hide',0)->where('id',$id)->order('id desc')->find();
        if($list){
            $list = $list->hidden(['hide','status','admin_id','mer_id'])->toArray();
            $list["content"] = Db::name('articleContent')->where('nid',$id)->value('content');
            return $list;
        }
        else return [];
    }

    /**
     * TODO 获取某个分类底下的文章
     * @param $cid
     * @param $page
     * @param $limit
     * @param string $field
     * @return mixed
     */
    public static function cidByArticleList($cid, $page, $limit, $field = 'id,title,image_input,visit,add_time,synopsis,url')
    {
        $model=new self();
//        if ($cid) $model->where("`cid` LIKE '$cid,%' OR `cid` LIKE '%,$cid,%' OR `cid` LIKE '%,$cid' OR `cid`=$cid ");
        if ((int)$cid) $model = $model->where("CONCAT(',',cid,',')  LIKE '%,$cid,%'");
        $model = $model->field($field);
        $model = $model->where('status', 1);
        $model = $model->where('hide', 0);
        $model = $model->order('sort DESC,add_time DESC');
        if($page) $model = $model->page($page, $limit);
        return $model->select();
    }

    /**
     * TODO 获取热门文章
     * @param string $field
     * @return mixed]
     */
    public static function getArticleListHot($field = 'id,title,image_input,visit,add_time,synopsis,url'){
        $model = new self();
        $model = $model->field($field);
        $model = $model->where('status', 1);
        $model = $model->where('hide', 0);
        $model = $model->where('is_hot', 1);
        $model = $model->order('sort DESC,add_time DESC');
        return $model->select();
    }

    /**
     * TODO 获取轮播文章
     * @param string $field
     * @return mixed
     */
    public static function getArticleListBanner($field = 'id,title,image_input,visit,add_time,synopsis,url'){
        $model = new self();
        $model = $model->field($field);
        $model = $model->where('status', 1);
        $model = $model->where('hide', 0);
        $model = $model->where('is_banner', 1);
        $model = $model->order('sort DESC,add_time DESC');
        $model = $model->limit(SystemConfigService::get('news_slides_limit') ?? 3);
        return $model->select();
    }
}
