<template>
  <div class="icon-style-config" v-if="configData">
    <!-- <div class="title">{{ configData.title || '图标样式' }}</div> -->

    <!-- 图标颜色 -->
    <div class="config-item" v-if="configData.color">
      <span class="item-label">{{ configData.color.title }}</span>
      <div class="color-box">
        <el-color-picker v-model="configData.color.color[0].item"></el-color-picker>
        <el-input class="input" v-model="configData.color.color[0].item" />
        <span class="reset" @click="configData.color.color[0].item = configData.color.default[0].item">重置</span>
      </div>
    </div>

    <!-- 图标大小 -->
    <div class="config-item" v-if="configData.size">
      <span class="item-label">{{ configData.size.title }}</span>
      <div class="slider-box">
        <c_slider_deferred
          v-model="configData.size.val"
          show-input
          :min="configData.size.min"
          :max="configData.size.max"
        />
      </div>
    </div>

    <!-- 图标位置 -->
    <div class="config-item" v-if="configData.position">
      <span class="item-label">{{ configData.position.title }}</span>
      <div class="radio-box">
        <el-radio-group v-model="configData.position.tabVal">
          <el-radio-button
            v-for="(item, index) in configData.position.tabList"
            :key="index"
            :label="index"
            :value="index"
          >
            <span class="iconfont" :class="item.icon"></span>
          </el-radio-button>
        </el-radio-group>
      </div>
    </div>

    <!-- 内边距 -->
    <div class="config-item" v-if="configData.padding">
      <span class="item-label">{{ configData.padding.title }}</span>
      <div class="slider-box">
        <c_slider_deferred
          v-model="configData.padding.val"
          show-input
          :min="configData.padding.min"
          :max="configData.padding.max"
        />
      </div>
    </div>

    <!-- 旋转角度 -->
    <div class="config-item" v-if="configData.rotate">
      <span class="item-label">{{ configData.rotate.title }}</span>
      <div class="slider-box">
        <c_slider_deferred
          v-model="configData.rotate.val"
          show-input
          :min="configData.rotate.min"
          :max="configData.rotate.max"
        />
      </div>
    </div>

    <!-- 阴影 -->
    <div class="config-item" v-if="configData.shadow">
      <span class="item-label">{{ configData.shadow.title }}</span>
      <el-radio-group v-model="configData.shadow.tabVal">
        <el-radio :label="item.val" :value="item.val" v-for="(item, index) in configData.shadow.tabList" :key="index">{{
          item.name
        }}</el-radio>
      </el-radio-group>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import c_slider_deferred from './c_slider_deferred.vue';

defineOptions({ name: 'c_icon_style' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const configData = ref(null);

watch(
  () => props.configObj,
  (nVal) => {
    configData.value = nVal[props.configNme];
  },
  { deep: true, immediate: true },
);

onMounted(() => {
  nextTick(() => {
    if (props.configObj) {
      configData.value = props.configObj[props.configNme];
    }
  });
});
</script>

<style scoped lang="scss">
.icon-style-config {
  padding-bottom: 20px;
  .title {
    padding: 20px 15px;
    font-size: 14px;
    color: #333333;
  }
  .config-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    .item-label {
      font-size: 12px;
      color: #999;
      width: 70px;
    }
    .color-box {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      flex: 1;
      .input {
        margin-left: 11px;
        width: 192px;
      }
      .reset {
        font-size: 12px;
        color: #1890ff;
        cursor: pointer;
        margin-left: 10px;
      }
    }
    .slider-box {
      width: 75%;
    }
    .radio-box {
      display: flex;
      .radio-item {
        width: 30px;
        height: 30px;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        &.active {
          border-color: #1890ff;
          color: #1890ff;
          background-color: #e6f7ff;
        }
        &:first-child {
          border-top-left-radius: 4px;
          border-bottom-left-radius: 4px;
        }
        &:last-child {
          border-top-right-radius: 4px;
          border-bottom-right-radius: 4px;
        }
        & + .radio-item {
          border-left: none;
        }
      }
    }
  }
}
</style>
