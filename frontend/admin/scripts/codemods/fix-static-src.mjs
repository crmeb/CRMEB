// 一次性修复：模板静态 src="@/..." 改为相对路径（plugin-vue 自动处理模板相对资源 URL）
import fs from 'node:fs';
import path from 'node:path';

const targets = process.argv.slice(2);
let total = 0;
for (const f of targets) {
  let c = fs.readFileSync(f, 'utf8');
  let n = 0;
  c = c.replace(/(\ssrc)="@\/([^"]+)"/g, (m, pre, target) => {
    const rel = path.relative(path.dirname(f), path.resolve('src', target));
    n++;
    return `${pre}="${rel}"`;
  });
  c = c.replace(/(\ssrc)='@\/([^']+)'/g, (m, pre, target) => {
    const rel = path.relative(path.dirname(f), path.resolve('src', target));
    n++;
    return `${pre}="${rel}"`;
  });
  if (n) fs.writeFileSync(f, c);
  total += n;
  console.log(f, n, '处');
}
console.log('total', total);
