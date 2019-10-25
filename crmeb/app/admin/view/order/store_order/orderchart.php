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
                        <form action="" class="form-inline search-form">
                            <div class="search-item" data-name="status">
                                <span>订单状态：</span>
                                <button type="button" class="btn btn-outline btn-link" data-value="">全部</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="0">未支付</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="1">未发货</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="2">待收货</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="3">待评价</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="4">交易完成</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="-1">退款中</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="-2">已退款</button>
                                <input class="search-item-value" type="hidden" name="status" value="{$where.status}" />
                            </div>
                            <div class="search-item" data-name="combination_id">
                                <span>订单类型：</span>
                                <button type="button" class="btn btn-outline btn-link" data-value="">全部</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="普通订单">普通订单</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="拼团订单">拼团订单</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="秒杀订单">秒杀订单</button>
                                <input class="search-item-value" type="hidden" name="combination_id" value="{$where.combination_id}" />
                            </div>
                            <div class="search-item" data-name="data">
                                <span>创建时间：</span>
                                <!--                                <button type="button" class="btn btn-outline btn-link" data-value="">全部</button>-->
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
                            <hr>
                            <?php $list_num = $list->toArray(); ?>
                            <div class="col-sm-12" style="padding-bottom: 20px;">
                                <div class="count-price">
                                    <span>售出商品：<strong class="h3 text-warning">{$price.total_num}</strong></span>
                                </div>
                                <div class="count-price">
                                    <span>订单数量：<strong class="h3 text-warning">{$list_num.total}</strong></span>
                                </div>
                                <div class="count-price">
                                    <span>订单金额：<strong class="h3 text-warning">￥{$price.pay_price}</strong></span>
                                </div>
                                <div class="count-price">
                                    <span>退款金额：<strong class="h3 text-warning">￥{$price.refund_price}</strong></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-title">
                                            <h5>主要数据统计</h5>
                                            <div class="ibox-tools">
                                                <a class="collapse-link">
                                                    <i class="fa fa-chevron-up"></i>
                                                </a>
                                                <a class="close-link">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="ibox-content" id="ec-goods-count" style="height:390px;">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="ibox-content" id="ec-order-count" style="height:390px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    (function(){
                                        var option = {
                                            title: {
                                                text: '订单统计'
                                            },
                                            tooltip : {
                                                trigger: 'item',
                                                formatter: "{a} <br/>{b} : {c} ({d}%)"
                                            },
                                            legend: {
                                                orient: 'horizontal',
                                                left: 'left',
                                                top: 25,
                                                data: <?=urldecode(json_encode(array_keys($orderCount)))?>,
                                                selected:<?php
                                                $data = [];
                                                $selected = [];
                                                foreach ($orderCount as $k=>$count){
                                                    $data[] = ['value'=>$count,'name'=>$k];
                                                    $selected[$k] = $count>0;
                                                }
                                                echo urldecode(json_encode($selected));
                                                ?>
                                            },
                                            series : [
                                                {
                                                    name: '订单数量',
                                                    type: 'pie',
                                                    radius : '55%',
                                                    center: ['50%', '60%'],
                                                    data:<?=urldecode(json_encode($data))?>,
                                                    itemStyle: {
                                                        emphasis: {
                                                            shadowBlur: 10,
                                                            shadowOffsetX: 0,
                                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                                        }
                                                    }
                                                }
                                            ]
                                        };
                                        var myChart = echarts.init(document.getElementById('ec-order-count'),'light');
                                        myChart.setOption(option);
                                    })();
                                    (function(){
                                        var option = {
                                            title: {
                                                text: '数据统计'
                                            },
                                            tooltip: {
                                                trigger: 'axis'
                                            },
                                            xAxis: {
                                                data: <?=json_encode($orderDays)?>
                                            },
                                            yAxis: {
                                                splitLine: {
                                                    show: false
                                                }
                                            },
                                            legend: {
                                                orient: 'horizontal',
                                                left: 'center',
                                                top: 25,
                                                data: ['商品数','订单数','订单金额','退款金额']
                                            },
                                            toolbox: {
                                                left: 'right',
                                                feature: {
                                                    restore: {},
                                                    saveAsImage: {}
                                                }
                                            },
                                            dataZoom: [{
                                                startValue: '<?php
                                                    $index = count($orderDays) > 30 ? count($orderDays)-30 : 0;
                                                    if(isset($orderDays[$index]))
                                                        echo $orderDays[$index];
                                                    ?>'
                                            }, {
                                                type: 'inside'
                                            }],
                                            visualMap: {
                                                top: 10,
                                                right: 10,
                                                pieces: [{
                                                    gt: 0,
                                                    lte: 50,
                                                    color: '#096'
                                                }, {
                                                    gt: 50,
                                                    lte: 100,
                                                    color: '#ffde33'
                                                }, {
                                                    gt: 100,
                                                    lte: 150,
                                                    color: '#ff9933'
                                                }, {
                                                    gt: 150,
                                                    lte: 200,
                                                    color: '#cc0033'
                                                }, {
                                                    gt: 200,
                                                    lte: 300,
                                                    color: '#660099'
                                                }, {
                                                    gt: 300,
                                                    color: '#7e0023'
                                                }],
                                                outOfRange: {
                                                    color: '#999'
                                                }
                                            },
                                            series: <?= json_encode($orderCategory)?>
                                        };
                                        var myChart = echarts.init(document.getElementById('ec-goods-count'),'light');
                                        myChart.setOption(option);
                                    })();
                                </script>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $(function init(){
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
    });
    $('.btn-order').on('click',function(){
        var that = $(this),value = that.data('value');
        $('input[name=order]').val(value);
        $('input[name=export]').val(0);
        $('form').submit();
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
