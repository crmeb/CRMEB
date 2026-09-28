import { defineConfig, loadEnv } from 'vite';
import uni from '@dcloudio/vite-plugin-uni';
import { installAutoSubpackages, uniAutoSubpackagesPlugin } from './scripts/uni-auto-subpackages.mjs';

export default defineConfig(({ mode }) => {
	// 读取根目录 .env / .env.[mode] 中自定义的 UNI_APP_* 变量
	// （vite 的 loadEnv 默认只取 VITE_ 前缀，第三个参数传 '' 表示取全部）
	const env = loadEnv(mode, process.cwd(), '');

	// 把 src/pages-modules/ 模块分包在内存中合并进 pages.json 的读取（磁盘文件不变）；
	// 必须在 uni() 首次读取 pages.json 之前执行
	installAutoSubpackages();

	return {
		plugins: [uniAutoSubpackagesPlugin(), uni()],
		define: {
			// 注入 src/config/app.js 中使用的接口地址变量；.env 未配置时置空串，代码内回退到各端默认值
			'process.env.UNI_APP_API_URL': JSON.stringify(env.UNI_APP_API_URL || '')
		}
	};
});
