<?php
/**
 * 附件目录
 *
 */

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 文件检验model
 * Class SystemFile
 * @package app\admin\model\system
 */
class SystemAttachmentType extends BaseModel
{
    use ModelTrait;
    /**添加附件记录
     */
    public static function attachmentAdd($name,$att_size,$att_type,$att_dir,$satt_dir='',$pid = 0 )
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
    public static function getAll($id){
        $model = new self;
        $where['pid'] = $id;
        $model->where($where)->order('att_id desc');
        return $model->page($model,$where,'',30);
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