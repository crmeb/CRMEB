{extend name="public/container"}
{block name="title"}与{$to_user.nickname}聊天中{/block}
{block name="head_top"}
    <link rel="stylesheet" href="{__WAP_PATH}crmeb/css/store_service.css" />
    <script type="text/javascript" src="{__PLUG_PATH}jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}crmeb/module/store_service/unslider.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}crmeb/module/store_service/moment.min.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}crmeb/module/store_service/jquery.touchSwipe.min.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}crmeb/module/store_service/mobile.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}crmeb/module/store_service/msn.js"></script>
{/block}
{block name="content"}
<div class="kj"></div>
<div class="prompt"><p></p></div>
<script>
    var site="http://{$_SERVER['SERVER_NAME']}"; //定义站点域名
    var bq_m="{__WAP_PATH}crmeb/images/storeservice/"; //定义表情路径
    //获取双方信息
    var uid="{$user.uid}";
    var uavatar="{$user.avatar}";
    var unickname="{$user.nickname}";
    var to_uid="{$to_user.uid}";
    var to_uavatar="{$to_user.avatar}";
    var to_unickname="{$to_user.nickname}";
    var mer_id="{$merchant.id}";
    var mer_name="{$merchant.mer_name}";

    $(function(){
        //初始化
        c=($(window).height()/2)-20;
        $(".prompt p").css("margin-top",c+"px");//信息提示框居中

        moban_duihua();
        // $(".kj").html(moban_duihua());//加载聊天框html
        moban_duihua_js();  //初始化聊天框中js事件

        setInterval("refresh_msn()",1500);  //每过两秒读取一次消息
    });
</script>
<script>
    requirejs(['vue','store','helper'],function(Vue,storeApi,$h){
        mapleWx($jssdk(),function(){
            var _this = this;
            document.querySelector('.msn i.img_icon').onclick = function(){
                storeApi.wechatUploadImg(_this,9,function(res){
                    for (var i = 0; i < res.length; i++) {
                        var img = '<img class="img" src="'+res[i]+'" onclick="img_detail($(this))" />';
                        add_msn(img,"html");
                    }
                });
            }
        });
    });
    function img_detail(_this){
        var imgurl = site+_this.attr("src")
        var imgArray = [];
        imgArray.push(imgurl);
        wx.previewImage({
            current: imgurl,
            urls: imgArray
        });
    }
</script>
{/block}
