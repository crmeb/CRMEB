import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const sourceDir = path.join(projectRoot, 'dist', 'build', 'h5');
const targetDir = path.resolve(projectRoot, '../../backend/public');
const generatedDirectories = ['assets', 'pages', 'static'];

if (!fs.existsSync(sourceDir)) {
  throw new Error(`uni-app H5 构建产物不存在：${sourceDir}`);
}

for (const directory of generatedDirectories) {
  fs.rmSync(path.join(targetDir, directory), { recursive: true, force: true });
}
fs.mkdirSync(targetDir, { recursive: true });
fs.cpSync(sourceDir, targetDir, { recursive: true });

console.log(`uni-app H5 构建产物已发布到 ${targetDir}`);
