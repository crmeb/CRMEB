<?php
// +----------------------------------------------------------------------
// | zbMysql
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2016 http://www.xazbkj.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: honor <sugar1569@vip.qq.com>
// +----------------------------------------------------------------------
//访问方法 ／mysql.php?pwd=******&table=表名为空所有表
session_start();
header("Content-type:text/html;charset=utf-8");

/**
 * 生成mysql数据字典
 */
// 配置数据库
$database = array();
$password = '327677';//访问密码GET['pwd'] 传输
$database['DB_HOST'] = '127.0.0.1';
$database['DB_NAME'] = 'gitcrmeb';//数据库名称
$database['DB_USER'] = 'gitcrmeb';//用户名
$database['DB_PWD'] = 'y3NfiJTjRp8PNeee';//密码
$char_set = 'UTF8';//数据库编码
date_default_timezone_set('Asia/Shanghai');
$mysql_conn = @mysqli_connect("{$database['DB_HOST']}", "{$database['DB_USER']}", "{$database['DB_PWD']}") or die("Mysql connect is error.");
mysqli_select_db($mysql_conn,$database['DB_NAME']);
$result = mysqli_query($mysql_conn,'show tables');
mysqli_query($mysql_conn,'SET NAMES '.$char_set);

$pwd = !empty($_GET['pwd'])?$_GET['pwd']:$_SESSION['pwd'];
if($password != $pwd)exit('无权访问');
$_SESSION['pwd'] = $pwd;

if($_GET['table'])
{
    $tables[]['TABLE_NAME'] = $_GET['table'];
}else{
    // 取得所有表名
    while ($row = mysqli_fetch_array($result))
    {
        $tables[]['TABLE_NAME'] = $row[0];
    }
}
// 循环取得所有表的备注及表中列消息
foreach($tables as $k => $v)
{
    $sql = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.TABLES ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database['DB_NAME']}'";
    $table_result = mysqli_query($mysql_conn,$sql);
    while ($t = mysqli_fetch_array($table_result))
    {
        $tables[$k]['TABLE_COMMENT'] = $t['TABLE_COMMENT'];
    }
    $sql = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database['DB_NAME']}'";
    $fields = array();
    $field_result = mysqli_query($mysql_conn,$sql);
    while ($t = mysqli_fetch_array($field_result))
    {
        $fields[] = $t;
    }
    $tables[$k]['COLUMN'] = $fields;
}
mysqli_close($mysql_conn);
//print_r($tables);
$html = '';
if(isset($_GET['table']))
{
    $html .= '<h1 style="text-align:center;">表结构</h1>';
    $html .= '<p style="text-align:center;margin:20px auto;">生成时间：' . date('Y-m-d H:i:s') . '</p>';
    // 循环所有表
    foreach($tables as $k => $v)
    {
        $html .= '<table border="1" cellspacing="0" cellpadding="0" align="center">';
        $html .= '<caption>表名：' . $v['TABLE_NAME'] . ' ------- ' . $v['TABLE_COMMENT'] . '</caption>';
        $html .= '<tbody><tr><th>字段名</th><th>数据类型</th><th>默认值</th><th>允许非空</th><th>自动递增</th><th>备注</th></tr>';
        $html .= '';
        foreach($v['COLUMN'] AS $f)
        {
            $html .= '<td class="c1">' . $f['COLUMN_NAME'] . '</td>';
            $html .= '<td class="c2">' . $f['COLUMN_TYPE'] . '</td>';
            $html .= '<td class="c3">' . $f['COLUMN_DEFAULT'] . '</td>';
            $html .= '<td class="c4">' . $f['IS_NULLABLE'] . '</td>';
            $html .= '<td class="c5">' . ($f['EXTRA'] == 'auto_increment'?'是':' ') . '</td>';
            $html .= '<td class="c6">' . $f['COLUMN_COMMENT'] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></p>';
        $html .= '<p style="text-align:left;margin:20px auto;">总共：' . count($v['COLUMN']) . '个字段</p>';
        $html .= '</body></html>';
    }
}else{
    $html .= '<h1 style="text-align:center;">数据字典</h1>';
    $html .= '<p style="text-align:center;margin:20px auto;">生成时间：' . date('Y-m-d H:i:s') . '</p>';
    foreach($tables as $k => $v)
    {
        $html .= '<table border="1" cellspacing="0" cellpadding="0" align="center">';
        $html .= '<caption>表名：' . $v['TABLE_NAME'] . ' ------- ' . $v['TABLE_COMMENT'] . '<a href="javascript:void(0)" class="abiao" data-tabname="'.$v['TABLE_NAME'].'" >[查看结构]</a></caption>';


        $html .= '</tbody></table></p>';
    }
    $html .= '<p style="text-align:left;margin:20px auto;">总共：' . count($tables) . '个数据表</p>';
    $html .= '</body></html>';
}
// 输出
echo '<html>
	<meta charset="utf-8">
	<title>'.$_GET["table"].'</title>
	<style>
		body,td,th {font-family:"宋体"; font-size:12px;}
		table,h1,p{width:960px;margin:0px auto;}
		table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;}
		table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }
		table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:12px; border:1px solid #CCC;padding-left:5px;}
		table td{height:20px; font-size:12px; border:1px solid #CCC;background-color:#fff;padding-left:5px;}
		.c1{ width: 150px;}
		.c2{ width: 150px;}
		.c3{ width: 80px;}
		.c4{ width: 100px;}
		.c5{ width: 100px;}
		.c6{ width: 300px;}
	</style>
	<body>';

echo $html;

?>
<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="http://apps.bdimg.com/libs/layer/2.1/layer.js"></script>
<script type="text/javascript">
    var geturl = window.location.href;
    var arr = geturl.split('?');
    var url = arr[0];
    $(document).ready(function(){
        $(".abiao").on('click', function(e){
            var tabname = $(this).data("tabname");
            layer.open({
                title:'表名：'+name,
                area: ['1000px', '600px'],
                offset: '100px',
                type: 2,
                content: url +'?table='+tabname //这里content是一个URL，如果你不想让iframe出现滚动条，你还可以content: ['http://sentsin.com', 'no']
            });
        })
    });

</script>
