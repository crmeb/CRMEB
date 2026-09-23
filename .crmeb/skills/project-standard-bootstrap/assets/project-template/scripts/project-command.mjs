#!/usr/bin/env node
import fs from 'node:fs'
import path from 'node:path'
import { spawnSync } from 'node:child_process'
import { fileURLToPath } from 'node:url'

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const contractPath = path.join(root, '.crmeb/project.json')
const requested = process.argv[2]
const aliases = {
  'reset-data': 'resetData',
  'upgrade-plan': 'upgradePlan',
  'upgrade-package': 'upgradePackage',
  'upgrade-verify': 'upgradeVerify',
}
const commandName = aliases[requested] || requested

if (!requested || ['-h', '--help', 'help'].includes(requested)) {
  console.log('用法：./run.sh <doctor|setup|dev|restart|stop|down|status|logs|shell|check|test|smoke|build|reset-data|upgrade-plan|upgrade-package|upgrade-verify> [参数]')
  process.exit(0)
}

if (!fs.existsSync(contractPath)) {
  console.error('缺少 .crmeb/project.json，请先完成项目规范初始化。')
  process.exit(2)
}

let contract
try {
  contract = JSON.parse(fs.readFileSync(contractPath, 'utf8'))
}
catch (error) {
  console.error(`无法解析 .crmeb/project.json：${error.message}`)
  process.exit(2)
}

const command = contract.commands?.[commandName]
if (!command) {
  console.error(`标准命令尚未配置：${requested}`)
  process.exit(2)
}

const rawArgs = process.argv.slice(3)
for (const value of rawArgs) {
  if (!/^[A-Za-z0-9._/@:+-]+$/.test(value)) {
    console.error(`参数包含不允许的字符：${value}`)
    process.exit(2)
  }
}
const extraArgs = rawArgs.join(' ')
const result = spawnSync(extraArgs ? `${command} ${extraArgs}` : command, {
  cwd: root,
  env: process.env,
  shell: true,
  stdio: 'inherit',
})
if (result.error) {
  console.error(result.error.message)
  process.exit(1)
}
process.exit(result.status ?? 1)
