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
        $tables = $request->post('tables/a');
        $db= new Backup();
        $res = $db->optimize($tables);
        return Json::successful($res ? '优化成功':'优化失败');
    }

    /**修复表
     * @param Request|null $request
     */
    public function repair(Request $request = null)
    {
        $tables = $request->post('tables/a');
        $db = new Backup();
        $res = $db->repair($tables);
        return Json::successful($res ? '修复成功':'修复失败');
    }
    /**备份表
     * @param Request|null $request
     */
    public function backup(Request $request = null)
    {
        $tables = $request->post('tables/a');
        $db= new Backup();
        $data = '';
        foreach ($tables as $t){
            $res = $db->backup($t,0);
            var_dump($res);
            if($res == false && $res != 0){
                $data .= $t.'|';
            }
        }
        echo $data;
        return Json::successful($data? '备份失败'.$data:'备份成功');
    }
}
