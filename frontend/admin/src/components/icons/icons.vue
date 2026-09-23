<template>
  <i :class="`iconfont icon-${type}`" :style="styles"></i>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ name: 'Icons' });

const props = defineProps({
  type: {
    type: String,
    required: true,
  },
  color: {
    type: String,
    default: '#5c6b77',
  },
  size: {
    type: Number,
    default: 16,
  },
});

const styles = computed(() => ({
  fontSize: `${props.size}px`,
  color: props.color,
}));
</script>

<style></style>
