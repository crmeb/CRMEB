// 全局 monaco 统一入口：编辑器核心 + 全部编辑器功能 + 常用语言语法高亮 + JSON 语言服务。
// 不引入 typescript/css/html 语言服务和全量语言包（全量 monaco 约 3MB+，且 ts 服务需要独立 worker，
// 当前 vite-plugin-monaco-editor 只打包 editor/json worker，全量引入时打开部分语言本就会报 worker 缺失）。
import * as monaco from 'monaco-editor/esm/vs/editor/editor.api';
import 'monaco-editor/esm/vs/editor/editor.all.js';
// basic-languages 为纯语法高亮（无独立 worker），覆盖文件管理器常编辑的文件类型
import 'monaco-editor/esm/vs/basic-languages/javascript/javascript.contribution';
import 'monaco-editor/esm/vs/basic-languages/typescript/typescript.contribution';
import 'monaco-editor/esm/vs/basic-languages/html/html.contribution';
import 'monaco-editor/esm/vs/basic-languages/css/css.contribution';
import 'monaco-editor/esm/vs/basic-languages/scss/scss.contribution';
import 'monaco-editor/esm/vs/basic-languages/less/less.contribution';
import 'monaco-editor/esm/vs/basic-languages/php/php.contribution';
import 'monaco-editor/esm/vs/basic-languages/xml/xml.contribution';
import 'monaco-editor/esm/vs/basic-languages/sql/sql.contribution';
import 'monaco-editor/esm/vs/basic-languages/shell/shell.contribution';
import 'monaco-editor/esm/vs/basic-languages/ini/ini.contribution';
import 'monaco-editor/esm/vs/basic-languages/markdown/markdown.contribution';
import 'monaco-editor/esm/vs/basic-languages/yaml/yaml.contribution';
// JSON 语言服务（校验/格式化），需要 json worker（vite.config 的 languageWorkers 已包含）
import 'monaco-editor/esm/vs/language/json/monaco.contribution';

export * from 'monaco-editor/esm/vs/editor/editor.api';
