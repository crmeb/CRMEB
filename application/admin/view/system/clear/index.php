{include file="public/frame_head"}
<div class="row" style="width: 100%;margin-top: 50px; text-align: center;">
<button type="button" class="btn btn-w-m btn-primary" data-url="{:Url('system.clear/refresh_cache')}">刷新数据缓存</button>
<button type="button" class="btn btn-w-m btn-primary" data-url="{:Url('system.clear/delete_cache')}">清除缓存</button>
<button type="button" class="btn btn-w-m btn-primary" data-url="{:Url('system.clear/delete_log')}">清除日志</button>
</div>
<script>
    $('button').on('click',function(){
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
        },{'title':'您确定要进行此操作吗？','text':'操作后runtime目录文件有可能被删除,请谨慎操作！','confirm':'是的，我要操作'})
    })
</script>
{include file="public/inner_footer"}
