{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<link href="{__FRAME_PATH}css/plugins/footable/footable.core.css" rel="stylesheet">
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<script src="{__PLUG_PATH}moment.js"></script>
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
<script src="{__PLUG_PATH}echarts.common.min.js"></script>
<style>
    .btn-group-sm>.btn, .btn-sm{
         padding: 4px 10px;
         font-size: 12px;
     }
    .btn{
        padding: 4px 10px;
        font-size: 12px;

    }
    .search-form{
        margin-top: 0;
    }
    .search-form .search-item span{
        margin-right: 0;
    }
    .search-form .search-item{
        padding: 0;
    }
    .search-form .search-item-css{
        padding: 6px 0;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline search-form">
                                <div class="search-item" data-name="date">
                                    <span>创建时间：</span>
                                    <button type="button" class="btn btn-outline btn-link" data-value="">本月</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="today">今天</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="week">本周</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="quarter">本季度</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="year">本年</button>
                                    <div class="datepicker" style="display: inline-block;">
                                        <button type="button" class="btn btn-outline btn-link" data-value="{$where.date?:'no'}">自定义</button>
                                    </div>
                                    <input class="search-item-value" type="hidden" name="date" value="{$where.date}" />
                                </div>
                                <div class="search-item search-item-css" data-name="status">
                                    <span>选择状态：</span>
                                    <button type="button" class="btn btn-outline btn-link" data-value="">默认</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="1">正常</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="0">锁定</button>
                                    <input class="search-item-value" type="hidden" name="status" value="{$where.status}" />
                                </div>
                                <div class="search-item search-item-css" data-name="is_promoter">
                                    <span>选择身份：</span>
                                    <button type="button" class="btn btn-outline btn-link" data-value="">全部</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="0">普通用户</button>
                                    <button type="button" class="btn btn-outline btn-link" data-value="1">推广用户</button>
                                    <input class="search-item-value" type="hidden" name="is_promoter" value="{$where.is_promoter}" />
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
    <div class="col-sm-8">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>主要数据统计</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
                <div class="ibox-content">
                    <div  data-hide="true" id="container" style="height: 390px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>消费统计</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
                <div class="ibox-content" data-hide="true" id="user_index" style="height: 310px"></div>
                <div class="ibox-content"  style="height: 115px">
                    <div class="col-sm-6" style="border-right: 1px solid #CCCCCC">
                        <p style="font-size: 12px">{$consume.rightTitle.title}</p>
                        <p style="font-size: 16px;color:#ed5565"><i class="fa {$consume.rightTitle.icon}" style="padding-right: 10px;"></i>&nbsp;&nbsp;￥{$consume.rightTitle.number}</p>
                    </div>
                    <div class="col-sm-6">
                        <p style="font-size: 12px">{$consume.leftTitle.title}</p>
                        <p style="font-size: 16px;color:#23c6c8;"><i class="fa {$consume.leftTitle.icon}" style="padding-right: 10px;">&nbsp;&nbsp;{$consume.leftTitle.count}</i></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="row">
<div class="col-sm-4">
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>用户分布</h5>
            <div class="ibox-tools">
                <a class="collapse-link">
                    <i class="fa fa-chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="ibox-content">
            <div  id="distribution" style="height:290px;"></div>
        </div>
    </div>
</div>
<div class="col-sm-4">
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>用户浏览分析（次）</h5>
            <div class="ibox-tools">
                <a class="collapse-link">
                    <i class="fa fa-chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="ibox-content">
            <div  style="height:290px;" id='count'></div>
        </div>
    </div>
</div>
<div class="col-sm-4">
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>消费排行榜 TOP20</h5>
            <div class="ibox-tools">
                <a class="collapse-link">
                    <i class="fa fa-chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="ibox-content" style="height:290px;overflow-y: scroll;background-color: #ffffff">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th class="text-center">排名</th>
                    <th class="text-center">用户名</th>
                    <th class="text-center">时间</th>
                    <th class="text-center">消费金额 ￥</th>
                    <th class="text-center">余额 ￥</th>
                    <th class="text-center">操作</th>
                </tr>
                </thead>
                <tbody>
                {volist name='user_null' id='vo'}
                <tr>
                    <td class="text-center">{$key+1}</td>
                    <td class="text-center">{$vo.nickname}</td>
                    <td class="text-center">{$vo.add_time|date='Y-m-d H:i:s'}</td>
                    <td class="text-center">{$vo.totel_number}</td>
                    <td class="text-center">{$vo.now_money}</td>
                    <td class="text-center">
                        <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('uid'=>$vo['uid']))}')">
                            <i class="fa fa-edit"></i> 编辑</button>
                    </td>
                </tr>
                {/volist}
                {if !$user_null}
                <tr>
                    <td colspan="6" class="text-center"><h4>暂无数据</h4></td>
                </tr>
                {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
{/block}
{block name="script"}
<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
<script>
    $('.search-item>.btn').on('click',function(){
        var that = $(this),value = that.data('value'),p = that.parent(),name = p.data('name'),form = p.parents();
        form.find('input[name="'+name+'"]').val(value);
        form.submit();
    });
    $('.search-item-value').each(function(){
        var that = $(this),name = that.attr('name'), value = that.val(),dom = $('.search-item[data-name="'+name+'"] .btn[data-value="'+value+'"]');
        dom.eq(0).removeClass('btn-outline btn-link').addClass('btn-primary btn-sm')
            .siblings().addClass('btn-outline btn-link').removeClass('btn-primary btn-sm')
    });
    (function(){
        var dom = document.getElementById("container"), myChart = echarts.init(dom), option = null;
        option = {
            tooltip: {trigger: 'axis'},
            toolbox: {left: 'right', feature: {restore: {}, saveAsImage: {}}},
            legend: {orient: 'horizontal', left: 'center', top: 25, data: <?php echo $user_index['name']?$user_index['name']:'false';?> || []},
            xAxis: {type: 'category', splitLine: {show: false}, data:<?php echo $user_index['date']?$user_index['date']:'false';?> || []},
            yAxis: {type: 'log',show :true,min:1},
            grid: {left: '3%', right: '4%', bottom: '3%', containLabel: true},
            series:<?php echo $user_index['series']?$user_index['series']:'false';?> || []
            <?php if($where['date']==null || $where['date']=='today'){?>
            ,dataZoom: [{
                endValue : <?php echo $where['date']=='today'?date('H',time()):date('d',time());?>
            }, {
                type: 'inside'
            }],
            <?php }?>
        };
        if (option && typeof option === "object") {
            myChart.setOption(option, true);
        }
    })();
    (function() {
        var dom = document.getElementById("user_index"), myChart = echarts.init(dom), option=null;
            option={
            title:{text:<?php echo empty($consume['series']['data'])?'false':json_encode($consume['title']);?> || '暂无数据'},
            tooltip: {trigger: 'item', formatter: "{a} <br/>{b}: {c} ({d}%)"},
            series: [<?php echo empty($consume['series']['data'])?'false':json_encode($consume['series']);?> || {name:'暂无数据',type:'pie',radius:['40%', '50%'],data:[{value:100,name:'暂无数据'}]}]
        };
        if (option && typeof option === "object"){
            myChart.setOption(option, true);
        }
    })();
    (function () {
        var distributionChart = echarts.init(document.getElementById("distribution"));
        option={
            tooltip: {trigger: 'item', formatter: "{a} <br/>{b}: {c} ({d}%)"},
            legend:{
                orient: 'vertical',
                x: 'left',
                data:<?php echo empty($form['legend_date'])?'false':json_encode($form['legend_date']);?> || [{name:'暂无数据',icon:'circle'}]
            },
            series: [
                {
                    name:'<?php echo isset($form['legend_date'][0]['name'])?$form['legend_date'][0]['name']:'暂无数据';?>',
                    type:'pie',
                    radius: ['70%', '90%'],
                    label: {
                        normal: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            show: true,
                            textStyle: {
                                fontSize: '20',
                                fontWeight: 'bold'
                            }
                        }
                    },
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data:<?php echo empty($form['series_date'])?'false':json_encode($form['series_date']);?> || [{value:100,name:'暂无数据'}]
                }
            ]
        };
        if (option && typeof option === "object") {
            distributionChart.setOption(option, true);
        }
    })();
    
    (function(){
        var option = {
            tooltip : {
                trigger: 'axis',
                axisPointer : {
                    type : 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                top: '4%',
                containLabel: true
            },
            yAxis : [
                {
                    inverse:true,
                    axisLine: {show: false},
                    type : 'category',
                    data : <?php echo empty($view['name'])?'false':json_encode($view['name'])?> || [],
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            xAxis : [
                {
                    show:false,
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'<?php echo isset($view['name'][0])?$view['name'][0]:'';?>',
                    type:'bar',
                    barWidth: '<?php if($view['name'][0]=='暂无数据'){echo '20%'; }else{ echo '50%';}?>',
                    data:<?php echo empty($view['name'])?'false':json_encode($view['value']);?> || []
                },
            ]
        };

        var myChart = echarts.init(document.getElementById('count'),'light');
        myChart.setOption(option);
    })();

    $(".open_image").on('click',function (e) {
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
        $("input[name=date]").val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
        $('form').submit();
    });

</script>
{/block}
