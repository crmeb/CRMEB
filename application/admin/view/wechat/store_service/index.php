{extend name="public/container"}
{block name="head_top"}{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加客服</button>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">微信用户名称</th>
                            <th class="text-center">客服头像</th>
                            <th class="text-center">客服名称</th>
                            <th class="text-center">是否显示</th>
                            <th class="text-center">添加时间</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">{$vo.id}</td>
                            <td class="text-center">{$vo.wx_name}</td>
                            <td class="text-center"><img src="{$vo.avatar}" class="head_image" data-image="{$vo.avatar}" width="35" height="35"></td>
                            <td class="text-center">{$vo.nickname}</td>
                            <td class="text-center">
                                <i class="fa {eq name='vo.status' value='1'}fa-check text-navy{else/}fa-close text-danger{/eq}"></i>
                            </td>
                            <td class="text-center">{$vo.add_time|date='Y-m-d H:i:s',###}</td>
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('聊天记录','{:Url('chat_user',array('id'=>$vo['id']))}')"><i class="fa fa-commenting-o"></i> 聊天记录</button>
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-paste"></i> 编辑</button>
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
    {/block}
    {block name="script"}
    <script>
        $('.btn-warning').on('click',function(){
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
        $('.head_image').on('click',function (e) {
            var image = $(this).data('image');
            $eb.openImage(image);
        })
    </script>
</div>
{/block}
