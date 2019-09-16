{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <a type="button" class="btn btn-w-m btn-primary" href="{:Url('index')}">规则首页</a>
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{$addurl}')">添加规则</button>
                <div class="ibox-tools">

                </div>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">

                            <select name="is_show" aria-controls="editable" class="form-control input-sm">
                                <option value="">是否显示</option>
                                <option value="1" {eq name="params.is_show" value="1"}selected="selected"{/eq}>显示</option>
                                <option value="0" {eq name="params.is_show" value="0"}selected="selected"{/eq}>不显示</option>
                            </select>
                            <?php
                            /**<select name="access" aria-controls="editable" class="form-control input-sm">
                                <option value="">子管理员是否可用</option>
                                <option value="1" {eq name="params.access" value="1"}selected="selected"{/eq}>可用</option>
                                <option value="0" {eq name="params.access" value="0"}selected="selected"{/eq}>不可用</option>
                            </select>
                            **/?>
                        <div class="input-group">
                            <input type="text" name="keyword" value="{$params.keyword}" placeholder="请输入关键词/规则ID/父级ID" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                        </div>
                        </form>
                    </div>

                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>

                            <th class="text-center">编号</th>
                            <th class="text-center">按钮名</th>
                            <th class="text-center">父级</th>
                            <th class="text-center">模块名</th>
                            <th class="text-center">控制器名</th>
                            <th class="text-center">方法名</th>
                            <th class="text-center">是否菜单</th>
<!--                            <th class="text-center">子管理员可用</th>-->
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.id}
                            </td>
                            <td class="text-center">
                                <a href="{:Url('index',array('pid'=>$vo['id']))}">{$vo.menu_name}</a>
                            </td>
                            <td class="text-center">
                                {$vo.pid}
                            </td>
                            <td class="text-center">
                                {$vo.module}
                            </td>
                            <td class="text-center">
                                {$vo.controller}
                            </td>
                            <td class="text-center">
                                {$vo.action}
                            </td>
                            <td class="text-center">
                                <i class="fa {eq name='vo.is_show' value='1'}fa-check text-navy{else/}fa-close text-danger{/eq}"></i>
                            </td>
                            <!--<td class="text-center">
                                <i class="fa {eq name='vo.access' value='1'}fa-check text-navy{else/}fa-close text-danger{/eq}"></i>
                            </td>-->
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame(this.innerText,'{:Url('create',array('cid'=>$vo['id']))}')"><i class="fa fa-paste"></i> 添加子菜单</button>
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame(this.innerText,'{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-paste"></i> 编辑</button>
                                <button class="btn btn-warning btn-xs" data-url="{:Url('delete',array('id'=>$vo['id']))}" type="button"><i class="fa fa-warning"></i> 删除
                                </button>
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
    $('.btn-warning').on('click',function(){
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
</script>
{/block}
