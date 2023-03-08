<template>
  <div class="side-menu-wrapper">
    <!-- <sider-trigger :collapsed="collapsed" @on-change="handleCollpasedChange"></sider-trigger> -->
    <div class="side-menu-box" v-if="!menuCollapse">
      <div class="parent-menu">
        <slot></slot>
        <Menu
          ref="menu"
          class="parent-menus"
          :active-name="headerName"
          :open-names="openedNames"
          :accordion="accordion"
          :theme="theme"
          @on-open-change="openNameData"
          @on-select="handleSelect"
        >
          <template v-for="item in header">
            <template>
              <menu-item :name="item.path" :key="`menu${item.path}`"
                ><common-icon :type="item.icon || ''" /><span class="title">{{ item.title }}</span></menu-item
              >
            </template>
          </template>
        </Menu>
      </div>
      <div class="child-menu"  v-if="sider.length">
        <div class="cat-name">{{ oneMenuName }}</div>
        <Menu
          ref="childMenu"
          :active-name="activePath"
          :open-names="openNames"
          :accordion="accordion"
          :theme="theme"
          class="child-menus"
          @on-open-change="openChildNameData"
          @on-select="handleChildSelect"
        >
          <template v-for="item in sider">
            <template v-if="item.auth === undefined">
              <template v-if="item.children && item.children.length >= 1">
                <side-menu-item
                  v-if="showChildren(item)"
                  :key="`menu${item.path}`"
                  :parent-item="item"
                ></side-menu-item>
                <menu-item v-else :name="item.path" :key="`menu${item.path}`"
                  ><span class="title">{{ item.children[0].title }}</span></menu-item
                >
              </template>
              <template v-else>
                <side-menu-item
                  v-if="showChildren(item)"
                  :key="`menu${item.path}`"
                  :parent-item="item"
                ></side-menu-item>
                <menu-item v-else :name="item.path" :key="`menu${item.path}`"
                  ><span class="title">{{ item.title }}</span></menu-item
                >
              </template>
            </template>
          </template>
        </Menu>
      </div>
    </div>

    <div class="menu-collapsed" v-if="menuCollapse">
      <slot></slot>
      <template v-for="item in menusName">
        <collapsed-menu
          @on-click="collHandleSelect"
          :hide-title="true"
          :activeMenuPath="headerName"
          :root-icon-size="rootIconSize"
          :icon-size="iconSize"
          :theme="theme"
          :parent-item="item"
          :key="`drop-menu-${item.path}`"
        ></collapsed-menu>
        <!-- <Tooltip transfer v-else :content="item.title" placement="right" :key="`drop-menu-${item.path}`">
          <a
            @click="collHandleSelect(item)"
            class="drop-menu-a"
            :class="{ on: item.path == headerName }"
            :style="{ textAlign: 'center' }"
            ><common-icon :type="item.icon || (item.children && item.children[0].icon)" />
            <span class="title">{{ item.title }}</span>
          </a>
        </Tooltip> -->
      </template>
    </div>
  </div>
</template>
<script>
import SideMenuItem from './side-menu-item.vue';
import CollapsedMenu from './collapsed-menu.vue';
import { getUnion } from '@/libs/tools';
import { mapState, mapGetters } from 'vuex';
import mixin from './mixin';
import itemMixin from './item-mixin';
import { setCookies } from '@/libs/util';
import siderTrigger from '../header-bar/sider-trigger';

