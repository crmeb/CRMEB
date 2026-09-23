<template>
  <div>
    <div v-if="configObj">
      <c_title :configObj="configObj" configNme="titleCurrency" v-if="configObj.titleCurrency"></c_title>
      <!-- <c_slider :configObj="configObj" configNme="zIndexConfig" v-if="configObj.zIndexConfig"></c_slider> -->
      <c_bg_color :configObj="configObj" configNme="moduleColor" v-if="configObj.moduleColor"></c_bg_color>
      <c_bg_color :configObj="configObj" configNme="bgColor" v-if="configObj.bgColor"></c_bg_color>
      <!-- Background Config -->
      <div v-if="configObj.componentBgConfig">
        <!-- <c_title
          :configObj="configObj.componentBgConfig"
          configNme="title"
          v-if="configObj.componentBgConfig.title"
        ></c_title> -->
        <c_radio :configObj="configObj" configNme="componentBgConfig"></c_radio>
        <div v-if="configObj.componentBgConfig.tabVal == 0">
          <c_bg_color :configObj="configObj.componentBgConfig" configNme="colorConfig"></c_bg_color>
          <c_radio :configObj="configObj.componentBgConfig" configNme="colorDirection"></c_radio>
        </div>
        <div v-if="configObj.componentBgConfig.tabVal == 1">
          <c_upload_img :configObj="configObj.componentBgConfig" configNme="imageConfig"></c_upload_img>
        </div>
      </div>
      <c_fillet :configObj="configObj" configNme="fillet" v-if="configObj.fillet"></c_fillet>
      <c_bg_color :configObj="configObj" configNme="bottomBgColor" v-if="configObj.bottomBgColor"></c_bg_color>
      <c_bg_color :configObj="configObj" configNme="textColor" v-if="configObj.textColor"></c_bg_color>
      <c_margin_style :configObj="configObj" configNme="marginConfig" v-if="configObj.marginConfig"></c_margin_style>
      <c_margin_style :configObj="configObj" configNme="paddingConfig" v-if="configObj.paddingConfig"></c_margin_style>
      <!-- Border Config -->
      <div v-if="configObj.borderConfig">
        <c_radio :configObj="configObj" configNme="borderConfig"></c_radio>
        <div v-if="configObj.borderConfig.tabVal == 1">
          <c_radio :configObj="configObj.borderConfig" configNme="styleConfig"></c_radio>
          <c_slider :configObj="configObj.borderConfig" configNme="widthConfig"></c_slider>
          <c_bg_color :configObj="configObj.borderConfig" configNme="colorConfig"></c_bg_color>
        </div>
      </div>
      <!-- Shadow Config -->

      <div v-if="configObj.shadowConfig">
        <c_radio :configObj="configObj" configNme="shadowConfig"></c_radio>
        <div v-if="configObj.shadowConfig.tabVal == 1">
          <c_bg_color :configObj="configObj.shadowConfig" configNme="colorConfig"></c_bg_color>
          <c_slider :configObj="configObj.shadowConfig" configNme="xConfig"></c_slider>
          <c_slider :configObj="configObj.shadowConfig" configNme="yConfig"></c_slider>
          <c_slider :configObj="configObj.shadowConfig" configNme="blurConfig"></c_slider>
          <c_slider :configObj="configObj.shadowConfig" configNme="spreadConfig"></c_slider>
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

defineOptions({ name: 'c_common_style' });

defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});
</script>
