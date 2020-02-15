<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/head"}
    <title>{$title|default=''}</title>
    <style>

    </style>
</head>
<body id="body" style="position: absolute;height: 100%;width: 100%">
    <iframe id="mapPage" width="100%" height="100%" frameborder=0
            src="https://3gimg.qq.com/lightmap/components/locationPicker2/index.html?type=1&key={$key}&referer=myapp">
    </iframe>
</body>
<script>
     window.onload=function(){
         document.getElementById('body').style.height=document.body.clientHeight+'px';
    }
    mpFrame.start(function(layer) {
        window.addEventListener('message',function (evevt) {
            if(evevt.data){
                parent.selectAdderss(evevt.data);
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            }else{
                return layer.msg('请选择地图');
            }
        });
    })
</script>