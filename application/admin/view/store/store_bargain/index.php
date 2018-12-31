{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<link href="{__FRAME_PATH}css/plugins/footable/footable.core.css" rel="stylesheet">
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<script src="{__PLUG_PATH}moment.js"></script>
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
<script src="{__PLUG_PATH}echarts.common.min.js"></script>
<script src="{__FRAME_PATH}js/plugins/footable/footable.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">

        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline" id="form">
                            <div class="search-item" data-name="data">
                                <span>创建时间：</span>
                                <button style="height: 26px;line-height: 12px;font-size: 12px;" type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.today}">今天</button>
                                <button style="height: 26px;line-height: 12px;font-size: 12px;" type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.week}">本周</button>
                                <button style="height: 26px;line-height: 12px;font-size: 12px;" type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.month}">本月</button>
                                <button style="height: 26px;line-height: 12px;font-size: 12px;" type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.quarter}">本季度</button>
                                <button style="height: 26px;line-height: 12px;font-size: 12px;" type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.year}">本年</button>
                                <div class="datepicker" style="display: inline-block;">
                                    <button style="height: 26px;line-height: 12px;font-size: 12px;" type="button" class="btn btn-outline btn-link" data-value="{$where.data?:'no'}">自定义</button>
                                </div>
                                <input class="search-item-value" type="hidden" name="data" value="{$where.data}" />
                            </div>
                            <div class="search-item" data-name="status">
                                <span>砍价状态：</span>
                                <button type="button" class="btn btn-outline btn-link" data-value="">全部</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="1">开启</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="0">关闭</button>
                                <input class="search-item-value" type="hidden" name="status" value="{$where.status}" />
                            </div>

                            <div class="input-group" style="float: right;">
                                <span class="input-group-btn">
                                <input type="hidden" name="export" value="0">
                                <input type="text" name="store_name" value="{$where.store_name}" placeholder="请输入产品名称,关键字,编号" class="input-sm form-control" size="38">
                                 <button type="submit" id="no_export" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i> 搜索</button>
                                <button type="submit" id="export" class="btn btn-sm btn-info btn-outline"> <i class="fa fa-exchange" ></i> Excel导出</button></span>
                                <script>
                                    $('#export').on('click',function(){
                                        $('input[name=export]').val(1);
                                    });
                                    $('#no_export').on('click',function(){
                                        $('input[name=export]').val(0);
                                    });
                                </script>
                            </div>
                        </form>
                    </div>

                </div>

                <div class="table-responsive" style="margin-top: 20px;">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">砍价活动名称</th>
                            <th class="text-center">砍价产品名称</th>
                            <th class="text-center">砍价图片</th>
                            <th class="text-center">砍价价格</th>
                            <th class="text-center">砍价状态</th>
                            <th class="text-center">活动状态</th>
                            <th class="text-center">库存</th>
                            <th class="text-center">排序</th>
                            <th class="text-center">内容</th>
                            <th class="text-center" width="5%">操作</th>
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
                                {$vo.store_name}
                            </td>
                            <td class="text-center">
                                <img src="{$vo.image}" alt="{$vo.store_name}" class="open_image" data-image="{$vo.image}" style="width: 50px;height: 50px;cursor: pointer;">
                            </td>
                            <td class="text-center">
                                {$vo.price}
                            </td>
                            <td class="text-center">
                                <i class="fa {eq name='vo.status' value='1'}fa-check text-navy{else/}fa-close text-danger{/eq}"></i>
                            </td>
                            <td class="text-center">
                                {$vo.start_name}
                            </td>
                            <td class="text-center">
                                {$vo.stock}
                            </td>
                            <td class="text-center">
                                {$vo.sort}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content',array('id'=>$vo['id']))}')"><i class="fa fa-pencil"></i> 编辑内容</button>
                            </td>
                            <td class="text-center">
                                <div class="input-group-btn js-group-btn" style="min-width: 106px;">
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{$vo.store_name}-编辑','{:Url('edit',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-paste"></i> 编辑
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" id="delstor" data-url="{:Url('delete',array('id'=>$vo['id']))}">
                                                    <i class="fa fa-warning"></i> 删除
                                                </a>
                                            </li>
                                        </ul>
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
    $('.search-item>li').on('click', function () {
        var that = $(this), value = that.data('value'), p = that.parent(), name = p.data('name'), form = $('#form');
        form.find('input[name="' + name + '"]').val(value);
        $('input[name=export]').val(0);
        form.submit();
    });
    $('.search-item>.btn').on('click',function(){
        var that = $(this),value = that.data('value'),p = that.parent(),name = p.data('name'),form = p.parents();
        form.find('input[name="'+name+'"]').val(value);
        $('input[name=export]').val(0);
        form.submit();
    });
    $('.search-item-value').each(function(){
        var that = $(this),name = that.attr('name'), value = that.val(),dom = $('.search-item[data-name="'+name+'"] .btn[data-value="'+value+'"]');
        dom.eq(0).removeClass('btn-outline btn-link').addClass('btn-primary btn-sm')
            .siblings().addClass('btn-outline btn-link').removeClass('btn-primary btn-sm')
    });
    $('.search-is>.btn').on('click',function(){
        var that = $(this),value = that.data('value'),p = that.parent(),name = p.data('name'),form = p.parents();
        var valueAdmin = form.find('input[name="'+name+'"]').val();
        if(valueAdmin) value = '';
        else value = 1;
        form.find('input[name="'+name+'"]').val(value);
        $('input[name=export]').val(0);
        form.submit();
    });
    $('.js-group-btn').on('click',function(){
        $('.js-group-btn').css({zIndex:1});
        $(this).css({zIndex:2});
    });
    $('.search-is-value').each(function(){
        var that = $(this),name = that.attr('name'), value = that.val(),dom = $('.search-is[data-name="'+name+'"] .btn[data-value="'+value+'"]');
        dom.eq(0).removeClass('btn-outline btn-link').addClass('btn-primary btn-sm')
            .siblings().addClass('btn-outline btn-link').removeClass('btn-primary btn-sm')
    });
    $('#delstor').on('click',function(){
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
            cancelLabel : '取消',
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
        //$("input[name=limit_time]").val('');
    });
    dateInput.on('apply.daterangepicker', function(ev, picker) {
        $("input[name=data]").val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
        $('input[name=export]').val(0);
        $('form').submit();
    });
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
</script>
{/block}
