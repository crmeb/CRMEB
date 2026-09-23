<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
    <link rel="stylesheet" href="./css/install.css?v=9.0"/>
    <script src="./js/jquery.js?v=9.0"></script>
    <link rel="stylesheet" href="./css/step4.css"/>
    <link rel="stylesheet" href="./css/theme-chalk.css">
    <script src="./js/vue2.6.11.js"></script>
    <script src="./js/element-ui.js?v=9.0"></script>
</head>
<body>
<div class="wrap" id="step4">
    <div class="title">
        安装进度
    </div>
    <!--  --><?php //require './templates/header.php';?>
    <section class="section">
        <div class="title">
            <h1>系统安装中，请稍等片刻...</h1>
        </div>
        <div class="progress">
            <el-progress :percentage="percentage" color="#37CA71" define-back-color="rgba(255,255,255,0.5)"
                         :stroke-width="8"
                         status="success"></el-progress>
            <div class="progress-msg">
                <div id="loginner_item" class="msg p8">{{currentMessage}}</div>
            </div>
        </div>
        <div class="bottom tac"><a href="javascript:;" class="btn_old mid"><img class="shuaxin" src="./images/install/shuaxin.png"
                                                                            align="absmiddle"/>&nbsp;正在安装...</a></div>
    </section>
    <script type="text/javascript">
        var n = -1;
        var data = <?php echo json_encode($_POST);?>;
        $.ajaxSetup({cache: false});

        new Vue({
            el: '#step4',
            data() {
                return {
                    percentage: 0,
                    currentMessage: '准备写入默认表结构...'
                }
            },
            mounted() {
                this.reloads(n, 'tables');
            },
            methods: {
                reloads(n, phase = 'tables') {
                    var url = "<?php echo $_SERVER['PHP_SELF']; ?>?step=4&install=1&n=" + n + "&phase=" + encodeURIComponent(phase);
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: 'json',
                        beforeSend: () => {
                        },
                        success: (msg) => {
                            const nextPhase = msg.phase || phase;
                            if (msg.n >= 0) {
                                const ratio = msg.count > 0 ? Math.min(msg.n / msg.count, 1) : 0;
                                if (msg.n == 999999) {
                                    this.percentage = 100;
                                } else if (nextPhase === 'plugins') {
                                    this.percentage = 80 + Math.round(ratio * 19);
                                } else if (nextPhase === 'data') {
                                    this.percentage = 60 + Math.round(ratio * 20);
                                } else {
                                    this.percentage = Math.round(ratio * 60);
                                }
                            }
                            if (msg.n >= 0) {
                                this.currentMessage = msg.msg;
                                if (msg.n == '999999') {
                                    setTimeout(e => {
                                        this.gonext()
                                    }, 1000);
                                    return false;
                                } else {
                                    this.reloads(msg.n, nextPhase);
                                }

                            } else {
                                alert(msg.msg);
                            }

                        }
                    });
                },
                gonext() {
                    window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?step=5';
                }
            }
        })
    </script>
</div>
<?php require './templates/footer.php'; ?>

</body>
</html>
