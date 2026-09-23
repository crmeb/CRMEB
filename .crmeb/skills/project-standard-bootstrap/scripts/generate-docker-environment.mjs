#!/usr/bin/env node
import fs from 'node:fs'
import path from 'node:path'
import { spawnSync } from 'node:child_process'
import { fileURLToPath } from 'node:url'

const args = process.argv.slice(2)
const writeMissing = args.includes('--write-missing')
const target = path.resolve(args.find(arg => !arg.startsWith('--')) || process.cwd())
const scriptDir = path.dirname(fileURLToPath(import.meta.url))
const analysisRun = spawnSync(process.execPath, [path.join(scriptDir, 'analyze-project-environment.mjs'), target], { encoding: 'utf8' })
if (analysisRun.status !== 0) {
  process.stderr.write(analysisRun.stderr)
  process.exit(analysisRun.status ?? 1)
}
const analysis = JSON.parse(analysisRun.stdout)
const backend = analysis.backend

if (!backend) {
  console.error('无法可靠识别后端运行时，未生成 Docker 文件。请先根据分析报告补充真实后端入口。')
  process.exit(2)
}

const projectName = path.basename(target).toLowerCase().replace(/[^a-z0-9_-]+/g, '-') || 'application'
const backendPath = backend.path === '.' ? '.' : `./${backend.path}`
const db = analysis.services.find(item => item.id === 'db')
const hasRedis = analysis.services.some(item => item.id === 'redis')
const services = []
const volumes = new Set()

if (db?.engine === 'mysql') {
  services.push(`  db:
    image: mysql:8.4
    environment:
      MYSQL_DATABASE: \${DB_DATABASE:-app}
      MYSQL_USER: \${DB_USERNAME:-app}
      MYSQL_PASSWORD: \${DB_PASSWORD:-app-local-only}
      MYSQL_ROOT_PASSWORD: \${DB_ROOT_PASSWORD:-root-local-only}
    ports:
      - "\${DB_PORT_FORWARD:-3306}:3306"
    volumes:
      - database-data:/var/lib/mysql
    healthcheck:
      test: ["CMD-SHELL", "mysqladmin ping -h 127.0.0.1 -uroot -p$$MYSQL_ROOT_PASSWORD --silent"]
      interval: 5s
      timeout: 5s
      retries: 20`)
  volumes.add('database-data')
}
if (db?.engine === 'postgres') {
  services.push(`  db:
    image: postgres:17-alpine
    environment:
      POSTGRES_DB: \${DB_DATABASE:-app}
      POSTGRES_USER: \${DB_USERNAME:-app}
      POSTGRES_PASSWORD: \${DB_PASSWORD:-app-local-only}
    ports:
      - "\${DB_PORT_FORWARD:-5432}:5432"
    volumes:
      - database-data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U $$POSTGRES_USER -d $$POSTGRES_DB"]
      interval: 5s
      timeout: 5s
      retries: 20`)
  volumes.add('database-data')
}
if (hasRedis) {
  services.push(`  redis:
    image: redis:7.4-alpine
    command: ["redis-server", "--appendonly", "yes"]
    ports:
      - "\${REDIS_PORT_FORWARD:-6379}:6379"
    volumes:
      - redis-data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 5s
      timeout: 3s
      retries: 20`)
  volumes.add('redis-data')
}

const dependencies = [db && 'db', hasRedis && 'redis'].filter(Boolean)
const dependsBlock = dependencies.length
  ? `\n    depends_on:\n${dependencies.map(id => `      ${id}:\n        condition: service_healthy`).join('\n')}`
  : ''
let dockerfile = ''
let appService = ''
let proxyService = ''

