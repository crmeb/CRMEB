import { defineConfig, loadEnv } from 'vite';
import uni from '@dcloudio/vite-plugin-uni';

export default defineConfig(({ mode }) => {
	// 读取根目录 .env / .env.[mode] 中自定义的 UNI_APP_* 变量
	// （vite 的 loadEnv 默认只取 VITE_ 前缀，第三个参数传 '' 表示取全部）
	const env = loadEnv(mode, process.cwd(), '');

	return {
		plugins: [uni()],
		define: {
			// 注入 src/config/app.js 中使用的接口地址变量；.env 未配置时置空串，代码内回退到各端默认值
			'process.env.UNI_APP_API_URL': JSON.stringify(env.UNI_APP_API_URL || '')
		}
	};
});
