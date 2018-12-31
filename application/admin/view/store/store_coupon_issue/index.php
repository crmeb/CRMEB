{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">

                        <form action="" class="form-inline">
                            <select name="status" aria-controls="editable" class="form-control input-sm">
                                <option value="">状态</option>
                                <option value="1" {eq name="where.status" value="1"}selected="selected"{/eq}>正常</option>
                                <option value="0" {eq name="where.status" value="0"}selected="selected"{/eq}>未开启</option>
                                <option value="2" {eq name="where.status" value="2"}selected="selected"{/eq}>已过期</option>
                                <option value="2" {eq name="where.status" value="2"}selected="selected"{/eq}>已失效</option>
                            </select>
                            <div class="input-group">
                                <input type="text" name="coupon_title" value="{$where.coupon_title}" placeholder="请输入优惠券名称" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>

                            <th class="text-center">编号</th>
                            <th class="text-center">优惠券名称</th>
                            <th class="text-center">领取日期</th>
                            <th class="text-center">发布数量</th>
                            <th class="text-center">状态</th>
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
                                {$vo.title}
                            </td>
                            <td class="text-center">
                                {empty name="$vo.start_time"}
                                不限时
                                {else/}
                                {$vo.start_time|date="Y/m/d H:i",###} - {$vo.end_time|date="Y/m/d H:i",###}
                                {/empty}
                            </td>
                            <td class="text-center">
                                {empty name="$vo.total_count"}
                                不限量
                                {else/}
                                <b style="color: #0a6aa1">发布:{$vo.total_count}</b>
                                    <br/>
                                <b style="color:#ff0000;">剩余:{$vo.remain_count}</b>
                                {/empty}
                            </td>
                            <td class="text-center">
                                <?php if(!$vo['status']){ ?>
                                <span class="label label-warning">未开启</span>
                                <?php }elseif(-1 == $vo['status']){ ?>
                                    <span class="label label-danger">已失效</span>
                                <?php }elseif(1 == $vo['status']){ ?>
                                <span class="label label-primary">正常</span>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('修改状态','{:Url('issue_log',array('id'=>$vo['id']))}')"><i class="fa fa-commenting-o"></i> 领取记录</button>
                                {neq name="vo.status" value="-1"}
                                <button class="btn btn-primary btn-xs" type="button"  onclick="$eb.createModalFrame('修改状态','{:Url('edit',array('id'=>$vo['id']))}',{w:300,h:170})"><i class="fa fa-paste"></i> 修改状态</button>
                                {/neq}
                                <button class="btn btn-danger btn-xs" data-url="{:Url('delete',array('id'=>$vo['id']))}" type="button"><i class="fa fa-warning"></i> 删除
                                </button>
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
<script>
    $('.btn-danger').on('click',function(){
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
        },{'title':'您确定要删除发布的优惠券吗？','text':'删除后将无法恢复,请谨慎操作！','confirm':'是的，我要删除'})
    });
</script>
{/block}
