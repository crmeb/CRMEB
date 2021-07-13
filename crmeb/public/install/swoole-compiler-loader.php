<?php
/**
 * Swoole Compiler Loader Wizard
 * Swoole Compiler Loader 安装助手
 * version : 2.0.2
 * date    : 2019-01-09
 */
// Set debug var
ini_set("display_errors", "On");
error_reporting(E_ALL);
restore_exception_handler();
restore_error_handler();
date_default_timezone_set('Asia/Shanghai');

// Set constants
define('WIZARD_VERSION', '2.0.2');
define('WIZARD_DEFAULT_LANG', 'zh-cn');
define('WIZARD_OPTIONAL_LANG', 'zh-cn,en');
define('WIZARD_NAME_ZH', 'Swoole Compiler Loader安装助手');
define('WIZARD_NAME_EN', 'Swoole Compiler Loader Wizard');
define('WIZARD_DEFAULT_RUN_MODE', 'web');
define('WIZARD_OPTIONAL_RUN_MODE', 'cli,web');
define('WIZARD_DEFAULT_OS', 'linux');
define('WIZARD_OPTIONAL_OS', 'linux,windows');
define('WIZARD_BASE_API', 'http://compiler.swoole.com');

// Language items
$languages['zh-cn'] = [
    'title' => 'Swoole Compiler Loader 安装助手',
];
$languages['en'] = [
    'title' => 'Swoole Compiler Loader Wizard',
];

// Set env variable for current environment
$env = [];
// Check os type
$env['os'] = [];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $env['os']['name'] = "windows";
    $env['os']['raw_name'] = php_uname();
} else {
    $env['os']['name'] = "unix";
    $env['os']['raw_name'] = php_uname();
}
// Check php
$env['php'] = [];
$env['php']['version'] = phpversion();
// Check run mode
$sapi_type = php_sapi_name();
if ("cli" == $sapi_type) {
    $env['php']['run_mode'] = "cli";
} else {
    $env['php']['run_mode'] = "web";
}
// Check php bit
if (PHP_INT_SIZE == 4) {
    $env['php']['bit'] = 32;
} else {
    $env['php']['bit'] = 64;
}
$env['php']['sapi'] = $sapi_type;
$env['php']['ini_loaded_file'] = php_ini_loaded_file();
$env['php']['ini_scanned_files'] = php_ini_scanned_files();
$env['php']['loaded_extensions'] = get_loaded_extensions();
$env['php']['incompatible_extensions'] = ['xdebug', 'ionCube', 'zend_loader'];
$env['php']['loaded_incompatible_extensions'] = [];
$env['php']['extension_dir'] = ini_get('extension_dir');
// Check incompatible extensions
if (is_array($env['php']['loaded_extensions'])) {
    foreach ($env['php']['loaded_extensions'] as $loaded_extension) {
        foreach ($env['php']['incompatible_extensions'] as $incompatible_extension) {
            if (strpos(strtolower($loaded_extension), strtolower($incompatible_extension)) !== false) {
                $env['php']['loaded_incompatible_extensions'][] = $loaded_extension;
            }
        }
    }
}
$env['php']['loaded_incompatible_extensions'] = array_unique($env['php']['loaded_incompatible_extensions']);
// Parse System Environment Info
$sysInfo = w_getSysInfo();
// Check php thread safety
$env['php']['raw_thread_safety'] = isset($sysInfo['thread_safety']) ? $sysInfo['thread_safety'] : false;
if (isset($sysInfo['thread_safety'])) {
    $env['php']['thread_safety'] = $sysInfo['thread_safety'] ? '线程安全' : '非线程安全';
} else {
    $env['php']['thread_safety'] = '未知';
}
// Check swoole loader installation
if (isset($sysInfo['swoole_loader']) and isset($sysInfo['swoole_loader_version'])) {
    $env['php']['swoole_loader']['status'] = $sysInfo['swoole_loader'] ? "<span style='color: #007bff;'>已安装</span>"
        : '未安装';
    if ($sysInfo['swoole_loader_version'] !== false) {
        $env['php']['swoole_loader']['version'] = "<span style='color: #007bff;'>" . $sysInfo['swoole_loader_version'] . "</span>";
    } else {
        $env['php']['swoole_loader']['version'] = '未知';
    }
} else {
    $env['php']['swoole_loader']['status'] = '未安装';
    $env['php']['swoole_loader']['version'] = '未知';
}
/**
 *  Web mode
 */
