<?php
namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\services\UtilService;

/**
 * 附件目录
 * Class SystemAttachmentCategory
 * @package app\admin\model\system
 */
class SystemAttachmentCategory extends BaseModel
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
    protected $name = 'system_attachment_category';

    use ModelTrait;

    /**
     * 添加分类
     * @param $name
     * @param $att_size
     * @param $att_type
     * @param $att_dir
     * @param string $satt_dir
     * @param int $pid
     * @return SystemAttachmentCategory|\think\Model
     */
    public static function Add($name,$att_size,$att_type,$att_dir,$satt_dir='',$pid = 0 )
    {
        $data['name'] = $name;
        $data['att_dir'] = $att_dir;
        $data['satt_dir'] = $satt_dir;
        $data['att_size'] = $att_size;
        $data['att_type'] = $att_type;
        $data['time'] = time();
        $data['pid'] = $pid;
        return self::create($data);
    }

    /**
     * 获取分类图
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAll($name){
        $model = new self;
        if($name) $model = $model->where('name','LIKE',"%$name%");
        return self::tidyMenuTier($model->select(),0);
    }
    public static function tidyMenuTier($menusList,$pid = 0,$navList = [])
    {

        foreach ($menusList as $k=>$menu){
            $menu = $menu->getData();
            if($menu['pid'] == $pid){
                unset($menusList[$k]);
                $menu['child'] = self::tidyMenuTier($menusList,$menu['id']);
                $navList[] = $menu;
            }
        }
        return $navList;
    }

    /**
     * 获取分类下拉列表
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCateList($id = 10000){
        $model = new self();
        if($id == 0)
            $model->where('pid',$id);
        return UtilService::sortListTier($model->select()->toArray());
    }

    /**
     * 获取单条信息
     * @param $att_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getinfo($att_id){
        $model = new self;
        $where['att_id'] = $att_id;
        return $model->where($where)->select()->toArray()[0];
    }

}