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
namespace app\adminapi\controller\system;

use think\facade\App;
use think\facade\Db;
use think\facade\Session;
use app\adminapi\controller\AuthController;
use app\services\system\SystemDatabackupServices;


/**
 * 数据备份
 * Class SystemDatabackup
 * @package app\admin\controller\system
 *
 */
class SystemDatabackupController extends AuthController
{
    /**
     * @var SystemDatabackupServices
     */
    protected $services;


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
        [$tablename] = $this->request->getMore([
            ['tablename', ''],
        ], true);
        return app('json')->success($this->services->getRead($tablename));
    }

    /**
     * 更新数据表或者表字段备注
     * @return \think\Response
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/04/11
     */
    public function updateMark()
    {
        [$table, $field, $mark, $is_field] = $this->request->postMore([
            ['table', ''],
            ['field', ''],
            ['mark', ''],
            ['is_field', 0],
        ], true);
        // 空参校验，防止空表名/字段名拼入 SQL 引发语法错误
        if ($table === '' || ($is_field == 1 && $field === '')) {
            return app('json')->fail('数据表名称或字段名称不能为空');
        }
        // 校验数据表是否存在，防止非法表名直接拼入 SQL
        $tableExists = Db::query('SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table', ['table' => $table]);
        if (empty($tableExists)) {
            return app('json')->fail('数据表不存在');
        }
        if ($is_field == 0) {
            $sql = "ALTER TABLE `{$table}` COMMENT '" . addslashes($mark) . "'";
        } else {
            // 通过 information_schema 绑定参数查询字段信息，字段不存在时直接拦截
            $fieldInfo = Db::query(
                'SELECT column_name AS `Field`, is_nullable AS `Null`, column_default AS `Default`, extra AS `Extra`, column_type AS `Type` FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :field',
                ['table' => $table, 'field' => $field]
            );
            if (empty($fieldInfo)) {
                return app('json')->fail('字段不存在');
            }
            $sql = "ALTER TABLE `{$table}` MODIFY COLUMN ";
            // 字段类型取 information_schema 权威值，避免外部传入非法类型拼入 SQL
            $sql .= $field . ' ' . $fieldInfo[0]['Type'] . ' ';
            if ($fieldInfo[0]['Null'] == 'NO') {
                $sql .= 'NOT NULL ';
                if (!is_null($fieldInfo[0]['Default'])) {
                    $sql .= "DEFAULT '" . addslashes($fieldInfo[0]['Default']) . "' ";
                }
            }
            if ($fieldInfo[0]['Extra']) {
                $sql .= $fieldInfo[0]['Extra'] . ' ';
            }
            $sql .= "COMMENT '" . addslashes($mark) . "'";
        }
        Db::execute($sql);
        return app('json')->success('备注成功');
    }

    /**
     * 优化表
     */
    public function optimize()
    {
        [$tables] = $this->request->postMore([
            ['tables', ''],
        ], true);
        $res = $this->services->getDbBackup()->optimize($tables);
        return app('json')->success($res ? '优化成功' : '优化失败');
    }

    /**
     * 修复表
     */
    public function repair()
    {
        [$tables] = $this->request->postMore([
            ['tables', ''],
        ], true);
        $res = $this->services->getDbBackup()->repair($tables);
        return app('json')->success($res ? '修复成功' : '修复失败');
    }

    /**
     * 备份表
     */
    public function backup()
    {
        [$tables] = $this->request->postMore([
            ['tables', ''],
        ], true);
        // 空参校验，防止空表名拼入备份 SQL 引发语法错误
        if (trim((string) $tables) === '') {
            return app('json')->fail('请选择需要备份的数据表');
        }
        $data = $this->services->backup($tables);
        return app('json')->success('备份成功');
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
        return app('json')->success('删除成功');
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
                return app('json')->success('初始化完成', array('part' => 1, 'start' => 0));
            } else {
                return app('json')->fail('备份文件可能已经损坏，请检查');
            }
        } else if (is_numeric($part) && is_numeric($start) && $part && $start) {
            $list = session::get('backup_list');
            $start = $db->setFile($list)->import($start);
            if (false === $start) {
                return app('json')->fail('还原数据出错');
            } elseif (0 === $start) {
                if (isset($list[++$part])) {
                    $data = array('part' => $part, 'start' => 0);
                    return app('json')->success('正在还原...', $data);
                } else {
                    session::delete('backup_list');
                    return app('json')->success('还原完成');
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if ($start[1]) {
                    $rate = floor(100 * ($start[0] / $start[1]));
                    return app('json')->success('正在还原...', $data);
                } else {
                    $data['gz'] = 1;
                    return app('json')->success('正在还原...', $data);
                }
            }
        } else {
            return app('json')->fail('参数错误');
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
