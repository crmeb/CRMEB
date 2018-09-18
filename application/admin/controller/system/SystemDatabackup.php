<?php
namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use think\Request;
use service\JsonService as Json;
use \tp5er\Backup;

/**
 * 文件校验控制器
 * Class SystemDatabackup
 * @package app\admin\controller\system
 *
 */
class SystemDatabackup extends AuthController
{
    protected $DB;
    public function _initialize()
    {
        $config = array(
            'path' => './backup/data/',
            //数据库备份路径
            'part' => 20971520,
            //数据库备份卷大小
            'compress' => 1,
            //数据库备份文件是否启用压缩 0不压缩 1 压缩
            'level' => 5,
        );
        $this->DB = new Backup($config);
    }

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
       $db= $this->DB;
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
        $db= $this->DB;
        $res = $db->optimize($tables);
        return Json::successful($res ? '优化成功':'优化失败');
    }

    /**修复表
     * @param Request|null $request
     */
    public function repair(Request $request = null)
    {
        $tables = $request->post('tables/a');
        $db = $this->DB;
        $res = $db->repair($tables);
        return Json::successful($res ? '修复成功':'修复失败');
    }
    /**备份表
     * @param Request|null $request
     */
    public function backup(Request $request = null)
    {
        $tables = $request->post('tables/a');
        $db= $this->DB;
        $data = '';
        foreach ($tables as $t){
            $res = $db->backup($t,0);
            if($res == false && $res != 0){
                $data .= $t.'|';
            }
        }
        return Json::successful($data? '备份失败'.$data:'备份成功');
    }
    /**获取备份记录表
     */
    public function fileList()
    {
        $db = $this->DB;
        $files = $db->fileList();
        $data = [];
        foreach ($files as $key=>$t){
            $data[$key]['backtime'] = $key;
            $data[$key]['part'] = $t['part'];
            $data[$key]['size'] = $t['size'].'B';
            $data[$key]['compress'] = $t['compress'];
            $data[$key]['time'] = date('Y-m-d H:i:s',$t['time']);
        }

        return Json::result(0,'sucess',$data,count($data));
    } /**删除备份记录表
     * @param Request|null $request
     */
    public function delFile(Request $request = null)
    {
        $time = strtotime($request->post('time'));
        $files = $this->DB->delFile($time);
        var_dump($files);

        return Json::result(0,'sucess',$data,count($data));
    }
}
