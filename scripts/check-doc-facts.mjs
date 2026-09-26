#!/usr/bin/env node
// +----------------------------------------------------------------------
// | CRMEB 文档事实校验脚本
// +----------------------------------------------------------------------
// | 校验两类易漂移的开发资料事实：
// | 1. 代码事实数量：模块 / 驱动 / 默认安装 / MCP 内置工具 / 基础表 /
// |    开发规则 / 技能 / 子智能体数量，与各文档中的数字声明逐一比对
// | 2. 根入口文档链接：根目录各入口文件（README / AGENTS.md / CLAUDE.md /
// |    llms.txt 等）中的本地相对链接目标是否存在
// |
// | 用法：node scripts/check-doc-facts.mjs [--root 仓库根目录]
// | 退出码：0=通过；1=存在 error（可接入 CI / 提交流程拦截）
// | 维护：文档中的数字声明调整措辞后，同步更新下方 DOC_FACTS 注册表
// +----------------------------------------------------------------------
import { readdirSync, readFileSync, existsSync, statSync } from 'node:fs';
import { join, resolve, dirname, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
let repoRoot = resolve(scriptDir, '..');

// --root 参数：显式指定仓库根（脚本不在 scripts/ 下运行时使用）
const rootArg = process.argv.indexOf('--root');
if (rootArg > -1 && process.argv[rootArg + 1]) {
  repoRoot = resolve(process.argv[rootArg + 1]);
}

const errors = [];
const checked = { facts: 0, links: 0 };

function error(message) {
  errors.push(message);
}

function readText(relPath) {
  return readFileSync(join(repoRoot, relPath), 'utf8');
}

// ---------------------------------------------------------------------------
// 一、代码事实统计（以磁盘实现为唯一基线）
// ---------------------------------------------------------------------------

/** 递归收集目录下指定深度的 plugin.json（用于 modules 两层/三层嵌套） */
function findPluginJson(baseDir, maxDepth) {
  const found = [];
  const walk = (dir, depth) => {
    if (depth > maxDepth) return;
    let entries;
    try {
      entries = readdirSync(dir, { withFileTypes: true });
    } catch {
      return;
    }
    for (const entry of entries) {
      if (!entry.isDirectory() || entry.name.startsWith('.')) continue;
      const full = join(dir, entry.name);
      const manifest = join(full, 'plugin.json');
      if (existsSync(manifest)) found.push(manifest);
      walk(full, depth + 1);
    }
  };
  walk(baseDir, 1);
  return found;
}

function readManifest(file) {
  try {
    return JSON.parse(readFileSync(file, 'utf8'));
  } catch (e) {
    error(`plugin.json 解析失败: ${relative(repoRoot, file)}（${e.message}）`);
    return {};
  }
}

/** 模块：backend/modules 递归 plugin.json */
function statModules() {
  const files = findPluginJson(join(repoRoot, 'backend/modules'), 3);
  const modules = files.map((file) => {
    const json = readManifest(file);
    return {
      key: String(json.key ?? ''),
      required: json.required === true,
      defaultInstall: json.default_install === true,
    };
  });
  return {
    total: modules.length,
    required: modules.filter((m) => m.required).length,
    defaultOnly: modules.filter((m) => m.defaultInstall && !m.required).length,
    defaultAll: modules.filter((m) => m.defaultInstall || m.required).length,
    list: modules,
  };
}

/** 驱动：backend/driver/<类别>/<供应商>/plugin.json */
function statDrivers() {
  const files = findPluginJson(join(repoRoot, 'backend/driver'), 3);
  const drivers = files.map((file) => {
    const json = readManifest(file);
    return { key: String(json.key ?? ''), defaultInstall: json.default_install === true };
  });
  return {
    total: drivers.length,
    defaultInstall: drivers.filter((d) => d.defaultInstall).length,
    list: drivers,
  };
}

/** MCP 内置工具：tools/ 下以 Tool.php 结尾的类文件 */
function statMcpTools() {
  const dir = join(repoRoot, 'backend/modules/service/mcp/tools');
  let files = [];
  try {
    files = readdirSync(dir).filter((f) => f.endsWith('Tool.php'));
  } catch {
    error('未找到 backend/modules/service/mcp/tools/ 目录');
  }
  return {
    total: files.length,
    dev: files.filter((f) => /^Dev.+Tool\.php$/.test(f)).length,
  };
}

/** 基础表：安装 SQL 中的 CREATE TABLE 语句数 */
function statTables() {
  const sql = join(repoRoot, 'backend/public/install/crmeb_table.sql');
  if (!existsSync(sql)) {
    error('未找到 backend/public/install/crmeb_table.sql');
    return 0;
  }
  return (readFileSync(sql, 'utf8').match(/CREATE\s+TABLE/gi) ?? []).length;
}

/** AI 开发资产：规则 / 技能 / 子智能体数量 */
function statAssets() {
  const rulesDir = join(repoRoot, '.crmeb/rules');
  const skillsDir = join(repoRoot, '.crmeb/skills');
  const agentsDir = join(repoRoot, '.crmeb/agents');
  const countMd = (dir) => {
    try {
      return readdirSync(dir).filter((f) => f.endsWith('.md')).length;
    } catch {
      return 0;
    }
  };
  let skills = 0;
  try {
    skills = readdirSync(skillsDir, { withFileTypes: true })
      .filter((e) => e.isDirectory() && existsSync(join(skillsDir, e.name, 'SKILL.md')))
      .length;
  } catch {
    /* .crmeb 缺失时归零，由声明比对报错提示 */
  }
  return { rules: countMd(rulesDir), skills, agents: countMd(agentsDir) };
}

const facts = {
  modules: statModules(),
  drivers: statDrivers(),
  mcpTools: statMcpTools(),
  tables: statTables(),
  assets: statAssets(),
};

const FACT_LABELS = {
  'modules.total': `模块总数（backend/modules/**/plugin.json）`,
  'modules.required': `必装模块数（required=true）`,
  'modules.defaultOnly': `默认安装模块数（default_install=true 且非必装）`,
  'modules.defaultAll': `新装自动安装模块数（required 或 default_install）`,
  'drivers.total': `驱动总数（backend/driver/**/plugin.json）`,
  'drivers.defaultInstall': `默认安装驱动数（default_install=true）`,
  'mcpTools.total': `MCP 内置工具数（tools/*Tool.php）`,
  'mcpTools.dev': `MCP 开发知识工具数（Dev*Tool.php）`,
  'tables.total': `基础表数（crmeb_table.sql 的 CREATE TABLE）`,
  'assets.rules': `开发规则数（.crmeb/rules/*.md）`,
  'assets.skills': `开发技能数（.crmeb/skills/*/SKILL.md）`,
  'assets.agents': `子智能体数（.crmeb/agents/*.md）`,
};

// ---------------------------------------------------------------------------
// 二、文档数字声明注册表
// ---------------------------------------------------------------------------
// 每条声明 = { file, regex, checks }：
// - regex 需与文档中的声明行匹配，捕获组按顺序对应 checks 中的事实键
// - 文档措辞调整后需同步修改此处；新增数量声明时补一条注册
const DOC_FACTS = [
  {
    file: 'help/docs/模块与驱动.md',
    regex: /\|\s*业务模块\s*\|\s*`backend\/modules\/`\s*\|\s*(\d+) 个清单/,
    checks: ['modules.total'],
  },
  {
    file: 'help/docs/模块与驱动.md',
    regex: /\|\s*第三方驱动\s*\|\s*`backend\/driver\/`\s*\|\s*(\d+) 个清单/,
    checks: ['drivers.total'],
  },
  {
    file: 'help/docs/模块与驱动.md',
    regex: /新装自动安装 (\d+) 个模块（(\d+) 个必装 \+ (\d+) 个默认安装）/,
    checks: ['modules.defaultAll', 'modules.required', 'modules.defaultOnly'],
  },
  {
    file: 'help/docs/模块与驱动.md',
    regex: /新装自动安装 (\d+) 个驱动/,
    checks: ['drivers.defaultInstall'],
  },
  {
    file: 'help/docs/项目说明.md',
    regex: /当前可发现 (\d+) 个模块、(\d+) 个驱动/,
    checks: ['modules.total', 'drivers.total'],
  },
  {
    file: 'help/docs/项目说明.md',
    regex: /新装默认安装 (\d+) 个模块和 (\d+) 个驱动/,
    checks: ['modules.defaultAll', 'drivers.defaultInstall'],
  },
  {
    file: 'help/docs/文档索引.md',
    regex: /(\d+) 个模块、(\d+) 个驱动、默认安装清单/,
    checks: ['modules.total', 'drivers.total'],
  },
  {
    file: 'README_ZH.md',
    regex: /当前内置 (\d+) 个官方模块（[^）]*）与 (\d+) 个官方驱动/,
    checks: ['modules.total', 'drivers.total'],
  },
  {
    file: 'README_ZH.md',
    regex: /当前内置 (\d+) 个模块与 (\d+) 个驱动/,
    checks: ['modules.total', 'drivers.total'],
  },
  {
    file: 'README.md',
    regex: /(\d+) 条开发规则、(\d+) 个开发技能与 (\d+) 个专项子智能体/,
    checks: ['assets.rules', 'assets.skills', 'assets.agents'],
  },
  {
    file: '.crmeb/README.md',
    regex: /(\d+) 个专项技能，详细教程/,
    checks: ['assets.skills'],
  },
  {
    file: '.crmeb/README.md',
    regex: /(\d+) 个技能及按需加载的参考手册/,
    checks: ['assets.skills'],
  },
  {
    file: '.crmeb/README.md',
    regex: /(\d+) 个专项角色说明/,
    checks: ['assets.agents'],
  },
  {
    file: 'help/docs/api/MCP接口.md',
    regex: /当前自动发现 (\d+) 个内置工具，其中 (\d+) 个业务工具、(\d+) 个开发知识工具/,
    checks: ['mcpTools.total', 'mcpTools.business', 'mcpTools.dev'],
  },
];

/** 校验文档数字声明 */
function checkDocFacts() {
  const factValue = (kind) => {
    if (kind === 'mcpTools.business') return facts.mcpTools.total - facts.mcpTools.dev;
    const [group, key] = kind.split('.');
    return facts[group]?.[key];
  };
  const byFile = new Map();
  for (const rule of DOC_FACTS) {
    if (!byFile.has(rule.file)) byFile.set(rule.file, []);
    byFile.get(rule.file).push(rule);
  }
  for (const [file, rules] of byFile) {
    let content;
    try {
      content = readText(file);
    } catch {
      error(`声明校验目标不存在: ${file}`);
      continue;
    }
    const lines = content.split('\n');
    for (const rule of rules) {
      let matched = false;
      lines.forEach((line, idx) => {
        const m = line.match(rule.regex);
        if (!m) return;
        matched = true;
        rule.checks.forEach((kind, i) => {
          checked.facts += 1;
          const declared = Number(m[i + 1]);
          const actual = factValue(kind);
          if (Number.isNaN(declared)) {
            error(`${file}:${idx + 1} 数字声明无法解析（${kind}）`);
          } else if (declared !== actual) {
            error(
              `${file}:${idx + 1} 数字漂移：声明 ${declared}，代码实际 ${actual}（${FACT_LABELS[kind] ?? kind}）`,
            );
          }
        });
      });
      if (!matched) {
        error(`声明校验规则未匹配到任何行: ${file}（${rule.regex}），文档措辞可能已变化，请同步更新 scripts/check-doc-facts.mjs`);
      }
    }
  }
}

// ---------------------------------------------------------------------------
// 三、根入口文档链接校验
// ---------------------------------------------------------------------------

const ROOT_ENTRIES = [
  'README.md',
  'README_ZH.md',
  'AGENTS.md',
  'CLAUDE.md',
  'llms.txt',
  'CONTRIBUTING.md',
  '.crmeb/README.md',
];

/** 剥离围栏代码块，避免校验示例中的链接 */
function stripFencedBlocks(text) {
  return text.replace(/^```[^\n]*\n[\s\S]*?^```/gm, (block) => block.replace(/[^\n]/g, ''));
}

function collectLinks(text, allowAngle) {
  const links = [];
  // Markdown 链接 [text](target)，排除图片 ![alt](src)
  const mdLink = /(?<!\!)\[[^\]]*\]\(([^)\s]+)\)/g;
  // llms.txt 风格内联链接 <target>（其他入口文件含 HTML 标签，不适用）
  const angleLink = allowAngle ? /<(?!https?:)([^<>\s]+)>/g : null;
  for (const m of text.matchAll(mdLink)) links.push(m[1]);
  if (angleLink) for (const m of text.matchAll(angleLink)) links.push(m[1]);
  return links;
}

