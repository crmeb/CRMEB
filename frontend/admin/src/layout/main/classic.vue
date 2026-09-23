<template>
  <el-container class="layout-container flex-center">
    <Headers />
    <el-container class="layout-mian-height-50">
      <Asides />
      <div class="flex-center layout-backtop">
        <TagsView v-if="getThemeConfig.isTagsview" />
        <Mains />
      </div>
    </el-container>
    <el-backtop target=".layout-backtop .el-main .el-scrollbar__wrap"></el-backtop>
  </el-container>
</template>

<script setup>
import { computed } from 'vue';
import Asides from '@/layout/component/aside.vue';
import Headers from '@/layout/component/header.vue';
import Mains from '@/layout/component/main.vue';
import TagsView from '@/layout/navBars/tagsView/tagsView.vue';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutClassic' });

const themeConfigStore = useThemeConfigStore();

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);
</script>
