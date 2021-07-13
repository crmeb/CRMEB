<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\system;

use app\adminapi\controller\AuthController;
use app\services\system\config\SystemConfigServices;
use crmeb\services\UpgradeService as uService;
use app\models\system\SystemConfig;
use think\facade\Db;

/**
 * 在线升级控制器
 * Class SystemUpgradeclient
 * @package app\admin\controller\system
 *
 */
class SystemUpgradeClient extends AuthController
{

    protected $serverweb = array('version' => '1.0', 'version_code' => 0);//本站点信息

    public function initialize()
    {
        parent::initialize();
        self::snyweninfo();//更新站点信息
    }

    //同步更新站点信息
    public function snyweninfo()
    {
        /** @var SystemConfigServices $systemConfig */
        $systemConfig = app()->make(SystemConfigServices::class);
        $this->serverweb['ip'] = $this->request->ip();
        $this->serverweb['host'] = $this->request->host();
        $this->serverweb['https'] = !empty($this->request->domain()) ? $this->request->domain() : $systemConfig->getConfigValue('site_url');
        $this->serverweb['webname'] = $systemConfig->getConfigValue('site_name');
        $local = uService::getVersion();
        if ($local['code'] == 200 && isset($local['msg']['version']) && isset($local['msg']['version_code'])) {
            $this->serverweb['version'] = uService::replace($local['msg']['version']);
            $this->serverweb['version_code'] = (int)uService::replace($local['msg']['version_code']);
        }
        uService::snyweninfo($this->serverweb);
    }

    //是否授权
    public function isauth()
    {
        return uService::isauth();
    }

