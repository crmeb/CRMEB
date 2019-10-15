(function(global){
    var factory = {},startCache = null,startCacheFn = function(startFn){ startFn && startFn(startCache);};
    factory.start = function(startFn){
        typeof startCache == 'function' ? startCacheFn(startFn) : requirejs(['vue','iview','layer'],function(Vue,iView,layer){
            Vue.use(iView);
            window.iView = iView;
            (startCache = Vue) && startCacheFn(startFn);
        });
    };

    global.mpFrame = factory;

    /*$(function init(){
        if(typeof footable=='function') $(".footable").footable();
        $(".no-sort").off('click');
        $('.search-item>.btn').on('click',function(){
            var that = $(this),value = that.data('value'),p = that.parent(),name = p.data('name'),form = p.parents();
            form.find('input[name="'+name+'"]').val(value);
            form.submit();
        });
        $('.search-item-value').each(function(){
            var that = $(this),name = that.attr('name'), value = that.val(),dom = $('.search-item[data-name="'+name+'"] .btn[data-value="'+value+'"]');
            dom.eq(0).removeClass('btn-outline btn-link').addClass('btn-primary btn-sm')
                .siblings().addClass('btn-outline btn-link').removeClass('btn-primary btn-sm')
        });
    });*/

})(this);