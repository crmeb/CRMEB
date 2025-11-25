<?php
//文件签名
$fileValue = '';
//最低php版本要求
define('PHP_EDITION', '7.1.0');
//服务环境检测
if (function_exists('saeAutoLoader') || isset($_SERVER['HTTP_BAE_ENV_APPID'])) {
    showHtml('对不起，当前环境不支持本系统，请使用独立服务或云主机！');
}

define('APP_DIR', _dir_path(substr(dirname(__FILE__), 0, -15)));//项目目录
define('SITE_DIR', _dir_path(substr(dirname(__FILE__), 0, -8)));//入口文件目录

if (file_exists('../install.lock')) {
    showHtml('你已经安装过该系统，如果想重新安装，请先删除public目录下的 install.lock 文件，然后再安装。');
}

@set_time_limit(1000);

if ('7.1.0' > phpversion()) {
    exit('您的php版本过低，不能安装本软件，兼容php版本7.1~7.4，谢谢！');
}
if (phpversion() >= '8.0.0') {
    exit('您的php版本太高，不能安装本软件，兼容php版本7.1~7.4，谢谢！');
}

date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=UTF-8');

//数据库
$sqlFile = 'crmeb.sql';
$configFile = '.env';
if (!file_exists(SITE_DIR . 'install/' . $sqlFile) || !file_exists(SITE_DIR . 'install/' . $configFile)) {
    echo '缺少必要的安装文件!';
    exit;
}
$Title = "CRMEB安装向导";
$Powered = "Powered by CRMEB";
$steps = array(
    '1' => '安装许可协议',
    '2' => '运行环境检测',
    '3' => '安装参数设置',
    '4' => '安装详细过程',
    '5' => '安装完成',
);
$step = $_GET['step'] ?? 1;

//地址
$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
$rootPath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
[$request_scheme, $request_host] = getSchemeAndHost();

