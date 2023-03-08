<template>
  <div class="sider-menu" @click="handleChange">
    <a type="text" :class="['sider-trigger-a', menuCollapse ? 'menuCollapse' : '']"
      ><i class="iconfont" color="#fff" :class="menuCollapse ? 'iconcaidanzhankai' : 'iconcaidanshouqi'"></i
    ></a>
  </div>
</template>
<script>
import { mapState } from 'vuex';
export default {
  name: 'siderTrigger',
  props: {
    // collapsed: Boolean,
    icon: {
      type: String,
      default: 'navicon-round',
    },
    size: {
      type: Number,
      default: 18,
    },
  },
  computed: {
    ...mapState('layout', ['menuCollapse']),
  },
  data() {
    return {};
  },
  methods: {
    handleChange() {
      this.$emit('on-change', !this.menuCollapse);
      this.$store.commit('layout/changeCol', !this.menuCollapse);
    },
  },
};
</script>
<style lang="less" scoped>
.sider-menu {
  height: 50px;
  // border-top: 1px solid #f2f2f2;
  color: #515a6e;
  padding: 0 16px;
  display: flex;
  justify-content: center;
  align-items: center;
}
@import './sider-trigger.less';
.sider-trigger-a {
  height: 50px;
  color: rgba(0, 0, 0, 0.65);
}
.iconcaidanshouqi {
  font-size: 16px;
}
</style>
