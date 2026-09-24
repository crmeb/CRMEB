<?php
//文件签名
$fileValue = '';
//最低php版本要求
define('PHP_EDITION', '8.2.0');
//兼容版本上限（不含），与 backend/composer.json 的 php 约束保持一致
define('PHP_EDITION_MAX', '8.5.0');
//展示给用户的兼容版本区间
define('PHP_EDITION_RANGE', '8.2-8.4');
//服务环境检测
if (function_exists('saeAutoLoader') || isset($_SERVER['HTTP_BAE_ENV_APPID'])) {
    showHtml('对不起，当前环境不支持本系统，请使用独立服务或云主机！');
}

define('APP_DIR', _dir_path(substr(dirname(__FILE__), 0, -15)));//项目目录
define('SITE_DIR', _dir_path(substr(dirname(__FILE__), 0, -8)));//入口文件目录

// 首次打开安装入口时，确保配置文件存在，便于后续安装流程检测文件权限。
foreach (['.env', '.constant'] as $filename) {
    $configPath = APP_DIR . $filename;
    if (!is_file($configPath)) {
        @touch($configPath);
    }
}

if (file_exists('../install.lock')) {
    showHtml('你已经安装过该系统，如果想重新安装，请先删除public目录下的 install.lock 文件，然后再安装。');
}

@set_time_limit(1000);

if (version_compare(PHP_VERSION, PHP_EDITION, '<')) {
    exit('您的php版本过低，不能安装本软件，兼容php版本' . PHP_EDITION_RANGE . '，谢谢！');
}

if (version_compare(PHP_VERSION, PHP_EDITION_MAX, '>=')) {
    exit('您的php版本过高，不能安装本软件，兼容php版本' . PHP_EDITION_RANGE . '，当前版本：' . PHP_VERSION . '，谢谢！');
}

date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);
mysqli_report(MYSQLI_REPORT_OFF);
header('Content-Type: text/html; charset=UTF-8');

//mysql数据库配置容器中获取
$MYSQL_HOST_IP = getenv('MYSQL_HOST_IP') ?: '127.0.0.1';
$MYSQL_PORT = getenv('MYSQL_PORT') ?: '3306';
$MYSQL_USER = getenv('MYSQL_USER') ?: 'root';
$MYSQL_PASSWORD = getenv('MYSQL_PASSWORD') ?: '123456';
$MYSQL_DATABASE = getenv('MYSQL_DATABASE') ?: 'crmeb';
//redis配置容器中获取
$REDIS_HOST_IP = getenv('REDIS_HOST_IP') ?: '127.0.0.1';
$REDIS_PORT = getenv('REDIS_PORT') ?: '6379';
$REDIS_DATABASE = getenv('REDIS_DATABASE') ?: 0;
$REDIS_PASSWORD = getenv('REDIS_PASSWORD') ?: '';

