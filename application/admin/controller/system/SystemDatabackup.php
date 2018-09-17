<?php
namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use think\Request;
use service\JsonService as Json;
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
//       $res = [];
//       $res['code'] = '200';
//       $res['msg'] = 'sucess';
//       $res['data'] = $db->dataList();
//       $res['count'] = count($db->dataList());
       return Json::result(0,'sucess',$db->dataList(),count($db->dataList()));
   }
}