if (backend.runtime === 'php') {
  const phpVersion = backend.version || '8.2'
  dockerfile = `ARG PHP_VERSION=${phpVersion}\nFROM php:\${PHP_VERSION}-fpm-bookworm\n\nRUN apt-get update \\\n    && apt-get install -y --no-install-recommends git unzip libzip-dev libpq-dev \\\n    && docker-php-ext-install pdo_mysql pdo_pgsql zip bcmath pcntl \\\n    && rm -rf /var/lib/apt/lists/*\n\nCOPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer\nWORKDIR /workspace/backend\nCMD ["php-fpm"]\n`
  const phpExtensions = new Set((backend.extensions || []).filter(name => name !== 'json'))
  if (db?.engine === 'mysql') phpExtensions.add('pdo_mysql')
  if (db?.engine === 'postgres') phpExtensions.add('pdo_pgsql')
  const buildableExtensions = [...phpExtensions].filter(name => ['bcmath', 'curl', 'gd', 'intl', 'mbstring', 'opcache', 'pcntl', 'pdo_mysql', 'pdo_pgsql', 'simplexml', 'soap', 'sockets', 'zip'].includes(name))
  const aptPackages = new Set(['git', 'unzip'])
  if (phpExtensions.has('curl')) aptPackages.add('libcurl4-openssl-dev')
  if (phpExtensions.has('gd')) ['libfreetype6-dev', 'libjpeg62-turbo-dev', 'libpng-dev'].forEach(item => aptPackages.add(item))
  if (phpExtensions.has('intl')) aptPackages.add('libicu-dev')
  if (phpExtensions.has('mbstring')) aptPackages.add('libonig-dev')
  if (phpExtensions.has('pdo_pgsql')) aptPackages.add('libpq-dev')
  if (phpExtensions.has('simplexml') || phpExtensions.has('soap')) aptPackages.add('libxml2-dev')
  if (phpExtensions.has('zip')) aptPackages.add('libzip-dev')
  const installSteps = [`    && apt-get install -y --no-install-recommends ${[...aptPackages].join(' ')}`]
  if (phpExtensions.has('gd')) installSteps.push('    && docker-php-ext-configure gd --with-freetype --with-jpeg')
  if (buildableExtensions.length) installSteps.push(`    && docker-php-ext-install ${buildableExtensions.join(' ')}`)
  if (phpExtensions.has('redis')) installSteps.push('    && pecl install redis', '    && docker-php-ext-enable redis')
  installSteps.push('    && rm -rf /var/lib/apt/lists/*')
  dockerfile = [
    `ARG PHP_VERSION=${phpVersion}`,
    'FROM php:${PHP_VERSION}-fpm-bookworm',
    '',
    'RUN apt-get update \\',
    installSteps.join(' \\\n'),
    '',
    'COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer',
    'WORKDIR /workspace/backend',
    'CMD ["php-fpm"]',
    '',
  ].join('\n')
  appService = `  backend:
    build:
      context: .
      dockerfile: docker/backend/Dockerfile.dev
      args:
        PHP_VERSION: \${PHP_VERSION:-${phpVersion}}
    working_dir: /workspace/backend
    volumes:
      - ${backendPath}:/workspace/backend
      - backend-dependencies:/workspace/backend/vendor
${db || hasRedis ? `    environment:\n${db ? '      DB_HOST: db\n' : ''}${hasRedis ? '      REDIS_HOST: redis\n' : ''}` : ''}${dependsBlock}`
  proxyService = `  proxy:
    image: nginx:1.27-alpine
    ports:
      - "\${APP_PORT:-8080}:80"
    volumes:
      - ${backendPath}:/workspace/backend:ro
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - backend
    healthcheck:
      test: ["CMD", "wget", "-qO-", "http://127.0.0.1/"]
      interval: 10s
      timeout: 3s
      retries: 12`
  volumes.add('backend-dependencies')
}
else if (backend.runtime === 'node' && backend.commands.dev) {
  const nodeVersion = backend.version || '22'
  const dependencyPath = '/workspace/backend/node_modules'
  dockerfile = `ARG NODE_VERSION=${nodeVersion}\nFROM node:\${NODE_VERSION}-alpine\nRUN corepack enable\nWORKDIR /workspace/backend\nCMD ["sh"]\n`
  appService = `  backend:
    build:
      context: .
      dockerfile: docker/backend/Dockerfile.dev
      args:
        NODE_VERSION: \${NODE_VERSION:-${nodeVersion}}
    working_dir: /workspace/backend
    command: ["sh", "-lc", "${backend.commands.install} && ${backend.commands.dev}"]
    ports:
      - "\${BACKEND_PORT:-3000}:3000"
    volumes:
      - ${backendPath}:/workspace/backend
      - backend-dependencies:${dependencyPath}
    environment:
      HOST: 0.0.0.0
${db ? '      DB_HOST: db\n' : ''}${hasRedis ? '      REDIS_HOST: redis\n' : ''}${dependsBlock}`
  volumes.add('backend-dependencies')
}
else {
  console.error(`已识别 ${backend.runtime}，但缺少可验证的通用开发启动模板或启动命令；未生成可能误导的 Compose。`)
  process.exit(2)
}

