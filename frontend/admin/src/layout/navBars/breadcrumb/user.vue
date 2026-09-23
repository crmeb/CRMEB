<template>
  <div class="layout-navbars-breadcrumb-user" :style="{ flex: layoutUserFlexNum }">
    <div class="layout-navbars-breadcrumb-user-icon" v-db-click @click="refresh">
      <i class="el-icon-refresh-right" :title="$t('message.user.title7')"></i>
    </div>
    <div class="layout-navbars-breadcrumb-user-icon" v-db-click @click="onSearchClick">
      <i class="el-icon-search" :title="$t('message.user.title2')"></i>
    </div>

    <div class="layout-navbars-breadcrumb-user-icon">
      <el-popover
        placement="bottom"
        :width="300"
        trigger="click"
        popper-class="user-news-popover"
        @show="isDot = false"
      >
        <UserNews @haveNews="initIsDot" />
        <template #reference>
          <el-badge :is-dot="isDot">
            <i class="el-icon-bell" :title="$t('message.user.title4')"></i>
          </el-badge>
        </template>
      </el-popover>
    </div>
    <div class="layout-navbars-breadcrumb-user-icon" v-db-click @click="onScreenfullClick">
      <i
        :title="isScreenfull ? $t('message.user.title6') : $t('message.user.title5')"
        :class="!isScreenfull ? 'el-icon-full-screen' : 'el-icon-crop'"
      ></i>
    </div>
    <div class="layout-navbars-breadcrumb-user-icon mr10" v-db-click @click="openMobelPage">
      <i title="商城页面" class="el-icon-mobile-phone"></i>
    </div>
    <el-dropdown :show-timeout="70" @command="onDropdownCommand">
      <span class="layout-navbars-breadcrumb-user-link">
        <img :src="getUserInfos.head_pic" class="layout-navbars-breadcrumb-user-link-photo mr5" />
        {{ getUserInfos.account === '' ? 'test' : getUserInfos.account }}
        <i class="el-icon-arrow-down el-icon--right"></i>
      </span>
      <template #dropdown
        ><el-dropdown-menu>
          <el-dropdown-item command="user">{{ $t('message.user.dropdown6') }}</el-dropdown-item>
          <el-dropdown-item divided command="logOut">{{ $t('message.user.dropdown5') }}</el-dropdown-item>
        </el-dropdown-menu></template
      >
    </el-dropdown>
    <div class="layout-navbars-breadcrumb-user-icon" v-db-click @click="onLayoutSetingClick">
      <i class="el-icon-setting" :title="$t('message.user.title3')"></i>
    </div>
    <Search ref="searchRef" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import screenfull from 'screenfull';
import { ElMessage, ElMessageBox } from 'element-plus';
import { AccountLogout } from '@/api/account';
import { removeCookies } from '@/libs/util';
import { Session, Local } from '@/utils/storage.js';
import UserNews from '@/layout/navBars/breadcrumb/userNews.vue';
import Search from '@/layout/navBars/breadcrumb/search.vue';
import bus from '@/utils/bus';
import { i18n } from '@/i18n/index.js';
import { useAppStore } from '@/store/module/app';
import { useUserInfoStore } from '@/store/module/userInfo';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutBreadcrumbUser' });

const { proxy } = getCurrentInstance();

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const userInfoStore = useUserInfoStore();
const themeConfigStore = useThemeConfigStore();

const searchRef = ref(null);
const isScreenfull = ref(false);
const disabledI18n = ref('zh-cn');
const disabledSize = ref('');
const isDot = ref(false);

// 获取用户信息
const getUserInfos = computed(() => userInfoStore.userInfo);

// 设置弹性盒子布局 flex
const layoutUserFlexNum = computed(() => {
  let { layout, isClassicSplitMenu } = themeConfigStore.themeConfig;
  let num = '';
  if (layout === 'defaults' || (layout === 'classic' && !isClassicSplitMenu) || layout === 'columns') num = 1;
  else num = null;
  return num;
});

onMounted(() => {
  if (Local.get('themeConfigPrev')) {
    initI18n();
    initComponentSize();
  }
});

/**
 * 初始化 isDot 属性
 * @param {boolean} status - 状态值
 */
function initIsDot(status) {
  isDot.value = status;
}

function openMobelPage() {
  // 获取域名
  window.open(window.location.origin, '_blank');
}

// 搜索点击
function onSearchClick() {
  searchRef.value.openSearch();
}

// 布局配置点击
function onLayoutSetingClick() {
  bus.emit('openSetingsDrawer');
}

function refresh() {
  bus.emit('onTagsViewRefreshRouterView', route.path);
}

