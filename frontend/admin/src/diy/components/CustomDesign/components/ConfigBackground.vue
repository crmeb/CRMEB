<template>
  <el-form-item label="背景色">
    <div style="margin-bottom: 10px">
      <el-radio-group v-model="curComponent.propValue.bgDirection" @change="onChange">
        <el-radio label="horizontal" value="horizontal">横向</el-radio>
        <el-radio label="vertical" value="vertical">纵向</el-radio>
        <el-radio label="left-diagonal" value="left-diagonal">左斜</el-radio>
        <el-radio label="right-diagonal" value="right-diagonal">右斜</el-radio>
      </el-radio-group>
    </div>
    <div class="row">
      <div class="iconfont iconlianjie"></div>
      <el-color-picker v-model="curComponent.propValue.backgroundColor" @change="onChange" show-alpha></el-color-picker>
      <el-input
        v-model="curComponent.propValue.backgroundColor"
        placeholder="请输入颜色"
        @change="onChange"
        style="margin-left: 10px; flex: 1"
      ></el-input>
      <span
        class="reset-btn"
        @click="
          curComponent.propValue.backgroundColor = '';
          onChange();
        "
        >重置</span
      >
    </div>
    <div class="row" style="margin-top: 10px">
      <el-color-picker v-model="curComponent.propValue.bgColor2" @change="onChange" show-alpha></el-color-picker>
      <el-input
        v-model="curComponent.propValue.bgColor2"
        placeholder="请输入颜色"
        @change="onChange"
        style="margin-left: 10px; flex: 1"
      ></el-input>
      <span
        class="reset-btn"
        @click="
          curComponent.propValue.bgColor2 = '';
          onChange();
        "
        >重置</span
      >
    </div>
  </el-form-item>
</template>

<script setup>
defineOptions({ name: 'ConfigBackground' });

defineProps({
  curComponent: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['change']);

function onChange() {
  emit('change');
}
</script>

<style scoped lang="scss">
.row {
  display: flex;
  align-items: center;
  position: relative;
  .iconlianjie {
    position: absolute;
    left: 11px;
    z-index: 11;
    top: 41px;
    transform: translateY(-50%);
  }
  .reset-btn {
    cursor: pointer;
    font-size: 12px;
    margin-left: 10px;
    color: var(--prev-color-primary);
    &:hover {
      color: var(--prev-color-primary-light-1);
    }
  }
}
</style>
