{extend name="public/container"}
{block name="content"}
<style type="text/css">
    .form-add{position: fixed;left: 0;bottom: 0;width:100%;}
    .form-add .sub-btn{border-radius: 0;width: 100%;padding: 6px 0;font-size: 14px;outline: none;border: none;color: #fff;background-color: #2d8cf0;}
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-inline">
                            <label class="layui-form-label">所有分类</label>
                            <div class="layui-input-block">
                                <select name="cate_id">
                                    <option value=" ">全部</option>
                                    {volist name='cate' id='vo'}
                                    <option value="{$vo.id}">{$vo.html}{$vo.cate_name}</option>
                                    {/volist}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">商品名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="store_name" class="layui-input" placeholder="请输入商品名称,关键字,编号">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                    <i class="layui-icon layui-icon-search"></i>搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--图片-->
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.image}}">
                    </script>
                    <!--操作-->
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-normal layui-btn-sm select" lay-event='select'>选择</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name='script'}
<script>
    var parentinputname = '{$Request.param.fodder}';
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('store.store_product/product_ist',['type'=>1])}",function (){
        return [
            {field: 'id', title: 'ID', sort: true,event:'id',width:'8%'},
            {field: 'image', title: '商品图片',templet:'#image',width:'12%'},
            {field: 'store_name', title: '商品名称',templet:'#store_name',width:'40%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act'}
        ]
    });
    //点击事件绑定
    layList.tool(function (event,data) {
        switch (event) {
            case 'select':
                parent.$f.changeField('product',data.image);
                parent.$f.changeField('product_id',data.id);
                parent.$f.changeField('title',data.store_name);
                parent.$f.changeField('store_name',data.store_name);
                parent.$f.changeField('info',data.store_info);
                parent.$f.changeField('unit_name',data.unit_name);
                parent.$f.changeField('temp_id',data.temp_id.toString());
                parent.$f.changeField('image',data.image);
                parent.$f.changeField('images',eval('('+data.slider_image+')'));
                parent.$f.changeField('price',data.price);
                parent.$f.changeField('ot_price',data.ot_price);
                parent.$f.changeField('cost',data.cost);
                parent.$f.changeField('stock',data.stock);
                parent.$f.changeField('sales',data.sales);
                parent.$f.changeField('sort',data.sort);
                parent.$f.changeField('num',1);
                parent.$f.changeField('give_integral',data.give_integral);
                parent.$f.changeField('description',data.description);
                parent.$f.closeModal(parentinputname);
                break;
        }
    })
    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
</script>
{/block}