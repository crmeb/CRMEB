// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import $store from "@/store";
import {
	HTTP_REQUEST_URL
} from "@/config/app.js";
import {
	VUE_APP_WS_URL
} from "@/utils/index.js";
import {
	getServerType
} from '@/api/api.js';
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
		uni.$emit('socketOpen', my)
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
		let datas = JSON.stringify(data)
		return uni.sendSocketMessage({
			data: datas
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
		uni.$emit("socket_error", e);
	},
	close: function() {
		uni.closeSocket();
	},
	onStart: function(token, form_type) {
		let wssUrl = `${VUE_APP_WS_URL}`
		this.ws = uni.connectSocket({
			url: wssUrl + '?type=user&token=' + token + '&form_type=' + form_type,
			header: {
				'content-type': 'application/json'
			},
			method: 'GET',
			success: (res) => {}
		});
		this.ws.onOpen(this.onSocketOpen.bind(this))
		this.ws.onError(this.onError.bind(this));
		this.ws.onMessage(this.onMessage.bind(this))
		this.ws.onClose(this.onClose.bind(this));
	}
};

Socket.prototype.constructor = Socket;
export default Socket;
