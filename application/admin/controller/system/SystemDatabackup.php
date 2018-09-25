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
            'path' => PUBILC_PATH.'backup/data/',
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
        return Json::result(200,'sucess',$data,count($data));
    }
    /**删除备份记录表
     * @param Request|null $request
     */
    public function delFile(Request $request = null)
    {
        $feilname = strtotime($request->post('feilname'));
        $files = $this->DB->delFile($feilname);
       return Json::result(200,'sucess');
    }
    /**倒入备份记录表
     * @param Request|null $request
     */
    public function import(Request $request = null)
    {
        $part = null; $start = null;
        $time = strtotime($request->post('feilname'));
        $db = $this->DB;
        if(is_numeric($time) && is_null($part) && is_null($start)){
            $list= $db->getFile('timeverif',$time);
            if(is_array($list)){
                session::set('backup_list',$list);
            $this->success('初始化完成！','',array('part' =>1,'start'=>0));
            }else{
                $this->error('备份文件可能已经损坏，请检查！');
            }
        }else if(is_numeric($part)&&is_numeric($start)){
                $list=session::get('backup_list');
                $start=$db->setFile($list)->import($start);
                if(false===$start){
                    $this->error('还原数据出错！');
                }elseif(0===$start){
                    if(isset($list[++$part])){
                        $data=array('part'=>$part,'start'=>0);
                    $this->success("正在还原...#{$part}",'',$data);
                    }else{
                        session::delete('backup_list');
                        $this->success('还原完成！');
                    }
                }else{
                    $data=array('part'=>$part,'start'=>$start[0]);
                    if($start[1]){
                        $rate=floor(100*($start[0]/$start[1]));
                        $this->success("正在还原...#{$part}({$rate}%)",'',$data);
                    }else{
                         $data['gz']=1;
                        $this->success("正在还原...#{$part}",'',$data);
                    }
                    $this->success("正在还原...#{$part}",'');
                }
        }else{
                $this->error('参数错误！');
        }
        // return Json::result(0,'sucess',$data,count($data));
    }
    /**下载备份记录表
     * @param Request|null $request
     */
    public function downloadFile(Request $request = null)
    {
        $feilname = strtotime($request->post('feilname'));
        $this->DB->downloadFile($feilname);
    }
}