switch ($step) {
    case '1':
        include_once("./templates/step1.php");
        exit();

    case '2':
        if (phpversion() < '7.1.0' || phpversion() >= '8.0.0') {
            die('本系统需要PHP为 7.1~7.4 版本，当前PHP版本为：' . phpversion());
        }

        $passOne = $passTwo = 'yes';
        $os = PHP_OS;
        $server = $_SERVER["SERVER_SOFTWARE"];
        $phpv = phpversion();
        if (ini_get('file_uploads')) {
            $uploadSize = '<img class="yes" src="images/install/yes.png" alt="对">' . ini_get('upload_max_filesize');
        } else {
            $passOne = 'no';
            $uploadSize = '<img class="no" src="images/install/warring.png" alt="错">禁止上传';
        }
        if (function_exists('session_start')) {
            $session = '<img class="yes" src="images/install/yes.png" alt="对">启用';
        } else {
            $passOne = 'no';
            $session = '<img class="no" src="images/install/warring.png" alt="错">关闭';
        }
        if (!ini_get('safe_mode')) {
            $safe_mode = '<img class="yes" src="images/install/yes.png" alt="对">启用';
        } else {
            $passOne = 'no';
            $safe_mode = '<img class="no" src="images/install/warring.png" alt="错">关闭';
        }
        $tmp = function_exists('gd_info') ? gd_info() : array();
        if (!empty($tmp['GD Version'])) {
            $gd = '<img class="yes" src="images/install/yes.png" alt="对">' . $tmp['GD Version'];
        } else {
            $passOne = 'no';
            $gd = '<img class="no" src="images/install/warring.png" alt="错">未安装';
        }
        if (function_exists('mysqli_connect')) {
            $mysql = '<img class="yes" src="images/install/yes.png" alt="对">已安装';
        } else {
            $passOne = 'no';
            $mysql = '<img class="no" src="images/install/warring.png" alt="错">请安装mysqli扩展';
        }
        if (function_exists('curl_init')) {
            $curl = '<img class="yes" src="images/install/yes.png" alt="对">启用';
        } else {
            $passOne = 'no';
            $curl = '<img class="no" src="images/install/warring.png" alt="错">关闭';
        }
        if (function_exists('bcadd')) {
            $bcmath = '<img class="yes" src="images/install/yes.png" alt="对">启用';
        } else {
            $passOne = 'no';
            $bcmath = '<img class="no" src="images/install/warring.png" alt="错">关闭';
        }
        if (function_exists('openssl_encrypt')) {
            $openssl = '<img class="yes" src="images/install/yes.png" alt="对">启用';
        } else {
            $passOne = 'no';
            $openssl = '<img class="no" src="images/install/warring.png" alt="错">关闭';
        }

        $folder = array(
            'backup',
            'public',
            'runtime',
        );
        foreach ($folder as $dir) {
            if (!is_file(APP_DIR . $dir)) {
                if (!is_dir(APP_DIR . $dir)) {
                    dir_create(APP_DIR . $dir);
                }
            }
            if (!testwrite(APP_DIR . $dir) || !is_readable(APP_DIR . $dir)) {
                $passTwo = 'no';
            }
        }
        $file = array(
            '.env',
            '.version',
            '.constant',
        );
        foreach ($file as $filename) {
            if (!is_writeable(APP_DIR . $filename) || !is_readable(APP_DIR . $filename)) {
                $passTwo = 'no';
            }
        }

        include_once("./templates/step2.php");
        exit();

    case '3':
        $dbName = strtolower(trim($_POST['dbName']));
        $_POST['dbport'] = $_POST['dbport'] ?: '3306';
        if ($_GET['mysqldbpwd']) {
            $dbHost = $_POST['dbHost'];
            $conn = mysqli_init();
            mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            @mysqli_real_connect($conn, $dbHost, $_POST['dbUser'], $_POST['dbPwd'], NULL, $_POST['dbport']);
            if ($error = mysqli_connect_errno($conn)) {
                if ($error == 2002) {
                    die(json_encode(2002));//地址或端口错误
                } else if ($error == 1045) {
                    die(json_encode(1045));//用户名或密码错误
                } else {
                    die(json_encode(-1));//链接失败
                }
            } else {
                if (mysqli_get_server_info($conn) < 5.1) {
                    die(json_encode(-5));//版本过低
                }
                $result = mysqli_query($conn, "SELECT @@global.sql_mode");
                $result = $result->fetch_array();
                $version = mysqli_get_server_info($conn);
                if ($version >= 5.7) {
                    if (strstr($result[0], 'STRICT_TRANS_TABLES') || strstr($result[0], 'STRICT_ALL_TABLES') || strstr($result[0], 'TRADITIONAL') || strstr($result[0], 'ANSI'))
                        exit(json_encode(-2));//数据库配置需要修改
                }
                $result = mysqli_query($conn, "select count(table_name) as c from information_schema.`TABLES` where table_schema='$dbName'");
                $result = $result->fetch_array();
                if ($result['c'] > 0) {
                    mysqli_close($conn);
                    exit(json_encode(-3));//数据库存在
                } else {
                    if (!mysqli_select_db($conn, $dbName)) {
                        //创建数据时同时设置编码
                        if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;")) {
                            exit(json_encode(-4));//无权限创建数据库
                        } else {
                            mysqli_query($conn, "DROP DATABASE `" . $dbName . "` ;");
                            mysqli_close($conn);
                            exit(json_encode(1));//数据库配置成功
                        }
                    } else {
                        mysqli_close($conn);
                        exit(json_encode(1));//数据库配置成功
                    }
                }
            }
        }
        if ($_GET['redisdbpwd']) {

            //redis数据库信息
            $rbhost = $_POST['rbhost'] ?? '127.0.0.1';
            $rbport = $_POST['rbport'] ?? 6379;
            $rbpw = $_POST['rbpw'] ?? '';
            $rbselect = $_POST['rbselect'] ?? 0;

            try {
                if (!class_exists('redis')) {
                    exit(json_encode(-1));
                }
                $redis = new Redis();
                if (!$redis) {
                    exit(json_encode(-1));
                }
                $redis->connect($rbhost, $rbport);
                if ($rbpw) {
                    $redis->auth($rbpw);
                }
                if ($rbselect) {
                    $redis->select($rbselect);
                }
                $res = $redis->set('install', 1, 10);
                if ($res) {
                    exit(json_encode(1));
                } else {
                    exit(json_encode(-3));
                }
            } catch (Throwable $e) {
                exit(json_encode(-3));
            }
        }
        include_once("./templates/step3.php");
        exit();

    case '4':
        if (intval($_GET['install'])) {
            $n = intval($_GET['n']);
            if ($n == 999999)
                exit;
            $arr = array();

            $dbHost = trim($_POST['dbhost']);
            $_POST['dbport'] = $_POST['dbport'] ?: '3306';
            $dbName = strtolower(trim($_POST['dbname']));
            $dbUser = trim($_POST['dbuser']);
            $dbPwd = trim($_POST['dbpw']);
            $dbPrefix = empty($_POST['dbprefix']) ? 'eb_' : trim($_POST['dbprefix']);

            $username = trim($_POST['manager']);
            $password = trim($_POST['manager_pwd']) ?: 'crmeb.com';

            if (!function_exists('mysqli_connect')) {
                $arr['msg'] = "请安装 mysqli 扩展!";
                exit(json_encode($arr));
            }
            $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, NULL, $_POST['dbport']);
            if (mysqli_connect_errno($conn)) {
                $arr['msg'] = "连接数据库失败!" . mysqli_connect_error($conn);
                exit(json_encode($arr));
            }
            mysqli_set_charset($conn, "utf8"); //,character_set_client=binary,sql_mode='';
            $version = mysqli_get_server_info($conn);
            if ($version < 5.1) {
                $arr['msg'] = '数据库版本太低! 必须5.1以上';
                exit(json_encode($arr));
            }

            if (!mysqli_select_db($conn, $dbName)) {
                //创建数据时同时设置编码
                if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;")) {
                    $arr['msg'] = '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！';
                    exit(json_encode($arr));
                }
                if ($n == -1) {
                    $arr['n'] = 0;
                    $arr['msg'] = "成功创建数据库:{$dbName}";
                    exit(json_encode($arr));
                }
                mysqli_select_db($conn, $dbName);
            }

            //读取数据文件
            $sqldata = file_get_contents(SITE_DIR . 'install/' . $sqlFile);
            $sqlFormat = sql_split($sqldata, $dbPrefix);
            //创建写入sql数据库文件到库中 结束

            /**
             * 执行SQL语句
             */
            $counts = count($sqlFormat);
            for ($i = $n; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                if (strstr($sql, 'CREATE TABLE')) {
                    preg_match('/CREATE TABLE (IF NOT EXISTS)? `eb_([^ ]*)`/is', $sql, $matches);
                    mysqli_query($conn, "DROP TABLE IF EXISTS `$matches[2]`");
                    $sql = str_replace('`eb_', '`' . $dbPrefix, $sql);//替换表前缀
                    $ret = mysqli_query($conn, $sql);
                    if ($ret) {
                        $message = '创建数据表[' . $dbPrefix . $matches[2] . ']完成!';
                    } else {
                        $err = mysqli_error($conn);
                        $message = '创建数据表[' . $dbPrefix . $matches[2] . ']失败!失败原因：' . $err;
                    }
                    $i++;
                    $arr = array('n' => $i, 'count' => $counts, 'msg' => $message, 'time' => date('Y-m-d H:i:s'));
                    exit(json_encode($arr));
                } else {
                    if (trim($sql) == '')
                        continue;
                    $sql = str_replace('`eb_', '`' . $dbPrefix, $sql);//替换表前缀
                    $sql = str_replace('http://demo.crmeb.com', $request_scheme . '://' . $request_host, $sql);//替换图片域名
                    $sql = str_replace('http:\\\\/\\\\/demo.crmeb.com', $request_scheme . ':\\\\/\\\\/' . $request_host, $sql);//替换图片域名
                    $ret = mysqli_query($conn, $sql);
                    $message = '';
                    $arr = array('n' => $i, 'count' => $counts, 'msg' => $message, 'time' => date('Y-m-d H:i:s'));
                }
            }


            // 清空测试数据
            if (!$_POST['demo']) {
                $result = mysqli_query($conn, "show tables");
                $tables = mysqli_fetch_all($result);//参数MYSQL_ASSOC、MYSQLI_NUM、MYSQLI_BOTH规定产生数组类型
                $bl_table = array('eb_system_admin'
                , 'eb_system_role'
                , 'eb_cache'
                , 'eb_agent_level'
                , 'eb_page_link'
                , 'eb_page_categroy'
                , 'eb_system_config'
                , 'eb_system_config_tab'
                , 'eb_system_menus'
                , 'eb_system_notification'
                , 'eb_express'
                , 'eb_system_group'
                , 'eb_system_group_data'
                , 'eb_lang_code'
                , 'eb_lang_country'
                , 'eb_lang_type'
                , 'eb_template_message'
                , 'eb_shipping_templates'
                , "eb_shipping_templates_region"
                , 'eb_system_city'
                , 'eb_diy'
                , 'eb_member_ship'
                , 'eb_system_timer'
                , 'eb_member_right'
                , 'eb_agreement'
                , 'eb_store_service_speechcraft'
                , 'eb_system_user_level'
                , 'eb_out_interface'
                , 'eb_cache');
                foreach ($bl_table as $k => $v) {
                    $bl_table[$k] = str_replace('eb_', $dbPrefix, $v);
                }

                foreach ($tables as $key => $val) {
                    if (!in_array($val[0], $bl_table)) {
                        mysqli_query($conn, "truncate table " . $val[0]);
                    }
                }
            }

            $unique = uniqid();

            //读取配置文件，并替换真实配置数据1
            $strConfig = file_get_contents(SITE_DIR . 'install/' . $configFile);
            $strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
            $strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
            $strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
            $strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
            $strConfig = str_replace('#DB_PORT#', $_POST['dbport'], $strConfig);
            $strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
            $strConfig = str_replace('#DB_CHARSET#', 'utf8', $strConfig);

            //缓存配置
            $cachetype = $_POST['cache_type'] == 0 ? 'file' : 'redis';
            $strConfig = str_replace('#CACHE_TYPE#', $cachetype, $strConfig);
            $strConfig = str_replace('#CACHE_PREFIX#', 'cache_' . $unique . ':', $strConfig);
            $strConfig = str_replace('#CACHE_TAG_PREFIX#', 'cache_tag_' . $unique . ':', $strConfig);

            //redis数据库信息
            $rbhost = $_POST['rbhost'] ?? '127.0.0.1';
            $rbport = $_POST['rbport'] ?? '6379';
            $rbpw = $_POST['rbpw'] ?? '';
            $rbselect = $_POST['rbselect'] ?? 0;
            $strConfig = str_replace('#RB_HOST#', $rbhost, $strConfig);
            $strConfig = str_replace('#RB_PORT#', $rbport, $strConfig);
            $strConfig = str_replace('#RB_PWD#', $rbpw, $strConfig);
            $strConfig = str_replace('#RB_SELECT#', $rbselect, $strConfig);

            //需改队列名称
            $strConfig = str_replace('#QUEUE_NAME#', $unique, $strConfig);

            @chmod(APP_DIR . '/.env', 0777); //数据库配置文件的地址
            @file_put_contents(APP_DIR . '/.env', $strConfig); //数据库配置文件的地址

            //插入管理员表字段tp_admin表
            $time = time();
            $ip = get_client_ip();
            $ip = empty($ip) ? "0.0.0.0" : $ip;
            $password = password_hash($_POST['manager_pwd'], PASSWORD_BCRYPT);
            mysqli_query($conn, "truncate table {$dbPrefix}system_admin");
            $addadminsql = "INSERT INTO `{$dbPrefix}system_admin` (`id`, `account`, `head_pic`, `pwd`, `real_name`, `roles`, `last_ip`, `last_time`, `add_time`, `login_count`, `level`, `status`, `is_del`) VALUES
