{extend name="public/container"}
{block name="content"}

<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">模板状态</label>
                                <div class="layui-input-block">
                                    <select name="is_have">
                                        <option value="">状态</option>
                                        <option value="1">有</option>
                                        <option value="0">没有</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">模板列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="status">
                        {{# if(d.status == 1){ }}正常
                        {{# }else{}}锁定
                        {{#  }; }}
                    </script>
                    <script type="text/html" id="type">
                        {{#  if(d.type == 1){ }}验证码
                        {{#  }else if(d.type == 2){ }}通知
                        {{#  }else if(d.type == 3){ }}推广
                        {{#  }; }}
                    </script>
                    <script type="text/html" id="is_have">
                        {{#  if(d.is_have === 1){ }}有
                        {{#  }else if(d.is_have === 0){ }}
                        <button class="btn-xs btn btn-block" lay-event="status">添加</button>
                        {{#  }; }}
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('lst')}",function (){
        return [
            {field: 'id', title: 'ID', sort: true,event:'id',width:'4%',align:'center'},
            {field: 'templateid', title: '模板ID',align:'center',width:'6%'},
            {field: 'title', title: '模板名称',align:'center',width:'10%'},
            {field: 'content', title: '模板内容',align:'center'},
            {field: 'type', title: '模板类型',templet:'#type',align:'center',width:'6%'},
            {field: 'status', title: '模板状态',templet:'#status',align:'center',width:'6%'},
            {field: 'is_have', title: '是否拥有',templet:'#is_have',align:'center',width:'6%'}
        ];
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'status':
                var url = layList.U({c:'sms.smsPublicTemp',a:'status'});
                if(data.is_del) var code = {title:"操作提示",text:"确定恢复产品操作吗？",type:'info',confirm:'是的，恢复该产品'};
                else var code = {title:"操作提示",text:"确定申请添加" + data.templateid +"模板吗？",type:'info',confirm:'是的，我也添加'};
                $eb.$swal('delete',function(){
                    $eb.axios.post(url, {id:data.id,tempId: data.templateid}).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                        }else
                            return Promise.reject(res.data.msg || '添加失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
        }
    })
</script>
{/block}