if ('web' == $env['php']['run_mode']) {
    $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);
    if (preg_match("/zh-c/i", $language)) {
        $env['lang'] = "zh-cn";
        $wizard_lang = $env['lang'];
    } else {
        $env['lang'] = "en";
        $wizard_lang = $env['lang'];
    }
    $html = '';
    // Header
    $html_header = '<!doctype html>
	<html lang="en">
	  <head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Bootstrap CSS -->
		<link href="https://lib.baomitu.com/twitter-bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet">
		<title>%s</title>
		<style>
			.list_info {display: inline-block; width: 12rem;}
			.bold_text {font-weight: bold;}
			.code {color:#007bff;font-size: medium;}
		</style>
	  </head>
	  <body class="bg-light"> 
	  ';
    $html_header = sprintf($html_header, $languages[$wizard_lang]['title']);
    $html_body = '<div class="container">';
    $html_body_nav = '<div class="py-5 text-center"  style="padding-bottom: 1rem!important;">';
    $html_body_nav .= '<h2>CRMEB Swoole Compiler 安装向导</h2>';
    $html_body_nav .= '<p class="lead"> Version:2.0.2 Date:2019-01-09</p>';
    $html_body_nav .= '</div><hr>';

    // Environment information
    $html_body_environment = '
	<div class="col-12"  style="padding-top: 1rem!important;">
		<h5 class="text-center">检查当前环境</h5>
		<ul class="list-unstyled text-small">';
    $html_body_environment .= '<li><span class="list_info">操作系统 : </span>' . $env['os']['raw_name'] . '</li>';
    $html_body_environment .= '<li><span class="list_info">PHP版本 : </span>' . $env['php']['version'] . '</li>';
    $html_body_environment .= '<li><span class="list_info">PHP运行环境 : </span>' . $env['php']['sapi'] . '</li>';
    $html_body_environment .= '<li><span class="list_info">PHP配置文件 : </span>' . $env['php']['ini_loaded_file'] . '</li>';
    $html_body_environment .= '<li><span class="list_info">PHP扩展安装目录 : </span>' . $env['php']['extension_dir'] . '</li>';
    $html_body_environment .= '<li><span class="list_info">PHP是否线程安全 : </span>' . $env['php']['thread_safety'] . '</li>';
    $html_body_environment .= '<li><span class="list_info">是否安装swoole_loader : </span>' . $env['php']['swoole_loader']['status'] . '</li>';
    if (isset($sysInfo['swoole_loader']) and $sysInfo['swoole_loader']) {
        $html_body_environment .= '<li><span class="list_info">swoole_loader版本 : </span>' . $env['php']['swoole_loader']['version'] . '</li>';
    }
    if ($env['php']['bit'] == 32) {
        $html_body_environment .= '<li><span style="color:red">温馨提示：当前环境使用的PHP为 ' . $env['php']['bit'] . ' 位的PHP，Compiler 目前不支持 Debug 版本或 32 位的PHP，可在 phpinfo() 中查看对应位数，如果误报请忽略此提示</span></li>';
    }
    $html_body_environment .= '	</ul></div>';

    // Error infomation
    $html_error = "";
    if (!empty($env['php']['loaded_incompatible_extensions'])) {
        $html_error = '<hr>
		<div class="col-12"  style="padding-top: 1rem!important;">
		<h5 class="text-center" style="color:red">错误信息</h5>
		<p class="text-center" style="color:red">%s</p>
    </div>
		';
        $err_msg = "当前PHP包含与swoole_compiler_loader扩展不兼容的扩展" . implode(',', $env['php']['loaded_incompatible_extensions']) . "，请移除不兼容的扩展。";
        $html_error = sprintf($html_error, $err_msg);
    }

    // Check Loader Status
    $html_body_loader = '<hr>';
    if (empty($html_error)) {
        $html_body_loader .= '<div class="col-12" style="padding-top: 1rem!important;">';
        $html_body_loader .= '<h5 class="text-center">安装和配置Swoole Loader</h5>';
        $phpversion = substr($env['php']['version'], 0, 3);
        $phpversion = str_replace('.', '', $phpversion);
        $loaderFileName = '';
        if ($env['os']['name'] == "windows") {
            $loaderFileName = 'php_swoole_loader_php' . $phpversion;
            if ($env['php']['thread_safety'] == '非线程安全') {
                $loaderFileName .= '_nzts_x64.dll';
            } else {
                $loaderFileName .= '_zts_x64.dll';
            }
        } else {
            if ($env['php']['thread_safety'] != '非线程安全') {
                $loaderFileName = 'swoole_loader' . $phpversion . '_zts.so';
            } else {

                $loaderFileName = 'swoole_loader' . $phpversion . '.so';
            }
        }
        $html_body_loader .= '<p><span class="bold_text">1 - 安装Swoole Loader</span></p><p>前往根目录 /help/swoole-loader/' . $loaderFileName . '扩展文件上传到当前PHP的扩展安装目录中：<br/><pre class="code">' . $env['php']['extension_dir'] . '</pre></p>';
        $html_body_loader .= '<p><span class="bold_text">2 - 修改php.ini配置</span>（如已修改配置，请忽略此步骤，不必重复添加）</p><p>';
        $html_body_loader .= '编辑此PHP配置文件：<span class="code">' . $env['php']['ini_loaded_file'] . '</span>，在此文件底部结尾处加入如下配置<br/>';
        if ($env['os']['name'] == "windows") {
            $html_body_loader .= '<pre class="code">extension=' . $env['php']['extension_dir'] . DIRECTORY_SEPARATOR . $loaderFileName . '</pre>注意：需要名称和刚才上传到当前PHP的扩展安装目录中的文件名一致';
        } else {
            $html_body_loader .= '<pre class="code">extension=' . $env['php']['extension_dir'] . DIRECTORY_SEPARATOR . $loaderFileName . '</pre>注意：需要名称和刚才上传到当前PHP的扩展安装目录中的文件名一致';
        }
        $html_body_loader .= '</p>';
        $html_body_loader .= '<p><span class="bold_text">3 - 重启服务</span></p><p>重启或重载PHP配置</p>';
        $html_body_loader .= '</div>';
    }

    // Body footer
    $html_body_footer = '<footer class="my-5 pt-5 text-muted text-center text-small">
	<p class="mb-1">CopyRight © 2018 - ' . date('Y') . ' Swoole.com 上海识沃网络科技有限公司</p>
  </footer>';
    $html_body .= $html_body_nav . '<div class="row">' . $html_body_environment . $html_error . $html_body_loader . '</div>' . $html_body_footer;
    $html_body .= '</div>';
    // Footer
    $html_footer = '
		<script src="https://lib.baomitu.com/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://lib.baomitu.com/axios/0.18.0/axios.min.js"></script>
		<script src="https://lib.baomitu.com/twitter-bootstrap/4.1.0/js/bootstrap.min.js"></script>
		</body>
	</html>';
    // Make full html
    $html = $html_header . $html_body . $html_footer;
    // Output html content
    //ob_start();
    echo $html;
    //ob_end_clean();
    //die();
}

