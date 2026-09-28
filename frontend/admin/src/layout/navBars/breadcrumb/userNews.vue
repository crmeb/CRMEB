<template>
  <div class="layout-navbars-breadcrumb-user-news">
    <div class="head-box">
      <div class="head-box-title">系统通知</div>
      <!-- <div class="head-box-btn" v-if="newsList.length > 0" v-db-click @click="onAllReadClick">全部已读</div> -->
    </div>
    <div class="content-box">
      <template v-if="newsList.length > 0">
        <div class="content-box-item" v-for="(v, k) in newsList" :key="k" v-db-click @click="jumpUrl(v.url)">
          <img class="icon" :src="icon(v.type)" alt="" />
          <div class="content-box-right">
            <div class="content-box-type">{{ msgType(v.type) }}</div>
            <div class="content-box-msg">
              {{ v.title }}
            </div>
          </div>

          <!-- <div class="content-box-time">{{ v.time }}</div> -->
        </div>
      </template>
      <div class="content-box-empty" v-else>
        <div class="content-box-empty-margin">
          <img class="no-msg" src="../../../assets/images/no-msg.png" alt="" />
          <div class="mt15">暂无通知</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ElNotification } from 'element-plus';
import __asset_newOrderAudioLink_0 from '@/assets/video/newOrderAudioLink.mp3';

let newOrderAudioLink = new Audio(__asset_newOrderAudioLink_0);
import { jnoticeRequest } from '@/api/common';
import { adminSocket } from '@/libs/socket';
import { getCookies } from '@/libs/util';

defineOptions({ name: 'layoutBreadcrumbUserNews' });

const emit = defineEmits(['haveNews']);

const router = useRouter();

const newsList = ref([]);
const messageList = ref([]);

// 通知类型图标（原 require 动态资源改为 import.meta.glob）
const newsIconModules = import.meta.glob('../../../assets/images/news-*.png', { eager: true });

onMounted(() => {
  getNotict();
  adminSocket.then((ws) => {
    ws.send({
      type: 'login',
      data: getCookies('token'),
    });
    ws.$on('ADMIN_NEW_PUSH', function (data) {
      getNotict();
    });

    ws.$on('NEW_ORDER', function (data) {
      ElNotification.info({
        title: '新订单',
        message: '您有一个新的订单,ID为(' + data.order_id + '),请注意查看',
      });
      if (newOrderAudioLink) newOrderAudioLink.play();
      messageList.value.push({
        title: '新订单提醒',
        icon: 'md-bulb',
        iconColor: '#87d068',
        time: 0,
        read: 0,
      });
    });
    ws.$on('NEW_REFUND_ORDER', function (data) {
      ElNotification.info({
        title: '退款订单提醒',
        message: '您有一个订单申请退款,ID为(' + data.order_id + '),请注意查看',
      });
      if (newOrderAudioLink) newOrderAudioLink.play();
      messageList.value.push({
        title: '退款订单提醒',
        icon: 'md-information',
        iconColor: '#fe5c57',
        time: 0,
        read: 0,
      });
    });
    ws.$on('WITHDRAW', function (data) {
      // ElNotification.warning({
      //   title: '提现提醒',
      //   duration: 8,
      //   desc: '有用户申请提现,编号为(' + data.id + '),请注意查看',
      // });
      ElNotification.info({
        title: '提现提醒',
        message: '有用户申请提现,编号为(' + data.id + '),请注意查看',
      });
      messageList.value.push({
        title: '退款订单提醒',
        icon: 'md-people',
        iconColor: '#f06292',
        time: 0,
        read: 0,
      });
    });
    ws.$on('STORE_STOCK', function (data) {
      ElNotification.info({
        title: '库存预警',
        message: '商品ID为(' + data.id + ')的库存不足啦,请注意查看~',
      });
      messageList.value.push({
        title: '库存预警',
        icon: 'md-information',
        iconColor: '#fe5c57',
        time: 0,
        read: 0,
      });
    });
    ws.$on('PAY_SMS_SUCCESS', function (data) {
      ElNotification.info({
        title: '短信充值成功',
        message: '恭喜您充值' + data.price + '元，获得' + data.number + '条短信',
      });
      messageList.value.push({
        title: '短信充值成功',
        icon: 'md-bulb',
        iconColor: '#87d068',
        time: 0,
        read: 0,
      });
    });
  });
});

// 1 待发货 2 库存报警  3评论回复  4提现申请（原 filters.msgType）
function msgType(type) {
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
}

// 全部已读点击
function onAllReadClick() {
  newsList.value = [];
  emit('haveNews', !!newsList.value.length);
}

// 前往通知中心点击
function onGoToGiteeClick() {}

function getNotict() {
  jnoticeRequest()
    .then((res) => {
      newsList.value = res.data || [];
      emit('haveNews', !!newsList.value.length);
    })
    .catch(() => {});
}

function jumpUrl(path) {
  if (!path) return;
  // 外链直接新窗口打开
  if (/^https?:\/\//.test(path)) {
    window.open(path, '_blank');
    return;
  }
  // 兼容在弹层等特殊渲染环境下 router 可能为 undefined 的情况（原 this.$root.$router 兜底在 Vue3 下不存在）
  if (router && typeof router.push === 'function') {
    router.push({ path });
  } else {
    // 兜底：直接跳转
    window.location.href = path;
  }
}

function icon(type) {
  const key = Object.keys(newsIconModules).find((k) => k.endsWith(`news-${type}.png`));
  return key ? newsIconModules[key].default : '';
}
</script>

<style scoped lang="scss">
.layout-navbars-breadcrumb-user-news {
  width: 100%;
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
      color: var(--prev-color-text-regular);
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
        color: var(--prev-color-text-regular);
        line-height: 13px;
      }
      .content-box-msg {
        margin-top: 6px;
        font-size: 13px;
        font-weight: 400;
        color: var(--prev-color-text-primary);
        line-height: 13px;
      }
      .content-box-time {
        color: var(--prev-color-text-secondary);
      }
    }
    .content-box-empty {
      width: 100%;
      // height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      .content-box-empty-margin {
        text-align: center;
        font-size: 13px;
        color: var(--prev-color-text-secondary);
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
  :deep(.el-empty__description p) {
    font-size: 13px;
  }
}
</style>
