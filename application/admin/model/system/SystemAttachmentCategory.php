<?php
/**
 * 附件目录
 *
 */

namespace app\admin\model\system;

use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;

/**
 * 文件检验model
 * Class SystemFile
 * @package app\admin\model\system
 */
class SystemAttachmentCategory extends ModelBasic
{
    use ModelTrait;
    /**添加分类
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
     * */
    public static function getAll(){
        $model = new self;
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

    /**获取分类下拉列表
     * @return array
     */
    public static function getCateList($id = 10000){
        $model = new self();
        if($id == 0)
            $model->where('pid',$id);
        return UtilService::sortListTier($model->select()->toArray());
    }
    /**
     * 获取单条信息
     * */
    public static function getinfo($att_id){
        $model = new self;
        $where['att_id'] = $att_id;
        return $model->where($where)->select()->toArray()[0];
    }

}