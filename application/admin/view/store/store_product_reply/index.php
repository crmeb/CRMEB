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
                                    <div class="well">
                                        {$vo.comment}
                                        <br/>
                                        <?php  if(!empty($vo['pics'])) $image = explode(",",$vo['pics'][0]); else $image = [];?>
                                        {if condition="$image"}
                                            {volist name="image" id="v"}
                                            <img src="{$v}"  class="open_image m-t-sm" data-image="{$v}" style="width: 50px;height: 50px;cursor: pointer;">
                                            {/volist}
                                        {/if}
                                    </div>

                                        <p class="text-right">
                                        <div class="btn-group">
                                            {if condition="$vo['is_reply'] eq 2"}
                                            <button class="btn btn-info btn-xs reply_update"  data-url="{:Url('set_reply')}"  data-content="{$vo['merchant_reply_content']}" data-id="{$vo['id']}"><i class="fa fa-paste"></i> 编辑</button>
                                            {else/}
                                            <button class="btn btn-primary btn-xs reply"  data-url="{:Url('set_reply')}" data-id="{$vo['id']}"><i class="fa fa-comments"></i> 回复</button>
                                            {/if}
                                            <button class="btn btn-warning btn-xs delete" data-url="{:Url('delete',array('id'=>$vo['id']))}"><i class="fa fa-times"></i> 删除</button>
                                        </div>
                                        </p>


                                </div>
                                {if condition="$vo['merchant_reply_content']"}
                                <div class="social-footer">
                                    <div class="social-comment">
                                        <div class="media-body">回复时间：<small class="text-muted">{$vo.merchant_reply_time|date='Y-m-d H:i:s',###}</small></div>
                                    </div>
                                        <div class="well m">
                                            <p>{$vo['merchant_reply_content']}</p>
                                        </div>

                                </div>
                                {/if}
                        </div>
                    </div>
                    {/volist}
                </div>
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
