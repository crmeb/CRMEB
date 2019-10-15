<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>404错误</title>
    <link rel="stylesheet" href="{__MODULE_PATH}exception/css/style.css">
</head>
<body>

<div class="error-page">
    <div class="error-page-container">
        <div class="error-page-main">
            <h3>
                <strong>404</strong>{$msg}
            </h3>
            <div class="error-page-actions">
                <div>
                    <h4>可能原因：</h4>
                    <ol>
                        <li>页面加载失败</li>
                        <li>找不到请求的页面</li>
                        <li>输入的网址不正确</li>
                    </ol>
                </div>
                <div>
                    <h4>可以尝试：</h4>
                    <ul>
                        <li><a href="javascript:history.go(-1);">返回上一页</a></li>
                        <li><a href="javascript:location.reload();" style="margin-left: 10px">刷新</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>