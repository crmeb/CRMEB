<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
<link rel="stylesheet" href="./css/install.css?v=9.0" />
<script src="js/jquery.js"></script>
<?php 
$uri = $_SERVER['REQUEST_URI'];
$root = substr($uri, 0,strpos($uri, "install"));
$admin = $root."../index.php/admin/index/";
$host = $_SERVER['HTTP_HOST'];
?>
</head>
<body>
<div class="wrap">
  <?php require './templates/header.php';?>
  <section class="section">
    <div class="">
      <div class="success_tip cc"> <a href="<?php echo $admin;?>" class="f16 b">安装完成，进入后台管理</a>
		<p>为了您站点的安全，安装完成后即可将网站根目录下的“install”文件夹下的所有文件删除，<span style="color: red">除install.lock文件外</span>防止重复安装。<p>
      </div>
	        <div class="bottom tac"> 
	        <a href="<?php echo 'http://'.$host;?>" class="btn">进入前台</a>
            <a href="<?php echo 'http://'.$host;?>/admin" class="btn btn_submit J_install_btn">进入后台</a>
      </div>
      <div class=""> </div>
    </div>
  </section>
</div>
<?php require './templates/footer.php';?>
<script>
$(function(){
	$.ajax({
	type: "POST",
	url: "http://shop.crmeb.net/index.php/admin/server.upgrade_api/updatewebinfo",
        header:{
            'Access-Control-Allow-Origin':'*',
            'Access-Control-Allow-Headers':'X-Requested-With',
            'Access-Control-Allow-Methods':'PUT,POST,GET,DELETE,OPTIONS'
        },
	data: {
	    host:'<?php echo $host;?>',
        https:'<?php echo 'http://'.$host;?>',
        version:'<?php echo $version;?>',
        ip:<?php echo json_encode($ip);?>
    },
	dataType: 'json',
	success: function(){}
	});
});
</script>
</body>
</html>