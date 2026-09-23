#!/usr/bin/env node
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { defaultProcessDirectory, detectApplications, pathExists, requiredCommands, requiredDockerCommands, walkFiles } from './lib.mjs'

const args = process.argv.slice(2)
const dryRun = args.includes('--dry-run')
const targetArg = args.find(arg => !arg.startsWith('--')) || process.cwd()
const target = path.resolve(targetArg)

if (!pathExists(target) || !fs.statSync(target).isDirectory()) {
  console.error(`目标仓库不存在或不是目录：${target}`)
  process.exit(2)
}

const scriptDir = path.dirname(fileURLToPath(import.meta.url))
const templateRoot = path.resolve(scriptDir, '../assets/project-template')
const skillSkeletonRoot = path.resolve(scriptDir, '../assets/app-skill-skeleton')
const applications = detectApplications(target)
const actions = []

function ensureDirectory(relative) {
  const destination = path.join(target, relative)
  if (pathExists(destination)) return
  actions.push(`创建目录 ${relative}/`)
  if (!dryRun) fs.mkdirSync(destination, { recursive: true })
}

for (const relative of [
  '.crmeb/rules', '.crmeb/skills', '.crmeb/agents', '.crmeb/scopes',
  'backend', 'frontend', 'help/docs', 'docker', 'tests',
  `${defaultProcessDirectory}/plans`, `${defaultProcessDirectory}/reports`,
  'upgrades/versions', 'upgrades/packages',
]) ensureDirectory(relative)

for (const source of walkFiles(templateRoot, { ignored: [] })) {
  const relative = path.relative(templateRoot, source)
  const destination = path.join(target, relative)
  if (pathExists(destination)) {
    actions.push(`保留已有 ${relative}`)
    continue
  }
  actions.push(`创建文件 ${relative}`)
  if (!dryRun) {
    fs.mkdirSync(path.dirname(destination), { recursive: true })
    fs.copyFileSync(source, destination)
    if (relative === 'run.sh') fs.chmodSync(destination, 0o755)
  }
}

/** 把技能骨架占位符替换为目标应用的真实信息 */
function renderSkillFile(content, app) {
  return content
    .replaceAll('{{SKILL_NAME}}', `${app.id}-dev`)
    .replaceAll('{{APP_ID}}', app.id)
    .replaceAll('{{APP_PATH}}', app.path)
    .replaceAll('{{RUNTIME}}', app.runtime || 'unknown')
}

for (const app of applications) {
  const skillRelative = `.crmeb/skills/${app.id}-dev`
  ensureDirectory(skillRelative)
  for (const source of walkFiles(skillSkeletonRoot, { ignored: [] })) {
    const relative = path.relative(skillSkeletonRoot, source)
    const destination = path.join(target, skillRelative, relative)
    if (pathExists(destination)) {
      actions.push(`保留已有 ${skillRelative}/${relative}`)
      continue
    }
    actions.push(`创建文件 ${skillRelative}/${relative}`)
    if (!dryRun) {
      fs.mkdirSync(path.dirname(destination), { recursive: true })
      fs.writeFileSync(destination, renderSkillFile(fs.readFileSync(source, 'utf8'), app))
    }
  }
}

const agentsPath = path.join(target, '.crmeb/AGENTS.md')
if (applications.length && pathExists(agentsPath)) {
  const content = fs.readFileSync(agentsPath, 'utf8')
  const missing = applications
    .map(app => ({
      link: `skills/${app.id}-dev/SKILL.md`,
      row: `| [${app.id}-dev](skills/${app.id}-dev/SKILL.md) | 修改 \`${app.path}\` 前，先读该端的项目目录说明与核心功能开发 |`,
    }))
    .filter(item => !content.includes(item.link))

  if (missing.length) {
    actions.push(`更新文件 .crmeb/AGENTS.md（登记 ${missing.length} 个技能）`)
    if (!dryRun) {
      const lines = content.split(/\r?\n/)
      const heading = lines.findIndex(line => line.trim() === '## 技能索引')
      let insertAt
      if (heading < 0) {
        lines.push('', '## 技能索引', '', '| 技能 | 何时读取 |', '| --- | --- |')
        insertAt = lines.length
      } else {
        const separator = lines.findIndex((line, index) => index > heading && /^\|\s*-{2,}/.test(line))
        insertAt = separator >= 0 ? separator + 1 : heading + 1
      }
      lines.splice(insertAt, 0, ...missing.map(item => item.row))
      fs.writeFileSync(agentsPath, `${lines.join('\n').replace(/\n+$/, '')}\n`)
    }
  }
}

const contractPath = path.join(target, '.crmeb/project.json')
if (!pathExists(contractPath)) {
  const commands = Object.fromEntries([...requiredCommands, ...requiredDockerCommands].map(command => [command, '']))
  const contract = {
    schemaVersion: 1,
    status: 'draft',
    project: { name: path.basename(target), type: 'fullstack', versionSource: '' },
    directories: {
      backend: 'backend', frontend: 'frontend', docs: 'help/docs',
      process: defaultProcessDirectory, tests: 'tests', upgrades: 'upgrades',
    },
    applications,
    services: [],
    docker: { enabled: false, composeFile: 'docker-compose.yml', configRoot: 'docker' },
    documentation: {
      required: [
        'README.md', 'help/docs/README.md', 'help/docs/development.md',
        'help/docs/architecture.md', 'help/docs/testing.md',
      ],
    },
    commands,
  }
  actions.push('创建文件 .crmeb/project.json（draft，需按真实项目补全）')
  if (!dryRun) fs.writeFileSync(contractPath, `${JSON.stringify(contract, null, 2)}\n`)
}
else actions.push('保留已有 .crmeb/project.json')

console.log(dryRun ? '项目规范初始化预览：' : '项目规范初始化结果：')
for (const action of actions) console.log(`- ${action}`)
console.log('\n不会覆盖现有文件。补全 project.json 后运行 check-project-standard.mjs。')
