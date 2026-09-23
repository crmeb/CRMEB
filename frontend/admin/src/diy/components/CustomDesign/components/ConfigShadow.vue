<template>
  <div>
    <el-form-item label="阴影设置">
      <el-radio-group v-model="curComponent.propValue.showShadow" @change="onChange">
        <el-radio :label="false" :value="false">隐藏</el-radio>
        <el-radio :label="true" :value="true">显示</el-radio>
      </el-radio-group>
    </el-form-item>
    <template v-if="curComponent.propValue.showShadow">
      <el-form-item label="阴影颜色">
        <div class="row">
          <el-color-picker v-model="curComponent.propValue.shadowColor" @change="onChange" show-alpha></el-color-picker>
          <el-input
            v-model="curComponent.propValue.shadowColor"
            placeholder="请输入颜色"
            @change="onChange"
            style="margin-left: 10px; flex: 1"
          ></el-input>
          <span
            class="reset-color"
            @click="
              curComponent.propValue.shadowColor = '#000000';
              onChange();
            "
            >重置</span
          >
        </div>
      </el-form-item>
      <el-form-item label="X轴偏移">
        <div class="row">
          <el-slider
            v-model="curComponent.propValue.shadowX"
            :max="50"
            :min="-50"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.propValue.shadowX"
            :max="50"
            :min="-50"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="Y轴偏移">
        <div class="row">
          <el-slider
            v-model="curComponent.propValue.shadowY"
            :max="50"
            :min="-50"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.propValue.shadowY"
            :max="50"
            :min="-50"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="模糊半径">
        <div class="row">
          <el-slider
            v-model="curComponent.propValue.shadowBlur"
            :max="50"
            :min="0"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.propValue.shadowBlur"
            :max="50"
            :min="0"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="扩散半径">
        <div class="row">
          <el-slider
            v-model="curComponent.propValue.shadowSpread"
            :max="50"
            :min="-50"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.propValue.shadowSpread"
            :max="50"
            :min="-50"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
    </template>
  </div>
</template>

<script setup>
defineOptions({ name: 'ConfigShadow' });

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
</style>
