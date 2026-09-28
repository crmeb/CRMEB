// CLI 调试：node scripts/uni-auto-subpackages.mjs [--print]
// 注意：本文件被 vite.config.js import，由 esbuild 内联打包，不能带 shebang 行
/**
 * pages-modules 自动分包注册（免改 src/pages.json）
 *
 * 机制：本版本 uni 编译器（@dcloudio 3.0.0-5020420260813003）没有官方"目录即路由"机制，
 * src/pages.json 文本是路由唯一输入；但 H5 / 各小程序 / APP 三端的全部消费点（路由生成、
 * easycom、分包优化等）都通过 fs.readFileSync 读取该文件。这里在 vite 配置加载期对
 * "读取 src/pages.json"做路径级拦截，把 src/pages-modules/ 扫描出的分包条目在内存中
 * append-only 合并进返回文本——磁盘上的 pages.json 保持字节不变，系统配置零改动。
 * uni 官方在 @dcloudio/vite-plugin-uni 内部用同样手法拦截 scss 读取实现条件编译
 * （rewriteScssReadFileSync）；@dcloudio/* 整组锁版升级后需回归验证（见 AGENTS.md）。
 *
 * 约定（详见 frontend/uni-app/AGENTS.md「pages-modules 自动分包注册」）：
 * - src/pages-modules/<snake_case 模块名>/ = 一个独立分包，root = pages-modules/<模块名>
 * - 模块内 .vue 自动注册为页面，路径 = /pages-modules/<模块名>/<相对路径去扩展名>；
 *   排除 components/、component/、static/ 目录及 `_` 前缀的文件/目录
 * - 模块根可放自己的 pages.json 片段（{ "pages": [{ "path", "style" }] }），
 *   提供后该模块不再自动扫描，完全按片段注册
 * - 只追加 subPackages，绝不触碰 pages/tabBar/globalStyle/easycom 等系统字段
 *
 * CLI 调试：node scripts/uni-auto-subpackages.mjs [--print]
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { parse, parseTree, findNodeAtLocation, getNodeValue } from 'jsonc-parser';

const ROOT = path.resolve(fileURLToPath(new URL('..', import.meta.url)));
const PAGES_JSON_PATH = path.join(ROOT, 'src', 'pages.json');
const MODULES_DIR = path.join(ROOT, 'src', 'pages-modules');
const MODULES_ROOT_PREFIX = 'pages-modules';
// uni jsonJs 插件把虚拟模块 pages-json-js 解析为 <src>/pages-json-js，dev 下用于失效路由模块
const PAGES_JSON_JS_IDS = [path.join(ROOT, 'src', 'pages-json-js')];
// 模块内不参与页面注册的目录，与 scripts/check-naming.mjs 的 ROLE_DIRS 对齐
const ROLE_DIRS = new Set(['components', 'component', 'static']);

const toPosix = (p) => p.split(path.sep).join('/');

/** 递归收集模块内可注册为页面的 .vue 相对路径（排除 `_` 前缀与角色目录） */
function walkVueFiles(dir, base, out) {
	for (const name of fs.readdirSync(dir)) {
		if (name.startsWith('_') || name.startsWith('.')) continue;
		const full = path.join(dir, name);
		if (fs.statSync(full).isDirectory()) {
			if (ROLE_DIRS.has(name)) continue;
			walkVueFiles(full, base, out);
		} else if (name.endsWith('.vue')) {
			out.push(toPosix(path.relative(base, full)).replace(/\.vue$/, ''));
		}
	}
}

/** 读取模块自带的 pages.json 片段（JSONC），不存在返回 null */
function readModuleFragment(moduleDir) {
	const fragPath = path.join(moduleDir, 'pages.json');
	if (!fs.existsSync(fragPath)) return null;
	const errors = [];
	const value = parse(fs.readFileSync(fragPath, 'utf8'), errors, { allowTrailingComma: true });
	if (errors.length) {
		throw new Error(`${toPosix(path.relative(ROOT, fragPath))} 解析失败（第 ${errors[0].offset} 字符附近）`);
	}
	return value;
}

