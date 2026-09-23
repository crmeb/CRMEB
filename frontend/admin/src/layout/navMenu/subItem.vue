<!--
 * @Author: From-wh from-wh@hotmail.com
 * @Date: 2023-03-09 15:45:51
 * @FilePath: /admin/src/layout/navMenu/subItem.vue
 * @Description:
-->
<template>
  <div>
    <template v-for="val in chil" :key="val.path">
      <el-sub-menu :index="val.path" v-if="val.is_show && val.children && val.children.length > 0">
        <template #title>
          <menu-icon :icon="val.icon" />
          <span>{{ $t(val.title) }}</span>
        </template>
        <sub-item :chil="val.children" />
      </el-sub-menu>
      <template v-else-if="val.is_show">
        <el-menu-item :index="val.path">
          <template v-if="!val.isLink || (val.isLink && val.isIframe)">
            <menu-icon :icon="val.icon" />
            <span>{{ $t(val.title) }}</span>
          </template>
          <template v-else>
            <a :href="val.isLink" target="_blank">
              <menu-icon :icon="val.icon" />
              {{ $t(val.title) }}
            </a>
          </template>
        </el-menu-item>
      </template>
    </template>
  </div>
</template>

<script setup>
import MenuIcon from '@/components/menuIcon/index.vue';

defineOptions({
  // 递归组件：name 必须保留（Vue3 script setup 通过文件名 subItem.vue 支持自引用）
  name: 'subItem',
});

defineProps({
  chil: {
    type: Array,
    default() {
      return [];
    },
  },
});
</script>
