{extend name="public/container"}
{block name="content"}
<style>
    .dropdown-menu li a i{
        width: 10px;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}?type=0')">添加优惠券</button>
                <div class="ibox-tools">

                </div>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">

                            <select name="status" aria-controls="editable" class="form-control input-sm">
                                <option value="">是否有效</option>
                                <option value="1" {eq name="where.status" value="1"}selected="selected"{/eq}>开启</option>
                                <option value="0" {eq name="where.status" value="0"}selected="selected"{/eq}>关闭</option>
                            </select>
                            <select name="type" aria-controls="editable" class="form-control input-sm">
                                <option value="">类型</option>
                                <option value="0" {eq name="where.type" value="0"}selected="selected"{/eq}>通用券</option>
                                <option value="1" {eq name="where.type" value="1"}selected="selected"{/eq}>品类券</option>
                                <option value="2" {eq name="where.type" value="2"}selected="selected"{/eq}>商品券</option>
                            </select>

                            <div class="input-group">
                                <input type="text" name="title" value="{$where.title}" placeholder="请输入优惠券名称" class="input-sm form-control">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="table-responsive" style="overflow:visible">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>

                            <th class="text-center">编号</th>
                            <th class="text-center">优惠券名称</th>
                            <th class="text-center">优惠券类型</th>
                            <th class="text-center">优惠券面值</th>
                            <th class="text-center">优惠券最低消费</th>
                            <th class="text-center">优惠券有效期限</th>
                            <th class="text-center">排序</th>
                            <th class="text-center">是否有效</th>
                            <th class="text-center">添加时间</th>
                            <th class="text-center" width="10%">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.id}
                            </td>
                            <td class="text-center">
                                {$vo.title}
                            </td>
                            <td class="text-center">
                                {if $vo.type eq 0}
                                通用券
                                {elseif $vo.type eq 1}
                                品类券
                                {else/}
                                商品券
                                {/if}
                            </td>
                            <td class="text-center">
                                {$vo.coupon_price}
                            </td>
                            <td class="text-center">
                                {$vo.use_min_price}
                            </td>
                            <td class="text-center">
                                {$vo.coupon_time}天
                            </td>
                            <td class="text-center">
                                {$vo.sort}
                            </td>
                            <td class="text-center">
                                <i class="fa {eq name='vo.status' value='1'}fa-check text-navy{else/}fa-close text-danger{/eq}"></i>
                            </td>
                            <td class="text-center">
                                {$vo.add_time|date='Y-m-d H:i:s'}
                            </td>
                            <td class="text-center">
                                <div class="input-group-btn js-group-btn">
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            {if condition="$vo['status']"}
                                            <li>
                                                <a href="javascript:void(0);" class="del" data-url="{:Url('status',array('id'=>$vo['id']))}">
                                                    <i class="fa  fa-yelp"></i> 立即失效
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame(this.innerText+' [{$vo.title}] 优惠劵','{:Url('issue',['id'=>$vo['id']])}')">
                                                    <i class="fa  fa-male"></i> 发布
                                                </a>
                                            </li>
                                            {/if}
                                            <li>
                                                <a href="javascript:void(0);" class="delstor" data-url="{:Url('delete',array('id'=>$vo['id']))}">
                                                    <i class="fa fa-times"></i> 删除
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
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
    $('.js-group-btn').on('click',function(){
        $('.js-group-btn').css({zIndex:1});
        $(this).css({zIndex:100});
    });
    $('.del').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
//                        _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '修改失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{'title':'您确定要修改优惠券的状态吗？','text':'修改后将无法恢复并且已发出的优惠券将失效,请谨慎操作！','confirm':'是的，我要修改'})
    });
    $('.delstor').on('click',function(){
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
        },{'title':'您确定要删除优惠券吗？','text':'删除后将无法恢复,请谨慎操作！','confirm':'是的，我要删除'})
    });
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
</script>
{/block}
