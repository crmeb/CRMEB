<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {$title}</title>
    <meta name="keywords" content="CRMEB-500错误页面">
    <meta name="description" content="CRMEB 500错误页面">

    <link rel="shortcut icon" href="favicon.ico">
    <link href="{__FRAME_PATH}css/bootstrap.min.css" rel="stylesheet">
    <link href="{__FRAME_PATH}css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="{__FRAME_PATH}css/style.min.css?v=4.1.0" rel="stylesheet">

</head>

<body class="gray-bg">
<div class="middle-box text-center animated fadeInDown">
    <h1>500</h1>
    <h3 class="font-bold">服务器内部错误</h3>

    <div class="error-desc">
        服务器好像出错了...
        <p>{$msg}</p>
    </div>
</div>

</body>

</html>
