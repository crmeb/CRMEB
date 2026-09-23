<template>
  <div class="chatList">
    <!-- 会话搜索：置于 tab 上方（原顶部 header 搜索框迁移至此） -->
    <div class="search-box">
      <el-input v-model="searchValue" placeholder="搜索用户名称" clearable @change="onSearch" />
    </div>
    <div class="tab-head">
      <div
        class="item"
        :class="{ active: item.key == hdTabCur }"
        v-for="(item, index) in hdTab"
        :key="index"
        v-db-click
        @click="changeTab(item)"
      >
        {{ item.title }}
        <!-- 仅「会话列表」tab 显示未读消息总数（用户+游客，已结束的会话不计入） -->
        <span class="item-num" v-if="tabUnread(item.key) > 0">{{
          tabUnread(item.key) > 99 ? '99+' : tabUnread(item.key)
        }}</span>
      </div>
    </div>
    <div class="scroll-box">
      <!-- vue-scroll 为 Vue2 专用库且未再全局注册，改用原生滚动容器（触底加载下一页） -->
      <div class="scroll-inner" v-if="userList.length > 0" @scroll="handleScroll">
        <div
          class="chat-item"
          v-for="(item, index) in userList"
          :key="index"
          :class="{ active: curId == item.id }"
          v-db-click
          @click="selectUser(item)"
        >
          <div class="avatar">
            <img v-lazy="item.wx_avatar" alt="" />
            <div class="status" :class="{ off: item.online == 0 }"></div>
          </div>
          <div class="user-info">
            <div class="hd">
              <span class="name line1">{{ item.nickname }}</span>
              <!-- 来源标签：区分注册用户与游客 -->
              <span class="label source" :class="item.is_tourist == 1 ? 'tourist' : 'user-tag'">{{
                item.is_tourist == 1 ? '游客' : '用户'
              }}</span>
              <template v-if="item.type == 2">
                <span class="label">小程序</span>
              </template>
              <template v-if="item.type == 3">
                <span class="label H5">H5</span>
              </template>
              <template v-if="item.type == 1">
                <span class="label wechat">公众号</span>
              </template>
              <template v-if="item.type == 0">
                <span class="label pc">PC端</span>
              </template>
            </div>
            <div class="bd line1">
              <template v-if="item.message_type <= 2">{{ item.message }}</template>
              <template v-if="item.message_type == 3">[图片]</template>
              <template v-if="item.message_type == 5">[商品]</template>
              <template v-if="item.message_type == 6">[订单]</template>
            </div>
          </div>
          <div class="right-box">
            <div class="time">{{ toDay(item.update_time) }}</div>
            <!-- 已结束的会话不再显示未读消息数 -->
            <div class="num" v-if="hdTabCur === 0 && item.mssage_num > 0">
              <el-badge :value="item.mssage_num">
                <a href="#" class="demo-badge"></a>
              </el-badge>
            </div>
          </div>
        </div>
      </div>
      <empty v-else :msg="hdTabCur ? '暂无结束会话' : '暂无会话'" status="1"></empty>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { ElNotification } from 'element-plus';
import { Socket } from '@/pages/plugin/kefu/libs/socket';
import dayjs from 'dayjs';
import { record, unreadNumApi } from '@/pages/plugin/kefu/api';
import empty from '../../components/empty';
import bus from '@/utils/bus';

defineOptions({ name: 'chatList' });

const props = defineProps({
  userOnline: {
    type: Object,
    default: function () {
      return {};
    },
  },
  newRecored: {
    type: Object,
    default: function () {
      return {};
    },
  },
});

const emit = defineEmits(['setDataId']);

const hdTabCur = ref(0);
// key 为会话状态 is_end：0=会话列表（进行中，用户与游客混排），1=会话结束
const hdTab = ref([
  {
    key: 0,
    title: '会话列表',
  },
  {
    key: 1,
    title: '会话结束',
  },
]);
const userList = ref([]);
// 未读消息总数（仅统计进行中的会话）：user=注册用户，tourist=游客
const unreadNum = ref({ user: 0, tourist: 0 });
const curId = ref('');
const page = ref(1);
const limit = ref(15);
const isScroll = ref(true);
// 请求进行中标记：触底滚动会高频触发 getList，避免上一页未返回时重复请求
const listLoading = ref(false);
const nickname = ref('');
const isSearch = ref(false);
// 会话搜索关键字（输入框双向绑定）
const searchValue = ref('');

// 原 filters.toDay
// 列表时间显示规则：今天只显示时间，今年内只显示日期，跨年补年份，避免时间列过宽导致换行
function toDay(value) {
  if (!value) return '';
  const day = dayjs.unix(value);
  const now = dayjs();
  if (day.isSame(now, 'day')) return day.format('HH:mm');
  if (day.isSame(now, 'year')) return day.format('M月D日');
  return day.format('YYYY年M月D日');
}

