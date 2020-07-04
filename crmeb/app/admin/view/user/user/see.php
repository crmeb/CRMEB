{extend name="public/container"}
{block name="content"}
<style>
    .backlog-body{
        padding: 10px 15px;
        background-color: #f8f8f8;
        color: #999;
        border-radius: 2px;
        transition: all .3s;
        -webkit-transition: all .3s;
        overflow: hidden;
        max-height: 84px;
    }
    .backlog-body h3{
        margin-bottom: 10px;
    }
    .right-icon{
        position: absolute;
        right: 10px;
    }
    .backlog-body p cite {
        font-style: normal;
        font-size: 17px;
        font-weight: 300;
        color: #009688;
    }
    .layuiadmin-badge, .layuiadmin-btn-group, .layuiadmin-span-color {
        position: absolute;
        right: 15px;
    }
    .layuiadmin-badge {
        top: 50%;
        margin-top: -9px;
        color: #01AAED;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">会员详情</div>
                <div class="layui-card-body">
                    <ul class="layui-row layui-col-space10 layui-this">
                        {volist name='userinfo' id='vo'}
                            {if trim($vo.value)}
                            <li class="layui-col-xs<?=isset($vo['col']) ? $vo['col'] :4?>">
                                <div class="backlog-body">
                                    <h3>{$vo.name}</h3>
                                    <p><cite {if isset($vo['color'])} style="color: {$vo['color']}" {/if}>{$vo.value}</cite></p>
                                </div>
                            </li>
                            {/if}
                        {/volist}
                    </ul>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">其他详情</div>
                <div class="layui-card-body">
                    <div class="layui-row layui-col-space15">
                    {volist name='headerList' id='vo'}
                    <div class="layui-col-xs3" style="margin-bottom: 10px ">
                        <div class="layui-card">
                            <div class="layui-card-header">
                                {$vo.title}
                                <span class="layui-badge layuiadmin-badge {if isset($vo.class) && $vo.class}{$vo.class}{else}layui-bg-blue{/if}">{$vo.key}</span>
                            </div>
                            <div class="layui-card-body">
                                <p class="layuiadmin-big-font">{$vo.value}</p>
                            </div>
                        </div>
                    </div>
                    {/volist}
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">其他记录</div>
                <div class="layui-card-body">
                    <div class="layui-tab layui-tab-card">
                        <ul class="layui-tab-title">
                            <li class="layui-this">消费能力</li>
                            <li>积分明细</li>
                            <li>签到记录</li>
                            <li>持有优惠劵</li>
                            <li>余额变动记录</li>
                            <li>推广下线明细</li>
                        </ul>
                        <div class="layui-tab-content" id="content">
                            <div class="layui-tab-item layui-show">
                                <table class="layui-table" lay-skin="line" v-cloak="">
                                    <thead>
                                        <tr>
                                            <th>订单编号</th>
                                            <th>收货人</th>
                                            <th>商品数量</th>
                                            <th>商品总价</th>
                                            <th>实付金额</th>
                                            <th>交易完成时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in orderList">
                                            <td class="text-center">{{item.order_id}}
                                                <p>
                                                    <span class="layui-badge" :class="{'layui-bg-green':item.paid==1}" v-text="item.paid==1 ? '已支付': '未支付' ">正在加载</span>
                                                    <span class="layui-badge" :class="{'layui-bg-cyan':item.pay_type=='yue','layui-bg-blue':item.pay_type=='weixin'}" v-text="item.pay_type=='weixin' ? '微信支付': '余额支付' ">正在加载</span>
                                                    <span class="layui-badge layui-bg-black" v-show="item.pink_id!=0">拼团</span>
                                                    <span class="layui-badge layui-bg-blue" v-show="item.seckill_id!=0">秒杀</span>
                                                    <span class="layui-badge layui-bg-gray" v-show="item.bargain_id!=0">砍价</span>
                                                </p>
                                            </td>
                                            <td>{{item.real_name}}</td>
                                            <td>{{item.total_num}}</td>
                                            <td>{{item.total_price}}</td>
                                            <td>{{item.pay_price}}</td>
                                            <td>{{item.pay_time}}</td>
                                        </tr>
                                        <tr v-show="orderList.length<=0" style="text-align: center">
                                            <td colspan="6">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="page_order" v-show="count.order_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" lay-skin="line" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>来源/用途</th>
                                        <th>积分变化</th>
                                        <th>变化后积分</th>
                                        <th>日期</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in integralList">
                                            <td>{{item.title}}</td>
                                            <td>{{item.number}}</td>
                                            <td>{{item.balance}}</td>
                                            <td>{{item.add_time}}</td>
                                            <td>{{item.mark}}</td>
                                        </tr>
                                        <tr v-show="integralList.length<=0" style="text-align: center">
                                            <td colspan="5">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="integral_page" v-show="count.integral_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" lay-skin="line" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>动作</th>
                                        <th>获得积分</th>
                                        <th>签到时间</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in SignList">
                                            <td>{{item.title}}</td>
                                            <td>{{item.number}}</td>
                                            <td>{{item.add_time}}</td>
                                            <td>{{item.mark}}</td>
                                        </tr>
                                        <tr v-show="SignList.length<=0" style="text-align: center">
                                            <td colspan="4">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="Sign_page" v-show="count.sign_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>优惠券名称</th>
                                        <th>面值</th>
                                        <th>有效期</th>
                                        <th>所需积分</th>
                                        <th>兑换时间</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in CouponsList">
                                            <td>{{item.coupon_title}}
                                                <p>
                                                    <span class="layui-badge" :class="{'layui-bg-green':item._type>=1}" v-text="item._type>=1 ? '可使用': '已过期' ">正在加载</span>
                                                </p>
                                            </td>
                                            <td>{{item.coupon_price}}</td>
                                            <td>{{item._add_time}}-{{item._end_time}}</td>
                                            <td>{{item.integral}}</td>
                                            <td>{{item._add_time}}</td>
                                        </tr>
                                        <tr v-show="CouponsList.length<=0" style="text-align: center">
                                            <td colspan="5">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="copons_page" v-show="count.coupon_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>变动金额</th>
                                        <th>变动后</th>
                                        <th>类型</th>
                                        <th>创建时间</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in balanceChangList">
                                            <td>{{item.number}}
                                                <p v-show="item.pm==1">
                                                    <span class="layui-badge layui-bg-green" v-show="item.status==1">有效</span>
                                                    <span class="layui-badge layui-bg-orange" v-show="item.status==0">带确定</span>
                                                    <span class="layui-badge layui-bg-gray" v-show="item.status==-1">无效</span>
                                                </p>
                                            </td>
                                            <td>{{item.balance}}</td>
                                            <td>{{item._type}}</td>
                                            <td>{{item.add_time}}</td>
                                            <td>{{item.mark}}</td>
                                        </tr>
                                        <tr v-show="balanceChangList.length<=0" style="text-align: center">
                                            <td colspan="5">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="balancechang_page" v-show="count.balanceChang_count > limit" style="text-align: right;"></div>
                            </div>
                            <!--推广人-->
                            <div class="layui-tab-item">
                                <table class="layui-table" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>昵称</th>
                                        <th>余额</th>
                                        <th>积分</th>
                                        <th>加入时间</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="item in SpreadList">
                                        <td>
                                            {{item.nickname}}
                                            <p v-show="item.is_vip">
                                                <span class="layui-badge layui-bg-orange" v-text="item.vip_name"></span>
                                            </p>
                                        </td>
                                        <td>{{item.now_money}}</td>
                                        <td>{{item.integral}}</td>
                                        <td>{{item.add_time}}</td>
                                    </tr>
                                    <tr v-show="balanceChangList.length<=0" style="text-align: center">
                                        <td colspan="4">暂无数据</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div ref="spread_page" v-show="count.spread_count > limit" style="text-align: right;"></div>
                            </div>
                            <!--end-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var count=<?=json_encode($count)?>,
        $uid=<?=$uid?>;
    require(['vue'],function(Vue) {
        new Vue({
            el: "#content",
            data: {
                limit:10,
                uid:$uid,
                orderList:[],
                integralList:[],
                SignList:[],
                CouponsList:[],
                balanceChangList:[],
                SpreadList:[],
                count:count,
                page:{
                    order_page:1,
                    integral_page:1,
                    sign_page:1,
                    copons_page:1,
                    balancechang_page:1,
                    spread_page:1,
                },
            },
            watch:{
                'page.order_page':function () {
                    this.getOneorderList();
                },
                'page.integral_page':function () {
                    this.getOneIntegralList();
                },
                'page.sign_page':function () {
                    this.getOneSignList();
                },
                'page.copons_page':function () {
                    this.getOneCouponsList();
                },
                'page.balancechang_page':function () {
                    this.getOneBalanceChangList();
                },
                'page.spread_page':function () {
                    this.getSpreadList();
                }
            },
            methods:{
                getSpreadList:function(){
                    this.request('getSpreadList',this.page.spread_page,'SpreadList');
                },
                getOneorderList:function () {
                    this.request('getOneorderList',this.page.order_page,'orderList');
                },
                getOneIntegralList:function () {
                    this.request('getOneIntegralList',this.page.integral_page,'integralList');
                },
                getOneSignList:function () {
                    this.request('getOneSignList',this.page.sign_page,'SignList');
                },
                getOneCouponsList:function () {
                    this.request('getOneCouponsList',this.page.copons_page,'CouponsList');
                },
                getOneBalanceChangList:function () {
                    this.request('getOneBalanceChangList',this.page.balancechang_page,'balanceChangList');
                },
                request:function (action,page,name) {
                    var that=this;
                    layList.baseGet(layList.U({a:action,p:{page:page,limit:this.limit,uid:this.uid}}),function (res) {
                        that.$set(that,name,res.data)
                    });
                }
            },
            mounted:function () {
                this.getOneorderList();
                this.getOneIntegralList();
                this.getOneSignList();
                this.getOneCouponsList();
                this.getOneBalanceChangList();
                this.getSpreadList();
                var that=this;
                layList.laypage.render({
                    elem: that.$refs.page_order
                    ,count:that.count.order_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.order_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.integral_page
                    ,count:that.count.integral_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.integral_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.Sign_page
                    ,count:that.count.sign_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.sign_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.copons_page
                    ,count:that.count.coupon_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.copons_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.balancechang_page
                    ,count:that.count.balanceChang_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.balancechang_page=obj.curr;
                    }
                });

                layList.laypage.render({
                    elem: that.$refs.spread_page
                    ,count:that.count.spread_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.spread_page=obj.curr;
                    }
                });
            }
        });
    });
</script>
{/block}