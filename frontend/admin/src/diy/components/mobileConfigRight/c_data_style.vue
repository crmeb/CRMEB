<template>
  <div>
    <div v-if="configObj">
      <c_title :configObj="configObj" title="数据样式"></c_title>
      <!-- Background Config -->
      <div v-if="configObj.componentBgDataConfig">
        <c_radio :configObj="configObj" configNme="componentBgDataConfig"></c_radio>
        <div v-if="configObj.componentBgDataConfig.tabVal == 0">
          <c_bg_color :configObj="configObj.componentBgDataConfig" configNme="colorConfig"></c_bg_color>
          <c_radio :configObj="configObj.componentBgDataConfig" configNme="colorDirection"></c_radio>
        </div>
        <div v-if="configObj.componentBgDataConfig.tabVal == 1">
          <c_upload_img :configObj="configObj.componentBgDataConfig" configNme="imageConfig"></c_upload_img>
        </div>
      </div>
      <c_fillet :configObj="configObj" configNme="filletDataConfig" v-if="configObj.filletDataConfig"></c_fillet>
      <c_margin_style
        :configObj="configObj"
        configNme="marginDataConfig"
        v-if="configObj.marginDataConfig"
      ></c_margin_style>
      <c_margin_style
        :configObj="configObj"
        configNme="paddingDataConfig"
        v-if="configObj.paddingDataConfig"
      ></c_margin_style>
      <!-- Border Config -->
      <div v-if="configObj.borderDataConfig">
        <c_radio :configObj="configObj" configNme="borderDataConfig"></c_radio>
        <div v-if="configObj.borderDataConfig.tabVal == 1">
          <c_radio :configObj="configObj.borderDataConfig" configNme="styleConfig"></c_radio>
          <c_slider :configObj="configObj.borderDataConfig" configNme="widthConfig"></c_slider>
          <c_bg_color :configObj="configObj.borderDataConfig" configNme="colorConfig"></c_bg_color>
        </div>
      </div>
      <!-- Shadow Config -->
      <div v-if="configObj.shadowDataConfig">
        <c_radio :configObj="configObj" configNme="shadowDataConfig"></c_radio>
        <div v-if="configObj.shadowDataConfig.tabVal == 1">
          <c_bg_color :configObj="configObj.shadowDataConfig" configNme="colorConfig"></c_bg_color>
          <c_slider :configObj="configObj.shadowDataConfig" configNme="xConfig"></c_slider>
          <c_slider :configObj="configObj.shadowDataConfig" configNme="yConfig"></c_slider>
          <c_slider :configObj="configObj.shadowDataConfig" configNme="blurConfig"></c_slider>
          <c_slider :configObj="configObj.shadowDataConfig" configNme="spreadConfig"></c_slider>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import c_title from './c_title';
import c_bg_color from './c_bg_color';
import c_margin_style from './c_margin_style';
import c_fillet from './c_fillet';
import c_slider from './c_slider';
import c_radio from './c_radio';
import c_upload_img from './c_upload_img';

defineOptions({ name: 'c_data_style' });

defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});
</script>
