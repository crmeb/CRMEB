<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
    <link rel="stylesheet" href="./css/install.css?v=9.0"/>
    <link rel="stylesheet" href="./css/step3.css"/>
    <!-- 引入样式 -->
    <link rel="stylesheet" href="./css/theme-chalk.css">
    <!-- import Vue before Element -->
    <script src="./js/vue2.6.11.js"></script>
    <!-- import JavaScript -->
    <script src="./js/element-ui.js?v=9.0"></script>
</head>
<body>
<div class="wrap" id="step3">
    <div class="title">
        创建数据
    </div>
    <section class="section">
        <form id="J_install_form" action="index.php?step=4" method="post">
            <div class="server"  ref="mianscroll">
                <table width="100%">
                    <tr>
                        <td class="td1" width="100">数据库信息</td>
                        <td class="td1" width="200">&nbsp;</td>
                        <td class="td1">&nbsp;</td>
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
                        <td><input type="password" name="dbpw" id="dbpw" value="" class="input" autoComplete="off"></td>
                        <td>
                            <div id="J_install_tip_dbpw"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">数据库名：</td>
                        <td><input type="text" name="dbname" id="dbname" value="crmeb" class="input"></td>
                        <td>
                            <div id="J_install_tip_dbname"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">高级设置：</td>
                        <td colspan="2">
                            <el-switch
                                    v-model="value"
                                    active-color="#37CA71"
                                    inactive-color="#575869">
                            </el-switch>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr v-show="value">
                        <td class="tar">数据库服务器：</td>
                        <td><input type="text" name="dbhost" id="dbhost" value="127.0.0.1" class="input"></td>
                        <td>
                            <div id="J_install_tip_dbhost"></div>
                        </td>
                    </tr>
                    <tr v-show="value">
                        <td class="tar">数据库端口：</td>
                        <td><input type="text" name="dbport" id="dbport" value="3306" class="input"
                                   onBlur="mysqlDbPwd(0)"></td>
                        <td>
                            <div id="J_install_tip_dbport"></div>
                        </td>
                    </tr>

                    <tr v-show="value">
                        <td class="tar">数据库表前缀：</td>
                        <td><input type="text" name="dbprefix" id="dbprefix" value="eb_" class="input"></td>
                        <td></td>
                    </tr>
                    <tr v-show="value">
                        <td class="tar">演示数据：</td>
                        <td colspan="2"><input style="width:14px;height:14px;" type="checkbox" id="demo" name="demo"
                                               value="demo" checked></td>
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
                        <td><input type="text" name="manager" id="manager" value="admin" class="input"
                                   onblur="checkForm()"></td>
                        <td>
                            <div id="J_install_tip_manager"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">管理员密码：</td>
                        <td><input type="password" name="manager_pwd" id="manager_pwd" class="input" autoComplete="off"
                                placeholder="请输入密码(至少6个字符)"  placeholder-class="pl-style" onblur="checkForm()">
                        </td>
                        <td>
                            <div id="J_install_tip_manager_pwd"><span class="gray">请输入至少6个字符密码</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">重复密码：</td>
                        <td><input type="password" name="manager_ckpwd" id="manager_ckpwd" class="input"
                                   autoComplete="off" placeholder="请再次输入密码" onkeyup="checkForm()"></td>
                        <td>
                            <div id="J_install_tip_manager_ckpwd"></div>
                        </td>
                    </tr>

                </table>
                <table>
                    <tr>
                        <td class="td1" width="100">缓存设置</td>
                        <td class="td1" width="200">&nbsp;</td>
                        <td class="td1">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tar">缓存方式：</td>
                        <td>
                            <el-radio v-model="radio" :label="0" name="cache_type" id="cache_type1">文件缓存</el-radio>
                            <el-radio v-model="radio" :label="1" name="cache_type" id="cache_type2">redis缓存</el-radio>
                        </td>
                        <td></td>
                    </tr>
                    <tr v-show="radio == 1">
                        <td class="tar">服务器地址：</td>
                        <td><input type="text" name="rbhost" id="rbhost" value="127.0.0.1" class="input"></td>
                        <td>
                            <div id="J_install_redis_host"><span class="gray">redis服务器地址，一般为127.0.0.1</span></div>
                        </td>
                    </tr>
                    <tr v-show="radio == 1">
                        <td class="tar">端口号：</td>
                        <td><input type="text" name="rbport" id="rbport" value="6379" class="input" autoComplete="off">
                        </td>
                        <td>
                            <div id="J_install_redis_port"><span class="gray">redis端口,默认为6379</span></div>
                        </td>
                    </tr>
                    <tr v-show="radio == 1">
                        <td class="tar">数据库：</td>
                        <td><input type="text" name="rbselect" id="rbselect" value="0" class="input" autoComplete="off">
                        </td>
                        <td>
                            <div id="J_install_redis_select"><span class="gray">redis数据库，默认为0,一般不做更改</span></div>
                        </td>
                    </tr>
                    <tr v-show="radio == 1" id="scrollBtn">
                        <td class="tar">数据库密码：</td>
                        <td><input type="text" name="rbpw" id="rbpw" value="" class="input" autoComplete="off"></td>
                        <td>
                            <div id="J_install_redis_dbpw"><span class="gray">redis数据库密码</span></div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="bottom-btn">
                <div class="bottom tac up-btn">
                    <a href="./index.php?step=2" class="btn">上一步</a>
                </div>
                <div class="bottom tac next">
                    <a @click="submitForm();" class="btn">下一步</a>
                </div>
            </div>
        </form>
    </section>
    <div style="width:0;height:0;overflow:hidden;"><img src="./images/install/pop_loading.gif"></div>
    <script src="./js/jquery.js?v=9.0"></script>
    <script src="./js/validate.js?v=9.0"></script>
    <script src="./js/ajaxForm.js?v=9.0"></script>
    <script>
        //验证管理员信息
        function checkForm() {
            let manager = $.trim($('#manager').val());				//用户名表单
            let manager_pwd = $.trim($('#manager_pwd').val());				//密码表单
            let manager_ckpwd = $.trim($('#manager_ckpwd').val());		//密码提示区
            if (manager.length == 0) {
                $('#J_install_tip_manager').html('<span for="dbname" generated="true" class="tips_error" style="">请输入管理账号</span>');
                return false;
            }
            if (!(/^[a-zA-Z0-9]{0,32}$/.test(manager))) {
                $('#J_install_tip_manager').html('<span generated="true" class="tips_error" style="">账号必须为英文或者数字</span>');
                return false;
            } else {
                $('#J_install_tip_manager').html('<span generated="true" class="tips_success" style="">用户名可用</span>');
            }
            if (manager_pwd.length < 6) {
                $('#J_install_tip_manager_pwd').html('<span for="dbname" generated="true" class="tips_error" style="">管理员密码必须5位数以上</span>');
                return false;
            } else {
                $('#J_install_tip_manager_pwd').html('<span generated="true" class="tips_success" style="">密码可用</span>');
            }
            if (manager_ckpwd != manager_pwd) {
                $('#J_install_tip_manager_ckpwd').html('<span for="dbname" generated="true" class="tips_error" style="">两次密码不一致</span>');
                return false;
            } else {
                $('#J_install_tip_manager_ckpwd').html('<span generated="true" class="tips_success" style="">密码正确</span>');
            }
            return true;
        }
        new Vue({
            el: '#step3',
            data() {
                return {value: false, radio: 0}
            },
            created() {

            },
            methods: {
                mysqlDbPwd() {
                    let data = {
                        'dbHost': $('#dbhost').val(),
                        'dbUser': $('#dbuser').val(),
                        'dbPwd': $('#dbpw').val(),
                        'dbName': $('#dbname').val(),
                        'dbport': $('#dbport').val(),
                        'demo': $('#demo').val()
                    };
                    let url = "<?php echo $_SERVER['PHP_SELF']; ?>?step=3&mysqldbpwd=1";
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: data,
                            dataType: 'JSON',
                            success: (msg) => {
                                resolve(msg);
                            },
                            error: (err) => {
                                reject(err)
                            }
                        });
                    })

                },

                redisDbPwd() {
                    let data = {
                        rbhost: $('#rbhost').val(),
                        rbport: $("#rbport").val(),
                        rbselect: $("#rbselect").val(),
                        rbpw: $('#rbpw').val(),
                    };
                    let url = "<?php echo $_SERVER['PHP_SELF']; ?>?step=3&redisdbpwd=1";
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: data,
                            dataType: 'JSON',
                            success: function (msg) {
                                resolve(msg)
                            },
                            error: function () {
                                reject()
                            }
                        });
                    })

                },


                jumpButton(){
                   this.$refs.mianscroll.scrollTop = this.$refs.mianscroll.clientHeight
                },
                submitForm() {
                    this.mysqlDbPwd().then(res => {
                        if (res == 2002) {
                            this.value = true
                            $('#J_install_tip_dbhost').html('<span for="dbname" generated="true" class="tips_error" >地址或端口错误</span>');
                            $('#J_install_tip_dbport').html('<span for="dbname" generated="true" class="tips_error" >地址或端口错误</span>');
                            return false;
                        } else if (res == -1) {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >数据库链接配置失败</span>');
                            return false;
                        } else if (res == -2) {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >请在mysql配置文件修sql-mode或sql_mode为NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION</span><a href="https://doc.crmeb.com/web/single/crmeb_v4/936" target="_blank">查看文档</a>');
                            return false;
                        } else if (res == 1045) {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbname').html('');
                            $('#J_install_tip_dbuser').html('<span for="dbname" generated="true" class="tips_error" >用户名或密码错误</span>');
                            $('#J_install_tip_dbpw').html('<span for="dbname" generated="true" class="tips_error" >用户名或密码错误</span>');
                            return false;
                        } else if (res == -4) {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbuser').html('');
                            $('#J_install_tip_dbpw').html('');
                            $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >无权限创建数据，请先手动创建数据库</span>');
                            return false;
                        } else if (res == -3) {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbuser').html('');
                            $('#J_install_tip_dbpw').html('');
                            $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >数据库不为空，请更换一个数据库</span>');
                            return false;
                        } else if (res == -5) {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbuser').html('');
                            $('#J_install_tip_dbpw').html('');
                            $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >MySql数据库必须是5.6及以上版本</span>');
                            return false;
                        } else if (res == 1) {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbuser').html('');
                            $('#J_install_tip_dbpw').html('');
                            $('#J_install_tip_dbname').html('<span generated="true" class="tips_success" style="">数据库配置成功</span>');
                        } else {
                            $('#J_install_tip_dbhost').html('');
                            $('#J_install_tip_dbport').html('');
                            $('#J_install_tip_dbuser').html('');
                            $('#J_install_tip_dbpw').html('');
                            $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >未知错误</span>');
                            return false;
                        }
                        let redisStatus = $("input[name='cache_type']:checked").val();
                        if (redisStatus == 1) {
                            this.redisDbPwd().then(msg => {
                                if (msg == -1) {
                                    $('#J_install_redis_host').html('<span for="dbname" generated="true" class="tips_error" style="">Redis扩展没有安装</span>');
                                    this.$nextTick(() => {this.jumpButton()});
                                    return false;
                                } else if (msg == -3) {
                                    $('#J_install_redis_host').html('');
                                    $('#J_install_redis_dbpw').html('<span for="dbname" generated="true" class="tips_error" style="">Redis数据库没有启动或者配置错误</span>');
                                    this.$nextTick(() => {this.jumpButton()});

                                    return false;
                                } else if (msg == 1) {
                                    $('#J_install_redis_host').html('');
                                    $('#J_install_redis_dbpw').html('<span generated="true" class="tips_success" style="">Redis配置成功</span>');
                                } else {
                                    $('#J_install_redis_host').html('');
                                    $('#J_install_redis_dbpw').html('<span for="dbname" generated="true" class="tips_error" style="">Redis配置失败</span>');
                                    this.$nextTick(() => {this.jumpButton()});
                                    return false;
                                }
                                if (checkForm()) {
                                    $("#J_install_form").submit(); // ajax 验证通过后再提交表单
                                }
                            }).catch(err => {
                                $('#J_install_redis_host').html('');
                                $('#J_install_redis_dbpw').html('<span for="dbname" generated="true" class="tips_error" >未知错误</span>');
                                this.$nextTick(() => {this.jumpButton()});
                                return false;
                            })
                        } else {
                            if (checkForm()) {
                                $("#J_install_form").submit(); // ajax 验证通过后再提交表单
                            }
                        }
                    }).catch(err => {
                        $('#J_install_tip_dbhost').html('');
                        $('#J_install_tip_dbport').html('');
                        $('#J_install_tip_dbuser').html('');
                        $('#J_install_tip_dbpw').html('');
                        $('#J_install_tip_dbname').html('<span for="dbname" generated="true" class="tips_error" >未知错误1</span>');
                        return false;
                    })
                }
            }
        })


    </script>
</div>
<?php require './templates/footer.php'; ?>
</body>
</html>
