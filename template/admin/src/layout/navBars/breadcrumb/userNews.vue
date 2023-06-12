<template>
  <div class="layout-navbars-breadcrumb-user-news">
    <div class="head-box">
      <div class="head-box-title">{{ $t('message.user.newTitle') }}</div>
      <div class="head-box-btn" v-if="newsList.length > 0" @click="onAllReadClick">{{ $t('message.user.newBtn') }}</div>
    </div>
    <div class="content-box">
      <template v-if="newsList.length > 0">
        <div class="content-box-item" v-for="(v, k) in newsList" :key="k">
          <div>{{ v.type | msgType }}</div>
          <div class="content-box-msg">
            {{ v.title }}
          </div>
          <!-- <div class="content-box-time">{{ v.time }}</div> -->
        </div>
      </template>
      <div class="content-box-empty" v-else>
        <div class="content-box-empty-margin">
          <i class="el-icon-s-promotion"></i>
          <div class="mt15">{{ $t('message.user.newDesc') }}</div>
        </div>
      </div>
    </div>
    <!-- <div class="foot-box" @click="onGoToGiteeClick" v-if="newsList.length > 0">{{ $t('message.user.newGo') }}</div> -->
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
        that.$Notice.info({
          title: '新订单',
          duration: 8,
          desc: '您有一个新的订单,ID为(' + data.order_id + '),请注意查看',
        });
        if (this.newOrderAudioLink) this.newOrderAudioLink.play();
        that.messageList.push({
          title: '新订单提醒',
          icon: 'md-bulb',
          iconColor: '#87d068',
          time: 0,
          read: 0,
        });
      });
      ws.$on('NEW_REFUND_ORDER', function (data) {
        that.$Notice.warning({
          title: '退款订单提醒',
          duration: 8,
          desc: '您有一个订单申请退款,ID为(' + data.order_id + '),请注意查看',
        });
        if (window.newOrderAudioLink) this.newOrderAudioLink.play();
        that.messageList.push({
          title: '退款订单提醒',
          icon: 'md-information',
          iconColor: '#fe5c57',
          time: 0,
          read: 0,
        });
      });
      ws.$on('WITHDRAW', function (data) {
        that.$Notice.warning({
          title: '提现提醒',
          duration: 8,
          desc: '有用户申请提现,编号为(' + data.id + '),请注意查看',
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
        that.$Notice.warning({
          title: '库存预警',
          duration: 8,
          desc: '商品ID为(' + data.id + ')的库存不足啦,请注意查看~',
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
        that.$Notice.info({
          title: '短信充值成功',
          duration: 8,
          desc: '恭喜您充值' + data.price + '元，获得' + data.number + '条短信',
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
          typeName = '库存报警';
          break;
        case 4:
          typeName = '库存报警';
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
    jumpUrl(url) {
      this.$router.push({ path: url });
    },
  },
};
</script>

<style scoped lang="scss">
.layout-navbars-breadcrumb-user-news {
  .head-box {
    display: flex;
    border-bottom: 1px solid var(--prev-border-color-lighter);
    box-sizing: border-box;
    color: var(--prev-color-text-primary);
    justify-content: space-between;
    height: 35px;
    align-items: center;
    .head-box-btn {
      color: var(--prev-color-primary);
      font-size: 13px;
      cursor: pointer;
      opacity: 0.8;
      &:hover {
        opacity: 1;
      }
    }
  }
  .content-box {
    font-size: 13px;
    .content-box-item {
      padding-top: 12px;
      &:last-of-type {
        padding-bottom: 12px;
      }
      .content-box-msg {
        color: var(--prev-color-text-secondary);
        margin-top: 5px;
        margin-bottom: 5px;
      }
      .content-box-time {
        color: var(--prev-color-text-secondary);
      }
    }
    .content-box-empty {
      height: 260px;
      display: flex;
      .content-box-empty-margin {
        margin: auto;
        text-align: center;
        i {
          font-size: 60px;
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
