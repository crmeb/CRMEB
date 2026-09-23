import fs from 'node:fs'
import path from 'node:path'

export const requiredDirectories = ['.crmeb', 'backend', 'frontend', 'help/docs', 'tests']
export const defaultProcessDirectory = 'tests'
export const requiredCommands = [
  'doctor', 'setup', 'dev', 'stop', 'status', 'logs', 'check', 'test', 'build',
  'upgradePlan', 'upgradePackage', 'upgradeVerify',
]
export const requiredDockerCommands = ['restart', 'down', 'shell', 'smoke', 'resetData']
export const requiredAiEntrypoints = [
  'AGENTS.md', 'CLAUDE.md', 'GEMINI.md', '.github/copilot-instructions.md',
  '.cursor/rules/project-standard.mdc', 'llms.txt',
]

export function pathExists(target) {
  return fs.existsSync(target)
}

export function readJson(file) {
  try {
    return { value: JSON.parse(fs.readFileSync(file, 'utf8')), error: null }
  }
  catch (error) {
    return { value: null, error: error.message }
  }
}

/** 过程文档目录名以契约为准，缺省回退到 defaultProcessDirectory，不硬编码 */
export function resolveProcessDirectory(contract) {
  const value = contract?.directories?.process
  if (typeof value !== 'string' || !value.trim()) return defaultProcessDirectory
  return value.trim().replace(/^\.\//, '').replace(/\/+$/, '')
}

/** 解析 Markdown frontmatter 的简单键值；支持 `key: value` 与 `key:` + `- item` 列表 */
export function readFrontmatter(file) {
  if (!pathExists(file)) return null
  const match = fs.readFileSync(file, 'utf8').match(/^---\r?\n([\s\S]*?)\r?\n---/)
  if (!match) return null
  const data = {}
  let currentKey = ''
  for (const line of match[1].split(/\r?\n/)) {
    const item = line.match(/^\s*-\s+(.+?)\s*$/)
    if (item && currentKey) {
      if (!Array.isArray(data[currentKey])) data[currentKey] = data[currentKey] ? [data[currentKey]] : []
      data[currentKey].push(item[1].replace(/^['"]|['"]$/g, ''))
      continue
    }
    const pair = line.match(/^([A-Za-z][\w-]*):\s*(.*)$/)
    if (!pair) continue
    currentKey = pair[1]
    data[currentKey] = pair[2].trim().replace(/^['"]|['"]$/g, '') || []
  }
  return data
}

export function walkFiles(root, options = {}) {
  const ignored = new Set(options.ignored || ['.git', 'node_modules', 'vendor', 'dist', 'build', 'runtime'])
  const result = []
  if (!pathExists(root)) return result

  const visit = (current) => {
    for (const entry of fs.readdirSync(current, { withFileTypes: true })) {
      if (ignored.has(entry.name)) continue
      const absolute = path.join(current, entry.name)
      if (entry.isDirectory()) visit(absolute)
      else if (entry.isFile()) result.push(absolute)
    }
  }
  visit(root)
  return result
}

export function detectApplications(root) {
  const applications = []
  const backend = path.join(root, 'backend')
  if (pathExists(backend)) {
    let runtime = 'unknown'
    let versionSource = ''
    const candidates = [
      ['composer.json', 'php'], ['package.json', 'node'], ['pyproject.toml', 'python'],
      ['pom.xml', 'java'], ['build.gradle', 'java'], ['build.gradle.kts', 'java'], ['go.mod', 'go'],
    ]
    for (const [file, name] of candidates) {
      if (pathExists(path.join(backend, file))) {
        runtime = name
        versionSource = `backend/${file}`
        break
      }
    }
    applications.push({ id: 'backend', kind: 'backend', path: 'backend', runtime, versionSource })
  }

  const frontend = path.join(root, 'frontend')
  if (pathExists(frontend)) {
    for (const entry of fs.readdirSync(frontend, { withFileTypes: true })) {
      if (!entry.isDirectory() || entry.name.startsWith('.')) continue
      const relative = `frontend/${entry.name}`
      const appRoot = path.join(root, relative)
      if (pathExists(path.join(appRoot, 'package.json')) || pathExists(path.join(appRoot, 'pubspec.yaml'))) {
        applications.push({
          id: `frontend-${entry.name}`,
          kind: 'frontend',
          path: relative,
          runtime: pathExists(path.join(appRoot, 'package.json')) ? 'node' : 'flutter',
          versionSource: pathExists(path.join(appRoot, 'package.json')) ? `${relative}/package.json` : `${relative}/pubspec.yaml`,
        })
      }
    }
  }
  return applications
}

/**
 * 校验 .crmeb/skills/ 技能包骨架与应用覆盖：
 * 每个技能为 SKILL.md + references/；声明 appPath 的技能必须提供 directoryGuide 与 coreFeatures；
 * project.json 的每个应用都要有对应技能。
 */
export function collectSkillFindings(root, contract) {
  const findings = []
  const add = (severity, code, message) => findings.push({ severity, code, message })
  const skillsRoot = path.join(root, '.crmeb/skills')

  if (!pathExists(skillsRoot)) {
    add('error', 'missing-skills', '缺少 .crmeb/skills/：架构认知与核心功能开发文档按技能组织，目录不可缺省')
    return findings
  }

  const agentsFile = path.join(root, '.crmeb/AGENTS.md')
  const agentIndex = pathExists(agentsFile) ? fs.readFileSync(agentsFile, 'utf8') : ''
  const declaredPaths = new Set()
  let skillCount = 0

  for (const entry of fs.readdirSync(skillsRoot, { withFileTypes: true })) {
    if (!entry.isDirectory() || entry.name.startsWith('.')) continue
    skillCount += 1
    const skillDir = path.join(skillsRoot, entry.name)
    const skillFile = path.join(skillDir, 'SKILL.md')

    if (!pathExists(skillFile)) {
      add('error', 'missing-skill-file', `.crmeb/skills/${entry.name}/ 缺少 SKILL.md`)
      continue
    }
    if (!pathExists(path.join(skillDir, 'references'))) {
      add('error', 'missing-skill-references', `.crmeb/skills/${entry.name}/ 缺少 references/：技能骨架为 SKILL.md + references/`)
    }
    if (!agentIndex.includes(`skills/${entry.name}/SKILL.md`)) {
      add('warning', 'unregistered-skill', `.crmeb/skills/${entry.name}/ 未登记到 .crmeb/AGENTS.md 的技能索引`)
    }

    const frontmatter = readFrontmatter(skillFile)
    if (!frontmatter) {
      add('error', 'missing-skill-frontmatter', `.crmeb/skills/${entry.name}/SKILL.md 缺少 frontmatter`)
      continue
    }
    if (frontmatter.name !== entry.name) {
      add('error', 'skill-name-mismatch', `.crmeb/skills/${entry.name}/SKILL.md 的 name 必须与目录名一致，当前为 ${frontmatter.name || '(空)'}`)
    }
    if (!frontmatter.description) {
      add('error', 'skill-description', `.crmeb/skills/${entry.name}/SKILL.md 缺少非空 description`)
    }

    const skillDocs = walkFiles(skillDir).filter(file => file.endsWith('.md'))
    if (skillDocs.some(file => /\{\{|TODO/.test(fs.readFileSync(file, 'utf8')))) {
      add('warning', 'skill-placeholder', `.crmeb/skills/${entry.name}/ 仍含 TODO 或占位符：目录说明与核心功能文档尚未按当前代码补全`)
    }

    const appPath = typeof frontmatter.appPath === 'string' ? frontmatter.appPath.replace(/\/+$/, '') : ''
    if (!appPath) continue
    declaredPaths.add(appPath)

    const coreFeatures = Array.isArray(frontmatter.coreFeatures) ? frontmatter.coreFeatures : []
    if (!frontmatter.directoryGuide) {
      add('error', 'missing-directory-guide', `.crmeb/skills/${entry.name}/SKILL.md 缺少 directoryGuide：每个目录必须有项目目录说明`)
    }
    if (!coreFeatures.length) {
      add('error', 'missing-core-features', `.crmeb/skills/${entry.name}/SKILL.md 缺少 coreFeatures：每个目录必须有核心功能开发文档`)
    }
    for (const relative of [frontmatter.directoryGuide, ...coreFeatures].filter(Boolean)) {
      if (!pathExists(path.resolve(skillDir, relative))) {
        add('error', 'missing-skill-doc', `.crmeb/skills/${entry.name}/ 引用的文档不存在：${relative}`)
      }
    }
  }

  if (!skillCount) {
    add('error', 'empty-skills', '.crmeb/skills/ 下没有技能：后端与每个独立前端应用都需要一个架构认知技能')
  }

  const applicationPaths = new Set(
    (contract?.applications || []).map(app => String(app.path || '').replace(/\/+$/, '')).filter(Boolean),
  )
  for (const app of contract?.applications || []) {
    const appPath = String(app.path || '').replace(/\/+$/, '')
    if (!appPath || declaredPaths.has(appPath)) continue
    add('error', 'missing-app-skill', `应用 ${app.id || appPath}（${appPath}）没有架构认知技能：需要 .crmeb/skills/<技能>/SKILL.md 声明 appPath: ${appPath}`)
  }
  for (const appPath of declaredPaths) {
    if (!applicationPaths.has(appPath)) {
      add('warning', 'stale-app-skill', `技能声明的 appPath 不在 project.json 的 applications 中：${appPath}`)
    }
  }

  return findings
}

export function collectFindings(root) {
  const findings = []
  const add = (severity, code, message) => findings.push({ severity, code, message })

  const contractPath = path.join(root, '.crmeb/project.json')
  const contractResult = pathExists(contractPath) ? readJson(contractPath) : { value: null, error: null }
  const processDirectory = resolveProcessDirectory(contractResult.value)

  for (const relative of new Set([...requiredDirectories, processDirectory])) {
    if (!pathExists(path.join(root, relative))) add('error', 'missing-directory', `缺少统一目录：${relative}/`)
  }

  const rootAgents = path.join(root, 'AGENTS.md')
  if (!pathExists(rootAgents)) add('error', 'missing-entry', '缺少根 AGENTS.md')
  else if (!fs.readFileSync(rootAgents, 'utf8').includes('.crmeb/AGENTS.md')) {
    add('error', 'invalid-entry', '根 AGENTS.md 未指向 .crmeb/AGENTS.md')
  }

  if (!pathExists(path.join(root, '.crmeb/AGENTS.md'))) add('error', 'missing-ai-source', '缺少 .crmeb/AGENTS.md')

  for (const relative of requiredAiEntrypoints) {
    const entry = path.join(root, relative)
    if (!pathExists(entry)) {
      add('error', 'missing-ai-entry', `缺少 AI 工具入口：${relative}`)
      continue
    }
    if (!fs.readFileSync(entry, 'utf8').includes('.crmeb/AGENTS.md')) {
      add('error', 'invalid-ai-entry', `AI 工具入口未指向 .crmeb/AGENTS.md：${relative}`)
    }
  }

  if (!pathExists(contractPath)) {
    add('error', 'missing-contract', '缺少 .crmeb/project.json')
    return findings
  }

  const { value: contract, error } = contractResult
  if (error) {
    add('error', 'invalid-contract', `.crmeb/project.json 无法解析：${error}`)
    return findings
  }

  if (contract.schemaVersion !== 1) add('error', 'contract-schema', 'project.json schemaVersion 必须为 1')
  if (contract.status !== 'active') add('warning', 'contract-draft', 'project.json 尚未标记为 active')
  if (contract.status === 'active' && !contract.docker?.enabled) {
    add('error', 'docker-required', 'active 项目必须根据当前后端环境提供根 docker-compose.yml，并在 project.json 启用 docker')
  }
  if (!contract.project?.name) add('error', 'project-name', 'project.json 缺少 project.name')

  const commandsToCheck = contract.docker?.enabled
    ? [...requiredCommands, ...requiredDockerCommands]
    : requiredCommands
  for (const command of commandsToCheck) {
    const value = contract.commands?.[command]
    if (!value) {
      add('error', 'missing-command', `project.json 缺少标准命令：${command}`)
      continue
    }
    const parts = value.trim().split(/\s+/).map(part => part.replace(/^['"]|['"]$/g, ''))
    const executable = parts[0]
    if ((executable.startsWith('./') || executable.startsWith('../')) && !pathExists(path.resolve(root, executable))) {
      add('error', 'missing-command-entry', `标准命令 ${command} 的本地入口不存在：${executable}`)
    }
    if (['node', 'php', 'python', 'python3', 'bash', 'sh'].includes(executable)) {
      const script = parts[1]
      if (script && (script.startsWith('./') || script.startsWith('../') || script.includes('/')) && !pathExists(path.resolve(root, script))) {
        add('error', 'missing-command-script', `标准命令 ${command} 的脚本不存在：${script}`)
      }
    }
  }

  for (const app of contract.applications || []) {
    if (!app.id || !app.path) add('error', 'invalid-application', 'applications 条目必须包含 id 和 path')
    else if (!pathExists(path.join(root, app.path))) add('error', 'missing-application-path', `应用路径不存在：${app.path}`)
  }

  findings.push(...collectSkillFindings(root, contract))

  for (const relative of contract.documentation?.required || []) {
    if (!pathExists(path.join(root, relative))) {
      add('error', 'missing-documentation', `project.json 声明的必备文档不存在：${relative}`)
    }
  }

  if (contract.docker?.enabled) {
    for (const relative of ['docker-compose.yml', 'run.sh', 'docker/README.md', 'help/docs/docker-development.md']) {
      if (!pathExists(path.join(root, relative))) add('error', 'missing-docker-asset', `Docker 本地开发缺少：${relative}`)
    }
  }

  return findings
}

export function collectMarkdownLinkFindings(root) {
  const findings = []
  const { value: contract } = readJson(path.join(root, '.crmeb/project.json'))
  const roots = [...new Set(['.crmeb', 'help/docs', 'tests', resolveProcessDirectory(contract)])]
  const files = [
    path.join(root, 'README.md'), path.join(root, 'AGENTS.md'), path.join(root, 'CLAUDE.md'),
    path.join(root, 'GEMINI.md'), path.join(root, '.github/copilot-instructions.md'),
  ]
  for (const relative of roots) {
    for (const file of walkFiles(path.join(root, relative))) {
      if (file.endsWith('.md')) files.push(file)
    }
  }

  const linkPattern = /\[[^\]]*\]\(([^)]+)\)/g
  for (const file of [...new Set(files)].filter(pathExists)) {
    const lines = fs.readFileSync(file, 'utf8').split(/\r?\n/)
    let fenced = false
    lines.forEach((line, index) => {
      if (/^\s*```/.test(line)) {
        fenced = !fenced
        return
      }
      if (fenced) return
      for (const match of line.matchAll(linkPattern)) {
        const raw = match[1].trim().replace(/^<|>$/g, '')
        if (!raw || /^(https?:|mailto:|#|codex:)/i.test(raw)) continue
        const clean = decodeURIComponent(raw.split('#')[0].split('?')[0])
        if (!clean) continue
        const target = path.resolve(path.dirname(file), clean)
        if (!pathExists(target)) {
          findings.push({
            severity: 'error',
            code: 'broken-link',
            message: `${path.relative(root, file)}:${index + 1} 链接目标不存在：${raw}`,
          })
        }
      }
    })
  }
  return findings
}

export function printFindings(findings) {
  if (!findings.length) {
    console.log('✅ 项目统一规范检查通过')
    return
  }
  for (const finding of findings) {
    const icon = finding.severity === 'error' ? '❌' : '⚠️'
    console.log(`${icon} [${finding.code}] ${finding.message}`)
  }
  const errors = findings.filter(item => item.severity === 'error').length
  const warnings = findings.length - errors
  console.log(`\n汇总：${errors} error / ${warnings} warning`)
}
