(function (global) {
    var layList = {
        table: null,
        laydate: null,
        layer: null,
        form: null,
        tableIns: null,
        laypage:null,
        element:null,
        elemOdj:[],
        boxids:'ids',
        odj:'',
        initialize: function () {
            var that = this;
            layui.use(['form','table', 'laydate', 'layer', 'laypage','element'], function () {
                that.form = layui.form;
                that.table = layui.table;
                that.laydate = layui.laydate;
                that.layer = layui.layer;
                that.laypage =layui.laypage;
                that.element = layui.element;
            })
            $('.layui-input-block').each(function () {
                var name = $(this).data('type');
                if ($(this).data('type') != undefined) {
                    var input = $(this).find('input[name="' + name + '"]');
                    $(this).children('button').each(function () {
                        $(this).on('click', function () {
                            $(this).removeClass('layui-btn-primary').siblings().addClass('layui-btn-primary');
                            input.val($(this).data('value'));
                        })
                    });
                }
            });
        },
        inintclass: function ($names) {
            var that=this;
            $names.find('button').each(function() {
                var type = $names.data('type');
                $(this).on('click',function () {
                    var value=$(this).data('value');
                    $(this).addClass('layui-btn-radius').siblings().removeClass('layui-btn-primary');
                    $names.find('input[name="'+type+'"]').val(value);
                    that.reload({[type]:value})
                })
            });
        }
    };
    //ajax POST
    layList.basePost = function (url, data, successCallback, errorCallback) {
        var that = this;
        $.ajax({
            headers: this.headers(),
            url: url,
            data: data,
            type: 'post',
            dataType: 'json',
            success: function (rem) {
                if (rem.code == 200 || rem.status == 200)
                    successCallback && successCallback(rem);
                else
                    errorCallback && errorCallback(rem);
            },
            error: function (err) {
                errorCallback && errorCallback(err);
                that.msg(err);
            }
        })
    }
    //ajax GET
    layList.baseGet = function (url,successCallback, errorCallback) {
        var that = this;
        $.ajax({
            headers: this.headers(),
            url: url,
            type: 'get',
            dataType: 'json',
            success: function (rem) {
                if (rem.code == 200 || rem.status == 200)
                    successCallback && successCallback(rem);
                else
                    errorCallback && errorCallback(rem);
            },
            error: function (err) {
                errorCallback && errorCallback(err);
                that.msg('服务器异常');
            }
        });
    };
    //设置headers头
    layList.headers = function () {
        return {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        };
    };
    //初始化 layui table
    layList.tableList = function (odj, url, data, limit, size,boxids,is_tables) {
        var limit = limit || 20, size = size || 'lg', $data = [], that = this,boxids=boxids || this.boxids;
        switch (typeof data) {
            case 'object':
                $data = data;
                break;
            case "function":
                data && ($data = data());
                break;
        }
        if(is_tables!=true) this.odj=odj;
        if(that.elemOdj[odj]==undefined) that.elemOdj[odj]=odj;
        var elemOdj=that.elemOdj[this.odj];
        console.log(that.elemOdj);
        that.tableIns = that.table.render({
            id:boxids,
            elem: '#' +elemOdj,
            url: url,
            page: true,
            limit: limit,
            size: size,
            cols: [$data]
        });
        return that.tableIns;
    };
    //获得url PHP获取当前模块 和控制器
    layList.Url = function (opt) {
        var m = opt.m || window.module, c = opt.c || window.controlle, a = opt.a || 'index', q = opt.q || '',
            p = opt.p || {}, params = '';
        params = Object.keys(p).map(function (key) {
            return key + '/' + p[key];
        }).join('/');
        gets = Object.keys(q).map(function (key) {
            return key+'='+ q[key];
        }).join('&');

        return '/' + m + '/' + c + '/' + a + (params == '' ? '' : '/' + params) + (gets == '' ? '' : '?' + gets);
    };
    layList.U=function(obj){
        return this.Url(obj);
    }
    //表单重构 where 搜索条件 join,page 是否返回到第一页,tableIns 多table时 this.tableList 返回的参数
    layList.reload = function (where, page, tableIns,initSort) {
        var whereOdJ = {where: where || {}};
        if (initSort) whereOdJ.initSort = initSort;
        if (page == true) whereOdJ.page = {curr: 1};
        if(typeof tableIns=='Object'){
            tableIns.reload(whereOdJ);
        }else{
            console.log(whereOdJ);
            this.tableIns.reload(whereOdJ);
        }
    }
    //获取排序字符串
    layList.order = function (type, filde) {
        switch (type) {
            case 'desc':
                return filde + '-desc';
                break;
            case 'asc':
                return filde + '-asc';
                break;
            case null:
                return '';
                break;
        }
    }
    //监听列表
    layList.tool = function (EventFn, fieldStr,odjs) {
        var that = this;
        // var elemOdj=elemOdj || that.elemOdj
        var elemOdj=that.elemOdj[odjs || this.odj];
        console.log(elemOdj);
        this.table.on('tool(' + elemOdj + ')', function (obj) {
            console.log(obj)
            var data = obj.data, layEvent = obj.event;
            if (typeof EventFn == 'function') {
                EventFn(layEvent, data,obj);
            } else if (EventFn && (typeof fieldStr == 'function')) {
                switch (layEvent) {
                    case EventFn:
                        fieldStr(data);
                        break;
                    default:
                        console.log('暂未监听到事件');
                        break
                }
            }
        });
    }
    //监听排序 EventFn 需要监听的值 || 函数,page 是否回到第1页,tableIns 多table时 this.tableList 返回的参数
    layList.sort = function (EventFn, page,tableIns,odj) {
        var that = this;
        // var elemOdj=elemOdj || that.elemOdj;
        var elemOdj=that.elemOdj[odj || this.odj];
        this.table.on('sort(' + elemOdj + ')', function (obj) {
            var layEvent = obj.field;
            var type = obj.type;
            if (typeof EventFn == 'function') {
                EventFn(obj);
            } else if (typeof EventFn=='object'){
                for(value in EventFn){
                    switch (layEvent) {
                        case EventFn[value]:
                            if (page == true)
                                that.reload({order: that.order(type, EventFn[value])}, true, tableIns, obj);
                            else
                                that.reload({order: that.order(type, EventFn[value])}, null, tableIns, obj);
                            continue;
                    }
                }
            }else if(EventFn){
                switch (layEvent) {
                    case EventFn:
                        if (page == true)
                            that.reload({order: that.order(type, EventFn)}, true, tableIns, obj);
                        else
                            that.reload({order: that.order(type, EventFn)}, null, tableIns, obj);
                        break;
                    default:
                        console.log('暂未监听到事件');
                        break
                }
            }
        });
    }
    layList.msg = function (msg) {
        var msg = msg || '未知错误';
        try {
            return this.layer.msg(msg);
        } catch (e) {
            console.log(e);
        }
    }
    //时间选择器
    layList.date = function (IdName) {
        if (typeof IdName == 'string' && $('#' + IdName).length == 0) return console.info('并没有找到此元素');
        var json = typeof IdName == 'object' ? IdName : {elem: '#' + IdName, range: true};
        this.laydate.render(json);
    }
    //监听复选框
    layList.switch = function (switchname, successFn) {
        this.form.on('switch(' + switchname + ')', function (obj) {
            successFn && successFn(obj, this.value, this.name);
        });
    }
    //监听select
    layList.select = function (switchname, successFn) {
        this.form.on('select(' + switchname + ')', function (obj) {
            successFn && successFn(obj, this.value, this.name);
        });
    }
    //获取复选框选中的数组
    layList.getCheckData = function (boxids) {
        var boxids = boxids || this.boxids;
        return this.table.checkStatus(boxids).data;
    }
    //搜索
    layList.search = function (btnname, successFn) {
        var name = typeof btnname == 'string' ? btnname : '';
        var that = this;
        if (name == '') return false;
        this.form.on('submit(' + btnname + ')', function (data) {
            if (typeof successFn == "function") {
                successFn(data.field);
            } else {
                that.reload(data.field);
            }
            return false;
        })
    }
    layList.codeType = function (name, type) {
        switch (name) {
            // case :
        }
    }
    layList.edit=function(name,successFn,odj){
        var that = this;
        var elemOdj=that.elemOdj[odj || this.odj];
        this.table.on('edit('+elemOdj+')',function (obj) {
            var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,field = obj.field; //得到字段
            switch (field){
                case name:
                    successFn && successFn(obj);
                    break;
                default:
                    console.log('未检测到指定字段'+name);
                    break;
            }
        });
    }
    //页面有多个table请用此函数包裹起来
    layList.tables=function(odj,data,value,successFn){
        var url=data.url || '',limit=data.limit || 20,size=data.size || 'lg',that=this;
        this.tableList(odj,url,value,limit,size);
    }
    layList.createModalFrame=function(title,src,opt){
        opt === undefined && (opt = {});
        var area=[(opt.w || 750)+'px', (opt.h || 680)+'px'];
        // $(window).resize(function() {
        //     $('.layui-layer-iframe').css('top',);
        // });
        // $('.layui-layer-iframe').on('mousedown',function () {
        //     console.log($(document).height());
        // })
        return this.layer.open({
            type: 2,
            title:title,
            area: area,
            fixed: false, //不固定
            maxmin: true,
            moveOut:false,//true  可以拖出窗外  false 只能在窗内拖
            anim:5,//出场动画 isOutAnim bool 关闭动画
            offset:'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
            shade:0,//遮罩
            resize:true,//是否允许拉伸
            content: src,//内容
            move:'.layui-layer-title',// 默认".layui-layer-title",// 触发拖动的元素
            moveEnd:function(){//拖动之后回调
                console.log(this);
            }
        });
    };

    //提取主键
    Array.prototype.getIds = function (field) {
        var ids = [];
        $.each(this, function (name, value) {
            if (value[field] != undefined) ids.push(value[field]);
        });
        return ids;
    }
    //初始化layui
    layList.initialize();

    global.layList = layList;

    return layList;
}(this));