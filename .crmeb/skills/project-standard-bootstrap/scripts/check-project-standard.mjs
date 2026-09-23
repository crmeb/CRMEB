#!/usr/bin/env node
import fs from 'node:fs'
import path from 'node:path'
import { collectFindings, collectMarkdownLinkFindings, pathExists, printFindings } from './lib.mjs'

const target = path.resolve(process.argv[2] || process.cwd())
if (!pathExists(target) || !fs.statSync(target).isDirectory()) {
  console.error(`目标仓库不存在或不是目录：${target}`)
  process.exit(2)
}

const findings = [...collectFindings(target), ...collectMarkdownLinkFindings(target)]
printFindings(findings)
process.exit(findings.some(item => item.severity === 'error') ? 1 : 0)

