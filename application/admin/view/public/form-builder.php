<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?=$form->getTitle()?></title>
    <?=implode("\r\n",$form->getScript())?>
    <style>
        /*弹框样式修改*/
        .ivu-modal-body{padding: 5;}
        .ivu-modal-confirm-footer{display: none;}
    </style>
</head>
<body>
<script>
    formCreate.formSuccess = function(form,$r){
        <?=$form->getSuccessScript()?>
        //刷新父级页面
//        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
        //关闭当前窗口
//        var index = parent.layer.getFrameIndex(window.name);
//        parent.layer.close(index);
    };
	(function () {
		var create = <?=$form->formScript()?>
        create();
    })();
</script>
</body>
</html>