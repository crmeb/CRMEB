#!/usr/bin/env node
import fs from 'node:fs'
import path from 'node:path'

const args = process.argv.slice(2)
const write = args.includes('--write')
const target = path.resolve(args.find(arg => !arg.startsWith('--')) || process.cwd())

if (!fs.existsSync(target) || !fs.statSync(target).isDirectory()) {
  console.error(`目标仓库不存在或不是目录：${target}`)
  process.exit(2)
}

const exists = relative => fs.existsSync(path.join(target, relative))
const read = (relative) => {
  try { return fs.readFileSync(path.join(target, relative), 'utf8') }
  catch { return '' }
}
const json = (relative) => {
  try { return JSON.parse(read(relative)) }
  catch { return null }
}
const first = values => values.find(Boolean) || ''
const versionFromRange = value => String(value || '').match(/\d+(?:\.\d+){0,2}/)?.[0] || ''

function packageManager(base) {
  if (exists(path.join(base, 'pnpm-lock.yaml'))) return 'pnpm'
  if (exists(path.join(base, 'yarn.lock'))) return 'yarn'
  if (exists(path.join(base, 'bun.lockb')) || exists(path.join(base, 'bun.lock'))) return 'bun'
  if (exists(path.join(base, 'package-lock.json'))) return 'npm'
  return 'npm-install'
}

function nodeApp(relative, kind) {
  const manifest = json(path.join(relative, 'package.json')) || {}
  const deps = { ...(manifest.dependencies || {}), ...(manifest.devDependencies || {}) }
  const framework = first([
    deps['@dcloudio/uni-app'] && 'uniapp', deps.nuxt && 'nuxt', deps.next && 'next',
    deps.vue && 'vue', deps.react && 'react', deps.express && 'express', deps.fastify && 'fastify',
    deps['@nestjs/core'] && 'nestjs',
  ]) || 'node'
  const scripts = manifest.scripts || {}
  const manager = packageManager(relative)
  const commandManager = manager === 'npm-install' ? 'npm' : manager
  const run = name => commandManager === 'npm' ? `npm run ${name}` : `${commandManager} ${name}`
  const devScript = first(['dev', 'serve', 'start', 'dev:h5', 'dev:mp-weixin'].map(name => scripts[name] && run(name)))
  const buildScript = first(['build', 'build:h5'].map(name => scripts[name] && run(name)))
  const configSource = first(['vite.config.ts', 'vite.config.js', 'vite.config.mjs', 'nuxt.config.ts', 'next.config.js'].map(file => exists(path.join(relative, file)) && path.join(relative, file)))
  const scriptPort = Object.values(scripts).join('\n').match(/--port(?:=|\s+)(\d+)/)?.[1]
  const configPort = configSource && read(configSource).match(/\bport\s*:\s*(\d+)/)?.[1]
  return {
    id: relative.replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '') || kind,
    kind, path: relative || '.', runtime: 'node', framework, packageManager: manager,
    version: versionFromRange(manifest.engines?.node), versionSource: `${relative ? `${relative}/` : ''}package.json`,
    port: Number(scriptPort || configPort) || null,
    portSource: scriptPort ? `${relative ? `${relative}/` : ''}package.json#scripts` : configPort ? configSource : '',
    commands: { install: manager === 'npm' ? 'npm ci' : manager === 'npm-install' ? 'npm install' : `${manager} install --frozen-lockfile`, dev: devScript, build: buildScript },
  }
}

function detectBackend() {
  const bases = exists('backend') ? ['backend'] : ['']
  for (const base of bases) {
    const prefix = base ? `${base}/` : ''
    if (exists(`${prefix}composer.json`)) {
      const manifest = json(`${prefix}composer.json`) || {}
      const require = manifest.require || {}
      return {
        id: 'backend', kind: 'backend', path: base || '.', runtime: 'php',
        framework: require['topthink/framework'] ? 'thinkphp' : require['laravel/framework'] ? 'laravel' : require['symfony/framework-bundle'] ? 'symfony' : 'php',
        version: versionFromRange(require.php), versionSource: `${prefix}composer.json`,
        extensions: Object.keys(require).filter(name => name.startsWith('ext-')).map(name => name.slice(4)).sort(),
        commands: { install: 'composer install', dev: '', build: '' },
      }
    }
    if (exists(`${prefix}package.json`)) return nodeApp(base, 'backend')
    if (exists(`${prefix}pyproject.toml`) || exists(`${prefix}requirements.txt`)) {
      const source = exists(`${prefix}pyproject.toml`) ? `${prefix}pyproject.toml` : `${prefix}requirements.txt`
      return { id: 'backend', kind: 'backend', path: base || '.', runtime: 'python', framework: 'python', version: '', versionSource: source, commands: { install: exists(`${prefix}requirements.txt`) ? 'pip install -r requirements.txt' : 'pip install -e .', dev: '', build: '' } }
    }
    if (exists(`${prefix}pom.xml`) || exists(`${prefix}build.gradle`) || exists(`${prefix}build.gradle.kts`)) {
      const source = first(['pom.xml', 'build.gradle', 'build.gradle.kts'].map(file => exists(`${prefix}${file}`) && `${prefix}${file}`))
      return { id: 'backend', kind: 'backend', path: base || '.', runtime: 'java', framework: 'java', version: '', versionSource: source, commands: { install: '', dev: exists(`${prefix}mvnw`) ? './mvnw spring-boot:run' : exists(`${prefix}gradlew`) ? './gradlew bootRun' : '', build: '' } }
    }
    if (exists(`${prefix}go.mod`)) return { id: 'backend', kind: 'backend', path: base || '.', runtime: 'go', framework: 'go', version: read(`${prefix}go.mod`).match(/^go\s+([^\s]+)/m)?.[1] || '', versionSource: `${prefix}go.mod`, commands: { install: 'go mod download', dev: 'go run .', build: 'go build ./...' } }
  }
  return null
}

