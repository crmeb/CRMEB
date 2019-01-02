<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?=$form->getTitle()?></title>
    <script src="https://cdn.bootcss.com/vue/2.5.13/vue.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/iview@2.14.3/dist/styles/iview.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/iview@2.14.3/dist/iview.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/form-create/district/province_city.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/form-create@1.5.1/dist/form-create.min.js"></script>
    <style>
        /*弹框样式修改*/
        .ivu-modal-body{padding: 5;}
        .ivu-modal-confirm-footer{display: none;}
        .ivu-date-picker {display: inline-block;line-height: normal;width: 280px;}
    </style>
</head>
<body>
<script>
    formCreate.formSuccess = function(form,$r){
        <?=$form->getSuccessScript()?>
        //刷新父级页面
//        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
        //关闭当前窗口
//        var index = parent.layer.getFrameIndex(window.name);
//        parent.layer.close(index);
        //提交成功后按钮恢复
        $r.btn.finish();
    };

    (function () {
        var create = (function () {
            var getRule = function () {
                var rule = <?=json_encode($form->getRules())?>;
                rule.forEach(function (c) {
                    if ((c.type == 'cascader' || c.type == 'tree') && Object.prototype.toString.call(c.props.data) == '[object String]') {
                        if (c.props.data.indexOf('js.') === 0) {
                            c.props.data = window[c.props.data.replace('js.', '')];
                        }
                    }
                });
                return rule;
            }, vm = new Vue,name = 'formBuilderExec<?= !$form->getId() ? '' : '_'.$form->getId() ?>';
            var _b = false;
            window[name] =  function create(el, callback) {
                if(_b) return ;
                _b = true;
                if (!el) el = document.body;
                var $f = formCreate.create(getRule(), {
                    el: el,
                    form:<?=json_encode($form->getConfig('form'))?>,
                    row:<?=json_encode($form->getConfig('row'))?>,
                    submitBtn:<?=$form->isSubmitBtn() ? '{}' : 'false'?>,
                    resetBtn:<?=$form->isResetBtn() ? 'true' : '{}'?>,
                    iframeHelper:true,
                    upload: {
                        onExceededSize: function (file) {
                            vm.$Message.error(file.name + '超出指定大小限制');
                        },
                        onFormatError: function () {
                            vm.$Message.error(file.name + '格式验证失败');
                        },
                        onError: function (error) {
                            vm.$Message.error(file.name + '上传失败,(' + error + ')');
                        },
                        onSuccess: function (res) {
                            if (res.code == 200) {
                                return res.data.filePath;
                            } else {
                                vm.$Message.error(res.msg);
                            }
                        }
                    },
                    //表单提交事件
                    onSubmit: function (formData) {
                        $f.submitStatus({loading: true});
                        $.ajax({
                            url: '<?=$form->getAction()?>',
                            type: '<?=$form->getMethod()?>',
                            dataType: 'json',
                            data: formData,
                            success: function (res) {
                                if (res.code == 200) {
                                    vm.$Message.success(res.msg);
                                    $f.submitStatus({loading: false});
                                    formCreate.formSuccess && formCreate.formSuccess(res, $f, formData);
                                    callback && callback(0, res, $f, formData);
                                    //TODO 表单提交成功!
                                } else {
                                    vm.$Message.error(res.msg || '表单提交失败');
                                    $f.btn.finish();
                                    callback && callback(1, res, $f, formData);
                                    //TODO 表单提交失败
                                }
                            },
                            error: function () {
                                vm.$Message.error('表单提交失败');
                                $f.btn.finish();
                            }
                        });
                    }
                });
                return $f;
            };
            return window[name];
        }());

        window.$f = create();
//        create();
    })();
</script>
</body>
</html>