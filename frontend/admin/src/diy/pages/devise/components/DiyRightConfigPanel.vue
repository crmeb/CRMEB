<template>
  <div class="right-box">
    <div class="mConfig-item" style="background-color: #fff" v-for="(item, key) in items" :key="key">
      <component
        v-if="canRenderRightConfig(item)"
        :is="resolveComp(item.configName)"
        :key="getRightConfigKey(item)"
        @config="$emit('config', $event)"
        :activeIndex="activeIndex"
        :num="item.num"
        :index="key"
      ></component>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: { type: Array, required: true },
  activeIndex: { type: Number, required: true },
  resolveComp: { type: Function, required: true },
  getRightConfigKey: { type: Function, required: true },
  canRenderRightConfig: { type: Function, required: true },
});

defineEmits(['config']);
</script>
