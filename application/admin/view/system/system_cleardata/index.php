{include file="public/frame_head"}
<style>
    .panel{
        width: 100%;margin-top:20px; text-align: left;padding: 20px 40px;
    }
    .panel button{display: block;margin:5px;}
</style>
<div class="col-sm-12">
    <blockquote class="text-warning" style="font-size:14px">清除数据请谨慎，清除就无法恢复哦！
    </blockquote>

    <hr>
</div>
<div class="row panel">

<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/userRelevantData')}">清除用户数据</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/storeData')}">清除商城数据</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/categoryData')}">清除产品分类</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/orderData')}">清除订单数据</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/kefuData')}">清除客服数据</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/wechatData')}">清除微信数据</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/wechatuserData')}">清除微信用户</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/articleData')}">清除内容分类</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/uploadData')}">清除所有附件</button><br>
<button type="button" class="btn btn-w-m btn-danger btn-primary cleardata" data-url="{:Url('system.SystemCleardata/systemdata')}">清除系统记录</button><br>
<!--<button type="button" class="btn btn-w-m btn-danger btn-primary creatuser" data-url="{:Url('system.SystemCleardata/userdate')}">创建前台用户用户名：crmeb 密码：123456</button>-->
</div>
<script>
    $('.cleardata').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                }else
                    return Promise.reject(res.data.msg || '操作失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{'title':'您确定要进行此操作吗？','text':'数据清除无法恢复','confirm':'是的，我要操作'})
    });
    $('.creatuser').on('click',function(){
            window.t = $(this);
            var _this = $(this),url =_this.data('url');
            $eb.$swal('delete',function(){
                $eb.axios.get(url).then(function(res){
                    if(res.status == 200 && res.data.code == 200) {
                        $eb.$swal('success',res.data.msg);
                    }else
                        return Promise.reject(res.data.msg || '操作失败')
                }).catch(function(err){
                    $eb.$swal('error',err);
                });
            },{'title':'您确定要进行此操作吗？','text':'用户数据清除之后才能进行此操作','confirm':'是的，我要操作'})
        })
</script>
{include file="public/inner_footer"}
