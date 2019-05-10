<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="{__FRAME_PATH}js/jquery.min.js"></script>
    <link href="{__PLUG_PATH}layui/css/layui.css" rel="stylesheet">
    <script src="{__PLUG_PATH}layui/layui.js"></script>
    <script>
        $eb = parent._mpApi;
        if(!$eb) top.location.reload();
    </script>
    {block name="head"}{/block}
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
{block name="content"}{/block}
{block name="script"}{/block}
</div>
<!--全局layout模版-->
</body>
</html>
