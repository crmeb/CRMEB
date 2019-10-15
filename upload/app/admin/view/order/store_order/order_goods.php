{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff">
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">选择类型</label>
            <div class="layui-input-block">
                <input type="radio" name="type" value="1" lay-filter="type" title="发货" checked>
                <input type="radio" name="type" value="2" lay-filter="type" title="送货">
                <input type="radio" name="type" value="3" lay-filter="type" title="虚拟">
            </div>
        </div>
        <div class="type" data-type="1">
            <div class="layui-form-item">
                <label class="layui-form-label">快递公司</label>
                <div class="layui-input-block">
                    <select name="delivery_name">
                        <option value="">请选择</option>
                        {volist name='$list' id='item' key='k'}
                        <option value="{$item}">{$item}</option>
                        {/volist}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">快递单号</label>
                <div class="layui-input-block">
                    <input type="text" name="delivery_id"   placeholder="请输入快递单号" autocomplete="off" class="layui-input">
                </div>
            </div>
        </div>
        <div class="type" data-type="2" style="display: none">
            <div class="layui-form-item">
                <label class="layui-form-label">送货人姓名</label>
                <div class="layui-input-block">
                    <input type="text" name="sh_delivery_name"   placeholder="请输入送货人姓名" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">送货人电话</label>
                <div class="layui-input-block">
                    <input type="text" name="sh_delivery_id"   placeholder="请输入送货人电话" autocomplete="off" class="layui-input">
                </div>
            </div>
        </div>
        <div class="layui-form-item" style="margin:10px 0;padding-bottom: 10px;">
            <div class="layui-input-block">
                <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="delivery">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary layui-btn-sm">重置</button>
            </div>
        </div>
    </form>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    var id={$id};
    layList.form.render();
    layList.form.on('radio(type)', function(data){
       $('.type').each(function () {
           if($(this).data('type') == data.value){
               $(this).show();
           }else{
               $(this).hide();
           }
       })
    });
    layList.search('delivery',function (data) {
        console.log(data);
        if(data.type == '1'){
            if(!data.delivery_name) return layList.msg('请选择快递公司');
            if(!data.delivery_id) return layList.msg('请填写快递单号');
        }
        if(data.type == '2'){
            if(!data.sh_delivery_name) return layList.msg('请填写送货人姓名');
            if(!data.sh_delivery_id) return layList.msg('请填写送货人电话');
        }
        var index = layList.layer.load(1, {
            shade: [0.1,'#fff']
        });
        layList.basePost(layList.U({a:'update_delivery',q:{id:id}}),data,function (res) {
            layList.layer.close(index);
           layList.msg(res.msg);
            parent.layer.close(parent.layer.getFrameIndex(window.name));
        },function (res) {
            layList.layer.close(index);
            layList.msg(res.msg);
        });
    });

</script>
{/block}