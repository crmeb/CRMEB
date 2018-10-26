(function(global,$){
    var $err = $('#err');
    $err.hide();
    var showError = function(err){
        $err.html(err).show();
    };

    $('form').on('submit',function(){
        var account = $('#account'),pwd = $('#pwd');
        if(!account)return showError('请输入用户名!');
        if(!pwd) return showError('请输入密码');
    })
})(window,jQuery);
$(document).ready(function() {
    $('.login-bg').iosParallax({
        movementFactor: 50
    });
});

(function captcha(){
    var $captcha = $('#verify_img'),src = $captcha[0].src;
    $captcha.on('click',function(){
        this.src = src+'?'+Date.parse(new Date());
    });
})();