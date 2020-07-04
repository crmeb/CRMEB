{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">规格搜索</label>
                                <div class="layui-input-block">
                                    <input type="text" name="rule_name" class="layui-input" placeholder="请输入规格搜索">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search"
                                            lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索
                                    </button>
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
                <div class="layui-card-header">规格列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        <button type="button" class="layui-btn layui-btn-sm"
                                onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加规格模板
                        </button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="pid">
                        {{#  layui.each(d.attr_value, function(index, item){ }}
                        <p>{{item}}</p>
                        {{# }); }}
                    </script>
                    <script type="text/html" id="act">
                        <button class="layui-btn layui-btn-xs"
                                onclick="$eb.createModalFrame('编辑','{:Url('create')}?id={{d.id}}')">
                            <i class="fa fa-edit"></i> 编辑
                        </button>
                        <button class="layui-btn layui-btn-xs" lay-event='delstor'>
                            <i class="fa fa-times"></i> 删除
                        </button>
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
    setTimeout(function () {
        $('.alert-info').hide();
    }, 3000);
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List', "{:Url('index')}", function () {
        return [
            {field: 'id', title: 'ID', sort: true, event: 'id', width: '5%', align: 'center'},
            {field: 'rule_name', title: '规格名称', align: 'center'},
            {field: 'attr_name', title: '商品规格', align: 'center'},
            {field: 'attr_value', title: '商品属性', align: 'center'},
            {field: 'right', title: '操作', align: 'center', toolbar: '#act', width: '10%'},
        ];
    });
    //自定义方法
    var action = {
        set_category: function (field, id, value) {
            layList.baseGet(layList.Url({
                c: 'store.store_category',
                a: 'set_category',
                q: {field: field, id: id, value: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        }
    }
    //查询
    layList.search('search', function (where) {
        layList.reload(where, true);
    });

    //监听并执行排序
    layList.sort(['id', 'sort'], true);
    //点击事件绑定
    layList.tool(function (event, data, obj) {
        switch (event) {
            case 'delstor':
                var url = layList.U({c: 'store.store_product_rule', a: 'delete', q: {ids: [data.id]}});
                $eb.$swal('delete', function () {
                    $eb.axios.get(url).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg);
                            obj.del();
                        } else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function (err) {
                        $eb.$swal('error', err);
                    });
                })
                break;
            case 'open_image':
                $eb.openImage(data.pic);
                break;
        }
    })
</script>
{/block}
