{extend name="public/container"}
{block name="head"}
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__ADMIN_PATH}frame/js/plugins/iCheck/icheck.min.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<style>
    label.error{
        color: #a94442;
        margin-bottom: 0;
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        transform: translate(0, 0);
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox-title">
            <h5>配置</h5>
        </div>
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <form method="post" class="form-horizontal" id="signupForm" action="{:Url('save_basics',array('tab_id'=>$tab_id))}">
                    <input type="hidden" value="{$tab_id}" name="tab_id"/>
                    {volist name="list" id="vo"}
                    {eq name="$vo['config_tab_id']" value="$tab_id"}
                    <div class="form-group">
                        <label class="col-sm-2 control-label" {eq name="$vo['type']" value="radio"}style="padding-top: 0;"{/eq}>{$vo.info}</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    {switch name="$vo['type']" }
                                    {case value="text" break="1"}<!-- 文本框-->
                                        <input type="{$vo.type}" class="form-control" name="{$vo.menu_name}" value="{$vo.value}" validate="{$vo['required']}" style="width: {$vo.width}%"/>
                                    {/case}
                                    {case value="textarea" break="1"}<!--多行文本框-->
                                        <textarea name="{$vo.menu_name}" cols="{$vo.width}" rows="{$vo.high}" class="form-control" style="width: {$vo.width}%">{$vo.value}</textarea>
                                    {/case}
                                    {case value="checkbox" break="1"}<!--多选框-->
                                        <?php
                                        $parameter = array();
                                        $option = array();
                                        if($vo['parameter']){
                                            $parameter = explode("\n",$vo['parameter']);
                                            foreach ($parameter as $k=>$v){
                                                $option[$k] = explode('=>',$v);
                                            }
//                                            dump($option);
//                                            exit();
                                        }
                                        $checkbox_value = $vo['value'];
                                        if(!is_array($checkbox_value)) $checkbox_value = explode("\n",$checkbox_value);
//                                        dump($checkbox_value);
//                                        exit();
                                        ?>
                                        {volist name="option" id="son" key="k"}
                                            {if condition="in_array($son[0],$checkbox_value)"}
                                                <label class="checkbox-inline i-checks">
                                                    <input type="checkbox" value="{$son.0}" name="{$vo.menu_name}[]" checked="checked">{$son.1}</label>
                                            {else/}
                                                <label class="checkbox-inline i-checks">
                                                    <input type="checkbox" value="{$son.0}" name="{$vo.menu_name}[]">{$son.1}</label>
                                            {/if}
                                        {/volist}
                                    {/case}
                                    {case value="radio" break="1"}<!--单选按钮-->
                                        <?php
                                            $parameter = array();
                                            $option = array();

                                            if($vo['parameter']){
                                                $parameter = explode("\n",$vo['parameter']);
                                                foreach ($parameter as $k=>$v){
                                                    $option[$k] = explode('=>',$v);
                                                }
                                            }
                                        ?>
                                        {volist name="option" id="son"}
                                            {if condition="$son[0] eq $vo['value']"}
                                                <div class="radio i-checks checked" style="display:inline">
                                                    <label class="" style="padding-left: 0;">
                                                        <div class="iradio_square-green " style="position: relative;">
                                                            <input type="radio" checked="checked" value="{$son.0}" name="{$vo.menu_name}" style="position: absolute; opacity: 0;">
                                                        </div>
                                                        <i></i> {$son.1}
                                                    </label>
                                                </div>
                                            {else /}
                                                <div class="radio i-checks" style="display:inline">
                                                    <label class="" style="padding-left: 0;">
                                                        <div class="iradio_square-green" style="position: relative;">
                                                            <input type="radio" value="{$son.0}" name="{$vo.menu_name}" style="position: absolute; opacity: 0;">
                                                        </div>
                                                        <i></i> {$son.1}
                                                    </label>
                                                </div>
                                            {/if}
                                        {/volist}
                                    {/case}
                                    {case value="upload" break="1"}<!--文件上传-->
                                        <?php
                                             $img_image = $vo['value'];
                                             $num_img = 0;
                                             if(!empty($img_image)){
                                                 $num_img = 1;
                                             }
                                        ?>
                             <!--文件-->{if condition="$vo['upload_type'] EQ 3"}
                                            <div style="display: inline-flex;">
                                                <input type="file" class="{$vo.menu_name}_1" name="{$vo.menu_name}" style="display: none;" data-name="{$vo.menu_name}" id="{$vo.menu_name}" data-type = "{$vo.upload_type}" />
                                                <span class="flag" style="margin-top: 5px;width: 86px;height: 27px;border-radius: 6px;cursor:pointer;padding: .5rem;background-color: #18a689;color: #fff;text-align: center;" data-name="{$vo.menu_name}" >点击上传</span>
                                                    {if condition="$num_img < 1"}
                                                     <div class="file-box">
                                                        <div class="file {$vo.menu_name}">
                                                        </div>
                                                     </div>
                                                    {else/}
                                                        {volist name="$vo['value']" id="img"}
                                                        <div class="file-box">
                                                            <div class="file {$vo.menu_name}" style="position: relative;">
                                                                <a href="http://<?php echo $_SERVER['SERVER_NAME'].$img;?>" target="_blank">
                                                                    <span class="corner"></span>
                                                                    <div class="icon">
                                                                        <i class="fa fa-file"></i>
                                                                    </div>
                                                                    <div class="file-name">
                                                                        <?php
                                                                        //显示带有文件扩展名的文件名
                                                                        echo basename($img);
                                                                        ?>
                                                                    </div>
                                                                </a>
                                                                <div data-name="{$vo.menu_name}" style="position: absolute;right: 4%;top: 2%;cursor: pointer" onclick="delPic(this)">删除</div>
                                                                <input type="hidden" name="{$vo.menu_name}[]" value="{$img}">
                                                            </div>
                                                        </div>
                                                        {/volist}
                                                    {/if}
                                                    <div class="clearfix"></div>
                                            </div>
                             <!--多图-->{elseif condition="$vo['upload_type'] EQ 2"/}
                                            <div style="margin-top: 20px;">
                                                <input type="file" class="{$vo.menu_name}_1" name="{$vo.menu_name}" style="display: none;" data-name="{$vo.menu_name}" id="{$vo.menu_name}" data-type = "{$vo.upload_type}" />
                                                <span class="flag" style="margin-top: 5px;width: 86px;height: 27px;border-radius: 6px;cursor:pointer;padding: .5rem 1rem;background-color: #18a689;color: #fff;text-align: center;" data-name="{$vo.menu_name}" >点击上传</span>
                                                <div class="attachment upload_image_{$vo.menu_name}" style="display:block;margin:20px 0 5px -44px">
                                                    {volist name="$vo['value']" id="img"}
                                                    <div class="file-box">
                                                        <div class="file {$vo.menu_name}" style="position: relative;">
                                                            <a href="http://<?php echo $_SERVER['SERVER_NAME'].$img;?>" target="_blank">
                                                                <span class="corner"></span>
                                                                <div class="image">
                                                                    <img alt="image" class="img-responsive" src="{$img}">
                                                                </div>
                                                                <div class="file-name">
                                                                    <?php
                                                                    //显示带有文件扩展名的文件名
                                                                    echo basename($img);
                                                                    ?>
                                                                </div>
                                                            </a>
                                                            <div data-name="{$vo.menu_name}" style="position: absolute;right: 4%;top: 2%;cursor: pointer" onclick="delPic(this)">删除</div>
                                                            <input type="hidden" name="{$vo.menu_name}[]" value="{$img}">
                                                        </div>
                                                    </div>
                                                    {/volist}
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                             <!--单图-->{else/}
                                            <div style="display: inline-flex;">
                                                <input type="file" class="{$vo.menu_name}_1" name="{$vo.menu_name}" style="display: none;" data-name="{$vo.menu_name}" id="{$vo.menu_name}" data-type = "{$vo.upload_type}" />
                                                <div class="flag" style="width: 100px;height: 80px;background-image:url('/public/system/module/wechat/news/images/image.png');cursor: pointer"  data-name="{$vo.menu_name}" >
                                                </div>
                                                {if condition="$num_img LT 1"}
                                                        <div class="file-box">
                                                            <div class="{$vo.menu_name}">
                                                            </div>
                                                        </div>
                                                    {else/}
                                                        {volist name="$vo['value']" id="img"}
                                                            <div class="file-box">
                                                                <div class="{$vo.menu_name}">
                                                                    <div style="position: relative;" class="file">
                                                                        <a href="http://<?php echo $_SERVER['SERVER_NAME'].$img;?>" target="_blank">
                                                                            <span class="corner"></span>
                                                                            <div class="image">
                                                                                <img alt="image" class="img-responsive" src="{$img}" style="width: 100%;height: 100%">
                                                                            </div>
                                                                            <div class="file-name">
                                                                                <?php
                                                                                //显示带有文件扩展名的文件名
                                                                                echo basename($img);
                                                                                ?>
                                                                            </div>
                                                                        </a>
                                                                        <div data-name="{$vo.menu_name}" style="position: absolute;right: 4%;top: 2%;cursor: pointer" onclick="delPic(this)">删除</div>
                                                                        <input type="hidden" name="{$vo.menu_name}[]" value="{$img}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        {/volist}
                                                        <div class="clearfix"></div>
                                                    {/if}
                                                </div>
                                        {/if}
                                    {/case}
                                    {/switch}
                                </div>
                                <div class="col-md-2">
                                    <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> {$vo.desc}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    {/eq}
                    {/volist}
                    <div class="form-group" style="text-align: center;">
                        <div class="col-sm-4 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit">提交</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>
{/block}
{block name="script"}
<script>
    $eb = parent._mpApi;
    $().ready(function() {
        $("#signupForm").validate();
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
        $('.flag').on('click',function(){
            $('.'+$(this).data('name')+'_1').trigger('click');
            change_upload($(this).data('name'));
        });
        function change_upload(_this_name) {
            $('.'+_this_name+'_1').on('change',function(){
                ajaxFileUpload(this);
            });
        }
    });
    /**
    * 添加文件
    * */
    function getHtmlFlie(menu_name,html_src) {
        html_file = '';
        if(html_src.length < 1){
            return html_file;
        }
        html_file += '<a href="http://'+window.location.host+html_src+'" target="_blank">';
        html_file += '<span class="corner"></span>';
        html_file += '<div class="icon">';
        html_file += '<i class="fa fa-file"></i>';
        html_file += '</div>';
        html_file += '<div class="file-name">';
        $.ajax({
            url:"{:url('getImageName')}",
            data:'src='+html_src,
            type:'post',
            async:false,
            dataType:'json',
            success:function (re) {
                html_file += re.name;
            }
        })
        html_file += '</div>';
        html_file += '</a>';
        html_file += '<div data-name="'+menu_name+'" style="position: absolute;right: 4%;top: 2%;cursor: pointer" onclick="delPic(this)">删除</div>';
        html_file += '<input type="hidden" name="'+menu_name+'[]" value="'+html_src+'">';
        return html_file;
    }
    /**
    * 多图上传html 处理
    * */
    function getHtmlOrthe(menu_name,html_src) {
        html_order = '';
        if(html_src.length < 1){
            return html_order;
        }
        html_order += '<div class="file-box">';
        html_order += '<div class="file '+menu_name+'" style="position: relative">';
        html_order += '<a href="http://'+window.location.host+html_src+'" target="_blank">';
        html_order += '<span class="corner"></span>';
        html_order += '<div class="image">';
        html_order += '<img alt="image" class="img-responsive" src="'+html_src+'">';
        html_order += '</div>';
        html_order += '<div class="file-name">';
        $.ajax({
            url:"{:url('getImageName')}",
            data:'src='+html_src,
            type:'post',
            async:false,
            dataType:'json',
            success:function (re) {
                html_order += re.name;
            }
        })
        html_order += '</div>';
        html_order += '</a>';
        html_order += '<div data-name="'+menu_name+'" style="position: absolute;right: 4%;top: 2%;cursor: pointer" onclick="delPic(this)">删除</div>';
        html_order += '<input type="hidden" name="'+menu_name+'[]" value="'+html_src+'">';
        html_order += '</div>';
        html_order += '</div>';
        return html_order;
    }
    /**
    * 单图上传html处理
    * */
    function getHtml(menu_name,html_src) {
        html_one = '';
        if(html_src.length < 1){
               return html_one;
        }
        html_one += '<div style="position: relative;" class="file">'
        html_one += '<a href="http://'+window.location.host+html_src+'" target="_blank">';
        html_one += '<span class="corner"></span>';
        html_one += '<div class="image">';
        html_one += '<img alt="image" class="img-responsive" src="'+html_src+'">';
        html_one += '</div>';
        html_one += '<div class="file-name">';
        $.ajax({
            url:"{:url('getImageName')}",
            data:'src='+html_src,
            type:'post',
            async:false,
            dataType:'json',
            success:function (re) {
                html_one += re.name;
            }
        })
        html_one += '</div>';
        html_one += '</a>';
        html_one += '<div data-name="'+menu_name+'" style="position: absolute;right: 4%;top: 2%;cursor: pointer" onclick="delPic(this)">删除</div>';
        html_one += '<input type="hidden" name="'+menu_name+'[]" value="'+html_src+'">';
        html_one += '</div>'
        return html_one;
    }

    function ajaxFileUpload(is) {
        bool_upload_num = $(is).data('type');
        $.ajaxFileUpload({
            url: "{:url('view_upload')}",
            data:{file: $(is).data('name'),type:bool_upload_num},
            type: 'post',
            secureuri: false, //一般设置为false
            fileElementId: $(is).data('name'), // 上传文件的id、name属性名
            dataType: 'json', //返回值类型，一般设置为json、application/json
            success: function(data, status, e){
                if(data.code == 200){
                    if(bool_upload_num == 2){
                        getHtmlOrthe($(is).data('name'),data.data.url);
                        $('.upload_image_'+$(is).data('name')).append(html_order);
                    }else if(bool_upload_num == 1){
                        getHtml($(is).data('name'),data.data.url);
                        $('.'+$(is).data('name')).empty();
                        $('.'+$(is).data('name')).append(html_one);
                    }else if(bool_upload_num == 3){
                        getHtmlFlie($(is).data('name'),data.data.url);
                        $('.'+$(is).data('name')).empty();
                        $('.'+$(is).data('name')).append(html_file);
                    }else{}
                    $eb.message('success',data.msg);
                }else{
                    $eb.message('error',data.msg);
                }
                $('.'+$(is).data('name')).on('change',function(){ ajaxFileUpload(this);})
            },
            error: function(data, status, e){
               $('.'+$(is).data('name')).on('change',function(){ ajaxFileUpload(this);})
            }
        });
    }
    $('.del_upload_one')
    function delPic(_this) {
        if(!confirm('确认删除?')) return false;
        p = $(_this).parents('.'+$(_this).data('name'));
        p.empty();
    }
</script>
{/block}