#!/usr/bin/env node
import fs from 'node:fs'
import path from 'node:path'
import { spawnSync } from 'node:child_process'
import { fileURLToPath } from 'node:url'

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const composeFile = path.join(root, 'docker-compose.yml')
const action = process.argv[2]
const args = process.argv.slice(3)

function run(command, commandArgs, options = {}) {
  const result = spawnSync(command, commandArgs, { cwd: root, env: process.env, stdio: 'inherit', ...options })
  if (result.error) {
    console.error(result.error.message)
    process.exit(1)
  }
  if (result.status !== 0) process.exit(result.status ?? 1)
}

function compose(commandArgs) {
  if (!fs.existsSync(composeFile)) {
    console.error('缺少 docker-compose.yml，请先生成或完善 Docker 本地环境。')
    process.exit(2)
  }
  run('docker', ['compose', '-f', composeFile, ...commandArgs])
}

function requireService(value) {
  if (!value || !/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/.test(value)) {
    console.error('请提供合法的 Compose 服务名。')
    process.exit(2)
  }
  return value
}

switch (action) {
  case 'doctor':
    run('docker', ['version'])
    run('docker', ['compose', 'version'])
    compose(['config', '--quiet'])
    break
  case 'setup':
    compose(['build'])
    break
  case 'dev':
    compose(['up', '-d', '--build', '--remove-orphans'])
    compose(['ps'])
    break
  case 'restart':
    compose(args.length ? ['restart', ...args.map(requireService)] : ['restart'])
    break
  case 'stop':
    compose(['stop'])
    break
  case 'down':
    compose(['down', '--remove-orphans'])
    break
  case 'status':
    compose(['ps'])
    break
  case 'logs':
    compose(['logs', '--tail', '200', '-f', ...args.map(requireService)])
    break
  case 'shell': {
    const service = requireService(args[0])
    compose(['exec', service, 'sh'])
    break
  }
  case 'reset-data':
    if (!args.includes('--yes')) {
      console.error('该操作会删除本地 Compose 数据卷。确认后使用：./run.sh reset-data --yes')
      process.exit(2)
    }
    compose(['down', '--volumes', '--remove-orphans'])
    break
  default:
    console.error('用法：node scripts/docker-project.mjs <doctor|setup|dev|restart|stop|down|status|logs|shell|reset-data> [服务名|--yes]')
    process.exit(2)
}
