<template>
  <div class="chat-list">
    <div class="head-box">
      <div class="hd">
        <div class="left-wrappers">
          <img v-lazy="kefuInfo.avatar" @error="onAvatarError" />
          <div class="info" v-db-click @click="isOnLine = !isOnLine">
            <div>{{ kefuInfo.nickname }}</div>
            <div class="status">
              <span class="doc" :class="{ off: !kefuInfo.online }"></span>
              <span>{{ kefuInfo.online ? '在线' : '离线' }}</span>
            </div>
          </div>
          <div class="down-wrapper" v-show="isOnLine">
            <div class="item" v-db-click @click="changOnline(1)">
              <span class="dot green"></span>在线
              <span class="iconfont iconduihao" v-if="kefuInfo.online"></span>
            </div>
            <div class="item" v-db-click @click="changOnline(0)">
              <span class="dot"></span>离线
              <span class="iconfont iconduihao" v-if="!kefuInfo.online"></span>
            </div>
          </div>
        </div>
        <div class="right-wrapper" v-db-click @click="outLogin">
          <div class="icon-box"><span class="iconfont icontuichu"></span></div>

          <div style="margin-left: 5px">退出登录</div>
        </div>
      </div>
      <div class="tab-box">
        <div
          class="tab-item"
          :class="{ on: tabCur == item.key }"
          v-for="(item, index) in tabList"
          :key="index"
          v-db-click
          @click="changeClass(item)"
        >
          {{ item.title }}
        </div>
      </div>
      <div class="search-box">
        <el-input v-model="searchTxt" placeholder="搜索用户名称" @change="bindSearch" />
      </div>
    </div>
    <div class="list-box" v-if="list.length > 0">
      <!-- vue-scroll 为 Vue2 专用库且未再全局注册，改用原生滚动容器（触底加载下一页） -->
      <div class="list-scroll" @scroll="handleListScroll">
        <div class="item" v-for="(item, index) in list" :key="index" v-db-click @click="goPage(item)">
          <div class="left-wrappers">
            <div class="img-box">
              <!-- 列表接口的头像字段是 wx_avatar（avatar 可能为空导致碎图），加载失败露出灰色占位底 -->
              <img v-lazy="item.wx_avatar || item.avatar" @error="onAvatarError" />
              <div class="online" :class="{ on: item.online }"></div>
            </div>
            <div class="info">
              <div class="title">
                <span class="line1">{{ item.nickname }}</span>
                <!-- 来源标签：区分注册用户与游客 -->
                <span class="label source" :class="item.is_tourist == 1 ? 'tourist' : 'user-tag'">{{
                  item.is_tourist == 1 ? '游客' : '用户'
                }}</span>
                <template v-if="item.type == 2">
                  <span class="label">小程序</span>
                </template>
                <template v-if="item.type == 3">
                  <span class="label h5">H5</span>
                </template>
                <template v-if="item.type == 1">
                  <span class="label wx">公众号</span>
                </template>
                <template v-if="item.type == 0">
                  <span class="label pc">PC端</span>
                </template>
              </div>
              <div class="msg line1" v-if="item.message_type == 1">{{ item.message }}</div>
              <div class="msg" v-if="item.message_type == 2">[表情]</div>
              <div class="msg" v-if="item.message_type == 3">[图片]</div>
              <div class="msg" v-if="item.message_type == 5">[商品]</div>
              <div class="msg" v-if="item.message_type == 6">[订单]</div>
            </div>
          </div>
          <div class="right-wrapper">
            <div class="time">{{ toDay(item.update_time) }}</div>
            <!-- 已结束的会话不再显示未读消息数 -->
            <span class="num" v-if="tabCur === 0 && item.mssage_num > 0">{{ item.mssage_num }}</span>
          </div>
        </div>
      </div>
    </div>
    <empty v-else status="3" :msg="tabCur ? '暂无结束会话' : '暂无会话'"></empty>
  </div>
</template>

<script setup>
import __asset_notice_0 from '@/assets/video/notice.mp3';

