// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

/**
 * @description 判断列表1中是否包含了列表2中的某一项
 * 因为用户权限 access 为数组，includes 方法无法直接得出结论
 * */
function includeArray(list1, list2) {
  if (list1 === true) {
    return true;
  }
  if (typeof list2 !== 'object') {
    return false;
  }
  // some 命中即短路；路由守卫每次导航都会调用
  return list2.some((item) => list1.includes(item));
}
export { includeArray };
