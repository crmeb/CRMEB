<?php

namespace app\admin\controller\system;

use app\admin\model\system\SystemFile as SystemFileModel;
use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\services\FileService as FileClass;
use crmeb\services\JsonService as Json;

/**
 * 文件校验控制器
 * Class SystemFile
 * @package app\admin\controller\system
 *
 */
class SystemFile extends AuthController
{
    //打开目录
    public function opendir($filedir = '')
    {
        $fileAll = array('dir' => [], 'file' => []);
        //根目录
        $rootdir = app()->getRootPath();
        //当前目录
        $request_dir = app('request')->param('dir');
        //防止查看站点以外的目录
        if(strpos($request_dir,$rootdir) === false){
            $request_dir = $rootdir;
        }
        //判断是否是返回上级
        if (app('request')->param('superior') && !empty($request_dir)) {
            if(strpos(dirname($request_dir),$rootdir) !== false){
                $dir = dirname($request_dir);
            }else{
                $dir = $rootdir;
            }

        } else {
            $dir = !empty($request_dir) ? $request_dir : $rootdir;
            $dir = rtrim($dir,DS) .DS. app('request')->param('filedir');
        }
        $list = scandir($dir);
        foreach ($list as $key => $v) {
            if ($v != '.' && $v != '..') {
                if (is_dir($dir . DS . $v)) {
                    $fileAll['dir'][] = FileClass::list_info($dir .DS. $v);
                }
                if (is_file($dir . DS . $v)) {
                    $fileAll['file'][] = FileClass::list_info($dir .DS. $v);
                }
            }
        }
        //var_dump($fileAll['dir']);
        //兼容windows
        $uname = php_uname('s');
        if (strstr($uname, 'Windows') !== false) $dir = ltrim($dir, '\\');
        $this->assign(compact('fileAll', 'dir'));
        return $this->fetch();
    }

    //读取文件
    public function openfile($file = '')
    {
        $file = $this->request->param('file');
        if (empty($file)) return Json::fail('出现错误');
        $filepath = $file;
        $content = FileClass::read_file($filepath);//防止页面内嵌textarea标签
        $ext = FileClass::get_ext($filepath);
        $extarray = [
            'js' => 'text/javascript'
            , 'php' => 'text/x-php'
            , 'html' => 'text/html'
            , 'sql' => 'text/x-mysql'
            , 'css' => 'text/x-scss'];
        $mode = empty($extarray[$ext]) ? '' : $extarray[$ext];
        $this->assign(compact('content', 'mode', 'filepath'));
        return $this->fetch();
    }

    //保存文件
    public function savefile()
    {
        $comment = $this->request->post('comment');
        $filepath = $this->request->post('filepath');
        if (!empty($comment) && !empty($filepath)) {
            //兼容windows
            $uname = php_uname('s');
            if (strstr($uname, 'Windows') !== false)
                $filepath = ltrim(str_replace('/', DS, $filepath), '.');
            if (FileClass::isWritable($filepath)) {
                $res = FileClass::write_file($filepath, $comment);
                if ($res) {
                    return Json::successful('保存成功!');
                } else {
                    return Json::fail('保存失败');
                }
            } else {
                return Json::fail('没有权限！');
            }

        } else {
            return Json::fail('出现错误');
        }

    }

    public function index()
    {
        $app = $this->getDir('./application');
        $extend = $this->getDir('./extend');
        $public = $this->getDir('./public');
        $arr = array();
        $arr = array_merge($app, $extend);
        $arr = array_merge($arr, $public);
        $fileAll = array();//本地文件
        $cha = array();//不同的文件
        foreach ($arr as $k => $v) {
            $fp = fopen($v, 'r');
            if (filesize($v)) $ct = fread($fp, filesize($v));
            else $ct = null;
            fclose($fp);
            $cthash = md5($ct);
            $update_time = stat($v);
            $fileAll[$k]['cthash'] = $cthash;
            $fileAll[$k]['filename'] = $v;
            $fileAll[$k]['atime'] = $update_time['atime'];
            $fileAll[$k]['mtime'] = $update_time['mtime'];
            $fileAll[$k]['ctime'] = $update_time['ctime'];
        }
        $file = SystemFileModel::all(function ($query) {
            $query->order('atime', 'desc');
        })->toArray();//数据库中的文件
        if (empty($file)) {
            $data_num = array_chunk($fileAll, 10);
            SystemFileModel::beginTrans();
            $res = true;
            foreach ($data_num as $k => $v) {
                $res = $res && SystemFileModel::insertAll($v);
            }
            SystemFileModel::checkTrans($res);
            if ($res) {
                $cha = array();//不同的文件
            } else {
                $cha = $fileAll;
            }
        } else {
            $cha = array();//差异文件
            foreach ($file as $k => $v) {
                foreach ($fileAll as $ko => $vo) {
                    if ($v['filename'] == $vo['filename']) {
                        if ($v['cthash'] != $vo['cthash']) {
                            $cha[$k]['filename'] = $v['filename'];
                            $cha[$k]['cthash'] = $v['cthash'];
                            $cha[$k]['atime'] = $v['atime'];
                            $cha[$k]['mtime'] = $v['mtime'];
                            $cha[$k]['ctime'] = $v['ctime'];
                            $cha[$k]['type'] = '已修改';
                        }
                        unset($fileAll[$ko]);
                        unset($file[$k]);
                    }
                }

            }
            foreach ($file as $k => $v) {
                $cha[$k]['filename'] = $v['filename'];
                $cha[$k]['cthash'] = $v['cthash'];
                $cha[$k]['atime'] = $v['atime'];
                $cha[$k]['mtime'] = $v['mtime'];
                $cha[$k]['ctime'] = $v['ctime'];
                $cha[$k]['type'] = '已删除';
            }
            foreach ($fileAll as $k => $v) {
                $cha[$k]['filename'] = $v['filename'];
                $cha[$k]['cthash'] = $v['cthash'];
                $cha[$k]['atime'] = $v['atime'];
                $cha[$k]['mtime'] = $v['mtime'];
                $cha[$k]['ctime'] = $v['ctime'];
                $cha[$k]['type'] = '新增的';
            }

        }
//   dump($file);
//   dump($fileAll);
        $this->assign('cha', $cha);
        return $this->fetch();
    }


