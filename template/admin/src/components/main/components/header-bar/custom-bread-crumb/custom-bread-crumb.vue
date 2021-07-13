<template>
  <div class="custom-bread-crumb">
    <Breadcrumb :style="{fontSize: `${fontSize}px`}">
      <BreadcrumbItem v-for="item in list" :key="`${item.path}`" v-if="listLast[0].path !== '/admin/home/'">
        <common-icon style="margin-right: 4px;" :type="item.icon || ''"/>
        {{ item.title }}
      </BreadcrumbItem>
      <BreadcrumbItem v-if="listLast[0].path === '/admin/home/'">
        <common-icon style="margin-right: 4px;" :type="listLast[0].icon || ''"/>
        {{listLast[0].title}}
      </BreadcrumbItem>
      <BreadcrumbItem v-else>
        <common-icon style="margin-right: 4px;" :type="listLast[0].icon || ''"/>
        {{listLast[0].title}}
      </BreadcrumbItem>
    </Breadcrumb>
  </div>
</template>
<script>
    import { showTitle } from '@/libs/util'
    import CommonIcon from '_c/common-icon'
    import './custom-bread-crumb.less'
    export default {
        name: 'customBreadCrumb',
        components: {
            CommonIcon
        },
        props: {
            list: {
                type: Array,
                default: () => []
            },
            listLast: {
                type: Array,
                default: () => []
            },
            collapsed: Boolean,
            fontSize: {
                type: Number,
                default: 14
            },
            showIcon: {
                type: Boolean,
                default: false
            }
        },
        methods: {
            showTitle (item) {
                return showTitle(item, this)
            },
            isCustomIcon (iconName) {
                return iconName.indexOf('_') === 0
            },
            getCustomIconName (iconName) {
                return iconName.slice(1)
            }
        }
    }
</script>
<style lang="less">
  .custom-bread-crumb{
    margin-left: 46px!important;
  }
</style>