//数据库（表结构与表数据拆分存储：crmeb_table.sql 结构 + data/*_data*.sql 数据）
$sqlFile = 'crmeb_table.sql';
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
        if (version_compare(PHP_VERSION, PHP_EDITION, '<') || version_compare(PHP_VERSION, PHP_EDITION_MAX, '>=')) {
            die('本系统需要PHP为 ' . PHP_EDITION_RANGE . ' 版本，当前PHP版本为：' . PHP_VERSION);
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
        $dbName = strtolower(trim($_POST['dbName'] ?? ''));
        $_POST['dbport'] = !empty($_POST['dbport']) ? $_POST['dbport'] : '3306';
        if (isset($_GET['mysqldbpwd']) && $_GET['mysqldbpwd']) {
            $dbHost = $_POST['dbHost'];
            $conn = mysqli_init();
            mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            @mysqli_real_connect($conn, $dbHost, $_POST['dbUser'], $_POST['dbPwd'], NULL, $_POST['dbport']);
            if ($error = mysqli_connect_errno()) {
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
                    if (!@mysqli_select_db($conn, $dbName)) {
                        //创建数据时同时设置编码
                        if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;")) {
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
        if (isset($_GET['redisdbpwd']) && $_GET['redisdbpwd']) {

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
        if (intval($_GET['install'] ?? 0)) {
            $n = intval($_GET['n'] ?? 0);
            if ($n == 999999)
                exit;
            $arr = array();
            // 插件轮询仅执行下一项 Installer，不重复基础数据库与配置初始化。
            if (($_GET['phase'] ?? 'tables') === 'plugins') {
                installNextPlugin(max(0, $n));
            }

            $dbHost = trim($_POST['dbhost']);
            $_POST['dbport'] = !empty($_POST['dbport']) ? $_POST['dbport'] : '3306';
            $dbName = strtolower(trim($_POST['dbname']));
            $dbUser = trim($_POST['dbuser']);
            $dbPwd = trim($_POST['dbpw']);
            $dbPrefix = empty($_POST['dbprefix']) ? 'eb_' : trim($_POST['dbprefix']);

            $username = trim($_POST['manager']);
            $password = trim($_POST['manager_pwd']) ?: 'crmeb.com';

            // 新版安装流程将表结构、默认数据、插件拆为三个独立阶段。
            // 旧页面仍可能传 phase=sql，统一映射到表结构阶段，避免重新进入合并 SQL 流程。
            $phase = $_GET['phase'] ?? 'tables';
            $installPhase = $phase === 'sql' ? 'tables' : $phase;
            if ($installPhase === 'tables' || $installPhase === 'data') {
                handleInstallPhase($installPhase, $n, [
                    'dbHost' => $dbHost,
                    'dbName' => $dbName,
                    'dbUser' => $dbUser,
                    'dbPwd' => $dbPwd,
                    'dbPrefix' => $dbPrefix,
                    'requestScheme' => $request_scheme,
                    'requestHost' => $request_host,
                ]);
            }

            if (!function_exists('mysqli_connect')) {
                $arr['msg'] = "请安装 mysqli 扩展!";
                exit(json_encode($arr));
            }
            $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, NULL, $_POST['dbport']);
            if (mysqli_connect_errno()) {
                $arr['msg'] = "连接数据库失败!" . mysqli_connect_error();
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
                if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;")) {
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

            //读取数据文件（表结构 crmeb_table.sql + 表数据 crmeb_data.sql 合并执行）
            $sqldata = file_get_contents(SITE_DIR . 'install/' . $sqlFile);
            $sqldata .= "\n" . file_get_contents(SITE_DIR . 'install/crmeb_data.sql');
            $sqlFormat = sql_split($sqldata, $dbPrefix);
            //创建写入sql数据库文件到库中 结束

            /**
             * 执行SQL语句
             */
            $counts = count($sqlFormat);
            if ($n < 0) $n = 0;
            $dataBatchCount = 0;
            $dataBatchStartedAt = microtime(true);
            $inDumpSession = false;
            $tablesLocked = false;
            for ($i = $n; $i < $counts; $i++) {
                if (!isset($sqlFormat[$i])) continue;
                $sql = trim($sqlFormat[$i]);
                if (strstr($sql, 'CREATE TABLE')) {
                    preg_match('/CREATE TABLE (IF NOT EXISTS)? `eb_([^ ]*)`/is', $sql, $matches);
                    // $matches[2] 是不含 eb_ 前缀的表名，DROP 必须拼上真实前缀，否则覆盖安装时删不掉旧表
                    mysqli_query($conn, "DROP TABLE IF EXISTS `" . $dbPrefix . $matches[2] . "`");
                    $sql = str_replace('`eb_', '`' . $dbPrefix, $sql);//替换表前缀
                    $ret = mysqli_query($conn, $sql);
                    if ($ret) {
                        $message = '创建数据表[' . $dbPrefix . $matches[2] . ']完成!';
                    } else {
                        $err = mysqli_error($conn);
                        $message = '创建数据表[' . $dbPrefix . $matches[2] . ']失败!失败原因：' . $err;
                    }
                    file_put_contents('install.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
                    $i++;
                    $arr = array('n' => $i, 'count' => $counts, 'msg' => $message, 'time' => date('Y-m-d H:i:s'));
                    exit(json_encode($arr));
                } else {
                    if (trim($sql) == '')
                        continue;
                    $sql = str_replace('`eb_', '`' . $dbPrefix, $sql);//替换表前缀
                    $sql = str_replace('http://demo.crmeb.com', $request_scheme . '://' . $request_host, $sql);//替换图片域名
                    $sql = str_replace('http:\\\\/\\\\/demo.crmeb.com', $request_scheme . ':\\\\/\\\\/' . $request_host, $sql);//替换图片域名
                    // 导出文件的会话变量和锁表状态必须在同一次连接中恢复。
                    if (strpos($sql, 'SET @OLD_CHARACTER_SET_CLIENT=') !== false) {
                        $inDumpSession = true;
                    }
                    if (preg_match('/^LOCK TABLES\b/i', $sql)) {
                        $tablesLocked = true;
                    }
                    $ret = mysqli_query($conn, $sql);
                    if (strpos($sql, 'SET SQL_NOTES=@OLD_SQL_NOTES') !== false) {
                        $inDumpSession = false;
                    }
                    if (preg_match('/^UNLOCK TABLES\b/i', $sql)) {
                        $tablesLocked = false;
                    }
                    $dataBatchCount++;
                    $dataTable = '';
                    if (preg_match('/^INSERT\s+INTO\s+`([^`]+)`/i', $sql, $dataMatches)) {
                        $dataTable = $dataMatches[1];
                    }
                    if ($ret) {
                        $message = $dataTable !== ''
                            ? '写入数据表[' . $dataTable . ']完成，SQL进度[' . ($i + 1) . '/' . $counts . ']'
                            : '执行SQL语句[' . ($i + 1) . '/' . $counts . ']成功!';
                    } else {
                        $err = mysqli_error($conn);
                        $message = "执行SQL语句[" . $i . "]失败!失败原因：" . $err;
                    }
                    file_put_contents('install.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
                    $arr = array('n' => $i + 1, 'count' => $counts, 'msg' => $message, 'time' => date('Y-m-d H:i:s'));
                    // 每批最多 50 条或约 1 秒；游标指向下一条，避免轮询时重复导入。
                    if (!$inDumpSession && !$tablesLocked
                        && ($dataBatchCount >= 50 || microtime(true) - $dataBatchStartedAt >= 1 || $i + 1 >= $counts)) {
                        exit(json_encode($arr));
                    }
                }
            }


            // 清空测试数据
            if (empty($_POST['demo'])) {
                $result = mysqli_query($conn, "show tables");
                $tables = mysqli_fetch_all($result);//参数MYSQL_ASSOC、MYSQLI_NUM、MYSQLI_BOTH规定产生数组类型
                $bl_table = array(
                    'eb_agent_level',
                    'eb_agreement',
                    'eb_cache',
                    'eb_diy',
                    'eb_express',
                    'eb_express_company',
                    'eb_express_template',
                    'eb_lang_code',
                    'eb_lang_country',
                    'eb_lang_type',
                    'eb_member_right',
                    'eb_member_ship',
                    'eb_out_interface',
                    'eb_page_categroy',
                    'eb_page_link',
                    'eb_shipping_templates',
                    'eb_shipping_templates_region',
                    'eb_system_admin',
                    'eb_system_city',
                    'eb_system_config',
                    'eb_system_config_tab',
                    'eb_system_event_data',
                    'eb_system_group',
                    'eb_system_group_data',
                    'eb_system_menus',
                    'eb_notice_config',
                    'eb_notice_channel',
                    'eb_module_install_log',
                    'eb_pay_channel',
                    'eb_pay_channel_platform',
                    'eb_driver',
                    'eb_printer_channel',
                    'eb_system_ticket',
                    'eb_system_route',
                    'eb_system_route_cate',
                    'eb_system_crontab',
                    'eb_system_user_level'
                );
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
            $strConfig = str_replace('#APP_KEY#', 'app_key_' . $unique, $strConfig);
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

            $arr = array('n' => 0, 'count' => 0, 'phase' => 'plugins', 'msg' => '基础数据初始化完成，准备安装默认插件和驱动', 'time' => date('Y-m-d H:i:s'));
            exit(json_encode($arr, JSON_UNESCAPED_UNICODE));

        }
        include_once("./templates/step4.php");
        exit();

    case '5':
        $ip = get_client_ip();
        $host = $_SERVER['HTTP_HOST'];
        $curent_version = getversion();
        $uid = getUid();
        $version = trim($curent_version['version']);
        $platform = trim($curent_version['platform']);
        installlog();
        include_once("./templates/step5.php");
        @touch('../install.lock');
        exit();
}
/**
 * 执行安装向导的表结构或默认数据阶段。
 *
 * 每次请求只处理一个表：表结构阶段执行一个 CREATE TABLE，默认数据阶段执行
 * 当前表的全部 INSERT。阶段游标独立，避免数据 SQL 继续沿用表结构的总游标。
 *
 * @param string $phase 安装阶段（tables/data）
 * @param int $cursor 当前阶段游标
 * @param array<string, string> $config 数据库及请求配置
 * @return void
 */
function handleInstallPhase(string $phase, int $cursor, array $config): void
{
    if (!function_exists('mysqli_connect')) {
        exit(json_encode(['n' => -2, 'msg' => '请安装 mysqli 扩展!'], JSON_UNESCAPED_UNICODE));
    }

    $conn = @mysqli_connect(
        $config['dbHost'],
        $config['dbUser'],
        $config['dbPwd'],
        null,
        $_POST['dbport']
    );
    if (!$conn || mysqli_connect_errno()) {
        exit(json_encode(['n' => -2, 'msg' => '连接数据库失败!' . mysqli_connect_error()], JSON_UNESCAPED_UNICODE));
    }
    mysqli_set_charset($conn, 'utf8');
    if (mysqli_get_server_info($conn) < 5.1) {
        exit(json_encode(['n' => -2, 'msg' => '数据库版本太低! 必须5.1以上'], JSON_UNESCAPED_UNICODE));
    }

    $dbName = $config['dbName'];
    if (!mysqli_select_db($conn, $dbName)) {
        if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;")) {
            exit(json_encode(['n' => -2, 'msg' => '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！'], JSON_UNESCAPED_UNICODE));
        }
        mysqli_select_db($conn, $dbName);
        if ($cursor === -1 && $phase === 'tables') {
            $tableStatements = getInstallTableStatements();
            $message = "成功创建数据库:{$dbName}，开始写入默认表结构";
            $response = [
                'n' => 0,
                'count' => count($tableStatements),
                'phase' => 'tables',
                'msg' => $message,
                'time' => date('Y-m-d H:i:s'),
            ];
            file_put_contents('install.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
            exit(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    $tableStatements = getInstallTableStatements();
    $dataGroups = getInstallDataGroups();
    if ($dataGroups === []) {
        exit(json_encode([
            'n' => -2,
            'count' => 0,
            'phase' => 'data',
            'msg' => '默认数据文件不存在，请检查 install/data 目录',
            'time' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE));
    }

    if ($phase === 'tables') {
        $count = count($tableStatements);
        $cursor = max(0, $cursor);
        if ($cursor >= $count) {
            $response = [
                'n' => 0,
                'count' => count($dataGroups),
                'phase' => 'data',
                'msg' => '默认表结构写入完成，开始写入默认数据',
                'time' => date('Y-m-d H:i:s'),
            ];
            exit(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

        $sql = trim($tableStatements[$cursor]);
        preg_match('/CREATE TABLE(?: IF NOT EXISTS)? `eb_([^`]+)`/i', $sql, $matches);
        $tableName = $matches[1] ?? ('#' . ($cursor + 1));
        $fullTableName = $config['dbPrefix'] . $tableName;
        // 覆盖安装时删除实际前缀的表，不能只删除默认 eb_ 前缀。
        mysqli_query($conn, "DROP TABLE IF EXISTS `" . $fullTableName . "`");
        $sql = prepareInstallSql($sql, $config['dbPrefix'], $config['requestScheme'], $config['requestHost']);
        $ret = mysqli_query($conn, $sql);
        $message = $ret
            ? '写入默认表结构[' . $fullTableName . ']完成（' . ($cursor + 1) . '/' . $count . '）'
            : '写入默认表结构[' . $fullTableName . ']失败：' . mysqli_error($conn);
        file_put_contents('install.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
        $response = [
            'n' => $ret ? $cursor + 1 : -2,
            'count' => $count,
            'phase' => 'tables',
            'msg' => $message,
            'time' => date('Y-m-d H:i:s'),
        ];
        exit(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    $count = count($dataGroups);
    $cursor = max(0, $cursor);
    if ($cursor >= $count) {
        finalizeBaseInstall($conn, $config);
        $response = [
            'n' => 0,
            'count' => 0,
            'phase' => 'plugins',
            'msg' => '默认数据写入完成，开始安装插件',
            'time' => date('Y-m-d H:i:s'),
        ];
        exit(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    $group = $dataGroups[$cursor];
    $tableName = $group['table'];
    $displayTableName = preg_replace('/^eb_/', $config['dbPrefix'], $tableName);
    $success = true;
    foreach ($group['statements'] as $statement) {
        $sql = prepareInstallSql($statement, $config['dbPrefix'], $config['requestScheme'], $config['requestHost']);
        if (!mysqli_query($conn, $sql)) {
            $success = false;
            $message = '写入默认数据表[' . $displayTableName . ']失败：' . mysqli_error($conn);
            break;
        }
    }
    if ($success) {
        $message = '写入默认数据表[' . $displayTableName . ']完成（' . ($cursor + 1) . '/' . $count . '）';
    }
    file_put_contents('install.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
    $response = [
        'n' => $success ? $cursor + 1 : -2,
        'count' => $count,
        'phase' => 'data',
        'msg' => $message,
        'time' => date('Y-m-d H:i:s'),
    ];
    exit(json_encode($response, JSON_UNESCAPED_UNICODE));
}

/**
 * 获取安装 SQL 中的全部建表语句。
 *
 * @return array<int, string>
 */
function getInstallTableStatements(): array
{
    $statements = sql_split(file_get_contents(SITE_DIR . 'install/' . 'crmeb_table.sql'), 'eb_');
    return array_values(array_filter($statements, static function (string $statement): bool {
        return (bool)preg_match('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`eb_/i', trim($statement));
    }));
}

/**
 * 获取按文件名排序的默认数据 SQL 文件。
 *
 * 每个文件对应一个表；文件名不包含默认的 eb_ 前缀，并以 _data.sql 结尾。
 * 大表可使用带零填充序号的 _data_001.sql、_data_002.sql 文件继续拆分。
 * 文件名排序就是安装顺序，文件内的多条 INSERT 保持原有顺序一次执行。
 *
 * @return array<int, array{table: string, statements: array<int, string>}>
 */
function getInstallDataGroups(): array
{
    $files = glob(SITE_DIR . 'install/data/*_data*.sql', GLOB_NOSORT);
    if ($files === false || $files === []) {
        return [];
    }

    $files = array_values(array_filter($files, static function (string $file): bool {
        return (bool)preg_match('/^[a-z0-9_]+_data(?:_\d+)?\.sql$/i', basename($file));
    }));
    if ($files === []) {
        return [];
    }

    sort($files, SORT_STRING);
    $groups = [];
    foreach ($files as $file) {
        $statements = [];
        foreach (sql_split(file_get_contents($file), 'eb_') as $statement) {
            $statement = trim($statement);
            if ($statement !== '' && preg_match('/^INSERT\s+INTO\s+`([^`]+)`/i', $statement)) {
                $statements[] = $statement;
            }
        }
        if ($statements === []) {
            continue;
        }

        preg_match('/^INSERT\s+INTO\s+`([^`]+)`/i', $statements[0], $matches);
        $groups[] = [
            'table' => $matches[1] ?? pathinfo($file, PATHINFO_FILENAME),
            'statements' => $statements,
        ];
    }
    return $groups;
}

/**
 * 替换安装 SQL 的表前缀和演示站域名。
 */
function prepareInstallSql(string $sql, string $dbPrefix, string $requestScheme, string $requestHost): string
{
    $sql = str_replace('`eb_', '`' . $dbPrefix, $sql);
    $sql = str_replace('http://demo.crmeb.com', $requestScheme . '://' . $requestHost, $sql);
    // 内层 JSON 的斜杠被再次转义（SQL 文本形如 http:\\/\\/demo.crmeb.com），必须按同一转义层级替换，
    // 否则 eb_diy / eb_theme 等 JSON 数据安装后仍指向演示站。
    return str_replace('http:\\\\/\\\\/demo.crmeb.com', $requestScheme . ':\\\\/\\\\/' . $requestHost, $sql);
}

/**
 * 默认数据完成后写入环境配置、演示数据选项和管理员账号。
 */
function finalizeBaseInstall($conn, array $config): void
{
    $dbPrefix = $config['dbPrefix'];

    // 清空测试数据，但保留系统配置、菜单和其他安装基础数据。
    if (empty($_POST['demo'])) {
        $result = mysqli_query($conn, 'show tables');
        $tables = $result ? mysqli_fetch_all($result) : [];
        $baseTables = [
            'agent_level', 'agreement', 'cache', 'diy', 'express', 'express_company', 'express_template',
            'lang_code', 'lang_country', 'lang_type', 'member_right', 'member_ship', 'out_interface',
            'page_categroy', 'page_link', 'shipping_templates', 'shipping_templates_region', 'system_admin',
            'system_city', 'system_config', 'system_config_tab', 'system_event_data', 'system_group',
            'system_group_data', 'system_menus', 'notice_config', 'notice_channel', 'module_install_log',
            'pay_channel', 'pay_channel_platform', 'driver', 'printer_channel', 'system_ticket',
            'system_route', 'system_route_cate', 'system_crontab', 'system_user_level',
        ];
        $baseTables = array_map(static fn (string $table): string => $dbPrefix . $table, $baseTables);
        foreach ($tables as $table) {
            if (!in_array($table[0], $baseTables, true)) {
                mysqli_query($conn, 'truncate table `' . $table[0] . '`');
            }
        }
    }

    $unique = uniqid();
    $strConfig = file_get_contents(SITE_DIR . 'install/' . '.env');
    $replacements = [
        '#APP_KEY#' => 'app_key_' . $unique,
        '#DB_HOST#' => $config['dbHost'],
        '#DB_NAME#' => $config['dbName'],
        '#DB_USER#' => $config['dbUser'],
        '#DB_PWD#' => $config['dbPwd'],
        '#DB_PORT#' => $_POST['dbport'],
        '#DB_PREFIX#' => $dbPrefix,
        '#DB_CHARSET#' => 'utf8',
        '#CACHE_TYPE#' => ($_POST['cache_type'] ?? 0) == 0 ? 'file' : 'redis',
        '#CACHE_PREFIX#' => 'cache_' . $unique . ':',
        '#CACHE_TAG_PREFIX#' => 'cache_tag_' . $unique . ':',
        '#RB_HOST#' => $_POST['rbhost'] ?? '127.0.0.1',
        '#RB_PORT#' => $_POST['rbport'] ?? '6379',
        '#RB_PWD#' => $_POST['rbpw'] ?? '',
        '#RB_SELECT#' => $_POST['rbselect'] ?? 0,
        '#QUEUE_NAME#' => $unique,
    ];
    $strConfig = str_replace(array_keys($replacements), array_values($replacements), $strConfig);
    @chmod(APP_DIR . '/.env', 0777);
    @file_put_contents(APP_DIR . '/.env', $strConfig);

    $time = time();
    $ip = get_client_ip() ?: '0.0.0.0';
    $password = password_hash($_POST['manager_pwd'] ?? '', PASSWORD_BCRYPT);
    mysqli_query($conn, 'truncate table `' . $dbPrefix . 'system_admin`');
    $username = mysqli_real_escape_string($conn, trim($_POST['manager'] ?? ''));
    $password = mysqli_real_escape_string($conn, $password);
    $ip = mysqli_real_escape_string($conn, $ip);
    $addadminsql = "INSERT INTO `{$dbPrefix}system_admin` (`id`, `account`, `head_pic`, `pwd`, `real_name`, `roles`, `last_ip`, `last_time`, `add_time`, `login_count`, `level`, `status`, `is_del`) VALUES
(1, '{$username}', '/statics/system_images/admin_head_pic.png', '{$password}', 'admin', '1', '{$ip}', {$time}, {$time}, 0, 0, 1, 0)";
    mysqli_query($conn, $addadminsql);
    if ($config['requestHost']) {
        // system_config.value 统一以 JSON 存储（读取侧 SystemConfigServices::getConfigValue 会 json_decode），
        // 必须写入带引号的 JSON 字符串；写成裸 URL 会让 json_decode 返回 null，sys_config('site_url') 取到空值。
        $siteUrl = mysqli_real_escape_string($conn, json_encode($config['requestScheme'] . '://' . $config['requestHost']));
        mysqli_query($conn, 'UPDATE `' . $dbPrefix . 'system_config` SET `value`=\'' . $siteUrl . '\' WHERE `menu_name`=\'site_url\'');
    }
}

/**
 * 执行一个默认安装项，返回下一项游标；插件内部负责建表、菜单及种子数据。
 *
 * @param int $cursor 安装计划索引
 * @return void
 */
function installNextPlugin(int $cursor): void
{
    $total = 0;
    try {
        require_once APP_DIR . 'vendor/autoload.php';
        $app = new \think\App(APP_DIR);
        $app->initialize();
        /** @var \app\services\system\module\ModuleInstaller $installer */
        $installer = $app->make(\app\services\system\module\ModuleInstaller::class);
        $plan = $installer->getFreshInstallPlan();
        $total = count($plan);
        if ($cursor >= $total) {
            $response = ['n' => 999999, 'count' => $total, 'phase' => 'plugins', 'msg' => '安装完成'];
        } else {
            $item = $plan[$cursor];
            $result = $installer->install($item['key']);
            $status = match ($result['status'] ?? 'success') {
                'success' => '成功',
                'skipped' => '已跳过',
                default => '未知状态',
            };
            $message = $item['label'] . '[' . $item['name'] . ']安装结果：' . $status;
            file_put_contents('install.log', date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
            $response = ['n' => $cursor + 1, 'count' => $total, 'phase' => 'plugins', 'msg' => $message];
        }
    } catch (\Throwable $e) {
        $message = '安装系统插件失败：' . $e->getMessage();
        file_put_contents('install.log', date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
        $response = ['n' => -2, 'count' => $total, 'phase' => 'plugins', 'msg' => $message];
    }
    $response['time'] = date('Y-m-d H:i:s');
    exit(json_encode($response, JSON_UNESCAPED_UNICODE));
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

function getUid()
{
    $config = include_once APP_DIR.'/config/plat.php';

    return $config['stores']['sms']['template_id']['ADMIN_ORDER_UID'] ?? 0;
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

    // 表前缀统一通过 "`eb_" 反引号形式在执行前替换（见 step4 逻辑），
    // 此处不再做裸 "tp_" 子串替换，避免误伤 smtp_host 等正常字段名
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
