import { ElMessage } from 'element-plus';

/**
 * 过滤主题行内样式中的主色浅/深阶变量
 *
 * 主色浅色阶/深色阶由样式表 color-mix 表达式动态计算（亮色档在 theme/app.scss，
 * 暗色档在 theme/dark.scss），引用 --prev-color-primary，换肤时自动重算。
 * 这类变量不允许持久化为行内样式：行内样式优先级最高，会压过深色模式的暗色档，
 * 导致深色下表头/选中行/标签浅底仍是刺眼浅色（2026-09 深色模式适配遗留问题）。
 * @param {string} cssText document.documentElement.style.cssText
 * @returns {string} 过滤后的 cssText
 */
export function filterThemeCssText(cssText) {
  if (!cssText) return '';
  const dynamicVarRe = /^--(prev|el)-color-primary-(light|dark)-\d+$/;
  return cssText
    .split(';')
    .filter((item) => {
      const name = (item.split(':')[0] || '').trim();
      return name && !dynamicVarRe.test(name);
    })
    .join(';');
}

/**
 * 颜色转换函数
 * @method hexToRgb hex 颜色转 rgb 颜色
 * @method rgbToHex rgb 颜色转 Hex 颜色
 * @method getDarkColor 加深颜色值
 * @method getLightColor 变浅颜色值
 */
export function useChangeColor() {
  // str 颜色值字符串
  const hexToRgb = (str) => {
    let hexs = '';
    let reg = /^#?[0-9A-Fa-f]{6}$/;
    if (!reg.test(str)) {
      ElMessage.warning('输入错误的hex');
      return '';
    }
    str = str.replace('#', '');
    hexs = str.match(/../g);
    for (let i = 0; i < 3; i++) hexs[i] = parseInt(hexs[i], 16);
    return hexs;
  };
  // r 代表红色 | g 代表绿色 | b 代表蓝色
  const rgbToHex = (r, g, b) => {
    let reg = /^\d{1,3}$/;
    if (!reg.test(r) || !reg.test(g) || !reg.test(b)) {
      ElMessage.warning('输入错误的rgb颜色值');
      return '';
    }
    let hexs = [r.toString(16), g.toString(16), b.toString(16)];
    for (let i = 0; i < 3; i++) if (hexs[i].length == 1) hexs[i] = `0${hexs[i]}`;
    return `#${hexs.join('')}`;
  };
  // color 颜色值字符串 | level 变浅的程度，限0-1之间
  const getDarkColor = (color, level) => {
    let reg = /^#?[0-9A-Fa-f]{6}$/;
    if (!reg.test(color)) {
      ElMessage.warning('输入错误的hex颜色值');
      return '';
    }
    let rgb = useChangeColor().hexToRgb(color);
    for (let i = 0; i < 3; i++) rgb[i] = Math.floor(rgb[i] * (1 - level));
    return useChangeColor().rgbToHex(rgb[0], rgb[1], rgb[2]);
  };
  // color 颜色值字符串 | level 加深的程度，限0-1之间
  const getLightColor = (color, level) => {
    let reg = /^#?[0-9A-Fa-f]{6}$/;
    if (!reg.test(color)) {
      ElMessage.warning('输入错误的hex颜色值');
      return '';
    }
    let rgb = useChangeColor().hexToRgb(color);
    for (let i = 0; i < 3; i++) rgb[i] = Math.floor((255 - rgb[i]) * level + rgb[i]);
    return useChangeColor().rgbToHex(rgb[0], rgb[1], rgb[2]);
  };
  return {
    hexToRgb,
    rgbToHex,
    getDarkColor,
    getLightColor,
  };
}
