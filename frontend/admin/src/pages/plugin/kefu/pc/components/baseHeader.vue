<template>
  <div class="base-header">
    <div class="left-header-wrapper">
      <div class="user_info">
        <img v-lazy="kefuInfo.avatar" alt="" />
        <span>{{ kefuInfo.nickname }}</span>
        <div class="status-box">
          <div class="status" :class="online ? 'on' : 'off'" v-db-click @click.stop="setOnline">
            <span class="dot"></span>
            {{ online ? '在线' : '下线' }}
          </div>

          <div class="online-down" v-show="isOnline">
            <div class="item" v-db-click @click.stop="changeOnline(1)">
              <span class="iconfont iconduihao" v-if="online"></span><i class="green"></i>在线
            </div>
            <div class="item" v-db-click @click.stop="changeOnline(0)">
              <span class="iconfont iconduihao" v-if="!online"></span><i></i>下线
            </div>
            <div class="item logout" v-db-click @click.stop="outLogin">
              <span class="iconfont icontuichu"></span>退出登录
            </div>
          </div>
        </div>
      </div>
      <!-- 提示音开关：关闭后新消息不再响铃（标题滚动提醒不受影响） -->
      <div class="sound-btn" v-db-click :title="soundOff ? '开启提示音' : '关闭提示音'" @click.stop="toggleSound">
        <span class="iconfont" :class="soundOff ? 'iconshengyinjingyinxianxing' : 'iconshengyinyinliang'"></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessageBox } from 'element-plus';
import { useKefuStore } from '@/pages/plugin/kefu/store';

defineOptions({ name: 'baseHeader' });

const props = defineProps({
  kefuInfo: {
    type: Object,
    default: function () {
      return {};
    },
  },
  online: {
    type: [Boolean, Number],
    default: true,
  },
});

const emit = defineEmits(['setOnline']);

const kefuStore = useKefuStore();

const isOnline = ref(false);
// 提示音开关状态（localStorage 持久化，刷新后仍生效）
const soundOff = ref(window.localStorage.getItem('kefu_sound_off') === '1');

onMounted(() => {
  document.addEventListener('click', () => {
    isOnline.value = false;
  });
});

function setOnline() {
  isOnline.value = !isOnline.value;
}
function changeOnline(type) {
  emit('setOnline', type);
  isOnline.value = false;
}
// 切换提示音开关并持久化
function toggleSound() {
  soundOff.value = !soundOff.value;
  window.localStorage.setItem('kefu_sound_off', soundOff.value ? '1' : '0');
}
// 退出登录
function outLogin() {
  isOnline.value = false;
  ElMessageBox({
    title: '退出登录确认',
    message: '您确定退出登录当前账户吗？打开的标签页和个人设置将会保存。',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确认',
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
</script>

<style lang="scss" scoped>
.base-header {
  z-index: 99;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 66px;
  padding: 0 0 0 15px;
  // 主题令牌渐变（禁硬编码主题色 hex，换肤自动跟随）
  background: linear-gradient(270deg, var(--prev-color-primary-light-3) 0%, var(--prev-color-primary) 100%);
  color: #fff;
  flex-shrink: 0;
  .left-header-wrapper {
    position: relative;
    display: flex;
    flex: 1;
    align-items: center;
    .user_info {
      display: flex;
      align-items: center;

      img {
        width: 40px;
        height: 40px;
        margin-right: 10px;
        border-radius: 50%;
      }

      span {
        font-size: 16px;
      }
      .status-box {
        position: relative;
        cursor: pointer;
      }
      .status {
        display: flex;
        align-items: center;
        padding: 3px 10px;
        margin-left: 5px;
        background: var(--el-color-success-light-9);
        color: rgba(0, 0, 0, 0.65);
        border-radius: 9px;
        .dot {
          width: 6px;
          height: 6px;
          margin-right: 3px;
          border-radius: 50%;
          background: var(--prev-color-success);
        }
        &.off {
          background: var(--prev-bg-color);
          .dot {
            background: var(--prev-color-text-placeholder);
          }
        }
      }
      .online-down {
        z-index: 50;
        position: absolute;
        left: 0;
        // 徽章下方 8px 处展开，替代原 bottom:-116px 硬编码定位
        top: calc(100% + 8px);
        min-width: 132px;
        padding: 4px 0;
        background: #fff;
        color: var(--prev-color-text-regular);
        border: 1px solid var(--prev-border-color-lighter);
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        .item {
          position: relative;
          display: flex;
          align-items: center;
          // 长文案（退出登录）不折行，由 min-width 撑开面板
          white-space: nowrap;
          padding: 8px 16px 8px 32px;
          font-size: 14px;
          cursor: pointer;
          transition: background 0.2s;
          &:hover {
            background: var(--prev-color-hover);
          }
          i {
            flex: none;
            width: 10px;
            height: 10px;
            margin-right: 8px;
            border-radius: 50%;
            background: var(--prev-color-text-placeholder);
            &.green {
              background: var(--prev-color-success);
            }
          }
          .iconfont {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
          }
        }
        // 退出登录项：与上方选项分隔，hover 浅红底强化警示
        .item.logout {
          margin-top: 4px;
          border-top: 1px solid var(--prev-border-color-lighter);
          color: var(--prev-color-danger);
          &:hover {
            background: var(--el-color-danger-light-9);
          }
          .iconfont {
            position: static;
            transform: none;
            font-size: 14px;
            margin-right: 8px;
          }
        }
      }
    }
    // 右上角提示音开关
    .sound-btn {
      position: absolute;
      right: 30px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      font-size: 20px;
      color: #fff;
      cursor: pointer;
    }
  }
}
</style>
