<template>
  <div>
    <el-form-item label="边框显示">
      <el-radio-group v-model="curComponent.propValue.showBorder" @change="onChange">
        <el-radio :label="false" :value="false">隐藏</el-radio>
        <el-radio :label="true" :value="true">显示</el-radio>
      </el-radio-group>
    </el-form-item>
    <template v-if="curComponent.propValue.showBorder">
      <el-form-item label="边框样式">
        <el-radio-group v-model="curComponent.propValue.borderStyle" @change="onChange">
          <el-radio label="solid" value="solid">实线</el-radio>
          <el-radio label="dashed" value="dashed">虚线</el-radio>
          <el-radio label="dotted" value="dotted">点状</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="边框粗细">
        <div class="row">
          <el-slider
            v-model="curComponent.propValue.borderWidth"
            :max="20"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.propValue.borderWidth"
            :max="20"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="边框颜色">
        <div class="row">
          <el-color-picker v-model="curComponent.propValue.borderColor" @change="onChange" show-alpha></el-color-picker>
          <el-input
            v-model="curComponent.propValue.borderColor"
            placeholder="请输入颜色"
            @change="onChange"
            style="margin-left: 10px; flex: 1"
          ></el-input>
          <span
            class="reset-color"
            @click="
              curComponent.propValue.borderColor = '#000000';
              onChange();
            "
            >重置</span
          >
        </div>
      </el-form-item>
    </template>
  </div>
</template>

<script setup>
defineOptions({ name: 'ConfigBorder' });

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
}
.section-title {
  font-size: 14px;
  color: #333;
  margin-bottom: 18px;
  margin-top: 10px;
  width: calc(100% + 30px);
  margin-left: -15px;
  padding-left: 15px;
  padding-top: 20px;
  border-top: 6px solid #f0f2f5;
}
</style>
