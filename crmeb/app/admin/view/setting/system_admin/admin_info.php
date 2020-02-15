{extend name="public/container"}
{block name="head"}
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
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__ADMIN_PATH}frame/js/plugins/iCheck/icheck.min.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <div class="text-left">个人资料</div>
                <div class="ibox-tools">

                </div>
            </div>
            <div class="ibox-content">
                <form method="post" class="form-horizontal" id="signupForm" action="">
                    <input type="hidden" value="{$adminInfo.id}" name="id"/>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">账号</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="account" value="{$adminInfo.account}" validate="" disabled/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">姓名</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="real_name" value="{$adminInfo.real_name}" validate="required:true" id="real_name"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">原始密码</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="pwd"  id="pwd"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">新密码</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="new_pwd" id="new_pwd"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">确认新密码</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="new_pwd_ok" id="new_pwd_ok"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group" style="text-align: center;">
                        <div class="col-sm-4 col-sm-offset-2">
                            <button class="btn btn-primary add" type="button">提交</button>
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
    })
    $('.add').on('click',function (e) {
         var list = [];
         list.real_name = $('#real_name').val();
         list.pwd = $('#pwd').val();
         list.new_pwd = $('#new_pwd').val();
         list.new_pwd_ok = $('#new_pwd_ok').val();
         if(list.real_name.length < 1) return $eb.message('error','请填写姓名');
            var url = "{:Url('setAdminInfo')}";
            $.ajax({
                url:url,
                data:{real_name:list.real_name,pwd:list.pwd,new_pwd:list.new_pwd,new_pwd_ok:list.new_pwd_ok},
                type:'post',
                dataType:'json',
                success:function (re) {
                    if(re.code == 400)
                        return $eb.message('error',re.msg);
                    else
                        return $eb.message('success',re.msg);
                }
            })
    })
</script>
{/block}