{extend name="public/container"}
{block name="content"}
<style>
    .backlog-body{
        padding: 10px 15px;
        background-color: #f8f8f8;
        color: #999;
        border-radius: 2px;
        transition: all .3s;
        -webkit-transition: all .3s;
        min-height: 92px;
    }
    .backlog-body h3{
        margin-bottom: 10px;
    }
     .layui-carousel-ind {
        position: absolute;
        top: -30px;
        text-align: right;
         right: 10px;
    }
     .layui-carousel-ind ul {
        background: 0 0;
    }
    .layui-carousel-ind li.layui-this {
        background-color: #999;
    }
    .layui-carousel-ind ul:hover{
       background:none;
    }
    .right-icon{
        position: absolute;
        right: 10px;
    }
    .backlog-body p cite {
        font-style: normal;
        font-size: 17px;
        font-weight: 300;
        color: #009688;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">搜索
                    <span class="right-icon"><i class="layui-icon layui-icon-down"></i></span>
                </div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">订单号/昵称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="nickname" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">时间范围</label>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="start_time" id="start_time" placeholder="开始时间" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="end_time" id="end_time" placeholder="结束时间" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">用户信息
                    <span class="right-icon"><i class="layui-icon layui-icon-down"></i></span>
                </div>
                <div class="layui-card-body">
                    <ul class="layui-row layui-col-space10 layui-this">
                        <li class="layui-col-xs4">
                            <div class="backlog-body">
                                <h3>姓名</h3>
                                <p><cite>{$userinfo.nickname}</cite></p>
                            </div>
                        </li>
                        {if $userinfo.spread_name}
                        <li class="layui-col-xs4">
                            <div class="backlog-body">
                                <h3>上级推广人</h3>
                                <p><cite>{$userinfo.spread_name}</cite></p>
                            </div>
                        </li>
                        {/if}
                        <li class="layui-col-xs4">
                            <div class="backlog-body">
                                <h3>佣金总收入</h3>
                                <p><cite>{$userinfo.number}</cite></p>
                            </div>
                        </li>
                        <li class="layui-col-xs4">
                            <div class="backlog-body">
                                <h3>佣金余额</h3>
                                <p><cite>{$userinfo.now_money}</cite></p>
                            </div>
                        </li>
                        <li class="layui-col-xs4">
                            <div class="backlog-body">
                                <h3>创建时间</h3>
                                <p><cite>{$userinfo.add_time|date='Y-m-d H:i:s'}</cite></p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">详情列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List">
                    </table>
                    <script type="text/html" id="barDemo">
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="see" onclick="$eb.createModalFrame('{{d.nickname}}-详情','{:Url('content_info')}?uid={{d.uid}}')"><i class="layui-icon layui-icon-edit"></i>详情</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    layList.tableList('List',"{:Url('get_extract_list',['uid'=>$uid])}",function () {
        return [
            {field: 'number', title: '佣金金额',unresize:true},
            {field: '_add_time', title: '获得时间',unresize:true},
            {field: 'mark', title: '备注',unresize:true},
        ];
    });
    layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
    layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
    $('.right-icon').click(function(){
        if($(this).parent().next('.layui-card-body').css('display')=='none'){
            $(this).parent().next('.layui-card-body').show();
            $(this).find('i').addClass('layui-icon-down').removeClass('layui-icon-up');
        }else{
            $(this).parent().next('.layui-card-body').hide();
            $(this).find('i').addClass('layui-icon-up').removeClass('layui-icon-down');
        }
    });
    layList.search('search',function(where){
        if(where.start_time!=''){
            if(where.end_time==''){
                layList.msg('请选择结束时间');
                return;
            }
        }
        if(where.end_time!=''){
            if(where.start_time==''){
                layList.msg('请选择开始时间');
                return;
            }
        }
        layList.reload(where,true);
    });
</script>
{/block}