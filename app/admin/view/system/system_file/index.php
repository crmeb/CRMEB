{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>文件校验</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center" width="10%">类型</th>
                            <th class="text-center">文件地址</th>
                            <th class="text-center">校验码</th>
                            <th class="text-center">上次访问时间</th>
                            <th class="text-center">上次修改时间</th>
                            <th class="text-center">上次改变时间</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="cha" id="vo"}
                        <tr>
                            <td class="text-center">
                                <span style="color: #ff0000">[{$vo.type}]</span>
                            </td>
                            <td class="text-left">
                                {$vo.filename}
                            </td>
                            <td class="text-center">
                                {$vo.cthash}
                            </td>
                            <td class="text-center">
                                {$vo.atime|date='Y-m-d H:i:s'}
                            </td>
                            <td class="text-center">
                                {$vo.mtime|date='Y-m-d H:i:s'}
                            </td>
                            <td class="text-center">
                                {$vo.ctime|date='Y-m-d H:i:s'}
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
{/block}
{block name="script"}
<script>
    $('.btn-warning').on('click',function(){
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
