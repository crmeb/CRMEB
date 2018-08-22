{extend name="public/container"}
{block name="head_top"}
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">
                            <select name="is_reply" aria-controls="editable" class="form-control input-sm">
                                <option value="">评论状态</option>
                                <option value="0" {eq name="where.is_reply" value="0"}selected="selected"{/eq}>未回复</option>
<!--                                <option value="1" {eq name="where.is_reply" value="1"}selected="selected"{/eq}>客户已评价且管理员未回复</option>-->
                                <option value="2" {eq name="where.is_reply" value="2"}selected="selected"{/eq}>已回复</option>
                            </select>
                            <div class="input-group">
                                <input type="text" name="comment" value="{$where.comment}" placeholder="请输入评论内容" class="input-sm form-control" size="38"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="ibox">
                    {volist name="list" id="vo"}
                    <div class="col-sm-12">
                        <div class="social-feed-box">
                            <div class="pull-right social-action dropdown">
                                <button data-toggle="dropdown" class="dropdown-toggle btn-white" aria-expanded="false">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu m-t-xs">
                                    {if condition="$vo['is_reply'] eq 2"}
                                    <li><a href="#" class="reply_update"  data-url="{:Url('set_reply')}"  data-content="{$vo['merchant_reply_content']}" data-id="{$vo['id']}">编辑</a></li>
                                    {else/}
                                    <li><a href="#" class="reply"  data-url="{:Url('set_reply')}" data-id="{$vo['id']}">回复</a></li>
                                    {/if}
                                    <li><a href="#" class="delete" data-url="{:Url('delete',array('id'=>$vo['id']))}">删除</a></li>
                                </ul>
                            </div>
                            <div class="social-avatar">
                                <a href="" class="pull-left">
                                    <img alt="image" src="{$vo.headimgurl}">
                                </a>
                                <div class="media-body">
                                    <a href="#">
                                        {$vo.nickname}
                                    </a>
                                    <small class="text-muted">{$vo.add_time|date='Y-m-d H:i:s',###} 来自产品: {$vo.store_name}</small>
                                </div>
                            </div>
                            <div class="social-body">
                                <p>{$vo.comment}
                                    <?php $image = json_decode($vo['pics'],true);?>
                                    {if condition="$image"}
                                    {volist name="image" id="v"}
                                    <img src="{$v}" alt="{$vo.store_name}" class="open_image" data-image="{$v}" style="width: 50px;height: 50px;cursor: pointer;">
                                    {/volist}
                                    {else/}
                                    [无图]
                                    {/if}
                                </p>
                            </div>
                            {if condition="$vo['merchant_reply_content']"}
                            <div class="social-footer">
                                <div class="social-comment">
                                    <div class="media-body">回复时间：<small class="text-muted">{$vo.merchant_reply_time|date='Y-m-d H:i:s',###}</small></div>
                                    <div class="media-body">
                                        <p>{$vo['merchant_reply_content']}</p>
                                    </div>
                                </div>
                            </div>
                            {/if}
                        </div>
                    </div>
                    {/volist}
                </div>
               <!-- <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">产品名称</th>
                            <th class="text-center">评论人</th>
                            <th class="text-center">评论内容</th>
                            <th class="text-center">评论图片</th>
                            <th class="text-center">评论时间</th>
                            <th class="text-center">回复内容</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.id}
                            </td>
                            <td class="text-center">
                                {$vo.store_name}
                            </td>
                            <td class="text-center">
                                {$vo.nickname}
                            </td>
                            <td class="text-center">
                                {$vo.comment}
                            </td>
                            <td class="text-center">
                                <?php /*$image = json_decode($vo['pics'],true);*/?>
                                {if condition="$image"}
                                {volist name="image" id="v"}
                                <img src="{$v}" alt="{$vo.store_name}" class="open_image" data-image="{$v}" style="width: 50px;height: 50px;cursor: pointer;">
                                {/volist}
                                {else/}
                                无图
                                {/if}
                            </td>
                            <td class="text-center">
                                {$vo.add_time|date='Y-m-d H:i:s',###}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['merchant_reply_content']"}
                                {$vo['merchant_reply_content']}
                                <br/>
                                {$vo.merchant_reply_time|date='Y-m-d H:i:s',###}
                                {elseif condition="$vo['comment']"/}
                                <button class="reply btn btn-primary btn-xs" data-url="{:Url('set_reply')}" data-id="{$vo['id']}" type="button"><i class="fa fa-eyedropper"></i> 回复
                                </button>
                                {else/}
                                未评论
                                {/if}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['is_reply'] eq 2"}
                                <button class="reply_update btn btn-info btn-xs" data-url="{:Url('edit_reply')}" data-content="{$vo['merchant_reply_content']}" data-id="{$vo['id']}" type="button"><i class="fa fa-paste"></i> 修改
                                </button>
                                {/if}
                                <button class="btn btn-warning btn-xs" data-url="{:Url('delete',array('id'=>$vo['id']))}" type="button"><i class="fa fa-warning"></i> 删除
                                </button>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>-->
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.delete').on('click',function(){
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
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
    $('.reply').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url'),rid =_this.data('id');
        $eb.$alert('textarea',{'title':'请输入回复内容','value':''},function(result){
            $eb.axios.post(url,{content:result,id:rid}).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.swal(res.data.msg);
                }else
                    $eb.swal(res.data.msg);
            });
        })
    });
    $('.reply_update').on('click',function (e) {
        window.t = $(this);
        var _this = $(this),url =_this.data('url'),rid =_this.data('id'),content =_this.data('content');
        $eb.$alert('textarea',{'title':'请输入回复内容','value':content},function(result){
            $eb.axios.post(url,{content:result,id:rid}).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.swal(res.data.msg);
                }else{
                    $eb.swal(res.data.msg);
                }
            });
        })
    });
</script>
{/block}
