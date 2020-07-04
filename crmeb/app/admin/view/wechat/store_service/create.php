{include file="public/frame_head"}
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
<script src="{__PLUG_PATH}moment.js"></script>
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
<script src="{__ADMIN_PATH}frame/js/plugins/iCheck/icheck.min.js"></script>
<style type="text/css">
    .form-inline .input-group{display: inline-table;vertical-align: middle;}
    .form-inline .input-group .input-group-btn{width: auto;}
    .form-add{position: fixed;left: 0;bottom: 0;width:100%;}
    .form-add .sub-btn{border-radius: 0;width: 100%;padding: 6px 0;font-size: 14px;outline: none;border: none;color: #fff;background-color: #2d8cf0;}
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form class="form-inline search" id="form" method="get">
                            <div class="input-group datepicker">
                                <input style="width: 200px;" type="text" id="data" class="input-sm form-control"  readonly name="data" value="{$where.data}" placeholder="请选择日期" >
                            </div>
                            <div class="input-group">
                                <input style="width: 200px;" type="text" name="nickname" value="{$where.nickname}" placeholder="请输入微信用户名称" class="input-sm form-control"> <span class="input-group-btn">
                                <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search"></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center"></th>
                                <th class="text-center">编号</th>
                                <th class="text-center">微信用户名称</th>
                                <th class="text-center">头像</th>
                                <th class="text-center">性别</th>
                                <th class="text-center">地区</th>
                                <th class="text-center">是否关注公众号</th>
                                <th class="text-center">首次关注时间</th>
                            </tr>
                        </thead>
                        <tbody class="">
                            <form method="post" class="sub-save">
                                {volist name="list" id="vo"}
                                <tr>
                                    <td class="text-center">
                                        <label class="checkbox-inline i-checks">
                                            <input type="checkbox" name="ids[]" value="{$vo.uid}">
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        {$vo.uid}
                                    </td>
                                    <td class="text-center">
                                        {$vo.nickname}
                                    </td>
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
                                    <td class="text-center">
                                        {$vo.country}{$vo.province}{$vo.city}
                                    </td>
                                    <td class="text-center">
                                        {if condition="$vo['subscribe']"}
                                        关注
                                        {else/}
                                        未关注
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {$vo.time}
                                    </td>
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
</div>
<div class="form-add">
    <button type="submit" class="sub-btn">提交</button>
</div>
<script>
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
    });
    $('.head_image').on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
    var dateInput =$('.datepicker');
    dateInput.daterangepicker({
        autoUpdateInput: false,
        "opens": "center",
        "drops": "down",
        "ranges": {
             '今天': [moment(), moment().add(1, 'days')],
             '昨天': [moment().subtract(1, 'days'), moment()],
             '上周': [moment().subtract(6, 'days'), moment()],
             '前30天': [moment().subtract(29, 'days'), moment()],
             '本月': [moment().startOf('month'), moment().endOf('month')],
             '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "locale" : {
            applyLabel : '确定',
            cancelLabel : '清空',
            fromLabel : '起始时间',
            toLabel : '结束时间',
            format : 'YYYY/MM/DD',
            customRangeLabel : '自定义',
            daysOfWeek : [ '日', '一', '二', '三', '四', '五', '六' ],
            monthNames : [ '一月', '二月', '三月', '四月', '五月', '六月',
                '七月', '八月', '九月', '十月', '十一月', '十二月' ],
            firstDay : 1
        }
    });
    dateInput.on('cancel.daterangepicker', function(ev, picker) {
        $("#data").val('');
    });
    dateInput.on('apply.daterangepicker', function(ev, picker) {
        $("#data").val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
    });
    $(".sub-btn").on("click",function(){
        var formData = {checked_menus:[]};
        $("input[name='ids[]']:checked").each(function(){
            formData.checked_menus.push($(this).val());
        });
        $eb.axios.post("{$save}",formData).then((res)=>{
            if(res.status && res.data.code == 200)
                return Promise.resolve(res.data);
            else
                return Promise.reject(res.data.msg || '添加失败,请稍候再试!');
        }).then((res)=>{
            $eb.message('success',res.msg || '操作成功!');
            $eb.closeModalFrame(window.name);
            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
        }).catch((err)=>{
            this.loading=false;
            $eb.message('error',err);
        });
    })
</script>
{include file="public/inner_footer"}
