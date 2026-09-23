<template>
  <div class="right-wrapper">
    <div class="user-wrapper" v-if="activeUserInfo">
      <!-- 用户卡片：白底横排，头像 + 昵称/渠道 -->
      <div class="user-card">
        <img class="avatar" v-lazy="activeUserInfo.avatar" alt="" />
        <div class="user-meta">
          <div class="name line1">{{ activeUserInfo.nickname }}</div>
          <span v-if="channel" class="channel-tag" :class="channel.class">{{ channel.text }}</span>
        </div>
      </div>
      <!-- 基础信息 -->
      <div class="info-list">
        <div class="info-item">
          <span class="info-label">手机号</span>
          <span class="info-value" :class="{ 'is-empty': !activeUserInfo.phone }">{{
            activeUserInfo.phone || '暂无'
          }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">用户等级</span>
          <span class="info-value" :class="{ 'is-empty': !activeUserInfo.level_name }">{{
            activeUserInfo.level_name || '暂无'
          }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">推荐人</span>
          <span class="info-value" :class="{ 'is-empty': !activeUserInfo.spread_name }">{{
            activeUserInfo.spread_name || '暂无'
          }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">用户类型</span>
          <span class="info-value">{{ typeFilters(activeUserInfo.user_type) }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">余额</span>
          <span class="info-value">{{ activeUserInfo.now_money }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">推广员</span>
          <span class="info-value">{{ activeUserInfo.is_promoter ? '是' : '否' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">生日</span>
          <span class="info-value" :class="{ 'is-empty': !activeUserInfo.birthday }">{{
            getDay(activeUserInfo.birthday) || '暂无'
          }}</span>
        </div>
      </div>
      <!-- 可编辑块：分组 -->
      <div class="edit-block" v-db-click @click.stop="isUserGroup = true">
        <div class="edit-block-head">
          <span class="info-label">分组</span>
          <i class="el-icon-arrow-right arrow-icon"></i>
        </div>
        <div class="edit-block-body">
          <span class="tag" v-if="activeUserInfo.group_name">{{ activeUserInfo.group_name }}</span>
          <span class="empty-text" v-else>暂无分组，点击选择</span>
        </div>
      </div>
      <!-- 可编辑块：用户标签 -->
      <div class="edit-block" v-db-click @click.stop="isUserLabel = true">
        <div class="edit-block-head">
          <span class="info-label">用户标签</span>
          <i class="el-icon-arrow-right arrow-icon"></i>
        </div>
        <div class="edit-block-body">
          <template v-if="activeUserInfo.labelNames && activeUserInfo.labelNames.length">
            <span class="tag" v-for="(item, index) in activeUserInfo.labelNames" :key="index">{{ item }}</span>
          </template>
          <span class="empty-text" v-else>暂无标签，点击选择</span>
        </div>
      </div>
    </div>
    <empty v-else status="2" msg="暂无用户信息"></empty>

    <!-- 结束会话：进行中的会话（用户或游客）可移入「会话结束」列表 -->
    <div class="end-chat" v-if="canEndChat">
      <el-button type="danger" plain class="end-btn" :loading="ending" v-db-click @click="handleEndChat">
        结束会话
      </el-button>
    </div>

    <el-dialog title="选择用户标签" v-model="isUserLabel" width="470px" class="label-box" :show-close="true">
      <template #header
        ><p class="label-head">
          <span>选择用户标签</span>
        </p></template
      >
      <userLabel v-if="isUserLabel" @close="deliveryClose" :uid="uid" @editLabel="editLabel"></userLabel>
    </el-dialog>

    <el-dialog v-model="isUserGroup" title="选择分组" width="470px" class="label-box" :show-close="true">
      <template #header
        ><p class="label-head">
          <span>选择分组</span>
        </p></template
      >
      <userGroup
        v-if="isUserGroup"
        :groupId="activeUserInfo.group_id"
        :labelList="userGroupList"
        @close="deliveryClose"
        :uid="uid"
        @editUserLabel="editUserLabel"
      ></userGroup>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import userLabel from './userLabel';
import userGroup from './userGroup';
import { userInfo, userGroupApi, putGroupApi, endChatApi } from '@/pages/plugin/kefu/api';
import empty from '../../components/empty';
import dayjs from 'dayjs';
import bus from '@/utils/bus';

defineOptions({ name: 'rightMenu' });

const props = defineProps({
  isTourist: {
    type: [String, Number],
    default: 0,
  },
  status: {
    type: [String, Number],
    default: '',
  },
  uid: {
    type: [String, Number],
    default: '',
  },
  webType: {
    type: [String, Number],
    default: '',
  },
  // 当前选中会话的结束状态：1=已结束（不显示结束按钮）
  isEnd: {
    type: [String, Number],
    default: 0,
  },
});

// 原 filters.getDay
function getDay(val) {
  if (val) {
    return dayjs.unix(val).format('YYYY年M月D日');
  }
}
// 原 filters.typeFilters
function typeFilters(value) {
  const statusMap = {
    h5: 'H5',
    wechat: '公众号',
    routine: '小程序',
    pc: 'PC',
  };
  return statusMap[value];
}

const userGroupList = ref([]);
const activeUserInfo = ref('');
const isUserGroup = ref(false);
const isUserLabel = ref(false);
const ending = ref(false); // 结束会话请求中

// 仅进行中的会话显示「结束会话」按钮（已结束的会话不再重复结束）
const canEndChat = computed(() => !!props.uid && Number(props.isEnd) !== 1);

// 渠道徽标：webType 0=PC 1=公众号 2=小程序 3=H5
const channelMap = {
  0: { text: 'PC端', class: 'pc' },
  1: { text: '公众号', class: 'wechat' },
  2: { text: '小程序', class: 'routine' },
  3: { text: 'H5', class: 'h5' },
};
const channel = computed(() => channelMap[props.webType] || null);

// 结束会话：成功后广播给左侧列表刷新（已结束的会话从「会话列表」消失）
function handleEndChat() {
  ending.value = true;
  endChatApi(props.uid)
    .then((res) => {
      ElMessage.success(res.msg || '会话已结束');
      bus.emit('kefuChatEnded');
    })
    .finally(() => {
      ending.value = false;
    });
}

watch(
  () => props.uid,
  (nVal, oVal) => {
    // 切换 tab 后列表为空时 uid 会变为空值，此时不能请求用户详情，否则接口 404
    if (nVal && nVal != oVal && props.isTourist == 0) {
      getUserInfo();
      getUserGroup();
    } else if (!nVal) {
      // 未选中任何会话（如结束会话后列表刷新）时清空右侧用户信息
      activeUserInfo.value = '';
    }
  },
);
watch(
  () => props.isTourist,
  (nVal) => {
    if (nVal == 1) {
      activeUserInfo.value = '';
    }
  },
);

onMounted(() => {
  if (props.uid && props.isTourist == 0) {
    getUserInfo();
    getUserGroup();
  }
});

function getUserGroup() {
  userGroupApi().then((res) => {
    userGroupList.value = res.data;
  });
}
function getUserInfo() {
  userInfo(props.uid)
    .then((res) => {
      activeUserInfo.value = res.data;
    })
    .catch(() => {
      activeUserInfo.value = '';
    });
}
function editLabel() {
  isUserLabel.value = false;
  getUserInfo();
}
function editUserLabel(id) {
  isUserGroup.value = false;
  putGroupApi(props.uid, id).then((res) => {
    ElMessage.success(res.msg);
    getUserInfo();
  });
}
function deliveryClose() {
  isUserLabel.value = false;
  isUserGroup.value = false;
}
</script>

<style lang="scss" scoped>
.right-wrapper {
  width: 280px;
}
// 结束会话按钮：通栏置于面板底部
.end-chat {
  padding: 12px 0 4px;
  border-top: 1px solid var(--prev-border-color-lighter);
  .end-btn {
    width: 100%;
  }
}
.user-wrapper {
  padding: 0 8px;
}

/* 用户卡片：白底横排，轻盈克制 */
.user-card {
  display: flex;
  align-items: center;
  padding: 16px 4px 14px;
  border-bottom: 1px solid var(--prev-border-color-extra-light);

  .avatar {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    object-fit: cover;
    background: var(--prev-bg-color);
    border-radius: 50%;
  }

  .user-meta {
    min-width: 0;
    margin-left: 12px;
  }

  .name {
    font-size: 14px;
    font-weight: 600;
    color: var(--prev-color-text-regular);
  }
}

/* 渠道徽标：语义浅底胶囊 */
.channel-tag {
  display: inline-block;
  margin-top: 4px;
  padding: 0 8px;
  font-size: 12px;
  line-height: 18px;
  border-radius: 9px;

  &.h5 {
    color: #20b469;
    background: #e8f7ef;
  }

  &.routine {
    color: #2b9cff;
    background: #e8f4ff;
  }

  &.wechat {
    color: #07c160;
    background: #e8f7ef;
  }

  &.pc {
    color: #6440c2;
    background: #f0eaff;
  }
}

/* 基础信息行 */
.info-list {
  padding: 2px 0 6px;
}

.info-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 9px 0;
  font-size: 12px;
  border-bottom: 1px solid var(--prev-border-color-extra-light);

  &:last-child {
    border-bottom: none;
  }
}

.info-label {
  flex-shrink: 0;
  color: var(--prev-color-text-secondary);
}

.info-value {
  font-weight: 500;
  color: var(--prev-color-text-regular);
  text-align: right;
  word-break: break-all;

  /* 空值弱化显示 */
  &.is-empty {
    font-weight: 400;
    color: var(--prev-color-text-placeholder);
  }
}

/* 可编辑块（分组/标签）：浅底圆角卡，整块可点击 */
.edit-block {
  padding: 10px 12px;
  margin-bottom: 8px;
  cursor: pointer;
  background: var(--prev-bg-color);
  border-radius: 6px;
  transition: background-color 0.2s;

  &:hover {
    background: #eef1f6;

    .arrow-icon {
      color: var(--prev-color-primary);
    }
  }
}

.edit-block-head {
  display: flex;
  align-items: center;
  justify-content: space-between;

  .arrow-icon {
    font-size: 14px;
    color: var(--prev-color-text-placeholder);
    transition: color 0.2s;
  }
}

.edit-block-body {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
}

.tag {
  padding: 1px 10px;
  font-size: 12px;
  line-height: 18px;
  color: #6440c2;
  background: #fff;
  border: 1px solid #e2d9f8;
  border-radius: 4px;
}

.empty-text {
  font-size: 12px;
  color: var(--prev-color-text-placeholder);
}

.label-head {
  font-size: 16px;
  color: var(--prev-color-text-regular);
}
</style>
