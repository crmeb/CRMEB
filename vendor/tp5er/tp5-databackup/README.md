
### 使用composer进行安装
~~~
     composer require tp5er/tp5-databackup dev-master
~~~

### 使用composer update进行安装
~~~
    "require": {
        "tp5er/tp5-databackup": "dev-master"
    },
~~~

### 引入类文件
~~~
use \tp5er\Backup;
~~~

### 配置文件
~~~
$config=array(
    'path'     => './Data/',//数据库备份路径
    'part'     => 20971520,//数据库备份卷大小
    'compress' => 0,//数据库备份文件是否启用压缩 0不压缩 1 压缩
    'level'    => 9 //数据库备份文件压缩级别 1普通 4 一般  9最高
);
~~~

### 实例化
~~~
 $db= new Backup($config);
~~~

### 文件命名规则，请严格遵守（温馨提示）
~~~
$file=['name'=>date('Ymd-His'),'part'=>1]
~~~

### 数据类表列表
~~~
return $this->fetch('index',['list'=>$db->dataList()]);
~~~
### 备份文件列表
~~~
  return $this->fetch('importlist',['list'=>$db->fileList()]);
~~~


### 备份表
~~~
 $tables="数据库表1";
 $start= $db->setFile($file)->backup($tables[$id], 0);
 当$start返回0的时候就表示备份成功
~~~

### 导入表
~~~
 $start=0;
 $start= $db->setFile($file)->import($start);
~~~

### 删除备份文件
~~~
    $db->delFile($time);
~~~

### 下载备份文件
~~~
    $db->downloadFile($time);
~~~

### 修复表
~~~
    $db->repair($tables)
~~~

### 优化表
~~~
    $db->optimize($tables)
~~~



### 大数据备份采取措施1
~~~
如果备份数据比较大的情况下，需要修改如下参数
//默认php代码能够申请到的最大内存字节数就是134217728 bytes，如果代码执行的时候再需要更多的内存,根据情况定义指定字节数
memory_limit = 1024M
//默认php代码申请到的超时时间是20s，如果代码执行需要更长的时间，根据代码执行的超时时间定义版本运行超时时间
max_execution_time =1000
~~~

### 大数据备份采取措施2

~~~
    自由设置超时时间。支持连贯操作，该方法主要使用在表备份和还原中，防止备份还原和备份不完整
    //备份
    $time=0//表示不限制超时时间，直到程序结束，(慎用)
    $db->setTimeout($time)->setFile($file)->backup($tables[$id], 0);
    //还原
    $db->setTimeout($time)->setFile($file)->import($start);
~~~


无论您是大神，还是小白都希望您们加群进行交流，共同学习共同进步。
# 技术交流与bug提交QQ群：368683534!!!!
