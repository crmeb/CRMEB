<template>
  <div class="side-menu-wrapper">
    <slot></slot>
    <Menu ref="menu" v-show="!collapsed" :active-name="activeName" :open-names="openMenus" :accordion="accordion" :theme="theme" width="auto" @on-open-change="openNameData" @on-select="handleSelect">
      <template v-for="item in menuList">
        <template v-if="item.children && item.children.length === 1">
          <side-menu-item v-if="showChildren(item)" :key="`menu-${item.path}`" :parent-item="item"></side-menu-item>
          <menu-item v-else :name="item.path" :key="`menu-${item.children[0].path}`"><common-icon :type="item.children[0].icon || ''"/><span>{{ item.children[0].title }}</span></menu-item>
        </template>
        <template v-else>
          <side-menu-item v-if="showChildren(item)" :key="`menu${item.path}`" :parent-item="item"></side-menu-item>
          <menu-item v-else :name="item.path" :key="`menu${item.path}`"><common-icon :type="item.icon || ''"/><span>{{ item.title }}</span></menu-item>
        </template>
      </template>
    </Menu>

    <div class="menu-collapsed" v-show="collapsed" :list="menuList">
      <template v-for="item in menuList">
        <collapsed-menu v-if="item.children && item.children.length > 0" @on-click="handleSelect" hide-title :root-icon-size="rootIconSize" :icon-size="iconSize" :theme="theme" :parent-item="item" :key="`drop-menu-${item.path}`"></collapsed-menu>
        <Tooltip transfer v-else :content="item.title" placement="right" :key="`drop-menu-${item.path}`">
          <a @click="handleSelect(getNameOrHref(item, true))" class="drop-menu-a" :style="{textAlign: 'center'}"><common-icon :size="rootIconSize" :color="textColor" :type="item.icon || (item.children && item.children[0].icon)"/></a>
        </Tooltip>
      </template>
    </div>

  </div>
</template>
<script>
    import SideMenuItem from './side-menu-item.vue'
    import CollapsedMenu from './collapsed-menu.vue'
    import { getUnion } from '@/libs/tools'
    import { mapState } from 'vuex'
    import mixin from './mixin'

    export default {
        name: 'SideMenu',
        mixins: [ mixin ],
        components: {
            SideMenuItem,
            CollapsedMenu
        },
        props: {
            menuList: {
                type: Array,
                default () {
                    return []
                }
            },
            collapsed: {
                type: Boolean
            },
            theme: {
                type: String,
                default: 'dark'
            },
            rootIconSize: {
                type: Number,
                default: 20
            },
            iconSize: {
                type: Number,
                default: 16
            },
            accordion: Boolean,
            activeName: {
                type: String,
                default: ''
            },
            openNames: {
                type: Array,
                default: () => []
            }
        },
        data () {
            return {
                openedNames: []
            }
        },
        methods: {
            handleSelect (name) {
                this.$emit('on-select', name)
                // this.$store.commit('menus/getopenMenus', this.openedNames)
            },
            getOpenedNamesByActiveName () {
                return this.$route.matched.map(item => item.path).filter(item => item !== name)
            },
            updateOpenName (name) {
                if (name === this.$config.homeName) this.openedNames = []
                else this.openedNames = this.getOpenedNamesByActiveName()
            },
            openNameData (n) {
                // this.openedNames = n
                // this.$store.commit('menus/getopenMenus', n)
            }
        },
        computed: {
            ...mapState('menus', [
                'openMenus'
            ]),
            textColor () {
                return this.theme === 'dark' ? '#fff' : '#495060'
            }
        },
        watch: {
            activeName (name) {
                if (this.accordion) this.openedNames = this.getOpenedNamesByActiveName()
                else this.openedNames = getUnion(this.openedNames, this.getOpenedNamesByActiveName())
            },
            openNames (newNames) {
                this.openedNames = newNames
            },
            openedNames () {
                this.$nextTick(() => {
                    this.$refs.menu.updateOpened()
                })
            }
        },
        mounted () {
            this.openedNames = getUnion(this.openedNames, this.getOpenedNamesByActiveName())
        }
    }
</script>
<style lang="less">
@import './side-menu.less';
.side-menu-wrapper a.drop-menu-a{
  padding: 15px!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer{
  background: rgb(0, 21, 41)!important;
  width: 170px!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .ivu-select-dropdown{
  background: rgb(0, 21, 41)!important;
  width: 170px!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu{
  min-width: unset!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item{
  padding: 9px 0 9px 30px!important;
  font-size: 13px !important;
  text-align: left;
}
.ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover{
  background-color: #2d8cf0 !important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .menu-title{
  padding-left: 0!important;
  color: rgba(225,225,225,0.7)!important;
  font-size: 13px!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover .menu-title{
  color: #fff!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .collased-menu-dropdown{
  padding: 9px 0 9px 30px!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .collased-menu-dropdown:hover{
  background-color: #2d8cf0 !important;
  color: #fff!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .collased-menu-dropdown:hover>.ivu-dropdown-rel>.drop-menu-a>.menu-title{
  color: #fff!important;
}
.ivu-select-dropdown.ivu-dropdown-transfer .collased-menu-dropdown:hover>.ivu-dropdown-rel>.drop-menu-a>.ivu-icon{
  color: #fff!important;
}
</style>
