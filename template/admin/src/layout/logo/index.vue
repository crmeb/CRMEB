<template>
  <div
    class="layout-logo"
    v-if="$store.state.themeConfig.themeConfig.layout !== 'columns' && !$store.state.themeConfig.themeConfig.isCollapse"
    v-db-click
    @click="onThemeConfigChange"
  >
    <img v-if="maxLogo" class="layout-logo-medium-img" :src="maxLogo" />
  </div>
  <div class="layout-logo-size" v-else v-db-click @click="onThemeConfigChange">
    <img v-if="minLogo" class="layout-logo-size-img" :src="minLogo" />
  </div>
</template>

<script>
import { getLogo } from '@/api/common';

export default {
  name: 'layoutLogo',
  data() {
    return {
      minLogo: '',
      maxLogo: '',
    };
  },
  computed: {
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
    // 设置 logo 是否显示
    setShowLogo() {
      let { isCollapse, layout } = this.$store.state.themeConfig.themeConfig;
      return !isCollapse || layout === 'classic' || document.body.clientWidth < 1000;
    },
  },
  mounted() {
    this.getLogo();
  },
  methods: {
    // logo 点击实现菜单展开/收起
    onThemeConfigChange() {
      if (
        this.$store.state.themeConfig.themeConfig.layout == 'columns' &&
        !this.$store.state.menus.childMenuList.length &&
        this.$store.state.themeConfig.themeConfig.isCollapse
      )
        return;
      if (
        this.$store.state.themeConfig.themeConfig.layout === 'transverse' ||
        this.$store.state.themeConfig.themeConfig.layout === 'classic'
      )
        return false;
      this.$store.state.themeConfig.themeConfig.isCollapse = !this.$store.state.themeConfig.themeConfig.isCollapse;
    },
    getLogo() {
      getLogo().then((res) => {
        this.minLogo = res.data.logo_square;
        this.maxLogo = res.data.logo;
      });
    },
  },
};
</script>

<style scoped lang="scss">
.layout-logo {
  width: 180px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  //   box-shadow: 0px 1px 4px rgba(0, 21, 41, 2%);
  color: var(--prev-color-primary);
  font-size: 16px;
  cursor: pointer;
  animation: logoAnimation 0.3s ease-in-out;
  &:hover {
    span {
      opacity: 0.9;
    }
  }
  &-medium-img {
    width: 100%;
    height: 50px;
    margin-right: 5px;
    position: relative;
    top: 2px;
  }
}
.layout-logo-size {
  width: 50px;
  height: 50px;
  display: flex;
  cursor: pointer;
  margin: auto;

  &-img {
    width: 50px;
    height: 50px;
    margin: auto;
    animation: logoAnimation 0.3s ease-in-out;
  }
}
</style>
