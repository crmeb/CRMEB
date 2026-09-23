<template>
  <div class="layout-footer mt15">
    <div class="layout-footer-warp">
      <iCopyright />
    </div>
  </div>
</template>

<script setup>
import iCopyright from '@/components/copyright';

defineOptions({ name: 'layoutFooter' });
</script>

<style scoped lang="scss">
.layout-footer {
  width: 100%;
  display: flex;
  &-warp {
    margin: auto;
    color: var(--prev-color-text-secondary);
    text-align: center;
  }
}
</style>
