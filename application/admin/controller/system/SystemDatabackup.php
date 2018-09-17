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

    /**
     * 获取数据库表
     * @param Request|null $request
     */
    public function tablelist(Request $request = null)
    {
       $db= new Backup();
       return Json::result(0,'sucess',$db->dataList(),count($db->dataList()));
    }

    /**
     * 查看表结构
     * @param Request|null $request
     */
    public function seetable(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * 优化表
     * @param Request|null $request
     */
    public function optimize(Request $request = null)
    {
        return Json::successful($status==0 ? '禁用成功':'解禁成功');
    }

    /**修复表
     * @param Request|null $request
     */
    public function repair(Request $request = null)
    {
        return Json::successful($status==0 ? '禁用成功':'解禁成功');
    }
}
