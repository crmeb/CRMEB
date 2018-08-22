{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">

                            <select name="status" aria-controls="editable" class="form-control input-sm">
                                <option value="">产品状态</option>
                                <option value="1" {eq name="where.status" value="1"}selected="selected"{/eq}>上架</option>
                                <option value="0" {eq name="where.status" value="0"}selected="selected"{/eq}>下架</option>
                            </select>
                            <div class="input-group">
                                <input type="text" name="store_name" value="{$where.store_name}" placeholder="请输入活动标题" class="input-sm form-control" size="38"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="table-responsive" style="overflow:visible">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">产品图片</th>
                            <th class="text-center">活动标题</th>
                            <th class="text-center" width="55%">活动简介</th>
                            <th class="text-center">价格</th>
                            <th class="text-center">库存</th>
                            <th class="text-center">开始时间</th>
                            <th class="text-center">结束时间</th>
                            <th class="text-center">添加时间</th>
                            <th class="text-center" width="8%">产品状态</th>
                            <th class="text-center">内容</th>
                            <th class="text-center" width="5%">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.id}
                            </td>
                            <td class="text-center">
                                <img src="{$vo.image}" alt="{$vo.store_name}" class="open_image" data-image="{$vo.image}" style="width: 50px;height: 50px;cursor: pointer;">
                            </td>
                            <td class="text-center">
                                {$vo.title}
                            </td>
                            <td class="text-center">
                                {$vo.info}
                            </td>
                            <td class="text-center">
                                {$vo.price}
                            </td>
                            <td class="text-center">
                                {$vo.stock}
                            </td>
                            <td class="text-center">
                                {$vo.start_time|date='Y-m-d H:i:s',###}
                            </td>
                            <td class="text-center">
                                {$vo.stop_time|date='Y-m-d H:i:s',###}
                            </td>
                            <td class="text-center">
                                {$vo.add_time|date='Y-m-d H:i:s',###}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['status']"}
                                {$vo.start_name}
                                {else/}
                                <i class="fa fa-close text-danger"></i>
                                {/if}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content',array('id'=>$vo['id']))}')"><i class="fa fa-pencil"></i> 编辑内容</button>
                            </td>
                            <td class="text-center">
                                <div class="input-group-btn js-group-btn">
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{$vo.store_name}-属性','{:Url('attr',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-shekel"></i> 属性
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-paste"></i> 编辑
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" id="delstor" data-url="{:Url('delete',array('id'=>$vo['id']))}">
                                                    <i class="fa fa-warning"></i> 删除
                                                </a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.js-group-btn').on('click',function(){
        $('.js-group-btn').css({zIndex:1});
        $(this).css({zIndex:2});
    });
    $('#delstor').on('click',function(){
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
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
</script>
{/block}
