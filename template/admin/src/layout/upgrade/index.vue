<template>
  <div class="upgrade-dialog">
    <el-dialog
      :visible.sync="isUpgrade"
      width="470px"
      destroy-on-close
      :show-close="true"
      :close-on-click-modal="false"
      :close-on-press-escape="false"
    >
      <div class="upgrade-title">
        <div class="upgrade-title-warp">
          <span class="upgrade-title-warp-txt">{{ $t('message.upgrade.title') }}</span>
          <span class="upgrade-title-warp-version">v{{ version }}</span>
        </div>
      </div>
      <div class="upgrade-content">
        {{ getThemeConfig.globalTitle }} {{ $t('message.upgrade.msg') }}
        <div class="mt5">
          <el-link type="primary" class="font12" href="xx" target="_black"> CHANGELOG.md </el-link>
        </div>
        <div class="upgrade-content-desc mt5">{{ $t('message.upgrade.desc') }}</div>
      </div>
      <div class="upgrade-btn">
        <el-button round size="small" v-db-click @click="onCancel">{{ $t('message.upgrade.btnOne') }}</el-button>
        <el-button type="primary" round size="small" v-db-click @click="onUpgrade" :loading="isLoading">{{
          btnTxt
        }}</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { Local, Session } from '@/utils/storage';
import config from '../../../package.json';
import setting from '../../setting';
export default {
  data() {
    return {
      isUpgrade: false,
      version: config.version,
      isLoading: false,
      btnTxt: '',
    };
  },
  computed: {
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
  },
  methods: {
    // 残忍拒绝
    onCancel() {
      this.isUpgrade = false;
    },
    // 马上更新
    onUpgrade() {
      this.isLoading = true;
      this.btnTxt = this.$t('message.upgrade.btnTwoLoading');
      setTimeout(() => {
        Local.clear();
        Session.clear();
        Local.set('version', this.version);
        this.$router.push({ path: `${setting.routePre}/login` });
      }, 2000);
    },
    // 延迟显示，防止刷新时界面显示太快
    delayShow() {
      setTimeout(() => {
        this.btnTxt = this.$t('message.upgrade.btnTwo');
      }, 1000);
      setTimeout(() => {
        this.isUpgrade = true;
      }, 2000);
    },
  },
  mounted() {
    this.delayShow();
  },
};
</script>

<style scoped lang="scss">
.upgrade-dialog {
  & ::v-deep .el-dialog {
    .el-dialog__body {
      padding: 0 !important;
    }
    .el-dialog__header {
      display: none !important;
    }
    .upgrade-title {
      text-align: center;
      height: 130px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      &::after {
        content: '';
        position: absolute;
        background-color: var(--prev-color-primary-light-1);
        width: 130%;
        height: 130px;
        border-bottom-left-radius: 100%;
        border-bottom-right-radius: 100%;
      }
      .upgrade-title-warp {
        z-index: 1;
        position: relative;
        .upgrade-title-warp-txt {
          color: var(--prev-color-text-white);
          font-size: 22px;
          letter-spacing: 3px;
        }
        .upgrade-title-warp-version {
          background-color: var(--prev-color-primary-light-4);
          color: var(--prev-color-text-white);
          font-size: 12px;
          position: absolute;
          display: flex;
          top: -2px;
          right: -50px;
          padding: 2px 4px;
          border-radius: 2px;
        }
      }
    }
    .upgrade-content {
      padding: 20px;
      line-height: 22px;
      color: var(--prev-color-text-regular);
      .upgrade-content-desc {
        color: var(--prev-color-text-placeholder);
        font-size: 12px;
      }
    }
    .upgrade-btn {
      border-top: 1px solid var(--prev-border-color-lighter);
      display: flex;
      justify-content: space-around;
      padding: 15px 20px;
      .el-button {
        width: 100%;
      }
    }
  }
}
</style>
