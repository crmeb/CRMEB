<template>
    <div class="deliver-goods" v-if="delivery">
        <header>
            <div class="order-num acea-row row-between-wrapper">
                <div class="num line1">订单号：{{ orderId }}</div>
                <div class="name line1">
                    <span class="iconfontYI icon-yonghu2"></span>{{ delivery.userInfo?delivery.userInfo.nickname:'' }}
                </div>
            </div>
            <div class="address">
                <div class="name">
                    {{ delivery.orderInfo.real_name
                    }}<span class="phone">{{ delivery.orderInfo.phone }}</span>
                </div>
                <div>{{ delivery.orderInfo.user_address }}</div>
            </div>
            <div class="line"><img src="../../../../assets/images/line.jpg"/></div>
        </header>
        <div class="wrapper">
            <div class="item acea-row row-between-wrapper">
                <div>发货方式</div>
                <div class="mode acea-row row-middle row-right">
                    <div
                            class="goods"
                            :class="active === index ? 'on' : ''"
                            v-for="(item, index) in types"
                            :key="index"
                            @click="changeType(item, index)"
                    >
                        {{ item.title }}<span class="iconfontYI icon-xuanzhong2"></span>
                    </div>
                </div>
            </div>
            <div class="item acea-row row-between-wrapper" v-if="active === 0">
                <div>发货类型</div>
                <div class="mode acea-row row-middle row-right">
                    <div
                            class="goods"
                            :class="activeExpTpe === index ? 'on' : ''"
                            v-for="(item, index) in expressType"
                            :key="index"
                            @click="changeExpTpe(item, index)"
                    >
                        {{ item.title }}<span class="iconfontYI icon-xuanzhong2"></span>
                    </div>
                </div>
            </div>
            <div class="list" v-if="active === 0">
                <div class="item acea-row row-between-wrapper">
                    <div>快递公司</div>
                    <span class="checkName" v-text="expFrom.delivery_name" @click="show"></span>
                    <vue-pickers :data="pickData"
                                 :showToolbar="true"
                                 :maskClick="true"
                                 @cancel="cancel"
                                 @confirm="confirm"
                                 :defaultIndex="0"
                                 :visible.sync="pickerVisible"
                    ></vue-pickers>
                </div>
                <div class="item acea-row row-between-wrapper" v-if="expFrom.express_record_type === 1">
                    <div>快递单号</div>
                    <input
                            type="text"
                            placeholder="填写快递单号"
                            v-model="expFrom.delivery_id"
                            class="mode input-input"
                    />
                </div>
                <div class="item acea-row row-between-wrapper" v-if="expFrom.express_record_type === 1">
                    <div class="tip">顺丰请输入单号：收件人或寄件人手机号后四位,</div>
                    <div class="tip">例如：SF000000000000:3941</div>
                </div>
            </div>
            <div class="list" v-if="expTemp.length && active === 0">
                <div class="item acea-row row-between-wrapper">
                    <div>电子面单</div>
                    <div class="acea-row">
                        <span class="checkName" v-text="expFrom.delivery_name" @click="showExpTemp"></span>
                        <vue-pickers :data="expTempData"
                                     :showToolbar="true"
                                     :maskClick="true"
                                     @confirm="confirmExpTemp"
                                     :defaultIndex="0"
                                     :visible.sync="pickerVisibleExpTemp"
                        ></vue-pickers>
                        <div class="look">
                            <span>预览</span>
                            <viewer class="viewer" ref="viewer">
                                <img v-lazy="tempImg" class="image">
                            </viewer>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list" v-if="expFrom.express_record_type === 2 && active === 0">
                <div class="item acea-row row-between-wrapper">
                    <div>寄件人姓名</div>
                    <input
                            type="text"
                            placeholder="填写寄件人姓名"
                            v-model="expFrom.to_name"
                            class="mode input-input"
                    />
                </div>
                <div class="item acea-row row-between-wrapper">
                    <div>寄件人电话</div>
                    <input
                            type="text"
                            placeholder="填写寄件人电话"
                            v-model="expFrom.to_tel"
                            class="mode input-input"
                    />
                </div>
                <div class="item acea-row row-between-wrapper">
                    <div>寄件人地址</div>
                    <input
                            type="text"
                            placeholder="填写寄件人地址"
                            v-model="expFrom.to_addr"
                            class="mode input-input"
                    />
                </div>
            </div>
            <div class="list" v-if="active === 1">
                <div class="item acea-row row-between-wrapper">
                    <div>送货人</div>
                    <span class="checkName" v-text="expFrom.sh_delivery_name" @click="showName"></span>
                    <vue-pickers :data="deliveryList"
                                 :showToolbar="true"
                                 :maskClick="true"
                                 @confirm="confirmName"
                                 :defaultIndex="0"
                                 :visible.sync="pickerVisibleName"
                    ></vue-pickers>
                </div>
                <div class="item acea-row row-between-wrapper">
                    <div>送货人电话</div>
                    <input
                            type="text"
                            placeholder="填写送货人电话"
                            v-model="expFrom.sh_delivery_id"
                            class="mode input-input"
                    />
                </div>
            </div>
            <textarea v-if="active === 2" v-model="expFrom.fictitious_content" class="textarea" placeholder="备注" :maxlength="500"></textarea>
        </div>
        <div style="height:1.2rem;"></div>
        <div class="confirm" @click="saveInfo">确认提交</div>
    </div>
