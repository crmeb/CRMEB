<template>
  <div class="header-style-config">
    <div class="config-title">{{ configData.title }}</div>

    <!-- 字号 -->
    <div class="config-item">
      <span class="item-label">标题字号</span>
      <div class="slider-container">
        <c_slider_deferred v-model="configData.fontSize" show-input :min="12" :max="24" />
      </div>
    </div>

    <!-- 左侧文字颜色 -->
    <div class="config-item">
      <span class="item-label">标题文字色</span>
      <!-- <el-color-picker v-model="configData.leftColor" @change="handleChange"></el-color-picker> -->
      <div class="row slider-container">
        <el-color-picker v-model="configData.leftColor" @change="handleChange" show-alpha></el-color-picker>
        <el-input
          v-model="configData.leftColor"
          placeholder="请输入颜色"
          @change="handleChange"
          style="margin-left: 10px; flex: 1"
        ></el-input>
        <span
          class="reset-btn"
          @click="
            configData.leftColor = '#fff';
            handleChange();
          "
          >重置</span
        >
      </div>
    </div>
    <!-- 左侧字重 可选 300 500 正常 -->
    <div class="config-item">
      <span class="item-label">标题字重</span>
      <el-radio-group v-model="configData.leftWeight">
        <el-radio-button label="300" value="300"></el-radio-button>
        <el-radio-button label="500" value="500"></el-radio-button>
        <el-radio-button label="normal" value="normal">正常</el-radio-button>
      </el-radio-group>
    </div>
    <!-- 右侧文字颜色 -->
    <div class="config-item">
      <span class="item-label">按钮字号</span>
      <div class="slider-container">
        <c_slider_deferred v-model="configData.rightFontSize" show-input :min="12" :max="24" />
      </div>
    </div>
    <div class="config-item">
      <span class="item-label">按钮文字色</span>
      <!-- <el-color-picker v-model="configData.rightColor" @change="handleChange"></el-color-picker> -->
      <div class="row slider-container">
        <el-color-picker v-model="configData.rightColor" @change="handleChange" show-alpha></el-color-picker>
        <el-input
          v-model="configData.rightColor"
          placeholder="请输入颜色"
          @change="handleChange"
          style="margin-left: 10px; flex: 1"
        ></el-input>
        <span
          class="reset-btn"
          @click="
            configData.rightColor = '#fff';
            handleChange();
          "
          >重置</span
        >
      </div>
    </div>
    <div class="config-item">
      <span class="item-label">按钮字重</span>
      <el-radio-group v-model="configData.rightWeight">
        <el-radio-button label="300" value="300"></el-radio-button>
        <el-radio-button label="500" value="500"></el-radio-button>
        <el-radio-button label="normal" value="normal">正常</el-radio-button>
      </el-radio-group>
    </div>
    <!-- 上边距 -->
    <div class="config-item">
      <span class="item-label">上边距</span>
      <div class="slider-container">
        <c_slider_deferred v-model="configData.topPadding" show-input :min="0" />
      </div>
    </div>

    <!-- 下边距 -->
    <div class="config-item">
      <span class="item-label">下边距</span>
      <div class="slider-container">
        <c_slider_deferred v-model="configData.bottomPadding" show-input :min="0" />
      </div>
    </div>

    <!-- 左右边距 -->
    <div class="config-item">
      <span class="item-label">左右边距</span>
      <div class="slider-container">
        <c_slider_deferred v-model="configData.leftRightPadding" show-input :min="0" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import c_slider_deferred from './c_slider_deferred.vue';

defineOptions({ name: 'c_header_style' });

const props = defineProps({
  configNme: {
    type: String,
  },
  configObj: {
    type: Object,
    default: () => {},
  },
});

const emit = defineEmits(['getConfig']);

const configData = ref({
  title: '顶部样式',
  fontSize: 14,
  leftColor: '#333333',
  rightColor: '#333333',
  topPadding: 10,
  bottomPadding: 10,
  leftRightPadding: 12,
});

watch(
  () => props.configObj,
  (nVal) => {
    configData.value = nVal[props.configNme] || {
      title: '顶部样式',
      fontSize: 14,
      leftColor: '#333333',
      rightColor: '#333333',
      topPadding: 10,
      bottomPadding: 10,
      leftRightPadding: 12,
    };
  },
  { deep: true, immediate: true },
);

function handleChange() {
  emit('getConfig', configData.value);
}
</script>

<style lang="scss" scoped>
.header-style-config {
  padding: 12px 16px;

  .config-title {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 12px;
  }

  .config-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    .slider-container {
      width: 75%;
    }
    &:last-child {
      margin-bottom: 0;
    }

    .item-label {
      font-size: 13px;
      color: #999;
    }
  }
  .row {
    display: flex;
    align-items: center;
    position: relative;
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
}
</style>
