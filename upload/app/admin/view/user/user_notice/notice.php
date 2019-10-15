{extend name="public/container"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/content.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-8 m-b-xs">
                        <form action="" class="form-inline">
                            <i class="fa fa-search" style="margin-right: 10px;"></i>
                            <div class="input-group" style="width: 80%">
                                <input type="text" name="title" value="{$where.title}" placeholder="请输入通知标题" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">发送人</th>
                            <th class="text-center">通知标题</th>
                            <th class="text-center" width="700">通知内容</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">{$vo.id}</td>
                            <td class="text-center">{$vo.user}</td>
                            <td class="text-center">{$vo.title}</td>
                            <td class="text-center">{$vo.content}</td>
                            <td class="text-center">
                                <a class="btn-send" data-url="{:Url('send_user',array('id'=>$vo['id'],'uid'=>$uid))}">立即发送</a>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.btn-send').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        swal({
            title: "您确定要发送这条信息给‘{$nickname}’吗？",
            text:"发送后将无法修改通知信息，请谨慎操作！",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText:"是的，我要发送！",
            cancelButtonText:"让我再考虑一下…",
            closeOnConfirm: false,
            closeOnCancel: false
        }).then(function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    swal(res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '发送失败')
            }).catch(function(err){
                swal(err);
            });
        }).catch(console.log);
    });
</script>
{/block}
