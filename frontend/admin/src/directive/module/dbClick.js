/**
 * 防重复点击：点击后约 1 秒内禁用元素，仅用于提交/保存/确定等写数据操作。
 * 用法：
 * - v-db-click
 * - v-db-click="condition"  （condition 为 false 时不节流，适合「下一步/提交」共用按钮）
 */
const dbClick = {
  mounted(el, binding) {
    el.__dbClickEnabled__ = binding.value !== false;
    el.__dbClickHandler__ = () => {
      if (el.__dbClickEnabled__ === false) return;
      if (el.disabled) return;
      el.disabled = true;
      el.style.cursor = 'not-allowed';
      setTimeout(() => {
        el.style.cursor = 'pointer';
        el.disabled = false;
      }, 1000);
    };
    el.addEventListener('click', el.__dbClickHandler__);
  },
  updated(el, binding) {
    el.__dbClickEnabled__ = binding.value !== false;
  },
  beforeUnmount(el) {
    if (el.__dbClickHandler__) {
      el.removeEventListener('click', el.__dbClickHandler__);
      delete el.__dbClickHandler__;
      delete el.__dbClickEnabled__;
    }
  },
};

export default dbClick;
