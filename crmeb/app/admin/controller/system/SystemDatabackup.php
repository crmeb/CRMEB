<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use crmeb\services\JsonService as Json;
use \crmeb\services\MysqlBackupService as Backup;
use think\facade\Config;
use think\facade\Session;
use think\facade\Db;

/**
 * 文件校验控制器
 * Class SystemDatabackup
 * @package app\admin\controller\system
 *
 */
class SystemDatabackup extends AuthController
{
    /**
     * @var Backup
     */
    protected $DB;

    public function initialize()
    {
        parent::initialize();
        $config = array(
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
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取数据库表
     */
    public function tablelist()
    {
        $db = $this->DB;
        return Json::result(0, 'sucess', $db->dataList(), count($db->dataList()));
    }

    /**
     * 查看表结构
     */
    public function seetable()
    {
        request()->filter(['strip_tags', 'trim', 'htmlspecialchars']);
        $database = Config::get("database.connections." . Config::get('database.default') . '.database');
        $tablename = request()->param('tablename');
        $res = Db::query("select * from information_schema.columns where table_name = '" . $tablename . "' and table_schema = '" . $database . "'");
        $html = '';
        $html .= '<table border="1" cellspacing="0" cellpadding="0" align="center">';
        $html .= '<tbody><tr><th>字段名</th><th>数据类型</th><th>默认值</th><th>允许非空</th><th>自动递增</th><th>备注</th></tr>';
        $html .= '';
        foreach ($res AS $f) {
            $html .= '<td class="c1">' . $f['COLUMN_NAME'] . '</td>';
            $html .= '<td class="c2">' . $f['COLUMN_TYPE'] . '</td>';
            $html .= '<td class="c3">' . $f['COLUMN_DEFAULT'] . '</td>';
            $html .= '<td class="c4">' . $f['IS_NULLABLE'] . '</td>';
            $html .= '<td class="c5">' . ($f['EXTRA'] == 'auto_increment' ? '是' : ' ') . '</td>';
            $html .= '<td class="c6">' . $f['COLUMN_COMMENT'] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></p>';
        $html .= '<p style="text-align:left;margin:20px auto;">总共：' . count($res) . '个字段</p>';
        $html .= '</body></html>';
        echo '<style>
                body,td,th {font-family:"宋体"; font-size:12px;}
                table,h1,p{width:960px;margin:0px auto;}
                table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;}
                table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }
                table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:12px; border:1px solid #CCC;padding-left:5px;}
                table td{height:20px; font-size:12px; border:1px solid #CCC;background-color:#fff;padding-left:5px;}
                .c1{ width: 150px;}
                .c2{ width: 150px;}
                .c3{ width: 80px;}
                .c4{ width: 100px;}
                .c5{ width: 100px;}
                .c6{ width: 300px;}
            </style>';
        echo $html;
    }

    /**
     * 优化表
     */
    public function optimize()
    {
        $tables = request()->post('tables/a');
        $db = $this->DB;
        try {
            $db->optimize($tables);
            return Json::successful('优化成功');
        } catch (\Exception $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 修复表
     */
    public function repair()
    {
        $tables = request()->post('tables/a');
        $db = $this->DB;
        try {
            $db->repair($tables);
            return Json::successful('修复成功');
        } catch (\Exception $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 备份表
     */
    public function backup()
    {
        $tables = request()->post('tables/a');
        $db = $this->DB;
        $data = '';
        foreach ($tables as $t) {
            $res = $db->backup($t, 0);
            if ($res == false && $res != 0) {
                $data .= $t . '|';
            }
        }
        return Json::successful($data ? '备份失败' . $data : '备份成功');
    }

    /**
     * 获取备份记录表
     */
    public function fileList()
    {
        $db = $this->DB;
        $files = $db->fileList();
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
        return Json::result(0, 'sucess', $data, count($data));
    }

    /**
     * @param string|null $msg
     * @param string|null $url
     * @param array|null $data
     */
    public function success(?string $msg = null, ?string $url = null, ?array $data = [])
    {
        return Json::success($msg, $data);
    }

    /**
     * @param string|null $msg
     */
    public function error(?string $msg = null)
    {
        return Json::fail($msg);
    }

    /**
     * 删除备份记录表
     */
    public function delFile()
    {
        $feilname = intval(request()->post('feilname'));
        $files = $this->DB->delFile($feilname);
        return Json::result(0, 'sucess');
    }

    /**
     * 导入备份记录表
     */
    public function import()
    {
        $part = request()->post('part') != '' ? intval(request()->post('part')) : null;
        $start = request()->post('start') != '' ? intval(request()->post('start')) : null;
        $time = intval(request()->post('time'));
        $db = $this->DB;
        if (is_numeric($time) && is_null($part) && is_null($start)) {
            $list = $db->getFile('timeverif', $time);
            if (is_array($list)) {
                session::set('backup_list', $list);
                $this->success('初始化完成！', '', array('part' => 1, 'start' => 0));
            } else {
                $this->error('备份文件可能已经损坏，请检查！');
            }
        } else if (is_numeric($part) && is_numeric($start)) {
            $list = session::get('backup_list');
            $start = $db->setFile($list)->import($start);
            if (false === $start) {
                $this->error('还原数据出错！');
            } elseif (0 === $start) {
                if (isset($list[++$part])) {
                    $data = array('part' => $part, 'start' => 0);
                    $this->success("正在还原...#{$part}", '', $data);
                } else {
                    session::delete('backup_list');
                    $this->success('还原完成！');
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if ($start[1]) {
                    $rate = floor(100 * ($start[0] / $start[1]));
                    $this->success("正在还原...#{$part}({$rate}%)", '', $data);
                } else {
                    $data['gz'] = 1;
                    $this->success("正在还原...#{$part}", '', $data);
                }
                $this->success("正在还原...#{$part}", '');
            }
        } else {
            $this->error('参数错误！');
        }
    }

    /**
     * 下载备份记录表
     */
    public function downloadFile()
    {
        $time = intval(request()->param('feilname'));
        $this->DB->downloadFile($time);
    }
}
