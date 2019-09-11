{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加通知模板</button>
                <div class="ibox-tools">

                </div>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">

                        <div class="input-group">
                            <input type="text" name="key" value="" placeholder="请输入关键词" class="input-sm form-control"> <span class="input-group-btn">
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
                            <th class="text-center">标题</th>
                            <th class="text-center">关键字</th>
                            <th class="text-center">模板</th>
                            <th class="text-center">发送管理员</th>
                            <th class="text-center">状态</th>
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
                                  {$vo.title}
                              </td>
                              <td class="text-center">
                                  {$vo.type}
                              </td>
                              <td class="text-center">
                                  {$vo.template}
                              </td>
                              <td class="text-center">
                                  {$vo.push_admin_name}
                              </td>
                              <td class="text-center">
                                  <i class="fa {eq name='vo.status' value='1'}fa-check text-navy{else/}fa-close text-danger{/eq}"></i>
                              </td>
                              <td class="text-center">
                                  <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-paste"></i> 编辑</button>
                                  <button class="btn btn-warning btn-xs" data-url="{:Url('delete',array('id'=>$vo['id']))}" type="button"><i class="fa fa-warning"></i> 删除
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
