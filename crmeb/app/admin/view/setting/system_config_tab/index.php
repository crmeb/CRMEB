{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">

                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加分类</button>
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('setting.systemConfig/create',['type'=>0])}')">添加配置</button>

                <div class="ibox-tools">

                </div>

            </div>
            <div class="ibox-content">

                <div class="row">

                    <div class="m-b m-l">

                        <form action="" class="form-inline">

                            <select name="status" aria-controls="editable" class="form-control input-sm">
                                <option value="">状态</option>
                                <option value="1" {eq name="where.status" value="1"}selected="selected"{/eq}>显示</option>
                                <option value="0" {eq name="where.status" value="0"}selected="selected"{/eq}>不显示</option>
                            </select>

                            <div class="input-group" style="margin-top: 5px;">

                                <input type="text" placeholder="请输入分类昵称" name="title" value="{$where.title}" class="input-sm form-control"> <span class="input-group-btn">

                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search"></i>搜索</button> </span>

                            </div>

                        </form>

                    </div>



                </div>

                <div class="table-responsive">

                    <table class="table table-striped  table-bordered">

                        <thead>

                        <tr>



                            <th>编号</th>

                            <th>分类昵称</th>

                            <th>分类字段</th>

                            <th>是否显示</th>

                            <th>操作</th>

                        </tr>

                        </thead>

                        <tbody class="">

                        {volist name="list" id="vo"}

                        <tr>

                            <td class="text-center">

                                {$vo.id}

                            </td>

                            <td class="text-center">

                                <a href="{:url('index',array('pid'=>$vo['id']))}" style="cursor: pointer">{$vo.title}</a>

                            </td>

                            <td class="text-center">

                                {$vo.eng_title}

                            </td>

                            <td class="text-center">

                                {if condition="$vo.status eq 1"}
                                <i class="fa fa-check text-navy"></i>
                                {elseif condition="$vo.status eq 2"/}
                                <i class="fa fa-close text-danger"></i>
                                {/if}

                            </td>

                            <td class="text-center">

                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('查看字段','{:Url('sonConfigTab',array('tab_id'=>$vo['id']))}',{w:900})"><i class="fa fa-edit"></i> 配置字段</button>
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-edit"></i> 编辑</button>

                                {if condition="$vo['id'] > 2"}
                                <button class="btn btn-danger btn-xs  del_config_tab" data-id="{$vo.id}" type="button" data-url="{:Url('delete',array('id'=>$vo['id']))}" ><i class="fa fa-times"></i> 删除

                                </button>
                                {/if}

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

    $('.del_config_tab').on('click',function(){

        var _this = $(this),url =_this.data('url');

        $eb.$swal('delete',function(){

            $eb.axios.get(url).then(function(res){

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

</script>
{/block}