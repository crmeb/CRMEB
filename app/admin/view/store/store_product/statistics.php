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
                        <form action="" class="form-inline">
                            <div class="search-item" data-name="data">
                                <span>创建时间：</span>
                                <button type="button" class="btn btn-outline btn-link" data-value="">全部</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.today}">今天</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.week}">本周</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.month}">本月</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.quarter}">本季度</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.year}">本年</button>
                                <div class="datepicker" style="display: inline-block;">
                                    <button type="button" class="btn btn-outline btn-link" data-value="{$where.data?:'no'}">自定义</button>
                                </div>
                                <input class="search-item-value" type="hidden" name="data" value="{$where.data}" />
                            </div>
                            {volist name='header' id='val'}
                            <div class="col-sm-3">
                                <div class="widget style1 {$val.color}-bg" style="height: 120px;">
                                    <div class="row" style="margin-top: 16px;padding: 0 20px;">
                                        <div class="col-xs-4">
                                            <i class="fa {$val.class} fa-5x"></i>
                                        </div>
                                        <div class="col-xs-8 text-right">
                                            <span> {$val.name} </span>
                                            <h2 class="font-bold">{$val.value}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/volist}

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>商品销量折线图</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                <div  id="ec-goods-count" style="height:390px;">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>销量排行前十</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>

                    </div>
                </div>
                <div class="ibox-content">
                    <div style="float: left;">
                        当月前十商品销售总计： <span style="color: lightblue;"><?php  echo $total; ?></span>件
                        共计 <span style="color: coral;"><?php  echo $price; ?></span>元
                    </div>
                    <div class="search-item" data-name="sex" style="float: right;">
                        <input class="search-item-value" type="hidden" name="sex" value="{$where.sex}" />
                        <button type="button" class="btn btn-outline btn-link" data-value="2" style="border-radius:0;border-left: 1px solid #eee;border-top: 1px solid #eee;border-bottom: 1px solid #eee;">全部</button><button type="button" class="btn btn-outline btn-link" data-value="1" style="border-radius:0;border-top: 1px solid #eee;border-bottom: 1px solid #eee;" >男</button><button type="button" class="btn btn-outline btn-link" data-value="0" style="border-radius:0;border-right: 1px solid #eee;border-top: 1px solid #eee;border-bottom: 1px solid #eee;">女</button>
                    </div>
                    <div class="ibox-content">
                        <div  id="ec-order-count1" style="height:390px;">
                        {notempty name="stores"}
                        {volist name="stores" id="vo"}
                        <div style="float: left;width: 100px;">{$vo.name}</div>
                        <div class="progress">
                            <span style="margin-left: 8px">{$vo.sum}</span>
                            <div class="progress-bar {$vo.color}" role="progressbar" aria-valuenow="{$vo.sum}" aria-valuemin="0" aria-valuemax="<?php  echo $storeSum1; ?>" style="width: <?php if($storeSum1>0){ ?> <?php $b=$vo['sum']/$storeSum1; echo ceil($b*100);}?>%  ">
                                <span class="sr-only">40% Complete (success)</span>
                            </div>
                        </div>
                        {/volist}
                        {/notempty}
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-sm-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>利润排行前十</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <!--                                            <a class="close-link">-->
                        <!--                                                <i class="fa fa-times"></i>-->
                        <!--                                            </a>-->
                    </div>
                </div>
                <div class="ibox-content">
                    <div style="float: left;">
                        当月前十商品利润总计： <span style="color: lightblue;"><?php  echo $total1; ?></span>件
                        共计 <span style="color: coral;"><?php  echo $price1; ?></span>元
                    </div>
                    <div class="search-item" data-name="sex1" style="float: right;">
                        <input class="search-item-value" type="hidden" name="sex1" value="{$where.sex1}" />
                        <button type="button" class="btn btn-outline btn-link" data-value="2" style="border-radius:0;border-left: 1px solid #eee;border-top: 1px solid #eee;border-bottom: 1px solid #eee;">全部</button><button type="button" class="btn btn-outline btn-link" data-value="1" style="border-radius:0;border-top: 1px solid #eee;border-bottom: 1px solid #eee;" >男</button><button type="button" class="btn btn-outline btn-link" data-value="0" style="border-radius:0;border-right: 1px solid #eee;border-top: 1px solid #eee;border-bottom: 1px solid #eee;">女</button>
                    </div>
                    <div class="ibox-content" id="ec-order-count2" style="height:390px;">
                        {notempty name="stor"}
                        {volist name="stor" id="vs"}
                        <div style="float: left;width: 100px;">{$vs.name}</div>
                        <div class="progress">
                            <span style="margin-left: 8px">￥<?php echo $vs['price']*$vs['piece'];?></span>

                            <div class="progress-bar <?php echo $vs['color'];?>" role="progressbar" aria-valuenow="<?php echo $vs['price']*$vs['piece'];?>" aria-valuemin="11" aria-valuemax="<?php echo $priceSum;?>"
                                 style="width: <?php if($priceSum>0){?><?php $p=$vs['price']*$vs['piece']/$priceSum; echo ceil($p*100);}?>%">
                                <span class="sr-only">Complete</span>
                            </div>

                        </div>
                        {/volist}
                        {/notempty}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12" style="margin-bottom: 20px;">
        <div class="col-sm-4" >
            <div class="ibox float-e-margins">
                <div style="border: 1px solid #eee;">
                    <div class="ibox-title" >
                        <h5>待补货商品</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link  save-height">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <!--                                                <a class="close-link">-->
                            <!--                                                    <i class="fa fa-times"></i>-->
                            <!--                                                </a>-->
                        </div>
                    </div>
                    <div class="ibox-content replenishment" style="height: 300px;width: 100%;overflow: scroll;overflow-x:hidden">
                        <table style="width: 100%;" class="tab">
                            <tr style="background-color: #fff;height: 40px;">
                                <th>商品信息</th>
                                <th>商品单价</th>
                                <th>库存数量</th>
                                <th>操作</th>
                            </tr>
                            {volist name="stock" id="vk"}
                            <tr class="tr1" style="height: 36px;line-height: 36px;">
                                <td>{$vk.store_name}</td>
                                <td>{$vk.price}</td>
                                <td>{$vk.stock}</td>
                                <td>
                                    <!--                                                        <button type="button" class="btn btn-xs btn-link" data-id="{$vk.id}" style="border: 1px solid #eee;" onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vk['id']))}')">编辑</button>-->
                                    <button class="btn  btn-xs " type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vk['id']))}')"><i class="fa fa-paste"></i> 编辑</button>
                                </td>
                            </tr>
                            {/volist}
                        </table>
                    </div>

                </div>

            </div>
        </div>
        <div class="col-sm-4">
            <div class="ibox float-e-margins">
                <div style="border: 1px solid #eee;">
                    <div class="ibox-title">
                        <h5>差评商品</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <!--                                            <a class="close-link">-->
                            <!--                                                <i class="fa fa-times"></i>-->
                            <!--                                            </a>-->
                        </div>
                    </div>
                    <div class="ibox-content">
                        <table style="width: 100%;">
                            <tr style="background-color: #fff;height: 40px;">
                                <th>商品信息</th>
                                <th>商品单价</th>
                                <th>差评数量</th>
                                <th>操作</th>
                            </tr>
                            {volist name="stor1" id="st"}
                            <tr style="height: 36px;line-height: 36px;">
                                <td>{$st.name}</td><td>{$st.price}</td><td>{$st.sun}</td>
                                <td>
                                    <button class="btn btn-xs " type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$st['uid']))}')"><i class="fa fa-paste"></i> 编辑</button>
                                </td>
                            </tr>
                            {/volist}
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ibox float-e-margins">
                <div style="border: 1px solid #eee;">
                    <div class="ibox-title">
                        <h5>退货商品</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <!--                                            <a class="close-link">-->
                            <!--                                                <i class="fa fa-times"></i>-->
                            <!--                                            </a>-->
                        </div>
                    </div>
                    <div class="ibox-content">
                        <table style="width: 100%;">
                            <tr style="background-color: #fff;height: 40px;">
                                <th>商品信息</th>
                                <th>商品单价</th>
                                <th>退货数量</th>
                                <th>操作</th>
                            </tr>
                            {volist name="refund" id="st"}
                            <tr style="height: 36px;line-height: 36px;">
                                <td>{$st.name}</td><td>{$st.price}</td><td>{$st.sun}</td>
                                <td>
                                    <button class="btn btn-xs " type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$st['sid']))}')"><i class="fa fa-paste"></i> 编辑</button>
                                </td>
                            </tr>
                            {/volist}
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
<script>
    (function(){
        var option = {
            //设置标题
            title:{
//                                            text:'销量图',
//                                            subtext:'商品销量。'
            },
            //设置提示
            tooltip: {
                show: true
            },
            //设置图例
            legend: {
                data:['销量']
            },
            //设置坐标轴
            xAxis : [
                {
                    type : 'category',
                    data : <?=json_encode($orderDays)?>
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            //设置数据
            series : [
                //折线图
                {
                    "name":"销量",
                    "type":"line",
                    "data":<?=json_encode($sum)?>,
                    //绘制平均线
                    markLine : {
                        data : [
                            {type : 'average', name: '平均值'}
                        ]
                    },
                    //绘制最高最低点
                    markPoint : {
                        data : [
                            {type : 'max', name: '最大值'},
                            {type : 'min', name: '最小值'}
                        ]
                    }
                }

            ]
        };

        var myChart = echarts.init(document.getElementById('ec-goods-count'));
        myChart.setOption(option);
    })();
</script>

<script>
    $(".footable").footable();
    $(".no-sort").off('click');
    $('.search-item>.btn').on('click',function(){
        var that = $(this),value = that.data('value'),p = that.parent(),name = p.data('name'),form = p.parents();
        form.find('input[name="'+name+'"]').val(value);
        form.submit();
    });
    $('.search-item-value').each(function(){
        var that = $(this),name = that.attr('name'), value = that.val(),dom = $('.search-item[data-name="'+name+'"] .btn[data-value="'+value+'"]');
        dom.eq(0).removeClass('btn-outline btn-link').addClass('btn-primary')
            .siblings().addClass('btn-outline btn-link').removeClass('btn-primary')
    });
    $(".btn").mouseover(function (event){
            $(this).closest('table').find("tr:eq(1)").find("td:eq(3)").find("button:eq(0)").removeClass('btn-info');
            $(event.target).addClass('btn-info').siblings().removeClass('btn-info');

    }).mouseout(function (){
        $(event.target).removeClass('btn-info');
//        $(this).closest('table').find("tr:eq(1)").find("td:eq(3)").find("button:eq(0)").addClass('btn-info');
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
</script>
{/block}
