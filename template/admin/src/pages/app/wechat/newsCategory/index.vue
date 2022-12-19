<template>
  <div style="height: 100%">
    <news-category :isShow="isShow"></news-category>
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
