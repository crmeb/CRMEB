<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
    <link rel="stylesheet" href="./css/install.css?v=9.0"/>
</head>
<body>
<div class="wrap">
    <?php require './templates/header.php'; ?>
    <section class="section">
        <div class="step">
            <ul>
                <li class="on"><em>1</em>检测环境</li>
                <li class="current"><em>2</em>创建数据</li>
                <li><em>3</em>完成安装</li>
            </ul>
        </div>
        <form id="J_install_form" action="index.php?step=4" method="post">
            <input type="hidden" name="force" value="0"/>
            <div class="server">
                <table width="100%">
                    <tr>
                        <td class="td1" width="100">数据库信息</td>
                        <td class="td1" width="200">&nbsp;</td>
                        <td class="td1">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tar">数据库服务器：</td>
                        <td><input type="text" name="dbhost" id="dbhost" value="127.0.0.1" class="input"></td>
                        <td>
                            <div id="J_install_tip_dbhost"><span class="gray">数据库服务器地址，一般为127.0.0.1</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库端口：</td>
                        <td><input type="text" name="dbport" id="dbport" value="3306" class="input"></td>
                        <td>
                            <div id="J_install_tip_dbport"><span class="gray">数据库服务器端口，一般为3306</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库用户名：</td>
                        <td><input type="text" name="dbuser" id="dbuser" value="root" class="input"></td>
                        <td>
                            <div id="J_install_tip_dbuser"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库密码：</td>
                        <td><input type="password" name="dbpw" id="dbpw" value="" class="input" autoComplete="off" onBlur="mysqlDbPwd(0)"></td>
                        <td>
                            <div id="J_install_tip_dbpw"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库名：</td>
                        <td><input type="text" name="dbname" id="dbname" value="crmeb" class="input" onBlur="mysqlDbPwd(0)"></td>
                        <td>
                            <div id="J_install_tip_dbname"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库表前缀：</td>
                        <td><input type="text" name="dbprefix" id="dbprefix" value="eb_" class="input"></td>
                        <td>
                            <div id="J_install_tip_dbprefix"><span class="gray">建议使用默认，同一数据库安装多个CRMEB时需修改</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">演示数据：</td>
                        <td colspan="2"><input style="width:18px;height:18px;" type="checkbox" id="demo" name="demo" value="demo" checked></td>
                    </tr>
                </table>
                <table width="100%">
                    <tr>
                        <td class="td1" width="100">redis数据库信息</td>
                        <td class="td1" width="200">&nbsp;</td>
                        <td class="td1">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tar">服务器地址：</td>
                        <td><input type="text" name="rbhost" id="rbhost" value="127.0.0.1" class="input" onBlur="redisDbPwd(0)"></td>
                        <td>
                            <div id="J_install_redis_host"><span class="gray">redis服务器地址，一般为127.0.0.1</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">端口号：</td>
                        <td><input type="text" name="rbport" id="rbport" value="6379" class="input" autoComplete="off" onBlur="redisDbPwd(0)">
                        </td>
                        <td>
                            <div id="J_install_redis_port"><span class="gray">redis端口,默认为6379</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库：</td>
                        <td><input type="text" name="rbselect" id="rbselect" value="0" class="input" autoComplete="off"  onBlur="redisDbPwd(0)"></td>
                        <td>
                            <div id="J_install_redis_select"><span class="gray">redis数据库，默认为0,一般不做更改</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库密码：</td>
                        <td><input type="text" name="rbpw" id="rbpw" value="" class="input" autoComplete="off"  onBlur="redisDbPwd(0)"></td>
                        <td>
                            <div id="J_install_redis_dbpw"><span class="gray">redis数据库密码</span></div>
                        </td>
                    </tr>

                </table>
                <table width="100%">
                    <tr>
                        <td class="td1" width="100">管理员信息</td>
                        <td class="td1" width="200">&nbsp;</td>
                        <td class="td1">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tar">管理员帐号：</td>
                        <td><input type="text" name="manager" id="manager" value="admin" class="input" onblur="checkForm()"></td>
                        <td>
                            <div id="J_install_tip_manager"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">管理员密码：</td>
                        <td><input type="password" name="manager_pwd" id="manager_pwd" class="input" autoComplete="off"  onblur="checkForm()">
                        </td>
                        <td>
                            <div id="J_install_tip_manager_pwd"><span class="gray">请输入至少6个字符密码</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">重复密码：</td>
                        <td><input type="password" name="manager_ckpwd" id="manager_ckpwd" class="input" autoComplete="off"  onkeyup="checkForm()"></td>
                        <td>
                            <div id="J_install_tip_manager_ckpwd"></div>
                        </td>
                    </tr>

                </table>
                <div id="J_response_tips" style="display:none;"></div>
            </div>
            <div class="bottom tac"><a href="./index.php?step=2" class="btn">上一步</a>
                <button type="button" onClick="submitForm();" class="btn btn_submit J_install_btn">创建数据</button>
            </div>
        </form>
    </section>
    <div style="width:0;height:0;overflow:hidden;"><img src="./images/install/pop_loading.gif"></div>
    <script src="./js/jquery.js?v=9.0"></script>
    <script src="./js/validate.js?v=9.0"></script>
    <script src="./js/ajaxForm.js?v=9.0"></script>
    <script>
        var mysqlstatu = false;
        var redisstatu = false;
        function mysqlDbPwd(connect_db) {
            var data = {
                'dbHost': $('#dbhost').val(),
                'dbUser': $('#dbuser').val(),
                'dbPwd': $('#dbpw').val(),
                'dbName': $('#dbname').val(),
                'dbport': $('#dbport').val(),
                'demo': $('#demo').val()
            };
            var url = "<?php echo $_SERVER['PHP_SELF']; ?>?step=3&mysqldbpwd=1";
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'JSON',
                beforeSend: function () {
                },
                success: function (msg) {
                    if (msg == 1) {
                        // $('#J_install_tip_dbpw').html('<span generated="true" class="tips_success" style="">密码正确</span>');
                        $('#J_install_tip_dbpw').html('');
                        $('#J_install_tip_dbname').html('');
                        $('#J_install_rbhost').html('');
                        mysqlstatu = true;
                    } else if (msg == -1) {
                        // $('#dbpw').val("");
                        $('#J_install_tip_dbpw').html('<span for="dbname" generated="true" class="tips_error" >数据库链接配置失败</span>');
                        mysqlstatu = false;
                    } else if (msg == -2) {
                        $('#J_install_tip_dbpw').html('<span for="dbname" generated="true" class="tips_error" >请在mysql配置文件修sql-mode或sql_mode为NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION</span><a href="https://doc.crmeb.com/web/single/crmeb_v4/936" target="_blank">查看文档</a>');
                        mysqlstatu = false;
                    } else if (msg == -3) {
                        $('#J_install_tip_dbpw').html('');
                        $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >你的不是空数据库, 请更换一个数据库名字</span>');
                        mysqlstatu = false;
                    }else if (msg == -4) {
                        $('#J_install_tip_dbpw').html('');
                        $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >无权限创建数据，请先手动创建数据库</span>');
                        mysqlstatu = false;
                    } else if (msg == 2002) {
                        $('#J_install_tip_dbpw').html('<span for="dbname" generated="true" class="tips_error" >地址或端口错误</span>');
                        mysqlstatu = false;
                    } else if (msg == 1045) {
                        $('#J_install_tip_dbpw').html('<span for="dbname" generated="true" class="tips_error" >用户名或密码错误</span>');
                        mysqlstatu = false;
                    }  else {
                        $('#J_install_tip_dbpw').html('');
                        mysqlstatu = false;
                    }
                },
                complete: function () {
                },
                error: function () {
                    $('#J_install_tip_dbpw').html('<span for="dbname" generated="true" class="tips_error" style="">数据库链接配置失败</span>');
                    mysqlstatu = false;
                }
            });
        }
        function redisDbPwd(connect_db) {
            var data = {
                rbhost: $('#rbhost').val(),
                rbport: $("#rbport").val(),
                rbselect: $("#rbselect").val(),
                rbpw: $('#rbpw').val(),
            };
            var url = "<?php echo $_SERVER['PHP_SELF']; ?>?step=3&redisdbpwd=1";
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'JSON',
                beforeSend: function () {
                },
                success: function (msg) {
                    if (msg == 1) {
                        // $('#J_install_redis_host').html('<span generated="true" class="tips_success" style="">地址配置正确</span>');
                        // $('#J_install_redis_port').html('<span generated="true" class="tips_success" style="">端口配置正确</span>');
                        // $('#J_install_redis_select').html('<span generated="true" class="tips_success" style="">库可用</span>');
                        $('#J_install_redis_dbpw').html('<span generated="true" class="tips_success" style="">配置成功</span>');
                        redisstatu = true;
                    } else if (msg == -1) {
                        $('#J_install_redis_host').html('<span for="dbname" generated="true" class="tips_error" style="">Redis扩展没有安装</span>');
                        redisstatu = false;
                    } else if (msg == -3) {
                        $('#J_install_redis_dbpw').html('<span for="dbname" generated="true" class="tips_error" style="">Redis数据库没有启动或者配置信息错误</span>');
                        redisstatu = false;
                    } else {
                        $('#J_install_redis_dbpw').html('<span for="dbname" generated="true" class="tips_error" style="">配置失败</span>');
                        redisstatu = false;
                    }
                },
                complete: function () {
                },
                error: function () {
                    $('#J_install_redis_dbpw').html('<span for="dbname" generated="true" class="tips_error" style="">数据库链接配置失败</span>');
                    $('#dbpw').val("");
                    redisstatu = false;
                }
            });
        }
        function checkForm() {
            manager = $.trim($('#manager').val());				//用户名表单
            manager_pwd = $.trim($('#manager_pwd').val());				//密码表单
            manager_ckpwd = $.trim($('#manager_ckpwd').val());		//密码提示区

            if (manager.length == 0) {
                $('#J_install_tip_manager').html('<span for="dbname" generated="true" class="tips_error" style="">请输入管理账号</span>');
                return false;
            }
            if (!(/^[a-zA-Z]{0,}$/.test(manager))) {
                // alert('账号必须为英文或者数字');
                $('#J_install_tip_manager').html('<span generated="true" class="tips_error" style="">账号必须为英文或者数字</span>');
                return false;
            } else {
                $('#J_install_tip_manager').html('<span generated="true" class="tips_success" style="">用户名可用</span>');
            }
            if (manager_pwd.length < 6) {
                // alert('管理员密码必须6位数以上');
                $('#J_install_tip_manager_pwd').html('<span for="dbname" generated="true" class="tips_error" style="">管理员密码必须5位数以上</span>');
                return false;
            } else {
                $('#J_install_tip_manager_pwd').html('<span generated="true" class="tips_success" style="">密码可用</span>');
            }
            if (manager_ckpwd != manager_pwd) {
                // alert('两次密码不一致');
                $('#J_install_tip_manager_ckpwd').html('<span for="dbname" generated="true" class="tips_error" style="">两次密码不一致</span>');
                return false;
            } else {
                $('#J_install_tip_manager_ckpwd').html('<span generated="true" class="tips_success" style="">密码正确</span>');
                return true;
            }


        }
        function submitForm() {
            if(!mysqlstatu) {
                mysqlDbPwd(1);
                return false;
            }
            if(!redisstatu) {
                redisDbPwd(1);
                return false;
            }
            if(checkForm()) {
                $("#J_install_form").submit(); // ajax 验证通过后再提交表单
            }
        }
    </script>
</div>
<?php require './templates/footer.php'; ?>
</body>
</html>
