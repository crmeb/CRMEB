<template>
    <div  v-if="orderDatalist">
        <Modal v-model="modals"  scrollable title="订单信息" width="700" class="order_box" footer-hide>
            <Card :bordered="false" dis-hover class="i-table-no-border">
              <div class="ivu-description-list-title">收货信息</div>
              <Row class="mb10">
                <Col span="12">用户昵称：{{orderDatalist.userInfo.nickname}}</Col>
                <Col span="12">收货人：{{orderDatalist.orderInfo.real_name}}</Col>
              </Row>
              <Row class="mb10">
                <Col span="12">联系电话：{{orderDatalist.orderInfo.user_phone}}</Col>
                <Col span="12">收货地址：{{orderDatalist.orderInfo.user_address}}</Col>
              </Row>
              <Divider style="margin: 20px 0 !important;"/>
              <div class="ivu-description-list-title">订单信息</div>
              <Row class="mb10">
                <Col span="12">订单ID：{{orderDatalist.orderInfo.order_id}}</Col>
                <Col span="12" class="fontColor1">订单状态：{{orderDatalist.orderInfo.status_name}}</Col>
              </Row>
              <Row class="mb10">
                <Col span="12">商品名称：{{ orderDatalist.orderInfo.store_name + " | "}}{{orderDatalist.orderInfo.suk ? orderDatalist.orderInfo.suk : ""}}</Col>
              </Row>
              <Row class="mb10">
                <Col span="12">商品总数：{{orderDatalist.orderInfo.total_num}}</Col>
                <Col span="12">商品总积分：{{orderDatalist.orderInfo.total_price}}</Col>
              </Row>
              <Row class="mb10">
                <Col span="12" class="mb10">创建时间：{{orderDatalist.orderInfo.add_time}}</Col>
                <Col span="12" class="mb10" v-if="orderDatalist.orderInfo.remark">商家备注：{{orderDatalist.orderInfo.remark}}</Col>
                <Col span="12" class="mb10" v-if="orderDatalist.orderInfo.fictitious_content">虚拟发货备注：{{orderDatalist.orderInfo.fictitious_content}}</Col>
              </Row>
              <div v-if="orderDatalist.orderInfo.delivery_type==='express'">
                <Divider style="margin: 20px 0 !important;"/>
                <div class="ivu-description-list-title">物流信息</div>
                <Row class="mb10">
                  <Col span="12">快递公司：{{orderDatalist.orderInfo.delivery_name}}</Col>
                  <Col span="12">快递单号：{{orderDatalist.orderInfo.delivery_id}} <Button type="info" size="small" @click="openLogistics">物流查询</Button></Col>
                </Row>
              </div>
              <div v-if="orderDatalist.orderInfo.delivery_type==='send'">
                <Divider style="margin: 20px 0 !important;"/>
                <div class="ivu-description-list-title">配送信息</div>
                <Row class="mb10">
                  <Col span="12">送货人姓名：{{orderDatalist.orderInfo.delivery_name}}</Col>
                  <Col span="12">送货人电话：{{orderDatalist.orderInfo.delivery_id}}</Col>
                </Row>
              </div>
              <div v-if="orderDatalist.orderInfo.mark">
                <Divider style="margin: 20px 0 !important;" v-if="orderDatalist.orderInfo.mark"/>
                <div class="ivu-description-list-title" v-if="orderDatalist.orderInfo.mark">备注信息</div>
                <Row class="mb10">
                  <Col span="12" class="fontColor2">{{orderDatalist.orderInfo.mark}}</Col>
                </Row>
              </div>
            </Card>
        </Modal>
        <Modal v-model="modal2" scrollable title="物流查询"  width="350" class="order_box2">
            <div class="logistics acea-row row-top">
                <div class="logistics_img"><img src="../../../../assets/images/expressi.jpg"></div>
                <div class="logistics_cent">
                    <span>物流公司：{{orderDatalist.orderInfo.delivery_name}}</span>
                    <span>物流单号：{{orderDatalist.orderInfo.delivery_id}}</span>
                </div>
            </div>
            <div class="acea-row row-column-around trees-coadd">
                <div class="scollhide">
                    <Timeline>
                        <TimelineItem v-for="(item,i) in result" :key="i">
                            <p class="time" v-text="item.time"></p>
                            <p class="content" v-text="item.status"></p>
                        </TimelineItem>
                    </Timeline>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
    import { getExpress } from '@/api/marketing';
    export default {
        name: 'orderDetails',
        data () {
            return {
                modal2: false,
                modals: false,
                grid: {
                    xl: 8,
                    lg: 8,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                result: []
            }
        },
        props: {
            orderDatalist: Object,
            orderId: Number
        },
        methods: {
            openLogistics () {
                this.getOrderData()
                this.modal2 = true;
            },
            // 获取订单物流信息
            getOrderData () {
                getExpress(this.orderId).then(async res => {
                    this.result = res.data.result;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            }
        },
        computed: {
        }
    }
</script>

<style scoped lang="stylus">
    .ivu-description-list-title {
        margin-bottom: 16px;
        color: #17233d;
        font-weight: 500;
        font-size: 14px;
    }
    .logistics
        align-items: center
        padding: 10px 0px
        .logistics_img
            width 45px
            height 45px
            margin-right: 12px
            img
             width 100%
             height 100%
        .logistics_cent
            span
              display block
              font-size 12px
    .trees-coadd
        width: 100%;
        height: 400px;
        border-radius: 4px;
        overflow: hidden;
        .scollhide
            width: 100%;
            height: 100%;
            overflow: auto;
            margin-left: 18px;
            padding: 10px 0 10px 0;
            box-sizing: border-box;
            .content
              font-size 12px
            .time
              font-size 12px
              color: #2d8cf0
.order_box2
   position absolute
   z-index 999999999
.order_box >>> .ivu-modal-header
   padding 30x 16px !important
.order_box >>> .ivu-card
    font-size 12px !important
.fontColor1 >>> .ivu-description-term
    color red !important
.fontColor1 >>> .ivu-description-detail
    color red !important
    padding-bottom 14px !important
.fontColor2 >>> .ivu-description-detail
    color #733AF9 !important
.order_box >>> .ivu-description-term
    padding-bottom 10px !important
.order_box >>> .ivu-description-detail
    padding-bottom 10px !important
.order_box >>> .ivu-modal-body
    padding: 0 16px !important
.fontColor3 >>> .ivu-description-term
    color #f1a417 !important
.fontColor3 >>> .ivu-description-detail
    color #f1a417 !important
.tabBox_img
    width 100px
    height 70px
    border-radius:4px
    cursor pointer
    img
       width 100%
       height 100%
       padding 2px
</style>
