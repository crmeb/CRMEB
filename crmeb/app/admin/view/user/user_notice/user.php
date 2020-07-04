{include file="public/frame_head"}
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
<script src="{__PLUG_PATH}moment.js"></script>
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
<script src="{__ADMIN_PATH}frame/js/plugins/iCheck/icheck.min.js"></script>
<div class="row">
    <div class="col-sm-12">
        {if condition="$notice['is_send'] eq 0 AND $notice['type'] eq 2"}
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('user_create',array('id'=>$notice['id']))}')">添加发送用户</button>
                <button type="button" class="btn btn-w-m btn-primary select-delete" data-url="{:Url('user_select_delete',array('id'=>$notice['id']))}">选择删除</button>
            </div>
        {/if}
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped  table-bordered">
                    <thead>
                    <tr>
                        {if condition="$notice['is_send'] eq 0 AND $notice['type'] eq 2"}<th class="text-center"></th>{/if}
                        <th class="text-center">编号</th>
                        <th class="text-center">微信用户名称</th>
                        <th class="text-center">头像</th>
                        <th class="text-center">性别</th>
                        {if condition="$notice['is_send'] eq 1 AND $notice['type'] eq 2"}<th class="text-center">是否查看</th>{/if}
                        {if condition="$notice['is_send'] eq 0 AND $notice['type'] eq 2"}<th class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody class="">
                        <form method="post" class="sub-save">
                            {volist name="list" id="vo"}
                            <tr>
                                {if condition="$notice['is_send'] eq 0 AND $notice['type'] eq 2"}
                                    <td class="text-center">
                                        <label class="checkbox-inline i-checks">
                                            <input type="checkbox" name="ids[]" value="{$vo.uid}">
                                        </label>
                                    </td>
                                {/if}
                                <td class="text-center">{$vo.uid}</td>
                                <td class="text-center">{$vo.nickname}</td>
                                <td class="text-center">
                                    <img src="{$vo.headimgurl}" alt="{$vo.nickname}" title="{$vo.nickname}" style="width:50px;height: 50px;cursor: pointer;" class="head_image" data-image="{$vo.headimgurl}">
                                </td>
                                <td class="text-center">
                                    {if condition="$vo['sex'] eq 1"}
                                        男
                                    {elseif condition="$vo['sex'] eq 2"/}
                                        女
                                    {else/}
                                        保密
                                    {/if}
                                </td>
                                {if condition="$notice['is_send'] eq 1 AND $notice['type'] eq 2"}
                                    <td class="text-center">
                                        {if condition="$vo['is_see'] eq 1"}
                                            <span style="color:green">是</span>
                                        {else/}
                                            <span style="color:red">否</span>
                                        {/if}
                                    </td>
                                {/if}
                                {if condition="$notice['is_send'] eq 0 AND $notice['type'] eq 2"}
                                    <td class="text-center">
                                        <button class="btn btn-danger btn-xs " data-url="{:Url('user_delete',array('id'=>$notice['id'],'uid'=>$vo['uid']))}" type="button"><i class="fa fa-times"></i> 删除</button>
                                    </td>
                                {/if}
                            </tr>
                            {/volist}
                        </form>
                    </tbody>
                </table>
            </div>
            {include file="public/inner_page"}
        </div>
    </div>
</div>

<script>
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
    });
    $('.btn-danger').on('click',function(){
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

    $(".select-delete").on("click",function(){
        var url = $(this).data('url');
        var formData = {checked_menus:[]};
        $("input[name='ids[]']:checked").each(function(){
            formData.checked_menus.push($(this).val());
        });
        if(formData.checked_menus.length <= 0){
            $eb.message('error',"删除数据不能为空");
            return;
        }
        $eb.$swal('delete',function(){
            $eb.axios.post(url,formData).then((res)=>{
                if(res.status && res.data.code == 200)
                    return Promise.resolve(res.data);
                else
                    return Promise.reject(res.data.msg || '删除失败,请稍候再试!');
            }).then((res)=>{
                $eb.message('success',res.msg || '操作成功!');
                $eb.closeModalFrame(window.name);
                $("input[name='ids[]']:checked").each(function(){
                    $(this).parents('tr').remove();
                });
            }).catch((err)=>{
                this.loading=false;
                $eb.message('error',err);
            });
        },{
            title:"您确定要删除这些信息吗",
            text:"删除后将无法回复信息，请谨慎操作！",
            confirm:"是的，我要删除！",
            cancel:"让我再考虑一下"
        })
    });

    $('.head_image').on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    });
</script>
{include file="public/inner_footer"}