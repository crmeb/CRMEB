/**
 * 发现并编译前端扩展
 * 扫描 src/pages/product/type/ 和 src/pages/plugin/ 下所有带 ext.json + index.umd.js 的目录，
 * 逐个执行 vite build（scripts/vite.ext.config.js，UMD lib 模式，externals vue/element-plus）。
 * 产物输出到 dist/static/extensions/{key}/app.umd.js + app.umd.css
 *
 * 用法:
 *   node scripts/build-extensions.js                    # 编译所有扩展（并行）
 *   node scripts/build-extensions.js --ext agent        # 只编译指定扩展
 *   node scripts/build-extensions.js --ext agent --watch # 监控模式
 */
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, URL } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');

const scanDirs = [path.resolve(ROOT, 'src/pages/product/type'), path.resolve(ROOT, 'src/pages/plugin')];
const args = process.argv.slice(2);
const isWatch = args.includes('--watch');
const extFilterIndex = args.indexOf('--ext');
const extFilter = extFilterIndex !== -1 ? args[extFilterIndex + 1] : null;

function getExtensions() {
  const results = [];
  scanDirs.forEach((scanDir) => {
    if (!fs.existsSync(scanDir)) return;
    fs.readdirSync(scanDir).forEach((name) => {
      const configPath = path.join(scanDir, name, 'ext.json');
      const entryPath = path.join(scanDir, name, 'index.umd.js');
      if (!fs.existsSync(configPath) || !fs.existsSync(entryPath)) return;
      const config = JSON.parse(fs.readFileSync(configPath, 'utf-8'));
      results.push({ ...config, entry: entryPath, dir: path.join(scanDir, name) });
    });
  });
  return results;
}

function buildExtension(ext) {
  return new Promise((resolve, reject) => {
    const targetDir = path.resolve(ROOT, `dist/static/extensions/${ext.key}`);

    // 构建前清空旧目录，避免残留文件
    if (fs.existsSync(targetDir)) {
      fs.rmSync(targetDir, { recursive: true });
    }

    console.log(`\n━━━ 编译扩展：${ext.title} (${ext.key}) ━━━`);
    const viteBin = path.resolve(ROOT, 'node_modules/.bin/vite');
    const child = spawn(
      viteBin,
      ['build', '-c', path.resolve(__dirname, 'vite.ext.config.js')],
      {
        stdio: 'inherit',
        cwd: ROOT,
        env: {
          ...process.env,
          EXT_ENTRY: ext.entry,
          EXT_NAME: `__ext_${ext.key}`,
          EXT_OUT: targetDir,
        },
      },
    );

    child.on('close', (code) => {
      if (code !== 0) {
        reject(new Error(`${ext.title} 编译失败，exit code: ${code}`));
        return;
      }

      try {
        // 白名单清理：只保留 app.umd.js + app.umd.css（rollup cssCodeSplit:false 单文件输出，
        // lib 单入口下动态 import 自动内联，理论上无额外 chunk；防御性清理以防万一）
        const keep = new Set(['app.umd.js', 'app.umd.css']);
        if (fs.existsSync(targetDir)) {
          fs.readdirSync(targetDir).forEach((f) => {
            if (keep.has(f)) return;
            const fp = path.join(targetDir, f);
            fs.rmSync(fp, { recursive: true });
          });
        }
        if (!fs.existsSync(path.join(targetDir, 'app.umd.js'))) {
          throw new Error(`${ext.title} 产物 app.umd.js 未生成`);
        }
        console.log(`  \u2713 ${ext.title} 编译完成 \u2192 dist/static/extensions/${ext.key}/`);
        resolve();
      } catch (err) {
        reject(err);
      }
    });

    child.on('error', (err) => {
      reject(err);
    });
  });
}

async function main() {
  let extensions = getExtensions();

  if (extFilter) {
    extensions = extensions.filter((e) => e.key === extFilter);
    if (extensions.length === 0) {
      const available = getExtensions().map((e) => e.key + '(' + e.title + ')').join(', ');
      console.log('\n未找到扩展 "' + extFilter + '"，可用扩展：' + available);
      process.exit(1);
    }
  }

  if (extensions.length === 0) {
    console.log('\n未发现扩展（没有带 ext.json + index.umd.js 的目录）');
    process.exit(0);
  }

  console.log(`\n发现 ${extensions.length} 个扩展：`, extensions.map((e) => `${e.title}(${e.key})`).join(', '));

  if (isWatch) {
    // 监控模式：先并行编译一次
    await Promise.all(extensions.map(buildExtension));

    console.log('\n初始编译完成，进入监控模式，文件变更时自动重建...\n');
    const watchDirs = extensions.map((ext) => ext.dir);
    const watchSet = new Set(watchDirs);
    watchSet.forEach((dir) => {
      fs.watch(dir, { recursive: true }, (eventType, filename) => {
        if (!filename) return;
        if (!filename.endsWith('.vue') && !filename.endsWith('.js')) return;
        const ext = extensions.find((e) => filename.startsWith(e.key + '/') || filename.startsWith(e.dir));
        if (ext) {
          console.log(`\n  ${ext.title} 文件变更: ${filename}`);
          buildExtension(ext).catch((err) => console.error(err.message));
        }
      });
    });
    console.log('按 Ctrl+C 停止监控');
  } else {
    // 并行编译所有扩展
    await Promise.all(extensions.map(buildExtension));
    console.log('\n所有扩展编译完成！');
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
