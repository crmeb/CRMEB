<template>
  <div>
    <slot></slot>
  </div>
</template>

<script setup>
import { computed, watch, ref, nextTick, onBeforeUnmount, getCurrentInstance } from 'vue';
import throttle from 'lodash/throttle';

defineOptions({ name: 'el-table-virtual-scroll' });

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  height: {
    type: Number,
    default: 60,
  },
  buffer: {
    type: Number,
    default: 500,
  },
  keyProp: {
    type: String,
    default: 'id',
  },
  throttleTime: {
    type: Number,
    default: 100,
  },
});

const emit = defineEmits(['change']);

const { proxy } = getCurrentInstance();

const sizes = ref({}); // 尺寸映射（依赖响应式）

// 非响应式内部状态
let renderData = [];
let top = undefined;
let bottom = undefined;
let start = 0;
let end = undefined;
let scroller = null;
let onScroll = null;
let timer = null;

// 计算出每个item（的key值）到滚动容器顶部的距离
const offsetMap = computed(() => {
  const { keyProp, height, data } = props;
  const res = {};
  let total = 0;
  for (let i = 0; i < data.length; i++) {
    const key = data[i][keyProp];
    res[key] = total;

    const curSize = sizes.value[key];
    const size = typeof curSize === 'number' ? curSize : height;
    total += size;
  }
  return res;
});

// 初始化数据
function initData() {
  // 可视范围内显示数据
  renderData = [];
  // 页面可视范围顶端、底部
  top = undefined;
  bottom = undefined;
  // 截取页面可视范围内显示数据的开始和结尾索引
  start = 0;
  end = undefined;

  scroller = proxy.$el.querySelector('.el-table__body-wrapper');

  // 初次执行
  setTimeout(() => {
    handleScroll();
  }, 100);

  // 监听事件
  onScroll = throttle(handleScroll, props.throttleTime);
  scroller.addEventListener('scroll', handleScroll);
  window.addEventListener('resize', onScroll);
}

// 更新尺寸（高度）
function updateSizes() {
  const rows = proxy.$el.querySelectorAll('.el-table__body > tbody > .el-table__row');

  Array.from(rows).forEach((row, index) => {
    const item = renderData[index];
    if (!item) return;

    const key = item[props.keyProp];
    const offsetHeight = row.offsetHeight;

    if (sizes.value[key] !== offsetHeight) {
      sizes.value[key] = offsetHeight;
    }
  });
}

// 处理滚动事件
function handleScroll(shouldUpdate = true) {
  // 更新当前尺寸（高度）
  updateSizes();
  // 计算renderData
  calcRenderData();
  // 计算位置
  calcPosition();
  shouldUpdate && updatePosition();
  // 触发事件
  emit('change', renderData, start, end);
}

// 获取某条数据offsetTop
function getOffsetTop(index) {
  const item = props.data[index];
  if (item) {
    return offsetMap.value[item[props.keyProp]] || 0;
  }
  return 0;
}

// 获取某条数据的尺寸
function getSize(index) {
  const item = props.data[index];
  if (item) {
    const key = item[props.keyProp];
    return sizes.value[key] || props.height;
  }
  return props.height;
}

// 计算只在视图上渲染的数据
function calcRenderData() {
  const data = props.data;
  const buffer = props.buffer;
  // 计算可视范围顶部、底部
  const topVal = scroller.scrollTop - buffer;
  const bottomVal = scroller.scrollTop + scroller.offsetHeight + buffer;

  // 二分法计算可视范围内的开始的第一个内容
  let l = 0;
  let r = data.length - 1;
  let mid = 0;
  while (l <= r) {
    mid = Math.floor((l + r) / 2);
    const midVal = getOffsetTop(mid);
    if (midVal < topVal) {
      const midNextVal = getOffsetTop(mid + 1);
      if (midNextVal > topVal) break;
      l = mid + 1;
    } else {
      r = mid - 1;
    }
  }

  // 计算渲染内容的开始、结束索引
  let startVal = mid;
  let endVal = data.length - 1;
  for (let i = startVal + 1; i < data.length; i++) {
    const offsetTop = getOffsetTop(i);
    if (offsetTop >= bottomVal) {
      endVal = i;
      break;
    }
  }

  // 开始索引始终保持偶数，如果为奇数，则加1使其保持偶数【确保表格行的偶数数一致，不会导致斑马纹乱序显示】
  if (startVal % 2) {
    startVal = startVal - 1;
  }
  // console.log(startVal, endVal, 'start end')

  top = topVal;
  bottom = bottomVal;
  start = startVal;
  end = endVal;
  renderData = data.slice(startVal, endVal + 1);
}

// 计算位置
function calcPosition() {
  const last = props.data.length - 1;
  // 计算内容总高度
  const wrapHeight = getOffsetTop(last) + getSize(last);
  // 计算当前滚动位置需要撑起的高度
  const offsetTopVal = getOffsetTop(start);

  // 设置dom位置
  const classNames = [
    '.el-table__body-wrapper',
    '.el-table__fixed-right .el-table__fixed-body-wrapper',
    '.el-table__fixed .el-table__fixed-body-wrapper',
  ];
  classNames.forEach((className) => {
    const el = proxy.$el.querySelector(className);
    if (!el) return;

    // 创建wrapEl、innerEl
    if (!el.wrapEl) {
      const wrapEl = document.createElement('div');
      const innerEl = document.createElement('div');
      wrapEl.appendChild(innerEl);
      innerEl.appendChild(el.children[0]);
      el.insertBefore(wrapEl, el.firstChild);
      el.wrapEl = wrapEl;
      el.innerEl = innerEl;
    }

    if (el.wrapEl) {
      // 设置高度
      el.wrapEl.style.height = wrapHeight + 'px';
      // 设置transform撑起高度
      el.innerEl.style.transform = `translateY(${offsetTopVal}px)`;
      // 设置paddingTop撑起高度
      // el.innerEl.style.paddingTop = `${offsetTopVal}px`
    }
  });
}

// 空闲时更新位置
function updatePosition() {
  timer && clearTimeout(timer);
  timer = setTimeout(() => {
    timer && clearTimeout(timer);
    // 传入false，避免一直循环调用
    handleScroll(false);
  }, props.throttleTime + 10);
}

// 【外部调用】更新
function update() {
  handleScroll();
}

// 【外部调用】滚动到第几行
function scrollTo(index, stop = false) {
  const item = props.data[index];
  if (item && scroller) {
    updateSizes();
    calcRenderData();

    nextTick(() => {
      const offsetTopVal = getOffsetTop(index);
      scroller.scrollTop = offsetTopVal;

      // 调用两次scrollTo，第一次滚动时，如果表格行初次渲染高度发生变化时，会导致滚动位置有偏差，此时需要第二次执行滚动，确保滚动位置无误
      if (!stop) {
        setTimeout(() => {
          scrollTo(index, true);
        }, 50);
      }
    });
  }
}

// 【外部调用】重置
function reset() {
  sizes.value = {};
  scrollTo(0, false);
}

watch(
  () => props.data,
  () => {
    update();
  },
);

// created
nextTick(() => {
  initData();
});

onBeforeUnmount(() => {
  if (scroller) {
    scroller.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);
  }
});

defineExpose({ update, scrollTo, reset });
</script>

<style lang="less" scoped></style>
