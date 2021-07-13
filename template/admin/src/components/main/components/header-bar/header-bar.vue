<template>
  <div class="header-bar">
    <sider-trigger :collapsed="collapsed" icon="md-menu" @on-change="handleCollpasedChange"></sider-trigger>
    <span class="i-layout-header-trigger" @click="handleReload">
      <Icon type="ios-refresh" />
    </span>
    <custom-bread-crumb show-icon style="margin-left: 30px;" :list="breadCrumbList" :listLast="crumbPast" :collapsed="collapsed"></custom-bread-crumb>
    <div class="custom-content-con">
      <slot></slot>
    </div>
  </div>
</template>
<style scoped lang="less">
  .ivu-icon-ios-refresh{
     color: #999 !important;
     font-size: 23px;
  }
  .i-layout-header-trigger{
    position: absolute;
    margin: 0 10px;
    cursor: pointer;
    font-size: 19px;
  }
  .i-layout-header-trigger:hover{
    color: #2D8cF0;
  }
</style>
<script>
    import siderTrigger from './sider-trigger'
    import customBreadCrumb from './custom-bread-crumb'
    import { R } from '@/libs/util'

    import './header-bar.less'
    export default {
        name: 'HeaderBar',
        components: {
            siderTrigger,
            customBreadCrumb
        },
        props: {
            collapsed: Boolean
        },
        computed: {
            breadCrumbList () {
                let openMenus = this.$store.state.menus.openMenus
                let menuList = this.$store.state.menus.menusName
                let allMenuList = R(menuList, [])
                let selectMenu = []
                if (allMenuList.length > 0) {
                    openMenus.forEach((i) => {
                        allMenuList.forEach((a) => {
                            if (i === a.path) {
                                selectMenu.push(a)
                            }
                        })
                    })
                }
                return selectMenu
                // return this.$store.state.app.breadCrumbList
            },
            crumbPast () {
                let that = this
                let menuList = that.$store.state.menus.menusName
                let allMenuList = R(menuList, [])
                let selectMenu = []
                if (allMenuList.length > 0) {
                    allMenuList.forEach((a) => {
                        if (that.$route.path === a.path) {
                            selectMenu.push(a)
                        }
                    })
                }
                console.log(selectMenu)
                return selectMenu
            }
        },
        mounted () {

        },
        methods: {
            handleCollpasedChange (state) {
                this.$emit('on-coll-change', state)
            },
            handleReload () {
                this.$emit('on-reload')
            }
        }
    }
</script>
