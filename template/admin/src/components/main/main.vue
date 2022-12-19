<template>
  <Layout style="height: 100%" class="main">
    <Sider
      hide-trigger
      collapsible
      :width="200"
      :collapsed-width="isMobile ? 0 : 80"
      v-model="collapsed"
      class="left-sider"
      :style="{ overflow: 'hidden' }"
      v-if="!headMenuNoShow"
    >
      <side-menu
        accordion
        ref="sideMenu"
        :active-name="$route.path"
        :collapsed="collapsed"
        @on-select="turnToPage"
        :menu-list="menuList"
      >
        <!-- 需要放在菜单上面的内容，如Logo，写在side-menu标签内部，如下 -->
        <div class="logo-con">
          <img v-show="!collapsed" :src="maxLogo" key="max-logo" />
          <img v-show="collapsed" :src="minLogo" key="min-logo" />
        </div>
      </side-menu>
    </Sider>
    <Layout>
      <Header class="header-con" v-if="!headMenuNoShow">
        <header-bar :collapsed="collapsed" @on-coll-change="handleCollapsedChange" @on-reload="handleReload">
          <user :message-unread-count="unreadCount" :user-avatar="userAvatar" />
          <language v-if="$config.useI18n" @on-lang-change="setLocal" style="margin-right: 10px" :lang="local" />
          <header-notice></header-notice>
          <fullscreen v-model="isFullscreen" style="margin-right: 10px" />
          <error-store
            v-if="$config.plugin['error-store'] && $config.plugin['error-store'].showInHeader"
            :has-read="hasReadErrorPage"
            :count="errorCount"
          ></error-store>
          <header-search></header-search>
        </header-bar>
      </Header>
      <Content class="main-content-con">
        <Layout class="main-layout-con">
          <div class="tag-nav-wrapper" v-if="!headMenuNoShow">
            <tags-nav :value="$route" @input="handleClick" :list="tagNavList" @on-close="handleCloseTag" />
          </div>
          <Content class="content-wrapper">
            <!-- <keep-alive :include="cacheList">
              <router-view v-if="reload" style="min-height: 600px" />
            </keep-alive> -->
            <keep-alive>
              <router-view v-if="$route.meta.keepAlive && reload" style="min-height: 600px"></router-view>
            </keep-alive>
            <router-view v-if="!$route.meta.keepAlive && reload" style="min-height: 600px"></router-view>
            <!-- <router-view v-if="reload" style="min-height: 600px" /> -->
            <!--<ABackTop :height="100" :bottom="80" :right="50" container=".content-wrapper"></ABackTop>-->
          </Content>
          <i-copyright />
        </Layout>
      </Content>
    </Layout>
    <!-- <div class="open-image" @click="clear" v-if="openImage">
      <img src="@/assets/images/wechat_demo.png" alt="" />
    </div> -->
  </Layout>
</template>
<script>
import iCopyright from '@/components/copyright';
import SideMenu from './components/side-menu';
import HeaderBar from './components/header-bar';
import TagsNav from './components/tags-nav';
import User from './components/user';
import ABackTop from './components/a-back-top';
import Fullscreen from './components/fullscreen';
import Language from './components/language';
import ErrorStore from './components/error-store';
import HeaderSearch from './components/header-search';
import HeaderNotice from './components/header-notice';

