/** DIY 编辑器纯工具（无 Vue / store 依赖） */

export function objToArr(data) {
  return Object.keys(data).map((key) => data[key]);
}

export function swapArray(arr, index1, index2) {
  arr[index1] = arr.splice(index2, 1, arr[index1])[0];
  return arr;
}

export function getBadgeText(val) {
  const map = {
    user: '用户',
    article: '文章',
    coupon: '优惠券',
    goods: '商品',
  };
  return map[val] || '';
}

export function cloneThemeValue(value) {
  return JSON.parse(JSON.stringify(value || {}));
}

export function cloneThemeComponent(value) {
  if (!value) return null;
  return JSON.parse(JSON.stringify(value));
}

export function removeThemeComponent(value, name) {
  Object.keys(value || {}).forEach((key) => {
    if (value[key] && value[key].name == name) {
      delete value[key];
    }
  });
  return value;
}

/** 开发期：mobilePage defaultName 重复告警 */
export function warnDuplicateDefaultNames(lConfig) {
  const defaultNameMap = {};
  lConfig.forEach((item) => {
    if (!item.defaultName) return;
    if (!defaultNameMap[item.defaultName]) {
      defaultNameMap[item.defaultName] = [];
    }
    defaultNameMap[item.defaultName].push(item.name);
  });
  const duplicates = Object.keys(defaultNameMap).reduce((result, defaultName) => {
    if (defaultNameMap[defaultName].length > 1) {
      result[defaultName] = defaultNameMap[defaultName];
    }
    return result;
  }, {});
  if (Object.keys(duplicates).length) {
    console.error('[DIY] mobilePage 组件 defaultName 重复，保存数据无法唯一匹配组件：', duplicates);
  }
}