(1, '" . $username . "', '/statics/system_images/admin_head_pic.png', '" . $password . "', 'admin', '1', '" . $ip . "',$time , $time, 0, 0, 1, 0)";
            $res = mysqli_query($conn, $addadminsql);
            $res2 = true;
            if ($request_host) {
                $site_url = '\'"' . $request_scheme . '://' . $request_host . '"\'';
                $res2 = mysqli_query($conn, 'UPDATE `' . $dbPrefix . 'system_config` SET `value`=' . $site_url . ' WHERE `menu_name`="site_url"');
            }
            $arr = array('n' => 999999, 'count' => $counts, 'msg' => '安装完成', 'time' => date('Y-m-d H:i:s'));
            exit(json_encode($arr));

        }
        include_once("./templates/step4.php");
        exit();

    case '5':
        $ip = get_client_ip();
        $host = $_SERVER['HTTP_HOST'];
        $curent_version = getversion();
        $version = trim($curent_version['version']);
        $platform = trim($curent_version['platform']);
        installlog();
        include_once("./templates/step5.php");
        @touch('../install.lock');
        generateSignature();
        exit();
}
//读取版本号
function getversion()
{
    $version_arr = [];
    $curent_version = @file(APP_DIR . '.version');
    foreach ($curent_version as $val) {
        list($k, $v) = explode('=', $val);
        $version_arr[$k] = $v;
    }
    return $version_arr;
}

