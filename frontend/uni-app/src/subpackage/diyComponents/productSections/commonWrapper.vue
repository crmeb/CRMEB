<template>
  <view :style="[bottomBgColor]">
    <view :style="[boxStyle]">
      <slot></slot>
    </view>
  </view>
</template>

<script setup>
import { computed } from "vue";

defineOptions({ name: "commonWrapper" });

const props = defineProps({
  config: {
    type: Object,
    default: () => ({}),
  },
});

const boxStyle = computed(() => {
      const config = props.config || {};
      const marginConfig = config.marginConfig || {
        val: 0,
        valList: [
          {
            val: 0,
          },
          {
            val: 0,
          },
          {
            val: 0,
          },
          {
            val: 0,
          },
        ],
      };
      const paddingConfig = config.paddingConfig || {
        val: 0,
        valList: [
          {
            val: 0,
          },
          {
            val: 0,
          },
          {
            val: 0,
          },
          {
            val: 0,
          },
        ],
      };
      const componentBgConfig = config.componentBgConfig;
      const borderConfig = config.borderConfig;
      const shadowConfig = config.shadowConfig;
      const fillet = config.fillet;
      const zIndexConfig = config.zIndexConfig;

      let style = {
        overflow: "hidden",
      };

      // Margin
      if (!marginConfig.isAll) {
        style["margin"] = `${marginConfig.val * 2}rpx`;
      } else {
        style["margin-top"] = `${marginConfig.valList[0].val * 2}rpx`;
        style["margin-bottom"] = `${marginConfig.valList[2].val * 2}rpx`;
        style["margin-left"] = `${marginConfig.valList[3].val * 2}rpx`;
        style["margin-right"] = `${marginConfig.valList[1].val * 2}rpx`;
      }

      // Padding
      if (!paddingConfig.isAll) {
        style["padding"] = `${paddingConfig.val * 2}rpx`;
      } else {
        style["padding-top"] = `${paddingConfig.valList[0].val * 2}rpx`;
        style["padding-bottom"] = `${paddingConfig.valList[2].val * 2}rpx`;
        style["padding-left"] = `${paddingConfig.valList[3].val * 2}rpx`;
        style["padding-right"] = `${paddingConfig.valList[1].val * 2}rpx`;
      }

      // Background
      if (componentBgConfig) {
        if (componentBgConfig.tabVal === 0) {
          // Color
          const colorConfig = componentBgConfig.colorConfig;
          const colorDirection = componentBgConfig.colorDirection;

          if (colorConfig && colorConfig.color) {
            const colors = colorConfig.color.map((c) => c.item);
            if (colors.length > 1) {
              let deg = "90deg";
              if (colorDirection) {
                switch (colorDirection.tabVal) {
                  case 0:
                    deg = "90deg";
                    break;
                  case 1:
                    deg = "180deg";
                    break;
                  case 2:
                    deg = "135deg";
                    break;
                  case 3:
                    deg = "200deg";
                    break;
                }
              }
              style.background = `linear-gradient(${deg}, ${colors[0]} 0%, ${colors[1]} 100%)`;
            } else {
              style.background = colors[0];
            }
          }
        } else if (componentBgConfig.tabVal === 1) {
          // Image
          const imageConfig =
            componentBgConfig.imageConfig || componentBgConfig.imgConfig;
          if (imageConfig && imageConfig.url) {
            style["background-image"] = `url(${imageConfig.url})`;
            style["background-repeat"] = "no-repeat";
            style["background-size"] = "cover";
            style["background-position"] = "center";
          }
        }
      }
      // Border
      if (borderConfig && borderConfig.tabVal) {
        const color = borderConfig.colorConfig
          ? borderConfig.colorConfig.color[0].item
          : "#000";
        const width = borderConfig.widthConfig
          ? borderConfig.widthConfig.val
          : 1;
        // Style: solid, dashed, dotted
        let borderStyle = "solid";
        if (borderConfig.styleConfig) {
          const styleVal = borderConfig.styleConfig.tabVal;
          if (styleVal === 1) borderStyle = "dashed";
          if (styleVal === 2) borderStyle = "dotted";
        }
        style["border"] = `${width * 2}rpx ${borderStyle} ${color}`;
      }

      // Shadow
      if (shadowConfig && shadowConfig.tabVal) {
        const x = shadowConfig.xConfig ? shadowConfig.xConfig.val : 0;
        const y = shadowConfig.yConfig ? shadowConfig.yConfig.val : 0;
        const blur = shadowConfig.blurConfig ? shadowConfig.blurConfig.val : 0;
        const spread = shadowConfig.spreadConfig
          ? shadowConfig.spreadConfig.val
          : 0;
        const color = shadowConfig.colorConfig
          ? shadowConfig.colorConfig.color[0].item
          : "#000";
        style["box-shadow"] = `${x * 2}rpx ${y * 2}rpx ${blur * 2}rpx ${
          spread * 2
        }rpx ${color}`;
      }
      // Radius (Fillet)
      if (fillet) {
        if (fillet.type) {
          // 4 corners
          const valList = fillet.valList;
          style["border-radius"] = `${valList[0].val * 2}rpx ${
            valList[1].val * 2
          }rpx ${valList[3].val * 2}rpx ${valList[2].val * 2}rpx`;
        } else {

          style["border-radius"] = `${fillet.val * 2}rpx`;
        }
      }

      // Z-Index
      if (zIndexConfig) {
        style["z-index"] = zIndexConfig.val;
        // style["position"] = "relative";
      }
      return style;
});

const bottomBgColor = computed(() => {
      const config = props.config || {};
      let style = {
        overflow: "hidden",
      };
      // 外层背景优先取 moduleColor（"背景颜色"，系统标准字段，支持双色渐变），其次 bottomBgColor
      const moduleColor = config.moduleColor;
      if (moduleColor && moduleColor.color && moduleColor.color.length) {
        const colors = moduleColor.color.map((c) => c.item).filter((c) => c);
        if (colors.length > 1) {
          style.background = `linear-gradient(90deg, ${colors[0]} 0%, ${colors[1]} 100%)`;
        } else if (colors.length === 1) {
          style.background = colors[0];
        }
      } else if (config.bottomBgColor && config.bottomBgColor.color) {
        style.background = config.bottomBgColor.color[0].item;
      }
      return style;
});
</script>

<style scoped></style>
