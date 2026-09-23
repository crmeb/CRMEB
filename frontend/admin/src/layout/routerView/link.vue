<!--
 * @Author: From-wh from-wh@hotmail.com
 * @Date: 2023-03-09 15:45:51
 * @FilePath: /admin/src/layout/routerView/link.vue
 * @Description: 
-->
<template>
  <div class="layout-scrollbar layout-link-container">
    <div class="layout-view-bg-white flex layout-view-link">
      <div class="layout-link-warp">
        <i class="layout-link-icon iconfont icon-xingqiu"></i>
        <div class="layout-link-msg">页面 "{{ $t(meta.title) }}" 已在新窗口中打开</div>
        <el-button class="mt30" round v-db-click @click="onGotoFullPage">
          <i class="iconfont icon-lianjie"></i>
          <span>立即前往</span>
        </el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { verifyUrl } from '@/utils/toolsValidate';

defineOptions({ name: 'layoutLinkView' });

const props = defineProps({
  meta: {
    type: Object,
    default: () => {},
  },
});

// 立即前往
function onGotoFullPage() {
  const { origin, pathname } = window.location;
  // 原实现读取 this.isLink（组件上并无该属性，恒为 undefined），此处保持等价行为
  const isLink = props.isLink;
  if (verifyUrl(isLink)) window.open(isLink);
  else window.open(`${origin}${pathname}#${isLink}`);
}
</script>

<style scoped lang="scss">
.layout-link-container {
  .layout-link-warp {
    margin: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    i.layout-link-icon {
      position: relative;
      font-size: 100px;
      color: var(--prev-color-primary);
      &::after {
        content: '';
        position: absolute;
        left: 50px;
        top: 0;
        width: 15px;
        height: 100px;
        background: linear-gradient(
          rgba(255, 255, 255, 0.01),
          rgba(255, 255, 255, 0.01),
          rgba(255, 255, 255, 0.01),
          rgba(255, 255, 255, 0.05),
          rgba(255, 255, 255, 0.05),
          rgba(255, 255, 255, 0.05),
          rgba(235, 255, 255, 0.5),
          rgba(255, 255, 255, 0.05),
          rgba(255, 255, 255, 0.05),
          rgba(255, 255, 255, 0.05),
          rgba(255, 255, 255, 0.01),
          rgba(255, 255, 255, 0.01),
          rgba(255, 255, 255, 0.01)
        );
        transform: rotate(-15deg);
        animation: toRight 5s linear infinite;
      }
    }
    .layout-link-msg {
      font-size: 12px;
      color: var(--prev-bg-topBarColor);
      opacity: 0.7;
      margin-top: 15px;
    }
  }
}
</style>
