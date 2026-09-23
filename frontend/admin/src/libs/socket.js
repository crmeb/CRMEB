// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// admin 端 WebSocket（内部事件分发用 mitt 替代 new Vue() 实例）
import { getCookies, setCookies } from '@/libs/util';
import { getWorkermanUrl } from '@/api/setting';
import mitt from 'mitt';

const vm = mitt();
let wsAdminSocketUrl = getCookies('WS_ADMIN_URL') || '';

class wsSocket {
  constructor(opt) {
    this.ws = null;
    this.opt = opt || {};
    this.init(opt.key);
  }

  onOpen() {
    this.opt.open && this.opt.open();
    let that = this;
    that.ping();
    this.socketStatus = true;
  }

  init(key) {
    let wsUrl = '';
    if (key == 1) {
      wsUrl = wsAdminSocketUrl;
    }
    if (wsUrl) {
      this.ws = new WebSocket(wsUrl);
      this.ws.onopen = this.onOpen.bind(this);
      this.ws.onerror = this.onError.bind(this);
      this.ws.onmessage = this.onMessage.bind(this);
      this.ws.onclose = this.onClose.bind(this);
    }
  }

  ping() {
    var that = this;
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

  $on(...args) {
    vm.on(...args);
  }

  $off(...args) {
    vm.off(...args);
  }
}

async function createSocket(key) {
  // 先获取最新网关地址再建立连接，避免使用缓存的过期地址
  const res = await getWorkermanUrl();
  wsAdminSocketUrl = res.data.admin;
  setCookies('WS_ADMIN_URL', res.data.admin);
  return new Promise((resolve, reject) => {
    const ws = new wsSocket({
      key,
      open() {
        resolve(ws);
        vm.emit('socket_open', key);
      },
      error(e) {
        reject(e);
      },
      message(res) {
        const { type, data = {} } = JSON.parse(res.data);
        vm.emit(type, data);
      },
      close(e) {
        vm.emit('close', { e, key });
      },
    });
  });
}

export const adminSocket = createSocket(1);
