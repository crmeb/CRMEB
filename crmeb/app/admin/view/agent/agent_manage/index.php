{extend name="public/container"}
{block name="head_top"}
<style>
    .option {
        width: 200px;
        padding: 10px;
        background-color: #eeeeee;
        border-radius: 10px;
        text-align: center;
        display: none;
    }

    .option .layui-box p {
        margin: 5px 0;
        background-color: #ffffff;
        color: #0092DC;
        padding: 8px;
        cursor: pointer
    }

    .option .layui-box p.on {
        color: #eeeeee
    }
</style>
{/block}
{block name="content"}
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
                                    <label class="layui-form-label">时间选择:</label>
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
                                    <label class="layui-form-label">用户昵称:</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="nickname" style="width: 50%" v-model="where.nickname"
                                               placeholder="请输入姓名、电话、UID" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <div class="layui-input-block">
                                        <button @click="search" type="button"
                                                class="layui-btn layui-btn-sm layui-btn-normal">
                                            <i class="layui-icon layui-icon-search"></i>搜索
                                        </button>
                                        <button @click="excel" type="button"
                                                class="layui-btn layui-btn-warm layui-btn-sm export" type="button">
                                            <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出
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
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">分销员列表</div>
                <div class="layui-card-body">
                    <!--                    <div class="layui-btn-container">-->
                    <!--                        <div class="layui-btn-group conrelTable">-->
                    <!--                            <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="refresh"><i class="layui-icon layui-icon-refresh" ></i>刷新</button>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                    <script type="text/html" id="headimgurl">
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.headimgurl}}">
                    </script>
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span
                                    class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" class=""
                                   onclick="$eb.createModalFrame(this.innerText,'{:Url('stair')}?uid={{d.uid}}')">
                                    <i class="fa fa-list-alt"></i> 统计推广人列表
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class=""
                                   onclick="$eb.createModalFrame(this.innerText,'{:Url('stair_order')}?uid={{d.uid}}')">
                                    <i class="fa fa-reorder"></i> 统计推广订单
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='look_code'>
                                    <i class="fa fa-file-image-o"></i> 推广方式</a>
                            </li>
                            {{# if(d.spread_uid){ }}
                            <li>
                                <a href="javascript:void(0);" lay-event='delete_spread'>
                                    <i class="fa fa-unlock"></i> 清除上级
                                </a>
                            </li>
                            {{# } }}
                        </ul>
                    </script>
                </div>
                <!--用户信息-->
                <script type="text/html" id="userinfo">
                    昵称：{{d.nickname==null ? '暂无信息':d.nickname}}
                    <br>姓名：{{d.real_name==null ? '暂无信息':d.real_name}}
                    <br>电话：{{d.phone==null ? '暂无信息':d.phone}}
                </script>
                <div class="option">
                    <div class="layui-box">
                        <input type="hidden" name="uid" id="uid">
                        <p data-action="routine_code" data-type="wx">小程序推广二维码</p>
                        <p data-action="wechant_code" data-type="wx">公众号推广二维码</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    var action = {
        refresh: function () {
            layList.reload();
        },
        delete_spread: function () {
            var ids = layList.getCheckData().getIds('uid');
            if (ids.length) {
                $eb.$swal('delete', function () {
                    $eb.axios.post(layList.U({a: 'delete_promoter'}), {uids: ids}).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg);
                            layList.reload();
                        } else
                            return Promise.reject(res.data.msg || '清除失败')
                    }).catch(function (err) {
                        $eb.$swal('error', err);
                    });
                }, {
                    title: '您将解除选中用户的推广关系，请谨慎操作！',
                    text: '解除后不可恢复',
                    confirm: '是的我要解除'
                })
            } else {
                layList.msg('请选择要解除权限的用户');
            }
        },
    };
    layList.form.render();
    layList.tableList('userList', "{:Url('get_spread_list')}", function () {
        return [
            {type: 'checkbox'},
            {field: 'uid', title: 'UID', sort: true, width: '5%'},
            {field: 'headimgurl', title: '头像', templet: '#headimgurl'},
            {field: 'nickname', title: '用户信息', templet: '#userinfo', width: '12%'},
            {field: 'broken_commission', title: '冻结金额'},
            {field: 'spread_count', title: '推广用户数量', sort: true},
            {field: 'order_count', title: '订单数量'},
            {field: 'order_price', title: '订单金额', sort: true},
            {field: 'brokerage_money', title: '佣金金额', sort: true},
            {field: 'extract_count_price', title: '已提现金额', sort: true},
            {field: 'extract_count_num', title: '提现次数'},
            {field: 'new_money', title: '未提现金额', sort: true},
            {field: 'spread_name', title: '上级推广人', sort: true},
            {field: 'right', title: '操作', toolbar: '#act', width: '5%'},
        ];
    });
    layList.date({elem: '#start_time', theme: '#393D49', type: 'datetime'});
    layList.date({elem: '#end_time', theme: '#393D49', type: 'datetime'});
    layList.search('search', function (where) {
        if (where.start_time != '' && where.end_time == '') return layList.msg('请选择结束时间')
        if (where.end_time != '' && where.start_time == '') return layList.msg('请选择开始时间')
        console.log(where);
        layList.reload(where, true);
    });
    layList.search('export', function (where) {
        where.excel = 1;
        location.href = layList.U({a: 'get_spread_list', q: where});
    })
    $('.conrelTable').find('button').each(function () {
        var type = $(this).data('type');
        $(this).on('click', function () {
            action[type] && action[type]();
        })
    })
    $('.option .layui-box').find('p').each(function () {
        $(this).on('click', function () {
            var type = $(this).data('action'), uid = $('#uid').val();
            layList.baseGet(layList.U({a: 'look_code', q: {action: type, uid: uid}}), function (res) {
                if ($eb) {
                    $eb.openImage(res.data.code_src);
                } else {
                    layList.layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        shadeClose: true,
                        content: '<img src="' + res.data.code_src + '" style="display: block;width: 100%;" />'
                    });
                }
            }, function (res) {
                layList.msg(res.msg);
            });
        });
    });
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
                'top': -($(that).parent('td').height() / 2 + $(that).height() + $(that).next('ul').height() / 2),
                'min-width': 'inherit',
                'left': -64,
                'position': 'absolute'
            }).toggle();
        } else {
            $(that).next('ul').css({
                'padding': 10,
                'left': -64,
                'top': $(that).parent('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }

    layList.tool(function (event, data, obj) {
        switch (event) {
            case 'delete_spread':
                var url = layList.U({a: 'empty_spread', q: {uid: data.uid}});
                $eb.$swal('delete', function () {
                    $eb.axios.get(url).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg)
                        } else
                            return Promise.reject(res.data.msg || '清除失败')
                    }).catch(function (err) {
                        $eb.$swal('error', err);
                    });
                }, {
                    title: '您将解除【' + data.nickname + '】的上级推广人，请谨慎操作！',
                    text: '解除后无法恢复',
                    confirm: '是的我要解除'
                })
                break;
            case 'look_code':
                $('#uid').val(data.uid);
                var index = layList.layer.open({
                    type: 1,
                    area: ['200px', 'auto'], //宽高
                    content: $('.option'),
                    title: false,
                    cancel: function () {
                        $('.option').hide();
                        $('#uid').val('');
                    }
                });
                break;
            case 'open_image':
                if ($eb)
                    $eb.openImage(data.headimgurl);
                else
                    layList.layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        shadeClose: true,
                        content: '<img src="' + data.headimgurl + '" style="display: block;width: 100%;" />'
                    });
                break;

        }
    });
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
                    nickname: '',
                    excel: 0,
                },
                showtime: false,
            },
            watch: {},
            methods: {
                getBadge: function () {
                    var that = this;
                    layList.basePost(layList.Url({a: 'get_badge'}), this.where, function (rem) {
                        that.badge = rem.data;
                    });
                },
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
                search: function () {
                    this.where.excel = 0;
                    this.getBadge();
                    console.log(this.where);
                    layList.reload(this.where, true);
                },
                excel: function () {
                    this.where.excel = 1;
                    location.href = layList.U({a: 'get_spread_list', q: this.where});
                },
                refresh: function () {
                    layList.reload();
                    this.getBadge();
                }
            },
            mounted: function () {
                this.getBadge();
                var that = this;
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