function detectFrontends() {
  const roots = []
  const candidates = ['frontend', 'admin', 'web', 'uniapp', 'mobile', 'apps']
  for (const candidate of candidates) {
    if (!exists(candidate)) continue
    if (exists(`${candidate}/package.json`)) roots.push(candidate)
    for (const entry of fs.readdirSync(path.join(target, candidate), { withFileTypes: true })) {
      const relative = `${candidate}/${entry.name}`
      if (entry.isDirectory() && exists(`${relative}/package.json`)) roots.push(relative)
    }
  }
  return [...new Set(roots)].filter(relative => relative !== 'backend').map(relative => nodeApp(relative, 'frontend'))
}

const backend = detectBackend()
const frontends = detectFrontends()
const evidence = [
  'docker-compose.yml', 'compose.yaml', 'compose.yml', '.env.example', '.env.sample',
  backend?.versionSource, ...frontends.map(app => app.versionSource),
].filter(relative => relative && exists(relative))
const searchable = evidence.map(read).join('\n').toLowerCase()
const composerRequire = backend?.runtime === 'php' ? (json(path.join(backend.path === '.' ? '' : backend.path, 'composer.json'))?.require || {}) : {}
const services = []
if (/mysql|mariadb|db_host|database_url/.test(searchable) || composerRequire['ext-pdo_mysql']) services.push({ id: 'db', engine: 'mysql', defaultPort: 3306, required: true })
else if (/postgres|pgsql|postgis/.test(searchable) || composerRequire['ext-pdo_pgsql']) services.push({ id: 'db', engine: 'postgres', defaultPort: 5432, required: true })
if (/redis|redis_host/.test(searchable) || composerRequire['ext-redis']) services.push({ id: 'redis', engine: 'redis', defaultPort: 6379, required: true })

const existingDocker = ['docker-compose.yml', 'compose.yaml', 'compose.yml'].filter(exists)
const warnings = []
if (!backend) warnings.push('未识别后端运行时；生成 Docker 前必须定位真实启动入口。')
if (backend?.path === '.') warnings.push('后端当前位于仓库根目录；应先记录兼容映射，再规划迁移到 backend/，禁止直接移动。')
if (backend && !backend.commands.dev && backend.runtime !== 'php') warnings.push('未能可靠识别后端开发启动命令。')
if (!services.length) warnings.push('未从非敏感样例和依赖清单识别数据库或缓存；请核对外部服务。')

const requiredDocs = [
  'README.md', 'help/docs/README.md', 'help/docs/project-overview.md', 'help/docs/architecture.md',
  'help/docs/development.md', 'help/docs/configuration.md', 'help/docs/api.md', 'help/docs/testing.md',
  'help/docs/deployment.md', 'help/docs/operations.md', 'help/docs/troubleshooting.md',
  'help/docs/security.md', 'help/docs/upgrade-and-rollback.md',
]
if (backend) {
  requiredDocs.push('help/docs/backend-development.md', `help/docs/applications/${backend.id}.md`)
  if (backend.path !== '.') requiredDocs.push(`${backend.path}/README.md`)
}
if (frontends.length) requiredDocs.push('help/docs/frontend-development.md')
for (const app of frontends) {
  requiredDocs.push(`${app.path}/README.md`, `help/docs/applications/${app.id}.md`)
}
if (frontends.some(app => app.framework === 'uniapp')) requiredDocs.push('help/docs/uniapp-development.md')
if (services.some(service => service.id === 'db')) requiredDocs.push('help/docs/database-and-migrations.md', 'help/docs/backup-and-recovery.md')
requiredDocs.push('help/docs/docker-development.md')

const result = {
  schemaVersion: 1,
  repository: target,
  generatedAt: new Date().toISOString(),
  backend,
  frontends,
  services,
  docker: { existing: existingDocker, configRootExists: exists('docker'), rootEntrypoint: 'docker-compose.yml' },
  documentation: { required: [...new Set(requiredDocs)], missing: [...new Set(requiredDocs)].filter(relative => !exists(relative)) },
  warnings,
  evidence,
}

if (write) {
  const destination = path.join(target, '.crmeb/environment.generated.json')
  fs.mkdirSync(path.dirname(destination), { recursive: true })
  fs.writeFileSync(destination, `${JSON.stringify(result, null, 2)}\n`)
  console.error(`已写入 ${path.relative(target, destination)}`)
}
console.log(JSON.stringify(result, null, 2))
