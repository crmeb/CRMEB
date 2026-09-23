// 接口地址：优先读取 .env 环境变量 UNI_APP_API_URL（见 .env.development / .env.production），
// 未配置时回退到下方各端默认值

// 小程序 / APP请求配置
// #ifdef MP || APP-PLUS
// 请求域名 格式： https://您的域名
export const HTTP_REQUEST_URL = process.env.UNI_APP_API_URL;
// #endif

// H5请求配置
// #ifdef H5
// H5接口是浏览器地址，非单独部署（前后端同域部署）时未配置环境变量则自动取当前域名
export const HTTP_REQUEST_URL = process.env.UNI_APP_API_URL || window.location.protocol + "//" + window.location.host;
// #endif

// 以下配置在不做二开的前提下,不需要做任何的修改
export const HEADER = {
	'content-type': 'application/json',
	//#ifdef H5
	'Form-type': navigator.userAgent.toLowerCase().indexOf("micromessenger") !== -1 ? 'wechat' : 'h5',
	//#endif
	//#ifdef MP
	'Form-type': 'routine',
	//#endif
	//#ifdef APP-VUE
	'Form-type': 'app',
	//#endif
};
// 回话密钥名称 请勿修改此配置
export const TOKENNAME = 'Authori-zation';
// 缓存时间 0 永久
export const EXPIRE = 0;
//分页最多显示条数
export const LIMIT = 10;
// 请求超时限制 默认10秒
export const TIMEOUT = 100000;

export default {
	HTTP_REQUEST_URL,
	HEADER,
	TOKENNAME,
	EXPIRE,
	LIMIT,
	TIMEOUT
};
