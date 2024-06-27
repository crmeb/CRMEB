/**
 * @description 一个Vue指令，用于控制组件的显示和隐藏
 * @param {Object} el - 指令绑定的DOM元素
 * @param {Object} binding - 指令绑定的对象
 */
const dbClick = {
  inserted(el, binding) {
    el.addEventListener('click', (e) => {
      if (!el.disabled) {
        el.disabled = true;
        el.style.cursor = 'not-allowed';
        setTimeout(() => {
          el.style.cursor = 'pointer';
          el.disabled = false;
        }, 1000);
      }
    });
  },
};

export default dbClick;
