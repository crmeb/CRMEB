<template>
  <error-content code="500" desc="抱歉，服务器出错了" :src="src" />
</template>

<script>
import error500 from '@/assets/images/error-page/error-500.svg';
import errorContent from '../error-content.vue';
export default {
  name: 'error_500',
  components: {
    errorContent,
  },
  data() {
    return {
      src: error500,
    };
  },
};
</script>