services.push(appService)
if (proxyService) services.push(proxyService)
const frontendFiles = new Map()
analysis.frontends.filter(app => app.commands.dev).forEach((app, index) => {
  const appName = app.path.split('/').pop().toLowerCase().replace(/[^a-z0-9_-]+/g, '-')
  const serviceName = `frontend-${appName}`
  const port = app.port || 5173 + index
  const portVariable = `FRONTEND_${appName.toUpperCase().replace(/[^A-Z0-9]+/g, '_')}_PORT`
  const nodeVersion = app.version || '22'
  const hostSuffix = ['vue', 'react', 'uniapp'].includes(app.framework) ? ` -- --host 0.0.0.0 --port ${port}` : ''
  services.push(`  ${serviceName}:
    build:
      context: .
      dockerfile: docker/frontend/${appName}/Dockerfile.dev
      args:
        NODE_VERSION: \${NODE_VERSION:-${nodeVersion}}
    working_dir: /workspace/frontend/${appName}
    command: ["sh", "-lc", "${app.commands.install} && ${app.commands.dev}${hostSuffix}"]
    ports:
      - "\${${portVariable}:-${port}}:${port}"
    volumes:
      - ./${app.path}:/workspace/frontend/${appName}
      - ${serviceName}-dependencies:/workspace/frontend/${appName}/node_modules
    environment:
      HOST: 0.0.0.0
      PORT: ${port}`)
  volumes.add(`${serviceName}-dependencies`)
  frontendFiles.set(`docker/frontend/${appName}/Dockerfile.dev`, `ARG NODE_VERSION=${nodeVersion}\nFROM node:\${NODE_VERSION}-alpine\nRUN corepack enable\nWORKDIR /workspace/frontend/${appName}\nCMD ["sh"]\n`)
})
const compose = `name: \${COMPOSE_PROJECT_NAME:-${projectName}}\n\nservices:\n${services.join('\n\n')}\n\nvolumes:\n${[...volumes].map(name => `  ${name}:`).join('\n')}\n`
const nginx = `server {\n    listen 80;\n    server_name _;\n    root /workspace/backend/public;\n    index index.php index.html;\n\n    location / { try_files $uri $uri/ /index.php?$query_string; }\n    location ~ \\.php$ {\n        include fastcgi_params;\n        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n        fastcgi_pass backend:9000;\n    }\n}\n`
const frontendEnv = analysis.frontends.filter(app => app.commands.dev).map((app, index) => `FRONTEND_${app.path.split('/').pop().toUpperCase().replace(/[^A-Z0-9]+/g, '_')}_PORT=${app.port || 5173 + index}`).join('\n')
const envExample = `COMPOSE_PROJECT_NAME=${projectName}\nAPP_PORT=8080\nBACKEND_PORT=3000\n${frontendEnv}${frontendEnv ? '\n' : ''}DB_DATABASE=app\nDB_USERNAME=app\nDB_PASSWORD=app-local-only\nDB_ROOT_PASSWORD=root-local-only\nDB_PORT_FORWARD=${db?.defaultPort || 3306}\nREDIS_PORT_FORWARD=6379\n`
const dockerReadme = `# Docker 本地开发配置\n\n- 统一入口：仓库根目录 \`docker-compose.yml\`、\`run.sh\`。\n- 本目录只保存镜像、代理、入口和健康检查配置，不提交运行数据或日志。\n- 数据使用 Compose 具名卷；日常 \`stop/down\` 不删除数据。\n- 完整命令、端口、初始化、备份恢复和排障见 \`help/docs/docker-development.md\`。\n`
const dockerDoc = `# Docker 本地开发\n\n> 由项目环境分析生成基线；启用前必须核对运行时扩展、应用环境变量、迁移和健康接口。\n\n## 已识别环境\n\n- 后端：${backend.runtime} / ${backend.framework}，路径 \`${backend.path}\`，版本证据 \`${backend.versionSource}\`。\n- 基础服务：${analysis.services.map(item => item.engine).join('、') || '未识别'}。\n- Compose 入口：\`docker-compose.yml\`。\n\n## 首次启动\n\n1. 将 \`.env.docker.example\` 中需要的非敏感配置合并到本地 \`.env\`，不要覆盖已有配置。\n2. 运行 \`./run.sh doctor\`，成功信号是 Docker、Compose 与配置解析均通过。\n3. 运行 \`./run.sh setup\` 构建镜像。\n4. 根据项目真实机制执行数据库迁移或最小数据初始化。\n5. 运行 \`./run.sh dev\`，再运行 \`./run.sh status\`。\n6. 访问 \`http://localhost:\${APP_PORT:-8080}\` 或项目健康接口验证。\n\n## 日常命令\n\n- \`./run.sh dev\`：构建并启动；保留数据。\n- \`./run.sh restart [service]\`：重启全部或指定服务。\n- \`./run.sh logs [service]\`：跟随日志。\n- \`./run.sh shell <service>\`：进入容器。\n- \`./run.sh stop\`：停止但保留容器和数据。\n- \`./run.sh down\`：删除容器和网络，保留具名卷。\n- \`./run.sh reset-data --yes\`：删除本地具名卷，不可恢复。\n\n## 必须补充并验证\n\n- 应用所需系统扩展、环境变量、内部/外部端口和健康接口；\n- 数据库迁移、种子、开发账号、备份与恢复命令；\n- 每个前端、worker、scheduler、WebSocket 和队列进程；\n- 源码热更新、测试、构建、停止再启动后的数据持久化；\n- macOS、Windows、Linux 的权限与文件监听差异。\n`

