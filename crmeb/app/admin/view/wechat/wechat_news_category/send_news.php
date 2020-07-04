{extend name="public/container"}
{block name="head_top"}
<link href="{__ADMIN_PATH}module/wechat/news_category/css/style.css" type="text/css" rel="stylesheet">
<script src="{__FRAME_PATH}js/content.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <blockquote class="layui-elem-quote layui-quote-nm">
                    由于微信公众号限制,只能推送一条文章,超过一条的文章会推送失败
                </blockquote>
                <div class="ibox-tools">
                    <button class="btn btn-white btn-sm" onclick="location.reload()"><i class="fa fa-refresh"></i> 刷新</button>
                </div>
                <div style="margin-top: 2rem"></div>
                <div class="row">
                    <div class="col-sm-8 m-b-xs">
                        <form action="" class="form-inline">
                            <i class="fa fa-search" style="margin-right: 10px;"></i>
                            <div class="input-group">
                                <input type="text" name="cate_name" value="{$where.cate_name}" placeholder="请输入关键词" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="ibox-content">
                <div id="news_box">
                {volist name="list" id="vo"}
                    <div class="news_item" >
                        <div class="title" ><span>图文名称：{$vo.cate_name}</span></div>
                    {volist name="$vo['new']" id="vvo" key="k"}
                        {if condition="$k eq 1"}
                        <div class="news_tools hide">
                            <a href="javascript:void(0)" data-url="{:Url('push',array('id'=>$vo['id'],'new_id'=>$vo.new[0]['id'],'wechat'=>$wechat))}" class="push">推送</a>
                        </div>
                        <div class="news_articel_item" style="background-image:url({$vvo.image_input})">
                            <p>{$vvo.title}</p>
                        </div>
                        <div class="hr-line-dashed"></div>
                        {else/}
                        <div class="news_articel_item other">
                            <div class="right-text">{$vvo.title}</div>
                            <div class="left-image" style="background-image:url({$vvo.image_input});">
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        {/if}
                    {/volist}
                    </div>
                {/volist}
                </div>
            </div>
        </div>
    </div>
</div>
<div style="margin-left: 10px">
    {include file="public/inner_page"}
</div>
{/block}
{block name="script"}
<script>
    $('body').on('mouseenter', '.news_item', function () {
        $(this).find('.news_tools').removeClass('hide');
    }).on('mouseleave', '.news_item', function () {
        $(this).find('.news_tools').addClass('hide');
    });
    $('.push').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        swal({
            title: "您确定要发送消息吗？",
            text:"发送后将无法撤回，请谨慎操作！",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText:"是的，我要发送！",
            cancelButtonText:"让我再考虑一下…",
            closeOnConfirm: false,
            closeOnCancel: false
        }).then(function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) swal(res.data.msg);
                else swal('发送失败');
            }).catch(function(err){
                swal(err);
            });
        }).catch(console.log);
    });
</script>
{/block}