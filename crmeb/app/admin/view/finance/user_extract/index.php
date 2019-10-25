{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<script src="{__PLUG_PATH}moment.js"></script>
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">
                            <div class="search-item" data-name="date">
                                <span>选择时间：</span>
                                <button type="button" class="btn btn-outline btn-link" data-value="">全部</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.today}">今天</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.week}">本周</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.month}">本月</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.quarter}">本季度</button>
                                <button type="button" class="btn btn-outline btn-link" data-value="{$limitTimeList.year}">本年</button>
                                <div class="datepicker" style="display: inline-block;">
                                    <button type="button" class="btn btn-outline btn-link" data-value="{$where.date?:'no'}">自定义时间</button>
                                </div>
                                <input class="search-item-value" type="hidden" name="date" value="{$where.date}" />
                            </div>
                            <select name="status" aria-controls="editable" class="form-control input-sm">
                                <option value="">提现状态</option>
                                <option value="-1" {eq name="where.status" value="-1"}selected="selected"{/eq}>未通过</option>
                                <option value="0" {eq name="where.status" value="0"}selected="selected"{/eq}>未提现</option>
                                <option value="1" {eq name="where.status" value="1"}selected="selected"{/eq}>已通过</option>
                            </select>
                            <select name="extract_type"  class="form-control input-sm">
                                <option value="">提现方式</option>
                                <option value="alipay" {eq name="where.extract_type" value="alipay" }selected="selected"{/eq}>支付宝</option>
                                <option value="bank" {eq name="where.extract_type" value="bank"}selected="selected"{/eq}>银行卡</option>
                                <option value="weixin" {eq name="where.extract_type" value="weixin"}selected="selected"{/eq}>微信</option>
                            </select>
                            <div class="input-group">
                                  <span class="input-group-btn">
                                      <input type="text" name="nireid" value="{$where.nireid}" placeholder="微信昵称/姓名/支付宝账号/银行卡号" class="input-sm form-control" size="38"/>
                                      <button type="submit" class="btn btn-sm btn-primary"> 搜索</button>
                                  </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-3 ui-sortable">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right">￥</span>
                                <h5>已提现金额</h5>
                            </div>
                            <div class="ibox-content">
                                <h1 class="no-margins">{$data.priced}</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 ui-sortable">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-danger pull-right">急</span>
                                <h5>待提现金额</h5>
                            </div>
                            <div class="ibox-content">
                                <h1 class="no-margins">{$data.price}</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 ui-sortable">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right">待</span>
                                <h5>佣金总金额</h5>
                            </div>
                            <div class="ibox-content">
                                <h1 class="no-margins">{$data.brokerage_count}</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 ui-sortable">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right">待</span>
                                <h5>未提现金额</h5>
                            </div>
                            <div class="ibox-content">
                                <h1 class="no-margins">{$data.brokerage_not}</h1>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">编号</th>
                                <th class="text-center">用户信息</th>
                                <th class="text-center">提现金额</th>
                                <th class="text-center">提现方式</th>
                                <th class="text-center">添加时间</th>
                                <th class="text-center">备注</th>
                                <th class="text-center">审核状态</th>
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
                               用户昵称: {$vo.nickname}/用户id:{$vo.uid}
                            </td>
                            <td class="text-center" style="color: #00aa00;">
                                {$vo.extract_price}
                            </td>
                            <td class="text-left">
                                {if condition="$vo['extract_type'] eq 'bank'"}
                                姓名:{$vo.real_name}<br>
                                 银行卡号:{$vo.bank_code}
                                <br/>
                                 银行开户地址:{$vo.bank_address}
                                {elseif condition="$vo['extract_type'] eq 'weixin'"/}
                                昵称:{$vo.nickname}<br>
                                微信号号:{$vo.wechat}
                                {else/}
                                姓名:{$vo.real_name}<br>
                                  支付宝号:{$vo.alipay_code}
                                {/if}
                            </td>
                            <td class="text-center">
                                {$vo.add_time|date='Y-m-d H:i:s'}
                            </td>
                            <td class="text-center">
                                {$vo.mark}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['status'] eq 1"}
                                提现通过<br/>
                                {elseif condition="$vo['status'] eq -1"/}
                                提现未通过<br/>
                                未通过原因：{$vo.fail_msg}
                                <br>
                                未通过时间：{$vo.fail_time|date='Y-m-d H:i:s'}
                                {else/}
                                未提现<br/>
                                <button data-url="{:url('fail',['id'=>$vo['id']])}" class="j-fail btn btn-danger btn-xs" type="button"><i class="fa fa-close"></i> 无效</button>
                                <button data-url="{:url('succ',['id'=>$vo['id']])}" class="j-success btn btn-primary btn-xs" type="button"><i class="fa fa-check"></i> 通过</button>
                                {/if}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-paste"></i> 编辑</button>
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
    $(function init() {
        $('.search-item>.btn').on('click', function () {
            var that = $(this), value = that.data('value'), p = that.parent(), name = p.data('name'), form = p.parents();
            form.find('input[name="' + name + '"]').val(value);
            $('input[name=export]').val(0);
            form.submit();
        });
        $('.tag-item>.btn').on('click', function () {
            var that = $(this), value = that.data('value'), p = that.parent(), name = p.data('name'), form = p.parents(),list = $('input[name="' + name + '"]').val().split(',');
            var bool = 0;
            $.each(list,function (index,item) {
                if(item == value){
                    bool = 1
                    list.splice(index,1);
                }
            })
            if(!bool) list.push(''+value+'');
            form.find('input[name="' + name + '"]').val(list.join(','));
            $('input[name=export]').val(0);
            form.submit();
        });
        $('.search-item>li').on('click', function () {
            var that = $(this), value = that.data('value'), p = that.parent(), name = p.data('name'), form = $('#form');
            form.find('input[name="' + name + '"]').val(value);
            $('input[name=export]').val(0);
            form.submit();
        });
        $('.search-item>li').each(function () {
            var that = $(this), value = that.data('value'), p = that.parent(), name = p.data('name');
            if($where[name]) $('.'+name).css('color','#1ab394');
        });
        $('.search-item-value').each(function () {
            var that = $(this), name = that.attr('name'), value = that.val(), dom = $('.search-item[data-name="' + name + '"] .btn[data-value="' + value + '"]');
            dom.eq(0).removeClass('btn-outline btn-link').addClass('btn-primary btn-sm')
                .siblings().addClass('btn-outline btn-link').removeClass('btn-primary btn-sm')
        });
    })
    $('.j-fail').on('click',function(){
        var url = $(this).data('url');
        $eb.$alert('textarea',{
            title:'请输入未通过原因',
            value:'输入信息不完整或有误!',
        },function(value){
            $eb.axios.post(url,{message:value}).then(function(res){
                if(res.data.code == 200) {
                    $eb.$swal('success', res.data.msg);
                    setTimeout(function () {
                        window.location.reload();
                    },1000);
                }else
                    $eb.$swal('error',res.data.msg||'操作失败!');
            });
        });
    });
    $('.j-success').on('click',function(){
        var url = $(this).data('url');
        $eb.$swal('delete',function(){
            $eb.axios.post(url).then(function(res){
                if(res.data.code == 200) {
                    setTimeout(function () {
                        window.location.reload();
                    },1000);
                    $eb.$swal('success', res.data.msg);
                }else
                    $eb.$swal('error',res.data.msg||'操作失败!');
            });
        },{
            title:'确定审核通过?',
            text:'通过后无法撤销，请谨慎操作！',
            confirm:'审核通过'
        });
    });
    $('.btn-warning').on('click',function(){
        window.t = $(this);
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
        })
    });
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
    var dateInput = $('.datepicker');
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
    dateInput.on('apply.daterangepicker', function(ev, picker) {
        $("input[name=date]").val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
        $('form').submit();
    });
</script>
{/block}

