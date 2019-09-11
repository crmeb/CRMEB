(function(global,factory){
    typeof define == 'function' && define.amd && define(factory)
})(this,function(){
    return function(){
        return {
            methods:{
                hideModelFrameSpin(){
                    vm.showStatus.modelFrameSpinShow = false;
                },
                message(type,config){
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
                notice(type,config){
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
                noticeClose(name){
                    return vm.$Notice.close(name);
                },
                noticeDestroy(){
                    return vm.$Notice.destroy();
                },
                addModalFrame:function(title,src,opt){
                    vm.showStatus.modelFrameSpinShow = (src != vm.modalFrame.frame$vm.src);
                    vm.modalFrame.title = title;
                    vm.modalFrame.frame$vm.src = src;
                    vm.modalFrame.isShow = true;
                    opt.width && (vm.modalFrame.width = opt.width);
                    opt.height && (vm.modalFrame.frame$vm.height = opt.height);
                }
            }
        }
    }
});