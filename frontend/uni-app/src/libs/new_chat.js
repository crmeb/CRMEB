// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import {
	HTTP_REQUEST_URL
} from "@/config/app.js";
import {
	VUE_APP_WS_URL
} from "@/utils/index.js";
import {
	getServerType
} from '@/api/api.js';

// 断线自动重连：最大连续重试次数（退避 1s/2s/4s/8s/16s/30s，累计约 1 分钟）
const MAX_RECONNECT = 6;
// 单次重连延时上限（毫秒）
const MAX_RECONNECT_DELAY = 30000;

const Socket = function() {
	// 页面主动关闭标记：置 true 后被动关闭回调不再触发重连
	this.manualClose = false;
	// 已连续自动重连次数，连接成功或主动关闭时归零
	this.reconnectCount = 0;
	// 自动重连延时器句柄
	this.reconnectTimer = null;
	// 连接代数：每次 onStart 递增，用于忽略已被新连接取代的旧连接的关闭事件
	this.connId = 0;
	// 服务端鉴权失败标记：token 已失效，被动关闭后不再自动重连（重试同一 token 必然失败）
	this.authFailed = false;
};


// #ifdef H5
function wss(wsSocketUrl) {
	let ishttps = document.location.protocol == 'https:';
	if (ishttps) {
		return wsSocketUrl.replace('ws:', 'wss:');
	} else {
		return wsSocketUrl.replace('wss:', 'ws:');
	}
}
// #endif



Socket.prototype = {
	onSocketOpen: function(my) {
		// 连接建立成功（含重连成功）：重置重连计数
		this.reconnectCount = 0;
		uni.$emit('socketOpen', my)
	},
	init: function() {
		var that = this;
		// 启动心跳前先清旧定时器，避免重连后叠加多个 ping 定时器
		if (this.timer) clearInterval(this.timer);
		this.timer = setInterval(function() {
			that.send({
				type: "ping"
			});
		}, 10000);
	},
	send: function(data) {
		let datas = JSON.stringify(data)
		return uni.sendSocketMessage({
			data: datas
		});
	},
	onMessage: function(res) {
		let frame;
		try {
			frame = JSON.parse(res.data);
		} catch (e) {
			return;
		}
		const {
			type,
			data = {}
		} = frame;
		// 服务端要求关闭连接的帧：除 timeout（心跳超时，页面会重建连接）外均为鉴权失败等不可恢复错误，
		// token 已失效，标记后停止自动重连，由页面引导重新登录
		if (frame.close === true && type !== 'timeout') {
			this.authFailed = true;
			uni.$emit('ws_auth_expired', {
				msg: (data && data.msg) || '登录已失效'
			});
		}
		uni.$emit(type, data)
	},

	// 页面主动断开（离开聊天页时调用）：主动关闭不触发自动重连
	onClose: function() {
		this.manualClose = true;
		if (this.reconnectTimer) {
			clearTimeout(this.reconnectTimer);
			this.reconnectTimer = null;
		}
		this.reconnectCount = 0;
		uni.closeSocket()
		clearInterval(this.timer);
		uni.$emit("socket_close");
	},
	// 被动断开（网络波动/服务端重启/连接失败）：按指数退避自动重连
	onWsClose: function(connId) {
		clearInterval(this.timer);
		uni.$emit("socket_close");
		// 已被新连接取代的旧连接、页面主动关闭、服务端鉴权失败（token 失效重试无意义），均不重连
		if (connId !== this.connId || this.manualClose || this.authFailed) return;
		this.tryReconnect();
	},
	// 指数退避重连：超过最大次数放弃并通知页面，由用户决定是否手动重试
	tryReconnect: function() {
		var that = this;
		// 已有重连排队时去重，避免 onError/onClose 双触发导致重复连接
		if (this.reconnectTimer) return;
		if (this.reconnectCount >= MAX_RECONNECT) {
			this.reconnectCount = 0;
			uni.$emit("reconnect_failed");
			return;
		}
		var delay = Math.min(1000 * Math.pow(2, this.reconnectCount), MAX_RECONNECT_DELAY);
		this.reconnectCount++;
		this.reconnectTimer = setTimeout(function() {
			that.reconnectTimer = null;
			if (that.manualClose) return;
			// 复用最近一次连接参数（token/form_type）重新建连
			that.onStart(that.lastToken, that.lastFormType);
		}, delay);
	},
	onError: function(e) {
		// 仅通知页面提示，重连统一由 onWsClose 驱动（close 事件总在 error 后到达），避免双触发
		uni.$emit("socket_error", e);
	},
	close: function() {
		uni.closeSocket();
	},
	onStart: function(token, form_type) {
		// 记录连接参数供断线重连复用
		this.lastToken = token;
		this.lastFormType = form_type;
		this.connId++;
		this.manualClose = false;
		// 新建连接清除鉴权失败标记（如重新登录后再次进入），否则后续被动关闭不会再重连
		this.authFailed = false;
		// 取消未触发的自动重连，避免与本次新建连接重复
		if (this.reconnectTimer) {
			clearTimeout(this.reconnectTimer);
			this.reconnectTimer = null;
		}
		// 统一长连接网关地址（已含 ?service= 业务标识），追加参数时按已有 query 自动选择连接符
		let wssUrl = `${VUE_APP_WS_URL}`
		this.ws = uni.connectSocket({
			url: wssUrl + (wssUrl.indexOf('?') > -1 ? '&' : '?') + 'type=user&token=' + token + '&form_type=' + form_type,
			header: {
				'content-type': 'application/json'
			},
			method: 'GET',
			success: (res) => {}
		});
		this.ws.onOpen(this.onSocketOpen.bind(this))
		this.ws.onError(this.onError.bind(this));
		this.ws.onMessage(this.onMessage.bind(this))
		// 闭包捕获本次连接代数，旧连接的关闭事件因代数不匹配而被忽略
		const connId = this.connId;
		this.ws.onClose(() => this.onWsClose(connId));
	}
};

Socket.prototype.constructor = Socket;
export default Socket;

// Vue3 迁移：导出全局单例。
// main.js 的 $socket 与页面（customer_list/chat.vue）共用同一实例，
// 避免各自 new Socket() 产生多个 WebSocket 连接/定时器。
export const socketInstance = new Socket();
