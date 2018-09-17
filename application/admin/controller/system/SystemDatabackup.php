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
    /**
     * 数据类表列表
     */
   public function index(){

       $db= new Backup();
       var_dump($db->dataList());
//       return $this->fetch('index',['list'=>$db->dataList()]);
//       return $this->fetch();
   }
}
