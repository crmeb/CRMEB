<template>
  <div>
    <div class="layout-view-bg-white flex h100" v-loading="iframeLoading">
      <iframe :src="meta.isLink" frameborder="0" height="100%" width="100%" id="iframe"></iframe>
    </div>
  </div>
</template>

<script>
export default {
  name: 'layoutIfameView',
  props: {
    meta: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      iframeLoading: true,
    };
  },
  created() {
    this.bus.$on('onTagsViewRefreshRouterView', (path) => {
      if (this.$route.path !== path) return false;
      this.$emit('getCurrentRouteMeta');
    });
  },
  mounted() {
    this.initIframeLoad();
  },
  methods: {
    // 初始化页面加载 loading
    initIframeLoad() {
      this.$nextTick(() => {
        this.iframeLoading = true;
        const iframe = document.getElementById('iframe');
        if (!iframe) return false;
        iframe.onload = () => {
          this.iframeLoading = false;
        };
      });
    },
  },
};
</script>
