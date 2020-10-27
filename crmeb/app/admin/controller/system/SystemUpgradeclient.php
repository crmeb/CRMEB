<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use crmeb\services\JsonService as Json;
use crmeb\services\UpgradeService as uService;
use think\facade\Db;

/**
 * 在线升级控制器
 * Class SystemUpgradeclient
 * @package app\admin\controller\system
 *
 */
class SystemUpgradeclient extends AuthController
{

    protected $serverweb = array('version' => '1.0', 'version_code' => 0);//本站点信息

    public function initialize()
    {
        parent::initialize();
        //屏蔽所有错误避免操作文件夹发生错误提示
        ini_set('display_errors', 0);
        error_reporting(0);
        self::snyweninfo();//更新站点信息
        $this->assign(['auth' => self::isauth(), 'app' => uService::isWritable(app()->getRootPath()), 'extend' => uService::isWritable(EXTEND_PATH), 'public' => uService::isWritable(app()->getRootPath() . 'public')]);
    }

    //同步更新站点信息
    public function snyweninfo()
    {
        $this->serverweb['ip'] = $this->request->ip();
        $this->serverweb['host'] = $this->request->host();
        $this->serverweb['https'] = !empty($this->request->domain()) ? $this->request->domain() : sys_config('site_url');
        $this->serverweb['webname'] = sys_config('site_name');
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

    public function index()
    {
        $server = uService::start();
        $version = $this->serverweb['version'];
        $version_code = $this->serverweb['version_code'];
        $this->assign(compact('server', 'version', 'version_code'));
        return $this->fetch();
    }

    public function get_list()
    {
        $list = uService::request_post(uService::$isList, ['page' => input('post.page/d'), 'limit' => input('post.limit/d')]);
        if (is_array($list) && isset($list['code']) && isset($list['data']) && $list['code'] == 200) {
            $list = $list['data'];
        } else {
            $list = [];
        }
        Json::successful('ok', ['list' => $list, 'page' => input('post.page/d') + 1]);
    }


    public function get_new_version_conte()
    {
        $post = $this->request->post();
        if (!isset($post['id'])) Json::fail('缺少参数ID');
        $versionInfo = uService::request_post(uService::$NewVersionCount, ['id' => $post['id']]);
        if (isset($versionInfo['code']) && isset($versionInfo['data']['count']) && $versionInfo['code'] == 200) {
            return Json::successful(['count' => $versionInfo['data']['count']]);
        } else {
            return Json::fail('服务器异常');
        }
    }
}