watch(
  () => props.userOnline,
  (nVal, oVal) => {
    if (nVal.hasOwnProperty('to_uid')) {
      userList.value.forEach((el, index, arr) => {
        if (el.to_uid == nVal.to_uid) {
          el.online = nVal.online;
          if (nVal.online == 1) {
            ElNotification.info({
              title: '上线通知',
              message: `${el.nickname}上线`,
            });
          } else {
            // 对方离线：会话自动归入「会话结束」，从当前「会话列表」移除（重新发消息会自动激活回来）
            ElNotification.info({
              title: '下线通知',
              message: `${el.nickname}已离线，会话已移入「会话结束」`,
            });
            if (hdTabCur.value === 0) arr.splice(index, 1);
          }
        }
      });
    }
  },
  { deep: true },
);

// 按昵称搜索会话：重置分页后重新拉取列表（回车或点击清空按钮触发）
function onSearch() {
  nickname.value = searchValue.value;
  page.value = 1;
  isScroll.value = true;
  // 清除在途请求守卫，保证重置后的首次拉取不被跳过
  listLoading.value = false;
  userList.value = [];
  getList();
}

let chatSocket = null;
let syncTimer = null;
let syncLoading = false;

function upsertRecord(recordData) {
  if (!recordData?.id || hdTabCur.value !== 0) return;
  const index = userList.value.findIndex((item) => String(item.id) === String(recordData.id));
  if (index !== -1) userList.value.splice(index, 1);
  userList.value.unshift(recordData);
}

function onTransfer(data) {
  const index = userList.value.findIndex((item) => String(item.id) === String(data.recored?.id));
  if (index === 0) {
    emit('setDataId', data.recored);
    data.recored.mssage_num = 0;
  }
  upsertRecord(data.recored);
  ElNotification.info({
    title: '您有一条转接消息！',
  });
  getUnreadNum();
}

function onMessageNumber(data) {
  upsertRecord(data.recored);
  getUnreadNum();
}

// WebSocket 登录完成或偶发漏推时，以列表接口同步第一页；正常情况下仍由消息推送即时插入
function syncLatestRecords() {
  if (document.hidden || syncLoading) return;
  syncLoading = true;
  record({
    nickname: nickname.value,
    page: 1,
    limit: limit.value,
    is_end: hdTabCur.value,
  })
    .then((res) => {
      if (hdTabCur.value === 0) {
        // 少于一页说明服务端已返回当前分组的完整集合，可直接替换并清掉已离线/已结束的残留项
        if (res.data.length < limit.value) {
          userList.value = res.data;
          if (curId.value && !res.data.some((item) => String(item.id) === String(curId.value))) {
            curId.value = res.data[0]?.id || '';
            emit('setDataId', res.data[0] || 0);
          }
          return;
        }
        [...res.data].reverse().forEach(upsertRecord);
      }
    })
    .finally(() => {
      syncLoading = false;
    });
}

function onSocketSuccess() {
  syncLatestRecords();
}

onMounted(() => {
  Socket.then((ws) => {
    chatSocket = ws;
    ws.$on('transfer', onTransfer);
    ws.$on('mssage_num', onMessageNumber);
    ws.$on('success', onSocketSuccess);
  });
  bus.on('change', onBusChange);
  // 右侧面板「结束会话」后刷新当前列表（已结束的会话从会话列表消失）
  bus.on('kefuChatEnded', onChatEnded);
  getList();
  getUnreadNum();
  // 长连接断线重连期间可能错过会话事件，定时同步只拉第一页作为低频兜底
  syncTimer = window.setInterval(syncLatestRecords, 5000);
});

// 结束会话后重置分页并重新拉取当前 tab 列表
function onChatEnded() {
  page.value = 1;
  isScroll.value = true;
  listLoading.value = false;
  userList.value = [];
  getList();
  getUnreadNum();
}

function onBusChange(data) {
  nickname.value = data;
}

// 组件卸载时移除 bus 监听（off 必须传与 on 相同的具名函数）
onBeforeUnmount(() => {
  if (chatSocket) {
    chatSocket.$off('transfer', onTransfer);
    chatSocket.$off('mssage_num', onMessageNumber);
    chatSocket.$off('success', onSocketSuccess);
  }
  if (syncTimer) window.clearInterval(syncTimer);
  bus.off('change', onBusChange);
  bus.off('kefuChatEnded', onChatEnded);
});

