<template>
  <div>
    <el-card>
      <news-category :isShow="isShow"></news-category>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import newsCategory from '@/components/newsCategory/index';

defineOptions({ name: 'newsCategoryIndex' });

const isShow = ref(true);
</script>
