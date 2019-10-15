(function(global,factory){
    typeof define == 'function' && define.amd && define(factory());
})(this,function(){
    return {
        isNumber : function(string){
            return (parseFloat(string).toString() != "NaN");
        }
    }
});