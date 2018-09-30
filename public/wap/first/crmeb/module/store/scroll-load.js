(function(global,factory){
    typeof define == 'function' && define(['better-scroll'],factory)
})(this,function(BScroll){
    return {
        install:function(Vue){
            Vue.prototype.$scrollLoad = function(dom,loadFn){
                var scroll = new BScroll(dom,{click:true,probeType:1,cancelable:false,deceleration:0.005,snapThreshold:0.01});
                scroll.on('pullingUp',function(){
                    loadFn && loadFn(scroll.finishPullUp,scroll.refresh);
                });
                return scroll;
            };
        }
    }
});