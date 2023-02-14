module.exports = {
	// 小程序配置
	// #ifdef MP || APP-PLUS
	// 请求域名 格式： https://您的域名
	HTTP_REQUEST_URL: `https://demo.crmeb.com`,
	// #endif

	// H5配置
	// #ifdef H5
	//H5接口是浏览器地址，非单独部署不用修改
	HTTP_REQUEST_URL: window.location.protocol + "//" + window.location.host,
	// #endif 

	// 以下配置在不做二开的前提下,不需要做任何的修改
	HEADER: {
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
	},
	// 回话密钥名称 请勿修改此配置
	TOKENNAME: 'Authori-zation',
	// 缓存时间 0 永久
	EXPIRE: 0,
	//分页最多显示条数
	LIMIT: 10
}