</template>
<script>
    import {orderSendApi, orderDetailApi} from '@/api/order';
    import {orderTemp, orderInfo, orderExport, orderDelivery, getSender, orderDeliveryAll} from '@/api/kefu';
    import {required, num} from "@/utils/validate";
    import {validatorDefaultCatch} from "@/libs/dialog";
    import vuePickers from 'vue-pickers';
    export default {
        name: "GoodsDeliver",
        components: { vuePickers },
        props: {},
        data: function () {
            return {
                pickerVisible: false, // 快递公司选择
                types: [
                    {
                        type: 1,
                        title: "发货"
                    },
                    {
                        type: 2,
                        title: "送货"
                    },
                    {
                        type: 3,
                        title: "无需发货"
                    }
                ],
                expressType: [
                    {
                        title: '手动填写',
                        key: 1
                    },
                    {
                        title: '电子面单打印',
                        key: 2
                    },
                ],
                active: 0,
                activeExpTpe: 0,
                orderId: "",
                delivery: null,
                pickData: [],
                type: "1",
                result: {},
                expFrom:{
                    type: 1, // 发货方式
                    delivery_name: '', //快递公司
                    delivery_id: '', //快递单号
                    delivery_code: '', //快递公司编码
                    express_record_type: 1, // 发货类型
                    express_temp_id: '', // 电子面单模板
                    to_name: '',
                    to_tel: '',
                    to_addr: '',
                    sh_delivery_name: '',
                    sh_delivery_id: '',
                    sh_delivery_uid: '',
                    fictitious_content: ''
                },
                expTemp: [],
                pickerVisibleName: false, // 送货人选择
                pickerVisibleExpTemp: false, //电子面单选择
                expTempData: [], // 面单数据
                tempName: '', // 面单名称
                tempImg: '', //面单图片
                deliveryList: [] // 送货人数据
            };
        },
        watch: {
            "$route.params.orderId": function (newVal) {
                let that = this;
                if (newVal != undefined) {
                    that.orderId = newVal;
                    that.getIndex();
                }
            }
        },
        created() {
            // import('@/assets/js/media_750')
        },
        mounted: function () {
            this.orderId = this.$route.params.orderId;
            this.getIndex();
            this.getLogistics();
        },
        methods: {
            // 显示送货人
            showName(){
                this.pickerVisibleName = true
            },
            // 获取配送人
            getDelivery(){
                orderDeliveryAll().then(res=>{
                    let tdata = []
                    res.data.map(item => {
                        tdata.push({
                            label: item.nickname,
                            value: item.uid,
                            phone: item.phone
                        })
                    });
                    this.deliveryList = [tdata];
                    this.expFrom.sh_delivery_name = tdata[0].label;
                    this.expFrom.sh_delivery_id = tdata[0].phone;
                    this.expFrom.sh_delivery_uid = tdata[0].value;
                    if(this.expFrom.express_record_type === 2) this.getTemp();
                })
            },
            // 选择送货人
            confirmName(res){
                this.expFrom.sh_delivery_name = res[0].label;
                this.expFrom.sh_delivery_id = res[0].phone;
                this.expFrom.sh_delivery_uid = res[0].value
            },
            // 获取订单打印默认配置
            orderDeliveryInfo(){
                getSender().then(res=>{
                    this.expFrom.to_name = res.data.to_name;
                    this.expFrom.to_tel = res.data.to_tel;
                    this.expFrom.to_addr = res.data.to_add;
                })
            },
            cancel() {
                // this.result = 'click cancel result: null'
            },
            // 选择发货类型
            changeExpTpe(item, index) {
                this.expFrom.express_record_type = item.key;
                this.activeExpTpe = index;
                if(item.key === 2){
                    this.orderDeliveryInfo();
                    this.getTemp();
                }else{
                    this.expTemp = [];
                }
            },
            // 快递模板
            getTemp(){
                orderTemp({
                    com: this.expFrom.delivery_code
                }).then(res=>{
                    this.expTemp = res.data.data;
                    let tdata = []
                    if(this.expTemp.length){
                        this.expTemp.map(item => {
                            tdata.push({
                                label: item.title,
                                value: item.temp_id,
                                id: item.id,
                                pic: item.pic,
                                code: item.code
                            })
                        });
                        this.expTempData = [tdata];
                        this.expFrom.express_temp_id = tdata[0].value;
                        this.tempName = tdata[0].label;
                        this.tempImg = tdata[0].pic;
                    }

                })
            },
            // 选择电子面单模板
            confirmExpTemp(res) {
                this.expFrom.express_temp_id = res[0].value;
                this.tempName = res[0].label;
                this.tempImg = res[0].pic
            },
            // 选择快递公司
            confirm(res) {
                this.expFrom.delivery_name = res[0].label;
                this.expFrom.delivery_code = res[0].value;
                if(this.expFrom.express_record_type === 2) this.getTemp();
            },
            show() {
                this.pickerVisible = true
            },
            showExpTemp(){
                this.pickerVisibleExpTemp = true
            },
            // 发货方式
            changeType: function (item, index) {
                this.active = index;
                this.expFrom.type = item.type;
                if(index ===1) this.getDelivery();
            },
            getIndex() {
                orderInfo(this.$route.params.id).then(res => {
                    this.delivery = res.data;
                }).catch((error) => {
                    this.$dialog.error(error.msg);
                })
            },
            getLogistics() {
                orderExport().then(async res => {
                    let tdata = []
                    res.data.map(item => {
                        tdata.push({
                            label: item.value,
                            value: item.code,
                            id: item.id
                        })
                    });
                    this.pickData = [tdata];
                    this.expFrom.delivery_name = tdata[0].label;
                    this.expFrom.delivery_code = tdata[0].value;
                    if(this.expFrom.express_record_type === 2) this.getTemp();
                })
            },
            async saveInfo() {
                let that = this,
                    type = that.type,
                    // expressId = that.expressId,
                    // expressCode = that.expressCode,
                    save = {};
                // save.id = that.$route.params.id;
                // save.type = that.expFrom.type;
                switch (type) {
                    case "1":
                        if (this.expFrom.type === 1 && !that.expFrom.delivery_name) return that.$dialog.error('请输入快递公司');
                        if (this.expFrom.type === 1 && this.expFrom.express_record_type === 1 && !that.expFrom.delivery_id) return that.$dialog.error('请输入快递单号');
                        if (this.expFrom.type === 1 && !that.expFrom.express_temp_id && this.expFrom.express_record_type === 2) return that.$dialog.error('请选择电子面单');
                        that.setInfo(that.expFrom);
                        break;
                    case "2":
                        try {
                            await this.$validator({
                                expressId: [required(required.message("发货人姓名"))],
                                expressCode: [required(required.message("发货人电话"))]
                            }).validate({expressId, expressCode});
                        } catch (e) {
                            return validatorDefaultCatch(e);
                        }
                        save.expressId = expressId;
                        save.expressCode = expressCode;
                        that.setInfo(save);
                        break;
                    case "3":
                        that.setInfo(save);
                        break;
                }
            },
            setInfo: function (item) {
                let that = this;
                orderDelivery(that.$route.params.id,item).then(
                    res => {
                        that.$dialog.success('发送货成功');
                        that.$router.go(-1);
                    },
                    error => {
                        that.$dialog.error(error.msg);
                    }
                );
            }
        }
    };
