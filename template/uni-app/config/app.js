// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------


module.exports = {

	// 小程序配置
	// #ifdef MP
	// 请求域名 格式： https://您的域名
	HTTP_REQUEST_URL: `https://您的域名`,
	// 长连接 格式：wss://您的域名:20003 
	// 需要在后台设置->基础配置->WSS配置 开启WSS配置，并上传证书，重启workerman后生效
	// 请在PHP项目根目录下启动，长连接命令：sudo -u www php think workerman start --d
	// 系统默认客服端口20003,如果需要修改端口，请修改【20003】
	VUE_APP_WS_URL: `wss://您的域名:20003`,
	// #endif

	// H5配置
	// #ifdef H5
	//H5接口是浏览器地址，非单独部署不用修改
	HTTP_REQUEST_URL: window.location.protocol + "//" + window.location.host,
	// 长连接地址，非单独部署不用修改
	// 系统默认客服端口20003,如果需要修改端口，请修改【20003】
	VUE_APP_WS_URL: `ws://${window.location.host}:20003`,
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
