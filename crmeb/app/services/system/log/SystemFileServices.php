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

namespace app\services\system\log;


use app\dao\system\log\SystemFileDao;
use app\services\BaseServices;
use app\services\system\admin\SystemAdminServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;
use crmeb\services\FileService as FileClass;
use crmeb\utils\JwtAuth;
use Firebase\JWT\ExpiredException;
use think\facade\Log;

/**
 * 文件校验
 * Class SystemFileServices
 * @package app\services\system\log
 */
class SystemFileServices extends BaseServices
{
    /**
     * 构造方法
     * SystemFileServices constructor.
     * @param SystemFileDao $dao
     */
    public function __construct(SystemFileDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $admin
     * @param string $password
     * @param string $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     *
     * @date 2022/09/07
     * @author yyw
     */
    public function Login(string $password, string $type)
    {
        if (config('filesystem.password') !== $password) {
            throw new AdminException(400140);
        }
        $md5Password = md5($password);
        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        $tokenInfo = $jwtAuth->createToken($md5Password, $type, ['pwd' => $md5Password]);
        CacheService::set(md5($tokenInfo['token']), $tokenInfo['token'], 3600);
        return [
            'token' => md5($tokenInfo['token']),
            'expires_time' => $tokenInfo['params']['exp'],
        ];

    }

    /**
     * 获取Admin授权信息
     * @param string $token
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function parseToken(string $token): bool
    {
        /** @var CacheService $cacheService */
        $cacheService = app()->make(CacheService::class);

        if (!$token || $token === 'undefined') {
            throw new AuthException(110008);
        }

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        //设置解析token
        [$id, $type, $pwd] = $jwtAuth->parseToken($token);

        //检测token是否过期
        $md5Token = md5($token);
        if (!$cacheService->has($md5Token) || !($cacheService->get($md5Token))) {
            throw new AuthException(110008);
        }

        //验证token
        try {
            $jwtAuth->verifyToken();
        } catch (\Throwable $e) {
            if (!request()->isCli()) {
                $cacheService->delete($md5Token);
            }
            throw new AuthException(110008);
        }

        if ($id !== md5(config('filesystem.password'))) {
            throw new AuthException(110008);
        }

        if ($pwd !== md5(config('filesystem.password'))) {
            throw new AuthException(110008);
        }

        return true;
    }


    /**
     * 获取文件校验列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFileList()
    {
        $rootPath = app()->getRootPath();
        $key = 'system_file_app_crmeb_public';
        $arr = CacheService::get(md5($key));
        if (!$arr) {
            $app = $this->getDir($rootPath . 'app');
            $extend = $this->getDir($rootPath . 'crmeb');
            $arr = array_merge($app, $extend);
            CacheService::set(md5($key), $arr, 3600 * 24);
        }
        $fileAll = [];//本地文件
        $cha = [];//不同的文件
        $len = strlen($rootPath);
        $file = $this->dao->getAll();//数据库中的文件
        if (empty($file)) {
            foreach ($arr as $k => $v) {
                $update_time = stat($v);
                $fileAll[$k]['cthash'] = md5_file($v);
                $fileAll[$k]['filename'] = substr($v, $len);
                $fileAll[$k]['atime'] = $update_time['atime'];
                $fileAll[$k]['mtime'] = $update_time['mtime'];
                $fileAll[$k]['ctime'] = $update_time['ctime'];
            }
            $data_num = array_chunk($fileAll, 100);
            $res = true;
            $res = $this->transaction(function () use ($data_num, $res) {
                foreach ($data_num as $k => $v) {
                    $res = $res && $this->dao->saveAll($v);
                }
                return $res;
            });
            if ($res) {
                $cha = [];//不同的文件
            } else {
                $cha = $fileAll;
            }
        } else {
            $file = array_combine(array_column($file, 'filename'), $file);
            foreach ($arr as $ko => $vo) {
                $update_time = stat($vo);
                $cthash = md5_file($vo);
                $cha[] = [
                    'filename' => $vo,
                    'cthash' => $cthash,
                    'atime' => date('Y-m-d H:i:s', $update_time['atime']),
                    'mtime' => date('Y-m-d H:i:s', $update_time['mtime']),
                    'ctime' => date('Y-m-d H:i:s', $update_time['ctime']),
                    'type' => '新增的',
                ];
                if (isset($file[$vo]) && $file[$vo] != $cthash) {
                    $cha[] = [
                        'type' => '已修改',
                    ];
                    unset($file[$vo]);
                }
            }
            foreach ($file as $k => $v) {
                $cha[] = [
                    'filename' => $v['filename'],
                    'cthash' => $v['cthash'],
                    'atime' => date('Y-m-d H:i:s', $v['atime']),
                    'mtime' => date('Y-m-d H:i:s', $v['mtime']),
                    'ctime' => date('Y-m-d H:i:s', $v['ctime']),
                    'type' => '已删除',

                ];
            }
        }
        $ctime = array_column($cha, 'ctime');
        array_multisort($ctime, SORT_DESC, $cha);
        return $cha;
    }

    /**
     * 获取文件夹中的文件 包括子文件
     * @param $dir
     * @return array
     */
    public function getDir($dir)
    {
        $data = [];
        $this->searchDir($dir, $data);
        return $data;
    }