    /**
     * 获取文件夹中的文件 不包括子文件
     * @param $dir
     * @return array
     */
    public function getNextDir()
    {
        $dir = './';
        $list = scandir($dir);
        $dirlist = array();
        $filelist = array();
        foreach ($list as $key => $v) {
            if ($v != '.' && $v != '..') {
                if (is_dir($dir . '/' . $v)) {
                    $dirlist['dir'][$key] = $v;
                }
                if (is_file($dir . '/' . $v)) {
                    $filelist['file'][$key] = $v;
                }
            }
        }
        $filesarr = array_merge($dirlist, $filelist);
        print_r($filesarr);
    }

    /**
     * 获取文件夹中的文件 包括子文件 不能直接用  直接使用  $this->getDir()方法 P156
     * @param $path
     * @param $data
     */
    public function searchDir($path, &$data)
    {
        if (is_dir($path) && !strpos($path, 'uploads')) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    $this->searchDir($path . '/' . $file, $data);
                }
            }
            $dp->close();
        }
        if (is_file($path)) {
            $data[] = $path;
        }
    }

    /**
     * 获取文件夹中的文件 包括子文件
     * @param $dir
     * @return array
     */
    public function getDir($dir)
    {
        $data = array();
        $this->searchDir($dir, $data);
        return $data;
    }

    //测试
    public function ceshi()
    {
        //创建form
        $form = Form::create('/save.php', [
            Form::input('goods_name', '商品名称')
            , Form::input('goods_name1', 'password')->type('password')
            , Form::input('goods_name2', 'textarea')->type('textarea')
            , Form::input('goods_name3', 'email')->type('email')
            , Form::input('goods_name4', 'date')->type('date')
            , Form::city('address', 'cityArea',
                '陕西省', '西安市'
            )
            , Form::dateRange('limit_time', 'dateRange',
                strtotime('- 10 day'),
                time()
            )
            , Form::dateTime('add_time', 'dateTime')
            , Form::color('color', 'color', '#ff0000')
            , Form::checkbox('checkbox', 'checkbox', [1])->options([['value' => 1, 'label' => '白色'], ['value' => 2, 'label' => '红色'], ['value' => 31, 'label' => '黑色']])
            , Form::date('riqi', 'date', '2018-03-1')
            , Form::dateTimeRange('dateTimeRange', '区间时间段')
            , Form::year('year', 'year')
            , Form::month('month', 'month')
            , Form::frame('frame', 'frame', '/admin/system.system_attachment/index.html?fodder=frame')
            , Form::frameInputs('frameInputs', 'frameInputs', '/admin/system.system_attachment/index.html?fodder=frameInputs')
            , Form::frameFiles('month1', 'frameFiles', '/admin/system.system_attachment/index.html?fodder=month1')
            , Form::frameImages('fodder1', 'frameImages', '/admin/system.system_attachment/index.html?fodder=fodder1')->maxLength(3)->width('800px')->height('400px')
            , Form::frameImages('fodder11', 'frameImages', '/admin/system.system_attachment/index.html?fodder=fodder11')->icon('images')
            , Form::frameInputOne('month3', 'frameInputOne', '/admin/system.system_attachment/index.html?fodder=month3')->icon('ionic')
            , Form::frameFileOne('month4', 'frameFileOne', '/admin/system.system_attachment/index.html?fodder=month4')
            , Form::frameImageOne('month5', 'frameImageOne', '/admin/system.system_attachment/index.html?fodder=month5')->icon('image')
            , Form::hidden('month6', 'hidden')
            , Form::number('month7', 'number')
//            ,Form::input input输入框,其他type: text类型Form::text,password类型Form::password,textarea类型Form::textarea,url类型Form::url,email类型Form::email,date类型Form::idate
            , Form::radio('month8', 'radio')->options([['value' => 1, 'label' => '白色'], ['value' => 2, 'label' => '红色'], ['value' => 31, 'label' => '黑色']])
            , Form::rate('month9', 'rate')
            , Form::select('month10', 'select')->options([['value' => 1, 'label' => '白色'], ['value' => 2, 'label' => '红色'], ['value' => 31, 'label' => '黑色']])
            , Form::selectMultiple('month11', 'selectMultiple')
            , Form::selectOne('month12', 'selectOne')
            , Form::slider('month13', 'slider', 2)
            , Form::sliderRange('month23', 'sliderRange', 2, 13)
            , Form::switches('month14', '区间时间段')
            , Form::timePicker('month15', '区间时间段')
            , Form::time('month16', '区间时间段')
            , Form::timeRange('month17', '区间时间段')
//            ,Form::upload('month','区间时间段')
//            ,Form::uploadImages('month','区间时间段')
//            ,Form::uploadFiles('month','区间时间段')
//            ,Form::uploadImageOne('month','区间时间段')
//            ,Form::uploadFileOne('month','区间时间段')

        ]);
        $html = $form->setMethod('get')->setTitle('编辑商品')->view();
        echo $html;
    }
}
