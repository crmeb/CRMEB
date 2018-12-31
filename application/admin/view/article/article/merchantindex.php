{extend name="public/container"}
{block name="head_top"}
<link href="{__ADMIN_PATH}module/wechat/news/css/index.css" type="text/css" rel="stylesheet">
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <div class="row">
                    <div class="m-b-xs">
                        <form action="" class="form-inline">
                            <i class="fa fa-search" style="margin-right: 10px;"></i>
                            <div class="input-group">
                                <input type="text" name="title" value="{$where.title}" placeholder="请输入关键词" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="ibox-content">
                {volist name="list" id="vo"}
                <div id="news_box" >
                    <div class="news_item">
                        <div class="news_tools hide">
<!--                            <a data-phone-view="/wechat/review.html?type=news&amp;content=37" href="javascript:void(0)">预览</a>-->
<!--                            <a data-modal="/wechat/news/push.html?id=37" href="javascript:void(0)">推送</a>-->
                            <a href="{:Url('create',array('id'=>$vo['id']))}">编辑</a>
                            <a href="javascript:void(0)" data-url="{:Url('delete',array('id'=>$vo['id']))}" class="del_news_one">删除</a>
                        </div>
                        <div class="news_articel_item" style="background-image:url({$vo.image_input})">
                            <p>{$vo.admin_name}&nbsp;&nbsp;{$vo.title}</p>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                    <div style="clear:both"></div>
                </div>
                {/volist}
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
    $('.del_news_one').on('click',function(){
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
