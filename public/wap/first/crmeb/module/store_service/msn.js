    var page=1; //分页
    var moban_time=0; //全局时间戳
    var bq_h=".png"; //表情格式后缀
    var load_over = false; //是否加载完聊天记录

    /******************全局方法*********************/
    //提示框弹出
    function ts(html){
        //获取垂直居中的px
        $(".prompt p").html(html)
        $(".prompt").fadeIn();
    }

    //提示框关闭
    function ts_no(){
        $(".prompt").fadeOut();
    }

    //ajax获取当前指定页聊天记录
    function ajax_get_list(page){
        ts("正在拉取<b>"+to_unickname+"</b>的记录");
        var query = new Object();
        query.uid = uid;
        query.to_uid = to_uid;
        query.page = page;
        query.mer_id = mer_id;
        $.ajax({
             type:"post",
             url:"/wap/auth_api/get_msn",
             data:query,
             dataType:"json",
             async:true,
             success: function(data){
                if(data.code == 200 && data.data.length > 0){
                    //取原高度
                    jili=$(".kj").height();

                    html = moban_msn(data.data);
                    $(".duihua").prepend(html);

                    //更新滚动距离
                    conter(jili);
                }else{
                    load_over = true;
                }
                //关闭提示框
                ts_no();
            }
        });
    }

    //发送添加消息
    function add_msn(msn,type){
        var query = new Object();
        query.uid = uid;
        query.to_uid = to_uid;
        query.msn = msn;
        query.type = type;
        query.mer_id = mer_id;
        $.ajax({
             type:"post",
             url:"/wap/auth_api/add_msn",
             data:query,
             dataType:"json",
             async:true,
             success: function(data){
                //合成我的消息
                if(type == "html")
                    json_msn(msn);
                else
                    json_msn($('<div>').text(msn).html());
             }
        });
    }

    //页面刷新消息显示
    function refresh_msn(){
        var query = new Object();
        query.uid = uid;
        query.to_uid = to_uid;
        query.mer_id = mer_id;
        $.ajax({
             type:"post",
             url:"/wap/auth_api/refresh_msn",
             data:query,
             dataType:"json",
             async:true,
             success: function(data){
                if(data.code == 200 && data.data.length > 0){
                    html = moban_msn(data.data);
                    $(".duihua").append(html);
                    bottom();
                }
            }
        });
    }

    //聊天显示时间处理
    function timedate(sdate,type){
        //取今天0点时间戳
        today = new Date();
        today.setHours(0);
        today.setMinutes(0);
        today.setSeconds(0);
        today.setMilliseconds(0);
        today=Date.parse(today);
        //时间戳计算
        var oneday = 1000 * 60 * 60 * 24;
        //取昨天0点
        tooday=today-oneday;
        //取前天0点
        toooday=today-oneday*2;
        //取7天0点
        tooooday=today-oneday*7;
        //js的时间戳和php的时间戳少4位
        sdate=parseInt(sdate)*1000;
        d=new Date(sdate);
        if(sdate > today){//判断是否在今天内
            res = moment(d).format('ah:mm');
        }

        if(sdate < today){//判断是否是昨天
            res = moment(d).format('昨天 ah:mm');
            if(type == 1)
                res="昨天";
        }

        if(sdate < tooday){//判断是否昨天前 显示前天
            res = moment(d).format('前天 ah:mm');
            if(type == 1)
                res="前天";
        }

        if(sdate < toooday){//判断是否前天前 显示星期
            res = moment(d).format('dddd ah:mm');
            if(type == 1)
                res=moment(d).format('dddd');
        }

        if(sdate < tooooday){//判断是否7天前
            res = moment(d).format('YYYY年M月D日 ah:mm');
            if(type == 1)
                res=moment(d).format('YY/M/D');
        }
        res =res.replace("am", "上午");
        res =res.replace("pm", "下午");
        res =res.replace("Sunday", "星期天");
        res =res.replace("Saturday", "星期六");
        res =res.replace("Monday", "星期一");
        res =res.replace("Tuesday", "星期二");
        res =res.replace("Wednesday", "星期三");
        res =res.replace("Thursday", "星期四");
        res =res.replace("Friday", "星期五");
        return res;
    }
    /******************全局方法*********************/


    //点击发送消息
    function msn_add(){
        if($("#msn").val() != ""){
            //取消息
            msn=$("#msn").val();

            //编辑框清空
            $("#msn").val("");

            //添加信息
            add_msn(msn,"auto");
        }
    }

    //合成我的消息json
    function json_msn(msn){
        msn=msn.replace(/(^\s*)|(\s*$)/g,"");
        if(msn !== ""){
            var obj = new Object();
            obj.my = "my";
            obj.msn = msn;
            obj.add_time = moment().format('X');
            data = "["+JSON.stringify(obj)+"]";
            // data='[{"my":"my","msn":"'+msn+'","add_time":"'+moment().format('X')+'"}]';
            html = moban_msn(JSON.parse(data));
            $(".duihua").append(html);
            bottom();
        }
    }

    //表情替换模版
    function moban_bq(name){
        jc=0;
        var moj = new Array();
		moj['阿'] = 1;
		moj['唉'] = 2;
		moj['拜拜'] = 3;
		moj['板砖拍你'] = 4;
		moj['暴走'] = 5;
		moj['悲伤'] = 6;
		moj['鼻出血'] = 7;
		moj['别逼我'] = 8;
		moj['不高兴'] = 9;
		moj['崇拜'] = 10;
		moj['出发'] = 11;
		moj['打你'] = 12;
		moj['打肿'] = 13 ;
		moj['大哭'] = 14 ;
		moj['大喷血'] = 15;
		moj['大笑'] =16 ;
		moj['发呆'] =17 ;
		moj['愤怒'] = 18;
		moj['感觉真好'] = 19;
		moj['哈哈'] = 20;
		moj['害羞'] = 21;
		moj['好困'] = 22;
		moj['好冷'] = 23;
		moj['好美'] =24 ;
		moj['好色'] = 25;
		moj['黑客'] =26 ;
		moj['怀疑'] =27 ;
		moj['坏淫'] =28 ;
		moj['惊讶'] = 29;
		moj['可爱'] = 30;
		moj['可怜'] =31 ;
		moj['老大'] =32 ;
		moj['流口水'] = 33;
		moj['妈呀'] =34 ;
		moj['你懂得'] =35 ;
		moj['牛B'] = 36;
		moj['亲亲'] =37 ;
		moj['确定'] = 38;
		moj['塞饭'] = 39;
		moj['神阿'] = 40;
		moj['生气'] =41 ;
		moj['受伤'] = 42;
		moj['说唱'] = 43;
		moj['偷看'] =44 ;
		moj['投降'] =45 ;
		moj['无奈'] = 46;
		moj['咽气'] =47 ;
		moj['郁闷'] =48 ;
		moj['找炮轰'] = 49;
		moj[ '真臭'] =50;
		moj['蜘蛛侠'] = 51;
		moj['自信'] = 52;
		moj['做梦'] = 53;
        while(jc==0){
            shou=name.indexOf("[");
            wei=name.indexOf("]");
            if(shou > -1 && wei > -1){
                temp=name.substring(shou,wei+1);
                bq_name=temp.replace("[", "");
                bq_name=bq_name.replace("]", "");

                html='<img class="express" src="'+bq_m+moj[bq_name]+bq_h+'" title="'+bq_name+'" >';
                name=name.replace(temp,html);
            }else{
                jc=1;
            }
        }
        return name;
    }

    //对话窗口模版
    function moban_duihua(){
        if(mer_id > 0)
            $(".kj").html('<div class="duihua" style="padding-top: 45px;"></div>');
        else
            $(".kj").html('<div class="duihua"></div>');
        var html= '<div class="msn">'+
                '<div class="msn_k">'+
                    '<i class="express_icon"></i>'+
                    '<i class="img_icon"></i>'+
                    '<b onclick="msn_add()">发送</b>'+
                    '<span>'+
                    '<input type="text" id="msn" placeholder="请输入要发送的消息">'+
                    '</span>'+
                '</div>'+
                '<div class="biaoqing">'+
                    '<ul>'+
                        '<li>'+
                            '<span>'+
                                '<div>'+moban_bq("[大笑]")+'</div>'+
                                '<div>'+moban_bq("[哈哈]")+'</div>'+
                                '<div>'+moban_bq("[感觉真好]")+'</div>'+
                                '<div>'+moban_bq("[拜拜]")+'</div>'+
                                '<div>'+moban_bq("[害羞]")+'</div>'+
                                '<div>'+moban_bq("[亲亲]")+'</div>'+
                                '<div>'+moban_bq("[可爱]")+'</div>'+
                                '<div>'+moban_bq("[阿]")+'</div>'+
                                '<div>'+moban_bq("[唉]")+'</div>'+
                                '<div>'+moban_bq("[悲伤]")+'</div>'+
                                '<div>'+moban_bq("[鼻出血]")+'</div>'+
                                '<div>'+moban_bq("[别逼我]")+'</div>'+
                                '<div>'+moban_bq("[不高兴]")+'</div>'+
                                '<div>'+moban_bq("[崇拜]")+'</div>'+
                                '<div>'+moban_bq("[出发]")+'</div>'+
                                '<div>'+moban_bq("[打你]")+'</div>'+
                                '<div>'+moban_bq("[打肿]")+'</div>'+
                                '<div>'+moban_bq("[大哭]")+'</div>'+
                                '<div>'+moban_bq("[大喷血]")+'</div>'+
                                '<div>'+moban_bq("[发呆]")+'</div>'+
                                '<div>'+moban_bq("[愤怒]")+'</div>'+
                            '</span>'+
                        '</li>'+
                        '<li>'+
                            '<span>'+
                                '<div>'+moban_bq("[好困]")+'</div>'+
                                '<div>'+moban_bq("[好冷]")+'</div>'+
                                '<div>'+moban_bq("[好美]")+'</div>'+
                                '<div>'+moban_bq("[好色]")+'</div>'+
                                '<div>'+moban_bq("[黑客]")+'</div>'+
                                '<div>'+moban_bq("[怀疑]")+'</div>'+
                                '<div>'+moban_bq("[坏淫]")+'</div>'+
                                '<div>'+moban_bq("[惊讶]")+'</div>'+
                                '<div>'+moban_bq("[可怜]")+'</div>'+
                                '<div>'+moban_bq("[老大]")+'</div>'+
                                '<div>'+moban_bq("[流口水]")+'</div>'+
                                '<div>'+moban_bq("[妈呀]")+'</div>'+
                                '<div>'+moban_bq("[你懂得]")+'</div>'+
                                '<div>'+moban_bq("[牛B]")+'</div>'+
                                '<div>'+moban_bq("[确定]")+'</div>'+
                                '<div>'+moban_bq("[塞饭]")+'</div>'+
                                '<div>'+moban_bq("[神阿]")+'</div>'+
                                '<div>'+moban_bq("[生气]")+'</div>'+
                                '<div>'+moban_bq("[受伤]")+'</div>'+
                                '<div>'+moban_bq("[投降]")+'</div>'+
                                '<div>'+moban_bq("[偷看]")+'</div>'+
                            '</span>'+
                        '</li>'+
                        '<li>'+
                            '<span>'+
                                '<div>'+moban_bq("[无奈]")+'</div>'+
                                '<div>'+moban_bq("[咽气]")+'</div>'+
                                '<div>'+moban_bq("[郁闷]")+'</div>'+
                                '<div>'+moban_bq("[找炮轰]")+'</div>'+
                                '<div>'+moban_bq("[真臭]")+'</div>'+
                                '<div>'+moban_bq("[做梦]")+'</div>'+
                                '<div>'+moban_bq("[自信]")+'</div>'+
                                '<div>'+moban_bq("[暴走]")+'</div>'+
                                '<div>'+moban_bq("[板砖拍你]")+'</div>'+
                                '<div>'+moban_bq("[说唱]")+'</div>'+
                                '<div>'+moban_bq("[蜘蛛侠]")+'</div>'+
                            '</span>'+
                        '</li>'+
                    '</ul>'+
                '</div>'+
            '</div>';
        $(".kj").after(html);
        if(mer_id > 0){
            var html = '<div class="title">';
                html += '<a href="javascript:history.back(-1);" class="title-back"></a>';
                html += '<div class="title-text">'+mer_name+'</div>';
                html += '<a class="title-go" href="/wap/merchant/index/mid/'+mer_id+'.html">进店</a>';
            html += '</div>';
            $(".kj").before(html);
        }
    }

    //对话窗口所用的js
    function moban_duihua_js(){
        //当按下回车时，发送消息
        $("#msn").keyup(function(){
            if(event.keyCode == 13){
                $(".msn_k b").trigger("click");
            }
        });

        //打开表情窗口
        $(".msn i.express_icon").click(function(){
            $(".biaoqing").show();
            $(".duihua").css("padding-bottom","190px");
            bottom();
        });

        //当点击其它区域时 关闭表情窗口
        $("#msn,.msn_k b,.duihua").click(function(){
            $(".biaoqing").hide();
            $(".duihua").css("padding-bottom","60px");
        })

        //表情轮播
        var slidey=$('.biaoqing').unslider({
            delay: false,
            dots: true
        });
        sliden = slidey.data('unslider');

        //表情滑动
        $(".biaoqing ul").swipe({
            swipeStatus:function(event, phase, direction, distance, duration,fingerCount) {
                if(direction == "left")//手指按住向左 表情跟随
                    $(this).css({"margin-left":"-"+distance+"px"});
                if(direction == "right")//手指按住向右 表情跟随
                    $(this).css({"margin-left":distance+"px"});

                if(phase == "cancel" || phase == "end"){//停止时
                    if(distance > 50){//滑动像素大于 30
                        $(this).css({"margin-left":"0"});
                        if(direction == "left")  //向左滑动
                             sliden.next();
                        if(direction == "right") //向右滑动
                             sliden.prev();
                    }
                    else{
                        $(this).animate({marginLeft:"0"},300);
                    }
                }
            },
        });

        //表情滑动
        $(".biaoqing ul").swipe({
            swipeStatus:function(event, phase, direction, distance, duration,fingerCount) {
                if(direction == "left")//手指按住向左 表情跟随
                    $(this).css({"margin-left":"-"+distance+"px"});
                if(direction == "right")//手指按住向右 表情跟随
                    $(this).css({"margin-left":distance+"px"});

                if(phase == "cancel" || phase == "end"){//停止时
                    if(distance > 50){//滑动像素大于 30
                        $(this).css({"margin-left":"0"});
                        if(direction == "left")  //向左滑动
                             sliden.next();
                        if(direction == "right") //向右滑动
                             sliden.prev();
                    }
                    else{
                        $(this).animate({marginLeft:"0"},300);
                    }
                }
            },
        });

        //表情滑动
        $(".biaoqing ul").swipe({
            swipeStatus:function(event, phase, direction, distance, duration,fingerCount) {
                if(direction == "left")//手指按住向左 表情跟随
                    $(this).css({"margin-left":"-"+distance+"px"});
                if(direction == "right")//手指按住向右 表情跟随
                    $(this).css({"margin-left":distance+"px"});

                if(phase == "cancel" || phase == "end"){//停止时
                    if(distance > 50){//滑动像素大于 30
                        $(this).css({"margin-left":"0"});
                        if(direction == "left")//向左滑动
                             sliden.next();
                        if(direction == "right")//向右滑动
                             sliden.prev();
                    }
                    else{
                        $(this).animate({marginLeft:"0"},300);
                    }
                }
            },
        });

        //点击表情添加
        $(".biaoqing ul li span div img").swipe({
            click:function(){
                title=$(this).attr("title");//取在title标签中的内容
                $("#msn").val($("#msn").val()+"["+title+"]");
            }
        });

        page=1; //初始化分页
        ajax_get_list(page); //首次加载聊天记录
        $(window).scroll(function() {
            //console.log($(window).scrollTop());
            if ($(window).scrollTop() <= 0 && !load_over) {
                page++;//更新分页
                ajax_get_list(page);//加载此分页聊天记录
            }
        });
        // $(".duihua").bind('swipeone',function(){
        //     alert("zhixing!");
        //     if ($(window).scrollTop() <= 0) {
        //         page++;//更新分页
        //         ajax_get_list(page);//加载此分页聊天记录
        //     }
        // });
    }



    //消息模版
    function moban_msn(data){
        var html = "";
        for(var i=0;i<data.length;i++){
            //根据主从取头像
            if(data[i].my == "my")
                tou = uavatar;
            else
                tou = to_uavatar;


            //对比时间戳
            temptime=data[i].add_time - moban_time;

            //超过60秒的信息就显示时间
            if(Math.abs(temptime) > 60){
                html +='<div class="time"><p>'+timedate(data[i].add_time,0)+'</p></div>';

                //更新全局时间戳
                moban_time=data[i].add_time;
            }
            //模版开始
            html +='<div class="'+data[i].my+'hua">'+
                   '<div class="tou">'+
                          '<img src="'+tou+'">'+
                   '</div>'+
                   '<i class="ci_ico"></i>'+
                   '<div class="ci">'+moban_bq(data[i].msn)+'</div>'+
            '</div>';
        }
        return html;
    }



     //滚动到底部
    function bottom(){
        $('html,body').animate({scrollTop:$(".kj").height()}, 500);
    }

    //顶上有新记录时，要滚动到以前的顶部距离
    function conter(jili){
        //取没更新的距离 减更新过的距离，就是要滚动到的高度
        $conter_jili=$(".kj").height()-jili
        $(document).scrollTop($conter_jili);

    }
