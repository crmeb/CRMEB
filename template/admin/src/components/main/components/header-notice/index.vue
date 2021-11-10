<template>
  <div class="header-notice">
    <Dropdown @on-click="jumpUrl">
      <div>
        <Badge dot :count="needList.length ? needList.length : 0">
          <Icon type="ios-notifications-outline" size="26"></Icon>
        </Badge>
      </div>
      <DropdownMenu slot="list">
        <DropdownItem
          :name="item.url"
          v-for="(item, index) in needList"
          :key="index"
          ><Icon
            :type="item.icon"
            :style="'background-color:' + item.iconColor"
            class="iconImg"
          />{{ item.title }}</DropdownItem
        >
      </DropdownMenu>
    </Dropdown>
  </div>
</template>
<style lang="less">
.header-notice {
  margin-right: 30px;
}
.header-notice .ivu-dropdown-item {
  font-size: 14px !important;
  font-weight: 400;
  line-height: 22px;
  color: #515a6e;
}
.header-notice .ivu-dropdown-item ~ .ivu-dropdown-item {
  border-top: 1px solid #e8eaec;
}
.header-notice .iconImg {
  width: 32px;
  height: 32px;
  line-height: 32px;
  border-radius: 50%;
  color: #fff;
  font-size: 18px;
  margin-right: 10px;
  vertical-align: middle;
}
.header-notice .ivu-dropdown {
  height: 30px;
  line-height: 30px;
}
.header-notice .ivu-dropdown .ivu-select-dropdown {
  margin-top: 22px;
}
.header-notice .ivu-badge-dot {
  z-index: 0 !important;
}
</style>
<script>
let newOrderAudioLink = new Audio(
  require("@/assets/video/newOrderAudioLink.mp3")
);
import { jnoticeRequest } from "@/api/common";
import { adminSocket } from "@/libs/socket";
import { getCookies, removeCookies, setCookies } from "@/libs/util";
export default {
  name: "User",
  data() {
    return {
      needList: [],
      newOrderAudioLink: null,
    };
  },
  mounted() {
    this.getNotict();
    this.newOrderAudioLink = newOrderAudioLink;
    adminSocket.then((ws) => {
      ws.send({
        type: "login",
        data: getCookies("token"),
      });
      let that = this;
      ws.$on("ADMIN_NEW_PUSH", function (data) {
        that.getNotict();
      });

      ws.$on("NEW_ORDER", function (data) {
        that.$Notice.info({
          title: "新订单",
          duration: 8,
          desc: "您有一个新的订单,ID为(" + data.order_id + "),请注意查看",
        });
        if (this.newOrderAudioLink) this.newOrderAudioLink.play();
        that.messageList.push({
          title: "新订单提醒",
          icon: "md-bulb",
          iconColor: "#87d068",
          time: 0,
          read: 0,
        });
      });
      ws.$on("NEW_REFUND_ORDER", function (data) {
        that.$Notice.warning({
          title: "退款订单提醒",
          duration: 8,
          desc: "您有一个订单申请退款,ID为(" + data.order_id + "),请注意查看",
        });
        if (window.newOrderAudioLink) this.newOrderAudioLink.play();
        that.messageList.push({
          title: "退款订单提醒",
          icon: "md-information",
          iconColor: "#fe5c57",
          time: 0,
          read: 0,
        });
      });
      ws.$on("WITHDRAW", function (data) {
        that.$Notice.warning({
          title: "提现提醒",
          duration: 8,
          desc: "有用户申请提现,编号为(" + data.id + "),请注意查看",
        });
        that.messageList.push({
          title: "退款订单提醒",
          icon: "md-people",
          iconColor: "#f06292",
          time: 0,
          read: 0,
        });
      });
      ws.$on("STORE_STOCK", function (data) {
        that.$Notice.warning({
          title: "库存预警",
          duration: 8,
          desc: "商品ID为(" + data.id + ")的库存不足啦,请注意查看~",
        });
        that.messageList.push({
          title: "库存预警",
          icon: "md-information",
          iconColor: "#fe5c57",
          time: 0,
          read: 0,
        });
      });
      ws.$on("PAY_SMS_SUCCESS", function (data) {
        that.$Notice.info({
          title: "短信充值成功",
          duration: 8,
          desc: "恭喜您充值" + data.price + "元，获得" + data.number + "条短信",
        });
        that.messageList.push({
          title: "短信充值成功",
          icon: "md-bulb",
          iconColor: "#87d068",
          time: 0,
          read: 0,
        });
      });
    });
  },
  methods: {
    getNotict() {
      jnoticeRequest()
        .then((res) => {
          this.needList = res.data || [];
        })
        .catch(() => {});
    },
    jumpUrl(url) {
      this.$router.push({ path: url });
    },
  },
};
</script>