//切换
function changeTab(item) {
  if (hdTabCur.value == item.key) return;
  hdTabCur.value = item.key;
  isScroll.value = true;
  listLoading.value = false;
  page.value = 1;
  userList.value = [];
  getList();
  getUnreadNum();
}
// 拉取进行中会话的未读消息总数（tab 角标需展示汇总值，故由服务端按用户/游客分别汇总）
function getUnreadNum() {
  unreadNumApi()
    .then((res) => {
      unreadNum.value = res.data;
    })
    .catch(() => {});
}
// tab 角标取值：仅「会话列表」显示未读总数（用户+游客），「会话结束」不显示
function tabUnread(key) {
  if (key !== 0) return 0;
  return Number(unreadNum.value.user || 0) + Number(unreadNum.value.tourist || 0);
}
function getList() {
  if (!isScroll.value || listLoading.value) return;
  listLoading.value = true;
  record({
    nickname: nickname.value,
    page: page.value,
    limit: limit.value,
    // key 与 is_end 一一对应：0=会话列表（进行中），1=会话结束；用户与游客混排，无需按游客标识过滤
    is_end: hdTabCur.value,
  })
    .then((res) => {
      listLoading.value = false;
      if (res.data.length > 0) {
        res.data[0].mssage_num = 0;
        isScroll.value = res.data.length >= limit.value;

        const currentIds = new Set(userList.value.map((item) => String(item.id)));
        userList.value = userList.value.concat(res.data.filter((item) => !currentIds.has(String(item.id))));

        if (page.value == 1 && res.data.length > 0 && !isSearch.value) {
          curId.value = res.data[0].id;
          emit('setDataId', res.data[0]);
        }
        page.value++;
      } else {
        emit('setDataId', 0);
      }
    })
    .catch(() => {
      listLoading.value = false;
    });
}
function chartReachBottom() {
  getList();
}
// 选择用户
function selectUser(item) {
  if (curId.value == item.id) return;
  item.mssage_num = 0;
  curId.value = item.id;
  emit('setDataId', item);
}
// 原生滚动触底：距底部不足 10px 时加载下一页
function handleScroll(e) {
  const el = e.target;
  if (el.scrollTop + el.clientHeight >= el.scrollHeight - 10) {
    getList();
  }
}
</script>

<style lang="scss" scoped>
.chatList {
  display: flex;
  flex-direction: column;
  width: 320px;
  height: 742px;
  border-right: 1px solid #ececec;
  // 会话搜索框：与 header 底边、左右边缘留出间距
  .search-box {
    flex-shrink: 0;
    padding: 12px 16px 6px;
  }
  .tab-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 50px;
    flex-shrink: 0;
    padding: 0 52px;
    font-size: 14px;
    color: #000000;
    .item {
      position: relative;
      cursor: pointer;
      &:after {
        display: none;
        content: ' ';
        position: absolute;
        left: 50%;
        bottom: -15px;
        transform: translateX(-50%);
        height: 2px;
        width: 100%;
        background: var(--prev-color-primary);
      }
      &.active {
        color: var(--prev-color-primary);
        &:after {
          display: block;
        }
      }
      // 未读消息总数角标
      .item-num {
        position: absolute;
        top: -8px;
        right: -18px;
        min-width: 16px;
        height: 16px;
        box-sizing: border-box;
        padding: 0 4px;
        border-radius: 8px;
        background: var(--prev-color-danger);
        color: #fff;
        font-size: 12px;
        line-height: 16px;
        text-align: center;
      }
    }
  }
  .scroll-box {
    flex: 1;
    height: 500px;
    overflow: hidden;
  }
  // 会话列表滚动容器：撑满 scroll-box，滚动触底由 handleScroll 加载下一页
  .scroll-inner {
    height: 100%;
    overflow-y: auto;
  }
  .chat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 10px;
    height: 74px;
    box-sizing: border-box;
    border-left: 3px solid transparent;
    cursor: pointer;
    &.active {
      background: #eff0f1;
      border-left: 3px solid var(--prev-color-primary);
    }
    .avatar {
      position: relative;
      width: 40px;
      height: 40px;
      img {
        display: block;
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }
      .status {
        position: absolute;
        right: 3px;
        bottom: 0;
        width: 8px;
        height: 8px;
        background: #48d452;
        border: 1px solid #fff;
        border-radius: 50%;
        &.off {
          background: #999999;
        }
      }
    }
    .user-info {
      width: 185px;
      margin-left: 12px;
      margin-top: 5px;
      font-size: 16px;
      .hd {
        display: flex;
        align-items: center;
        color: rgba(0, 0, 0, 0.65);
        .name {
          // 昵称占满剩余空间，被标签挤压时收缩出省略号
          flex: 1;
          min-width: 0;
        }
        .label {
          margin-left: 5px;
          // 标签禁止收缩与折行（避免「PC端」等标签文字竖排折行）
          flex-shrink: 0;
          white-space: nowrap;
          color: #3875ea;
          font-size: 12px;
          background: #d8e5ff;
          border-radius: 2px;
          padding: 1px 5px;
          // 来源标签：区分注册用户与游客（用户=主题色，游客=灰色）
          &.source {
            &.user-tag {
              color: var(--prev-color-primary);
              background: var(--prev-color-primary-light-9);
            }
            &.tourist {
              color: var(--prev-color-info);
              background: rgba(144, 147, 153, 0.16);
            }
          }
          &.H5 {
            background: #faf1d0;
            color: #dc9a04;
          }
          &.wechat {
            background: rgba(64, 194, 73, 0.16);
            color: #40c249;
          }
          &.pc {
            background: rgba(100, 64, 194, 0.16);
            color: #6440c2;
          }
        }
      }
      .bd {
        margin-top: 3px;
        font-size: 12px;
        color: #8e959e;
      }
    }
    .right-box {
      position: relative;
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      color: #8e959e;
      .time {
        font-size: 12px;
        white-space: nowrap;
      }
      .num {
        margin-right: 12px;
      }
    }
  }
}
.chart-scroll {
  margin-top: -10px;
}
</style>
