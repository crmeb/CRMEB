<template>
  <div class="icon-style-settings">
    <el-form-item label="图标颜色">
      <div class="row">
        <el-color-picker v-model="curComponent.propValue.color" @change="onChange" show-alpha></el-color-picker>
        <el-input
          v-model="curComponent.propValue.color"
          placeholder="请输入颜色"
          @change="onChange"
          style="margin-left: 10px; flex: 1"
        ></el-input>
        <span
          class="reset-color"
          @click="
            curComponent.propValue.color = '#000000';
            onChange();
          "
          >重置</span
        >
      </div>
    </el-form-item>
    <el-form-item label="图标大小">
      <div class="row">
        <el-slider
          v-model="curComponent.propValue.size"
          :max="100"
          :min="12"
          @change="onChange"
          style="flex: 1; margin-right: 10px"
        ></el-slider>
        <el-input-number
          v-model="curComponent.propValue.size"
          :max="100"
          :min="12"
          @change="onChange"
          style="width: 90px"
        ></el-input-number>
      </div>
    </el-form-item>
    <el-form-item label="图标位置">
      <el-radio-group v-model="curComponent.propValue.iconAlign" @change="onChange">
        <el-radio-button label="flex-start" value="flex-start"
          ><span class="iconfont iconzuoduiqi"></span
        ></el-radio-button>
        <el-radio-button label="center" value="center"><span class="iconfont iconjuzhongduiqi"></span></el-radio-button>
        <el-radio-button label="flex-end" value="flex-end"><span class="iconfont iconyouduiqi"></span></el-radio-button>
      </el-radio-group>
    </el-form-item>
    <!-- <el-form-item label="旋转角度">
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
    </el-form-item> -->
  </div>
</template>

<script setup>
defineOptions({ name: 'ConfigIconStyle' });

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
}
</style>