</script>
<style scoped lang="less">
    .textarea {
        display: block;
        min-height: 1.92rem;
        padding: 0.3rem;
        width: 100%;
        border: 0;
        outline: none;
        border-bottom: 1px solid #f0f0f0;
        resize:none;
    }
    .cheeckName{
        width: 1rem;
        text-align: right;
    }
    .viewer{
        opacity: 0;
        top: 1%;
        position: absolute;
        .image{
            width: 1rem;
            height: 0.5rem;
        }
    }
    .look{
        color: #1890FF;
        margin-left: 0.2rem;
        position: relative;
    }
    .deliver-goods header {
        width: 100%;
        background-color: #fff;
    }

    .deliver-goods header .order-num {
        padding: 0 0.3rem;
        border-bottom: 1px solid #f5f5f5;
        height: 0.67rem;
    }

    .deliver-goods header .order-num .num {
        width: 4.3rem;
        font-size: 0.26rem;
        color: #282828;
        position: relative;
    }

    .deliver-goods header .order-num .num:after {
        position: absolute;
        content: '';
        width: 1px;
        height: 0.3rem;
        background-color: #ddd;
        top: 50%;
        margin-top: -0.15rem;
        right: 0;
    }

    .deliver-goods header .order-num .name {
        width: 2.6rem;
        font-size: 0.26rem;
        color: #282828;
        text-align: center;
    }

    .deliver-goods header .order-num .name .iconfontYI {
        font-size: 0.35rem;
        color: #477ef3;
        vertical-align: middle;
        margin-right: 0.1rem;
    }

    .deliver-goods header .address {
        font-size: 0.26rem;
        color: #868686;
        background-color: #fff;
        padding: 0.3rem;
    }

    .deliver-goods header .address .name {
        font-size: 0.3rem;
        color: #282828;
        margin-bottom: 0.1rem;
    }

    .deliver-goods header .address .name .phone {
        margin-left: 0.4rem;
    }

    .deliver-goods header .line {
        width: 100%;
        height: 0.03rem;
    }

    .deliver-goods header .line img {
        width: 100%;
        height: 100%;
        display: block;
    }

    .deliver-goods .wrapper {
        width: 100%;
        background-color: #fff;
    }

    .deliver-goods .wrapper .item {
        border-bottom: 1px solid #f0f0f0;
        padding: 0 0.3rem;
        height: 0.96rem;
        font-size: 0.32rem;
        color: #282828;
        position: relative;
    }

    .deliver-goods .wrapper .item .tip{
        color: #c4c4c4;
        text-align: right;
        width: 100%;
        font-size: 0.25rem;
    }

    .deliver-goods .wrapper .item .mode {
        width: 4.6rem;
        height: 100%;
        text-align: right;
        outline: none;
    }

    .deliver-goods .wrapper .item .mode .iconfontYI {
        font-size: 0.3rem;
        margin-left: 0.13rem;
    }

    .deliver-goods .wrapper .item .mode .goods ~ .goods {
        margin-left: 0.3rem;
    }

    .deliver-goods .wrapper .item .mode .goods {
        color: #bbb;
    }

    .deliver-goods .wrapper .item .mode .goods.on {
        color: #477ef3;
    }

    .deliver-goods .wrapper .item .icon-up {
        position: absolute;
        font-size: 0.35rem;
        color: #2c2c2c;
        right: 0.3rem;
    }

    .deliver-goods .wrapper .item select {
        direction: rtl;
        padding-right: 0.6rem;
        position: relative;
        z-index: 2;
    }

    .deliver-goods .wrapper .item input::placeholder {
        color: #bbb;
    }

    .deliver-goods .confirm {
        font-size: 0.32rem;
        color: #fff;
        width: 100%;
        height: 1rem;
        background-color: #477ef3;
        text-align: center;
        line-height: 1rem;
        position: fixed;
        bottom: 0;
    }
</style>
