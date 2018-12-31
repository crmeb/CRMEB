{extend name="public/container"}
{block name="title"}聊天记录{/block}
{block name="head_top"}
    <link rel="stylesheet" href="{__WAP_PATH}crmeb/css/store_service.css" />
    <script type="text/javascript" src="{__PLUG_PATH}jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}crmeb/module/store_service/moment.min.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}crmeb/module/store_service/msn.js"></script>
{/block}
{block name="content"}
<div class="kj"><div class="list"></div></div>
<div class="prompt"><p></p></div>
<script>
    var last_time = 0;
    var interval;
    $(function(){
        //初始化
        c=($(window).height()/2)-20;
        $(".prompt p").css("margin-top",c+"px");//信息提示框居中
        ts("正在加载最近联系人");

        refresh_chat_list();
    });
    //获取聊天记录信息列表
    function refresh_chat_list(){
        var query = new Object();
        query.last_time = last_time;
        $.ajax({
             type:"post",
             url:"/wap/auth_api/refresh_msn_new",
             data:query,
             dataType:"json",
             async:true,
             success: function(data){
                if(last_time == 0 && !interval){
                    interval = setInterval("refresh_chat_list()",1000);//每过两秒读取一次消息
                    ts_no();//关闭提示框
                }
                if(data.code == 200 && data.data.length > 0)set_chat_list(data.data);
            }
        });
    }

    function set_chat_list(data){
        console.log(data);
        var html = '';
        for(var i=0;i<data.length;i++){
            html += get_html(data[i]);
            if($("#"+data[i]["to_info"]["uid"]+"_"+data[i]["to_info"]["mer_id"]).length){
                $("#"+data[i]["to_info"]["uid"]+"_"+data[i]["to_info"]["mer_id"]).remove();
            }
        }
        console.log(html);
        if(last_time > 0)
            $(".list").prepend(html);
        else
            $(".list").html(html);
        last_time = data[0]["add_time"];
    }

    function get_html(data){
        var html = '<div onclick="window.location.href=\'/wap/service/service_ing/to_uid/'+data["to_info"]["uid"]+'/mer_id/'+data["to_info"]["mer_id"]+'.html\'" id="'+data["to_info"]["uid"]+"_"+data["to_info"]["mer_id"]+'">';
        html += '<span>';
        html += '<img src="'+data["to_info"]["avatar"]+'">';
        html += '<em>';
        html += '<h1>'+data["to_info"]["mer_name"]+data["to_info"]["nickname"]+'</h1>';
        html += '<h2>'+timedate(data["add_time"],1)+'</h2>';
        html += '<h3>'+data["msn"]+'</h3>';
        if(data["count"] > 0)
            html += '<h4>'+data["count"]+'</h4>';
        html += '</em>';
        html += '</span>';
        html += '</div>';
        return html;
    }
</script>
{/block}