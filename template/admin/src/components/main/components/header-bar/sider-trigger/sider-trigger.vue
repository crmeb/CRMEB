<template>
  <a @click="handleChange" type="text" :class="['sider-trigger-a', collapsed ? 'collapsed' : '']"
    ><i class="iconfont" :class="collapsed ? 'iconcaidanzhankai' : 'iconcaidanshouqi'"></i
  ></a>
</template>
<script>
export default {
  name: 'siderTrigger',
  props: {
    collapsed: Boolean,
    icon: {
      type: String,
      default: 'navicon-round',
    },
    size: {
      type: Number,
      default: 18,
    },
  },
  methods: {
    handleChange() {
      this.$emit('on-change', !this.collapsed);
    },
  },
};
</script>
<style lang="less" scoped>
@import './sider-trigger.less';
.sider-trigger-a {
  padding: 14px 6px;
  margin-left: 10px;
  line-height: 16px;
}
.iconcaidanshouqi {
  font-size: 16px;
}
</style>
