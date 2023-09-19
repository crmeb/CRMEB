<template>
  <div>
    <el-card>
      <news-category :isShow="isShow"></news-category>
    </el-card>
  </div>
</template>

<script>
import newsCategory from '@/components/newsCategory/index';
export default {
  name: 'newsCategoryIndex',
  data() {
    return {
      isShow: true,
    };
  },
  components: {
    newsCategory,
  },
};
</script>
