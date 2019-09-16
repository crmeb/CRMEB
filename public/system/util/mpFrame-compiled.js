(function (global) {
    var factory = {},
        startCache = null,
        startCacheFn = function startCacheFn(startFn) {
        startFn && startFn(startCache);
    };
    factory.start = function (startFn) {
        typeof startCache == 'function' ? startCacheFn(startFn) : requirejs(['vue', 'iview', 'layer'], function (Vue, iView, layer) {
            Vue.use(iView);
            window.iView = iView;
            (startCache = Vue) && startCacheFn(startFn);
        });
    };

    global.mpFrame = factory;
})(this);

//# sourceMappingURL=mpFrame-compiled.js.map