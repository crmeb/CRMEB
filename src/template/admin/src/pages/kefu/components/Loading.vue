<template>
  <div
    class="Loads acea-row row-center-wrapper"
    v-if="loading && !loaded"
    style="margin-top: .2rem;font-size: 12px"
  >
    <template v-if="loading">
      <div
        class="iconfontYI icon-jiazai loading acea-row row-center-wrapper"
      ></div>
      正在加载中
    </template>
    <template v-else>
      上拉加载更多
    </template>
  </div>
</template>

<script>
export default {
  name: "Loading",
  props: {
    loaded: Boolean,
    loading: Boolean
  },
  created() {
    //import('@/assets/js/media_750')
  }
};
</script>
