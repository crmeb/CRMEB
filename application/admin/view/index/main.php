<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <base href="{__FRAME_PATH}">
    <link href="css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <link href="css/font-awesome.min.css?v=4.3.0" rel="stylesheet">

    <!-- Morris -->
    <link href="css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">

    <!-- Gritter -->
    <link href="js/plugins/gritter/jquery.gritter.css" rel="stylesheet">

    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/style.min.css?v=3.0.0" rel="stylesheet">

</head>

<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-2">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">订单</span>
                    <h5>待发货订单数</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">{$topData.orderDeliveryNum}</h1>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">订单</span>
                    <h5>退换货订单数</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">{$topData.orderRefundNum}</h1>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">商品</span>
                    <h5>库存预警</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">{$topData.stockProduct}</h1>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">用户</span>
                    <h5>待处理提现</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">{$topData.treatedExtract}</h1>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">订单</span>
                    <h5>昨日订单数</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">{$topData.orderNum}</h1>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">订单</span>
                    <h5>昨日交易额</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">{$topData.orderPriceNum}</h1>
                </div>
            </div>
        </div>
<!--        <div class="col-lg-3">-->
<!--            <div class="ibox float-e-margins">-->
<!--                <div class="ibox-title">-->
<!--                    <span class="label label-info pull-right">今天</span>-->
<!--                    <h5>日收入</h5>-->
<!--                </div>-->
<!--                <div class="ibox-content">-->
<!--                    <h1 class="no-margins">{$first_line.d_price.data}</h1>-->
<!--                    <div class="stat-percent font-bold text-info">-->
<!--                        {$first_line.d_price.percent}%-->
<!--                        {if condition='$first_line.d_price.is_plus egt 0'}<i class="fa {if condition='$first_line.d_price.is_plus eq 1'}fa-level-up{else /}fa-level-down{/if}"></i>{/if}-->
<!--                    </div>-->
<!--                    <small>总收入</small>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="col-lg-3">-->
<!--            <div class="ibox float-e-margins">-->
<!--                <div class="ibox-title">-->
<!--                    <span class="label label-success pull-right">月</span>-->
<!--                    <h5>月收入</h5>-->
<!--                </div>-->
<!--                <div class="ibox-content">-->
<!--                    <h1 class="no-margins">{$first_line.m_price.data}</h1>-->
<!--                    <div class="stat-percent font-bold text-success">-->
<!--                        {$first_line.m_price.percent}%-->
<!--                        {if condition='$first_line.m_price.is_plus egt 0'}<i class="fa {if condition='$first_line.m_price.is_plus eq 1'}fa-level-up{else /}fa-level-down{/if}"></i>{/if}-->
<!--                    </div>-->
<!--                    <small>总收入</small>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="col-lg-3">-->
<!--            <div class="ibox float-e-margins">-->
<!--                <div class="ibox-title">-->
<!--                    <span class="label label-primary pull-right">今天</span>-->
<!--                    <h5>日增粉丝</h5>-->
<!--                </div>-->
<!--                <div class="ibox-content">-->
<!--                    <h1 class="no-margins">{$first_line.day.data}</h1>-->
<!--                    <div class="stat-percent font-bold text-navy">-->
<!--                        {$first_line.day.percent}%-->
<!--                        {if condition='$first_line.day.is_plus egt 0'}<i class="fa {if condition='$first_line.day.is_plus eq 1'}fa-level-up{else /}fa-level-down{/if}"></i>{/if}-->
<!--                    </div>-->
<!--                    <small>新粉丝</small>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="col-lg-3">-->
<!--            <div class="ibox float-e-margins">-->
<!--                <div class="ibox-title">-->
<!--                    <span class="label label-danger pull-right">月</span>-->
<!--                    <h5>月增粉丝</h5>-->
<!--                </div>-->
<!--                <div class="ibox-content">-->
<!--                    <h1 class="no-margins">{$first_line.month.data}</h1>-->
<!--                    <div class="stat-percent font-bold text-danger">-->
<!--                        {$first_line.month.percent}%-->
<!--                        {if condition='$first_line.month.is_plus egt 0'}<i class="fa {if condition='$first_line.month.is_plus eq 1'}fa-level-up{else /}fa-level-down{/if}"></i>{/if}-->
<!--                    </div>-->
<!--                    <small>新粉丝</small>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>订单</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="flot-chart">
                                <div class="flot-chart-content" id="flot-dashboard-chart1"></div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <ul class="stat-list">
                                <li>
                                    <h2 class="no-margins ">{$first_line.m_price.data}</h2>
                                    <small>本月销售额</small>
                                    <div class="stat-percent">
                                        {$first_line.m_price.percent}%
                                        {if condition='$first_line.m_price.is_plus egt 0'}<i class="fa text-navy {if condition='$first_line.m_price.is_plus eq 1'}fa-level-up{else /}fa-level-down{/if}"></i>{/if}
                                    </div>
                                    <div class="progress progress-mini">
                                        <div style="width: {$first_line.m_price.percent}%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins">{$second_line.order_info.first.data}</h2>
                                    <small>本月订单总数</small>
                                    <div class="stat-percent">
                                        {$second_line.order_info.first.percent}%
                                        {if condition='$second_line.order_info.first.is_plus egt 0'}<i class="fa text-navy {if condition='$second_line.order_info.first.is_plus eq 1'}fa-level-up{else /}fa-level-down{/if}"></i>{/if}
                                    </div>
                                    <div class="progress progress-mini">
                                        <div style="width: {$second_line.order_info.first.percent}%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins ">{$second_line.order_info.second.data}</h2>
                                    <small>上月订单总数</small>
                                    <div class="stat-percent">
                                        {$second_line.order_info.second.percent}%
                                        {if condition='$second_line.order_info.second.is_plus egt 0'}<i class="fa text-navy {if condition='$second_line.order_info.second.is_plus eq 1'}fa-level-up{else /}fa-level-down{/if}"></i>{/if}
                                    </div>
                                    <div class="progress progress-mini">
                                        <div style="width: {$second_line.order_info.second.percent}%;" class="progress-bar"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>收入</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="flot-chart">
                                <div class="flot-chart-content" id="flot-dashboard-chart2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 全局js -->
