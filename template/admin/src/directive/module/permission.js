import { Local } from '@/utils/storage.js';

/**
 * 判断传入的 key 是否在数组 arr 中存在
 * @param {string} key - 待判断的字符串
 * @returns {boolean} - 返回布尔值，表示是否有权限
 */
function checkArray(key) {
  // seckill 秒杀 bargain 砍价 combination 拼团
  let arr = Local.get('PERMISSIONS') || ['seckill', 'bargain', 'combination']; // 定义一个数组，包含三种类型
  let index = arr.indexOf(key); // 获取 key 在数组中的索引
  if (index > -1) {
    // 如果索引大于 -1，说明 key 存在于数组中
    return true; // 有权限
  } else {
    return false; // 无权限
  }
}

/**
 * @description 一个Vue指令，用于控制组件的显示和隐藏
 * @param {Object} el - 指令绑定的DOM元素
 * @param {Object} binding - 指令绑定的对象
 */
const permission = {
  inserted: function (el, binding) {
    let permission = binding.value; // 获取到 v-permission的值
    if (permission) {
      let hasPermission = checkArray(permission); // 调用checkArray函数判断是否有权限
      if (!hasPermission) {
        // 没有权限 移除Dom元素
        el.parentNode && el.parentNode.removeChild(el);
      }
    }
  },
};

export default permission;
