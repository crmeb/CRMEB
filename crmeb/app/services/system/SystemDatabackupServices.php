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

namespace app\services\system;


use app\services\BaseServices;
use crmeb\services\MysqlBackupService;
use think\facade\Db;
use think\facade\Env;

/**
 * 数据库备份
 * Class SystemDatabackupServices
 * @package app\services\system
 */
class SystemDatabackupServices extends BaseServices
{

    /**
     *
     * @var MysqlBackupService
     */
    protected $dbBackup;

    /**
     * 构造方法
     * SystemDatabackupServices constructor.
     */
    public function __construct()
    {
        $this->dbBackup = app()->make(MysqlBackupService::class, [[
            //数据库备份卷大小
            'compress' => 1,
            //数据库备份文件是否启用压缩 0不压缩 1 压缩
            'level' => 5,
        ]]);
    }

    /**
     * 获取数据库列表
     * @return array
     * @throws \think\db\exception\BindParamException
     */
    public function getDataList()
    {
        $list = $this->dbBackup->dataList();
        $count = count($list);
        return compact('list', 'count');
    }

    /**
     * 获取表详情
     * @param string $tablename
     * @return array
     */
    public function getRead(string $tablename)
    {
        $database = Env::get("database.database");
        $list = Db::query("select * from information_schema.columns where table_name = '" . $tablename . "' and table_schema = '" . $database . "'");
        $count = count($list);
        foreach ($list as $key => $f) {
            $list[$key]['EXTRA'] = ($f['EXTRA'] == 'auto_increment' ? '是' : ' ');
        }
        return compact('list', 'count');
    }

    /**
     * @return MysqlBackupService
     */
    public function getDbBackup()
    {
        return $this->dbBackup;
    }

    /**
     * 备份表
     * @param string $tables
     * @return string
     * @throws \think\db\exception\BindParamException
     */
    public function backup(string $tables)
    {
        $tables = explode(',', $tables);
        $data = '';
        ini_set ("memory_limit","-1");
        foreach ($tables as $t) {
            $res = $this->dbBackup->backup($t, 0);
            if ($res == false && $res != 0) {
                $data .= $t . '|';
            }
        }
        return $data;
    }

    /**
     * 获取备份列表
     * @return array
     */
    public function getBackup()
    {
        $files = $this->dbBackup->fileList();
        $data = [];
        foreach ($files as $key => $t) {
            $data[$key]['filename'] = $t['filename'];
            $data[$key]['part'] = $t['part'];
            $data[$key]['size'] = $t['size'] . 'B';
            $data[$key]['compress'] = $t['compress'];
            $data[$key]['backtime'] = $key;
            $data[$key]['time'] = $t['time'];
        }
        krsort($data);//根据时间降序
        return ['count' => count($data), 'list' => array_values($data)];
    }

}
