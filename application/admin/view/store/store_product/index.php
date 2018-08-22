{extend name="public/container"}
{block name="head_top"}
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
<script src="{__PLUG_PATH}moment.js"></script>
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
<script src="{__ADMIN_PATH}frame/js/plugins/iCheck/icheck.min.js"></script>
<link href="{__FRAME_PATH}css/plugins/footable/footable.core.css" rel="stylesheet">
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<script src="{__FRAME_PATH}js/plugins/footable/footable.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">

        <div class="ibox">
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}',{h:760,w:900})">添加产品</button>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline" id="form">
                            <div class="ibox">
                                <div class="input-group">
                                    <select name="cate_id" aria-controls="editable" class="form-control input-sm">
                                        <option value="">所有分类</option>
                                        {volist name="cate" id="vo"}
                                        <option value="{$vo.id}" {eq name="where.cate_id" value="$vo.id"}selected="selected"{/eq}>{$vo.html}{$vo.cate_name}</option>
                                        {/volist}
                                    </select>
                                    <span class="input-group-btn">
                                <input type="hidden" name="export" value="0">
                                <input type="hidden" name="is_show" value="{$where.is_show}" />
                                <input type="text" name="store_name" value="{$where.store_name}" placeholder="请输入产品名称,关键字,编号" class="input-sm form-control" size="38">
                                 <button type="submit" id="no_export" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i> 搜索</button>
                                <button type="submit" id="export" class="btn btn-sm btn-info btn-outline"> <i class="fa fa-exchange" ></i> Excel导出</button></span>
                                    <script>
                                        $('#export').on('click',function(){
                                            $('input[name=export]').val(1);
                                        });
                                        $('#no_export').on('click',function(){
                                            $('input[name=export]').val(0);
                                        });
                                    </script>
                                </div>
                            </div>
                            <!--                            <div class="layui-btn-group"  data-name="type">-->
                            <!-- 默认 layui-btn-primary 高亮 layui-btn-radius-->
                            <!--                                <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" data-value="1">出售中产品({$onsale})</button>-->
                            <!--                                <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" data-value="2">待上架产品({$forsale})</button>-->
                            <!--                                <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" data-value="3">仓库中产品({$warehouse})</button>-->
                            <!--                                <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" data-value="4">已经售馨产品({$outofstock})</button>-->
                            <!--                                <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" data-value="5">警戒库存({$policeforce})</button>-->
                            <!--                                <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" data-value="6">产品回收站({$recycle})</button>-->
                            <!--                                <input class="search-item-value" type="hidden" name="type" value="{$where.type}" />-->
                            <!--                            </div>-->
                            <div class="search-item protype" data-name="type">
                                <div class="btn-group">
                                    <button class="btn btn-white" type="button"  data-value="1">出售中产品({$onsale})</button>
                                    <button class="btn btn-white" type="button" data-value="2">待上架产品({$forsale})</button>
                                    <button class="btn btn-white" type="button" data-value="3">仓库中产品({$warehouse})</button>
                                    <button class="btn btn-white" type="button" data-value="4">已经售馨产品({$outofstock})</button>
                                    <button class="btn btn-white" type="button" data-value="5">警戒库存({$policeforce})</button>
                                    <button class="btn btn-white" type="button" data-value="6">产品回收站({$recycle})</button>
                                </div>
                                <input class="search-item-value" type="hidden" name="type" value="{$where.type}" />
                            </div>
                            <input type="hidden" name="sales" value="{$where.sales}" />
                        </form>
                    </div>
                </div>
                <div class="table-responsive" style="overflow:visible">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">产品图片</th>
                            <th class="text-center">产品名称</th>
                            <th class="text-center">产品价格</th>
                            <th class="text-center">虚拟销量</th>
                            <th class="text-center">商品访客数</th>
                            <th class="text-center">商品浏览量</th>
                            <th class="text-center">
                                <div class="btn-group">
                                    <button data-toggle="dropdown" class="btn btn-white btn-xs dropdown-toggle" style="font-weight: bold;background-color: #f5f5f6;border: solid 0;"
                                            aria-expanded="false">销量
                                        <span class="stair caret"></span>
                                    </button>
                                    <ul class="dropdown-menu search-item" data-name="sales">
                                        <li data-value=""  {eq name="where.sales" value=""}style="color:#1ab394"{/eq}>
                                        <a class="save_mark" href="javascript:void(0);"  >
                                            <i class="fa fa-arrows-v"></i>默认
                                        </a>
                                        </li>
                                        <li data-value="p.sales desc," {eq name="where.sales" value="p.sales desc,"}style="color:#1ab394"{/eq}>
                                        <a class="save_mark" href="javascript:void(0);"  >
                                            <i class="fa fa-sort-numeric-desc"></i>降序
                                        </a>
                                        </li>
                                        <li data-value="p.sales asc,"  {eq name="where.sales" value="p.sales asc,"}style="color:#1ab394"{/eq}>
                                        <a class="save_mark" href="javascript:void(0);">
                                            <i class="fa fa-sort-numeric-asc"></i>升序
                                        </a>
                                        </li>
                                    </ul>
                                </div>
                            </th>
                            <th class="text-center">库存</th>
                            <th class="text-center">排序</th>
                            <th class="text-center">点赞</th>
                            <th class="text-center">收藏</th>
                            <th class="text-center" width="5%">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                <label class="checkbox-inline i-checks">
                                    <input type="checkbox" name="id[]" value="{$vo.id}">
                                </label>
                            </td>
                            <td class="text-center">
                                <img src="{$vo.image}" alt="{$vo.store_name}" class="open_image" data-image="{$vo.image}" style="width: 50px;height: 50px;cursor: pointer;">
                            </td>
                            <td class="text-left">
                                {$vo.store_name}<br>
                                价格：{$vo.price}<br>
                                分类：{$vo.cate_name}
                            </td>
                            <td class="text-center">
                                <span class="edit-price edit-price-{$vo.id}" data-id="{$vo.id}">{$vo.price}</span>
                                <input type="number" name="price" data-id="{$vo.id}" class="price price-{$vo.id}" value="{$vo.price}" style="display: none">
                            </td>
                            <td class="text-center">
                                {$vo.ficti}
                            </td>
                            <td class="text-center">
                                {$vo.visitor}
                            </td>
                            <td class="text-center">
                                {$vo.browse}
                            </td>
                            <td class="text-center">
                                {$vo.sales}
                            </td>
                            <td class="text-center">
                                <span class="edit-stock edit-stock-{$vo.id}" data-attr="{$vo.stock_attr}" data-name="{$vo.store_name}" data-id="{$vo.id}">{$vo.stock}</span>
                                <input type="number" name="stock" data-id="{$vo.id}" data-attr="{$vo.stock_attr}" class="stock stock-{$vo.id}" value="{$vo.stock}" style="display: none">
                            </td>
                            <td class="text-center">
                                {$vo.sort}
                            </td>
                            <td class="text-center">
                                <span class="btn btn-xs btn-white" {if condition="$vo['collect'] gt 0"}onclick="$eb.createModalFrame('点赞','{:Url('collect',array('id'=>$vo['id']))}')"{/if} style="cursor: pointer">
                                <i class="fa fa-thumbs-up"></i>&nbsp;&nbsp;{$vo.collect}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="btn btn-xs btn-white" {if condition="$vo['like'] gt 0"}onclick="$eb.createModalFrame('收藏','{:Url('like',array('id'=>$vo['id']))}')"{/if}  style="cursor: pointer">
                                <i class="fa fa-heart"></i>&nbsp;&nbsp;{$vo.like}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="input-group-btn js-group-btn" style="min-width: 136px;">
                                    <div class="btn-group">
                                        <button class="btn btn-success btn-xs"  aria-expanded="false" onclick="$eb.createModalFrame('{$vo.store_name}-属性','{:Url('attr',array('id'=>$vo['id']))}',{h:700,w:800})">
                                            属性
                                        </button>
                                        <button class="btn btn-default btn-xs" aria-expanded="false" onclick="$eb.createModalFrame('{$vo.store_name}-编辑','{:Url('edit',array('id'=>$vo['id']))}')">
                                            编辑
                                        </button>
                                        <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-pencil"></i> 编辑内容</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame(this.innerText,'{:Url('ump.store_seckill/seckill',array('id'=>$vo['id']))}')"">
                                                <i class="fa fa-gavel"></i> 开启秒杀</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="delstor" data-url="{:Url('delete',array('id'=>$vo['id']))}">
                                                    <i class="fa fa-trash"></i> 删除
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{:Url('store.storeProductReply/index',array('product_id'=>$vo['id']))}">
                                                    <i class="fa fa-warning"></i> 评论查看
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                            </td>

                        </tr>
                        {/volist}
                        </tbody>
                    </table>
