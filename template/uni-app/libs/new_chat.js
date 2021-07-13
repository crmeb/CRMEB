// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import $store from "@/store";
import {
	HTTP_REQUEST_URL,
	VUE_APP_WS_URL
} from "@/config/app.js";
let wsUrl = `${VUE_APP_WS_URL}`
const Socket = function() {
	
	// this.ws.close(this.close.bind(this));
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
	// close() {
	//   clearInterval(this.timer);
	//   this.ws.close();
	// },
	onSocketOpen: function(my) {
		uni.$emit('socketOpen',my)
	},
	init: function() {
		var that = this;
		this.timer = setInterval(function() {
			that.send({
				type: "ping"
			});
		}, 10000);
	},
	send: function(data) {
		let gl13r25s4sn3or36o6sg5h = JSON.stringify(data)
		return uni.sendSocketMessage({
			data: gl13r25s4sn3or36o6sg5h
		});
	},
	onMessage: function(res) {
		const {
			type,
			data = {}
		} = JSON.parse(res.data);
		uni.$emit(type, data)
	},

	onClose: function() {
		uni.closeSocket()
		clearInterval(this.timer);
		uni.$emit("socket_close");
	},
	onError: function(e) {
		console.log(e);
		uni.$emit("socket_error", e);
	},
	close: function() {
		uni.closeSocket();
	},
	onStart:function(){
		this.ws = uni.connectSocket({
			// #ifdef H5
			url:wss(wsUrl),
			// #endif
			// #ifdef MP
			url:wsUrl,
			// #endif
			header: {
				'content-type': 'application/json'
			},
			method: 'GET',
			success: (res) => {
				console.log(res, 'success');
			}
		});
		this.ws.onOpen(this.onSocketOpen.bind(this))
		this.ws.onError(this.onError.bind(this));
		this.ws.onMessage(this.onMessage.bind(this))
		this.ws.onClose(this.onClose.bind(this));
	}
};

Socket.prototype.constructor = Socket;
export default Socket;
