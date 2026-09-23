<template>
  <el-slider v-model="draftValue" v-bind="$attrs" @change="commitValue" />
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_slider_deferred', inheritAttrs: false });

const props = defineProps({
  modelValue: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);
const draftValue = ref(props.modelValue);

// Keep the draft aligned with external changes without traversing the full config object.
watch(
  () => props.modelValue,
  (value) => {
    draftValue.value = value;
  },
);

function commitValue(value) {
  emit('update:modelValue', value ?? draftValue.value);
  emit('change', value ?? draftValue.value);
}
</script>
