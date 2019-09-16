{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>文件管理</h5>
            </div>
            <div class="ibox-content  no-padding">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-left" width="20%">列表</th>
                            <th class="text-left">文件大小</th>
                            <th class="text-left">是否可写</th>
                            <th class="text-left">更新时间</th>
                            <th class="text-center" width="30%">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        <tr>
                            <td class="text-left" colspan="4">
                                <span> <i class="fa fa-folder-o"></i> <a href="{:Url('opendir')}?dir={$dir}&superior=1">返回上级</a></span>
                            </td>
                            <td class="text-center"></td>
                        </tr>
                        {volist name="fileAll['dir']" id="vo"}
                        <tr>
                            <td class="text-left">
                                <span> <i class="fa fa-folder-o"></i> <a href="{:Url('opendir')}?dir={$dir}&filedir={$vo.filename}">{$vo.filename}</a></span>
                            </td>
                            <td class="text-left">
                                <span> {$vo.size}</span>
                            </td>
                            <td class="text-left">
                                <span>  {if condition="$vo.isWritable"}可写{else/}不可写{/if}</span>
                            </td>
                            <td class="text-left">
                                <span>  {$vo.mtime|date='Y-m-d H:i:s'}</span>
                            </td>


                            <td class="text-center">
                                <a class="btn btn-info btn-xs" href="{:Url('opendir')}?dir={$dir}&filedir={$vo.filename}"><i class="fa fa-paste"></i> 打开</a>

                            </td>
                        </tr>
                        {/volist}
                        {volist name="fileAll['file']" id="vo"}
                        <tr>
                            <td class="text-left">
                                <span onclick="$eb.createModalFrame('{$vo.filename}','{:Url('openfile')}?file={$vo.pathname}',{w:1260,h:600})"> <i class="fa fa-file-text-o"></i> {$vo.filename}</span>
                            </td>
                            <td class="text-left">
                                <span> {$vo.size}</span>
                            </td>
                            <td class="text-left">
                                <span>  {if condition="$vo.isWritable"}可写{else/}不可写{/if}</span>
                            </td>
                            <td class="text-left">
                                <span>  {$vo.mtime|date='Y-m-d H:i:s'}</span>
                            </td>

                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('{$vo.filename}','{:Url('openfile')}?file={$vo.pathname}',{w:1260,h:660})"><i class="fa fa-paste"></i> 编辑</button>
<!--                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('{$vo.filename}','{:Url('openfile')}?file={$vo.filename}&dir={$dir}',{w:1260,h:600})"><i class="fa fa-paste"></i> 重命名</button>-->
<!--                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('{$vo.filename}','{:Url('openfile')}?file={$vo.filename}&dir={$dir}',{w:1260,h:600})"><i class="fa fa-paste"></i> 删除</button>-->
<!--                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('{$vo.filename}','{:Url('openfile')}?file={$vo.filename}&dir={$dir}',{w:1260,h:600})"><i class="fa fa-paste"></i> 下载</button>-->

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
