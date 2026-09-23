<template>
  <el-dialog v-model="visible" width="1024px" title="选择视频" :close-on-click-modal="false">
    <uploaFile v-if="visible" :isChoice="isMore" @getVideo="getImage" :gridBtn="gridBtn" :gridPic="gridPic"></uploaFile>
  </el-dialog>
</template>

<script setup>
// 命令式单例弹窗：model.js 调用 expose 的 open() 驱动（script setup 绑定不可从外部赋值）
import { computed, ref } from 'vue';
import uploaFile from './index.vue';

const isChoice = ref('');
const visible = ref(false);
const callback = ref(function () {});
const gridBtn = ref({
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
});
const gridPic = ref({
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
});
const more = ref(false);
// model.js 全局 $videoModal 兼容字段
const modelName = ref('');
const booleanVal = ref('');

const isMore = computed(() => (more.value ? 'more' : 'one'));

// model.js 命令式调用入口（script setup 绑定不可从实例外部直接赋值，须经 expose 的方法驱动）
function open(options = {}) {
  visible.value = true;
  if (options.callback) callback.value = options.callback;
  more.value = !!options.isMore;
  if (options.modelName !== undefined) modelName.value = options.modelName;
  if (options.boolean !== undefined) booleanVal.value = options.boolean;
}
defineExpose({ open });
function handleClose() {
  visible.value = false;
  callback.value(visible.value);
}
function getImage(img) {
  callback.value(img);
  visible.value = false;
}
</script>
<style lang="scss" scoped></style>
