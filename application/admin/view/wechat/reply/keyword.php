{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="window.location.href='{:Url('add_keyword')}'">添加关键字</button>
                <div class="ibox-tools">
                    <button class="btn btn-white btn-sm" onclick="location.reload()"><i class="fa fa-refresh"></i> 刷新</button>
                </div>
                <div style="margin-top: 2rem"></div>
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">

                            <select name="type" aria-controls="editable" class="form-control input-sm">
                                <option value="">回复类型</option>
                                <option value="text" {eq name="$where.type" value="text"}selected="selected"{/eq}>文字消息</option>
                                <option value="image" {eq name="$where.type" value="image"}selected="selected"{/eq}>图片消息</option>
                                <option value="news" {eq name="$where.type" value="news"}selected="selected"{/eq}>图文消息</option>
                                <option value="voice" {eq name="$where.type" value="voice"}selected="selected"{/eq}>声音消息</option>
                            </select>
                            <div class="input-group">
                                <input type="text" name="key" value="{$where.key}" placeholder="请输入关键词" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
<!--                            <th class="text-center">编号</th>-->
                            <th class="text-center">关键字</th>
                            <th class="text-center">回复类型</th>
                            <th class="text-center">状态</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
<!--                            <td class="text-center">-->
<!--                                {$vo.id}-->
<!--                            </td>-->
                            <td class="text-center">
                                {$vo.key}
                            </td>
                            <td class="text-center">
                                {switch name="$vo['type']" }
                                {case value="text" break="1"}文字消息{/case}
                                {case value="voice" break="1"}声音消息{/case}
                                {case value="image" break="1"}图片消息{/case}
                                {case value="news" break="1"}图文消息{/case}
                                {/switch}
                            </td>
                            <td class="text-center">
                                {switch name="$vo['status']" }
                                {case value="1" break="1"}<i class="fa fa-check text-navy"></i>{/case}
                                {case value="0" break="1"}<i class="fa fa-close text-danger"></i>{/case}
                                {/switch}
                            </td>

                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="window.location.href='{:Url('info_keyword',array('key'=>$vo['key']))}'" ><i class="fa fa-paste"></i> 编辑</button>
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
</script>
{/block}