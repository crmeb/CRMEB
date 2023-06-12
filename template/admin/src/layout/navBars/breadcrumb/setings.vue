<template>
  <div class="layout-breadcrumb-seting">
    <el-drawer
      title="主题编辑"
      :visible.sync="getThemeConfig.isDrawer"
      direction="rtl"
      destroy-on-close
      size="300px"
      @close="onDrawerClose"
    >
      <el-scrollbar class="layout-breadcrumb-seting-bar">
        <!-- 布局切换 -->
        <el-divider :content-position="contentPosotion">{{ $t('message.layout.sixTitle') }}</el-divider>
        <div class="layout-drawer-content-flex">
          <!-- defaults 布局 -->
          <div
            class="layout-drawer-content-item"
            :class="{ 'drawer-layout-active': getThemeConfig.layout === 'defaults' }"
            @click="onSetLayout('defaults')"
          >
            <section class="el-container el-circular">
              <aside class="el-aside w10 mr5" style="width: 17px"></aside>
              <section class="el-container is-vertical">
                <header class="el-header mb5" style="height: 10px"></header>
                <main class="el-main"></main>
              </section>
            </section>
            <!-- <div class="layout-tips-warp" :class="{ 'layout-tips-warp-active': getThemeConfig.layout === 'defaults' }">
              <div class="layout-tips-box">
                <p class="layout-tips-txt">{{ $t('message.layout.sixDefaults') }}</p>
              </div>
            </div> -->
          </div>
          <!-- columns 布局 -->
          <div
            class="layout-drawer-content-item"
            :class="{ 'drawer-layout-active': getThemeConfig.layout === 'columns' }"
            @click="onSetLayout('columns')"
          >
            <section class="el-container el-circular">
              <aside class="el-aside mr5" style="width: 10px"></aside>
              <aside class="el-aside-dark mr5" style="width: 17px"></aside>
              <section class="el-container is-vertical">
                <header class="el-header mb5" style="height: 10px"></header>
                <main class="el-main"></main>
              </section>
            </section>
            <!-- <div class="layout-tips-warp" :class="{ 'layout-tips-warp-active': getThemeConfig.layout === 'columns' }">
              <div class="layout-tips-box">
                <p class="layout-tips-txt">{{ $t('message.layout.sixColumns') }}</p>
              </div>
            </div> -->
          </div>
          <!-- classic 布局 -->
          <div
            class="layout-drawer-content-item"
            :class="{ 'drawer-layout-active': getThemeConfig.layout === 'classic' }"
            @click="onSetLayout('classic')"
          >
            <section class="el-container is-vertical el-circular">
              <header class="el-aside mb5" style="height: 10px"></header>
              <section class="el-container">
                <aside class="el-aside-dark mr5" style="width: 17px"></aside>
                <section class="el-container is-vertical">
                  <main class="el-main"></main>
                </section>
              </section>
            </section>
            <!-- <div class="layout-tips-warp" :class="{ 'layout-tips-warp-active': getThemeConfig.layout === 'classic' }">
              <div class="layout-tips-box">
                <p class="layout-tips-txt">{{ $t('message.layout.sixClassic') }}</p>
              </div>
            </div> -->
          </div>

          <!-- transverse 布局 -->
          <div
            class="layout-drawer-content-item"
            :class="{ 'drawer-layout-active': getThemeConfig.layout === 'transverse' }"
            @click="onSetLayout('transverse')"
          >
            <section class="el-container is-vertical el-circular">
              <header class="el-aside mb5" style="height: 10px"></header>
              <section class="el-container">
                <section class="el-container is-vertical">
                  <main class="el-main"></main>
                </section>
              </section>
            </section>
            <!-- <div
              class="layout-tips-warp"
              :class="{ 'layout-tips-warp-active': getThemeConfig.layout === 'transverse' }"
            >
              <div class="layout-tips-box">
                <p class="layout-tips-txt">{{ $t('message.layout.sixTransverse') }}</p>
              </div>
            </div> -->
          </div>
        </div>
        <!-- 全局主题 -->
        <!-- <el-divider :content-position="contentPosotion">{{ $t('message.layout.oneTitle') }}</el-divider>
        <div class="layout-breadcrumb-seting-bar-flex">
          <div class="layout-breadcrumb-seting-bar-flex-label">primary</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-color-picker v-model="getThemeConfig.primary" size="small" @change="onColorPickerChange">
            </el-color-picker>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.menuBag') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-color-picker v-model="getThemeConfig.menuBgColor" size="small" @change="onMenuBgColorChange">
            </el-color-picker>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsDark') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isIsDark" :width="35" @change="onAddDarkChange"></el-switch>
          </div>
        </div> -->

        <!-- 界面设置 -->
        <el-divider :content-position="contentPosotion">{{ $t('message.layout.threeTitle') }}</el-divider>
        <div class="layout-breadcrumb-seting-bar-flex mb10">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.themeStyle') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-select
              v-model="getThemeConfig.themeStyle"
              placeholder="请选择"
              size="mini"
              style="width: 90px"
              @change="setLocalTheme"
            >
              <el-option label="蓝黑" value="theme-1"></el-option>
              <el-option label="蓝白" value="theme-2"></el-option>
              <el-option label="绿黑" value="theme-3"></el-option>
              <el-option label="绿白" value="theme-4"></el-option>
              <el-option label="紫黑" value="theme-5"></el-option>
              <el-option label="紫白" value="theme-6"></el-option>
              <el-option label="红黑" value="theme-7"></el-option>
              <el-option label="红白" value="theme-8"></el-option>
              <el-option label="渐变" value="theme-9" v-if="getThemeConfig.layout === 'columns'"></el-option>
            </el-select>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.threeIsCollapse') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isCollapse" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.threeIsUniqueOpened') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isUniqueOpened" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.threeIsFixedHeader') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isFixedHeader" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div>

        <!-- 界面显示 -->
        <el-divider :content-position="contentPosotion">{{ $t('message.layout.fourTitle') }}</el-divider>
        <div class="layout-breadcrumb-seting-bar-flex">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsShowLogo') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isShowLogo" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div>
        <div
          class="layout-breadcrumb-seting-bar-flex mt15"
          :style="{ opacity: getThemeConfig.layout === 'classic' || getThemeConfig.layout === 'transverse' ? 0.5 : 1 }"
        >
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsBreadcrumb') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch
              v-model="getThemeConfig.isBreadcrumb"
              :disabled="getThemeConfig.layout === 'classic' || getThemeConfig.layout === 'transverse'"
              :width="35"
              @change="setLocalThemeConfig"
            ></el-switch>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsBreadcrumbIcon') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isBreadcrumbIcon" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsTagsview') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isTagsview" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div>
        <!-- <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsTagsviewIcon') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch
              v-model="getThemeConfig.isTagsviewIcon"
              :disabled="!getThemeConfig.isTagsview"
              :width="35"
              @change="setLocalThemeConfig"
            ></el-switch>
          </div>
        </div> -->
        <!-- <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsCacheTagsView') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isCacheTagsView" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div> -->
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsFooter') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isFooter" :width="35" @change="setLocalThemeConfig"></el-switch>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsGrayscale') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch
              v-model="getThemeConfig.isGrayscale"
              :width="35"
              @change="onAddFilterChange('grayscale')"
            ></el-switch>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fourIsInvert') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-switch v-model="getThemeConfig.isInvert" :width="35" @change="onAddFilterChange('invert')"></el-switch>
          </div>
        </div>

        <!-- 其它设置 -->
        <el-divider :content-position="contentPosotion">{{ $t('message.layout.fiveTitle') }}</el-divider>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fiveTagsStyle') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <!-- <el-select
              v-model="getThemeConfig.tagsStyle"
              placeholder="请选择"
              size="mini"
              style="width: 90px"
              @change="setLocalThemeConfig"
            >
              <el-option label="风格1" value="tags-style-one"></el-option>
              <el-option label="风格2" value="tags-style-four"></el-option>
              <el-option label="风格3" value="tags-style-five"></el-option>
            </el-select> -->
            <el-radio-group
              v-model="getThemeConfig.tagsStyle"
              :disabled="!getThemeConfig.isTagsview"
              size="mini"
              @change="setLocalThemeConfig"
            >
              <el-radio-button label="tags-style-one">卡片</el-radio-button>
              <el-radio-button label="tags-style-four">灵动</el-radio-button>
              <el-radio-button label="tags-style-five">圆滑</el-radio-button>
            </el-radio-group>
          </div>
        </div>
        <div class="layout-breadcrumb-seting-bar-flex mt15">
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fiveAnimation') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-radio-group v-model="getThemeConfig.animation" size="mini" @change="setLocalThemeConfig">
              <el-radio-button label="slide-left">左滑</el-radio-button>
              <el-radio-button label="opacitys">透明</el-radio-button>
              <el-radio-button label="slide-right">右滑</el-radio-button>
            </el-radio-group>
          </div>
        </div>
        <div
          class="layout-breadcrumb-seting-bar-flex mt15"
          :class="{ mb28: getThemeConfig.layout !== 'columns' && getThemeConfig.layout !== 'classic' }"
        >
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fiveColumnsAsideStyle') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-radio-group v-model="getThemeConfig.columnsAsideStyle" size="mini" @change="setLocalThemeConfig">
              <el-radio-button label="columns-round">圆角</el-radio-button>
              <el-radio-button label="columns-card">卡片</el-radio-button>
            </el-radio-group>
          </div>
        </div>
        <div
          class="layout-breadcrumb-seting-bar-flex mt15 mb28"
          v-if="getThemeConfig.layout === 'columns' || getThemeConfig.layout === 'classic'"
        >
          <div class="layout-breadcrumb-seting-bar-flex-label">{{ $t('message.layout.fiveColumnsAsideLayout') }}</div>
          <div class="layout-breadcrumb-seting-bar-flex-value">
            <el-radio-group v-model="getThemeConfig.columnsAsideLayout" size="mini" @change="setLocalThemeConfig">
              <el-radio-button label="columns-horizontal">水平</el-radio-button>
              <el-radio-button label="columns-vertical">垂直</el-radio-button>
            </el-radio-group>
          </div>
        </div>
        <!-- <div class="copy-config">
          <el-alert :title="$t('message.layout.tipText')" type="warning" :closable="false"> </el-alert>
          <el-button
            size="small"
            class="copy-config-btn"
            icon="el-icon-document-copy"
            type="primary"
            ref="copyConfigBtnRef"
            @click="onCopyConfigClick"
            >{{ $t('message.layout.copyText') }}
          </el-button>
          <el-button
            size="small"
            class="copy-config-btn-reset"
            type="info"
            icon="el-icon-refresh-right"
            @click="onResetConfigClick"
          >
            {{ $t('message.layout.resetText') }}
          </el-button>
        </div> -->
      </el-scrollbar>
    </el-drawer>
  </div>
