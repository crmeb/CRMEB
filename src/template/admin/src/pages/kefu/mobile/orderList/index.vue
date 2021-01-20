<template>
    <div class="pos-order-list" ref="container">
        <div class="head-box">
            <div class="nav acea-row row-around row-middle">
                <div
                        class="item"
                        :class="where.type === '' ? 'on' : ''"
                        @click="changeStatus('')"
                >
                    全部
                </div>
                <div
                        class="item"
                        :class="where.type === 0 ? 'on' : ''"
                        @click="changeStatus(0)"
                >
                    未支付
                </div>
                <div
                        class="item"
                        :class="where.type === 2 ? 'on' : ''"
                        @click="changeStatus(2)"
                >
                    未收货
                </div>
                <div
                        class="item"
                        :class="where.type === -1 ? 'on' : ''"
                        @click="changeStatus(-1)"
                >
                    退款中
                </div>
            </div>
            <div class="input-box">
                <Input placeholder="搜索订单编号" v-model="where.search" @on-enter="bindSearch" />
            </div>
        </div>

        <div class="list">
            <vue-scroll :ops="ops" @load-before-deactivate="handleWordsScroll" style="height: 100%"
            >
                <div class="slot-load" slot="load-deactive"></div>
                <div class="slot-load" slot="load-beforeDeactive"></div>
                <div class="slot-load" slot="load-active">下滑加载更多</div>
                <template v-if="list.length > 0">
                    <div class="item" v-for="(item, index) in list" :key="index">
                        <div class="order-num acea-row row-middle" @click="toDetail(item)">
                            订单号：{{ item.order_id }}
                            <span class="time">下单时间：{{ item._add_time }}</span>
                        </div>
                        <template if="item.productList && item.productList.length">
                            <div
                                    class="pos-order-goods"
                                    v-for="(val, key) in item.cartInfo"
                                    :key="key"
                            >
                                <div
                                        class="goods acea-row row-between-wrapper"
                                        @click="toDetail(item)"
                                >
                                    <div class="picTxt acea-row row-between-wrapper">
                                        <div class="pictrue">
                                            <img :src="val.productInfo.image" />
                                        </div>
                                        <div class="text ">
                                            <div class="info line2">
                                                {{ val.productInfo.store_name }}
                                            </div>
                                            <div class="attr line1" v-if="val.productInfo.attrInfo.suk">
                                                {{ val.productInfo.attrInfo.suk }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="money">
                                        <div class="x-money">￥{{ val.productInfo.attrInfo.price }}</div>
                                        <div class="num">x{{ val.cart_num }}</div>
                                        <div class="y-money">
                                            <!--￥{{ val.info.productInfo.attrInfo.otPrice }}-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="public-total">
                            共{{ item.total_num }}件商品，应支付
                            <span class="money">￥{{ item.pay_price }}</span> ( 邮费 ¥{{
                            item.pay_postage
                            }}
                            )
                        </div>
                        <div class="operation acea-row row-between-wrapper">
                            <div class="more">
                                <!--            <div class="iconfontYI icon-gengduo" @click="more(index)"></div>-->
                                <!--            <div class="order" v-show="current === index">-->
                                <!--              <div class="items">-->
                                <!--                {{ where.status > 0 ? "删除" : "取消" }}订单-->
                                <!--              </div>-->
                                <!--              <div class="arrow"></div>-->
                                <!--            </div>-->
                            </div>
                            <div class="acea-row row-middle">
                                <div class="bnt" @click="modify(item, 0)" v-if="where.type === 0">
                                    一键改价
                                </div>
                                <div class="bnt" @click="modify(item, 1)">订单备注</div>
                                <div
                                        class="bnt"
                                        @click="modify(item, 0)"
                                        v-if="where.type == -3 && item.refund_status === 1"
                                >
                                    立即退款
                                </div>
                                <div
                                        class="bnt cancel"
                                        v-if="item.pay_type === 'offline' && item.paid === 0"
                                        @click="offlinePay(item)"
                                >
                                    确认付款
                                </div>
                                <router-link
                                        class="bnt"
                                        v-if="where.type === 1 && item.shipping_type !== 2"
                                        :to="'/kefu/orderDelivery/' + item.id + '/' + item.order_id"
                                >去发货
                                </router-link>
                                <div
                                    class="bnt cancel"
                                    v-if="where.type === 1 && item.shipping_type === 2"
                                    @click="storeCancellation(item)"
                            >
                                去核销
                            </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-if="!loading && list.length === 0 && where.type === ''">
                    <div style="text-align: center;">
                        <img src="@/assets/images/no_all.png" alt="" style="width: 3.9rem">
                        <p style="color: #9F9F9F">亲，该客户暂无订单～</p>
                    </div>
                </template>
                <template v-if="!loading && list.length === 0 && where.type === 0">
                    <div style="text-align: center;">
                        <img src="@/assets/images/no_zf.png" alt="" style="width: 3.9rem" >
                        <p style="color: #9F9F9F">暂无未支付订单～</p>
                    </div>
                </template>
                <template v-if="!loading && list.length === 0 && where.type === 2">
                    <div style="text-align: center;">
                        <img src="@/assets/images/no_fh.png" alt="" style="width: 3.9rem" >
                        <p style="color: #9F9F9F">暂无未收货订单～</p>
                    </div>
                </template>
                <template v-if="!loading && list.length === 0 && where.type === -1">
                    <div style="text-align: center;">
                        <img src="@/assets/images/no_tk.png" alt="" style="width: 3.9rem" >
                        <p style="color: #9F9F9F">暂无退款订单～</p>
                    </div>
                </template>
            </vue-scroll>
        </div>
<!--        <Loading :loaded="loaded" :loading="loading"></Loading>-->
        <PriceChange
                v-if="orderInfo"
                :change="change"
                :orderInfo="orderInfo"
                v-on:closechange="changeclose($event)"
                @closeChange="closeChange($event)"
                :status="status"
        ></PriceChange>
        <write-off
                v-if="iShidden"
                :iShidden="iShidden"
                :orderInfo="orderInfo"
                @cancel="cancel"
                @confirm="confirm"
        ></write-off>
    </div>
</template>
<script>
    import PriceChange from "../../components/PriceChange";
    import Loading from "../../components/Loading";
    import { getorderList, orderVerificApi } from '@/api/kefu';
    import { required, num } from "@/utils/validate";
    import { validatorDefaultCatch } from "@/libs/dialog";
    import WriteOff from "../../components/writeOff";
    import { HappyScroll } from 'vue-happy-scroll'
    export default {
        name: "AdminOrderList",
        components: {
            WriteOff,
            PriceChange,
            Loading,
            HappyScroll
        },
        props: {},
        data: function() {
            return {
                current: "",
                change: false,
                types: 0,
                where: {
                    page: 1,
                    limit: 5,
                    search: '',
                    type: ""
                },
                list: [],
                loaded: false,
                loading: false,
                orderInfo: {},
                status: null,
                iShidden: false,
                ops:{
                    vuescroll:{
                        mode: 'slide',
                        enable: false,
                        tips: {
                            deactive: 'Push to Load',
                            active: 'Release to Load',
                            start: 'Loading...',
                            beforeDeactive: 'Load Successfully!'
                        },
                        auto: false,
                        autoLoadDistance: 0,
                        pullRefresh: {
                            enable: false
                        },
                        pushLoad: {
                            enable: true,
                            auto: true,
                            autoLoadDistance: 10
                        }
                    },
                    bar:{
                        background:'#393232',
                        opacity:'.5',
                        size:'2px'
                    }
                },
            };
        },
        watch: {
            "$route.params.type": function(newVal) {
                let that = this;
                if (newVal != undefined) {
                    that.where.type = newVal;
                    that.init();
                }
            },
            types: function() {
                this.getIndex();
            }
        },
        created() {
           // import('@/assets/js/media_750')
        },
        mounted() {

            this.current = "";
            this.getIndex();
            this.$scroll(this.$refs.container, () => {
                !this.loading && this.getIndex();
            });
        },
        methods: {
            // 搜索回车
            bindSearch(){
                this.init()
            },
            // 去核销
            storeCancellation(item){
                this.orderInfo = item;
                this.iShidden = true;
            },
            cancel: function(res) {
                this.iShidden = res;
            },
            confirm: function() {
                orderVerificApi(this.orderInfo.id)
                    .then(res => {
                        this.iShidden = false;
                        this.init();
                        this.$dialog.success(res.msg);
                    })
                    .catch(res => {
                        this.$dialog.error(res.msg);
                    });
            },
            more: function(index) {
                if (this.current === index) this.current = "";
                else this.current = index;
            },
            modify: function(item, status) {
                this.change = true;
                this.orderInfo = item;
                this.status = status;
            },
            closeChange(msg){
                this.change = msg;
            },
            changeclose: function(msg) {
                this.change = msg;
                this.init();
            },
            // 拒绝退款
            getRefuse(id) {
                orderRefuseApi(data).then(() =>{
                    that.change = false;
                    that.$dialog.success("已拒绝退款");
                    that.init();
                }).catch((error) => {
                    that.$dialog.error(error.message);
                });
            },
            async savePrice(opt) {
                let that = this,
                    data = {},
                    price = opt.price,
                    refundPrice = opt.refundPrice,
                    refundStatus = that.orderInfo.refundStatus,
                    remark = opt.remark;
                if (that.status == 0 && refundStatus === 0) {
                    try {
                        await this.$validator({
                            price: [
                                required(required.message("金额"))
                            ]
                        }).validate({ price });
                    } catch (e) {
                        return validatorDefaultCatch(e);
                    }
                    data.price = price;
                    data.orderId  = opt.orderId;
                    editPriceApi(data).then(() =>{
                        that.change = false;
                        that.$dialog.success("改价成功");
                        that.init();
                    }).catch((error) => {
                        that.$dialog.error(error.message);
                    });
                } else if (that.status == 0 && refundStatus === 1) {
                    try {
                        await this.$validator({
                            refundPrice: [
                                required(required.message("金额")),
                                num(num.message("金额"))
                            ]
                        }).validate({ refundPrice });
                    } catch (e) {
                        return validatorDefaultCatch(e);
                    }
                    data.amount = refundPrice;
                    data.type = opt.type;
                    data.orderId  = opt.orderId;
                    orderRefundApi(data).then(
                        res => {
                            that.change = false;
                            that.$dialog.success('退款成功');
                            that.init();
                        },
                        err => {
                            that.change = false;
                            that.$dialog.error(err.message);
                        }
                    );
                } else {
                    try {
                        await this.$validator({
                            remark: [required(required.message("备注"))]
                        }).validate({ remark });
                    } catch (e) {
                        return validatorDefaultCatch(e);
                    }
                    data.mark = remark;
                    data.id = opt.id;
                    orderMarkApi(data).then(
                        res => {
                            that.change = false;
                            that.$dialog.success('提交成功');
                            that.init();
                        },
                        err => {
                            that.change = false;
                            that.$dialog.error(err.msg);
                        }
                    );
                }
            },
            init: function() {
                this.list = [];
                this.where.page = 1;
                this.loaded = false;
                this.loading = false;
                this.getIndex();
                this.current = "";
            },
            getIndex() {
                if (this.loading || this.loaded) return;
                this.loading = true;
                getorderList(this.$route.params.toUid,this.where).then(
                    res => {
                        this.loading = false;
                        this.loaded = res.data.length < this.where.limit;
                        this.list.push.apply(this.list, res.data || []);
                        this.where.page = this.where.page + 1;
                    },
                    err => {
                        this.$dialog.error(err.msg);
                    }
                );
            },
            changeStatus(val) {
                if (this.where.type !== val) {
                    this.where.type = val;
                    this.init();
                }
            },
            toDetail(item) {
               this.$router.push({ path: "/kefu/orderDetail/" + item.id });
            },
            offlinePay(item) {
                // setOfflinePay({ order_id: item.order_id }).then(
                //   res => {
                //     this.$dialog.success(res.message);
                //     this.init();
                //   },
                //   error => {
                //     this.$dialog.error(error.message);
                //   }
                // );
            },
            // 话术滚动到底部
            handleWordsScroll(vm, refreshDom, done){
                this.getIndex()
                done();
            },
        }
    };
</script>
<style scoped lang="stylus">
    .pos-order-goods{padding:0 0.3rem;background-color: #fff; }
    .pos-order-goods .goods{height:1.85rem;}
    .pos-order-goods .goods~.goods{border-top:1px dashed #e5e5e5;}
    .pos-order-goods .goods .picTxt{width:5.15rem;}
    .pos-order-goods .goods .picTxt .pictrue{width:1.3rem;height:1.3rem;}
    .pos-order-goods .goods .picTxt .pictrue img{width:100%;height:100%;border-radius:0.06rem;}
    .pos-order-goods .goods .picTxt .text{width:3.65rem;height:1.3rem;}
    .pos-order-goods .goods .picTxt .text .info{font-size:0.28rem;color:#282828;}
    .pos-order-goods .goods .picTxt .text .attr{font-size:0.2rem;color:#999;height: 0.8rem;
        line-height: 0.8rem;}
    .pos-order-goods .goods .money{width:1.64rem;text-align:right;font-size:0.28rem;height: 1.3rem;}
    .pos-order-goods .goods .money .x-money{color:#282828;}
    .pos-order-goods .goods .money .num{color:#ff9600;margin:0.05rem 0;}
    .pos-order-goods .goods .money .y-money{color:#999;text-decoration:line-through;}
    .pos-order-list{display:flex;flex-direction column; background: #f5f5f5; height: 100%; }
    .pos-order-list .head-box{
        width:100%;background-color:#fff;
        .input-box{
            width: 6.9rem;
            margin: .2rem auto;
            background: #F5F6F9;
            border-radius: .39rem;
            >>> .ivu-input{
                font-size: .28rem !important;
                background: #F5F6F9;
                border-radius: .39rem;
            }
            >>> .ivu-input, .ivu-input:hover, .ivu-input:focus {
                border: transparent;
                box-shadow: none;
            }
        }
    }
    .pos-order-list .nav{width:100%;height:0.8rem;font-size:0.3rem;color:#282828; display: flex;align-items: center;}
    .pos-order-list .nav .item{ position: relative; line-height: .8rem}
    .pos-order-list .nav .item.on{color:#3875EA; border-bottom: 1px solid #3875EA;}
    .pos-order-list .list{flex 1; margin-top:0.1rem; overflow hidden;}
    .pos-order-list .list .item{background-color:#fff;width:100%;}
    .pos-order-list .list .item~.item{margin-top:0.24rem;}
    .pos-order-list .list .item .order-num{height:1.24rem;border-bottom:1px solid #eee;font-size:0.3rem;font-weight:bold;color:#282828;padding:0 0.3rem;}
    .pos-order-list .list .item .order-num .time{font-size:0.26rem;font-weight:normal;color:#999;margin-top: -0.4rem;}
    .pos-order-list .list .item .operation{padding:0.2rem 0.3rem;margin-top: 0.03rem;}
    .pos-order-list .list .item .operation .more{position:relative;}
    .pos-order-list .list .item .operation .icon-gengduo{font-size:0.5rem;color:#aaa;}

    .pos-order-list .list .item .operation .order .arrow{width: 0;height: 0;border-left: 0.11rem solid transparent;border-right: 0.11rem solid transparent;border-top: 0.2rem solid #e5e5e5;position:absolute;left: 0.15rem;bottom:-0.18rem;}
    .pos-order-list .list .item .operation .order .arrow:before{content:'';width: 0;height: 0;border-left: 0.07rem solid transparent;border-right: 0.07rem solid transparent;border-top: 0.2rem solid #fff;position:absolute;left:-0.07rem;bottom:0;}
    .pos-order-list .list .item .operation .order{width:2rem;background-color:#fff;border:1px solid #eee;border-radius:0.1rem;position:absolute;top:-1rem;z-index:9;}
    .pos-order-list .list .item .operation .order .items{height:0.77rem;line-height:0.77rem;text-align:center;}
    .pos-order-list .list .item .operation .order .items~.items{border-top:1px solid #f5f5f5;}

    .pos-order-list .list .item .operation .bnt{font-size:0.28rem;color:#5c5c5c;width:1.7rem;height:0.6rem;border-radius:0.3rem;border:1px solid #bbb;text-align:center;line-height:0.6rem;}
    .pos-order-list .list .item .operation .bnt~.bnt{margin-left:0.14rem;}
    .public-total{font-size:0.28rem;color:#282828;border-top:1px solid #eee;height:0.92rem;line-height:0.92rem;text-align:right;padding:0 0.3rem;background-color: #fff;}
    .public-total .money{color:#ff4c3c;}
</style>