import Setting from '@/setting';
import iView from 'iview';
import { mapMutations, mapActions, mapGetters, mapState } from 'vuex';
import { getNewTagList, routeEqual, getMenuopen, getCookies, setCookies } from '@/libs/util';
import { getLogo } from '@/api/common';
import routers from '@/router/routers';
import minLogo from '@/assets/images/logo-small.png';
import maxLogo from '@/assets/images/logo.png';
import './main.less';
export default {
  name: 'Main',
  components: {
    SideMenu,
    HeaderBar,
    Language,
    TagsNav,
    Fullscreen,
    ErrorStore,
    User,
    ABackTop,
    iCopyright,
    HeaderSearch,
    HeaderNotice,
  },
  data() {
    return {
      collapsed: JSON.parse(getCookies('collapsed') || 'false'),
      minLogo,
      maxLogo,
      isFullscreen: false,
      reload: true,
      screenWidth: '',
      openImage: true,
      headMenuNoShow: false,
    };
  },
  computed: {
    ...mapGetters(['errorCount']),
    ...mapState('media', ['isMobile']),
    tagNavList() {
      return this.$store.state.app.tagNavList;
    },
    tagRouter() {
      return this.$store.state.app.tagRouter;
    },
    userAvatar() {
      return this.$store.state.user.avatarImgPath;
    },
    cacheList() {
      const list = [
        'ParentView',
        ...(this.tagNavList.length
          ? this.tagNavList.filter((item) => !(item.meta && item.meta.notCache)).map((item) => item.name)
          : []),
      ];
      return list;
    },
    menuList() {
      let menus = this.$store.state.menus.menusName;
      let newArray = [];
      menus.forEach((now, index) => {
        newArray[index] = now;
        if (newArray[index].children && now.children) {
          newArray[index].children = now.children.filter((item) => {
            return !item.auth;
          });
        }
      });
      return newArray;
      // return this.$store.state.menus.menusName
    },
    local() {
      return this.$store.state.app.local;
    },
    hasReadErrorPage() {
      return this.$store.state.app.hasReadErrorPage;
    },
    unreadCount() {
      return this.$store.state.user.unreadCount;
    },
  },
  methods: {
    ...mapMutations(['setBreadCrumb', 'setTagNavList', 'addTag', 'setLocal', 'setHomeRoute', 'closeTag']),
    ...mapActions(['handleLogin', 'getUnreadMessageCount']),
    turnToPage(route, all) {
      let { path, name, params, query } = {};
      if (typeof route === 'string' && !all) path = route;
      else if (typeof route === 'string' && all) name = route;
      else {
        path = route.path;
        name = route.name;
        params = route.params;
        query = route.query;
      }
      this.$router.push({
        path,
        name,
        params,
        query,
      });
    },
    handleCollapsedChange(state) {
      this.collapsed = state;
      setCookies('collapsed', state);
    },
    handleCloseTag(res, type, route) {
      if (type !== 'others') {
        if (type === 'all') {
          this.turnToPage(this.$config.homeName, 'all');
        } else {
          if (routeEqual(this.$route, route)) {
            this.closeTag(route);
          }
        }
      }
      if (res.length === 1 && res[0].name === this.$config.homeName) {
        this.$router.push({ name: this.$config.homeName });
      }
      this.setTagNavList(res);
    },
    handleClick(item) {
      this.turnToPage(item);
    },
    getLogo() {
      let logo = this.$store.state.userInfo.logo;
      let logoSmall = this.$store.state.userInfo.logoSmall;
      this.maxLogo = logo || this.maxLogo;
      this.minLogo = logoSmall || this.minLogo;
      getLogo().then((res) => {
        localStorage.setItem('ADMIN_TITLE', res.data.site_name);
        this.minLogo = res.data.logo_square;
        this.maxLogo = res.data.logo;
      });
    },
    handleReload() {
      this.reload = false;
      // if (Setting.showProgressBar) iView.LoadingBar.start()
      this.$nextTick(() => {
        this.reload = true;
        // if (Setting.showProgressBar) iView.LoadingBar.finish()
      });
    },
    clear() {
      this.openImage = false;
    },
  },
  watch: {
    $route(newRoute) {
      this.headMenuNoShow = this.$route.meta.fullScreen;
      let openNames = getMenuopen(newRoute, this.menuList);
      this.$store.commit('menus/setopenMenus', openNames);
      const { name, query, params, meta } = newRoute;
      this.addTag({
        route: { name, query, params, meta },
        type: 'push',
      });
      this.setBreadCrumb(newRoute);
      this.setTagNavList(getNewTagList(this.tagNavList, newRoute));
      this.$refs.sideMenu.updateOpenName(newRoute.path);
    },
  },
  mounted() {
    this.headMenuNoShow = this.$route.meta.fullScreen;
    this.getLogo();
    this.screenWidth = document.body.clientWidth;
    window.onresize = () => {
      return (() => {
        this.screenWidth = document.body.clientWidth;
        if (this.screenWidth <= 1060) {
          this.collapsed = true;
          setCookies('collapsed', true);
        } else {
          this.collapsed = false;
          setCookies('collapsed', false);
        }
      })();
    };

    /**
     * @description 初始化设置面包屑导航和标签导航
     */
    this.setTagNavList();
    this.setHomeRoute(routers);
    const { name, params, query, meta } = this.$route;
    this.addTag({
      route: { name, params, query, meta },
    });
    this.setBreadCrumb(this.$route);
    // 设置初始语言
    this.setLocal(this.$i18n.locale);
    // 如果当前打开页面不在标签栏中，跳到homeName页
    if (!this.tagNavList.find((item) => item.name === this.$route.name)) {
      this.$router.push({
        name: this.$config.homeName,
      });
    }
    // 获取未读消息条数
    this.getUnreadMessageCount();
  },
};
</script>
<style lang="less">
.main .header-con {
  padding: 0 20px 0 0px;
}
.main .logo-con img {
  height: 50px;
}
.main .tag-nav-wrapper {
  // height: 10px;
  background: unset;
}
.open-image {
  display: flex;
  align-items: center;
  justify-content: center;
  position: fixed;
  background-color: rgba(0, 0, 0, 0.6);
  height: 100%;
  width: 100%;
  top: 0;
  left: 0;
  z-index: 1000;
  img {
    width: 800px;
  }
}
</style>