const mp3 = new Audio(__asset_notice_0);
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage, ElMessageBox, ElNotification } from 'element-plus';
import { Socket } from '@/pages/plugin/kefu/libs/socket';
import { record } from '@/pages/plugin/kefu/api';
import { serviceInfo } from '@/pages/plugin/kefu/api_mobile';
import { getCookies } from '@/libs/util';
import dayjs from 'dayjs';
import empty from '../components/empty';
import { useKefuStore } from '@/pages/plugin/kefu/store';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'chat_list.vue' });

const router = useRouter();
const kefuStore = useKefuStore();

// PC 端直接访问手机版客服页面时自动跳转 PC 工作台（与客服登录页/工作台入口的设备分流口径一致）
if (!useMediaStore().isMobile) {
  router.replace(Setting.routePre + '/kefu/pc_list');
}

// 列表请求进行中标记：触底滚动高频触发，避免重复请求同一页
const listLoading = ref(false);
const list = ref([]);
const page = ref(1);
const limit = ref(15);
const isScroll = ref(true);
const searchTxt = ref('');
const isOpen = ref(true);
const kefuInfo = ref({});
const isOnLine = ref(false);
const tabCur = ref(0);
// key 为会话状态 is_end：0=会话列表（进行中，用户与游客混排），1=会话结束
const tabList = ref([
  {
    key: 0,
    title: '会话列表',
  },
  {
    key: 1,
    title: '会话结束',
  },
]);
const wsLogin = ref(JSON.parse(sessionStorage.getItem('wsLogin')));

// 列表时间显示规则（修复原实现忽略传入时间的 bug）：今天只显示时间，今年内只显示日期，跨年补年份
function toDay(value) {
  if (!value) return '';
  const day = dayjs.unix(value);
  const now = dayjs();
  if (day.isSame(now, 'day')) return day.format('HH:mm');
  if (day.isSame(now, 'year')) return day.format('M月D日');
  return day.format('YYYY年M月D日');
}

// created
Promise.all([getKefuInfo(), getList()]);

onMounted(() => {
  // 监听页面刷新
  window.addEventListener('beforeunload', onBeforeUnload);
  // 获取是否登录的key
  wsLogin.value = JSON.parse(sessionStorage.getItem('wsLogin'));
  let token = getCookies('kefu_token');
  Socket.then((ws) => {
    if (!wsLogin.value && token) {
      ws.send({
        type: 'kefu_login',
        data: getCookies('kefu_token'),
      });
    }
    //用户转接：转接进入的均为进行中的会话，只在「会话列表」tab 插入
    ws.$on('transfer', (data) => {
      if (data.recored.id && tabCur.value === 0) {
        let status = false;
        for (let i = 0; i < list.value.length; i++) {
          if (data.recored.id == list.value[i].id) {
            status = true;
            list.value[i] = data.recored;
          }
        }
        if (!status) {
          list.value.unshift(data.recored);
        }
      }
    });
    ws.$on('mssage_num', (data) => {
      if (data.num > 0) {
        mp3.play();
      }
      if (data.recored.id) {
        let status = false;
        list.value.forEach((el, index, arr) => {
          if (data.recored.id == el.id) {
            status = true;
            // 新消息会把会话重新激活（is_end=0），只在「会话列表」tab 置顶
            if (tabCur.value === 0) {
              let oldVal = data.recored;
              arr.splice(index, 1);
              arr.unshift(oldVal);
            }
          }
        });
        if (!status && tabCur.value === 0) {
          list.value.unshift(data.recored);
        }
        // 当前在「会话结束」tab 时不展示新消息，弹通知提醒（文案按消息来源区分用户/游客）
        if (tabCur.value === 1) {
          ElNotification.info({
            title: data.recored.is_tourist == 1 ? '游客发来消息啦！' : '用户发来消息啦！',
          });
        }
      }
    });
    // ws登录成功
    ws.$on('success', (data) => {
      sessionStorage.setItem('wsLogin', true);
    });
  });
});

function onBeforeUnload(e) {
  sessionStorage.setItem('wsLogin', false);
}

onBeforeUnmount(() => {});

// 头像加载失败时隐藏图片，露出 .img-box 的灰色圆形占位底
function onAvatarError(e) {
  e.target.style.visibility = 'hidden';
}