function checkRootLinks() {
  for (const entry of ROOT_ENTRIES) {
    const fullPath = join(repoRoot, entry);
    if (!existsSync(fullPath)) {
      error(`根入口文件缺失: ${entry}`);
      continue;
    }
    const text = stripFencedBlocks(readText(entry));
    const baseDir = dirname(fullPath);
    const allowAngle = entry === 'llms.txt';
    for (const raw of collectLinks(text, allowAngle)) {
      let target = raw;
      // 排除外部链接、邮件与页内锚点
      if (/^(https?:|mailto:|#)/i.test(target)) continue;
      // 去掉页内锚点与查询串
      target = target.split('#')[0].split('?')[0];
      if (target === '') continue;
      try {
        target = decodeURIComponent(target);
      } catch {
        /* 保留原样 */
      }
      checked.links += 1;
      const resolved = resolve(baseDir, target);
      if (!existsSync(resolved)) {
        error(`${entry}: 本地链接目标不存在 ${raw} → ${relative(repoRoot, resolved)}`);
      }
    }
  }
}

// ---------------------------------------------------------------------------
// 四、执行与输出
// ---------------------------------------------------------------------------

checkDocFacts();
checkRootLinks();

if (errors.length > 0) {
  console.error(`文档事实校验未通过（${errors.length} 个 error）：\n`);
  for (const e of errors) console.error(`  ${e}`);
  console.error(
    `\n基线统计：模块 ${facts.modules.total}（必装 ${facts.modules.required} + 默认 ${facts.modules.defaultOnly}）、` +
      `驱动 ${facts.drivers.total}（默认安装 ${facts.drivers.defaultInstall}）、` +
      `MCP 工具 ${facts.mcpTools.total}（开发知识 ${facts.mcpTools.dev}）、` +
      `基础表 ${facts.tables}、规则 ${facts.assets.rules} / 技能 ${facts.assets.skills} / 子智能体 ${facts.assets.agents}`,
  );
  process.exit(1);
}

console.log(
  `文档事实校验通过：数字声明 ${checked.facts} 处、根入口链接 ${checked.links} 条均与代码一致。` +
    `基线：模块 ${facts.modules.total}、驱动 ${facts.drivers.total}、MCP 工具 ${facts.mcpTools.total}、基础表 ${facts.tables}、` +
    `规则 ${facts.assets.rules} / 技能 ${facts.assets.skills} / 子智能体 ${facts.assets.agents}。`,
);
