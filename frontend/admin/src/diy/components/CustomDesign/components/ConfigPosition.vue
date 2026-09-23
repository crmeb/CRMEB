<template>
  <div class="position-settings">
    <div class="section-title">位置设置</div>
    <el-form label-width="70px" label-position="left">
      <el-form-item label="X 坐标">
        <div class="row">
          <el-slider
            v-model="curComponent.style.left"
            :max="canvasWidth - curComponent.style.width"
            :min="0"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.style.left"
            :max="canvasWidth - curComponent.style.width"
            :min="0"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="Y 坐标">
        <div class="row">
          <el-slider
            v-model="curComponent.style.top"
            :max="canvasHeight - curComponent.style.height"
            :min="0"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.style.top"
            :max="canvasHeight - curComponent.style.height"
            :min="0"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="旋转角度">
        <div class="row">
          <el-slider
            v-model="curComponent.style.rotate"
            :max="360"
            :min="0"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="curComponent.style.rotate"
            :max="360"
            :min="0"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
defineOptions({ name: 'ConfigPosition' });

defineProps({
  curComponent: {
    type: Object,
    required: true,
  },
  canvasWidth: {
    type: Number,
    required: true,
  },
  canvasHeight: {
    type: Number,
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
}
</style>
