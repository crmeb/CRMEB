// 客服聊天消息渲染：消息由用户输入，会经 v-html 输出，必须先转义再替换表情

const HTML_ESCAPES = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };

export function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (ch) => HTML_ESCAPES[ch]);
}

/**
 * 转义消息文本后把 [em-xxx] 替换为表情 span。
 * 表情名限定为 [\w-]+（与 utils/emoji.js 一致），防止通过类名拼接注入属性。
 */
export function replaceEm(str) {
  return escapeHtml(str).replace(/\[([\w-]+)\]/g, "<span class='em $1'></span>");
}
