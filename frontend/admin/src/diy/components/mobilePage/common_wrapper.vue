<template>
  <div :style="wrapperStyle">
    <div :style="boxStyle">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({
  name: 'common_wrapper',
});

const props = defineProps({
  config: {
    type: Object,
    default: null,
  },
});

const wrapperStyle = computed(() => {
  const config = props.config || {};
  // 外层背景优先取 moduleColor（"背景颜色"，系统标准字段，支持双色渐变），其次 bottomBgColor
  const moduleColor = config.moduleColor;
  let bottomBgColor = '';
  if (moduleColor && moduleColor.color && moduleColor.color.length) {
    const colors = moduleColor.color.map((c) => c.item).filter((c) => c);
    if (colors.length > 1) {
      bottomBgColor = `linear-gradient(90deg, ${colors[0]} 0%, ${colors[1]} 100%)`;
    } else if (colors.length === 1) {
      bottomBgColor = colors[0];
    }
  } else if (config.bottomBgColor && config.bottomBgColor.color) {
    bottomBgColor = config.bottomBgColor.color[0].item;
  }
  return {
    background: bottomBgColor,
    overflow: 'hidden', // Create BFC to contain margins of the inner box
  };
});

const boxStyle = computed(() => {
  const config = props.config || {};
  const marginConfig = config.marginConfig || {
    val: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  };
  const paddingConfig = config.paddingConfig || {
    val: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  };
  // bottomBgColor logic moved to wrapperStyle
  const componentBgConfig = config.componentBgConfig;
  const zIndexConfig = config.zIndexConfig;
  const borderConfig = config.borderConfig;
  const shadowConfig = config.shadowConfig;
  const fillet = config.fillet;
  const borderRadius = config.borderRadius || '0';
  const style = {
    marginTop: (!marginConfig.isAll ? marginConfig.val : marginConfig.valList[0].val) + 'px',
    marginBottom: (!marginConfig.isAll ? marginConfig.val : marginConfig.valList[2].val) + 'px',
    marginLeft: (!marginConfig.isAll ? marginConfig.val : marginConfig.valList[3].val) + 'px',
    marginRight: (!marginConfig.isAll ? marginConfig.val : marginConfig.valList[1].val) + 'px',
    paddingTop: (!paddingConfig.isAll ? paddingConfig.val : paddingConfig.valList[0].val) + 'px',
    paddingBottom: (!paddingConfig.isAll ? paddingConfig.val : paddingConfig.valList[2].val) + 'px',
    paddingLeft: (!paddingConfig.isAll ? paddingConfig.val : paddingConfig.valList[3].val) + 'px',
    paddingRight: (!paddingConfig.isAll ? paddingConfig.val : paddingConfig.valList[1].val) + 'px',
    overflow: 'hidden',
  };

  // Background
  if (componentBgConfig) {
    if (componentBgConfig.tabVal === 0) {
      const colorConfig = componentBgConfig.colorConfig;
      const colorDirection = componentBgConfig.colorDirection;

      if (colorConfig && colorConfig.color) {
        const colors = colorConfig.color.map((c) => c.item);
        if (colors.length > 1) {
          let deg = '90deg';
          if (colorDirection) {
            switch (colorDirection.tabVal) {
              case 0:
                deg = '90deg';
                break;
              case 1:
                deg = '180deg';
                break;
              case 2:
                deg = '135deg';
                break;
              case 3:
                deg = '200deg';
                break;
            }
          }
          style.background = `linear-gradient(${deg}, ${colors[0]} 0%, ${colors[1]} 100%)`;
        } else {
          style.background = colors[0];
        }
      }
    } else if (componentBgConfig.tabVal === 1) {
      const imageConfig = componentBgConfig.imageConfig;
      if (imageConfig && imageConfig.url) {
        style.backgroundImage = `url(${imageConfig.url})`;
        style.backgroundRepeat = 'no-repeat';
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';
      }
    }
  }
  // Removed else if (bottomBgColor) fallback as it is now on the wrapper

  // Border Radius
  if (fillet) {
    if (fillet.type) {
      // 4 corners
      const valList = fillet.valList;
      style.borderRadius = `${valList[0].val}px ${valList[1].val}px ${valList[3].val}px ${valList[2].val}px`;
    } else {
      // Single value
      style.borderRadius = fillet.val + 'px';
    }
  } else if (borderRadius && borderRadius !== '0') {
    style.borderRadius = borderRadius;
  }

  // Border
  if (borderConfig && borderConfig.tabVal) {
    // Assuming tabVal 1 means show
    const color = borderConfig.colorConfig ? borderConfig.colorConfig.color[0].item : '#000';
    const width = borderConfig.widthConfig ? borderConfig.widthConfig.val : 1;

    let borderStyle = 'solid';
    if (borderConfig.styleConfig) {
      const styleVal = borderConfig.styleConfig.tabVal;
      if (styleVal === 1) {
        borderStyle = 'dashed';
      } else if (styleVal === 2) {
        borderStyle = 'dotted';
      }
    }

    style.border = `${width}px ${borderStyle} ${color}`;
  }

  // Shadow
  if (shadowConfig && shadowConfig.tabVal) {
    const x = shadowConfig.xConfig ? shadowConfig.xConfig.val : 0;
    const y = shadowConfig.yConfig ? shadowConfig.yConfig.val : 0;
    const blur = shadowConfig.blurConfig ? shadowConfig.blurConfig.val : 0;
    const spread = shadowConfig.spreadConfig ? shadowConfig.spreadConfig.val : 0;
    const color = shadowConfig.colorConfig ? shadowConfig.colorConfig.color[0].item : '#000';
    style.boxShadow = `${x}px ${y}px ${blur}px ${spread}px ${color}`;
  }

  // Z-Index
  if (zIndexConfig) {
    style.zIndex = zIndexConfig.val;
    style.position = 'relative'; // z-index needs position
  }

  // Text Color
  if (config.textColor && config.textColor.color) {
    style.color = config.textColor.color[0].item;
  }

  return style;
});
</script>

<style scoped></style>