    /**
     * 获取文件夹中的文件 包括子文件 不能直接用  直接使用  $this->getDir()方法 P156
     * @param $path
     * @param $data
     */
    public function searchDir($path, &$data)
    {
        if (is_dir($path) && !strpos($path, 'uploads')) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $this->searchDir($path . '/' . $file, $data);
                }
            }
        }
        if (is_file($path)) {
            $data[] = $path;
        }
    }

    //打开目录
    public function opendir()
    {
        $fileAll = array('dir' => [], 'file' => []);
        //根目录
        $rootdir = $this->formatPath(app()->getRootPath());
//        return $rootdir;
        //当前目录
        $request_dir = app('request')->param('dir');
        //防止查看站点以外的目录
        if (strpos($request_dir, $rootdir) === false) {
            $request_dir = $rootdir;
        }
        //判断是否是返回上级
        if (app('request')->param('superior') && !empty($request_dir)) {
            if (strpos(dirname($request_dir), $rootdir) !== false) {
                $dir = dirname($request_dir);
            } else {
                $dir = $rootdir;
            }

        } else {
            $dir = !empty($request_dir) ? $request_dir : $rootdir;
            $dir = rtrim($dir, DS) . DS . app('request')->param('filedir');
        }
        $list = scandir($dir);
        foreach ($list as $key => $v) {
            if ($v != '.' && $v != '..') {
                if (is_dir($dir . DS . $v)) {
                    $fileAll['dir'][] = FileClass::listInfo($dir . DS . $v);
                }
                if (is_file($dir . DS . $v)) {
                    $fileAll['file'][] = FileClass::listInfo($dir . DS . $v);
                }
            }
        }
        //兼容windows
        $uname = php_uname('s');
        if (strstr($uname, 'Windows') !== false) {
            $dir = ltrim($dir, '\\');
            $rootdir = str_replace('\\', '\\\\', $rootdir);
        }
        $list = array_merge($fileAll['dir'], $fileAll['file']);
        $navList = [];
        foreach ($list as $key => $value) {
            $list[$key]['real_path'] = str_replace($rootdir, '', $value['pathname']);
            $list[$key]['mtime'] = date('Y-m-d H:i:s', $value['mtime']);

            $navList[$key]['title'] = $value['filename'];
            if ($value['isDir']) $navList[$key]['loading'] = false;
            $navList[$key]['children'] = [];
            $navList[$key]['path'] = $value['path'];
            $navList[$key]['isDir'] = $value['isDir'];
            $navList[$key]['pathname'] = $value['pathname'];
            $navList[$key]['contextmenu'] = true;
        }
        return compact('dir', 'list', 'navList');
    }

    //读取文件
    public function openfile($filepath)
    {
        $filepath = $this->formatPath($filepath);
        $content = FileClass::readFile($filepath);//防止页面内嵌textarea标签
        $ext = FileClass::getExt($filepath);
        $encoding = mb_detect_encoding($content, mb_detect_order());
        //前端组件支持的语言类型
        //['plaintext', 'json', 'abap', 'apex', 'azcli', 'bat', 'cameligo', 'clojure', 'coffeescript', 'c', 'cpp', 'csharp', 'csp', 'css', 'dart', 'dockerfile', 'fsharp', 'go', 'graphql', 'handlebars', 'hcl', 'html', 'ini', 'java', 'javascript', 'julia', 'kotlin', 'less', 'lexon', 'lua', 'markdown', 'mips', 'msdax', 'mysql', 'objective-c', 'pascal', 'pascaligo', 'perl', 'pgsql', 'php', 'postiats', 'powerquery', 'powershell', 'pug', 'python', 'r', 'razor', 'redis', 'redshift', 'restructuredtext', 'ruby', 'rust', 'sb', 'scala', 'scheme', 'scss', 'shell', 'sol', 'aes', 'sql', 'st', 'swift', 'systemverilog', 'verilog', 'tcl', 'twig', 'typescript', 'vb', 'xml', 'yaml']

        $extarray = [
            'js' => 'javascript'
            , 'htm' => 'html'
            , 'shtml' => 'html'
            , 'html' => 'html'
            , 'xml' => 'xml'
            , 'php' => 'php'
            , 'sql' => 'mysql'
            , 'css' => 'css'
            , 'txt' => 'plaintext'
            , 'vue' => 'html'
            , 'json' => 'json'
            , 'lock' => 'json'
            , 'md' => 'markdown'
            , 'bat' => 'bat'
            , 'ini' => 'ini'


        ];
        $mode = empty($extarray[$ext]) ? 'php' : $extarray[$ext];
        return compact('content', 'mode', 'filepath', 'encoding');
    }

    //保存文件
    public function savefile($filepath, $comment)
    {
        $filepath = $this->formatPath($filepath);
        if (!FileClass::isWritable($filepath)) {
            throw new AdminException(400611);
        }
        return FileClass::writeFile($filepath, $comment);
    }

    // 文件重命名
    public function rename($newname, $oldname)
    {
        if (($newname != $oldname) && is_writable($oldname)) {
            return rename($oldname, $newname);
        }
        return true;
    }


    /**
     * 删除文件或文件夹
     * @param string $path
     * @return bool
     *
     * @date 2022/09/20
     * @author yyw
     */
    public function delFolder(string $path)
    {
        $path = $this->formatPath($path);
        if (is_file($path)) {
            return unlink($path);
        }
        $dir = opendir($path);
        while ($fileName = readdir($dir)) {
            $file = $path . '/' . $fileName;
            if ($fileName != '.' && $fileName != '..') {
                if (is_dir($file)) {
                    self::delFolder($file);
                } else {
                    unlink($file);
                }
            }
        }
        closedir($dir);
        return rmdir($path);
    }

    /**
     * 新建文件夹
     * @param string $path
     * @param string $name
     * @param int $permissions
     * @return bool
     *
     * @date 2022/09/20
     * @author yyw
     */
    public function createFolder(string $path, string $name, int $permissions = 0755)
    {
        $path = $this->formatPath($path, $name);
        /** @var FileClass $fileClass */
        $fileClass = app()->make(FileClass::class);
        return $fileClass->createDir($path, $permissions);
    }

    /**
     * 新建文件
     * @param string $path
     * @param string $name
     * @return bool
     *
     * @date 2022/09/20
     * @author yyw
     */
    public function createFile(string $path, string $name)
    {
        $path = $this->formatPath($path, $name);
        /** @var FileClass $fileClass */
        $fileClass = app()->make(FileClass::class);
        return $fileClass->createFile($path);
    }

    public function copyFolder($surDir, $toDir)
    {
        return FileClass::copyDir($surDir, $toDir);
    }

    /**
     * 格式化路径
     * @param string $path
     * @param string $name
     * @return string
     *
     * @date 2022/09/20
     * @author yyw
     */
    public function formatPath(string $path = '', string $name = ''): string
    {
        if ($path) {
            $path = rtrim($path, DS);
            if ($name) $path = $path . DS . $name;
            $uname = php_uname('s');
//            $search = '/';
            if (strstr($uname, 'Windows') !== false)
                $path = ltrim(str_replace('\\', '\\\\', $path), '.');

        }
        return $path;
    }
}