// 列表切换
function changeClass(item) {
  if (tabCur.value == item.key) return;
  tabCur.value = item.key;
  page.value = 1;
  list.value = [];
  isScroll.value = true;
  // 清除在途请求守卫，保证重置后的首次拉取不被跳过
  listLoading.value = false;
  getList();
}
// 客服上下线
function changOnline(key) {
  kefuInfo.value.online = key;
  isOnLine.value = false;
  Socket.then((ws) => {
    ws.send({
      type: 'online',
      data: {
        online: key,
      },
    });
  });
}
// 客服详细信息
function getKefuInfo() {
  serviceInfo().then((res) => {
    kefuInfo.value = res.data;
    window.document.title = `${res.data.site_name} - 消息列表`;
  });
}
function getList() {
  if (!isScroll.value || listLoading.value) return;
  listLoading.value = true;
  record({
    nickname: searchTxt.value,
    page: page.value,
    limit: limit.value,
    // key 与 is_end 一一对应：0=会话列表（进行中），1=会话结束；用户与游客混排，无需按游客标识过滤
    is_end: tabCur.value,
  })
    .then((res) => {
      listLoading.value = false;
      isScroll.value = res.data.length >= limit.value;
      list.value = list.value.concat(res.data);
      page.value++;
    })
    .catch(() => {
      listLoading.value = false;
    });
}
// 客服退出
function outLogin() {
  ElMessageBox({
    title: '退出登录确认',
    message: '您确定退出登录当前账户吗？打开的标签页和个人设置将会保存。',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    iconClass: 'el-icon-warning',
    confirmButtonClass: 'btn-custom-cancel',
  })
    .then(() => {
      kefuStore.logoutKefu({
        confirm: false,
      });
    })
    .catch(() => {});
}
// 搜索
function bindSearch(e) {
  page.value = 1;
  list.value = [];
  isScroll.value = true;
  listLoading.value = false;
  getList();
}
// 进入对话
function goPage(item) {
  router.push({
    path: 'mobile_chat',
    query: {
      toUid: item.to_uid,
      nickname: item.nickname,
      // 游客标识由会话项自身携带：会话列表用户与游客混排，与 tab 无关
      is_tourist: item.is_tourist == 1 ? 1 : 0,
    },
  });
}
// 原生滚动触底：距底部不足 10px 时加载下一页
function handleListScroll(e) {
  const el = e.target;
  if (el.scrollTop + el.clientHeight >= el.scrollHeight - 10) {
    getList();
  }
}
</script>
<style>
html,
body {
  font-size: 50px;
}
</style>
<style lang="scss" scoped>
.chat-list {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  padding-bottom: 0.15rem;
  background: #fff;
  .head-box {
    .hd {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 1rem;
      padding: 0 0.3rem;
      background: linear-gradient(90deg, #3875ea 0%, #1890fc 100%);
      .left-wrappers {
        position: relative;
        display: flex;
        align-items: center;
        color: #fff;
        font-size: 0.26rem;

        img {
          width: 0.58rem;
          height: 0.58rem;
          border-radius: 50%;
          // 头像加载失败时露出灰色圆形占位底
          background: #eef1f6;
          object-fit: cover;
        }
        .info {
          margin-left: 0.12rem;
          .status {
            display: flex;
            align-items: center;
            font-size: 0.2rem;
            .doc {
              width: 0.14rem;
              height: 0.14rem;
              margin-right: 0.1rem;
              background-color: #27f2cb;
              border-radius: 50%;
              margin-top: 0.04rem;
              &.off {
                background: #919191;
              }
            }
          }
        }
        .down-wrapper {
          z-index: 50;
          position: absolute;
          left: 0;
          bottom: -1.9rem;
          width: 2.14rem;
          background: #434343;
          border-radius: 0.1rem;
          .item {
            display: flex;
            align-items: center;
            height: 0.8rem;
            padding-left: 0.3rem;
            border-bottom: 1px solid rgba(240, 241, 242, 0.16);
            font-size: 0.28rem;
            &:last-child {
              border-bottom: none;
            }
            .dot {
              width: 0.12rem;
              height: 0.12rem;
              margin-right: 0.16rem;
              border-radius: 50%;
              background: linear-gradient(180deg, #bcbcbc 0%, #919191 100%);
              &.green {
                background: linear-gradient(143deg, #27f2cb 0%, #14e3b4 100%);
              }
            }
            .iconfont {
              margin-left: 0.36rem;
              color: #b9b9b9;
              font-size: 0.18rem;
            }
          }
        }
      }
      .right-wrapper {
        display: flex;
        align-items: center;
        color: #fff;
        font-size: 0.24rem;
      }
    }
    .search-box {
      padding: 0 0.3rem 0.2rem;
      border-bottom: 1px solid #eceff8;
      // Element Plus 输入框：灰色圆角底、去默认边框阴影（原 ivu-input 类名为 iView 残留，样式从未生效）
      :deep(.el-input__wrapper) {
        background: #f5f6f9;
        border-radius: 0.39rem;
        box-shadow: none;
      }
      :deep(.el-input__inner) {
        height: 0.68rem;
        font-size: 0.28rem;
        text-align: center;
      }
    }
    .tab-box {
      display: flex;
      padding: 0.2rem 0;
      .tab-item {
        flex: 1;
        height: 100%;
        line-height: 0.6rem;
        text-align: center;
        font-size: 0.3rem;
        &.on {
          color: #3875ea;
        }
        &:first-child {
          border-right: 1px solid #ddd;
        }
      }
    }
  }
  .list-box {
    flex: 1;
    overflow: hidden;
    // 会话列表滚动容器（替代未注册的 vue-scroll）
    .list-scroll {
      height: 100%;
      overflow-y: auto;
    }
    .item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.2rem 0.3rem;
      // 相邻会话项之间的细分隔线
      & + .item {
        border-top: 1px solid #f2f3f7;
      }
      .left-wrappers {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        .img-box {
          flex: none;
          width: 0.9rem;
          height: 0.9rem;
          position: relative;
          // 头像加载失败（@error 隐藏图片）时露出灰色圆形占位底
          // 不加 overflow:hidden：会裁掉贴边悬浮的在线状态点（圆形裁剪由 border-radius + img 圆角承担）
          background: #eef1f6;
          border-radius: 50%;
        }
        .online {
          position: absolute;
          right: 0;
          bottom: 0.02rem;
          width: 0.16rem;
          height: 0.16rem;
          background: linear-gradient(143deg, #bcbcbc 0%, #919191 100%);
          border-radius: 50%;
          border: 1px solid #ffffff;
          box-sizing: border-box;
          &.on {
            background: linear-gradient(143deg, #27f2cb 0%, #14e3b4 100%);
          }
        }
        img {
          display: block;
          width: 0.9rem;
          height: 0.9rem;
          border-radius: 50%;
          object-fit: cover;
        }
        .info {
          flex: 1;
          min-width: 0;
          margin-left: 0.2rem;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          .title {
            display: flex;
            align-items: center;
            min-width: 0;
            .line1 {
              min-width: 0;
            }
            .label {
              flex-shrink: 0;
              // 标签不随空间挤压折行（原「游客/PC端」被压成竖排两行）
              white-space: nowrap;
              margin-left: 0.12rem;
              font-size: 0.2rem;
              padding: 0.03rem 0.1rem;
              background: rgba(56, 117, 234, 0.14);
              color: #3875ea;
              border-radius: 0.04rem;
              &.h5 {
                background: rgba(255, 162, 0, 0.18);
                color: #d08800;
              }
              &.wx {
                background: rgba(0, 186, 100, 0.14);
                color: #00a219;
              }
              &.pc {
                background: rgba(133, 64, 227, 0.14);
                color: #820adb;
              }
            }
          }
          .msg {
            margin-top: 0.08rem;
            font-size: 0.24rem;
            color: #9f9f9f;
          }
        }
      }
      .right-wrapper {
        flex: none;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        margin-left: 0.16rem;
        color: #9f9f9f;
        font-size: 0.22rem;
        .num {
          display: inline-block;
          min-width: 0.32rem;
          margin-top: 0.1rem;
          padding: 0 0.08rem;
          background-color: #f74c31;
          color: #fff;
          border-radius: 0.16rem;
          font-size: 0.2rem;
          line-height: 0.32rem;
          text-align: center;
          box-sizing: border-box;
        }
      }
    }
  }
}
</style>
