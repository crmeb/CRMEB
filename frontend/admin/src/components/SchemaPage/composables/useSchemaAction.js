/**
 * 通用页面动作执行器
 *
 * 处理行操作/顶部动作的确认交互，以及服务端动作响应的封闭指令集
 * （msg 提示 / refresh 刷新 / redirect 跳转 / download 下载）。
 */
import { ElMessage, ElMessageBox } from 'element-plus';
import router from '@/router';

/**
 * @description 执行确认交互：confirm_action 弹确认框，direct_action 直接执行
 * @param {object} actionItem 动作配置 {action, label, mode, confirm}
 * @param {Function} executor 真正发起请求的执行函数，返回 Promise
 * @returns {Promise<object|null>} 动作响应；取消确认时返回 null
 */
export function runAction(actionItem, executor) {
  const { mode, label, confirm } = actionItem;
  if (mode === 'confirm_action') {
    return ElMessageBox({
      title: '提示',
      message: confirm || `确定要${label || '执行该操作'}吗？`,
      type: 'warning',
      showCancelButton: true,
      cancelButtonText: '取消',
      confirmButtonText: '确定',
    })
      .then(() => executor())
      .catch(() => null);
  }
  return executor();
}

/**
 * @description 处理服务端动作响应指令集
 * @param {object} res 请求响应 {msg, data: {msg?, refresh?, redirect?, download?}}
 * @param {Function} refreshCallback 刷新回调（refresh 指令触发）
 * @returns {void}
 */
export function handleActionResponse(res, refreshCallback) {
  const data = res?.data || {};
  const message = data.msg || res?.msg || '操作成功';
  if (message) ElMessage.success(message);
  if (data.download) {
    window.open(data.download);
  }
  if (data.redirect) {
    router.push(data.redirect);
    return;
  }
  // 默认刷新（后端指令 refresh 显式为 false 时不刷新）
  if (data.refresh !== false && typeof refreshCallback === 'function') {
    refreshCallback();
  }
}
