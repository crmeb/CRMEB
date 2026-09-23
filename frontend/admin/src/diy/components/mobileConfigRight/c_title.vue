<template>
  <div>
    <!-- <div class="setUpTop"></div> -->
    <div class="title" v-if="configData">{{ configData }}</div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_title' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
  title: {
    type: String,
    default: '',
  },
});

const defaults = ref({});
const configData = ref({});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.title || props.configObj[props.configNme];
  });
});
</script>

<style scoped lang="scss">
.setUpTop {
  height: 6px;
  background: rgb(240, 242, 245);
}
.title {
  padding: 20px 15px;
  font-size: 14px;
  color: #333333;
  border-top: 6px solid #f0f2f5;
  margin-top: -5px;
}
</style>
