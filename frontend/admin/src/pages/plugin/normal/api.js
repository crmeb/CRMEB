/**
 * 实物商品扩展 API 工具
 * 复用主应用的 axios 实例，自动携带 token
 * 注意：request 实例的 baseURL 已包含 /adminapi，因此不需要重复添加
 */
import request from '@/libs/request';

const API_BASE = '/normal';

export function normalApi(url, options = {}) {
  const method = (options.method || 'get').toLowerCase();
  const config = {
    url: API_BASE + url,
    method,
  };
  if (options.body) {
    config.data = typeof options.body === 'string' ? JSON.parse(options.body) : options.body;
  }
  return request(config);
}
