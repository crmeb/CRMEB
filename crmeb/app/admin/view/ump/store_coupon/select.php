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
                            <label class="layui-form-label">产品名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="store_name" class="layui-input" placeholder="请输入产品名称,关键字,编号">
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
                </div>
            </div>
        </div>
</div>
<div class="form-add">
    <button type="submit" class="sub-btn">提交</button>
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
            {type: 'checkbox'},
            {field: 'id', title: 'ID', sort: true,event:'id'},
            {field: 'image', title: '产品图片',templet:'#image'},
            {field: 'store_name', title: '产品名称',templet:'#store_name'},
        ]
    });

    //点击事件绑定
    $(".sub-btn").on("click",function(){
        var ids = layList.getCheckData().getIds('id');
        var pics = layList.getCheckData().getIds('image');
        var p_ids = parent.$f.getValue('product_id');
        var p_pics = parent.$f.getValue('image');

        var ids_arr;
        if(typeof (p_ids) != 'string' && p_ids != 0){
            ids_arr = p_ids.concat(ids);
        }else{
            ids_arr = ids;
        }
        var pics_arr = p_pics.concat(pics);
        parent.$f.changeField('image',distinct(pics_arr));
        parent.$f.changeField('product_id',distinct(ids_arr));
        parent.$f.closeModal(parentinputname);
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
    function distinct (arr) {
        var newArr = [];
        for( i = 0; i < arr.length; i++) {
            if(!newArr.includes(arr[i])) {
                newArr.push(arr[i])
            }
        }
        return newArr
    }
</script>
{/block}