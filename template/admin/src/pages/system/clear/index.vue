<template>
  <div>
    <div class="acea-row row-center clear_tit">
      <Button type="primary" @click="clearCache" class="mr20">清除缓存</Button>
      <Button type="primary" @click="clearlog">清除日志</Button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'clear',
  data() {
    return {
      delfromData: {},
    };
  },
  methods: {
    clearCache() {
      let delfromData = {
        title: '清除缓存',
        num: 0,
        url: `system/refresh_cache/cache`,
        method: 'get',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    clearlog() {
      let delfromData = {
        title: '清除日志',
        num: 0,
        url: `system/refresh_cache/log`,
        method: 'get',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.clear_tit
    margin-top 150px
</style>