//写入安装信息
function installlog()
{
    $mt_rand_str = sp_random_string(6);
    $str_constant = "<?php" . PHP_EOL . "define('INSTALL_DATE'," . time() . ");" . PHP_EOL . "define('SERIALNUMBER','" . $mt_rand_str . "');";
    @file_put_contents(APP_DIR . '.constant', $str_constant);
}

//判断权限
function testwrite($d)
{
    if (is_file($d)) {
        if (is_writeable($d)) {
            return true;
        }
        return false;

    } else {
        $tfile = "_test.txt";
        $fp = @fopen($d . "/" . $tfile, "w");
        if (!$fp) {
            return false;
        }
        fclose($fp);
        $rs = @unlink($d . "/" . $tfile);
        if ($rs) {
            return true;
        }
        return false;
    }

}


function sql_split($sql, $tablepre)
{

    if ($tablepre != "tp_")
        $sql = str_replace("tp_", $tablepre, $sql);

    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$num] .= $query;
        }
        $num++;
    }
    return $ret;
}

function _dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

// 获取客户端IP地址
function get_client_ip()
{
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}

function dir_create($path, $mode = 0777)
{
    if (is_dir($path))
        return TRUE;
    $ftp_enable = 0;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

function dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function sp_password($pw, $pre)
{
    $decor = md5($pre);
    $mi = md5($pw);
    return substr($decor, 0, 12) . $mi . substr($decor, -4, 4);
}

function sp_random_string($len = 8)
{
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

// 递归删除文件夹
function delFile($dir, $file_type = '')
{
    if (is_dir($dir)) {
        $files = scandir($dir);
        //打开目录 //列出目录中的所有文件并去掉 . 和 ..
        foreach ($files as $filename) {
            if ($filename != '.' && $filename != '..') {
                if (!is_dir($dir . '/' . $filename)) {
                    if (empty($file_type)) {
                        unlink($dir . '/' . $filename);
                    } else {
                        if (is_array($file_type)) {
                            //正则匹配指定文件
                            if (preg_match($file_type[0], $filename)) {
                                unlink($dir . '/' . $filename);
                            }
                        } else {
                            //指定包含某些字符串的文件
                            if (false != stristr($filename, $file_type)) {
                                unlink($dir . '/' . $filename);
                            }
                        }
                    }
                } else {
                    delFile($dir . '/' . $filename);
                    rmdir($dir . '/' . $filename);
                }
            }
        }
    } else {
        if (file_exists($dir)) unlink($dir);
    }
}

//错误提示方法
function showHtml($str)
{
    echo '
		<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>
        ' . $str . '
        </body>
        </html>';
    exit;
}

/**
 * 计算签名
 * @param string $path
 * @throws Exception
 */
function getFileSignature(string $path)
{
    global $fileValue;
    if (!is_dir($path)) {
        $fileValue .= @md5_file($path);
    } else {
        if (!$dh = opendir($path)) throw new Exception($path . " File open failed!");
        while (($file = readdir($dh)) != false) {
            if ($file == "." || $file == "..") {
                continue;
            } else {
                getFileSignature($path . DIRECTORY_SEPARATOR . $file);
            }
        }
        closedir($dh);
    }
}

/**
 * 写入签名
 * @return void
 * @throws Exception
 */
function generateSignature()
{
    $file = APP_DIR . '.version';
    if (!$data = @file($file)) {
        throw new Exception('.version读取失败');
    }
    $list = [];
    if (!empty($data)) {
        foreach ($data as $datum) {
            list($name, $value) = explode('=', $datum);
            $list[$name] = rtrim($value);
        }
    }

    if (!isset($list['project_signature'])) {
        $list['project_signature'] = '';
    }

    global $fileValue;
    getFileSignature(APP_DIR . DIRECTORY_SEPARATOR . 'app');
    getFileSignature(APP_DIR . DIRECTORY_SEPARATOR . 'crmeb');

    $list['project_signature'] = md5($fileValue);

    $str = "";
    foreach ($list as $key => $item) {
        $str .= "{$key}={$item}\n";
    }

    file_put_contents($file, $str);
}

function getSchemeAndHost()
{
    // 检查反向代理设置的头信息
    $request_scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'http';
    $request_host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';

    // 如果没有反向代理头信息，则使用标准的 HTTP 头信息
    if (empty($request_host)) {
        $request_scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $request_host = $_SERVER['HTTP_HOST'] ?? '';
    }

    // 如果仍然没有获取到域名，则使用服务器变量作为备选
    if (empty($request_host)) {
        $request_host = $_SERVER['SERVER_NAME'] ?? 'localhost';

        // 如果使用了端口号（非标准端口），则添加端口号
        $port = $_SERVER['SERVER_PORT'] ?? '';
        if (($request_scheme === 'https' && $port !== '443') || ($request_scheme === 'http' && $port !== '80')) {
            $request_host .= ':' . $port;
        }
    }

    // 构建并返回scheme和host
    return [$request_scheme, $request_host];
}

?>
