<template>
  <div class="article-manager">
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">系统设置</span>
        <div>
          <el-tabs v-model="currentTab">
            <el-tab-pane label="公众号配置" name="onsale" />
            <el-tab-pane label="配置" name="forsale" />
          </el-tabs>
        </div>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" v-if="currentTab === 'onsale'">
      <!--<form-create  :rule="Array.from(FromData.rules)" @submit="onSubmit" ></form-create>-->
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({ name: 'setApp' });

const FromData = ref(null);
const currentTab = ref('');
</script>

<style scoped></style>
