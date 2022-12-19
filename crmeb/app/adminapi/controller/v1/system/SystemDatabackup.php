<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\system;

use think\facade\App;
use think\facade\Session;
use app\adminapi\controller\AuthController;
use app\services\system\SystemDatabackupServices;


/**
 * 数据备份
 * Class SystemDatabackup
 * @package app\admin\controller\system
 *
 */
class SystemDatabackup extends AuthController
{
    /**
     * 构造方法
     * SystemDatabackup constructor.
     * @param App $app
     * @param SystemDatabackupServices $services
     */
    public function __construct(App $app, SystemDatabackupServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取数据库表
     */
    public function index()
    {
        return app('json')->success($this->services->getDataList());
    }

    /**
     * 查看表结构 详情
     */
    public function read()
    {
        $tablename = request()->param('tablename', '', 'htmlspecialchars');
        return app('json')->success($this->services->getRead($tablename));
    }

    /**
     * 优化表
     */
    public function optimize()
    {
        $tables = $this->request->param('tables', '', 'htmlspecialchars');
        $res = $this->services->getDbBackup()->optimize($tables);
        return app('json')->success($res ? 100047 : 100048);
    }

    /**
     * 修复表
     */
    public function repair()
    {
        $tables = $this->request->param('tables', '', 'htmlspecialchars');
        $res = $this->services->getDbBackup()->repair($tables);
        return app('json')->success($res ? 100049 : 100050);
    }

    /**
     * 备份表
     */
    public function backup()
    {
        $tables = $this->request->param('tables', '', 'htmlspecialchars');
        $data = $this->services->backup($tables);
        return app('json')->success(100051);
    }

    /**
     * 获取备份记录表
     */
    public function fileList()
    {
        return app('json')->success($this->services->getBackup());
    }

    /**
     * 删除备份记录表
     */
    public function delFile()
    {
        $filename = intval(request()->post('filename'));
        $files = $this->services->getDbBackup()->delFile($filename);
        return app('json')->success(100002);
    }

    /**
     * 导入备份记录表
     */
    public function import()
    {
        [$part, $start, $time] = $this->request->postMore([
            [['part', 'd'], 0],
            [['start', 'd'], 0],
            [['time', 'd'], 0],
        ], true);
        $db = $this->services->getDbBackup();
        if (is_numeric($time) && !$start) {
            $list = $db->getFile('timeverif', $time);
            if (is_array($list)) {
                session::set('backup_list', $list);
                return app('json')->success(400307, array('part' => 1, 'start' => 0));
            } else {
                return app('json')->fail(400308);
            }
        } else if (is_numeric($part) && is_numeric($start) && $part && $start) {
            $list = session::get('backup_list');
            $start = $db->setFile($list)->import($start);
            if (false === $start) {
                return app('json')->fail(400309);
            } elseif (0 === $start) {
                if (isset($list[++$part])) {
                    $data = array('part' => $part, 'start' => 0);
                    return app('json')->success(400310, $data);
                } else {
                    session::delete('backup_list');
                    return app('json')->success(400311);
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if ($start[1]) {
                    $rate = floor(100 * ($start[0] / $start[1]));
                    return app('json')->success(400310, $data);
                } else {
                    $data['gz'] = 1;
                    return app('json')->success(400310, $data);
                }
            }
        } else {
            return app('json')->fail(100100);
        }
    }

    /**
     * 下载备份记录表
     */
    public function downloadFile()
    {
        $time = intval(request()->param('time'));
        return app('json')->success(['key' => $this->services->getDbBackup()->downloadFile($time, 0, true)]);
    }

}
