import request from '@/utils/request.js';

/**
 * 已登录端签发插件 webview 票据
 * @param {string} path 插件页面路径
 */
export function createWebviewTicket(path) {
	return request.post('webview/ticket', { path });
}

/**
 * 插件页用票据换 token
 * @param {string} ticket
 * @param {string} path 必须与签发时一致
 */
export function exchangeWebviewTicket(ticket, path) {
	return request.post('webview/ticket/exchange', { ticket, path }, { noAuth: true });
}