</template>

<script>
import ClipboardJS from 'clipboard';
import { Local } from '@/utils/storage.js';
import { useChangeColor } from '@/utils/theme.js';
import config from '../../../../package.json';
import { themeList } from './theme';
export default {
  name: 'layoutBreadcrumbSeting',
  computed: {
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
  },
  data() {
    return {
      contentPosotion: 'center',
    };
  },
  created() {
    // 判断当前布局是否不相同，不相同则初始化当前布局的样式，防止监听窗口大小改变时，布局配置logo、菜单背景等部分布局失效问题
    if (!Local.get('frequency')) this.initSetLayoutChange();
    Local.set('frequency', 1);
    // 监听窗口大小改变，非默认布局，设置成默认布局（适配移动端）
    this.bus.$on('layoutMobileResize', (res) => {
      if (this.$store.state.themeConfig.themeConfig.layout === res.layout) return false;
      this.$store.state.themeConfig.themeConfig.layout = res.layout;
      this.$store.state.themeConfig.themeConfig.isDrawer = false;
      this.$store.state.themeConfig.themeConfig.isCollapse = false;
      this.initSetLayoutChange();
    });
    this.setLocalTheme(this.$store.state.themeConfig.themeConfig.themeStyle);
  },
  mounted() {
    this.initLayoutConfig();
  },
  methods: {
    // 全局主题
    onColorPickerChange() {
      if (!this.getThemeConfig.primary) return;
      // 颜色加深
      // document.documentElement.style.setProperty('--prev-color-primary', this.getThemeConfig.primary);
      // 颜色变浅
      for (let i = 1; i <= 9; i++) {
        document.documentElement.style.setProperty(
          `--prev-color-primary-light-${i}`,
          `${useChangeColor().getLightColor(this.getThemeConfig.primary, i / 10)}`,
        );
      }
      this.setLocalThemeConfig();
    },
    setLocalTheme(val) {
      let themeSelect = themeList[val];
      if (['theme-1', 'theme-3', 'theme-5', 'theme-7'].includes(val)) {
        // 这几个为黑色背景主题
        themeSelect['--prev-border-color-lighter'] = '#ebeef5';

        if (['classic', 'transverse'].includes(this.getThemeConfig.layout)) {
          themeSelect['--prev-bg-topBar'] = '#282c34';
          themeSelect['--prev-bg-topBarColor'] = '#fff';
          themeSelect['--prev-bg-menuBarColor'] = '#fff';
          themeSelect['--prev-MenuActiveColor'] = '#fff';
          if (val == 'theme-1') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#1890ff';
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-border-color-lighter'] = '#282c34';
          } else if (val == 'theme-3') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#41b584';
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-border-color-lighter'] = '#282c34';
          } else if (val == 'theme-5') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#6954f0';
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-border-color-lighter'] = '#282c34';
          } else if (val == 'theme-7') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#f34d37';
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-border-color-lighter'] = '#282c34';
          }
        } else if (this.getThemeConfig.layout === 'columns') {
          themeSelect['--prev-bg-topBar'] = '#fff';
          themeSelect['--prev-bg-topBarColor'] = '#515a6e';
          themeSelect['--prev-bg-menuBar'] = '#fff';
          themeSelect['--prev-bg-menuBarColor'] = '#303133';
          themeSelect['--prev-border-color-lighter'] = '#ebeef5';

          if (val == 'theme-1') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#e8f4ff';
            themeSelect['--prev-color-primary'] = '#1890ff';
            themeSelect['--prev-MenuActiveColor'] = '#1890ff';
          } else if (val == 'theme-3') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#ecf8f3';
            themeSelect['--prev-color-primary'] = '#41b584';
            themeSelect['--prev-MenuActiveColor'] = '#41b584';
          } else if (val == 'theme-5') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#f0eefe';
            themeSelect['--prev-color-primary'] = '#6954f0';
            themeSelect['--prev-MenuActiveColor'] = '#6954f0';
          } else if (val == 'theme-7') {
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#feedeb';
            themeSelect['--prev-color-primary'] = '#f34d37';
            themeSelect['--prev-MenuActiveColor'] = '#f34d37';
          }
        } else {
          if (val == 'theme-1') {
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#1890ff';
            themeSelect['--prev-color-primary'] = '#1890ff';
            themeSelect['--prev-bg-topBarColor'] = '#282c34';
            themeSelect['--prev-bg-topBar'] = '#fff';
            themeSelect['--prev-bg-menuBarColor'] = '#fff';
            themeSelect['--prev-MenuActiveColor'] = '#fff';
          } else if (val == 'theme-3') {
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#41b584';
            themeSelect['--prev-color-primary'] = '#41b584';
            themeSelect['--prev-bg-topBar'] = '#fff';
            themeSelect['--prev-bg-topBarColor'] = '#282c34';
            themeSelect['--prev-bg-menuBarColor'] = '#fff';
            themeSelect['--prev-MenuActiveColor'] = '#fff';
          } else if (val == 'theme-5') {
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#6954f0';
            themeSelect['--prev-bg-topBarColor'] = '#282c34';
            themeSelect['--prev-color-primary'] = '#6954f0';
            themeSelect['--prev-bg-topBar'] = '#fff';

            themeSelect['--prev-bg-menuBarColor'] = '#fff';
            themeSelect['--prev-MenuActiveColor'] = '#fff';
          } else if (val == 'theme-7') {
            themeSelect['--prev-bg-menuBar'] = '#282c34';
            themeSelect['--prev-bg-topBar'] = '#fff';
            themeSelect['--prev-bg-topBarColor'] = '#282c34';
            themeSelect['--prev-bg-menu-hover-ba-color'] = '#f34d37';
            themeSelect['--prev-color-primary'] = '#f34d37';
            themeSelect['--prev-bg-menuBarColor'] = '#fff';
            themeSelect['--prev-MenuActiveColor'] = '#fff';
          }
        }
      } else {
        themeSelect['--prev-border-color-lighter'] = '#ebeef5';
      }
      for (let key in themeSelect) {
        document.documentElement.style.setProperty(key, themeSelect[key]);
      }
    },
    onMenuBgColorChange() {
      if (!this.getThemeConfig.menuBgColor) return;
      // 颜色加深
      document.documentElement.style.setProperty('--prev-bg-menuBar', this.getThemeConfig.menuBgColor);
      this.setLocalThemeConfig();
    },
    // 深色模式
    onAddDarkChange() {
      const body = document.documentElement;
      if (this.getThemeConfig.isIsDark) body.setAttribute('data-theme', 'dark');
      else body.setAttribute('data-theme', '');
      this.setLocalThemeConfig();
    },
    // 初始化：刷新页面时，设置了值，直接取缓存中的值进行初始化
    initLayoutConfig() {
      window.addEventListener('load', () => {
        // 默认样式
        this.onColorPickerChange();
        // 灰色模式
        if (this.$store.state.themeConfig.themeConfig.isGrayscale) this.onAddFilterChange('grayscale');
        // 色弱模式
        if (this.$store.state.themeConfig.themeConfig.isInvert) this.onAddFilterChange('invert');
        // 深色模式
        if (this.$store.state.themeConfig.themeConfig.isIsDark) this.onAddDarkChange();
        // 语言国际化
        if (Local.get('themeConfigPrev')) this.$i18n.locale = Local.get('themeConfigPrev').globalI18n;
      });
    },
    // 存储布局配置
    setLocalThemeConfig() {
      Local.remove('themeConfigPrev');
      Local.set('themeConfigPrev', this.$store.state.themeConfig.themeConfig);
      this.setLocalThemeConfigStyle();
    },
    // 存储布局配置全局主题样式（html根标签）
    setLocalThemeConfigStyle() {
      Local.set('themeConfigStyle', document.documentElement.style.cssText);
    },
    // 布局配置弹窗打开
    openDrawer() {
      this.$store.state.themeConfig.themeConfig.isDrawer = true;
    },
    // 关闭弹窗时，初始化变量
    onDrawerClose() {
      this.$store.state.themeConfig.themeConfig.isDrawer = false;
      this.setLocalThemeConfig();
    },
    // 灰色模式/色弱模式
    onAddFilterChange(attr) {
      if (attr === 'grayscale') {
        if (this.$store.state.themeConfig.themeConfig.isGrayscale)
          this.$store.state.themeConfig.themeConfig.isInvert = false;
      } else {
        if (this.$store.state.themeConfig.themeConfig.isInvert)
          this.$store.state.themeConfig.themeConfig.isGrayscale = false;
      }
      const cssAttr =
        attr === 'grayscale'
          ? `grayscale(${this.$store.state.themeConfig.themeConfig.isGrayscale ? 1 : 0})`
          : `invert(${this.$store.state.themeConfig.themeConfig.isInvert ? '80%' : '0%'})`;
      const appEle = document.body;
      appEle.setAttribute('style', `filter: ${cssAttr};`);
      this.setLocalThemeConfig();
    },
    // 布局切换
    onSetLayout(layout) {
      Local.set('oldLayout', layout);
      if (this.$store.state.themeConfig.themeConfig.layout === layout) return false;
      if (['classic', 'transverse'].includes(layout)) {
        this.$store.state.themeConfig.themeConfig.isTagsview = false;
      } else {
        this.$store.state.themeConfig.themeConfig.isTagsview = true;
      }
      this.$store.state.themeConfig.themeConfig.layout = layout;
      this.$store.state.themeConfig.themeConfig.isDrawer = false;
      this.$store.state.themeConfig.themeConfig.columnsAsideStyle = 'columns-card';
      // this.initSetLayoutChange();
      this.setLocalTheme(this.$store.state.themeConfig.themeConfig.themeStyle);
    },
    // 设置布局切换，重置主题样式
    initSetLayoutChange() {
      // if (this.$store.state.themeConfig.themeConfig.layout === 'classic') {
      //   this.onBgColorPickerChange('menuBar', '#282c34');
      //   this.onBgColorPickerChange('menuBarColor', '#ffffff');
      //   this.onBgColorPickerChange('topBar', '#282c34');
      //   this.onBgColorPickerChange('topBarColor', '#ffffff');
      // } else if (this.$store.state.themeConfig.themeConfig.layout === 'transverse') {
      //   this.onBgColorPickerChange('menuBarColor', '#ffffff');
      //   this.onBgColorPickerChange('topBar', '#282c34');
      //   this.onBgColorPickerChange('topBarColor', '#fff');
      //   // this.onBgColorPickerChange('bgTopBarColor', '#ccc');
      // } else if (this.$store.state.themeConfig.themeConfig.layout === 'columns') {
      //   this.onBgColorPickerChange('menuBar', '#282c34');
      //   this.onBgColorPickerChange('menuBarColor', '#fff');
      //   this.onBgColorPickerChange('topBar', '#ffffff');
      //   this.onBgColorPickerChange('topBarColor', '#606266');
      //   this.onBgColorPickerChange('columnsMenuBar', '#282c34');
      // } else {
      //   this.onBgColorPickerChange('menuBar', '#282c34');
      //   this.onBgColorPickerChange('menuBarColor', '#ffffff');
      //   this.onBgColorPickerChange('topBar', '#ffffff');
      //   this.onBgColorPickerChange('topBarColor', '#606266');
      // }
    },
    // 菜单 / 顶栏背景等
    onBgColorPickerChange(bg, rgb) {
      document.documentElement.style.setProperty(`--prev-bg-${bg}`, rgb);
      this.setLocalThemeConfigStyle();
    },
    // 一键复制配置
    onCopyConfigClick() {
      this.$store.state.themeConfig.themeConfig.isDrawer = false;
      let clipboardJS = new ClipboardJS('.copy-config-btn', {
        text: () => JSON.stringify(this.$store.state.themeConfig.themeConfig),
      });
      clipboardJS.on('success', () => {
        this.$message.success('配置复制成功');
        this.isDrawer = false;
        clipboardJS.destroy();
      });
      clipboardJS.on('error', () => {
        this.$message.error('配置复制失败');
      });
    },
    // 一键恢复默认
    onResetConfigClick() {
      Local.clear();
      window.location.reload();
      Local.set('version', config.version);
    },
  },
};
</script>
<style>
body .v-modal {
  background-color: rgba(0, 0, 0, 0.1);
}
</style>
<style scoped lang="scss">
.w10 {
  width: 10px;
}
.mr5 {
  margin-right: 5px;
}
/deep/ .el-radio-button--mini .el-radio-button__inner {
  padding: 7px 8px;
}
.layout-breadcrumb-seting-bar {
  height: calc(100vh - 50px);
  padding: 0 15px;
  ::v-deep .el-scrollbar__view {
    overflow-x: hidden !important;
  }
  .layout-breadcrumb-seting-bar-flex {
    display: flex;
    align-items: center;
    &-label {
      flex: 1;
      color: var(--prev-color-text-primary);
    }
  }
  .layout-drawer-content-flex {
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    justify-content: center;
    margin: 0 -5px;
    .layout-drawer-content-item.drawer-layout-active {
      border: 1px solid;
      border-color: var(--prev-color-primary);
    }
    .layout-drawer-content-item:hover {
      transition: all 0.3s ease-in-out;
      border: 1px solid;
      border-color: var(--prev-color-primary);
    }
    .layout-drawer-content-item {
      width: 107px;
      height: 70px;
      cursor: pointer;
      border: 1px solid rgba(0, 0, 0, 0);
      position: relative;
      padding: 6px;
      background: #ffffff;
      box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.08);
      border-radius: 6px;
      opacity: 1;
      margin: 10px;

      .el-container {
        height: 100%;
        .el-aside-dark {
          opacity: 0.5;
          background-color: var(--prev-tag-active-color);
          border-radius: 2px;
        }
        .el-aside {
          background-color: var(--prev-tag-active-color);
          border-radius: 2px;
        }
        .el-header {
          border-radius: 2px;
          background-color: var(--prev-color-seting-header);
        }
        .el-main {
          border-radius: 2px;
          border: 1px dashed var(--prev-color-primary);
          padding: 0;
          background-color: var(--prev-color-seting-main);
        }
      }
      .el-circular {
        border-radius: 2px;
        overflow: hidden;
        border: 1px solid transparent;
        transition: all 0.3s ease-in-out;
      }

      .layout-tips-warp,
      .layout-tips-warp-active {
        transition: all 0.3s ease-in-out;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        border: 1px solid;
        border-color: var(--prev-color-primary-light-5);
        border-radius: 100%;
        padding: 4px;
        .layout-tips-box {
          transition: inherit;
          width: 30px;
          height: 30px;
          z-index: 9;
          border: 1px solid;
          border-color: var(--prev-color-primary-light-5);
          border-radius: 100%;
          .layout-tips-txt {
            transition: inherit;
            position: relative;
            top: 5px;
            font-size: 12px;
            line-height: 1;
            letter-spacing: 2px;
            white-space: nowrap;
            color: var(--prev-color-primary-light-5);
            text-align: center;
            transform: rotate(30deg);
            left: -1px;
            background-color: var(--prev-color-seting-main);
            width: 32px;
            height: 17px;
            line-height: 17px;
          }
        }
      }
      .layout-tips-warp-active {
        border: 1px solid;
        border-color: var(--prev-color-primary);
        .layout-tips-box {
          border: 1px solid;
          border-color: var(--prev-color-primary);
          .layout-tips-txt {
            color: var(--prev-color-primary) !important;
            background-color: var(--prev-color-seting-main) !important;
          }
        }
      }
      &:hover {
        .layout-tips-warp {
          transition: all 0.3s ease-in-out;
          border-color: var(--prev-color-primary);
          .layout-tips-box {
            transition: inherit;
            border-color: var(--prev-color-primary);
            .layout-tips-txt {
              transition: inherit;
              color: var(--prev-color-primary) !important;
              background-color: var(--prev-color-seting-main) !important;
            }
          }
        }
      }
    }
  }
  .copy-config {
    margin: 10px 0;
    .copy-config-btn {
      width: 100%;
      margin-top: 15px;
    }
    .copy-config-btn-reset {
      width: 100%;
      margin: 10px 0 0;
    }
  }
}
</style>
