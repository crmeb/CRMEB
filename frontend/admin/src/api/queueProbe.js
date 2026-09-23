import request from '@/libs/request';

/** 匿名创建队列测试：立即任务 + 60 秒延迟任务。 */
export function createQueueProbe() {
  return request({ url: 'system/queue_probe', method: 'get' });
}

/** 查询一小时内的队列测试回执。 */
export function getQueueProbe(id) {
  return request({ url: `system/queue_probe/${encodeURIComponent(id)}`, method: 'get' });
}
