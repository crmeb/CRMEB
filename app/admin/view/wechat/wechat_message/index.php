{extend name="public/container"}
{block name="head_top"}
<script src="{__PLUG_PATH}moment.js"></script>
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline" id="form">

                            <select name="type" aria-controls="editable" class="form-control input-sm">
                                <option value="">操作名称</option>
                                {volist name="$mold" id="vo" key="k"}
                                 <option value="{$key}" {eq name="where.type" value="$key"}selected="selected"{/eq}>{$vo}</option>
                                {/volist}
                            </select>
                            <div class="input-group datepicker">
                                <input style="width: 188px;" type="text" id="data" class="input-sm form-control" name="data" value="{$where.data}" placeholder="请选择日期" >
                            </div>
                            <div class="input-group">
                                <input type="text" name="nickname" value="{$where.nickname}" placeholder="请输入操作用户" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search" ></i> 搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">操作用户</th>
                            <th class="text-center">操作名称</th>
                            <th class="text-center">关联内容</th>
                            <th class="text-center">操作时间</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.id}
                            </td>
                            <td class="text-center">
                                {$vo.nickname}
                            </td>
                            <td class="text-center">
                                {$vo.type_name}
                            </td>
                            <td class="text-center">
                                {$vo['result_arr']['msg']}
                            </td>
                            <td class="text-center">
                                {$vo.add_time|date="Y-m-d H:i:s"}
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
</script>
{/block}
