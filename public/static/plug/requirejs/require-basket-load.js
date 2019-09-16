;(function () {
    var original_loader = requirejs.load;
    requirejs.load = function (context, moduleName, url) {
        var config = requirejs.s.contexts._.config;
        if (config.basket && config.basket.excludes && config.basket.excludes.indexOf(moduleName) !== -1) {
            original_loader(context, moduleName, url);
        } else {
            var unique = 1;
            if(config.basket && config.basket.unique && config.basket.unique.hasOwnProperty(moduleName) ){
                unique = config.basket.unique[moduleName];
            }
            basket.require({ url: url,unique:unique }).then(function () {
                context.completeLoad(moduleName);
            }, function (error) {
                context.onError(error);
            });
        }
    };
}());