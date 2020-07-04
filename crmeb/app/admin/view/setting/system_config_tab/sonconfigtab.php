{extend name="public/container"}
{block name="content"}
<div class="row">

    <div class="col-sm-12">

        <div class="ibox float-e-margins">

            <div class="ibox-title">
                <button type="button" class="btn btn-w-m btn-primary add-filed">配置分类</button>
                <button type="button" class="btn btn-w-m btn-primary add_filed_base">添加配置</button>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th>编号</th>
                            <th>配置名称</th>
                            <th>字段变量</th>
                            <th>字段类型</th>
                            <th>值</th>
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
                                {$vo.info}
                            </td>
                            <td class="text-center">
                                {$vo.menu_name}
                            </td>
                            <td class="text-center">
                                {$vo.type}
                            </td>
                            <td class="text-center">
                                <?php
                                    if($vo['type'] == 'text' || $vo['type'] == 'textarea' || $vo['type'] == 'radio' || $vo['type'] == 'checkbox'){
                                              echo $vo['value'];
                                    }else if($vo['type'] == 'upload'){
                                        if($vo['upload_type'] == 3){
                                            if($vo['value']) {
                                                if(is_array($vo['value'])){
                                                    foreach ($vo['value'] as $v){
                                                    ?>
                                                        <div class="attachment">
                                                            <div class="file-box">
                                                                <div class="file">
                                                                    <a href="http://<?php echo $_SERVER['SERVER_NAME'].$v;?>" target="_blank">
                                                                        <span class="corner"></span>

                                                                        <div class="icon">
                                                                            <i class="fa fa-file"></i>
                                                                        </div>
                                                                        <div class="file-name">
                                                                            <?php
                                                                            //显示带有文件扩展名的文件名
                                                                            echo basename($v);
                                                                            ?>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                <?php } }else{  ?>
                                                    <div class="attachment">
                                                        <div class="file-box">
                                                            <div class="file">
                                                                <a href="http://<?php echo $_SERVER['SERVER_NAME'].$vo['value'];?>" target="_blank">
                                                                    <span class="corner"></span>

                                                                    <div class="icon">
                                                                        <i class="fa fa-file"></i>
                                                                    </div>
                                                                    <div class="file-name">
                                                                        <?php
                                                                        //显示带有文件扩展名的文件名
                                                                        echo basename($vo['value']);
                                                                        ?>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                <?php }
                                            }
                                        }else{?>
                                            <div class="attachment">
                                                <?php
                                                if(is_array($vo['value'])){
                                                    foreach ($vo['value'] as $v){
                                                        ?>
                                                        <div class="file-box">
                                                            <div class="file">
                                                                <span class="corner"></span>
                                                                <div class="image" style="cursor: pointer">
                                                                    <img alt="image" class="img-responsive open_image" data-image="{$v}" src="{$v}">
                                                                </div>
                                                                <div class="file-name">
                                                                    <?php
                                                                    //显示带有文件扩展名的文件名
                                                                    echo basename($v);
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <?php
                                                    }
                                                }else{
                                                    ?>
                                                    <div class="file-box">
                                                        <div class="file">
                                                            <span class="corner"></span>
                                                            <div class="image" style="cursor: pointer">
                                                                <img alt="image" class="img-responsive open_image" data-image="{$vo['value']}" src="{$vo['value']}">
                                                            </div>
                                                            <div class="file-name">
                                                                <?php
                                                                //显示带有文件扩展名的文件名
                                                                echo basename($vo['value']);
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                                <div class="clearfix"></div>
                                            </div>
                                        <?php
                                        }
                                    }
                                ?>

                            </td>

                            <td class="text-center">

                                {if condition="$vo.status eq 1"}
                                <i class="fa fa-check text-navy"></i>
                                {elseif condition="$vo.status eq 2"/}
                                <i class="fa fa-close text-danger"></i>
                                {/if}

                            </td>

                            <td class="text-center">

                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('setting.system_config/edit_config',array('id'=>$vo['id']))}')"><i class="fa fa-edit"></i> 编辑</button>

                                <button class="btn btn-danger btn-xs  del_config_tab" data-id="{$vo.id}" type="button" data-url="{:Url('setting.system_config/delete_config',array('id'=>$vo['id']))}" ><i class="fa fa-times"></i> 删除

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

    $('.add-filed').on('click',function (e) {
        window.location.replace("{:Url('index')}");
    })
    $('.open_image').on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
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
    $('.add_filed_base').on('click',function (e) {
        $eb.createModalFrame('添加配置字段',"{:Url('setting.systemConfig/create',['tab_id'=>$Request.param.tab_id,'type'=>0])}");

//        $eb.swal({
//            title: '请选择数据类型',
//            input: 'radio',
//            inputOptions: ['文本框','多行文本框','单选框','文件上传','多选框'],
//            inputValidator: function(result) {
//                return new Promise(function(resolve, reject) {
//                    if (result) {
//                        resolve();
//                    } else {
//                        reject('请选择数据类型');
//                    }
//                });
//            }
//        }).then(function(result) {
//            if (result) {
//                $eb.createModalFrame(this.innerText,"{:Url('setting.systemConfig/create',array('tab_id'=>$Request.param.tab_id))}?type="+result);
//            }
//        })
    })
</script>
{/block}