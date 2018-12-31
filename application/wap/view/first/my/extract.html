
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport"content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!-- 禁止百度转码 -->
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <title>用户提现</title>
    {include file="public/style" /}
</head>
<body>
<div class="user-cash">
    <section>
        <div class="pay-selection">
            <div class="default default-hock"><i style="font-style: normal;">银联卡</i> <div class="icon current"><span></span></div></div>
            <div class="selects">
                <ul>
                    <li data-name="alipay">支付宝 <div class="icon"><span></span></div></li>
                    <li data-name="bank">银联卡 <div class="icon"><span></span></div></li>
                    <input class="payname" type="hidden" value="{$extractInfo.extract_type ?:'bank'}" />
                </ul>
            </div>
        </div>
        <div class="card-wrapper">
            <div class="card-info">
                <input class="card-name" type="text" placeholder="姓名" value="{$extractInfo.real_name}"/>
                <div class="bank">
                    <input class="card-number" type="number" placeholder="卡号" value="{$extractInfo.bank_code?:''}"/>
                    <input class="card-address" type="text" placeholder="开户行" value="{$extractInfo.bank_address}"/>
                </div>
                <div class="alipay">
                    <input class="alipay-number" type="text" placeholder="支付宝账号" value="{$extractInfo.alipay_code}"/>
                </div>
                <input class="card-money" type="tel" placeholder="请输入提现金额"/>
                <span class="balance">余额：￥<i>{$userInfo.now_money}</i></span>
            </div>
        </div>
        <div class="error-txt" style="margin: 0;"></div>
        <div class="submit"><span>提现</span></div>
    </section>
</div>
{include file="public/right_nav" /}
<script type="text/javascript">
    var def_type = $('.payname').val(), def = $('[data-name='+def_type+']');
    def.find('.icon').addClass('current');
    $('.default-hock i').text(def.text());
    $('.'+def_type).addClass('active');
    $(document).ready(function() {
        var min_p = '{$minExtractPrice}';
        cardOption();
        function cardOption() {
            $('.default-hock').on('click', function() {
                $('.selects').slideToggle();
            });
        }
        function cardTab() {
            var cardWrapper = $('.card-wrapper');
            var selects = $('.selects');
            selects.find('li').on('click', function() {
                selects.find('li').find('.icon').removeClass('current');
                $(this).find('.icon').addClass('current');
                $('.payname').val($(this).attr('data-name'));
                if($('.payname').val() == 'alipay'){
                    $('.bank').removeClass('active');
                    $('.alipay').addClass('active');
                }else{
                    $('.alipay').removeClass('active');
                    $('.bank').addClass('active');
                }
                selects.slideToggle();
                $('.default-hock i').text($(this).text());
            });
        }
        cardTab();
        function Submit() {
            var sub = $('.submit');
            sub.on('click', function() {
                var payType = $('.payname').val();
                $('.error-txt').text('');
                var name = $('.card-name').val(),
                    numbers = $('.card-number').val(),
                    address = $('.card-address').val(),
                    alipayNumber = $('.alipay-number').val(),
                    money = $('.card-money').val();
                if(name == ''){
                    $('.error-txt').css('display', 'block');
                    $('.error-txt').text('姓名不能为空！');
                    return false;
                }
                if(payType =='alipay'){
                    if(alipayNumber == ''){
                        $('.error-txt').css('display', 'block');
                        $('.error-txt').text('支付宝账号不能为空');
                        return false;
                    }
                }else{
                    if(numbers == ''){
                        $('.error-txt').css('display', 'block');
                        $('.error-txt').text('卡号不能为空');
                        return false;
                    }
                    if(!(/^(\d{16}|\d{19})$/).test(numbers)){
                        $('.error-txt').css('display', 'block');
                        $('.error-txt').text('卡号位数不对');
                        return false;
                    }
                    if(address == ''){
                        $('.error-txt').css('display', 'block');
                        $('.error-txt').text('开户行不能为空');
                        return false;
                    }
                }
                if(money == ''){
                    $('.error-txt').css('display', 'block');
                    $('.error-txt').text('请填写金额');
                    return false;
                }
                var yue = parseFloat($('.balance i').text());
                if(parseFloat(money)>yue||parseFloat(money)<min_p){
                    $('.error-txt').css('display', 'block');
                    $('.error-txt').text('提现金额不能小于'+min_p+'并且不能大于所剩余额');
                    return false;
                }else{
                    $.post('{:Url("AuthApi/user_extract")}',
                        {
                            type: payType,
                            real_name: name,
                            alipay_code: alipayNumber,
                            bank_code: numbers,
                            bank_address: address,
                            price: money

                        },
                        function(data) {
                            if(data.code ==200){
                                $('.error-txt').empty();
                                $('.balance i').text(accSub(yue, money));
                                $('.card-money').val('');
                                alert('申请提现成功！');
                                window.location.href = '{:Url("user_pro")}';
                            }else if(data.code =400){
                                $('.error-txt').text(data.msg);
                                return false;
                            }
                        },"json");
                }

            });
        }
        Submit();

        // 小数减法
        function accSub(arg1, arg2) {
            var r1, r2, m, n;
            try { r1 = arg1.toString().split(".")[1].length } catch (e) { r1 = 0 }
            try { r2 = arg2.toString().split(".")[1].length } catch (e) { r2 = 0 }
            m = Math.pow(10, Math.max(r1, r2));
            n = (r1 >= r2) ? r1 : r2;
            return ((arg1 * m - arg2 * m) / m).toFixed(n);
        }


        // 验证小数
        $(document).on('keypress', '.card-money', function (e) {
            // 在 keypress 事件中拦截错误输入

            var sCharCode = String.fromCharCode(e.charCode);
            var sValue = this.value;

            if (/[^0-9.]/g.test(sCharCode) || __getRegex(sCharCode).test(sValue)) {
                return false;
            }

            /**
             * 根据用户输入的字符获取相关的正则表达式
             * @param  {string} sCharCode 用户输入的字符，如 'a'，'1'，'.' 等等
             * @return {regexp} patt 正则表达式
             */
            function __getRegex (sCharCode) {
                var patt;
                if (/[0]/g.test(sCharCode)) {
                    // 判断是否为空
                    patt = /^$/g;
                } else if (/[.]/g.test(sCharCode)) {
                    // 判断是否已经包含 . 字符或者为空
                    patt = /((\.)|(^$))/g;
                } else if (/[1-9]/g.test(sCharCode)) {
                    // 判断是否已经到达小数点后两位
                    patt = /\.\d{2}$/g;
                }
                return patt;
            }
        }).on('keyup paste', '#id', function () {
            // 在 keyup paste 事件中进行完整字符串检测

            var patt = /^((?!0)\d+(\.\d{1,2})?)$/g;

            if (!patt.test(this.value)) {
                // 错误提示相关代码，边框变红、气泡提示什么的
                console.log('输入格式不正确！');
            }
        });

    });
</script>
</body>
</html>