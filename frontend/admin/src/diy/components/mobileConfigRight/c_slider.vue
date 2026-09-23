<template>
  <div class="slider-box" v-if="configData">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <c_slider_deferred
          v-model="configData.val"
          show-input
          :min="configData.min"
          :max="configData.max"
          @change="sliderChange"
        />
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import c_slider_deferred from './c_slider_deferred.vue';

defineOptions({ name: 'c_slider' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['getConfig']);

const configData = computed(() => props.configObj?.[props.configNme]);

function sliderChange(e) {
  emit('getConfig', e);
}
</script>

<style scoped lang="scss">
.c_row-item {
  margin: 0 15px 20px 15px;
  .label {
    color: #999;
    font-size: 12px;
  }
}
</style>
