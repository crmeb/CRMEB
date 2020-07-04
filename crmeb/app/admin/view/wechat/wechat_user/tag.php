{extend name="public/container"}
{block name="head_top"}
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
<script src="{__PLUG_PATH}moment.js"></script>
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
<script src="{__ADMIN_PATH}frame/js/plugins/iCheck/icheck.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_tag')}',{w:350,h:180})">添加标签</button>
                <button type="button" style="float: right;" class="btn btn-w-m btn-primary" onclick="location.href='{:Url('tag',['refresh'=>1])}'">
                    <i class="fa fa-refresh"></i>
                    立即同步
                </button>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">标签名</th>
                            <th class="text-center">人数</th>
                            <th class="text-center" width="180">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">

                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.id}
                            </td>
                            <td class="text-center">
                                {$vo.name}
                            </td>
                            <td class="text-center">
                                {$vo.count}
                            </td>
                            <td class="text-center">
                                {gt name="vo.id" value="99"}
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit_tag',['id'=>$vo['id']])}',{w:350,h:180})"><i class="fa fa-edit"></i> 编辑</button>
                                <button class="btn btn-danger btn-xs " data-url="{:Url('delete_tag',array('id'=>$vo['id']))}" type="button"><i class="fa fa-times"></i> 删除
                                </button>
                                {/gt}
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('.btn-danger').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });
</script>
{/block}
