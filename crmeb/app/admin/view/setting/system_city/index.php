{extend name="public/container"}
{block name="content"}
<style>
    a:hover{
        color: #0e9aef;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                {if condition="$pid neq 0"}
                <a type="button" class="btn btn-w-m btn-primary" href="{:Url('index')}">返回</a>
                {/if}
                <button type="button" class="btn btn-w-m btn-primary"
                        onclick="$eb.createModalFrame(this.innerText,'{$addurl}')">添加城市
                </button>
                <div class="ibox-tools">
                    <button class="btn btn-w-m btn-primary clean-cache"
                            data-url="{:Url('clean_cache')}" type="button"><i
                                class="fa fa-warning"></i> 清除缓存
                    </button>
                </div>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>

                            <th class="text-center">编号</th>
                            <th class="text-center">上级名称</th>
                            <th class="text-center">地区名称</th>
<!--                            <th class="text-center">父级</th>-->
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
                                {$vo.parent_id}
                            </td>
                            <td class="text-center">
                                <a href="{:Url('index',array('parent_id'=>$vo['city_id']))}">{$vo.name}</a>
                            </td>
<!--                            <td class="text-center">-->
<!--                                {$vo.parent_id}-->
<!--                            </td>-->
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"
                                        onclick="$eb.createModalFrame(this.innerText,'{:Url('edit',array('id'=>$vo['id']))}')">
                                    <i class="fa fa-edit"></i> 编辑
                                </button>
                                <button class="btn btn-danger btn-xs "
                                        data-url="{:Url('delete',array('city_id'=>$vo['city_id']))}" type="button"><i
                                            class="fa fa-warning"></i> 删除
                                </button>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.btn-danger').on('click', function () {
        var _this = $(this), url = _this.data('url');
        $eb.$swal('delete', function () {
            $eb.axios.get(url).then(function (res) {
                console.log(res);
                if (res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success', res.data.msg);
                    _this.parents('tr').remove();
                } else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function (err) {
                $eb.$swal('error', err);
            });
        })
    });
    $('.clean-cache').on('click', function () {
        var _this = $(this), url = _this.data('url');
        $eb.$swal('delete', function () {
            $eb.axios.get(url).then(function (res) {
                if (res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success', res.data.msg);
                } else
                    return Promise.reject(res.data.msg || '清除失败')
            }).catch(function (err) {
                $eb.$swal('error', err);
            });
        },{'title':'您确定要清除城市缓存吗？','text':'清除后系统会自动生成','confirm':'是的，我要清除'})
    });
</script>
{/block}