    /**
     * 升级列表
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20]
        ]);
        $list = uService::request_post(uService::$isList, ['page' => $where['page'], 'limit' => $where['limit']]);
        if (is_array($list) && isset($list['code']) && isset($list['data']) && $list['code'] == 200) {
            $list = $list['data'];
        } else {
            $list = [];
        }
        return app('json')->success($list);
    }

    //删除备份文件
    public function setcopydel()
    {
        $post = input('post.');
        if (!isset($post['id'])) app('json')->fail('删除备份文件失败，缺少参数ID');
        if (!isset($post['ids'])) app('json')->fail('删除备份文件失败，缺少参数IDS');
        $fileservice = new uService;
        if (is_array($post['ids'])) {
            foreach ($post['ids'] as $file) {
                $fileservice->del_dir(app()->getRootPath() . 'public' . DS . 'copyfile' . $file);
            }
        }
        if ($post['id']) {
            $copyFile = app()->getRootPath() . 'public' . DS . 'copyfile' . $post['id'];
            $fileservice->del_dir($copyFile);
        }
        return app('json')->success('删除成功');
    }

    public function get_new_version_conte()
    {
        $post = $this->request->post();
        if (!isset($post['id'])) app('json')->fail('缺少参数ID');
        $versionInfo = uService::request_post(uService::$NewVersionCount, ['id' => $post['id']]);
        if (isset($versionInfo['code']) && isset($versionInfo['data']['count']) && $versionInfo['code'] == 200) {
            return app('json')->success(['count' => $versionInfo['data']['count']]);
        } else {
            return app('json')->fail('服务器异常');
        }
    }

    //一键升级
    public function auto_upgrade()
    {
        $prefix = config('database.prefix');
        $fileservice = new uService;
        $post = $this->request->post();
        if (!isset($post['id'])) return app('json')->fail('缺少参数ID');
        $versionInfo = $fileservice->request_post(uService::$isNowVersion, ['id' => $post['id']]);
        if ($versionInfo === null) return app('json')->fail('服务器异常，请稍后再试');
        if (isset($versionInfo['code']) && $versionInfo['code'] == 400) return app('json')->fail(isset($versionInfo['msg']) ? $versionInfo['msg'] : '您暂时没有权限升级，请联系管理员！');
        if (is_array($versionInfo) && isset($versionInfo['data'])) {
            $list = $versionInfo['data'];
            $id = [];
            foreach ($list as $key => $val) {
                $savefile = app()->getRootPath() . 'public' . DS . 'upgrade_lv';
                //1，检查远程下载文件，并下载
                if (($save_path = $fileservice->check_remote_file_exists($val['zip_name'], $savefile)) === false) app('json')->fail('远程升级包不存在');
                //2，首先解压文件
                $savename = app()->getRootPath() . 'public' . DS . 'upgrade_lv' . DS . time();
                $fileservice->zipOpen($save_path, $savename);
                //3，执行SQL文件
                Db::startTrans();
                try {
                    //参数3不介意大小写的
                    $sqlfile = $fileservice->listDirInfo($savename . DS, true, 'sql');
                    if (is_array($sqlfile) && !empty($sqlfile)) {
                        foreach ($sqlfile as $file) {
                            if (file_exists($file)) {
                                //为一键安装做工作记得表前缀要改为[#DB_PREFIX#]哦
                                $execute_sql = explode(";\r", str_replace(['[#DB_PREFIX#]', "\n"], [$prefix, "\r"], file_get_contents($file)));
                                foreach ($execute_sql as $_sql) {
                                    if ($query_string = trim(str_replace(array(
                                        "\r",
                                        "\n",
                                        "\t"
                                    ), '', $_sql))) Db::execute($query_string);
                                }
                                //执行完sql记得删掉哦
                                $fileservice->unlinkFile($file);
                            }
                        }
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    //删除解压下的文件
                    $fileservice->del_dir(app()->getRootPath() . 'public' . DS . 'upgrade_lv');
                    //删除压缩包
                    $fileservice->unlinkFile($save_path);
                    //升级失败发送错误信息
                    $fileservice->request_post(uService::$isInsertLog, [
                        'content' => '升级失败，错误信息为:' . $e->getMessage(),
                        'add_time' => time(),
                        'ip' => $this->request->ip(),
                        'http' => $this->request->domain(),
                        'type' => 'error',
                        'version' => $val['version']
                    ]);
                    return app('json')->fail('升级失败SQL文件执行有误');
                }
                //4,备份文件
                $copyFile = app()->getRootPath() . 'public' . DS . 'copyfile' . $val['id'];
                $copyList = $fileservice->getDirs($savename . DS);
                if (isset($copyList['dir'])) {
                    if ($copyList['dir'][0] == '.' && $copyList['dir'][1] == '..') {
                        array_shift($copyList['dir']);
                        array_shift($copyList['dir']);
                    }
                    foreach ($copyList['dir'] as $dir) {
                        if (file_exists(app()->getRootPath() . $dir, $copyFile . DS . $dir)) {
                            $fileservice->copyDir(app()->getRootPath() . $dir, $copyFile . DS . $dir);
                        }
                    }
                }
                //5，覆盖文件
                $fileservice->handleDir($savename, app()->getRootPath());
                //6,删除升级生成的目录
                $fileservice->del_dir(app()->getRootPath() . 'public' . DS . 'upgrade_lv');
                //7,删除压缩包
                $fileservice->unlinkFile($save_path);
                //8,改写本地升级文件
                $handle = fopen(app()->getRootPath() . '.version', 'w+');
                if ($handle === false) return app('json')->fail(app()->getRootPath() . '.version' . '无法写入打开');
                $content = <<<EOT
version={$val['version']}
version_code={$val['id']}
EOT;
                if (fwrite($handle, $content) === false) return app('json')->fail('升级包写入失败');
                fclose($handle);
                //9,向服务端发送升级日志
                $posts = [
                    'ip' => $this->request->ip(),
                    'https' => $this->request->domain(),
                    'update_time' => time(),
                    'content' => '一键升级成功，升级版本号为：' . $val['version'] . '。版本code为：' . $val['id'],
                    'type' => 'log',
                    'versionbefor' => $this->serverweb['version'],
                    'versionend' => $val['version']
                ];
                $inset = $fileservice->request_post(uService::$isInsertLog, $posts);
                $id[] = $val['id'];
            }
            //10,升级完成
            return app('json')->success('升级成功', ['code' => end($id), 'version' => $val['version']]);
        } else {
            return app('json')->fail('服务器异常，请稍后再试');
        }
    }
}