<!--                    <p>-->
<!--                        <button class="btn btn-primary btn-sm this-all" type="button">全选</button>-->
<!--                        <button class="btn btn-primary btn-sm this-up" type="button">取消</button>-->
<!--                        <button class="btn btn-primary btn-sm updatetype" type="button">分类</button>-->
<!--                        <button class="btn btn-primary btn-sm" type="button">库存</button>-->
<!--                        <button class="btn btn-primary btn-sm" type="button">上架</button>-->
<!--                        <button class="btn btn-primary btn-sm" type="button">下架</button>-->
<!--                        <button class="btn btn-primary btn-sm" type="button">删除</button>-->
<!--                        <button class="btn btn-primary btn-sm" type="button">运费</button>-->
<!--                    </p>-->
                </div>
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.search-item>li').on('click', function () {
        var that = $(this), value = that.data('value'), p = that.parent(), name = p.data('name'), form = $('#form');
        form.find('input[name="' + name + '"]').val(value);
        $('input[name=export]').val(0);
        form.submit();
    });
    $('.price').on('blur',function () {
        var id = $(this).data('id');
        var price = $(this).val();
        $('.edit-price-'+id).show();
        $(this).hide();
        $eb.axios.post("{:Url('store.store_product/edit_product_price')}",{id:id,price:price}).then(function(res){
            if(res.status == 200 && res.data.code == 200) {
                $eb.layer.msg(res.data.msg);
                $('.edit-price-'+id).html(price);
            }else{
                $eb.layer.msg(res.data.msg);
            }
        }).catch(function(err){
            $eb.layer.msg(err);
        });
    });
    $('.edit-price').on('dblclick',function () {
        var id = $(this).data('id');
        $('.price-'+id).show();
        $('.price-'+id).focus();
        $(this).hide();
    })
    $('.stock').on('blur',function () {
        var attr = $(this).data('attr');
        if(!attr){
            var id = $(this).data('id');
            var stock = $(this).val();
            $('.edit-stock-'+id).show();
            $(this).hide();
            $eb.axios.post("{:Url('store.store_product/edit_product_stock')}",{id:id,stock:stock}).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.layer.msg(res.data.msg);
                    $('.edit-stock-'+id).html(stock);
                }else{
                    $eb.layer.msg(res.data.msg);
                }
            }).catch(function(err){
                $eb.layer.msg(err);
            });
        }
    });
    $('.edit-stock').on('dblclick',function () {
        var id = $(this).data('id');
        var attr = $(this).data('attr');
        var storeName = $(this).data('name');
        if(attr) $eb.createModalFrame(storeName+'-属性',"{:Url('attr')}?id="+id+",'{h:700,w:800}");
        else{
            $('.stock-'+id).show();
            $('.stock-'+id).focus();
            $(this).hide();
        }
    })
    //产品状态查找
    $('.protype>.btn-group>.btn').on('click', function () {
        var that = $(this), value = that.data('value'), p = that.parent().parent(), name = p.data('name'), form = $('#form');
        form.find('input[name="' + name + '"]').val(value);
        $('input[name=export]').val(0);
        form.submit();
    });
    //产品状态默认加载
    $('.protype').each(function(){
        var form = $('#form'),value=form.find('input[name="type"]').val(),dom = $('[data-value="'+value+'"]');
        dom.eq(0).removeClass('btn-white').addClass('btn-primary')
            .siblings().addClass('btn-white').removeClass('btn-primary btn-sm');
    });
    //全选
    $('.this-all').on('click',function () {
        $('input[name="id[]"]').each(function(){
            $(this).checked = true;
            $(this).parent().addClass('checked');
//            $eb.layer.msg('dsfds');
        });
    });
    //取消
    $('.this-up').on('click',function () {
        $('input[name="id[]"]').each(function(){
            $(this).checked = false;
            $(this).parent().removeClass('checked');
        });
    });
    //修改分类
    $('.updatetype').on('click',function () {
        var chk_value =[];
        $('input[name="id[]"]:checked').each(function(){
            chk_value.push($(this).val());
        });
        if(chk_value.length < 1){
            $eb.message('请选择商品');
            return false;
        }
        console.log(chk_value);
        var str = chk_value.join(',');
        var url = "http://"+window.location.host+"/admin/store.store_coupon/grant/id/"+str;
        $eb.createModalFrame(this.innerText,url,{'w':800});

    });
    //获取选择框值
    function getcheckvalue() {
        var chk_value =[];
        $('input[name="id[]"]:checked').each(function(){
            chk_value.push($(this).val());
        });
        if(chk_value.length < 1){
            $eb.message('请选择商品');
            return false;
        }
        return chk_value;
    }
    //浮动防止遮盖
    $('.js-group-btn').on('click',function(){
        $('.js-group-btn').css({zIndex:1});
        $(this).css({zIndex:2});
    });
    //复选框
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
    });

    //删除产品
    $('.delstor').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });

    //点击产品图放大
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
</script>
{/block}
