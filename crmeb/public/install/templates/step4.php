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
            <div class="progress-msg" v-if="!isShow">
                <div id="loginner_item" class="msg p8">{{installList[installList.length]}}</div>
                <!--                <div class="open" @click="openList">查看详情</div>-->
            </div>
        </div>
        <div class="install" ref="install" id="log" v-show="isShow">
            <div id="loginner" class="item" v-for="(item,index) in installList" :key="index">
                <span>{{item.msg}}</span>
                <span>{{item.time}}</span>
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
                return {percentage: 0, isShow: 0, installList: []}
            },
            mounted() {
                this.reloads(n);
            },
            methods: {
                reloads(n) {
                    var url = "<?php echo $_SERVER['PHP_SELF']; ?>?step=4&install=1&n=" + n;
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: 'json',
                        beforeSend: () => {
                        },
                        success: (msg) => {
                            this.percentage = Math.round((msg.n / msg.count) * 100) > 100 ? 100 : Math.round((msg.n / msg.count) * 100)
                            if (msg.n >= 0) {
                                $('#loginner_item').html(msg.msg);
                                this.installList.push({
                                    msg: msg.msg,
                                    time: msg.time
                                })
                                this.$nextTick(e => {
                                    this.$refs.install.scrollTop = this.$refs.install.scrollHeight;
                                })
                                if (msg.n == '999999') {
                                    setTimeout(e => {
                                        this.gonext()
                                    }, 1000);
                                    return false;
                                } else {
                                    this.reloads(msg.n);
                                }

                            } else {
                                //alert('指定的数据库不存在，系统也无法创建，请先通过其他方式建立好数据库！');
                                alert(msg.msg);
                            }

                        }
                    });
                },
                openList() {
                    this.isShow = true
                    this.$nextTick(e => {
                        this.$refs.install.scrollTop = this.$refs.install.scrollHeight;
                    })
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