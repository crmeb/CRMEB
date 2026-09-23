import MarkdownIt from 'markdown-it';

/**
 * AI 回复 Markdown 渲染器。
 *
 * html:false 时 markdown-it 会转义全部原始 HTML 标签，XSS 已在源头防护，
 * 渲染结果无需再引入 DOMPurify（该依赖未在 package.json 声明，避免幽灵依赖）。
 */
const markdown = new MarkdownIt({
  html: false,
  breaks: true,
  linkify: true,
});

/** 将模型回复安全地渲染为 Markdown，保留表格、分隔线和代码块等语义。 */
export function renderMarkdown(content) {
  return markdown.render(String(content || ''));
}