/**
 * Cli mode
 */
if ("cli" == $env['php']['run_mode']) {

}

/**
 * Useful functions
 */
// Dump detail of variable
function w_dump($var)
{
    if (is_object($var) and $var instanceof Closure) {
        $str = 'function (';
        $r = new ReflectionFunction($var);
        $params = array();
        foreach ($r->getParameters() as $p) {
            $s = '';
            if ($p->isArray()) {
                $s .= 'array ';
            } else if ($p->getClass()) {
                $s .= $p->getClass()->name . ' ';
            }
            if ($p->isPassedByReference()) {
                $s .= '&';
            }
            $s .= '$' . $p->name;
            if ($p->isOptional()) {
                $s .= ' = ' . var_export($p->getDefaultValue(), TRUE);
            }
            $params [] = $s;
        }
        $str .= implode(', ', $params);
        $str .= '){' . PHP_EOL;
        $lines = file($r->getFileName());
        for ($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
            $str .= $lines[$l];
        }
        echo $str;
        return;
    } else if (is_array($var)) {
        echo "<xmp class='a-left'>";
        print_r($var);
        echo "</xmp>";
        return;
    } else {
        var_dump($var);
        return;
    }
}

// Parse verion of php
function w_parse_version($version)
{
    $versionList = [];
    if (is_string($version)) {
        $rawVersionList = explode('.', $version);
        if (isset($rawVersionList[0])) {
            $versionList[] = $rawVersionList[0];
        }
        if (isset($rawVersionList[1])) {
            $versionList[] = $rawVersionList[1];
        }
    }
    return $versionList;
}

function w_getSysInfo()
{
    global $env;
    $sysEnv = [];
    // Get content of phpinfo
    ob_start();
    phpinfo();
    $sysInfo = ob_get_contents();
    ob_end_clean();
    // Explode phpinfo content
    if ($env['php']['run_mode'] == 'cli') {
        $sysInfoList = explode('\n', $sysInfo);
    } else {
        $sysInfoList = explode('</tr>', $sysInfo);
    }
    foreach ($sysInfoList as $sysInfoItem) {
        if (preg_match('/thread safety/i', $sysInfoItem)) {
            $sysEnv['thread_safety'] = (preg_match('/(enabled|yes)/i', $sysInfoItem) != 0);
        }
        if (preg_match('/swoole_loader support/i', $sysInfoItem)) {
            $sysEnv['swoole_loader'] = (preg_match('/(enabled|yes)/i', $sysInfoItem) != 0);
        }
        if (preg_match('/swoole_loader version/i', $sysInfoItem)) {
            preg_match('/\d+.\d+.\d+/s', $sysInfoItem, $match);
            $sysEnv['swoole_loader_version'] = isset($match[0]) ? $match[0] : false;
        }
    }
    //var_dump($sysEnv);die();
    return $sysEnv;
}
