# VicWord 一个纯php的分词

<a href="https://github.com/996icu/996.ICU/blob/master/LICENSE"><img src="https://img.shields.io/badge/support-996.icu-red.svg"></a>

QQ交流群: 731475644

## 安装

```shell
composer require lizhichao/word
```



## 分词说明
- 含有3种切分方法
    - `getWord` 长度优先切分 。最快
    - `getShortWord` 细粒度切分。比最快慢一点点
    - `getAutoWord` 自动切分  。效果最好
- 可自定义词典，自己添加词语到词库，词库支持文本格式`json`和二级制格式`igb`
二进制格式词典小，加载快
- `dict.igb`含有175662个词，欢迎大家补充词语到 `dict.txt` ，格式(词语 \t idf \t 词性)
    - idf 获取方法 百度搜索这个词语 `Math.log(100000001/结果数量)`，如果你有更好的方法欢迎补充。
    - 词性 [标点符号,名词,动词,形容词,区别词,代词,数词,量词,副词,介词,连词,助词,语气词,拟声词,叹词] 取index ；标点符号取0
- 三种分词结果对比
```php
$fc = new VicWord();
$arr = $fc->getWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
//北京大学|生喝|进口|红酒|，|在|北京大学|生活区|喝|进口|红酒
//$arr 是一个数组 每个单元的结构[词语,词语位置,词性,这个词语是否包含在词典中] 这里只值列出了词语

$arr =  $fc->getShortWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
//北京|大学|生喝|进口|红酒|，|在|北京|大学|生活|区喝|进口|红酒

$arr = $fc->getAutoWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
//北京|大学生|喝|进口|红酒|，|在|北京大学|生活区|喝|进口|红酒

//对比
//qq的分词 http://nlp.qq.com/semantic.cgi#page2 
//百度的分词 http://ai.baidu.com/tech/nlp/lexical

```
## 分词速度
机器阿里云 `Intel(R) Xeon(R) Platinum 8163 CPU @ 2.50GHz`   
`getWord` 每秒140w字  
`getShortWord` 每秒138w字  
`getAutoWord` 每秒40w字  
测试文本在百度百科拷贝的一段5000字的文本

## 制作词库
- 词库支持utf-8的任意字符   
- 词典大小不影响 分词速度  

只有一个方法 VicDict->add(词语,词性 = null)
```php
require __DIR__.'/Lib/VicDict.php';

//目前可支持 igb 和 json 两种词典库格式；igb需要安装igbinary扩展，igb文件小，加载快
$path = ''; //词典地址
$dict = new VicDict($path);

//添加词语词库 add(词语,词性) 不分语言，可以是utf-8编码的任何字符
$dict->add('中国','n');

//保存词库
$dict->save();
```

## demo 
[demo](http://blogs.vicsdf.com/my/fc)

## 该作者的其他软件
[一个极简高性能php框架，支持[swoole | php-fpm ]环境](https://github.com/lizhichao/one)




