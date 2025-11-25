<template>
  <div class="layout-navbars-breadcrumb-user-news">
    <div class="head-box">
      <div class="head-box-title">系统通知</div>
      <div class="head-box-btn" v-if="newsList.length > 0" v-db-click @click="onAllReadClick">全部已读</div>
    </div>
    <div class="content-box">
      <template v-if="newsList.length > 0">
        <div class="content-box-item" v-for="(v, k) in newsList" :key="k" v-db-click @click="jumpUrl(v.url)">
          <img class="icon" :src="icon(v.type)" alt="" />
          <div class="content-box-right">
            <div class="content-box-type">{{ v.type | msgType }}</div>
            <div class="content-box-msg">
              {{ v.title }}
            </div>
          </div>

          <!-- <div class="content-box-time">{{ v.time }}</div> -->
        </div>
      </template>
      <div class="content-box-empty" v-else>
        <div class="content-box-empty-margin">
          <img class="no-msg" src="@/assets/images/no-msg.png" alt="" />
          <div class="mt15">暂无通知</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
let newOrderAudioLink = new Audio(require('@/assets/video/newOrderAudioLink.mp3'));
import { jnoticeRequest } from '@/api/common';
import { adminSocket } from '@/libs/socket';
import { getCookies, removeCookies, setCookies } from '@/libs/util';
export default {
  name: 'layoutBreadcrumbUserNews',
  data() {
    return {
      newsList: [],
      newOrderAudioLink: null,
      messageList: [],
    };
  },
  mounted() {
    this.getNotict();
    this.newOrderAudioLink = newOrderAudioLink;
    adminSocket.then((ws) => {
      ws.send({
        type: 'login',
        data: getCookies('token'),
      });
      let that = this;
      ws.$on('ADMIN_NEW_PUSH', function (data) {
        that.getNotict();
      });

      ws.$on('NEW_ORDER', function (data) {
        that.$notify.info({
          title: '新订单',
          message: '您有一个新的订单,ID为(' + data.order_id + '),请注意查看',
        });
        if (newOrderAudioLink) newOrderAudioLink.play();
        that.messageList.push({
          title: '新订单提醒',
          icon: 'md-bulb',
          iconColor: '#87d068',
          time: 0,
          read: 0,
        });
      });
      ws.$on('NEW_REFUND_ORDER', function (data) {
        that.$notify.info({
          title: '退款订单提醒',
          message: '您有一个订单申请退款,ID为(' + data.order_id + '),请注意查看',
        });
        if (newOrderAudioLink) newOrderAudioLink.play();
        that.messageList.push({
          title: '退款订单提醒',
          icon: 'md-information',
          iconColor: '#fe5c57',
          time: 0,
          read: 0,
        });
      });
      ws.$on('WITHDRAW', function (data) {
        // that.$Notice.warning({
        //   title: '提现提醒',
        //   duration: 8,
        //   desc: '有用户申请提现,编号为(' + data.id + '),请注意查看',
        // });
        that.$notify.info({
          title: '提现提醒',
          message: '有用户申请提现,编号为(' + data.id + '),请注意查看',
        });
        that.messageList.push({
          title: '退款订单提醒',
          icon: 'md-people',
          iconColor: '#f06292',
          time: 0,
          read: 0,
        });
      });
      ws.$on('STORE_STOCK', function (data) {
        that.$notify.info({
          title: '库存预警',
          message: '商品ID为(' + data.id + ')的库存不足啦,请注意查看~',
        });
        that.messageList.push({
          title: '库存预警',
          icon: 'md-information',
          iconColor: '#fe5c57',
          time: 0,
          read: 0,
        });
      });
      ws.$on('PAY_SMS_SUCCESS', function (data) {
        that.$notify.info({
          title: '短信充值成功',
          message: '恭喜您充值' + data.price + '元，获得' + data.number + '条短信',
        });
        that.messageList.push({
          title: '短信充值成功',
          icon: 'md-bulb',
          iconColor: '#87d068',
          time: 0,
          read: 0,
        });
      });
    });
  },
  filters: {
    // 1 待发货 2 库存报警  3评论回复  4提现申请
    msgType(type) {
      let typeName;
      switch (type) {
        case 1:
          typeName = '待发货订单提醒';
          break;
        case 2:
          typeName = '库存报警';
          break;
        case 3:
          typeName = '评论回复';
          break;
        case 4:
          typeName = '提现申请';
          break;
        default:
          typeName = '其它';
      }
      return typeName;
    },
  },
  methods: {
    // 全部已读点击
    onAllReadClick() {
      this.newsList = [];
      this.$emit('haveNews', !!this.newsList.length);
    },
    // 前往通知中心点击
    onGoToGiteeClick() {},
    getNotict() {
      jnoticeRequest()
        .then((res) => {
          this.newsList = res.data || [];
          this.$emit('haveNews', !!this.newsList.length);
        })
        .catch(() => {});
    },
    jumpUrl(path) {
      this.$router.push({
        path,
      });
    },
    icon(type) {
      return require(`@/assets/images/news-${type}.png`);
    },
  },
};
</script>

<style scoped lang="scss">
.layout-navbars-breadcrumb-user-news {
  width: 320px;
  padding: 8px 14px 14px;
  .head-box {
    display: flex;
    // border-bottom: 1px solid var(--prev-border-color-lighter);
    box-sizing: border-box;
    color: var(--prev-color-text-primary);
    justify-content: space-between;
    // height: 35px;
    align-items: center;
    .head-box-title {
      font-size: 13px;
      font-weight: 500;
      color: #333333;
      line-height: 13px;
    }
    .head-box-btn {
      color: var(--prev-color-primary);
      font-size: 13px;
      cursor: pointer;
      opacity: 0.8;
      font-weight: 400;
      line-height: 13px;
      &:hover {
        opacity: 1;
      }
    }
  }
  .content-box {
    font-size: 13px;
    .content-box-item {
      padding-top: 24px;
      cursor: pointer;
      display: flex;
      align-items: center;
      &:last-of-type {
        // padding-bottom: 12px;
      }
      .icon {
        width: 26px;
        height: 26px;
        margin-right: 10px;
      }
      .content-box-right {
      }
      .content-box-type {
        font-size: 13px;
        font-weight: 500;
        color: #333333;
        line-height: 13px;
      }
      .content-box-msg {
        margin-top: 6px;
        font-size: 13px;
        font-weight: 400;
        color: #666666;
        line-height: 13px;
      }
      .content-box-time {
        color: var(--prev-color-text-secondary);
      }
    }
    .content-box-empty {
      width: 292px;
      // height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      .content-box-empty-margin {
        text-align: center;
        font-size: 13px;
        color: #999999;
        i {
          color: var(--prev-color-primary);
          font-size: 60px;
        }
        .no-msg {
          width: 180px;
          height: 138px;
        }
      }
    }
  }
  .foot-box {
    height: 35px;
    color: var(--prev-color-primary);
    font-size: 13px;
    cursor: pointer;
    opacity: 0.8;
    display: flex;
    align-items: center;
    justify-content: center;
    border-top: 1px solid var(--prev-border-color-lighter);
    &:hover {
      opacity: 1;
    }
  }
  ::v-deep(.el-empty__description p) {
    font-size: 13px;
  }
}
</style>
