<template>
  <div class="shape" :class="{ active }" ref="shapeRef" @click.stop="selectCurComponent" @mousedown="handleMouseDown">
    <span
      v-show="active && !element.isLock"
      v-for="item in active ? pointList : []"
      :key="item"
      class="shape-point"
      :style="getPointStyle(item)"
      @mousedown.stop="handlePointMouseDown(item, $event)"
    >
    </span>
    <span v-show="active && !element.isLock" class="shape-rotate" @mousedown.stop="handleRotate">
      <i class="el-icon-refresh-right"></i>
    </span>
    <span v-show="element.isLock" class="shape-lock">
      <i class="el-icon-lock"></i>
    </span>
    <slot></slot>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({ name: 'Shape' });

const props = defineProps({
  active: {
    type: Boolean,
    default: false,
  },
  element: {
    type: Object,
    default: () => ({}),
  },
  defaultStyle: {
    type: Object,
    default: () => ({}),
  },
  index: {
    type: [Number, String],
    default: 0,
  },
});

const emit = defineEmits(['select', 'resize', 'end']);

const shapeRef = ref(null);

const pointList = ['lt', 't', 'rt', 'r', 'rb', 'b', 'lb', 'l']; // 8 directions
const cursors = {
  lt: 'nw-resize',
  t: 'n-resize',
  rt: 'ne-resize',
  r: 'e-resize',
  rb: 'se-resize',
  b: 's-resize',
  lb: 'sw-resize',
  l: 'w-resize',
};

function getPointStyle(point) {
  let { width, height } = props.defaultStyle;
  // Ensure numeric values
  width = Number(width);
  height = Number(height);

  if (props.element.component === 'Line') {
    height += 10;
  }
  const hasT = /t/.test(point);
  const hasB = /b/.test(point);
  const hasL = /l/.test(point);
  const hasR = /r/.test(point);
  let newLeft = 0;
  let newTop = 0;

  // Four mid points
  if (point.length === 2) {
    newLeft = hasL ? 0 : width;
    newTop = hasT ? 0 : height;
  } else {
    // Top/Bottom mid
    if (hasT || hasB) {
      newLeft = width / 2;
      newTop = hasT ? 0 : height;
    }
    // Left/Right mid
    if (hasL || hasR) {
      newLeft = hasL ? 0 : width;
      newTop = height / 2;
    }
  }

  const style = {
    marginLeft: hasR ? '-4px' : '-4px',
    marginTop: '-4px',
    left: `${newLeft}px`,
    top: `${newTop}px`,
    cursor: cursors[point],
  };

  return style;
}

function selectCurComponent() {
  emit('select', props.element, props.index);
}

function handleMouseDown(e) {
  if (props.element.isLock) return;
  e.stopPropagation();
  emit('select', props.element, props.index);

  const pos = { ...props.defaultStyle };
  const startY = e.clientY;
  const startX = e.clientX;
  const startTop = Number(pos.top);
  const startLeft = Number(pos.left);

  const move = (moveEvent) => {
    const currX = moveEvent.clientX;
    const currY = moveEvent.clientY;
    pos.top = currY - startY + startTop;
    pos.left = currX - startX + startLeft;
    emit('resize', pos); // Reuse resize event for move
  };

  const up = () => {
    document.removeEventListener('mousemove', move);
    document.removeEventListener('mouseup', up);
    emit('end');
  };

  document.addEventListener('mousemove', move);
  document.addEventListener('mouseup', up);
}

function handleRotate(e) {
  e.stopPropagation();
  e.preventDefault();

  const pos = { ...props.defaultStyle };
  const startY = e.clientY;
  const startX = e.clientX;
  const startRotate = pos.rotate || 0;

  // Component center
  const rect = shapeRef.value.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;

  const rotateStart = (Math.atan2(startY - centerY, startX - centerX) * 180) / Math.PI;

  const move = (moveEvent) => {
    const currX = moveEvent.clientX;
    const currY = moveEvent.clientY;
    const rotateCurrent = (Math.atan2(currY - centerY, currX - centerX) * 180) / Math.PI;

    pos.rotate = (startRotate + rotateCurrent - rotateStart + 360) % 360;
    emit('resize', pos);
  };

  const up = () => {
    document.removeEventListener('mousemove', move);
    document.removeEventListener('mouseup', up);
    emit('end');
  };

  document.addEventListener('mousemove', move);
  document.addEventListener('mouseup', up);
}

