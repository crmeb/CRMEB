{extend name="public/container"}
{block name="head_top"}

{/block}
{block name="content"}
<style>
    .btn-outline{
        border:none;
    }
    .btn-outline:hover{
        background-color: #0e9aef;
        color: #fff;
    }
    .layui-form-item .layui-btn {
        margin-top: 5px;
        margin-right: 10px;
    }
    .layui-btn-primary{
        margin-right: 10px;
        margin-left: 0!important;
    }
    label{
        margin-bottom: 0!important;
        margin-top: 4px;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app">
        <!--搜索条件-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside"
                         lay-arrow="none" style="background:none">
                        <div class="layui-card-body">
                            <div class="layui-row layui-col-space10 layui-form-item">

                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">核销日期:</label>
                                    <div class="layui-input-block" data-type="data" v-cloak="">
                                        <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList"
                                                @click="setData(item)"
                                                :class="{'layui-btn-primary':where.data!=item.value}">{{item.name}}
                                        </button>
                                        <button class="layui-btn layui-btn-sm" type="button" ref="time"
                                                @click="setData({value:'zd',is_zd:true})"
                                                :class="{'layui-btn-primary':where.data!='zd'}">自定义
                                        </button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary"
                                                v-show="showtime==true" ref="date_time">{$year.0} - {$year.1}
                                        </button>
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">筛选条件:</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="real_name" style="width: 25%" v-model="where.real_name"
                                               placeholder="请输入姓名、电话、订单编号" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">选择门店:</label>
                                    <div class="layui-input-block">
                                        <select name="store_id" class="form-control input-sm" style="width: 50%"v-model="where.store_id">
                                            <option value="">--请选择--</option>
                                            {volist name="store_list" id="vo"}
                                            <option value="{$vo.id}">{$vo.name}</option>
                                            {/volist}
                                        </select>
                                    </div>

                                </div>

                                <div class="layui-col-lg12">
                                    <div class="layui-input-block">
                                        <button @click="search" type="button"
                                                class="layui-btn layui-btn-sm layui-btn-normal">
                                            <i class="layui-icon layui-icon-search"></i>搜索
                                        </button>
                                        <button @click="refresh" type="reset"
                                                class="layui-btn layui-btn-primary layui-btn-sm">
                                            <i class="layui-icon layui-icon-refresh"></i>刷新
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end-->
        <!-- 中间详细信息-->
        <div :class="item.col!=undefined ? 'layui-col-sm'+item.col+' '+'layui-col-md'+item.col:'layui-col-sm6 layui-col-md3'"
             v-for="item in badge" v-cloak="" v-if="item.count > 0">
            <div class="layui-card">
                <div class="layui-card-header">
                    {{item.name}}
                    <span class="layui-badge layuiadmin-badge" :class="item.background_color">{{item.field}}</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{{item.count}}</p>
                    <p v-show="item.content!=undefined">
                        {{item.content}}
                        <span class="layuiadmin-span-color">{{item.sum}}<i :class="item.class"></i></span>
                    </p>
                </div>
            </div>
        </div>
        <!--enb-->
    </div>
    <!--列表-->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">订单列表</div>
                <div class="layui-card-body">

                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--订单-->
                    <script type="text/html" id="order_id">
                        {{d.order_id}}<br/>
                    </script>
                    <!--用户信息-->
                    <script type="text/html" id="userinfo">
                        {{d.nickname==null ? '暂无信息':d.nickname}}/{{d.uid}}
                    </script>
                    <!--分销员信息-->
                    <script type="text/html" id="spread_uid">
                        {{# if(d.spread_uid != 0){ }}
                        <button class="btn btn-default btn-xs btn-outline" type="button"
                                onclick="$eb.createModalFrame('推荐人信息','{:Url('order.StoreOrder/order_spread_user')}?uid={{d.spread_uid}}',{w:600,h:400})">
                            {{d.spread_nickname}}
                        </button>
                        {{# }else{ }}无{{# } }}
                    </script>
                    <!--支付状态-->
                    <script type="text/html" id="paid">
                        {{#  if(d.pay_type==1){ }}
                        <p>{{d.pay_type_name}}</p>
                        {{#  }else{ }}
                        {{# if(d.pay_type_info!=undefined){ }}
                        <p><span>线下支付</span></p>
                        {{# }else{ }}
                        <p>{{d.pay_type_name}}</p>
                        {{# } }}
                        {{# }; }}
                    </script>
                    <!--订单状态-->
                    <script type="text/html" id="status">
                        {{d.status_name}}
                    </script>
                    <!--商品信息-->
                    <script type="text/html" id="info">
                        {{#  layui.each(d._info, function(index, item){ }}
                        {{#  if(item.cart_info.productInfo.attrInfo!=undefined){ }}
                        <div>
                            <span>
                                <img style="width: 30px;height: 30px;margin:0;cursor: pointer;"
                                     src="{{item.cart_info.productInfo.attrInfo.image}}">
                            </span>
                            <span>{{item.cart_info.productInfo.store_name}}&nbsp;{{item.cart_info.productInfo.attrInfo.suk}}</span>
                            <span> | ￥{{item.cart_info.truePrice}}×{{item.cart_info.cart_num}}</span>
                        </div>
                        {{#  }else{ }}
                        <div>
                            <span><img style="width: 30px;height: 30px;margin:0;cursor: pointer;"
                                       src="{{item.cart_info.productInfo.image}}"></span>
                            <span>{{item.cart_info.productInfo.store_name}}</span><span> | ￥{{item.cart_info.truePrice}}×{{item.cart_info.cart_num}}</span>
                        </div>
                        {{# } }}
                        {{#  }); }}
                    </script>

                </div>
            </div>
        </div>
    </div>
    <!--end-->
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    layList.tableList('List', "{:Url('order_list',['real_name'=>$real_name])}", function () {
        return [
            {type: 'checkbox'},
            {field: 'order_id', title: '订单号', sort: true, event: 'order_id', width: '14%', templet: '#order_id'},
            {field: 'nickname', title: '用户信息', templet: '#userinfo', width: '10%', align: 'center'},
            {field: 'spread_uid', title: '推荐人信息', templet: '#spread_uid', width: '10%', align: 'center'},
            {field: 'info', title: '商品信息', templet: "#info", height: 'full-20'},
            {field: 'pay_price', title: '实际支付', width: '8%', align: 'center'},
            {field: 'clerk_name', title: '核销员', width: '8%', align: 'center'},
            {field: 'store_name', title: '核销门店', width: '8%', align: 'center'},
            {field: 'paid', title: '支付状态', templet: '#paid', width: '8%', align: 'center'},
            {field: 'status', title: '订单状态', templet: '#status', width: '8%', align: 'center'},
            {field: 'add_time', title: '下单时间', width: '10%', sort: true, align: 'center'},
        ];
    });
    layList.tool(function (event, data, obj) {
        switch (event) {
            case 'order_paid':
                var url = layList.U({c: 'order.store_order', a: 'offline', p: {id: data.id}});
                $eb.$swal('delete', function () {
                    $eb.axios.get(url).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg);
                        } else
                            return Promise.reject(res.data.msg || '修改失败')
                    }).catch(function (err) {
                        $eb.$swal('error', err);
                    });
                }, {'title': '您确定要修改支付状态吗？', 'text': '修改后将无法恢复,请谨慎操作！', 'confirm': '是的，我要修改'})
                break;
            case 'marke':
                var url = layList.U({c: 'order.store_order', a: 'remark'}),
                    id = data.id,
                    make = data.remark;
                $eb.$alert('textarea', {title: '请修改内容', value: make}, function (result) {
                    if (result) {
                        $.ajax({
                            url: url,
                            data: 'remark=' + result + '&id=' + id,
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.code == 200) {
                                    $eb.$swal('success', res.msg);
                                } else
                                    $eb.$swal('error', res.msg);
                            }
                        })
                    } else {
                        $eb.$swal('error', '请输入要备注的内容');
                    }
                });
                break;
            case 'danger':
                var url = layList.U({c: 'order.store_order', a: 'take_delivery', p: {id: data.id}});
                $eb.$swal('delete', function () {
                    $eb.axios.get(url).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg);
                        } else
                            return Promise.reject(res.data.msg || '收货失败')
                    }).catch(function (err) {
                        $eb.$swal('error', err);
                    });
                }, {'title': '您确定要修改收货状态吗？', 'text': '修改后将无法恢复,请谨慎操作！', 'confirm': '是的，我要修改'})
                break;
            case 'order_info':
                $eb.createModalFrame(data.nickname + '订单详情', layList.U({a: 'order_info', q: {oid: data.id}}));
                break;
        }
    })
    var action = {
        del_order: function () {
            var ids = layList.getCheckData().getIds('id');
            if (ids.length) {
                var url = layList.U({c: 'order.store_order', a: 'del_order'});
                $eb.$swal('delete', function () {
                    $eb.axios.post(url, {ids: ids}).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg);
                        } else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function (err) {
                        $eb.$swal('error', err);
                    });
                }, {'title': '您确定要修删除订单吗？', 'text': '删除后将无法恢复,请谨慎操作！', 'confirm': '是的，我要删除'})
            } else {
                layList.msg('请选择要删除的订单');
            }
        },
        write_order: function () {
            return $eb.createModalFrame('订单核销', layList.U({a: 'write_order'}), {w: 500, h: 400});
        },
    };
    $('#container-action').find('button').each(function () {
        $(this).on('click', function () {
            var act = $(this).data('type');
            action[act] && action[act]();
        });
    })
    //下拉框
    $(document).click(function (e) {
        $('.layui-nav-child').hide();
    })

    function dropdown(that) {
        var oEvent = arguments.callee.caller.arguments[0] || event;
        oEvent.stopPropagation();
        var offset = $(that).offset();
        var top = offset.top - $(window).scrollTop();
        var index = $(that).parents('tr').data('index');
        $('.layui-nav-child').each(function (key) {
            if (key != index) {
                $(this).hide();
            }
        })
        if ($(document).height() < top + $(that).next('ul').height()) {
            $(that).next('ul').css({
                'padding': 10,
                'top': -($(that).parents('td').height() / 2 + $(that).height() + $(that).next('ul').height() / 2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        } else {
            $(that).next('ul').css({
                'padding': 10,
                'top': $(that).parents('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }

    var real_name = '<?=$real_name?>';
    require(['vue'], function (Vue) {
        new Vue({
            el: "#app",
            data: {
                badge: [],
                dataList: [
                    {name: '全部', value: ''},
                    {name: '今天', value: 'today'},
                    {name: '昨天', value: 'yesterday'},
                    {name: '最近7天', value: 'lately7'},
                    {name: '最近30天', value: 'lately30'},
                    {name: '本月', value: 'month'},
                    {name: '本年', value: 'year'},
                ],
                where: {
                    data: '',
                    real_name: real_name || '',
                    store_id:''
                },
                showtime: false,
            },
            watch: {
                'where.data': function () {
                    this.getBadge();
                    layList.reload(this.where, true);
                },
            },
            methods: {
                setData: function (item) {
                    var that = this;
                    if (item.is_zd == true) {
                        that.showtime = true;
                        this.where.data = this.$refs.date_time.innerText;
                    } else {
                        this.showtime = false;
                        this.where.data = item.value;
                    }
                },
                getBadge: function () {
                    var that = this;
                    layList.basePost(layList.Url({c: 'system.SystemVerifyOrder', a: 'getBadge'}), this.where, function (rem) {
                        that.badge = rem.data;
                    });
                },
                search: function () {
                    this.where.excel = 0;
                    this.getBadge();
                    layList.reload(this.where, true);
                },
                refresh: function () {
                    layList.reload();
                    this.getBadge();
                },
            },
            mounted: function () {
                var that = this;
                that.getBadge();
                window.formReload = this.search;
                layList.form.render();
                layList.laydate.render({
                    elem: this.$refs.date_time,
                    trigger: 'click',
                    eventElem: this.$refs.time,
                    range: true,
                    change: function (value) {
                        that.where.data = value;
                    }
                });
            }
        })
    });
</script>
{/block}