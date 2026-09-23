#!/usr/bin/env node
import fs from 'node:fs'
import path from 'node:path'
import { collectFindings, detectApplications, pathExists, printFindings } from './lib.mjs'

const args = process.argv.slice(2)
const json = args.includes('--json')
const targetArg = args.find(arg => !arg.startsWith('--')) || process.cwd()
const target = path.resolve(targetArg)

if (!pathExists(target) || !fs.statSync(target).isDirectory()) {
  console.error(`目标仓库不存在或不是目录：${target}`)
  process.exit(2)
}

const findings = collectFindings(target)
const detected = detectApplications(target)
const result = { repository: target, detectedApplications: detected, findings }

if (json) console.log(JSON.stringify(result, null, 2))
else {
  console.log(`项目规范审计：${target}`)
  if (detected.length) {
    console.log('检测到的应用：')
    for (const app of detected) console.log(`- ${app.id}: ${app.path} (${app.runtime})`)
  }
  else console.log('未从 backend/frontend 检测到应用 manifest。')
  console.log('')
  printFindings(findings)
}

// 审计用于收集差距，不以发现问题作为进程失败。
process.exit(0)

