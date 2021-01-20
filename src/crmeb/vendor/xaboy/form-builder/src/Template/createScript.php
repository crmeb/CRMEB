(function () {

    var isType = function (data, type) {
        return Object.prototype.toString.call(data) === ('[object ' + type + ']');
    };

    var parseRule = function (rule) {
        if (!rule) return [];
        rule.forEach(function (c) {
            var type = c.type ? ('' + c.type).toLocaleLowerCase() : '',
                children = isType(rule.children, 'Array') ? rule.children : [];
            if ((type === 'cascader' || type === 'tree') && c.props) {
                if (c.props.data && isType(c.props.data, 'String') && c.props.data.indexOf('js.') === 0) {
                    c.props.data = window[c.props.data.substr(3)];
                } else if (c.props.options && isType(c.props.options, 'String') && c.props.options.indexOf('js.') === 0) {
                    c.props.options = window[c.props.options.substr(3)];
                }
            } else if (type === 'group') {
                if (c.props.rules) parseRule(c.props.rules);
                if (c.props.rule) parseRule([c.props.rule]);
            } else if (c.control && c.control.length) {
                c.control.forEach(function(r) {
                    parseRule(r.rule);
                });
            }
            if (children.length) parseRule(children);
        });

        return rule;
    };

    var ajax = function (url, type, formData, callback){
        $.ajax({
            url: url,
            type: ('' + type).toLocaleUpperCase(),
            dataType: 'json',
            headers: headers,
            contentType: contentType,
            data: formData,
            success: function (res) {
                callback(1, res);
            },
            error: function () {
                callback(0, {});
            }
        });

    }

    var rule = parseRule(<?=$form->parseFormRule()?>), headers = <?=$form->parseHeaders()?>, config = <?=$form->parseFormConfig()?>, contentType = "<?=$form->getFormContentType()?>", action = "<?=$form->getAction()?>", method = "<?=$form->getMethod()?>", vm = new Vue();
    if (!vm.$Message && vm.$message) {
        Vue.prototype.$Message = vm.$message
    }
    return function (option) {
        if (!option) option = {};
        if (option.el) config.el = option.el;

        config.onSubmit = function (formData, $f) {
            $f.submitBtnProps({loading: true, disabled: true});
            ajax(action, method, formData, function (status, res) {
                if (option.callback) return option.callback(status, res, $f);

                $f.submitBtnProps({loading: false, disabled: false});
                if (status && res.code === 200) {
                    vm.$Message.success(res.msg || '表单提交成功');
                } else {
                    vm.$Message.error(res.msg || '表单提交失败');
                }
            });
        };
        if(!config.global) config.global = {};
        if(!config.global.upload) config.global.upload = {};
        if(!config.global.upload.props) config.global.upload.props = {};
        var uploadProps = config.global.upload.props;
        uploadProps.onExceededSize = function (file) {
            vm.$Message.error(file.name + '超出指定大小限制');
        };
        uploadProps.onFormatError = function (file) {
            vm.$Message.error(file.name + '格式验证失败');
        };
        uploadProps.onError = function (error, file) {
            vm.$Message.error(file.name + '上传失败,(' + error + ')');
        };
        uploadProps.onSuccess = function (res, file) {
            if (res.code === 200) {
                file.url = res.data.filePath;
            } else {
                vm.$Message.error(res.msg);
            }
        };
        var _create = function(){return formCreate.create(rule, config);}
        return option.filter ? option.filter(_create) : _create() ;
    };
})();
