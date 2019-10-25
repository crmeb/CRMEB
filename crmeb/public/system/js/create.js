(function(global,factory){

})(this,function(){"use strict";
    var createForm = function(h){
        this.h = h;
        return this;
    };
    var getProps = {
        input(options){
            let props = {};
            options
        }
    };
    createForm.prototype = {
        /**
         * @param opt
         */
        makeInput(opt){
            return this.h('i-input',{
                props:opt.props
            })
        }
    };
});