const templateRoot = path.resolve(scriptDir, '../assets/project-template')
const files = new Map([
  ['docker-compose.yml', compose],
  ['.env.docker.example', envExample],
  ['docker/backend/Dockerfile.dev', dockerfile],
  ['docker/README.md', dockerReadme],
  ['help/docs/docker-development.md', dockerDoc],
  ['run.sh', fs.readFileSync(path.join(templateRoot, 'run.sh'), 'utf8')],
  ['scripts/project-command.mjs', fs.readFileSync(path.join(templateRoot, 'scripts/project-command.mjs'), 'utf8')],
  ['scripts/docker-project.mjs', fs.readFileSync(path.join(templateRoot, 'scripts/docker-project.mjs'), 'utf8')],
])
if (backend.runtime === 'php') files.set('docker/nginx/default.conf', nginx)
for (const [relative, content] of frontendFiles) files.set(relative, content)

const created = []
const retained = []
for (const [relative, content] of files) {
  const destination = path.join(target, relative)
  if (fs.existsSync(destination)) {
    retained.push(relative)
    continue
  }
  created.push(relative)
  if (writeMissing) {
    fs.mkdirSync(path.dirname(destination), { recursive: true })
    fs.writeFileSync(destination, content)
    if (relative === 'run.sh') fs.chmodSync(destination, 0o755)
  }
}

let contractUpdated = false
const contractPath = path.join(target, '.crmeb/project.json')
if (writeMissing && fs.existsSync(contractPath)) {
  try {
    const contract = JSON.parse(fs.readFileSync(contractPath, 'utf8'))
    contract.docker = { ...(contract.docker || {}), enabled: true, composeFile: 'docker-compose.yml', configRoot: 'docker' }
    contract.applications ||= []
    for (const app of [backend, ...analysis.frontends]) {
      const existing = contract.applications.find(item => item.id === app.id)
      if (!existing) contract.applications.push(app)
      else {
        for (const [key, value] of Object.entries(app)) {
          if (existing[key] === undefined || existing[key] === '' || existing[key] === 'unknown') existing[key] = value
        }
      }
    }
    contract.services ||= []
    for (const service of analysis.services) {
      if (!contract.services.some(item => item.id === service.id)) contract.services.push(service)
    }
    contract.documentation ||= {}
    contract.documentation.required = [...new Set([...(contract.documentation.required || []), ...analysis.documentation.required])]
    contract.commands ||= {}
    const dockerCommands = {
      doctor: 'node scripts/docker-project.mjs doctor', setup: 'node scripts/docker-project.mjs setup',
      dev: 'node scripts/docker-project.mjs dev', restart: 'node scripts/docker-project.mjs restart',
      stop: 'node scripts/docker-project.mjs stop', down: 'node scripts/docker-project.mjs down',
      status: 'node scripts/docker-project.mjs status', logs: 'node scripts/docker-project.mjs logs',
      shell: 'node scripts/docker-project.mjs shell', resetData: 'node scripts/docker-project.mjs reset-data',
    }
    for (const [name, command] of Object.entries(dockerCommands)) {
      if (!contract.commands[name]) contract.commands[name] = command
    }
    fs.writeFileSync(contractPath, `${JSON.stringify(contract, null, 2)}\n`)
    contractUpdated = true
  }
  catch (error) {
    console.error(`无法安全合并 .crmeb/project.json：${error.message}`)
    process.exit(2)
  }
}

console.log(writeMissing ? 'Docker 本地环境生成结果：' : 'Docker 本地环境生成预览：')
for (const relative of created) console.log(`- ${writeMissing ? '创建' : '将创建'} ${relative}`)
for (const relative of retained) console.log(`- 保留已有 ${relative}（必须语义审计，生成器不会覆盖）`)
if (contractUpdated) console.log('- 增量合并 .crmeb/project.json 的 Docker、应用、服务、命令和文档清单')
console.log('\n下一步：语义合并已有文件，补全 project.json 命令与文档清单，再依次执行 doctor、compose config、build、dev、smoke 和持久化验证。')
