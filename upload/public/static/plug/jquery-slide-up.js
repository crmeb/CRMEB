(function($){
    $.fn.extend({
        "slideUp":function(value){
            var docthis = this;
            //默认参数
            value=$.extend({
                "li_h":"30",
                "time":2500,
                "movetime":500
            },value)

            //向上滑动动画
            function autoani(){
                $("li:first",docthis).animate({"margin-top":-value.li_h},value.movetime,function(){
                    $(this).css("margin-top",0).appendTo(".line");
                })
            }
            //自动间隔时间向上滑动
            var anifun = setInterval(autoani,value.time);
        }
    })
})(jQuery);