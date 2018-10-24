<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/frame_head" /}

    <link href="{__PLUG_PATH}codemirror/lib/codemirror.css" rel="stylesheet">
    <link href="{__PLUG_PATH}codemirror/theme/monokai.css" rel="stylesheet">
    <script src="{__PLUG_PATH}codemirror/lib/codemirror.js"></script>
    <script src="{__PLUG_PATH}codemirror/mode/php/php.js"></script>
    <script src="{__PLUG_PATH}codemirror/mode/javascript/javascript.js"></script>
    <script src="{__PLUG_PATH}codemirror/mode/clike/clike.js"></script>
    <script src="{__PLUG_PATH}codemirror/mode/css/css.js"></script>
    <script src="{__PLUG_PATH}codemirror/mode/sql/sql.js"></script>
    <script src="{__PLUG_PATH}codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="{__PLUG_PATH}codemirror/mode/xml/xml.js"></script>
    <script src="{__PLUG_PATH}jquery-1.4.1.min.js"></script>
    <script>
        var $eb = parent._mpApi;
        // if(!$eb) top.location.reload();
    </script>
    <title></title>
    <style type="text/css">
        .lineblock { display: inline-block; margin: 1px; height: 5px; }
        .CodeMirror {border: 1px solid #aaa; height: 700px}
        .savestyle{
            color: #fff;
            background-color: #2d8cf0;
            border-color: #2d8cf0;
            width: 100px;
            padding: 6px 15px 7px 15px;
            font-size: 14px;
            border-radius: 4px;
            margin: 10px auto;
            margin-left: 45%;
        }
        .savestyle:hover{
            color: #fff;
            background-color: #57a3f3;
            border-color: #57a3f3;
        }
    </style>

</head>
<body>
<div class="row">
    <div class="col-lg-12">
        <div class="btn-group p-xxs">
            <button type="button" class="btn btn-sm btn-white" id="savefile"><i class="fa fa-save"></i>保存</button>
            <button type="button" class="btn btn-sm btn-white" id="undo"><i class="fa fa-undo"></i>撤销</button>
            <button type="button" class="btn btn-sm btn-white" id="redo"><i class="fa fa-rotate-right"></i>回退</button>
            <button type="button" class="btn btn-sm btn-white" id="refresh"><i class="fa fa-refresh"></i>刷新</button>
<!--                <button type="button" class="btn btn-sm btn-white" id="replaceRange"><i class="fa fa-exchange"></i>刷新</button>-->
        </div>

        <div class="ibox-content no-padding" >
            <form>
                <textarea class="form-control" id="code" name="code">{$content}</textarea>
            </form>
        </div>
    </div>
</div>


<script>
    var editor = CodeMirror.fromTextArea(document.getElementById("code"), {  // 标识到textarea
        value : "http://www.crmeb.com",  // 文本域默认显示的文本
        mode : "{$mode}",  // 模式
        theme : "monokai",  // CSS样式选择
        indentUnit : 2,  // 缩进单位，默认2
        smartIndent : true,  // 是否智能缩进
        tabSize : 4,  // Tab缩进，默认4
        readOnly : false,  // 是否只读，默认false
        showCursorWhenSelecting : true,
        lineNumbers : true // 是否显示行号
        // .. 还有好多，翻译不完。需要的去看http://codemirror.net/doc/manual.html#config
    });
    reiframesize();//设置编辑框的尺寸
    $("#savefile").click(function(){
        $.post("{:Url('savefile')}",{comment:editor.getValue(),filepath:'{$filepath}'},function (res) {
            if(res.code == 200) {
                $eb.message('success',res.msg);
            }
        },'json');
    });
    $("#undo").click(function(){
        editor.undo();
    });
    $("#redo").click(function(){
        editor.redo();
    });
    $("#refresh").click(function(){
        editor.refresh();
    });
    //    editor.on('keydown', function() {
    //        editor.showHint(); //满足自动触发自动联想功能
    //    });
    //editor.getValue()//获取经过转义的编辑器文本内容
    window.onresize=function(){
        reiframesize();
    };
    function reiframesize(){
        var w = document.documentElement.clientWidth+3;
        var h = document.documentElement.clientHeight-48;
        editor.setSize(w,h);
    }
</script>
</body>
</html>