function handlePointMouseDown(point, e) {
  e.stopPropagation();
  e.preventDefault();

  const style = { ...props.defaultStyle };
  const startX = e.clientX;
  const startY = e.clientY;
  const startWidth = parseFloat(style.width);
  const startHeight = parseFloat(style.height);
  const startTop = parseFloat(style.top);
  const startLeft = parseFloat(style.left);
  const rotate = style.rotate || 0;

  // Component center
  const rect = shapeRef.value.getBoundingClientRect();
  const center = {
    x: rect.left + rect.width / 2,
    y: rect.top + rect.height / 2,
  };

  // Current mouse position relative to center
  // But we need the initial offset to be consistent.
  // Better approach: Calculate the new point position in unrotated coordinates.

  // Actually, standard approach:
  // Project the mouse movement (delta) onto the rotated axes of the component.

  const move = (moveEvent) => {
    const currX = moveEvent.clientX;
    const currY = moveEvent.clientY;
    const disX = currX - startX;
    const disY = currY - startY;

    // Convert delta to local rotated coordinates
    // angle in radians
    const angle = (rotate * Math.PI) / 180;
    const cos = Math.cos(angle);
    const sin = Math.sin(angle);

    // Rotated delta
    const rDisX = disX * cos + disY * sin;
    const rDisY = disY * cos - disX * sin;

    const hasT = /t/.test(point);
    const hasB = /b/.test(point);
    const hasL = /l/.test(point);
    const hasR = /r/.test(point);

    let newHeight = startHeight + (hasT ? -rDisY : hasB ? rDisY : 0);
    let newWidth = startWidth + (hasL ? -rDisX : hasR ? rDisX : 0);

    // Aspect ratio lock
    if (props.element.isRatioLock) {
      const ratio = startWidth / startHeight;
      if (hasL && hasR) {
        // Do nothing
      } else if (hasL || hasR) {
        newHeight = newWidth / ratio;
      } else if (hasT || hasB) {
        newWidth = newHeight * ratio;
      }

      if ((hasL || hasR) && (hasT || hasB)) {
        const deltaW = Math.abs(newWidth - startWidth);
        const deltaH = Math.abs(newHeight - startHeight);

        if (deltaW / startWidth > deltaH / startHeight) {
          newHeight = newWidth / ratio;
        } else {
          newWidth = newHeight * ratio;
        }
      }
    }

    pos.width = newWidth > 2 ? newWidth : 2;
    pos.height = newHeight > 2 ? newHeight : 2;

    const currentCenterX = startLeft + startWidth / 2;
    const currentCenterY = startTop + startHeight / 2;

    const dW = pos.width - startWidth;
    const dH = pos.height - startHeight;

    let dCx = 0;
    let dCy = 0;

    if (hasR) dCx = dW / 2;
    if (hasL) dCx = -dW / 2;
    if (hasB) dCy = dH / 2;
    if (hasT) dCy = -dH / 2;

    const dOx = dCx * cos - dCy * sin;
    const dOy = dCx * sin + dCy * cos;

    const newCenterX = currentCenterX + dOx;
    const newCenterY = currentCenterY + dOy;

    pos.left = newCenterX - pos.width / 2;
    pos.top = newCenterY - pos.height / 2;

    emit('resize', pos);
  };

  // Using object reference for reactivity in move closure
  const pos = { ...style };

  const up = () => {
    document.removeEventListener('mousemove', move);
    document.removeEventListener('mouseup', up);
    emit('end');
  };

  document.addEventListener('mousemove', move);
  document.addEventListener('mouseup', up);
}
</script>

<style scoped lang="scss">
.shape {
  position: absolute;
  &:hover {
    cursor: move;
    outline: 1px dashed var(--prev-color-primary);
  }
  &.active {
    outline: 1px solid var(--prev-color-primary);
    user-select: none;
  }
  .shape-point {
    position: absolute;
    background: #fff;
    border: 1px solid var(--prev-color-primary);
    width: 8px;
    height: 8px;
    // border-radius: 50%;
    z-index: 1;
  }
  .shape-rotate {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    cursor: grab;
    color: var(--prev-color-primary);
    font-size: 20px;
    font-weight: 600;

    &:active {
      cursor: grabbing;
    }
  }

  .shape-lock {
    position: absolute;
    top: 0;
    right: 0;
    color: red;
    padding: 2px;
    z-index: 10;
  }
  &.shape-line {
    padding: 5px 0;
    margin-top: -5px;
  }
}
</style>
