<?php
namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use think\Request;
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

       return $this->fetch();
   }
   public function tablelist(Request $request = null)
   {
       $db= new Backup();
       return Json::successlayui($db->dataList());
   }
}
