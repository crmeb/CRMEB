<template>
  <Submenu :name="parentItem.path">
    <template slot="title">
      <span>{{ parentItem.title }}</span>
    </template>
    <template v-for="item in children">
      <template v-if="item.auth === undefined">
        <template v-if="item.children && item.children.length === 1">
          <side-menu-item v-if="showChildren(item)" :key="`menu${item.path}`" :parent-item="item"></side-menu-item>
          <menu-item v-else :name="item.path" :key="`menu${item.children[0].path}`"
            ><span class="title">{{ item.children[0].title }}</span></menu-item
          >
        </template>
        <template v-else>
          <side-menu-item v-if="showChildren(item)" :key="`menu${item.path}`" :parent-item="item"></side-menu-item>
          <menu-item v-else :name="item.path" :key="`menu${item.path}`"
            ><span class="title">{{ item.title }}</span></menu-item
          >
        </template>
      </template>
    </template>
  </Submenu>
</template>
<script>
import mixin from './mixin';
import itemMixin from './item-mixin';
export default {
  name: 'SideMenuItem',
  mixins: [mixin, itemMixin],
};
</script>
