<!doctype html>
<!--suppress JSAnnotator -->
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>编辑内容</title>
    <link href="{__FRAME_PATH}css/font-awesome.min.css" rel="stylesheet">
    <link href="{__ADMIN_PATH}plug/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.min.js"></script>
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/lang/zh-cn/zh-cn.js"></script>
    <style>
        .edui-btn-toolbar .edui-btn.edui-active{  display: none;}
        .edui-container{overflow: initial !important;}
        button.btn-success.dim {  box-shadow: inset 0 0 0 #1872ab,0 5px 0 0 #1872ab,0 10px 5px #999; }
        .float-e-margins .btn { margin-bottom: 5px;  }
        button.dim { display: inline-block; color: #fff; text-decoration: none; text-transform: uppercase; text-align: center; padding-top: 6px; margin-right: 10px; position: relative; cursor: pointer; border-radius: 5px; font-weight: 600; margin-bottom: 20px!important;  }
        .btn-success { background-color: #1c84c6; border-color: #1c84c6; color: #FFF;  }
        .btn { border-radius: 3px;  }
        .btn-success { color: #fff; background-color: #5cb85c; border-color: #4cae4c;  }
        .btn { display: inline-block; padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px;  }
        button, input, select, textarea { font-family: inherit; font-size: inherit; line-height: inherit;  }
        button.btn-success.dim:active { box-shadow: inset 0 0 0 #1872ab,0 2px 0 0 #1872ab,0 5px 3px #999; }
        button.dim:active { bottom: 4px; }
        .btn-success.active, .btn-success:active, .open .dropdown-toggle.btn-success { background-image: none;  }
        .btn-success.active, .btn-success:active, .btn-success:focus, .btn-success:hover, .open .dropdown-toggle.btn-success { background-color: #1a7bb9; border-color: #1a7bb9; color: #FFF;  }
        .dim{bottom: 7px; right: 8px; z-index: 1003; position: fixed !important;}
    </style>
</head>
<body>
<button class="btn btn-success  dim" data-url="{$action}" type="button"><i class="fa fa-upload"></i>
</button>
<script type="text/plain" id="myEditor" style="width:100%;">
    <p>这里可以写一些输入内容</p>
</script>
<script type="text/javascript">
    $eb = parent._mpApi;
    $('.dim').on('click',function(){
        $eb.axios.post($(this).data('url'),{'{$field}':getContent()}).then(function(res){
            if(res.status == 200 && res.data.code == 200){
                $eb.message('success','保存成功!');
            } else
                return Promise.reject(res.data.msg || '保存失败!');
        }).catch(function(err){
            $eb.message('error',err);
        })
    });
    var editor = document.getElementById('myEditor');
    editor.style.height = document.body.scrollHeight+'px';
    //实例化编辑器
    var um = UM.getEditor('myEditor',{
        fullscreen:true
    });
    function getContent() {
        return (UM.getEditor('myEditor').getContent());
    }
    function hasContent() {
        return (UM.getEditor('myEditor').hasContents());
    }
</script>
</body>
</html>