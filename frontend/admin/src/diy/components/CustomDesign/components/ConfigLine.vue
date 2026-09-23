<template>
  <div class="line-settings">
    <div class="section-title">线条设置</div>
    <el-form label-width="70px">
      <el-form-item label="线条方向">
        <el-radio-group v-model="curComponent.propValue.direction" @change="handleLineDirectionChange">
          <el-radio label="horizontal" value="horizontal">横向</el-radio>
          <el-radio label="vertical" value="vertical">竖向</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="线条样式">
        <el-radio-group v-model="curComponent.propValue.style" @change="onChange">
          <el-radio label="solid" value="solid">实线</el-radio>
          <el-radio label="dashed" value="dashed">虚线</el-radio>
          <el-radio label="dotted" value="dotted">点线</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="线条长度">
        <div class="row">
          <el-slider
            v-model="lineLength"
            :max="canvasWidth"
            :min="10"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="lineLength"
            :max="canvasWidth"
            :min="10"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="线条粗细">
        <div class="row">
          <el-slider
            v-model="lineThickness"
            :max="100"
            :min="1"
            @change="onChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="lineThickness"
            :max="100"
            :min="1"
            @change="onChange"
            style="width: 90px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="线条颜色">
        <div class="row">
          <el-color-picker v-model="curComponent.propValue.color" @change="onChange"></el-color-picker>
          <el-input
            v-model="curComponent.propValue.color"
            placeholder="请输入颜色"
            @change="onChange"
            style="margin-left: 10px; flex: 1"
          ></el-input>
          <el-button
            type="text"
            @click="
              curComponent.propValue.color = '#000000';
              onChange();
            "
            >重置</el-button
          >
        </div>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ name: 'ConfigLine' });

const props = defineProps({
  curComponent: {
    type: Object,
    required: true,
  },
  canvasWidth: {
    type: Number,
    default: 375,
  },
});

const emit = defineEmits(['change']);

const lineLength = computed({
  get() {
    if (props.curComponent.propValue.direction === 'horizontal') {
      return props.curComponent.style.width;
    } else {
      return props.curComponent.style.height;
    }
  },
  set(val) {
    if (props.curComponent.propValue.direction === 'horizontal') {
      props.curComponent.style.width = val;
    } else {
      props.curComponent.style.height = val;
    }
    onChange();
  },
});
const lineThickness = computed({
  get() {
    return props.curComponent.propValue.height;
  },
  set(val) {
    props.curComponent.propValue.height = val;
    if (props.curComponent.propValue.direction === 'horizontal') {
      props.curComponent.style.height = val;
    } else {
      props.curComponent.style.width = val;
    }
    onChange();
  },
});

function handleLineDirectionChange() {
  const { width, height } = props.curComponent.style;
  props.curComponent.style.width = height;
  props.curComponent.style.height = width;
  onChange();
}
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
  padding-top: 10px;
  border-top: 6px solid #f0f2f5;
}
</style>
