<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use crmeb\services\JsonService as Json;
use crmeb\services\UpgradeService as uService;
use think\facade\Db;
use app\admin\model\system\SystemConfig;
/**
 * 在线升级控制器
 * Class SystemUpgradeclient
 * @package app\admin\controller\system
 *
 */
class SystemUpgradeclient extends AuthController
{

    protected $serverweb = array('version'=>'1.0','version_code'=>0);//本站点信息
    public function initialize()
    {
        parent::initialize();
        //屏蔽所有错误避免操作文件夹发生错误提示
        ini_set('display_errors',0);
        error_reporting(0);
        self::snyweninfo();//更新站点信息
        $this->assign(['auth'=>self::isauth(),'app'=>uService::isWritable(app()->getRootPath()),'extend'=>uService::isWritable(EXTEND_PATH),'public'=>uService::isWritable(app()->getRootPath().'public')]);
    }
    //同步更新站点信息
    public function snyweninfo(){
        $this->serverweb['ip'] = $this->request->ip();
        $this->serverweb['host'] = $this->request->host();
        $this->serverweb['https'] = !empty($this->request->domain())?$this->request->domain():SystemConfig::getConfigValue('site_url');
        $this->serverweb['webname'] = SystemConfig::getConfigValue('site_name');
        $local=uService::getVersion();
        if($local['code']==200 && isset($local['msg']['version']) && isset($local['msg']['version_code'])){
            $this->serverweb['version'] = uService::replace($local['msg']['version']);
            $this->serverweb['version_code'] = (int)uService::replace($local['msg']['version_code']);
        }
        uService::snyweninfo($this->serverweb);
    }
    //是否授权
    public function isauth(){
        return uService::isauth();
    }
    public function index(){
        $server=uService::start();
        $version=$this->serverweb['version'];
        $version_code=$this->serverweb['version_code'];
        $this->assign(compact('server','version','version_code'));
        return $this->fetch();
    }
    public function get_list(){
        $list=uService::request_post(uService::$isList,['page'=>input('post.page/d'),'limit'=>input('post.limit/d')]);
        if(is_array($list) && isset($list['code']) && isset($list['data']) && $list['code']==200){
            $list=$list['data'];
        }else{
            $list=[];
        }
        Json::successful('ok',['list'=>$list,'page'=>input('post.page/d')+1]);
    }
    //删除备份文件
    public function setcopydel(){
        $post=input('post.');
        if(!isset($post['id'])) Json::fail('删除备份文件失败，缺少参数ID');
        if(!isset($post['ids'])) Json::fail('删除备份文件失败，缺少参数IDS');
        $fileservice=new uService;
        if(is_array($post['ids'])){
            foreach ($post['ids'] as $file){
                $fileservice->del_dir(app()->getRootPath().'public'.DS.'copyfile'.$file);
            }
        }
        if($post['id']){
            $copyFile=app()->getRootPath().'public'.DS.'copyfile'.$post['id'];
            $fileservice->del_dir($copyFile);
        }
        Json::successful('删除成功');
    }
    public function get_new_version_conte(){
        $post=$this->request->post();
        if(!isset($post['id'])) Json::fail('缺少参数ID');
        $versionInfo=uService::request_post(uService::$NewVersionCount,['id'=>$post['id']]);
        if(isset($versionInfo['code']) && isset($versionInfo['data']['count']) && $versionInfo['code']==200){
            return Json::successful(['count'=>$versionInfo['data']['count']]);
        }else{
            return Json::fail('服务器异常');
        }
    }
    //一键升级
    public function auto_upgrad(){
        $prefix=config('database.prefix');
        $fileservice=new uService;
        $post=$this->request->post();
        if(!isset($post['id']))  Json::fail('缺少参数ID');
        $versionInfo=$fileservice->request_post(uService::$isNowVersion,['id'=>$post['id']]);
        if($versionInfo===null) Json::fail('服务器异常，请稍后再试');
        if(isset($versionInfo['code']) && $versionInfo['code']==400) Json::fail(isset($versionInfo['msg'])?$versionInfo['msg']:'您暂时没有权限升级，请联系管理员！');
        if(is_array($versionInfo) && isset($versionInfo['data'])){
            $list=$versionInfo['data'];
            $id=[];
            foreach ($list as $key=>$val){
                $savefile=app()->getRootPath() . 'public' . DS.'upgrade_lv';
                //1，检查远程下载文件，并下载
                if(($save_path=$fileservice->check_remote_file_exists($val['zip_name'],$savefile))===false) Json::fail('远程升级包不存在');
                //2，首先解压文件
                $savename=app()->getRootPath().'public'.DS.'upgrade_lv'.DS.time();
                $fileservice->zipopen($save_path,$savename);
                //3，执行SQL文件
                Db::startTrans();
                try{
                    //参数3不介意大小写的
                    $sqlfile=$fileservice->list_dir_info($savename.DS,true,'sql');
                    if(is_array($sqlfile) && !empty($sqlfile)){
                        foreach($sqlfile as $file){
                            if(file_exists($file)){
                                //为一键安装做工作记得表前缀要改为[#DB_PREFIX#]哦
                                $execute_sql=explode(";\r",str_replace(['[#DB_PREFIX#]', "\n"], [$prefix, "\r"], file_get_contents($file)));
                                foreach($execute_sql as $_sql){
                                    if ($query_string = trim(str_replace(array(
                                        "\r",
                                        "\n",
                                        "\t"
                                    ), '', $_sql))) Db::execute($query_string);
                                }
                                //执行完sql记得删掉哦
                                $fileservice->unlink_file($file);
                            }
                        }
                    }
                    Db::commit();
                }catch(\Exception $e){
                    Db::rollback();
                    //删除解压下的文件
                    $fileservice->del_dir(app()->getRootPath().'public'.DS.'upgrade_lv');
                    //删除压缩包
                    $fileservice->unlink_file($save_path);
                    //升级失败发送错误信息
                    $fileservice->request_post(uService::$isInsertLog,[
                        'content'=>'升级失败，错误信息为:'.$e->getMessage(),
                        'add_time'=>time(),
                        'ip'=>$this->request->ip(),
                        'http'=>$this->request->domain(),
                        'type'=>'error',
                        'version'=>$val['version']
                    ]);
                    return Json::fail('升级失败SQL文件执行有误');
                }
                //4,备份文件
                $copyFile=app()->getRootPath().'public'.DS.'copyfile'.$val['id'];
                $copyList=$fileservice->get_dirs($savename.DS);
                if(isset($copyList['dir'])){
                    if($copyList['dir'][0]=='.' && $copyList['dir'][1]=='..'){
                        array_shift($copyList['dir']);
                        array_shift($copyList['dir']);
                    }
                    foreach($copyList['dir'] as $dir){
                        if(file_exists(app()->getRootPath().$dir,$copyFile.DS.$dir)){
                            $fileservice->copy_dir(app()->getRootPath().$dir,$copyFile.DS.$dir);
                        }
                    }
                }
                //5，覆盖文件
                $fileservice->handle_dir($savename,app()->getRootPath());
                //6,删除升级生成的目录
                $fileservice->del_dir(app()->getRootPath().'public'.DS.'upgrade_lv');
                //7,删除压缩包
                $fileservice->unlink_file($save_path);
                //8,改写本地升级文件
                $handle=fopen(app()->getRootPath().'.version','w+');
                if($handle===false)  Json::fail(app()->getRootPath().'.version'.'无法写入打开');
                $content=<<<EOT
version={$val['version']}
version_code={$val['id']}
EOT;
                if(fwrite($handle,$content)===false) Json::fail('升级包写入失败');
                fclose($handle);
                //9,向服务端发送升级日志
                $posts=[
                    'ip'=>$this->request->ip(),
                    'https'=>$this->request->domain(),
                    'update_time'=>time(),
                    'content'=>'一键升级成功，升级版本号为：'.$val['version'].'。版本code为：'.$val['id'],
                    'type'=>'log',
                    'versionbefor'=>$this->serverweb['version'],
                    'versionend'=>$val['version']
                ];
                $inset=$fileservice->request_post(uService::$isInsertLog,$posts);
                $id[]=$val['id'];
            }
            //10,升级完成
            Json::successful('升级成功',['code'=>end($id),'version'=>$val['version']]);
        }else{
            Json::fail('服务器异常，请稍后再试');
        }
    }
}