export default {
  name: 'SideMenu',
  mixins: [mixin, itemMixin],
  components: {
    SideMenuItem,
    CollapsedMenu,
    siderTrigger,
  },
  props: {
    // collapsed: {
    //   type: Boolean,
    // },
    theme: {
      type: String,
      default: 'light',
    },
    rootIconSize: {
      type: Number,
      default: 20,
    },
    iconSize: {
      type: Number,
      default: 16,
    },
    accordion: Boolean,
    // openNames: {
    //   type: Array,
    //   default: () => [],
    // },
  },
  data() {
    return {
      openedNames: [],
      childList: [],
      activeChildName: '',
      childOptions: [],
      // activePath: '',
      activeMenuPath: '',
      catName: '',
    };
  },
  computed: {
    ...mapState('menus', ['menusName']),
    ...mapState('menu', ['activePath', 'openNames', 'header', 'headerName', 'sider', 'oneMenuName']),
    ...mapGetters('menu', ['filterSider']),
    ...mapState('layout', ['menuCollapse']),
    textColor() {
      return this.theme === 'dark' ? '#fff' : '#495060';
    },
  },
  watch: {
    // openedNames() {
    //   this.$nextTick(() => {
    //     this.$refs.menu.updateOpened();
    //     this.$refs.childMenu.updateActiveName();
    //   });
    // },
    activePath() {
      this.$nextTick(() => {
        this.$refs.childMenu.updateOpened();
        this.$refs.childMenu.updateActiveName();
      });
    },
    menuCollapse(val) {
      if (!val) {
        this.$nextTick(() => {
          this.$refs.menu.updateOpened();
          this.$refs.childMenu.updateActiveName();
          this.$refs.childMenu.updateOpened();
        });
      }
    },
  },
  mounted() {},
  methods: {
    handleCollpasedChange(state) {
      this.$emit('on-coll-change', state);
    },
    handleSelect(name, type) {
      this.menusName.map((e) => {
        if (e.path === name) {
          this.turnToPage(this.getChilden(e));
        }
      });
    },
    // 递归处理顶部菜单问题
    getChilden(data) {
      if (data.children) {
        return this.getChilden(data.children[0]);
      }
      return data.path;
    },
    handleChildSelect(name) {
      this.turnToPage(name);
    },
    collHandleSelect(name) {
      this.turnToPage(name);
    },
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
    openNameData(n) {},
    openChildNameData(e) {},
  },
};
</script>
<style lang="less" scoped>
/deep/ .ivu-menu-vertical .ivu-menu-submenu-title-icon {
  right: 8px;
}
</style>
<style lang="less">
@import './side-menu.less';
.ivu-layout-sider {
  overflow: unset !important;
  transition: all 0.3s ease !important;
}
.ivu-menu {
  .side-menu-wrapper {
    position: relative;
  }
  .side-menu-wrapper a.drop-menu-a {
    padding: 1px !important;
  }

  .ivu-select-dropdown.ivu-dropdown-transfer {
    background: rgb(0, 21, 41) !important;
    width: 170px !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .ivu-select-dropdown {
    background: rgb(0, 21, 41) !important;
    width: 170px !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu {
    min-width: unset !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item {
    padding: 9px 0 9px 30px !important;
    font-size: 13px !important;
    text-align: left;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover {
    background-color: #2d8cf0 !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .menu-title {
    padding-left: 0 !important;
    color: rgba(225, 225, 225, 0.7) !important;
    font-size: 13px !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover .menu-title {
    color: #fff !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .collased-menu-dropdown {
    padding: 9px 0 9px 30px !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer .collased-menu-dropdown:hover {
    background-color: #2d8cf0 !important;
    color: #fff !important;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer
    .collased-menu-dropdown:hover
    > .ivu-dropdown-rel
    > .drop-menu-a
    > .menu-title {
    color: #fff !important;
    font-size: 14px;
    line-height: 14px;
  }
  .ivu-select-dropdown.ivu-dropdown-transfer
    .collased-menu-dropdown:hover
    > .ivu-dropdown-rel
    > .drop-menu-a
    > .ivu-icon {
    color: #fff !important;
  }
}

.side-menu-box {
  display: flex;
  .drop-menu-a {
    font-size: 14px;
    line-height: 14px;
    display: flex;
    align-items: center;
    .ivu-icon {
      font-size: 14px;
      margin-right: 8px;
    }
  }
  .parent-menu {
    z-index: 9;
    width: @side-width;
    background-color: @side-dark-theme !important;
    height: 100vh;
    .parent-menus {
      width: @side-width !important;
    }
    .ivu-menu {
      color: #fff !important;
    }
    .ivu-menu-light {
      background-color: unset;
    }
    .ivu-menu-item-selected {
      background-color: #1890ff !important;
      color: #fff !important;
    }

    .ivu-menu-vertical .ivu-menu-item:hover {
      color: #1890ff !important;
      background-color: rgba(24, 144, 255, 0.1) !important;
      .ivu-icon {
        color: #1890ff !important;
      }
    }
    .ivu-menu-item.ivu-menu-item-active:hover {
      background-color: #1890ff !important;
      color: #fff !important;
      .ivu-icon {
        color: #fff !important;
      }
    }
    .ivu-menu-vertical .ivu-menu-item {
      padding: 18px 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      .title {
        font-size: 14px;
        line-height: 14px;
      }
    }
    .ivu-menu-vertical .ivu-menu-item:first-child {
      // margin-top: 8px;
    }
  }
  .child-menu {
    z-index: 9;
    box-shadow: 2px 0px 4px 0px rgba(0, 0, 0, 0.06);
    transition: width .2s ease .2s;
    overflow: hidden;
    .child-menus {
      width: @side-child-width !important;
    }
    .ivu-divider {
      height: 50px;
      line-height: 50px;
      margin: 0;
      width: @side-child-width !important;
    }
    .ivu-menu-vertical .ivu-menu-item {
      height: @child-menu-height;
      display: flex;
      align-items: center;
      .title {
        font-size: 14px;
        line-height: 14px;
      }
    }
    .ivu-menu-submenu {
      .ivu-menu-item {
        padding-left: 38px !important;
      }
      .ivu-menu-submenu {
        .ivu-menu-submenu-title {
          padding-left: 38px !important;
        }
        .ivu-menu-item {
          padding-left: 52px !important;
        }
      }
    }
    .ivu-menu-vertical .ivu-menu-submenu-title {
      font-size: 14px;
      padding: 16px 24px;
      line-height: 16px;
    }
    .cat-name {
      width: 100%;
      font-size: 16px;
      height: 50px;
      line-height: 20px;
      font-weight: 600;
      color: #606266;
      padding: 15px 23px;
      text-align: left;
      white-space: nowrap;
      border-bottom: 1px solid #f2f2f2;
    }
  }
  > .ivu-menu {
    padding: 8px;
  }

  .ivu-menu-vertical.ivu-menu-light:after {
    width: 0px !important;
  }
  .ivu-menu-light.ivu-menu-vertical .ivu-menu-item-active:not(.ivu-menu-submenu):after {
    width: 0px;
  }
}
/deep/ .ivu-select-dropdown {
  left: 95px !important;
}
.menu-collapsed {
  width: @side-width;
  .side-menu-wrapper a.drop-menu-a {
    display: flex;
    align-items: center;
    justify-content: center;
    color: @side-dark-theme-font-color;
    width: @side-width !important;
  }
  .drop-menu-a:hover {
    color: #1890ff !important;
    background-color: rgba(24, 144, 255, 0.1) !important;
    .ivu-icon {
      color: #1890ff !important;
    }
  }
  .drop-menu-a.on:hover {
    background-color: #1890ff !important;
    color: #fff !important;
    .ivu-icon {
      color: #fff !important;
    }
  }
  .drop-menu-a.on {
    background-color: #1890ff !important;
    color: #fff !important;
    .ivu-icon {
      color: #fff !important;
    }
  }
  .drop-menu-a {
    font-size: 14px;
    line-height: 14px;
    display: flex;
    padding: 18px 0;
    align-items: center;
    width: @side-width;
    flex-wrap: nowrap;
    .ivu-icon {
      font-size: 14px;
      margin-right: 6px;
    }
    .title {
      font-size: 14px;
      line-height: 14px;
      white-space: nowrap;
    }
  }

  .drop-menu-a {
    padding: 0;
  }
  .collased-menu-dropdown * {
    font-size: 14px;
  }
}
</style>
