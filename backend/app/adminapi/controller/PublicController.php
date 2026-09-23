<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\adminapi\controller;


use app\Request;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\SystemRouteServices;
use crmeb\services\CacheService;
use think\Response;
use think\facade\Db;

class PublicController
{

    /**
     * 下载文件
     * @param string $key
     * @return Response|\think\response\File
     */
    public function download(Request $request, string $key = '')
    {
        if ($key === '') {
            $key = $request->getMore([
                ['key', ''],
            ], true);
        }
        if (!$key) {
            return Response::create()->code(500);
        }
        $fileName = CacheService::get($key);
        if (is_array($fileName) && isset($fileName['path']) && isset($fileName['fileName']) && $fileName['path'] && $fileName['fileName'] && file_exists($fileName['path'])) {
            CacheService::delete($key);
            return download($fileName['path'], $fileName['fileName']);
        }
        return Response::create()->code(500);
    }

    /**
     * 获取workerman请求域名
     * @return mixed
     */
    public function getWorkerManUrl()
    {
        return app('json')->success(getWorkerManUrl());
    }

    /**
     * 扫码上传
     * @param Request $request
     * @param int $upload_type
     * @param int $type
     * @return Response
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/06/13
     */
    public function scanUpload(Request $request, $upload_type = 0, $type = 0)
    {
        [$file, $uploadToken, $pid] = $request->postMore([
            ['file', 'file'],
            ['uploadToken', ''],
            ['pid', 0]
        ], true, false);
        $service = app()->make(SystemAttachmentServices::class);
        $service->scanUpload($pid, $file, $uploadToken, $upload_type, $type);
        return app('json')->success('上传成功');
    }

    public function import(Request $request)
    {
        $filePath = $request->param('file_path', '');
        if (empty($filePath)) {
            return app('json')->fail('文件不存在');
        }
        app()->make(SystemRouteServices::class)->import($filePath);
        return app('json')->success('操作成功');
    }

    /**
     * 服务器信息
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/9/24
     */
    public function getSystemInfo()
    {
        $info['server'] = [
            ['name' => '服务器系统', 'require' => '类UNIX', 'value' => PHP_OS],
            ['name' => 'WEB环境', 'require' => 'Apache/Nginx/IIS', 'value' => $_SERVER['SERVER_SOFTWARE']],
        ];
        $gd_info = function_exists('gd_info') ? gd_info() : array();
        $info['environment'] = [
            ['name' => 'PHP版本', 'require' => '8.2-8.4', 'value' => phpversion()],
            ['name' => 'MySql版本', 'require' => '5.6-8.0', 'value' => Db::query("SELECT VERSION()")[0]['VERSION()']],
            ['name' => 'MySqli', 'require' => '开启', 'value' => function_exists('mysqli_connect')],
            ['name' => 'Openssl', 'require' => '开启', 'value' => function_exists('openssl_encrypt')],
            ['name' => 'Session', 'require' => '开启', 'value' => function_exists('session_start')],
            ['name' => 'Safe_Mode', 'require' => '开启', 'value' => !ini_get('safe_mode')],
            ['name' => 'GD', 'require' => '开启', 'value' => !empty($gd_info['GD Version'])],
            ['name' => 'Curl', 'require' => '开启', 'value' => function_exists('curl_init')],
            ['name' => 'Bcmath', 'require' => '开启', 'value' => function_exists('bcadd')],
            ['name' => 'Upload', 'require' => '开启', 'value' => (bool)ini_get('file_uploads')],
        ];

        $info['permissions'] = [
            ['name' => 'backup', 'require' => '读写', 'value' => is_readable(root_path('backup')) && is_writable(root_path('backup'))],
            ['name' => 'public', 'require' => '读写', 'value' => is_readable(root_path('public')) && is_writable(root_path('public'))],
            ['name' => 'runtime', 'require' => '读写', 'value' => is_readable(root_path('runtime')) && is_writable(root_path('runtime'))],
            ['name' => '.env', 'require' => '读写', 'value' => is_readable(root_path() . '.env') && is_writable(root_path() . '.env')],
            ['name' => '.version', 'require' => '读写', 'value' => is_readable(root_path() . '.version') && is_writable(root_path() . '.version')],
            ['name' => '.constant', 'require' => '读写', 'value' => is_readable(root_path() . '.constant') && is_writable(root_path() . '.constant')],
        ];

        $isHeartbeatFresh = static function (string $file, int $ttl = 90): bool {
            if (!is_file($file)) {
                return false;
            }

            $heartbeat = (int) trim((string) file_get_contents($file));
            return $heartbeat >= time() - $ttl && $heartbeat <= time();
        };
        $checkLegacyPid = static function (string $service): bool {
            $pidFile = root_path('runtime') . $service . '.pid';
            if (!is_file($pidFile)) {
                return false;
            }

            $pid = trim((string) file_get_contents($pidFile));
            if (!$pid || !is_numeric($pid)) {
                return false;
            }

            if (function_exists('posix_kill') && posix_kill((int) $pid, 0)) {
                return true;
            }

            if (function_exists('exec')) {
                $output = [];
                exec('ps -ef | grep ' . escapeshellarg($pid) . ' | grep -v grep', $output);
                return !empty($output);
            }

            return false;
        };
        $runtime = root_path('runtime');
        $info['process'] = [
            ['name' => '定时任务', 'require' => '开启', 'value' => $isHeartbeatFresh($runtime . '.timer') || $checkLegacyPid('timer')],
            ['name' => '消息队列', 'require' => '开启', 'value' => sys_config('queue_open', 0) == 1 && $isHeartbeatFresh($runtime . '.queue_heartbeat')],
        ];

        return app('json')->success($info);
    }

    public function customAdminJs()
    {
        return sys_config('custom_admin_js', '');
    }
}
