var globalMethods = function(vm,swal){
    return {
        swal:swal,
        layer:layer,
        openImage:function(href){
            return layer.open({
                type: 1,
                title: false,
                closeBtn: 0,
                shadeClose: true,
                content: '<img src="'+href+'" style="display: block;width: 100%;" />'
            });
        },
        $swal:function(type,param,code){
            if(param === undefined) param = function(){};
            switch (type){
                case 'delete':
                    if(typeof code != 'object') code = {};
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
                    swal("错误",param,"error");
                    break;
                case 'success':
                    swal("成功",param,"success");
                    break;
                case 'status':
                    code == 200 ? this.$swal('success',param) : this.$swal('error',param);
                    break;
                case 'message':
                    swal(param);
                    break;

            }
        },
        $alert:function(type,params,succFn){
            switch (type){
                case 'textarea':
                    swal({
                        title: params.title != undefined ? params.title.toString() : "请输入内容",
                        input: 'textarea',
                        inputValue: params.value || '',
                        confirmButtonText:"提交",
                        cancelButtonText: "取消",
                        showCancelButton: true
                    }).then(succFn).catch(console.log);
                    break;
            }
        },
        message:function(type,config){
            /*content	提示内容	String	-
            render	自定义描述内容，使用 Vue 的 Render 函数	Function	-
            duration	自动关闭的延时，单位秒，不关闭可以写 0	Number	1.5
            onClose	关闭时的回调	Function	-
            closable	是否显示关闭按钮*/
            vm.$Message.config({
                top: 1,
                duration:5
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
        notice:function(type,config){
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
        noticeClose:function(name){
            return vm.$Notice.close(name);
        },
        noticeDestroy:function(){
            return vm.$Notice.destroy();
        },
        modal:function(type,config){
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
        modalRemove:function(){
            return vm.$modal.remove();
        },
        loading:function(type,percent){
            switch (type){
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
setTimeout(function(){
    mpFrame.start(function(Vue){
        requirejs(['sweetalert','axios'],function(swal,axios){
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
            new Vue({
                el:'#vm',
                data:{},
                methods:{
                    globalApi:function(){
                        var api = globalMethods(this,swal);
                        api.closeModalFrame = function(name){
                            layer.close(layer.getFrameIndex(name) || name);
                        };
                        api.h = this.$createElement;
                        api.axios = axios;
                        api.createModalFrame = this.createModalFrame;
                        api.mpFrame = mpFrame;
                        api.layer = layer;
                        return api;
                    },
                    createModalFrame:function(title,src,opt){
                        opt === undefined && (opt = {});
                        return layer.open({
                            type: 2,
                            title:title,
                            area: [(opt.w || 750)+'px', (opt.h || 680)+'px'],
                            fixed: false, //不固定
                            maxmin: true,
                            moveOut:false,//true  可以拖出窗外  false 只能在窗内拖
                            anim:5,//出场动画 isOutAnim bool 关闭动画
                            offset:'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
                            shade:0,//遮罩
                            resize:true,//是否允许拉伸
                            content: src,//内容
                            move:'.layui-layer-title',// 默认".layui-layer-title",// 触发拖动的元素
                            moveEnd:function(){//拖动之后回调
                                console.log(this);

                            }
                        });
                    }
                },
                mounted:function(){
                    window._mpApi = this.globalApi();
                    $('.admin_close').on('click',function (e) {
                        $('.admin_open').removeClass('open');
                    })
                }
            })

        })

    });
},0);