<template>
  <div
    ref="editorRef"
    class="editor"
    id="editor"
    :style="canvasStyle"
    @drop="handleDrop"
    @dragover="handleDragOver"
    @mousedown="handleMouseDown"
  >
    <!-- Grid Background -->
    <div class="grid-bg"></div>

    <!-- Components -->
    <template v-for="(item, index) in componentData" :key="item.id">
      <Shape
        v-if="!item.isHidden"
        :defaultStyle="item.style"
        :style="getShapeStyle(item)"
        :active="activeComponentIds.includes(item.id)"
        :element="item"
        :index="index"
        @select="handleSelect"
        @resize="handleResize"
        @end="handleEnd"
        @delete="handleDelete(index)"
        @copy="handleCopy(item)"
        @move="(type) => handleMove(index, type)"
      >
        <div :style="{ width: '100%', height: '100%' }">
          <!-- Picture -->
          <img
            v-if="item.component === 'Picture'"
            :src="item.propValue.url || picDecode"
            :style="getPictureStyle(item.propValue)"
          />

          <!-- Text -->
          <div v-if="item.component === 'Text'" :style="getTextBg(item.propValue)">
            <div :style="getTextStyle(item.propValue)">
              {{ getDisplayText(item) || '请填写或选择信息' }}
            </div>
          </div>

          <!-- Icon -->
          <div v-else-if="item.component === 'Icon'" :style="getIconStyle(item.propValue)">
            <span
              class="mb-iconfont"
              :class="item.propValue.class"
              :style="{ fontSize: item.propValue.size + 'px', color: item.propValue.color }"
            ></span>
          </div>

          <!-- Line -->
          <div
            v-else-if="item.component === 'Line'"
            style="
              display: flex;
              align-items: center;
              justify-content: center;
              width: 100%;
              height: 100%;
              padding: 5px 0;
              box-sizing: border-box;
            "
          >
            <div
              :style="{
                width: item.propValue.direction === 'vertical' ? '0px' : '100%',
                height: item.propValue.direction === 'vertical' ? '100%' : '0px',
                borderTop:
                  item.propValue.direction === 'vertical'
                    ? 'none'
                    : item.propValue.height + 'px ' + item.propValue.style + ' ' + item.propValue.color,
                borderLeft:
                  item.propValue.direction === 'vertical'
                    ? item.propValue.height + 'px ' + item.propValue.style + ' ' + item.propValue.color
                    : 'none',
              }"
            ></div>
          </div>

          <!-- Panel -->
          <div v-else-if="item.component === 'Panel'" :style="getPanelStyle(item.propValue)"></div>
        </div>
      </Shape>
    </template>

    <!-- Guidelines -->
    <div v-show="guideline.showV" class="guideline-v" :style="{ left: guideline.v + 'px' }"></div>
    <div v-show="guideline.showH" class="guideline-h" :style="{ top: guideline.h + 'px' }"></div>

    <!-- Area Selector -->
    <div
      v-show="isShowArea"
      class="area-selector"
      :style="{
        left: areaStart.x + 'px',
        top: areaStart.y + 'px',
        width: areaWidth + 'px',
        height: areaHeight + 'px',
      }"
    ></div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Shape from './Shape.vue';
import picDecode from '@/assets/images/pic-decode.png';
import generateID from './utils/generateID';
import { deepCopy } from './utils/utils';

defineOptions({ name: 'Editor' });

