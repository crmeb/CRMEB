import config from '../../package.json';

// 1、window.localStorage 浏览器永久缓存
export const Local = {
  // 查看 v2.4.3版本更新日志
  setKey(key) {
    // @ts-ignore
    return `${config.name}:${key}`;
  },
  // 设置永久缓存
  set(key, val) {
    window.localStorage.setItem(Local.setKey(key), JSON.stringify(val));
  },
  // 获取永久缓存
  get(key) {
    let json = window.localStorage.getItem(Local.setKey(key));
    return JSON.parse(json);
  },
  // 移除永久缓存
  remove(key) {
    window.localStorage.removeItem(Local.setKey(key));
  },
  // 移除全部永久缓存
  clear() {
    window.localStorage.clear();
  },
};

// 2、window.sessionStorage 浏览器临时缓存
export const Session = {
  // 设置临时缓存
  set(key, val) {
    window.sessionStorage.setItem(Local.setKey(key), JSON.stringify(val));
  },
  // 获取临时缓存
  get(key) {
    let json = window.sessionStorage.getItem(Local.setKey(key));
    return JSON.parse(json);
  },
  // 移除临时缓存
  remove(key) {
    window.sessionStorage.removeItem(Local.setKey(key));
  },
  // 移除全部临时缓存
  clear() {
    window.sessionStorage.clear();
  },
};
