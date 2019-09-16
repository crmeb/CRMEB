(function(global,factory){
    typeof define == 'function' && define.amd && define(['lodash'],factory);
})(this,function(_){ 'use strict';
    //创建iframe框
    var $cf = {
        name:'CreateFrame',
        //父级元素
        parent:document.body,
        //iframe高度
        height:'100%',
        //iframe宽度
        width:'100%',
        itemList:{},
        count:1,
        frameClass:'m-frame-item',
        frameName:'m-frame-name',
        //删除frame
        destroy:function(frameName){
            $cf.itemList[frameName].$destroy();
        },
        //配置参数
        setConf:function(conf){
            conf.hieght && ($cf.hieght = conf.hieght);
            conf.wight && ($cf.wight = conf.wight);
            conf.parent &&($cf.parent = conf.parent);
        },
        //隐藏所有iframe
        hideAllFrame:function(){
            _($cf.itemList).each(function(v,k){
                v.isShow = false;
            })
        },
        //隐藏一个iframe
        hideFrame:function(frameName){
            $cf._displayItem(frameName,false);
        },
        //显示一个iframe
        showFrame:function(frameName){
            $cf._displayItem(frameName,true);
        },
        toggleFrame:function(frameName){
            $cf.hideAllFrame();
            $cf.showFrame(frameName);
        },
        _displayItem:function(frameName,type){
            _($cf.itemList).each(function(v,k){
                v.frameName == frameName && ($cf.itemList[k].isShow = type);
            });
        },
        _getFrameClass:function(){
            return $cf.frameClass + (++$cf.count);
        },
        _getDefaultName:function(){
            return $cf.frameName+$cf.count;
        },
        _setItem:function($e){
            $cf.itemList[$e.frameName] = $e
        },
        _destroyItem:function(frameName){
            $cf.itemList[frameName].$el.remove();
            delete $cf.itemList[frameName];
        },
        _factory: function (src,opt) {
            return {
                template: '<transition name="frameMove"><iframe v-show="isShow" :name="frameName" class="animated m-frame" :class="frameClass" :src="src" frameborder="false" scrolling="auto" width="100%" height="auto" allowtransparency="true" :style="{height:Height,width:Width}"></iframe></transition>',
                data: function () {
                    return {
                        frameClass: $cf._getFrameClass(),
                        frameName: opt.frameName || $cf._getDefaultName(),
                        isShow: true,
                        src: src,
                        height: opt.height || $cf.height,
                        width: opt.width || $cf.width,
                        load:false
                    }
                },
                methods:{
                    show:function(){
                        this.isShow = true;
                    },
                    hide:function(){
                        this.isShow = false;
                    },
                    destroy:function(){
                        this.$destroy();
                    }
                },
                computed: {
                    Height: function () {
                        return _.isNumber(this.height) ? this.height + 'px' : this.height;
                    },
                    Width: function () {
                        return _.isNumber(this.width) ? this.width + 'px' : this.width;
                    }
                },
                beforeDestroy:function(){
                    $cf._destroyItem(this.frameName);
                }
            }
        }
    };

    var $CreateFrameInstall = function(Vue){
        var $m = function(src,opt){
            var loadFn = opt.loadFn,parent = opt.parent || $cf.parent,frame = Vue.extend($cf._factory(src,opt)),
                $vm = new frame(),tpl = $vm.$mount().$el;
            tpl.onload = function(){ ($vm.load = true) && loadFn && loadFn(this.contentWindow);};
            parent.appendChild(tpl) && $cf._setItem($vm);
            return $vm;
        };
        $m.hide = $cf.hideFrame;
        $m.show = $cf.showFrame;
        $m.destroy = $cf.destroy;
        $m.toggle = $cf.toggleFrame;
        $m.hideAll = $cf.hideAllFrame;
        $m.conf = $cf.setConf;
        Vue.prototype.$CreateFrame = $m;
    };

    return {
        install:function(Vue){
            $CreateFrameInstall(Vue);
        }
    }
});