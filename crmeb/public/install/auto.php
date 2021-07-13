<?php

define('PHP_EDITION','7.1.0');
if(function_exists('saeAutoLoader')){
	define('IS_CLOUD',true);
	define('IS_SAE',true);
	define('IS_BAE',false);
	define('IO_TRUE_NAME','sae');
}elseif(isset($_SERVER['HTTP_BAE_ENV_APPID'])){
	define('IS_CLOUD',true);
	define('IS_SAE',false);
	define('IS_BAE',true);
	define('IO_TRUE_NAME','bae');
}else{
	define('IS_SAE',false);
	define('IS_BAE',false);
	define('IS_CLOUD',false);
}
