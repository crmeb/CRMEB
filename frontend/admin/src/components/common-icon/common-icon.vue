<template>
  <component :is="iconType" :type="iconName" :color="iconColor" :size="iconSize" />
</template>

<script setup>
import { computed } from 'vue';
import Icons from '_c/icons';

defineOptions({ name: 'CommonIcon' });

const props = defineProps({
  type: {
    type: String,
    required: true,
  },
  color: String,
  size: Number,
});

const iconType = computed(() => (props.type.indexOf('_') === 0 ? Icons : 'Icon'));
const iconName = computed(() => (iconType.value === 'Icons' ? getCustomIconName(props.type) : props.type));
const iconSize = computed(() => props.size || (iconType.value === 'Icons' ? 12 : undefined));
const iconColor = computed(() => props.color || '');

function getCustomIconName(iconName) {
  return iconName.slice(1);
}
</script>

<style></style>