/** 扫描单个模块，生成分包条目；无页面时返回 null */
function scanModule(name, moduleDir) {
	const root = `${MODULES_ROOT_PREFIX}/${name}`;
	const frag = readModuleFragment(moduleDir);
	let pages;
	if (frag) {
		if (!Array.isArray(frag.pages) || frag.pages.length === 0) {
			throw new Error(`${root}/pages.json 必须提供非空 pages 数组`);
		}
		pages = frag.pages.map((page, i) => {
			if (!page || typeof page.path !== 'string' || !page.path.trim()) {
				throw new Error(`${root}/pages.json 第 ${i + 1} 项缺少 path`);
			}
			return {
				path: page.path.trim().replace(/^\.?\//, '').replace(/\.vue$/, ''),
				style: page.style || {}
			};
		});
	} else {
		const found = [];
		walkVueFiles(moduleDir, moduleDir, found);
		pages = found.sort().map((p) => ({ path: p, style: {} }));
		if (pages.length === 0) return null;
	}
	return { root, pages };
}

/** 扫描 src/pages-modules/ 一级目录，产出按模块名排序的分包条目列表 */
export function collectSubpackageEntries() {
	if (!fs.existsSync(MODULES_DIR)) return [];
	return fs
		.readdirSync(MODULES_DIR)
		.filter((name) => {
			if (name.startsWith('_') || name.startsWith('.')) return false;
			return fs.statSync(path.join(MODULES_DIR, name)).isDirectory();
		})
		.sort()
		.map((name) => scanModule(name, path.join(MODULES_DIR, name)))
		.filter(Boolean);
}

const stripComments = (s) => s.replace(/\/\/[^\n\r]*/g, '').replace(/\/\*[\s\S]*?\*\//g, '');

/** 把分包条目以文本方式注入 pages.json 原文（增量编辑，其余内容逐字节保留） */
function injectSubPackages(text, entries) {
	const tree = parseTree(text);
	if (!tree || tree.type !== 'object') return text;
	const snippet = entries.map((e) => JSON.stringify(e, null, 2)).join(',\n');
	const prop = findNodeAtLocation(tree, ['subPackages']);
	if (!prop) {
		// 系统配置没有 subPackages 时在根对象末尾追加属性（当前工程不适用，仅兜底）
		const closeIdx = tree.offset + tree.length - 1;
		const children = tree.children || [];
		const lastEnd = children.length ? children[children.length - 1].offset + children[children.length - 1].length : tree.offset + 1;
		const needComma = children.length > 0 && !stripComments(text.slice(lastEnd, closeIdx)).includes(',');
		return (
			text.slice(0, closeIdx) +
			`${needComma ? ',' : ''}\n\t"subPackages": [\n\t\t${snippet}\n\t]\n` +
			text.slice(closeIdx)
		);
	}
	const arr = prop;
	if (arr.type !== 'array') return text;
	// 插入点选在闭括号 `]` 前：天然位于所有元素级 `// #ifdef ... // #endif` 块之后，
	// 不会被条件编译裁掉；逗号按“最后一个元素与 ] 之间（去注释后）是否已有逗号”判断
	const closeIdx = arr.offset + arr.length - 1;
	const children = arr.children || [];
	const lastEnd = children.length ? children[children.length - 1].offset + children[children.length - 1].length : arr.offset + 1;
	const needComma = children.length > 0 && !stripComments(text.slice(lastEnd, closeIdx)).includes(',');
	return text.slice(0, closeIdx) + `${needComma ? ',' : ''}\n\t\t${snippet}\n\t` + text.slice(closeIdx);
}

/** 校验合并结果：仍是合法 JSONC 对象，且每个模块分包都在 subPackages 中完整存在 */
function verifyMerged(mergedText, entries) {
	const errors = [];
	const value = parse(mergedText, errors, { allowTrailingComma: true, disallowComments: false });
	if (errors.length || !value || !Array.isArray(value.subPackages)) return false;
	return entries.every((e) => {
		const found = value.subPackages.find((v) => v && v.root === e.root);
		return !!found && Array.isArray(found.pages) && found.pages.length === e.pages.length;
	});
}

/** 扫描模块并把分包条目合并进 pages.json 原文（内存操作，不写盘）；异常时回退原文 */
export function mergePagesJsonText(originalText) {
	const entries = collectSubpackageEntries();
	if (entries.length === 0) return originalText;
	const merged = injectSubPackages(originalText, entries);
	if (!verifyMerged(merged, entries)) {
		console.warn('[uni-auto-subpackages] 合并结果自检失败，本次读取回退原始 pages.json');
		return originalText;
	}
	return merged;
}

let installed = false;

/**
 * 安装 fs.readFileSync 拦截：精确匹配 src/pages.json 的字符串读取，
 * 返回合并了 pages-modules 分包的文本；其余读取零开销透传。
 * 必须在 uni() 插件首次读取 pages.json 之前调用（vite.config.js 配置体内、return 之前）。
 */
export function installAutoSubpackages() {
	if (installed) return;
	installed = true;
	if (!fs.existsSync(PAGES_JSON_PATH)) return;
	const origReadFileSync = fs.readFileSync;
	const isPagesJson = (file) => {
		try {
			return typeof file === 'string' && path.resolve(file) === PAGES_JSON_PATH;
		} catch {
			return false;
		}
	};
	fs.readFileSync = function readFileSyncWithPagesModules(file, options) {
		const content = origReadFileSync.call(fs, file, options);
		if (typeof content === 'string' && isPagesJson(file)) {
			try {
				return mergePagesJsonText(content);
			} catch (e) {
				console.warn(`[uni-auto-subpackages] 合并失败，回退原始 pages.json：${e && e.message}`);
				return content;
			}
		}
		return content;
	};
	const entries = collectSubpackageEntries();
	if (entries.length) {
		const summary = entries.map((e) => `${e.root}(${e.pages.length}页)`).join('、');
		console.log(`[uni-auto-subpackages] 已注入模块分包：${summary}`);
	}
}

/**
 * dev 增量生效插件：监听 src/pages-modules/，文件增删改时 touch 一次 pages.json 的
 * mtime（内容不变），借 uni 自己的 addWatchFile(pagesJsonPath) 链路触发重建
 * （H5 整页刷新 / MP 重新产出 app.json）。
 * 两种 dev 形态分别接管：
 * - H5 dev（vite dev server）：configureServer 挂 server.watcher，并失效 pages-json-js 虚拟模块
 * - MP dev（vite build --watch，无 dev server）：buildStart 且 watchMode 时自建 chokidar 监听
 * 一次性 build（watchMode 为 false）不建监听，零开销。
 */
export function uniAutoSubpackagesPlugin() {
	let timer = null;
	let chokidarWatcher = null;
	let serverWatcherInstalled = false;

	const handleModuleChange = (server) => {
		clearTimeout(timer);
		timer = setTimeout(() => {
			try {
				const now = new Date();
				fs.utimesSync(PAGES_JSON_PATH, now, now);
			} catch {
				/* pages.json 必然存在，忽略 */
			}
			if (server) {
				for (const id of PAGES_JSON_JS_IDS) {
					const mod = server.moduleGraph.getModuleById(id);
					if (mod) server.moduleGraph.invalidateModule(mod);
				}
				server.ws.send({ type: 'full-reload' });
			}
		}, 200);
	};

	const isUnderModulesDir = (fp) => {
		const p = path.resolve(String(fp));
		return p === MODULES_DIR || p.startsWith(MODULES_DIR + path.sep);
	};

	return {
		name: 'uni-auto-subpackages',
		configureServer(server) {
			if (!fs.existsSync(MODULES_DIR)) return;
			serverWatcherInstalled = true;
			server.watcher.add(MODULES_DIR);
			const trigger = (fp) => {
				if (isUnderModulesDir(fp)) handleModuleChange(server);
			};
			server.watcher.on('add', trigger);
			server.watcher.on('unlink', trigger);
			server.watcher.on('change', trigger);
		},
		async buildStart() {
			if (!this.meta.watchMode || serverWatcherInstalled || chokidarWatcher) return;
			if (!fs.existsSync(MODULES_DIR)) return;
			let chokidar;
			try {
				chokidar = (await import('chokidar')).default;
			} catch {
				console.warn('[uni-auto-subpackages] 缺少 chokidar，dev watch 下新增模块页面需手动重启（build 不受影响）；可 npm i -D chokidar');
				return;
			}
			chokidarWatcher = chokidar.watch(MODULES_DIR, { ignoreInitial: true });
			const trigger = (fp) => {
				if (isUnderModulesDir(fp)) handleModuleChange(null);
			};
			chokidarWatcher.on('add', trigger);
			chokidarWatcher.on('unlink', trigger);
			chokidarWatcher.on('change', trigger);
		},
		closeWatcher() {
			if (chokidarWatcher) {
				const w = chokidarWatcher;
				chokidarWatcher = null;
				w.close().catch(() => {});
			}
		}
	};
}

// CLI 调试：node scripts/uni-auto-subpackages.mjs [--print]
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
	const entries = collectSubpackageEntries();
	if (entries.length === 0) {
		console.log('未发现可注册模块（src/pages-modules/ 为空或仅含排除目录）');
	} else {
		for (const e of entries) {
			console.log(`▸ ${e.root}（${e.pages.length} 页）`);
			for (const p of e.pages) console.log(`    /${e.root}/${p.path}`);
		}
	}
	const merged = mergePagesJsonText(fs.readFileSync(PAGES_JSON_PATH, 'utf8'));
	const subCount = getNodeValue(findNodeAtLocation(parseTree(merged), ['subPackages']) || {}).length;
	console.log(`合并自检：${verifyMerged(merged, entries) ? '通过' : '失败'}（subPackages 共 ${subCount} 个分包）`);
	if (process.argv.includes('--print')) console.log('\n' + merged);
}
