<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
    <link rel="stylesheet" href="./css/install.css?v=9.0"/>
    <link rel="stylesheet" href="./css/step2.css"/>
    <!-- 引入样式 -->
    <link rel="stylesheet" href="./css/theme-chalk.css">
    <!-- import Vue before Element -->
    <script src="./js/vue2.6.11.js"></script>
    <!-- import JavaScript -->
    <script src="./js/element-ui.js?v=9.0"></script>
</head>
<body>
<div class="wrap" id="step2">
    <!--    --><?php //require './templates/header.php'; ?>
    <div class="title">
        安装检测
    </div>
    <div class="content">
        <div class="menu">
            <div class="head">
                <h1>安装检测</h1>
                <a class="again" href="<?php echo $_SERVER['PHP_SELF']; ?>?step=2">重新检测
                    <img class="upload" src="./images/install/upload.png" alt="">
                </a>
            </div>
            <div class="p8">安装环境需满足系统运行要求</div>
            <div>
                <div class="tab" :class="{'on': index === 0}" @click="index = 0">
                    <div class="left-img">
                        <img class="env" src="./images/install/environment.png" alt="">
                        <img v-if="`<?php echo $passOne; ?>` == 'no'" class="warring"
                             src="./images/install/warring.png" alt="">
                        <img v-else class="warring" src="./images/install/sure.png" alt="">

                    </div>
                    <div>
                        <div>环境及配置</div>
                        <div class="p8">基础的系统操作环境</div>
                    </div>
                </div>
                <div class="tab" :class="{'on': index === 1}" @click="index = 1">
                    <div class="left-img">
                        <img class="jur" src="./images/install/jurisdiction.png" alt="">
                        <img v-if="`<?php echo $passTwo; ?>` == 'no'" class="warring btn-warning"
                             src="./images/install/warring.png" alt="">
                        <img v-else class="warring btn-warning" src="./images/install/sure.png" alt="">
                    </div>
                    <div>
                        <div>权限检测</div>
                        <div class="p8">目录及文件权限检测</div>
                    </div>
                </div>

            </div>
        </div>
        <section class="config-list">
            <!--        <div class="step">-->
            <!--            <ul>-->
            <!--                <li class="current"><em>1</em>检测环境</li>-->
            <!--                <li><em>2</em>创建数据</li>-->
            <!--                <li><em>3</em>完成安装</li>-->
            <!--            </ul>-->
            <!--        </div>-->
            <div class="server">
                <table width="100%" v-if="index === 0">
                    <tr>
                        <td class="td1">环境检测</td>
                        <td class="td1" width="25%">推荐配置</td>
                        <td class="td1" width="25%">最低要求</td>
                        <td class="td1" width="25%">当前状态</td>
                    </tr>
                    <tr>
                        <td>操作系统</td>
                        <td>类UNIX</td>
                        <td>不限制</td>
                        <td><div class="ls-td"><img class="yes" src="./images/install/yes.png" alt="对"><?php echo $os; ?></div></td>

                    </tr>
                    <tr>
                        <td>服务器环境</td>
                        <td>apache/nginx</td>
                        <td>apache2.0以上/nginx1.6以上</td>
                        <td><div class="ls-td"><img class="yes" src="./images/install/yes.png" alt="对"><?php echo $server; ?></div></td>

                    </tr>
                    <tr>
                        <td>PHP版本</td>
                        <td>><?php echo PHP_EDITION; ?></td>
                        <td><?php echo PHP_EDITION; ?>以上</td>
                        <td><div class="ls-td"><img class="yes" src="./images/install/yes.png" alt="对"><?php echo $phpv; ?></div></td>
                    </tr>
                    <tr>
                        <td>附件上传</td>
                        <td>>2M</td>
                        <td>不限制</td>
                        <td><div class="ls-td"><?php echo $uploadSize; ?></div></td>

                    </tr>
                    <tr>
                        <td>session</td>
                        <td>启用</td>
                        <td>启用</td>
                        <td><div class="ls-td"><?php echo $session; ?></div></td>

                    </tr>
                    <tr>
                        <td>safe_mode</td>
                        <td>基础配置</td>
                        <td>启用</td>
                        <td><div class="ls-td"><?php echo $safe_mode; ?></div></td>

                    </tr>
                    <tr>
                        <td>GD库</td>
                        <td>必须开启</td>
                        <td>1.0以上</td>
                        <td><div class="ls-td"><?php echo $gd; ?></div></td>

                    </tr>
                    <tr>
                        <td>mysqli</td>
                        <td>必须开启</td>
                        <td>启用</td>
                        <td><div class="ls-td"><?php echo $mysql; ?></div></td>

                    </tr>
                    <tr>
                        <td>curl_init</td>
                        <td>必须扩展</td>
                        <td>启用</td>
                        <td><div class="ls-td"><?php echo $curl; ?></div></td>

                    </tr>
                    <tr>
                        <td>bcmath</td>
                        <td>必须扩展</td>
                        <td>启用</td>
                        <td><div class="ls-td"><?php echo $bcmath; ?></div></td>
                    </tr>
                    <tr>
                        <td>openssl</td>
                        <td>必须扩展</td>
                        <td>启用</td>
                        <td><div class="ls-td"><?php echo $openssl; ?></div></td>
                    </tr>
                </table>


                <table width="100%" v-else>
                    <tr>
                        <td class="td1">权限检查</td>
                        <td class="td1" width="25%">推荐配置</td>
                        <td class="td1" width="25%">写入</td>
                        <td class="td1" width="25%">读取</td>
                    </tr>
                    <?php
                    foreach ($folder as $dir) {
                        $Testdir = APP_DIR . $dir;
                        if (!is_file($Testdir)) {
                            if (!is_dir($Testdir)) {
                                dir_create($Testdir);
                            }
                        }

                        if (testwrite($Testdir)) {
                            $w = '<img class="yes" src="./images/install/yes.png" alt="对">可写 ';
                        } else {
                            $w = '<img class="no" src="./images/install/warring.png" alt="错">不可写 ';
                        }


                        if (is_readable($Testdir)) {
                            $r = '<img class="yes" src="./images/install/yes.png" alt="对">可读';
                        } else {
                            $r = '<img class="no" src="./images/install/warring.png" alt="错">不可读';
                        }
                        ?>
                        <tr>
                            <td><?php echo $dir; ?></td>
                            <td>读写</td>
                            <td>
                                <div class="ls-td"><?php echo $w; ?></div>
                            </td>
                            <td>
                                <div class="ls-td"><?php echo $r; ?></div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <?php
                    foreach ($file as $filename) {
                        $filedir = APP_DIR . $filename;
                        if (is_writeable($filedir)) {
                            $w = '<img class="yes" src="./images/install/yes.png" alt="对">可写 ';
                        } else {
                            $w = '<img class="no" src="./images/install/warring.png" alt="错">不可写 ';
                        }
                        if (is_readable($filedir)) {
                            $r = '<img class="yes" src="./images/install/yes.png" alt="对">可读';
                        } else {
                            $r = '<img class="no" src="./images/install/warring.png" alt="错">不可读';
                        }
                        ?>

                        <tr>
                            <td><?php echo $filename; ?></td>
                            <td>读写</td>
                            <td>
                                <div class="ls-td"><?php echo $w; ?></div>
                            </td>
                            <td>
                                <div class="ls-td"><?php echo $r; ?></div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>


                </table>
                <!--            <table width="100%">-->
                <!--                <tr>-->
                <!--                  <td class="td1" width="25%">函数检测必须开启</td>-->
                <!--                  <td class="td1" width="25%">当前状态</td>-->
                <!--                  <td class="td1" width="25%">函数检测必须开启</td>-->
                <!--                  <td class="td1" width="25%">当前状态</td>-->
                <!--                </tr>-->
                <!--              <tr>-->
                <!--                    <td>file_put_contents</td>-->
                <!--                    <td>--><?php //echo $file_put_contents; ?><!--</td>-->
                <!--                <td>imagettftext</td>-->
                <!--                <td>--><?php //echo $imagettftext; ?><!--</td>-->
                <!--                </tr>-->
                <!--                <tr>-->
                <!--                    <td>proc_open</td>-->
                <!--                    <td>--><?php //echo $proc_open; ?><!--</td>-->
                <!--                    <td>pcntl_signal</td>-->
                <!--                    <td>--><?php //echo $pcntl_signal; ?><!--</td>-->
                <!--                </tr>-->
                <!--                <tr>-->
                <!--                    <td>pcntl_signal_dispatch</td>-->
                <!--                    <td>--><?php //echo $pcntl_signal_dispatch; ?><!--</td>-->
                <!--                    <td>pcntl_fork</td>-->
                <!--                    <td>--><?php //echo $pcntl_fork; ?><!--</td>-->
                <!--                </tr>-->
                <!--                <tr>-->
                <!--                    <td>pcntl_wait</td>-->
                <!--                    <td>--><?php //echo $pcntl_wait; ?><!--</td>-->
                <!--                    <td>pcntl_alarm</td>-->
                <!--                    <td>--><?php //echo $pcntl_alarm; ?><!--</td>-->
                <!--                </tr>-->
                <!--            </table>-->
            </div>

        </section>
    </div>
    <div class="trip mid">
        <img src="./images/install/trip-icon.png" alt="">
        温馨提示：程序运行需配置伪静态，否则安装后会存在无法使用的情况
    </div>
    <div class="bottom-btn">
        <div class="bottom tac up-btn">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=1" class="btn">上一步</a>
        </div>
        <div class="bottom tac">
            <?php if ($passOne == 'no' || $passTwo == 'no') { ?>
                <span class="next" @click="next" class="btn">下一步</span>
            <?php } else { ?>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=3" class="btn next">下一步</a>
            <?php } ?>
        </div>
    </div>

</div>
<?php require './templates/footer.php'; ?>
</body>
<script>
    new Vue({
        el: '#step2',
        data() {
            return {index: 0}
        },
        methods: {
            next() {
                this.$message({
                    message: '安装环境检测未通过，请检查',
                    type: 'warning'
                });
            }
        }
    })
</script>
</html>
