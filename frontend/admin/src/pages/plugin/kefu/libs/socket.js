// +----------------------------------------------------------------------
// | 客服插件 Socket 模块。
// | 客服工作台专属通道，独立于系统 libs/socket，避免插件依赖系统实现；
// | 系统侧仅保留 adminSocket（后台消息通知）。
// +----------------------------------------------------------------------

import { getCookies, setCookies } from '@/libs/util';
import { getWorkermanUrl } from '@/pages/plugin/kefu/api';
import mitt from 'mitt';

// 内部事件分发用 mitt 替代 new Vue() 实例（对外仍暴露 $on/$off）
const vm = mitt();
let wsKefuSocketUrl = getCookies('WS_CHAT_URL') || '';

class wsSocket {
  constructor(opt) {
    this.ws = null;
    this.opt = opt || {};
    this.init();
  }

  onOpen() {
    this.opt.open && this.opt.open();
    this.ping();
    this.socketStatus = true;
  }

  init() {
    if (wsKefuSocketUrl) {
      this.ws = new WebSocket(wsKefuSocketUrl);
      this.ws.onopen = this.onOpen.bind(this);
      this.ws.onerror = this.onError.bind(this);
      this.ws.onmessage = this.onMessage.bind(this);
      this.ws.onclose = this.onClose.bind(this);
    }
  }

  ping() {
    const that = this;
    this.timer = setInterval(function () {
      that.send({ type: 'ping' });
    }, 10000);
  }

  send(data) {
    return new Promise((resolve, reject) => {
      try {
        this.ws.send(JSON.stringify(data));
        resolve({ status: true });
      } catch (e) {
        reject({ status: false });
      }
    });
  }

  onMessage(res) {
    this.opt.message && this.opt.message(res);
  }

  onClose() {
    this.timer && clearInterval(this.timer);
    this.opt.close && this.opt.close();
  }

  onError(e) {
    this.opt.error && this.opt.error(e);
  }

  // 兼容 Vue2 $on 的事件名数组用法（mitt 本身不支持数组）
  $on(type, handler) {
    if (Array.isArray(type)) {
      type.forEach((t) => vm.on(t, handler));
      return;
    }
    vm.on(type, handler);
  }

  $off(type, handler) {
    if (Array.isArray(type)) {
      type.forEach((t) => vm.off(t, handler));
      return;
    }
    vm.off(type, handler);
  }
}

async function createSocket() {
  // 先获取最新网关地址再建立连接，避免使用缓存的过期地址
  const res = await getWorkermanUrl();
  wsKefuSocketUrl = res.data.chat;
  setCookies('WS_CHAT_URL', res.data.chat);
  return new Promise((resolve, reject) => {
    const ws = new wsSocket({
      open() {
        resolve(ws);
        vm.emit('socket_open', 2);
      },
      error(e) {
        reject(e);
      },
      message(res) {
        const { type, data = {} } = JSON.parse(res.data);
        vm.emit(type, data);
      },
      close(e) {
        vm.emit('close', { e, key: 2 });
      },
    });
  });
}

export const Socket = createSocket();
