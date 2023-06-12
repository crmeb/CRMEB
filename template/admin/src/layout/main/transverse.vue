<template>
  <el-container class="layout-container flex-center layout-backtop">
    <Headers />
    <Mains />
    <el-backtop target=".layout-backtop .el-main .el-scrollbar__wrap"></el-backtop>
  </el-container>
</template>

<script>
import Headers from '@/layout/component/header.vue';
import Mains from '@/layout/component/main.vue';
export default {
  name: 'layoutTransverse',
  components: { Headers, Mains },
};
</script>
