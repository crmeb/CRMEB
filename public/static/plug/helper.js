(function(global,factory){
    typeof define == 'function' && define('helper',[],factory.bind(window));
    global['$h'] = factory();
})(this,function(){
    var $h = $h || {};
    $h._errorMsgOptions = {offset: '80%',anim: 2,time:1200,shadeClose:true,shade:'0.1'};
    $h.returnErrorMsg = function(msg,fn){
        $h.pushMsg(msg,fn);
        return false;
    };
    $h._loadIndex = null;
    $h.pushMsg  = function(msg,fn){
        requirejs(['layer'],function(layer){
            layer.msg(msg.toString(),$h._errorMsgOptions,fn);
        });
    };

    $h.pushMsgOnceStatus = false;
    $h.pushMsgOnce = function(msg,fn){
        if($h.pushMsgOnceStatus) return ;
        $h.pushMsgOnceStatus = true;
        $h.pushMsg(msg,function(){
            fn && fn();
            $h.pushMsgOnceStatus = false;
        });
    }

    /**
     * 底部浮动框
     * @param msg
     */
    $h._promptStyle='<style id="_loading_bounce_style"> #_loading_bounce._prompt_hide{animation:down-hide .25s ease-in; animation-fill-mode: forwards; } #_loading_bounce{z-index: 998;position:fixed;bottom:0;background:#fff;width:100%;height:60px;box-shadow:0 1px 15px rgba(0,0,0,0.17);animation:up-show .25s ease-in}@keyframes up-show{0%{transform:translateY(60px)}100%{transform:translateY(0px)}}@keyframes down-hide{0%{transform:translateY(0px)}100%{transform:translateY(60px)}}#_loading_bounce ._double-container{height: 100%;display: table;position: absolute;width: 30%;left: 44%;} #_loading_bounce ._double-container .double-text{display: table-cell;vertical-align: middle;font-size: 12px;}.double-bounce1,.double-bounce2{width:50px;height:50px;border-radius:50%;background-color:#67CF22;opacity:.6;position:absolute;top:50%;margin-top:-25px;left:26%;-webkit-animation:bounce 2.0s infinite ease-in-out;-moz-animation:bounce 2.0s infinite ease-in-out;-o-animation:bounce 2.0s infinite ease-in-out;animation:bounce 2.0s infinite ease-in-out}.double-bounce2{-webkit-animation-delay:-1.0s;-moz-animation-delay:-1.0s;-o-animation-delay:-1.0s;animation-delay:-1.0s}@keyframes bounce{0%,100%{transform:scale(0.0)}50%{transform:scale(1.0)}}</style>';
    $h._promptHtml = '<div id="_loading_bounce" class="_prompt_loading"><div class="mop-css-1 double-bounce"><div class="double-bounce1"></div><div class="double-bounce2"></div></div><div class="_double-container"><span class="double-text">请稍等片刻...</span></div></div>';
    $h.prompt = function(msg){
        var $body = $('body'),$prompt = $($h._promptHtml);
        if(!$body.find('#_loading_bounce_style').length)
            $body.append($h._promptStyle);
        $prompt.find('.double-text').text(msg);
        $body.append($prompt);
    };
    $h.promptClear = function() {
        var that = $('._prompt_loading');
        that.addClass('_prompt_hide');
        setTimeout(function(){
            that.remove();
        },250)
    };
    $h.load = function(){
        if($h._loadIndex !== null) $h.loadClear();
        requirejs(['layer'],function(layer) {
            $h._loadIndex = layer.load(2, {shade: 0.3});
        });
    };
    $h.loadFFF = function(){
        if($h._loadIndex !== null) $h.loadClear();
        requirejs(['layer'],function(layer) {
            $h._loadIndex = layer.load(1, {shade: [0.1,'#fff']});
        });
    };
    $h.loadClear = function(){
        requirejs(['layer'],function(layer){
            setTimeout(function(){
                layer.close($h._loadIndex);
            },250);
        });
    };

    $h.uploadFile = function (name,url,successFn,errorFn) {
        $.ajaxFileUpload({
            url: url,
            type: 'post',
            secureuri: false, //一般设置为false
            fileElementId: name, // 上传文件的id、name属性名
            dataType: 'json', //返回值类型，一般设置为json、application/json
            success:successFn,
            error: errorFn
        });
    };
    $h.ajaxUploadFile = function (name,url,fnGroup) {
        fnGroup.start && fnGroup.start();
        $.ajaxFileUpload({
            url: url,
            type: 'POST',
            secureuri: false, //一般设置为false
            fileElementId: name, // 上传文件的id、name属性名
            dataType: 'JSON', //返回值类型，一般设置为json、application/json
            success:function(res,status){
                fnGroup.success && fnGroup.success(res,status);
                fnGroup.end && fnGroup.end(res,status);
                // var fileInput = $("#"+name),html = fileInput.prop('outerHTML'),p = fileInput.parent();
                // fileInput.remove();
                // p.append(html);
            },
            error: function(res,status){
                fnGroup.error && fnGroup.error(res,status);
                fnGroup.end && fnGroup.end(res,status);
            }
        });
    };

    //除法函数，用来得到精确的除法结果
    //说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。
    //调用：_h.Div(arg1,arg2)
    //返回值：arg1除以arg2的精确结果
    $h.div = function(arg1,arg2){
        var t1=0,t2=0,r1,r2;
        try{t1=arg1.toString().split(".")[1].length;}catch(e){}
        try{t2=arg2.toString().split(".")[1].length;}catch(e){}
        with(Math){
            r1=Number(arg1.toString().replace(".",""));
            r2=Number(arg2.toString().replace(".",""));
            return (r1/r2)*pow(10,t2-t1);
        }
    };
    //乘法函数，用来得到精确的乘法结果
    //说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
    //调用：_h.Mul(arg1,arg2)
    //返回值：arg1乘以arg2的精确结果
    $h.Mul = function(arg1,arg2) {
        var m=0,s1=arg1.toString(),s2=arg2.toString();
        try{m+=s1.split(".")[1].length}catch(e){}
        try{m+=s2.split(".")[1].length}catch(e){}
        return Number(s1.replace(".","")) * Number(s2.replace(".","")) / Math.pow(10,m);
    };

    //加法函数，用来得到精确的加法结果
    //说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
    //调用：_h.Add(arg1,arg2)
    //返回值：arg1加上arg2的精确结果
    $h.Add = function(arg1,arg2){
        var r1,r2,m;
        try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
        try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
        m=Math.pow(10,Math.max(r1,r2));
        return (arg1*m+arg2*m)/m;
    };

    //加法函数，用来得到精确的减法结果
    //说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的减法结果。
    //调用：_h.Sub(arg1,arg2)
    //返回值：arg1减去arg2的精确结果
    $h.Sub = function(arg1,arg2){
        var r1,r2,m,n;
        try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
        try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
        m=Math.pow(10,Math.max(r1,r2));
        //动态控制精度长度
        n=(r1>=r2)?r1:r2;
        return ((arg1*m-arg2*m)/m).toFixed(n);
    };
    $h.cookie = function(key,val,time) {
        if(val == undefined){
            return _helper.getCookie(key);
        }else if(val == null){
            return _helper.delCookie(key);
        }else{
            return _helper.setCookie(key,val,time);
        }
    };
    //操作cookie
    $h.setCookie = function(key,val,time){//设置cookie方法
        var date=new Date(); //获取当前时间
        if(!time) time = 1;  //将date设置为n天以后的时间
        date.setTime(date.getTime()+time*24*3600*1000); //格式化为cookie识别的时间
        document.cookie=key + "=" + val +";expires="+date.toGMTString();  //设置cookie
    };
    
    $h.getCookie = function(key) {//获取cookie方法
        /*获取cookie参数*/
        var getCookie = document.cookie.replace(/;[ ]/g, ";");  //获取cookie，并且将获得的cookie格式化，去掉空格字符
        var arrCookie = getCookie.split(";");  //将获得的cookie以"分号"为标识 将cookie保存到arrCookie的数组中
        var tips;  //声明变量tips
        for (var i = 0; i < arrCookie.length; i++) {   //使用for循环查找cookie中的tips变量
            var arr = arrCookie[i].split("=");   //将单条cookie用"等号"为标识，将单条cookie保存为arr数组
            if (key == arr[0]) {  //匹配变量名称，其中arr[0]是指的cookie名称，如果该条变量为tips则执行判断语句中的赋值操作
                tips = arr[1];   //将cookie的值赋给变量tips
                break;   //终止for循环遍历
            }
        }
        return tips;
    };

    $h.delCookie = function(key){ //删除cookie方法
        var date = new Date(); //获取当前时间
        date.setTime(date.getTime()-10000); //将date设置为过去的时间
        document.cookie = key + "=v; expires =" +date.toGMTString();//设置cookie
    };

    $pushCookie = function(key){
        var data = $h.getCookie(key);
        $h.delCookie(key);
        return data;
    };

    $h.getParmas = function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg);  //匹配目标参数
        if (r != null) return decodeURI(r[2]); return null; //返回参数值
    };

    $h.U = function(opt){
        var m = opt.m || 'wap',c = opt.c || 'public_api', a = opt.a || 'index',q = opt.q || '',p = opt.p || {},_params = '';
        _params = Object.keys(p).map(function(key){
            return key+'/'+p[key];
        }).join('/');
        return "/"+m+"/"+c+"/"+a+(_params == '' ? '' : '/'+_params)+(q == '' ? '' : '?'+q);
    };

    $h.isLogin = function(){
        return !!$h.getCookie('is_login');
    };
    
    return $h;

});