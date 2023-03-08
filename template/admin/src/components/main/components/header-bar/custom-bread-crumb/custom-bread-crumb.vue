<template>
  <div class="custom-bread-crumb">
    <Breadcrumb :style="{ fontSize: `${fontSize}px` }">
      <BreadcrumbItem
        v-for="(item, index) in list"
        :key="index"
        :to="index !== 1 ? item.path : ''"
        v-if="listLast[0].path !== homePath"
      >
        <common-icon style="margin-right: 4px" :type="item.icon || ''" />
        {{ item.title }}
      </BreadcrumbItem>
      <BreadcrumbItem :to="listLast[0].path" v-if="listLast[0].path === homePath">
        <common-icon style="margin-right: 4px" :type="listLast[0].icon || ''" />
        {{ listLast[0].title }}
      </BreadcrumbItem>
      <BreadcrumbItem v-else :to="listLast[0].path">
        <common-icon style="margin-right: 4px" :type="listLast[0].icon || ''" />
        {{ listLast[0].title }}
      </BreadcrumbItem>
    </Breadcrumb>
  </div>
</template>
<script>
import { showTitle } from '@/libs/util';
import CommonIcon from '_c/common-icon';
import settings from '@/setting';
import './custom-bread-crumb.less';
export default {
  name: 'customBreadCrumb',
  components: {
    CommonIcon,
  },
  props: {
    list: {
      type: Array,
      default: () => [],
    },
    listLast: {
      type: Array,
      default: () => [],
    },
    collapsed: Boolean,
    fontSize: {
      type: Number,
      default: 14,
    },
    showIcon: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      homePath: settings.routePre + '/home',
    };
  },
  methods: {
    showTitle(item) {
      return showTitle(item, this);
    },
    isCustomIcon(iconName) {
      return iconName.indexOf('_') === 0;
    },
    getCustomIconName(iconName) {
      return iconName.slice(1);
    },
  },
};
</script>
<style lang="less">
.ivu-breadcrumb-item-link {
  font-weight: 400;
  .ivu-icon {
    line-height: 14px;
  }
}
</style>
