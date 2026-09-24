import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const sourceDir = path.join(projectRoot, 'dist');
const targetDir = path.resolve(projectRoot, '../../backend/public/admin');

if (!fs.existsSync(sourceDir)) {
  throw new Error(`管理端构建产物不存在：${sourceDir}`);
}

fs.rmSync(targetDir, { recursive: true, force: true });
fs.mkdirSync(targetDir, { recursive: true });
fs.cpSync(sourceDir, targetDir, { recursive: true });

console.log(`管理端构建产物已发布到 ${targetDir}`);