<script src="js/jquery-2.1.1.min.js"></script>
<script src="js/bootstrap.min.js?v=3.4.0"></script>



<!-- Flot -->
<script src="js/plugins/flot/jquery.flot.js"></script>
<script src="js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="js/plugins/flot/jquery.flot.spline.js"></script>
<script src="js/plugins/flot/jquery.flot.resize.js"></script>
<script src="js/plugins/flot/jquery.flot.pie.js"></script>
<script src="js/plugins/flot/jquery.flot.symbol.js"></script>

<!-- Peity -->
<script src="js/plugins/peity/jquery.peity.min.js"></script>


<!-- 自定义js -->
<script src="js/content.min.js?v=1.0.0"></script>


<!-- jQuery UI -->
<script src="js/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- Jvectormap -->
<script src="js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>

<!-- EayPIE -->
<script src="js/plugins/easypiechart/jquery.easypiechart.js"></script>

<!-- Sparkline -->
<script src="js/plugins/sparkline/jquery.sparkline.min.js"></script>



<script>
    $(document).ready(function(){
        var a1=[
            {volist name="second_line.order_count" id="vo"}
                [c({$vo.y},{$vo.m},{$vo.d}),{$vo.count}],
            {/volist}
        ];
        var a2=[
            {
                label:"订单数",
                data:a1,
                color:"#1ab394",
                bars:{
                    show:true,
                    align:"center",
                    barWidth:24*60*60*600,
                    lineWidth:0
                }
            }
        ];
        var a3={
            xaxis:{
                mode:"time",
                tickSize:[1,"day"],
                tickLength:0,
                axisLabel:"Date",
                axisLabelUseCanvas:true,
                axisLabelFontSizePixels:12,
                axisLabelFontFamily:"Arial",
                axisLabelPadding:10,
                color:"#838383",
            },
            
            yaxes:[
                {
                    position:"left",
                    max:'{$second_line.order_count_max}',
                    color:"#838383",
                    axisLabelUseCanvas:true,
                    axisLabelFontSizePixels:12,
                    axisLabelFontFamily:"Arial",
                    axisLabelPadding:3
                },
            ],
            legend:{
                noColumns:1,
                labelBoxBorderColor:"#000000",
                position:"nw"
            },
            grid:{
                hoverable:false,
                borderWidth:0,
                color:"#838383"
            }
        };
        $.plot($("#flot-dashboard-chart1"),a2,a3);

        var b1=[
            {volist name="third_line.price_count" id="vo"}
                [c({$vo.y},{$vo.m},{$vo.d}),{$vo.count}],
            {/volist}
        ];
        var b2=[
            {
                label:"总金额",
                data:b1,
                color:"#1ab394",
                bars:{
                    show:true,
                    align:"center",
                    barWidth:24*60*60*600,
                    lineWidth:0
                }
            }
        ];
        var b3={
            xaxis:{
                mode:"time",
                tickSize:[1,"day"],
                tickLength:0,
                axisLabel:"Date",
                axisLabelUseCanvas:true,
                axisLabelFontSizePixels:12,
                axisLabelFontFamily:"Arial",
                axisLabelPadding:10,
                color:"#838383",
            },
            yaxes:[
                {
                    position:"left",
                    max:{$third_line.order_count_max},
                    color:"#838383",
                    axisLabelUseCanvas:true,
                    axisLabelFontSizePixels:12,
                    axisLabelFontFamily:"Arial",
                    axisLabelPadding:3
                },
            ],
            legend:{
                noColumns:1,
                labelBoxBorderColor:"#000000",
                position:"nw"
            },
            grid:{
                hoverable:false,
                borderWidth:0,
                color:"#838383"
            }
        };
        $.plot($("#flot-dashboard-chart2"),b2,b3);



        $(".chart").easyPieChart({
            barColor:"#f8ac59",scaleLength:5,lineWidth:4,size:80
        });
        $(".chart2").easyPieChart({
            barColor:"#1c84c6",scaleLength:5,lineWidth:4,size:80
        });
        function c(j,k,i){
            return new Date(j,k-1,i).getTime()
        }
        var b=null,d=null;

        var f={"US":298,"SA":200,"DE":220,"FR":540,"CN":120,"AU":760,"BR":550,"IN":200,"GB":120,};
        $("#world-map").vectorMap({
            map:"world_mill_en",
            backgroundColor:"transparent",
            regionStyle:{
                initial:{
                    fill:"#e4e4e4","fill-opacity":0.9,stroke:"none","stroke-width":0,"stroke-opacity":0
                }
            },
            series:{
                regions:[
                    {
                        values:f,
                        scale:["#1ab394","#22d6b1"],
                        normalizeFunction:"polynomial"
                    }
                ]
            }
        })
    });
</script>
</body>
</html>
