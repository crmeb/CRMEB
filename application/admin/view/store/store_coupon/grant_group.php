{extend name="public/container"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/content.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-8 m-b-xs">
                        <form action="" class="form-inline">
                            <i class="fa fa-search" style="margin-right: 10px;"></i>
                            <div class="input-group" style="width: 80%">
                                <input type="text" name="title" value="{$where.title}" placeholder="请输入优惠券名称" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">优惠券名称</th>
                            <th class="text-center">优惠券面值</th>
                            <th class="text-center">优惠券最低消费</th>
                            <th class="text-center">优惠券有效期限</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.title}
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
                                <button class="btn btn-primary btn-xs grant" data-id="{$vo['id']}" data-url="{:Url('store.storeCouponUser/grant_group',array('id'=>$vo['id']))}" type="button"><i class="fa  fa-arrow-circle-o-right"></i> 发放
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
{/block}
{block name="script"}
<script>
    var group = {$group};
    $('.grant').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        var option = {};
        $.each(group,function (item,index) {
            option[index.id] = index.name;
        })
        var inputOptions = new Promise(function(resolve) {
            resolve(option);
        });
        swal({
            title: '请选择要给哪个组发放',
            input: 'radio',
            inputOptions: inputOptions,
            inputValidator: function(result) {
                return new Promise(function(resolve, reject) {
                    if (result) {
                        resolve();
                    } else {
                        reject('请选择要给哪个组发放优惠卷');
                    }
                });
            }
        }).then(function(result) {
            if (result) {
                swal({
                    title: "您确定要发放优惠券吗？",
                    text:"发放后将无法撤回，请谨慎操作！",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText:"是的，我要发放！",
                    cancelButtonText:"让我再考虑一下…",
                    closeOnConfirm: false,
                    closeOnCancel: false
                }).then(function(){
                    $eb.axios.post(url,{group:result}).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            swal(res.data.msg);
                        }else
                            return Promise.reject(res.data.msg || '发放失败')
                    }).catch(function(err){
                        swal(err);
                    });
                }).catch(console.log);
            }
        })
    });
</script>
{/block}
