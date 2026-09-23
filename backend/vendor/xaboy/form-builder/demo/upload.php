<?php
/**
 * PHP表单生成器
 *
 * @package  FormBuilder
 * @author   xaboy <xaboy2005@qq.com>
 * @version  2.0
 * @license  MIT
 * @link     https://github.com/xaboy/form-builder
 * @document http://php.form-create.com
 */


namespace App;


use FormBuilder\Response;

require '../vendor/autoload.php';

//上传接口响应
Response::uploadSuccess('https://user-images.githubusercontent.com/37764940/83014819-f582b300-a051-11ea-8979-e7b02b372795.png')->send();