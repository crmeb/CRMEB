{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加通知</button>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">发送人</th>
                            <th class="text-center">通知标题</th>
                            <th class="text-center" width="700">通知内容</th>
                            <th class="text-center">消息类型</th>
                            <th class="text-center">是否发送</th>
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
                                {if condition="$vo['type'] eq 1"}
                                    系统消息
                                {elseif condition="$vo['type'] eq 2" /}
                                    用户通知
                                {/if}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['is_send'] eq 1"}
                                    状态：<span style="color:green;">已发送</span><br />
                                    时间：{$vo.send_time|date='Y-m-d H:i:s',###}
                                {else /}
                                    状态：<span style="color:red;">未发送</span> <a class="btn-send" data-url="{:Url('send',array('id'=>$vo['id']))}">立即发送</a>
                                {/if}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="window.location.href='{:Url('user',array('id'=>$vo['id']))}'"><i class="fa fa-user"></i> 用户信息</button>
                                {if condition="$vo['is_send'] eq 0"}
                                    <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-paste"></i> 编辑</button>
                                {/if}
                                <button class="btn btn-warning btn-xs" data-url="{:Url('delete',array('id'=>$vo['id']))}" type="button"><i class="fa fa-warning"></i> 删除</button>
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
    $('.btn-warning').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
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
    $('.btn-send').on('click',function(){
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    window.location.reload();
                }else
                    return Promise.reject(res.data.msg || '发送失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{
            title:"您确定要发送这条信息吗",
            text:"发送后将无法修改通知信息，请谨慎操作！",
            confirm:"是的，我要发送！",
            cancel:"让我再考虑一下"
        })
    });
    $('.head_image').on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
</script>
{/block}