const props = defineProps({
  componentData: { type: Array, default: () => [] },
  canvasStyle: { type: Object, default: () => ({}) },
  curComponent: { type: Object, default: null },
  type: { type: String, default: 'user' },
  fieldList: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:componentData', 'multi-select', 'select', 'record', 'delete', 'copy', 'move']);

const editorRef = ref(null);

const guideline = reactive({ v: 0, h: 0, showV: false, showH: false });
const isShowArea = ref(false);
const areaStart = reactive({ x: 0, y: 0 });
const areaWidth = ref(0);
const areaHeight = ref(0);
const activeComponentIds = ref([]);

watch(activeComponentIds, (val) => {
  emit('multi-select', val);
});
watch(
  () => props.curComponent,
  (val) => {
    if (val) {
      if (!activeComponentIds.value.includes(val.id)) {
        activeComponentIds.value = [val.id];
      }
    } else {
      activeComponentIds.value = [];
    }
  },
  { immediate: true },
);

const currentFieldList = computed(() => {
  const map = {
    user: 'user',
    article: 'article',
    coupon: 'coupon',
    goods: 'product',
  };
  const key = map[props.type] || 'user';
  const list = props.fieldList[key] || [];
  if (Array.isArray(list)) {
    return list.map((item) => {
      if (typeof item === 'object') {
        return {
          label: item.label || item.title || item.name,
          value: item.value || item.field || item.key || item.name,
        };
      }
      return { label: item, value: item };
    });
  }
  if (typeof list === 'object') {
    return Object.keys(list).map((k) => ({
      label: list[k],
      value: k,
    }));
  }
  return [];
});

function getPictureStyle(propValue) {
  const style = {
    width: '100%',
    height: '100%',
    objectFit: 'cover',
    pointerEvents: 'none',
    boxSizing: 'border-box',
    borderWidth: (propValue.showBorder ? propValue.borderWidth : 0) + 'px',
    borderColor: propValue.borderColor || 'transparent',
    borderStyle: propValue.borderStyle || 'solid',
  };

  // Border Radius
  if (propValue.isRadiusAll) {
    style.borderRadius = (propValue.borderRadius || 0) + 'px';
  } else {
    style.borderRadius = `${propValue.borderRadiusTopLeft || 0}px ${propValue.borderRadiusTopRight || 0}px ${
      propValue.borderRadiusBottomRight || 0
    }px ${propValue.borderRadiusBottomLeft || 0}px`;
  }

  // Box Shadow
  if (propValue.showShadow) {
    style.boxShadow = `${propValue.shadowX || 0}px ${propValue.shadowY || 0}px ${propValue.shadowBlur || 0}px ${
      propValue.shadowSpread || 0
    }px ${propValue.shadowColor || 'rgba(0,0,0,0.5)'}`;
  }
  return style;
}
function getIconStyle(propValue) {
  const style = {
    display: 'flex',
    justifyContent: propValue.iconAlign || 'center',
    alignItems: 'center',
    width: '100%',
    height: '100%',
    boxSizing: 'border-box',
    borderWidth: (propValue.showBorder ? propValue.borderWidth : 0) + 'px',
    borderColor: propValue.borderColor || 'transparent',
    borderStyle: propValue.borderStyle || 'solid',
    padding: `${propValue.paddingTop || 0}px ${propValue.paddingRight || 0}px ${propValue.paddingBottom || 0}px ${
      propValue.paddingLeft || 0
    }px`,
  };

  // Background
  if (propValue.bgColor2) {
    const directionMap = {
      horizontal: '90deg',
      vertical: '180deg',
      'left-diagonal': '135deg',
      'right-diagonal': '45deg',
    };
    const deg = directionMap[propValue.bgDirection] || '180deg';
    style.backgroundImage = `linear-gradient(${deg}, ${propValue.backgroundColor || 'transparent'}, ${
      propValue.bgColor2
    })`;
  } else {
    style.backgroundColor = propValue.backgroundColor || 'transparent';
  }

  // Border Radius
  if (propValue.isRadiusAll) {
    style.borderRadius = (propValue.borderRadius || 0) + 'px';
  } else {
    style.borderRadius = `${propValue.borderRadiusTopLeft || 0}px ${propValue.borderRadiusTopRight || 0}px ${
      propValue.borderRadiusBottomRight || 0
    }px ${propValue.borderRadiusBottomLeft || 0}px`;
  }

  return style;
}
function getTextStyle(propValue) {
  const style = {
    fontSize: propValue.fontSize + 'px',
    color: propValue.color,
    lineHeight: propValue.lineHeight,
    letterSpacing: (propValue.letterSpacing || 0) + 'px',
    fontWeight: propValue.fontWeight || 'normal',
    fontStyle: propValue.fontStyle || 'normal',
    textDecoration: propValue.textDecoration || 'none',
    textAlign: propValue.textAlign || 'left',

    boxSizing: 'border-box',
    wordBreak: 'break-all',
  };

  // Ellipsis
  if (propValue.ellipsis > 0) {
    style.display = '-webkit-box';
    style.WebkitBoxOrient = 'vertical';
    style.WebkitLineClamp = propValue.ellipsis;
    style.overflow = 'hidden';
    style.whiteSpace = 'normal';
  } else {
    style.whiteSpace = 'normal';
  }

  if (propValue.showTextShadow) {
    style.textShadow = `${propValue.shadowX || 0}px ${propValue.shadowY || 0}px ${propValue.shadowBlur || 0}px ${
      propValue.shadowColor || 'rgba(0,0,0,0.5)'
    }`;
  }

  return style;
}
function getTextBg(propValue) {
  const style = {
    height: '100%',
    borderWidth: (propValue.showBorder ? propValue.borderWidth : 0) + 'px',
    borderColor: propValue.borderColor || 'transparent',
    borderStyle: propValue.borderStyle || 'solid',
    padding: `${propValue.paddingTop || 0}px ${propValue.paddingRight || 0}px ${propValue.paddingBottom || 0}px ${
      propValue.paddingLeft || 0
    }px`,
  };
  // Background
  if (propValue.bgColor2) {
    const directionMap = {
      horizontal: '90deg',
      vertical: '180deg',
      'left-diagonal': '135deg',
      'right-diagonal': '45deg',
    };
    const deg = directionMap[propValue.bgDirection] || '180deg';
    style.backgroundImage = `linear-gradient(${deg}, ${propValue.backgroundColor || 'transparent'}, ${
      propValue.bgColor2
    })`;
  } else {
    style.backgroundColor = propValue.backgroundColor || 'transparent';
  }
  if (propValue.showBorder && propValue.borderWidth) {
    style.backgroundSize = `100% calc(100% + ${propValue.borderWidth * 2 || 0}px`;
    style.backgroundPosition = `0px -${propValue.borderWidth || 0}px`;
  }

  // Border Radius
  if (propValue.isRadiusAll) {
    style.borderRadius = (propValue.borderRadius || 0) + 'px';
  } else {
    style.borderRadius = `${propValue.borderRadiusTopLeft || 0}px ${propValue.borderRadiusTopRight || 0}px ${
      propValue.borderRadiusBottomRight || 0
    }px ${propValue.borderRadiusBottomLeft || 0}px`;
  }
  return style;
}
function getPanelStyle(propValue) {
  const style = {
    width: '100%',
    height: '100%',
    boxSizing: 'border-box',
    borderWidth: (propValue.showBorder ? propValue.borderWidth : 0) + 'px',
    borderColor: propValue.borderColor || 'transparent',
    borderStyle: propValue.borderStyle || 'solid',
    padding: `${propValue.paddingTop || 0}px ${propValue.paddingRight || 0}px ${propValue.paddingBottom || 0}px ${
      propValue.paddingLeft || 0
    }px`,
  };

  // Background
  if (propValue.bgColor2) {
    const directionMap = {
      horizontal: '90deg',
      vertical: '180deg',
      'left-diagonal': '135deg',
      'right-diagonal': '45deg',
    };
    const deg = directionMap[propValue.bgDirection] || '180deg';
    style.backgroundImage = `linear-gradient(${deg}, ${propValue.backgroundColor || 'transparent'}, ${
      propValue.bgColor2
    })`;
  } else {
    style.backgroundColor = propValue.backgroundColor || 'transparent';
  }
  // Border Radius
  if (propValue.isRadiusAll) {
    style.borderRadius = (propValue.borderRadius || 0) + 'px';
  } else {
    style.borderRadius = `${propValue.borderRadiusTopLeft || 0}px ${propValue.borderRadiusTopRight || 0}px ${
      propValue.borderRadiusBottomRight || 0
    }px ${propValue.borderRadiusBottomLeft || 0}px`;
  }

  if (propValue.showBorder && propValue.borderWidth) {
    style.backgroundSize = `100% calc(100% + ${propValue.borderWidth * 2 || 0}px`;
    style.backgroundPosition = `0px -${propValue.borderWidth || 0}px`;
  }

  // Box Shadow
  if (propValue.showShadow) {
    style.boxShadow = `${propValue.shadowX || 0}px ${propValue.shadowY || 0}px ${propValue.shadowBlur || 0}px ${
      propValue.shadowSpread || 0
    }px ${propValue.shadowColor || '#000000'}`;
  }

  return style;
}
function getDisplayText(item) {
  if (item.propValue.fieldType) {
    const field = currentFieldList.value.find((f) => f.value === item.propValue.fieldType);
    const label = field ? field.label : '';
    const title = item.propValue.text || '';
    return title ? `${title}${label}` : label;
  }
  return item.propValue.text || '';
}
function handleDrop(e) {
  e.preventDefault();
  e.stopPropagation();

  const componentStr = e.dataTransfer.getData('component');
  if (!componentStr) return;

  const component = JSON.parse(componentStr);
  const rect = editorRef.value.getBoundingClientRect();

  // Calculate drop position relative to canvas
  let left = e.clientX - rect.left;
  let top = e.clientY - rect.top;

  const canvasWidth = parseInt(props.canvasStyle.width);
  const canvasHeight = parseInt(props.canvasStyle.height);
  const compWidth = component.propValue.width;
  const compHeight = component.propValue.height;

  // Boundary checks for drop
  if (left < 0) left = 0;
  if (top < 0) top = 0;
  if (left + compWidth > canvasWidth) left = canvasWidth - compWidth;
  if (top + compHeight > canvasHeight) top = canvasHeight - compHeight;

  // Boundary checks can be added here

  const newComponent = {
    id: generateID(),
    component: component.type,
    label: component.label,
    style: {
      top: top,
      left: left,
      width: component.propValue.width,
      height: component.propValue.height,
      zIndex: 1,
    },
    propValue: deepCopy(component.propValue),
  };

  emit('update:componentData', [...props.componentData, newComponent]);
  emit('record');
  emit('select', newComponent, props.componentData.length);
}
function handleDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'copy';
}
function handleMouseDown(e) {
  e.stopPropagation();
  emit('select', null, -1);
  activeComponentIds.value = [];

  // Start area selection
  const rect = editorRef.value.getBoundingClientRect();
  const startX = e.clientX - rect.left;
  const startY = e.clientY - rect.top;
  areaStart.x = startX;
  areaStart.y = startY;
  isShowArea.value = true;
  areaWidth.value = 0;
  areaHeight.value = 0;

  const move = (moveEvent) => {
    const currX = moveEvent.clientX - rect.left;
    const currY = moveEvent.clientY - rect.top;
    areaWidth.value = Math.abs(currX - startX);
    areaHeight.value = Math.abs(currY - startY);
    areaStart.x = Math.min(currX, startX);
    areaStart.y = Math.min(currY, startY);
  };

  const up = () => {
    document.removeEventListener('mousemove', move);
    document.removeEventListener('mouseup', up);
    isShowArea.value = false;

    // Calculate intersection
    const selectedIds = [];
    props.componentData.forEach((component) => {
      if (component.isHidden) return;
      const { left, top, width, height } = component.style;
      // Check overlap
      if (
        left < areaStart.x + areaWidth.value &&
        left + width > areaStart.x &&
        top < areaStart.y + areaHeight.value &&
        top + height > areaStart.y
      ) {
        selectedIds.push(component.id);
      }
    });

    if (selectedIds.length > 0) {
      activeComponentIds.value = selectedIds;
      // Select the last one as curComponent
      const lastId = selectedIds[selectedIds.length - 1];
      const component = props.componentData.find((c) => c.id === lastId);
      const index = props.componentData.findIndex((c) => c.id === lastId);
      emit('select', component, index);
    }
  };

  document.addEventListener('mousemove', move);
  document.addEventListener('mouseup', up);
}
function handleSelect(component, index) {
  if (component) {
    // If clicking on an item that is NOT in the current selection, clear and select it.
    // If clicking on an item that IS in the selection, keep the selection (to allow moving group).
    // TODO: Add Ctrl/Command key support for toggling selection.
    if (!activeComponentIds.value.includes(component.id)) {
      activeComponentIds.value = [component.id];
    }
  } else {
    activeComponentIds.value = [];
  }
  emit('select', component, index);
}
function handleResize(pos) {
  if (props.curComponent) {
    const canvasWidth = parseInt(props.canvasStyle.width);
    const canvasHeight = parseInt(props.canvasStyle.height);

    // Calculate delta for group move before modifying pos (which might be clamped)
    // Wait, pos IS the new absolute position of curComponent.
    // We need to know the *intended* change.
    // But handleResize in Shape.vue calculates absolute pos based on mouse delta.
    // So pos.left - curComponent.style.left IS the delta.
    // However, we should apply this delta to other components *if* it's a move operation (not resize).
    // Resize usually changes width/height too.
    // Shape.vue emits resize event for both move and resize.
    // For move, width/height don't change.
    const isResize = pos.width !== props.curComponent.style.width || pos.height !== props.curComponent.style.height;

    let dx = 0;
    let dy = 0;
    if (!isResize) {
      dx = pos.left - props.curComponent.style.left;
      dy = pos.top - props.curComponent.style.top;
    }

    // Boundary checks
    if (isResize) {
      if (pos.left < 0) {
        pos.width += pos.left;
        pos.left = 0;
      }
      if (pos.top < 0) {
        pos.height += pos.top;
        pos.top = 0;
      }
      if (pos.left + pos.width > canvasWidth) {
        const leftChanged = pos.left !== props.curComponent.style.left;
        if (leftChanged) {
          pos.left = canvasWidth - pos.width;
        } else {
          pos.width = canvasWidth - pos.left;
        }
      }
      if (pos.top + pos.height > canvasHeight) {
        const topChanged = pos.top !== props.curComponent.style.top;
        if (topChanged) {
          pos.top = canvasHeight - pos.height;
        } else {
          pos.height = canvasHeight - pos.top;
        }
      }
      // Clear guidelines during resize
      guideline.showV = false;
      guideline.showH = false;
    } else {
      // Move
      const rotate = pos.rotate || 0;
      if (rotate === 0) {
        if (pos.left < 0) pos.left = 0;
        if (pos.top < 0) pos.top = 0;
        if (pos.left + pos.width > canvasWidth) pos.left = canvasWidth - pos.width;
        if (pos.top + pos.height > canvasHeight) pos.top = canvasHeight - pos.height;
      } else {
        // Rotated boundary check
        const r = (rotate * Math.PI) / 180;
        const w = pos.width;
        const h = pos.height;

        // AABB dimensions
        const boundingWidth = w * Math.abs(Math.cos(r)) + h * Math.abs(Math.sin(r));
        const boundingHeight = w * Math.abs(Math.sin(r)) + h * Math.abs(Math.cos(r));

        // Constraints
        const minLeft = (boundingWidth - w) / 2;
        const maxLeft = canvasWidth - (w + boundingWidth) / 2;
        const minTop = (boundingHeight - h) / 2;
        const maxTop = canvasHeight - (h + boundingHeight) / 2;

        if (pos.left < minLeft) pos.left = minLeft;
        if (pos.left > maxLeft) pos.left = maxLeft;
        if (pos.top < minTop) pos.top = minTop;
        if (pos.top > maxTop) pos.top = maxTop;
      }

      // Recalculate dx/dy after boundary checks
      dx = pos.left - props.curComponent.style.left;
      dy = pos.top - props.curComponent.style.top;

      // Snapping and Guidelines (Center Alignment)
      const threshold = 5;
      const curCenterX = pos.left + pos.width / 2;
      const curCenterY = pos.top + pos.height / 2;

      let closestX = null;
      let minDiffX = threshold;
      let closestY = null;
      let minDiffY = threshold;

      // 1. Check Canvas Center
      const canvasCenterX = canvasWidth / 2;
      const canvasCenterY = canvasHeight / 2;

      if (Math.abs(curCenterX - canvasCenterX) < threshold) {
        minDiffX = Math.abs(curCenterX - canvasCenterX);
        closestX = canvasCenterX;
      }
      if (Math.abs(curCenterY - canvasCenterY) < threshold) {
        minDiffY = Math.abs(curCenterY - canvasCenterY);
        closestY = canvasCenterY;
      }

      // 2. Check Other Components
      props.componentData.forEach((comp) => {
        if (comp.id === props.curComponent.id) return;
        if (comp.isHidden) return;
        // Skip components in the current selection group to avoid snapping to moving peers
        if (activeComponentIds.value.includes(comp.id)) return;

        const compCenterX = comp.style.left + comp.style.width / 2;
        const compCenterY = comp.style.top + comp.style.height / 2;

        const diffX = Math.abs(curCenterX - compCenterX);
        if (diffX < minDiffX) {
          minDiffX = diffX;
          closestX = compCenterX;
        }

        const diffY = Math.abs(curCenterY - compCenterY);
        if (diffY < minDiffY) {
          minDiffY = diffY;
          closestY = compCenterY;
        }
      });

      // Apply Snap X
      guideline.showV = false;
      if (closestX !== null) {
        const newLeft = closestX - pos.width / 2;
        // Re-calculate dx if snapped
        dx = newLeft - props.curComponent.style.left;
        pos.left = newLeft;
        guideline.showV = true;
        guideline.v = closestX;
      }

      // Apply Snap Y
      guideline.showH = false;
      if (closestY !== null) {
        const newTop = closestY - pos.height / 2;
        // Re-calculate dy if snapped
        dy = newTop - props.curComponent.style.top;
        pos.top = newTop;
        guideline.showH = true;
        guideline.h = closestY;
      }
    }

    // Apply changes to curComponent
    props.curComponent.style = { ...props.curComponent.style, ...pos };

    // Apply changes to other selected components
    if (!isResize && activeComponentIds.value.length > 1) {
      activeComponentIds.value.forEach((id) => {
        if (id === props.curComponent.id) return;
        const comp = props.componentData.find((c) => c.id === id);
        if (comp) {
          comp.style.left += dx;
          comp.style.top += dy;
        }
      });
    }

    // Sync Line thickness with container size
    if (props.curComponent.component === 'Line') {
      if (props.curComponent.propValue.direction === 'horizontal') {
        props.curComponent.propValue.height = pos.height;
      } else {
        props.curComponent.propValue.height = pos.width;
      }
    }
  }
}
function handleEnd() {
  guideline.showV = false;
  guideline.showH = false;
  emit('record', '调整组件');
}
function handleDelete(index) {
  emit('delete', index);
}
function handleCopy(item) {
  emit('copy', item);
}
function handleMove(index, type) {
  emit('move', { index, type });
}
function getShapeStyle(item) {
  const style = item.style;
  const isLine = item.component === 'Line';
  return {
    top: (isLine ? Number(style.top) - 5 : style.top) + 'px',
    left: style.left + 'px',
    width: style.width + 'px',
    height: (isLine ? Number(style.height) + 10 : style.height) + 'px',
    zIndex: style.zIndex,
    transform: `rotate(${style.rotate || 0}deg)`,
  };
}
</script>

<style scoped lang="scss">
.editor {
  position: relative;
  background: #fff;
  margin: 20px auto;
  // box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);

  .grid-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: linear-gradient(45deg, #f5f5f5 25%, transparent 25%, transparent 75%, #f5f5f5 75%, #f5f5f5),
      linear-gradient(45deg, #f5f5f5 25%, transparent 25%, transparent 75%, #f5f5f5 75%, #f5f5f5);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
    pointer-events: none;
  }

  .guideline-v {
    position: absolute;
    top: 0;
    bottom: 0;
    border-left: 1px dashed #1890ff;
    z-index: 9999;
    pointer-events: none;
  }

  .guideline-h {
    position: absolute;
    left: 0;
    right: 0;
    border-top: 1px dashed #1890ff;
    z-index: 9999;
    pointer-events: none;
  }

  .area-selector {
    position: absolute;
    border: 1px solid #1890ff;
    background-color: rgba(24, 144, 255, 0.3);
    z-index: 9999;
    pointer-events: none;
  }
}
</style>