// 全屏点击
function onScreenfullClick() {
  if (!screenfull.isEnabled) {
    ElMessage.warning('暂不不支持全屏');
    return false;
  }
  screenfull.toggle();
  screenfull.on('change', () => {
    if (screenfull.isFullscreen) isScreenfull.value = true;
    else isScreenfull.value = false;
  });
  // 监听菜单 horizontal.vue 滚动条高度更新
  bus.emit('updateElScrollBar');
}

// 组件大小改变
function onComponentSizeChange(size) {
  Local.remove('themeConfigPrev');
  themeConfigStore.themeConfig.globalComponentSize = size;
  Local.set('themeConfigPrev', themeConfigStore.themeConfig);
  // 原 this.$ELEMENT.size 全局尺寸配置（Vue2 element-ui 专属，element-plus 下已无该全局对象）
  if (proxy.$ELEMENT) proxy.$ELEMENT.size = size;
  initComponentSize();
  window.location.reload();
}

// 语言切换
function onLanguageChange(lang) {
  Local.remove('themeConfigPrev');
  themeConfigStore.themeConfig.globalI18n = lang;
  Local.set('themeConfigPrev', themeConfigStore.themeConfig);
  i18n.global.locale = lang;
  initI18n();
}

// 初始化言语国际化
function initI18n() {
  switch (Local.get('themeConfigPrev').globalI18n) {
    case 'zh-cn':
      disabledI18n.value = 'zh-cn';
      break;
    case 'en':
      disabledI18n.value = 'en';
      break;
    case 'zh-tw':
      disabledI18n.value = 'zh-tw';
      break;
  }
}

// 初始化全局组件大小
function initComponentSize() {
  switch (Local.get('themeConfigPrev').globalComponentSize) {
    case '':
      disabledSize.value = '';
      break;
    case 'medium':
      disabledSize.value = 'medium';
      break;
    case 'small':
      disabledSize.value = 'small';
      break;
    case 'mini':
      disabledSize.value = 'mini';
      break;
  }
}

// `dropdown 下拉菜单` 当前项点击
function onDropdownCommand(path) {
  if (path === 'logOut') {
    setTimeout(() => {
      ElMessageBox({
        closeOnClickModal: false,
        closeOnPressEscape: false,
        title: i18n.global.t('message.user.logOutTitle'),
        message: i18n.global.t('message.user.logOutMessage'),
        showCancelButton: true,
        confirmButtonText: i18n.global.t('message.user.logOutConfirm'),
        cancelButtonText: i18n.global.t('message.user.logOutCancel'),
        beforeClose: (action, instance, done) => {
          if (action === 'confirm') {
            instance.confirmButtonLoading = true;
            instance.confirmButtonText = i18n.global.t('message.user.logOutExit');
            AccountLogout()
              .then((res) => {
                done();
                ElMessage.success('您已成功退出');
                appStore.clearAll();
                // localStorage.clear();
                // sessionStorage.clear();
                removeCookies('token');
                removeCookies('expires_time');
                removeCookies('uuid');
                // router.replace({ path: `${settings.routePre}/login` });
              })
              .finally(() => {
                setTimeout(() => {
                  router.replace({ name: 'login' });
                  instance.confirmButtonLoading = false;
                  done();
                }, 1500);
              });
          } else {
            done();
          }
        },
      })
        .then(() => {
          // 清除缓存/token等
          Session.clear();
          // 使用 reload 时，不需要调用 resetRoute() 重置路由
          window.location.reload();
        })
        .catch(() => {});
    }, 150);
  } else if (path === 'user') {
    router.push({ name: 'systemUser' });
  } else {
    router.push(path);
  }
}
</script>

<style scoped lang="scss">
.layout-navbars-breadcrumb-user {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  .el-icon-bell {
    color: var(--prev-bg-topBarColor);
  }
  &-link {
    height: 100%;
    display: flex;
    align-items: center;
    white-space: nowrap;
    &-photo {
      width: 30px;
      height: 30px;
      border-radius: 100%;
    }
  }
  &-icon {
    padding: 0 10px;
    cursor: pointer;
    color: var(--prev-bg-topBarColor);
    height: 50px;
    line-height: 50px;
    display: flex;
    align-items: center;
    font-size: 15px;
    &:hover {
      background: var(--prev-color-hover);
      i {
        display: inline-block;
        animation: logoAnimation 0.3s ease-in-out;
      }
    }
  }
  & :deep(.el-dropdown) {
    color: var(--prev-bg-topBarColor);
    cursor: pointer;
  }
  & :deep(.el-badge) {
    height: 40px;
    line-height: 40px;
    display: flex;
    align-items: center;
  }
  & :deep(.el-badge__content.is-fixed) {
    top: 12px;
  }
}
</style>
