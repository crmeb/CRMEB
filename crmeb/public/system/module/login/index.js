(function(global,$){
    $(document).ready(function() {

        $('.login-bg').iosParallax({
            movementFactor: 50
        });

        var layer = null;
        layui.use(['layer'], function () {
            layer = layui.layer;
        })

        var $captcha = $('#verify_img'),src = $captcha[0].src;
        function captcha(){
            $captcha.click(function(){
                this.src = src+'?'+Date.parse(new Date());
            });
        }
        captcha();

        $('#form').on('submit',function(){
            var account = $('#account').val(),pwd = $('#pwd').val(),verify = $('#verify').val();

            if(!account) return layer.msg('请输入登陆账号');
            if(!pwd) return layer.msg('请输入登陆密码');
            if(!verify) return layer.msg('请输入验证码');
            $.ajax({
                url: this.action,
                data: {
                    account:account,
                    pwd:pwd,
                    verify:verify
                },
                type: 'post',
                dataType: 'json',
                success: function (rem) {
                    if (rem.code == 200 || rem.status == 200) {
                        window.location.href = rem.data.url || '/admin/index/index.html';
                    }else{
                        $('#verify_img').attr('src',src + '?' + Date.parse(new Date()));
                        layer.msg(rem.msg);
                    }
                },
                error: function (err) {
                    layer.msg('系统错误');
                }
            })
            return false;
        })

    });

})(window,jQuery);