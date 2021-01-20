import {wss} from '@/libs/util';
import Setting from '@/setting';
import Vue from 'vue';

const vm = new Vue;

class wsSocket {
    constructor (opt) {
        this.ws = null;
        this.opt = opt ||{};
        this.init(opt.key);
    }

    onOpen (key = false) {
        this.opt.open && this.opt.open();
        let that = this;
        // this.send({
        //     type: 'login',
        //     data: util.cookies.get('token')
        // }).then(() => {
        //     that.ping();
        // });
        that.ping();
        this.socketStatus = true;


    }

    init (key) {
        let wsUrl = ''
        if(key == 1){
            wsUrl = Setting.wsAdminSocketUrl
        }
        if(key == 2){
            wsUrl = Setting.wsKefuSocketUrl
        }
        if(wsUrl){
            this.ws = new WebSocket(wss( wsUrl ));
            this.ws.onopen = this.onOpen.bind(this);
            this.ws.onerror = this.onError.bind(this);
            this.ws.onmessage = this.onMessage.bind(this);
            this.ws.onclose = this.onClose.bind(this);
        }

    }

    ping () {
        var that = this;
        this.timer = setInterval(function () {
            that.send({ type: 'ping' });
        }, 10000);
    }

    send (data) {
        return new Promise((resolve, reject) => {
            try {
                this.ws.send(JSON.stringify(data));
                resolve({ status: true });
            } catch (e) {
                reject({ status: false })
            }
        });
    }

    onMessage (res) {
        this.opt.message && this.opt.message(res);
    }

    onClose () {
        this.timer && clearInterval(this.timer);
        this.opt.close && this.opt.close();
    }

    onError (e) {
        this.opt.error && this.opt.error(e);
    }

    $on(...args) {
        vm.$on(...args);
    }
}


function createSocket(key) {
    return new Promise((resolve, reject) => {
        const ws = new wsSocket({
            key,
            open(){
                resolve(ws);
            },
            error(e){
                reject(e)
            },
            message(res){
                const { type, data = {} } = JSON.parse(res.data);
                vm.$emit(type, data);
            },
            close(e){

                vm.$emit('close', e);
            }
        })
    });
}




export const adminSocket = createSocket(1);
export const Socket = createSocket(2);
//
// class Socket {
//     constructor () {
//         this.ws = null;
//         this.socketStatus = false;
//     }
//
//     close () {
//         console.log('ws close');
//         clearInterval(this.timer);
//         this.ws.close();
//     }
//
//     onOpen (key = '') {
//         let that = this;
//         this.send({
//             type: key?'kefu_logon':'login',
//             data: key?util.cookies.kefuGet('token'):util.cookies.get('token')
//         }).then(() => {
//             that.ping();
//         });
//         this.vm.$emit('socket_open');
//         this.socketStatus = true;
//     }
//
//     init (vm,key) {
//         this.vm = vm;
//         if (this.socketStatus === false) {
//             try {
//                 let wsSocketUrl = Setting.wsSocketUrl;
//                 this.ws = new WebSocket(util.wss(wsSocketUrl));
//                 this.ws.onopen = this.onOpen.bind(this,key);
//                 this.ws.onerror = this.onError.bind(this);
//                 this.ws.onmessage = this.onMessage.bind(this);
//                 this.ws.onclose = this.onClose.bind(this);
//             } catch (e) {
//                 console.log('失败', e)
//             }
//         }
//     }
//
//     ping () {
//         var that = this;
//         this.timer = setInterval(function () {
//             that.send({ type: 'ping' });
//         }, 10000);
//     }
//
//     send (data) {
//         return new Promise((resolve, reject) => {
//             try {
//                 this.ws.send(JSON.stringify(data));
//                 resolve({ status: true });
//             } catch (e) {
//                 reject({ status: false })
//             }
//         });
//     }
//
//     onMessage (res) {
//         this.opt.message && this.opt.message();
//         const { type, data = {} } = JSON.parse(res.data);
//         this.vm.$emit(type, data);
//     }
//
//     onClose () {
//         this.timer && clearInterval(this.timer);
//     }
//
//     onError (e) {
//         this.opt.error && this.opt.error();
//     }
// }

// export default new Socket();
