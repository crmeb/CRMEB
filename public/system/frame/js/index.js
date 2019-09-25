!(function () {
    var globalMethods = function (vm, swal) {
        return {
            swal: swal,
            layer: layer,
            openImage: function (href) {
                return layer.open({
                    type: 1,
                    title: false,
                    closeBtn: 0,
                    shadeClose: true,
                    content: '<img src="' + href + '" style="display: block;width: 100%;" />'
                });
            },
            $swal: function (type, param, code) {
                if (param === undefined) param = function () {
                };
                switch (type) {
                    case 'delete':
                        if (typeof code != 'object') code = {};
                        swal({
                            title: code.title || "您确定要删除这条信息吗",
                            text: code.text || "删除后将无法恢复，请谨慎操作！",
                            type: code.type || "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: code.confirm || "是的，我要删除！",
                            cancelButtonText: code.cancel || "让我再考虑一下…",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        }).then(param).catch(console.log);
                        break;
                    case 'error':
                        swal("错误", param, "error");
                        break;
                    case 'success':
                        swal("成功", param, "success");
                        break;
                    case 'status':
                        code == 200 ? this.$swal('success', param) : this.$swal('error', param);
                        break;
                    case 'message':
                        swal(param);
                        break;

                }
            },
            $alert: function (type, params, succFn) {
                switch (type) {
                    case 'textarea':
                        swal({
                            title: params.title != undefined ? params.title.toString() : "请输入内容",
                            input: 'textarea',
                            inputValue: params.value || '',
                            confirmButtonText: "提交",
                            cancelButtonText: "取消",
                            showCancelButton: true
                        }).then(succFn).catch(console.log);
                        break;
                }
            },
            message: function (type, config) {
                /*content	提示内容	String	-
                render	自定义描述内容，使用 Vue 的 Render 函数	Function	-
                duration	自动关闭的延时，单位秒，不关闭可以写 0	Number	1.5
                onClose	关闭时的回调	Function	-
                closable	是否显示关闭按钮*/
                vm.$Message.config({
                    top: 1,
                    duration: 5
                });
                switch (type) {
                    case 'success':
                        return vm.$Message.success(config);
                        break;
                    case 'warning':
                        return vm.$Message.warning(config);
                        break;
                    case 'error':
                        return vm.$Message.error(config);
                        break;
                    case 'loading':
                        return vm.$Message.loading(config);
                        break;
                    default :
                        return vm.$Message.info(config || type);
                        break;
                }
            },
            notice: function (type, config) {
                switch (type) {
                    case 'info':
                        return vm.$Notice.info(config);
                        break;
                    case 'success':
                        return vm.$Notice.success(config);
                        break;
                    case 'warning':
                        return vm.$Notice.warning(config);
                        break;
                    case 'error':
                        return vm.$Notice.error(config);
                        break;
                    default :
                        return vm.$Notice.open(config || type);
                        break;
                }
            },
            noticeClose: function (name) {
                return vm.$Notice.close(name);
            },
            noticeDestroy: function () {
                return vm.$Notice.destroy();
            },
            modal: function (type, config) {
                switch (type) {
                    case 'confirm':
                        return vm.$Modal.confirm(config);
                        break;
                    case 'success':
                        return vm.$Modal.success(config);
                        break;
                    case 'warning':
                        return vm.$Modal.warning(config);
                        break;
                    case 'error':
                        return vm.$Modal.error(config);
                        break;
                    default :
                        return vm.$Modal.info(config || type);
                        break;
                }
            },
            modalRemove: function () {
                return vm.$modal.remove();
            },
            loading: function (type, percent) {
                switch (type) {
                    case 'start':
                        return vm.$Loading.start();
                        break;
                    case 'finish':
                        return vm.$Loading.finish();
                        break;
                    case 'error':
                        return vm.$Loading.error();
                        break;
                    case 'update':
                        return vm.$Loading.update(percent);
                        break;
                }
            }
        }
    };
    setTimeout(function () {
        mpFrame.start(function (Vue) {
            requirejs(['sweetalert', 'axios'], function (swal, axios) {
                axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
                axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
                new Vue({
                    el: '#vm',
                    data: {
                        chnNumChar: ["零", "一", "二", "三", "四", "五", "六", "七", "八", "九"],
                        chnUnitSection: ["", "万", "亿", "万亿", "亿亿"],
                        chnUnitChar: ["", "十", "百", "千"],
                        noticeTime: 7
                    },
                    methods: {
                        globalApi: function () {
                            var api = globalMethods(this, swal);
                            api.closeModalFrame = function (name) {
                                layer.close(layer.getFrameIndex(name) || name);
                                //关闭页面刷新
                                // window.frames[$(".page-tabs-content .active").index()].location.reload();
                            };
                            api.h = this.$createElement;
                            api.axios = axios;
                            api.createModalFrame = this.createModalFrame;
                            api.mpFrame = mpFrame;
                            api.layer = layer;
                            return api;
                        },
                        createModalFrame: function (title, src, opt) {
                            opt === undefined && (opt = {});
                            var h = 0;
                            if (window.innerHeight < 800 && window.innerHeight >= 700) {
                                h = window.innerHeight - 50;
                            } else if (window.innerHeight < 900 && window.innerHeight >= 800) {
                                h = window.innerHeight - 100;
                            } else if (window.innerHeight < 1000 && window.innerHeight >= 900) {
                                h = window.innerHeight - 150;
                            } else if (window.innerHeight >= 1000) {
                                h = window.innerHeight - 200;
                            } else {
                                h = window.innerHeight;
                            }
                            var area = [(opt.w || window.innerWidth / 2.4) + 'px', (!opt.h || opt.h > h ? h : opt.h) + 'px'];
                            return layer.open({
                                type: 2,
                                title: title,
                                area: area,
                                fixed: false, //不固定
                                maxmin: true,
                                moveOut: false,//true  可以拖出窗外  false 只能在窗内拖
                                anim: 5,//出场动画 isOutAnim bool 关闭动画
                                offset: 'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
                                shade: 0,//遮罩
                                resize: true,//是否允许拉伸
                                content: src,//内容
                                move: '.layui-layer-title',// 默认".layui-layer-title",// 触发拖动的元素
                                moveEnd: function () {//拖动之后回调
                                    // console.log(this);

                                }
                            });
                        },
                        SectionToChinese: function (section) {
                            var strIns = '', chnStr = '';
                            var unitPos = 0;
                            var zero = true;
                            while (section > 0) {
                                var v = section % 10;
                                if (v === 0) {
                                    if (!zero) {
                                        zero = true;
                                        chnStr = this.chnNumChar[v] + chnStr;
                                    }
                                } else {
                                    zero = false;
                                    strIns = this.chnNumChar[v];
                                    strIns += this.chnUnitChar[unitPos];
                                    chnStr = strIns + chnStr;
                                }
                                unitPos++;
                                section = Math.floor(section / 10);
                            }
                            return chnStr;
                        },
                        NumberToChinese: function (num) {
                            var unitPos = 0;
                            var strIns = '', chnStr = '';
                            var needZero = false;
                            if (num === 0) return this.chnNumChar[0];
                            while (num > 0) {
                                var section = num % 10000;
                                if (needZero) {
                                    chnStr = this.chnNumChar[0] + chnStr;
                                }
                                strIns = this.SectionToChinese(section);
                                strIns += (section !== 0) ? this.chnUnitSection[unitPos] : this.chnUnitSection[0];
                                chnStr = strIns + chnStr;
                                needZero = (section < 1000) && (section > 0);
                                num = Math.floor(num / 10000);
                                unitPos++;
                            }
                            return chnStr;
                        },
                        titleRoll: function (newTitle) {
                            var time = this.noticeTime, oldTitle = 'CRMEB管理系统';
                            var timeInterval = setInterval(function () {
                                console.log(time);
                                if (time <= 0) {
                                    clearInterval(timeInterval);
                                    document.title = oldTitle;
                                    return;
                                }
                                document.title = newTitle.substring(1, newTitle.length) + newTitle.substring(0, 1);
                                newTitle = document.title.substring(0, newTitle.length);
                                time--;
                            }, 1000)
                        }
                    },
                    mounted: function () {
                        window._mpApi = this.globalApi();
                        var that = this;
                        $('.admin_close').on('click', function (e) {
                            $('.admin_open').removeClass('open');
                        });
                        var ws = new Socket;
                        ws.setVm(this);
                        this.$on('NEW_ORDER', function(data){
                            that.$Notice.info({
                                title: '新订单',
                                duration: 8,
                                desc: '您有一个新的订单(' + data.order_id + '),请注意查看'
                            });
                            if(window.newOrderAudioLink) (new Audio(window.newOrderAudioLink)).play();
                        });
                        this.$on('NEW_REFUND_ORDER', function(data){
                            that.$Notice.warning({
                                title: '订单提醒',
                                duration: 8,
                                desc: '您有一个订单(' + data.order_id + ')申请退款,请注意查看'
                            });
                            if(window.newOrderAudioLink) (new Audio(window.newOrderAudioLink)).play();
                        });
                        this.$on('WITHDRAW', function(data){
                            that.$Notice.warning({
                                title: '提现提醒',
                                duration: 8,
                                desc: '有用户申请提现(' + data.id + '),请注意查看'
                            })
                        });
                        this.$on('STORE_STOCK', function(data){
                            that.$Notice.warning({
                                title: '库存预警',
                                duration: 8,
                                desc: '(' + data.id + ')商品库存不足,请注意查看'
                            })
                        });
                        this.$on('PAY_SMS_SUCCESS', function(data){
                            that.$Notice.info({
                                title: '短信充值成功',
                                duration: 8,
                                desc: '恭喜您充值'+ data.price +'元，获得'+ data.number +'条短信'
                            })
                        });
                    }
                })

            })

        });
    }, 0);

    var Socket = function () {
        this.ws = new WebSocket(this.getUrl());
        this.ws.onopen = this.onOpen.bind(this);
        this.ws.onerror = this.onError.bind(this);
        this.ws.onmessage = this.onMessage.bind(this);
        this.ws.onclose = this.onClose.bind(this);
    };

    Socket.prototype = {
        setVm: function(vm) {
            this.vm = vm;
        },
        onOpen: function () {
            console.log('ws open');
            this.init();
            this.send({
                type: 'login',
                data: $h.getCookie('PHPSESSID')
            })
        },
        init: function () {
            var that = this;
            this.timer = setInterval(function () {
                that.send({type: 'ping'})
            }, 10000);
        },
        send: function (data) {
            return this.ws.send(JSON.stringify(data));
        },
        onMessage: function (res) {
            var data = JSON.parse(res.data);
            this.vm.$emit(data.type, data.data || {});
        },
        onClose: function (e) {
            console.log('ws close', e.code);
            clearInterval(this.timer);
        },
        onError: function (e) {
            console.log('ws error', e.data ? JSON.parse(e.data).message : '');
        },
        getUrl: function () {
            var ishttps = 'https:' == document.location.protocol ? true : false;
            return ( ishttps ? 'wss': 'ws' )+'://' + document.URL.split('//')[1].split('/')[0] + ':20002';
        }
    };

    Socket.prototype.constructor = Socket;

}());