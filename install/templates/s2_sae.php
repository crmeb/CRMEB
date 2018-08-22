<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
<link rel="stylesheet" href="./css/install.css?v=9.0" />
</head>
<body>
<div class="wrap">
  <?php require './templates/header.php';?>
  <section class="section">
    <div class="step">
      <ul>
        <li class="current"><em>1</em>检测环境</li>
        <li><em>2</em>创建数据</li>
        <li><em>3</em>完成安装</li>
      </ul>
    </div>
    <div class="server" style="height:200px; font-size:16px;">
     <table width="100%">
        <tr>
          <td class="td1">环境检测</td>
          <td class="td1" width="25%">配置要求</td>
          <td class="td1" width="25%">当前状态</td>
        </tr>
        <tr>
          <td>PHP版本</td>
          <td>>5.3.x</td>
          <td><span class="correct_span">&radic;</span> <?php echo $phpv; ?></td>
        </tr>
        <tr>
          <td>Mysql服务</td>
          <td>>需要开启</td>
          <td><?php echo $saeMysql; ?></td>
        </tr>
        <tr>
          <td>Storage服务</td>
          <td>需要开启</td>
          <td><?php echo $storage; ?></td>
        </tr>
		<tr>
          <td>Memcache服务</td>
          <td>需要开启</td>
          <td><?php echo $Memcache; ?></td>
        </tr>
        <tr>
          <td>KVDB服务</td>
          <td>需要开启</td>
          <td><?php echo $KVDB; ?></td>
        </tr>
      </table>
    </div>
    <div class="bottom tac"> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=2" class="btn">重新检测</a><a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=3" class="btn">下一步</a> </div>
  </section>
</div>
<?php require './templates/footer.php';?>
</body>
</html>