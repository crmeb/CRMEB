<?php
if (file_exists('./install.lock')) {
    echo '
		<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>
        你已经安装过该系统，如果想重新安装，请先删除站点install目录下的 install.lock 文件，然后再安装。
        </body>
        </html>';
    exit;
}
@set_time_limit(1000);
if (phpversion() <= PHP_EDITION)
    set_magic_quotes_runtime(0);
if (PHP_EDITION > phpversion())
    exit('您的php版本过低，不能安装本软件，请升级到'.PHP_EDITION.'或更高版本再安装，谢谢！');

date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=UTF-8');
define('SITEDIR', _dir_path(substr(dirname(__FILE__), 0, -8)));
define("TUZI_CMS_VERSION", '20150811');

//数据库
$configFile = 'config.php';
if (!file_exists(SITEDIR . 'install/CRMEB.sql' ) || !file_exists(SITEDIR . 'install/' . $configFile)) {
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
$step = isset($_GET['step']) ? $_GET['step'] : 1;

//地址
$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
$rootpath = @preg_replace("/\/(I|i)nstall\/index_sae\.php(.*)$/", "", $scriptName);
$domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
if ((int) $_SERVER['SERVER_PORT'] != 80) {
    $domain .= ":" . $_SERVER['SERVER_PORT'];
}
$domain = $domain . $rootpath;

// mysql
$conn = @mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
// kvdb
$kv = new SaeKV();   $kv_ini = $kv->init();
// memcache
$mmc=@memcache_init();
// storage
$s = new SaeStorage();


switch ($step) {

    case '1':
        include_once ("./templates/s1.php");
        exit();

    case '2':

        if (phpversion() < PHP_EDITION) {
            die('本系统需要PHP版本 >='.PHP_EDITION.'环境，当前PHP版本为：' . phpversion());
        }

        $phpv = @ phpversion();

        $err = 0;
		if($conn){
			$saeMysql = '<span class="correct_span">&radic;</span> 已开启';
		}else {
			$saeMysql = '<span class="correct_span error_span">&radic;</span> 未开启';
			$err++;
		}
		if(is_array($s->getListByPath('data'))){
			$storage = '<span class="correct_span">&radic;</span> 已开启';
		} else {
			$storage = '<span class="correct_span error_span">&radic;</span> 需开启并发创建名称为data的domain';
			$err++;
		}
		if($kv_ini){
			$KVDB = '<span class="correct_span">&radic;</span> 已开启';
		} else {
			$KVDB = '<span class="correct_span error_span">&radic;</span> 未开启';
			$err++;
		}
		if($mmc){
			$Memcache = '<span class="correct_span">&radic;</span> 已开启';
		} else {
			$Memcache = '<span class="correct_span error_span">&radic;</span> 未开启';
			$err++;
		}
        include_once ("./templates/s2_sae.php");
        exit();

    case '3':

        if ($_GET['testdbpwd']) {
            $dbHost = $_POST['dbHost'] . ':' . $_POST['dbport'];
            $conn = @mysql_connect($dbHost, $_POST['dbUser'], $_POST['dbPwd']);
            if ($conn) {
                die("1");
            } else {
                die("");
            }
        }
        include_once ("./templates/s3_sae.php");
        exit();


    case '4':
        if (intval($_GET['install'])) {
            $n = intval($_GET['n']);
            if ($i == 999999) exit;
            $arr = array();
			
			$dbName = SAE_MYSQL_DB;
            $dbPrefix = empty($_POST['dbprefix']) ? 'tp_' : trim($_POST['dbprefix']);

            $username = trim($_POST['manager']);
            $password = trim($_POST['manager_pwd']);
            $email	  = trim($_POST['manager_email']);

            if (!$conn) {
                $arr['msg'] = "连接数据库失败!";
                echo json_encode($arr);
                exit;
            }
            mysql_query("SET NAMES 'utf8'"); //,character_set_client=binary,sql_mode='';
            $version = mysql_get_server_info($conn);
            if ($version < 4.1) {
                $arr['msg'] = '数据库版本太低!';
                echo json_encode($arr);
                exit;
            }

            if (!mysql_select_db($dbName, $conn)) {
                //创建数据时同时设置编码
                if (!mysql_query("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;", $conn)) {
                    $arr['msg'] = '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！';
                    echo json_encode($arr);
                    exit;
                }
                if (empty($n)) {
                    $arr['n'] = 1;
                    $arr['msg'] = "成功创建数据库:{$dbName}<br>";
                    echo json_encode($arr);
                    exit;
                }
                mysql_select_db($dbName, $conn);
            }

            //读取数据文件
            $sqldata = file_get_contents(SITEDIR . 'install/' . $sqlFile);
            $sqlFormat = sql_split($sqldata, $dbPrefix);


            $counts = count($sqlFormat);
            for ($i = $n; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                if (strstr($sql, 'CREATE TABLE')) {
                    preg_match('/CREATE TABLE (IF NOT EXISTS)? `eb_([^ ]*)`/is', $sql, $matches);
                    mysqli_query($conn,"DROP TABLE IF EXISTS `$matches[2]");
                    $sql = str_replace('`eb_','`'.$dbPrefix,$sql);//替换表前缀
                    $ret = mysqli_query($conn,$sql);
                    if ($ret) {
                        $message = '<li><span class="correct_span">&radic;</span>创建数据表['.$dbPrefix.$matches[2] . ']完成!<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li> ';
                    } else {
                        $message = '<li><span class="correct_span error_span">&radic;</span>创建数据表['.$dbPrefix.$matches[2] . ']失败!<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li>';
                    }
                    $i++;
                    $arr = array('n' => $i, 'msg' => $message);
                    echo json_encode($arr);
                    exit;
                } else {
                    if(trim($sql) == '')
                        continue;
                    $sql = str_replace('`eb_','`'.$dbPrefix,$sql);//替换表前缀
                    $ret = mysqli_query($conn,$sql);
                    $message = '';
                    $arr = array('n' => $i, 'msg' => $message);
//                    echo json_encode($arr); exit;
                }
            }


            //插入管理员表字段tp_admin表
            $time = time();
            $ip = get_client_ip();
            $ip = empty($ip) ? "0.0.0.0" : $ip;
            $password = md5(trim($_POST['manager_pwd']));
            mysql_query("truncate table {$dbPrefix}system_admin");
            $addadminsql = "INSERT INTO `{$dbPrefix}system_admin` (`id`, `account`, `pwd`, `real_name`, `roles`, `last_ip`, `last_time`, `add_time`, `login_count`, `level`, `status`, `is_del`) VALUES
(1, '".$username."', '".$password."', 'admin', '1', '".$ip."',$time , $time, 0, 0, 1, 0)";
            $res = mysql_query($addadminsql);
            if($res){
                $message = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
                $arr = array('n' => 999999, 'msg' => $message);
                echo json_encode($arr);exit;
            }else{
                $message = '添加管理员失败<br />成功写入配置文件<br>安装完成．';
                $arr = array('n' => 999999, 'msg' => $message);
                echo json_encode($arr);exit;
            }
            //插入管理员
            //生成随机认证码
            $verify = sp_random_string(6);
            $time = time();
            $create_date=date("Y-m-d h:i:s");
            $ip = get_client_ip();
            $ip =empty($ip)?"0.0.0.0":$ip;
            $addadminsql = "INSERT INTO `{$dbPrefix}system_admin` (`id`, `account`, `pwd`, `real_name`, `roles`, `last_ip`, `last_time`, `add_time`, `login_count`, `level`, `status`, `is_del`) VALUES
(1, '".$username."', '".$password."', 'admin', '1', '".$ip."',$time , $time, 0, 0, 1, 0)";
            $res = mysql_query($addadminsql);
            if($res){
                $message = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
                $arr = array('n' => 999999, 'msg' => $message);
                echo json_encode($arr);exit;
            }else{
                $message = '添加管理员失败<br />成功写入配置文件<br>安装完成．';
                $arr = array('n' => 999999, 'msg' => $message);
                echo json_encode($arr);exit;
            }
        }

        include_once ("./templates/s4.php");
        exit();

    case '5':
    	$ip = get_client_ip();
    	$host=$_SERVER['HTTP_HOST'];
        include_once ("./templates/s5.php");
        exit();
}

function testwrite($d) {
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

function sql_execute($sql, $tablepre) {
    $sqls = sql_split($sql, $tablepre);
    if (is_array($sqls)) {
        foreach ($sqls as $sql) {
            if (trim($sql) != '') {
                mysql_query($sql);
            }
        }
    } else {
        mysql_query($sqls);
    }
    return true;
}

function sql_split($sql, $tablepre) {

    if ($tablepre != "sp_")
        $sql = str_replace("sp_", $tablepre, $sql);
    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

    if ($r_tablepre != $s_tablepre)
        $sql = str_replace($s_tablepre, $r_tablepre, $sql);
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

function _dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

// 获取客户端IP地址
function get_client_ip() {
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}

function dir_create($path, $mode = 0777) {
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

function dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}



function sp_random_string($len = 6) {
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

?>