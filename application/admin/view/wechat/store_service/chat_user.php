{include file="public/frame_head"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">用户名称</th>
                            <th class="text-center">用户头像</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">{$vo.nickname}</td>
                            <td class="text-center"><img src="{$vo.headimgurl}" class="head_image" data-image="{$vo.headimgurl}" width="35" height="35"></td>
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button" onclick="window.location.href='{:Url('chat_list',array('uid'=>$now_service['uid'],'to_uid'=>$vo['uid']))}'"><i class="fa fa-commenting-o"></i> 查看对话</button>
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
{include file="public/inner_footer"}
