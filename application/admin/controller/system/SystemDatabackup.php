<?php
namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use \tp5er\Backup;

/**
 * 文件校验控制器
 * Class SystemFile
 * @package app\admin\controller\system
 *
 */
class SystemDatabackup extends AuthController
{
   public function index(){
       $config=array(
           'path'     => './Data/',//数据库备份路径
           'part'     => 20971520,//数据库备份卷大小
           'compress' => 0,//数据库备份文件是否启用压缩 0不压缩 1 压缩
           'level'    => 9 //数据库备份文件压缩级别 1普通 4 一般  9最高
       );
       $db= new Backup($config);
       var_dump($db->dataList());
//       return $this->fetch('index',['list'=>$db->dataList()]);
//       return $this->fetch();
   }
}
