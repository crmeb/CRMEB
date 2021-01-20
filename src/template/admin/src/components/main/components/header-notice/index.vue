<template>
  <div class="header-notice">
    <Dropdown @on-click="jumpUrl">
      <div>
        <Badge dot :count="needList.length?needList.length:0">
          <Icon type="ios-notifications-outline" size="26"></Icon>
        </Badge>
      </div>
      <DropdownMenu slot="list">
        <DropdownItem :name="item.url" v-for="(item,index) in needList" :key="index"><Icon :type="item.icon" :style="'background-color:'+item.iconColor" class="iconImg" />{{item.title}}</DropdownItem>
      </DropdownMenu>
    </Dropdown>
  </div>
</template>
<style lang="less">
  .header-notice{
    margin-right: 30px;
  }
  .header-notice .ivu-dropdown-item{
    font-size: 14px!important;
    font-weight: 400;
    line-height: 22px;
    color: #515a6e;
  }
  .header-notice .ivu-dropdown-item~.ivu-dropdown-item{
    border-top: 1px solid #e8eaec;
  }
  .header-notice .iconImg{
    width: 32px;
    height: 32px;
    line-height: 32px;
    border-radius: 50%;
    color: #fff;
    font-size: 18px;
    margin-right: 10px;
    vertical-align: middle;
  }
  .header-notice .ivu-dropdown{
    height: 30px;
    line-height: 30px;
  }
  .header-notice .ivu-dropdown .ivu-select-dropdown{
    margin-top: 22px;
  }
  .header-notice .ivu-badge-dot{
    z-index: 0!important;
  }
</style>
<script>
    import { jnoticeRequest } from '@/api/common'
    import {adminSocket} from '@/libs/socket';
    import { getCookies, removeCookies,setCookies } from '@/libs/util'
    export default {
        name: 'User',
        data () {
            return {
                needList: []
            }
        },
        mounted () {
          this.getNotict()
          this.newOrderAudioLink = this.$store.state.userInfo.newOrderAudioLink;
          adminSocket.then(ws=>{
            ws.send({
              type: 'login',
              data: getCookies('token')
            })
            let that = this;
            that.$on('ADMIN_NEW_PUSH', function (data) {
              that.getNotict();
            })

            that.$on('NEW_ORDER', function (data) {
              console.log(data);
              that.$Notice.info({
                title: '新订单',
                duration: 8,
                desc: '您有一个新的订单(' + data.order_id + '),请注意查看'
              });
              if (this.newOrderAudioLink) (new Audio(this.newOrderAudioLink)).play();
              that.messageList.push({
                title: '新订单提醒',
                icon: 'md-bulb',
                iconColor: '#87d068',
                time: 0,
                read: 0
              });
            });
            that.$on('NEW_REFUND_ORDER', function (data) {
              that.$Notice.warning({
                title: '退款订单提醒',
                duration: 8,
                desc: '您有一个订单(' + data.order_id + ')申请退款,请注意查看'
              });
              if (window.newOrderAudioLink) (new Audio(window.newOrderAudioLink)).play();
              that.messageList.push({
                title: '退款订单提醒',
                icon: 'md-information',
                iconColor: '#fe5c57',
                time: 0,
                read: 0
              });
            });
            that.$on('WITHDRAW', function (data) {
              that.$Notice.warning({
                title: '提现提醒',
                duration: 8,
                desc: '有用户申请提现(' + data.id + '),请注意查看'
              });
              that.messageList.push({
                title: '退款订单提醒',
                icon: 'md-people',
                iconColor: '#f06292',
                time: 0,
                read: 0
              });
            });
            that.$on('STORE_STOCK', function (data) {
              that.$Notice.warning({
                title: '库存预警',
                duration: 8,
                desc: '(' + data.id + ')商品库存不足,请注意查看'
              })
              that.messageList.push({
                title: '库存预警',
                icon: 'md-information',
                iconColor: '#fe5c57',
                time: 0,
                read: 0
              });
            });
            that.$on('PAY_SMS_SUCCESS', function (data) {
              that.$Notice.info({
                title: '短信充值成功',
                duration: 8,
                desc: '恭喜您充值' + data.price + '元，获得' + data.number + '条短信'
              });
              that.messageList.push({
                title: '短信充值成功',
                icon: 'md-bulb',
                iconColor: '#87d068',
                time: 0,
                read: 0
              });
            });
          })
        },
        methods: {
            getNotict () {
                jnoticeRequest().then(res => {
                    this.needList = res.data || []
                }).catch(() => {})
            },
            jumpUrl (url) {
                this.$router.push({ path: url })
            }
        }
    }
</script>
