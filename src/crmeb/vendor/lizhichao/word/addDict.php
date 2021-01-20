<?php
/**
 * Created by PhpStorm.
 * User: tanszhe
 * Date: 2017/12/25
 * Time: 下午7:47
 * 添加分词库
 */
//定义词典文件路径
define('_VIC_WORD_DICT_PATH_',__DIR__.'/Data/dict.igb');

require __DIR__.'/vendor/autoload.php';

use Lizhichao\Word\VicDict;

//目前可支持 igb 和 json 两种词典库格式；igb需要安装igbinary扩展，igb文件小，加载快
$dict = new VicDict('igb');

//添加词语词库 add(词语,词性) 可以是除保留字符（\ ， \x  ，\i），以外的utf-8编码的任何字符
$dict->add('中国','n');

//保存词库
$dict->save();