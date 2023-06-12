<template>
  <el-container class="layout-container">
    <ColumnsAside />
    <div class="layout-columns-warp">
      <Asides />
      <el-container class="flex-center layout-backtop">
        <Headers v-if="isFixedHeader" />
        <el-scrollbar>
          <Headers v-if="!isFixedHeader" />
          <Mains />
        </el-scrollbar>
      </el-container>
    </div>
    <el-backtop target=".layout-backtop .el-scrollbar__wrap"></el-backtop>
  </el-container>
</template>

<script>
import Asides from '@/layout/component/aside.vue';
import Headers from '@/layout/component/header.vue';
import Mains from '@/layout/component/main.vue';
import ColumnsAside from '@/layout/component/columnsAside.vue';
export default {
  name: 'layoutColumns',
  components: { Asides, Headers, Mains, ColumnsAside },
  computed: {
    // 是否开启固定 header
    isFixedHeader() {
      return this.$store.state.themeConfig.themeConfig.isFixedHeader;
    },
  },
};
</script>
