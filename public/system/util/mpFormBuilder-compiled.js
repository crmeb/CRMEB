(function (global, factory) {
    define && define.amd && define(['mpBuilder', 'axios'], factory);
})(this, function (r, axios) {
    var FormBuilderName = 'form-builder';
    var api = {};
    var opt = {};
    var formBuilderInstall = function formBuilderInstall(Vue, _api, rules, _opt) {
        api = _api;
        opt = _opt;
        var builder = new formBuilder(rules),
            _render;
        Vue.component(FormBuilderName, {
            data: function data() {
                return {
                    formValidate: builder.metaData(),
                    formStatus: {
                        loading: false,
                        form: 'formValidate'
                    }
                };
            },
            render: function render(h) {
                window.__vm = this;
                _render = builder.createRender(this, h);
                window._fb = _render;
                return _render.parse();
            },
            watch: {},
            mounted: function mounted() {
                // render._bindWatch(this);
                _render._mountedCall(this);
            }
        });
    };

    var formBuilder = function formBuilder(rules) {
        this.original = rules;
        this.rules = this._handleRules();
        this.fields = this._getFields();
    };

    formBuilder.prototype = {
        //创建表单生成器
        createRender: function createRender(vm, h) {
            return new formRender(this.rules, vm, h);
        },

        //获得表单字段
        _getFields: function _getFields() {
            var field = [];
            this.rules.map(function (rule) {
                field.push(rule.field);
            });
            return field;
        },
        field: function field() {
            return this.fields;
        },

        //获得表单键值对
        metaData: function metaData() {
            var metaData = {};
            this.rules.map(function (rule) {
                metaData[rule.field] = {
                    value: rule.value,
                    type: rule.type,
                    select: rule.select
                };
            });
            return metaData;
        },

        //初始化参数
        _handleRules: function _handleRules() {
            return this.original.filter(function (rule) {
                return !!rule.field;
            }).map(function (rule) {
                rule.props || (rule.props = {});
                rule.type = rule.type.toLowerCase();
                return rule;
            });
        }
    };

    var formRender = function formRender(rules, vm, h) {
        this.vm = vm;
        this.h = h;
        this.rules = rules;
        this.r = new r(h);
        this.t = this.r.$t();
        this._mountedCallList = [];
    };

    formRender.prototype = {
        _mounted: function _mounted(call) {
            this._mountedCallList.push(call);
        },
        _mountedCall: function _mountedCall(vm) {
            this._mountedCallList.map(function (call) {
                call(vm);
            });
        },

        //绑定表单监听事件
        _bindWatch: function _bindWatch(vm) {
            var _this2 = this;

            this.rules.map(function (rule) {
                _this2._bindMetaWatch(vm, rule.field);
            });
        },

        //绑定字段监听事件
        _bindMetaWatch: function _bindMetaWatch(vm, field) {
            var _this3 = this;

            return this.vm.$watch('formValidate.' + field, function (n) {
                _this3.vm.$refs[_this3.metaRef(field)].currentValue && (_this3.vm.$refs[_this3.metaRef(field)].currentValue = n);
            });
        },
        _bindInput: function _bindInput(field, value) {
            this.setFieldValue(field, value);
            this.vm.$emit('input', value);
        },
        getFieldValue: function getFieldValue(field) {
            return this.vm.formValidate[field].value;
        },
        setFieldValue: function setFieldValue(field, value) {
            this.vm.formValidate[field].value = value;
        },

        //获得表单的ref名称
        metaRef: function metaRef(field) {
            return 'mp_' + field;
        },
        getRef: function getRef(field) {
            return this.vm.$refs[this.metaRef(field)];
        },
        getFormRef: function getFormRef() {
            return this.vm.$refs[this.vm.formStatus.form];
        },
        getParseFormData: function getParseFormData() {
            var parseData = {},
                formData = this.vm.formValidate;
            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                var _loop = function _loop() {
                    var it = _step.value;

                    var item = formData[it];
                    if (['datepicker', 'timepicker'].indexOf(item.type) != -1) {
                        if (Object.prototype.toString.call(item.value) == '[object Array]') {
                            c = [];
                            item.value.map(function (value) {
                                c.push((isNaN(Date.parse(value)) ? Date.parse(new Date()) : Date.parse(value)) / 1000);
                            });
                        } else {
                            c = parseData[it] = (isNaN(Date.parse(item.value)) ? Date.parse(new Date()) : Date.parse(item.value)) / 1000;
                        }
                        parseData[it] = c;
                    } else if (['checkbox', 'select', 'radio'].indexOf(item.type) != -1) {
                        if (Object.prototype.toString.call(item.value) == '[object Array]') {
                            c = [];
                            item.value.map(function (value) {
                                item.select[value] != undefined && c.push(item.select[value]);
                            });
                        } else {
                            c = item.select[item.value];
                        }
                        parseData[it] = c;
                    } else parseData[it] = item.value;
                };

                for (var _iterator = Object.keys(formData)[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var c;
                    var c;

                    _loop();
                }
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            return parseData;
        },
        makeForm: function makeForm(VNodeFn) {
            var _this4 = this;

            var t = new this.t();
            t.props({ 'label-width': 125 }).ref(this.vm.formStatus.form).attrs({ method: 'POST', action: opt.router + '/save' }).nativeOn('submit', function (e) {
                e.preventDefault();
                var _this = _this4.getFormRef();
                var parseData = _this4.getParseFormData();
                axios.post(_this.$attrs['action'], parseData).then(function (res) {
                    if (res.status && res.data.code == 200) return Promise.resolve(res.data);else return Promise.reject(res.data.msg || '添加失败,请稍候再试!');
                }).then(function (res) {
                    api.message('success', res.msg || '操作成功!');
                    api.closeModalFrame(window.name);
                }).catch(function (err) {
                    api.message('error', err);
                });
                console.log(parseData);
            });
            return this.r.form(t.get(), VNodeFn);
        },
        makeFormItem: function makeFormItem(field, label, VNodeFn) {
            return this.r.formItem({
                props: {
                    'props': field,
                    'label': label || ''
                }
            }, VNodeFn);
        },
        makeInput: function makeInput(rule) {
            var _this5 = this;

            _vm = this.vm;
            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this5._bindInput(field, value);
            });
            return this.r.input(t.get());
        },
        makeInputNumber: function makeInputNumber(rule) {
            var _this6 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this6._bindInput(field, value);
            });
            return this.r.inputNumber(t.get());
        },
        makeRadio: function makeRadio(rule) {
            var _this7 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this7._bindInput(field, value);
            });
            return this.r.radioGroup(t.get(), function () {
                return rule.options.map(function (option) {
                    return _this7.r.radio({ props: option.props });
                });
            });
        },
        makeCheckBox: function makeCheckBox(rule) {
            var _this8 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this8._bindInput(field, value);
            });
            return this.r.checkboxGroup(t.get(), function () {
                return rule.options.map(function (option) {
                    return _this8.r.checkbox({ props: option.props });
                });
            });
        },
        markSelect: function markSelect(rule) {
            var _this9 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this9._bindInput(field, value);
            });
            return this.r.select(t.get(), this.markSelectOptions(rule.options));
        },
        markSelectOptions: function markSelectOptions(options) {
            var _this10 = this;

            return options.map(function (option) {
                return _this10.r.option({ props: option.props });
            });
        },
        stringToDate: function stringToDate(field) {
            var val = this.getFieldValue(field);
            if (Object.prototype.toString.call(val) == '[object Array]') {
                val.map(function (v, k) {
                    Object.prototype.toString.call(v) == '[object Date]' || (val[k] = new Date(v * 1000));
                });
            } else {
                Object.prototype.toString.call(val) == '[object Date]' || (val = new Date(v * 1000));
            }
        },
        stringToTime: function stringToTime(field) {
            var val = this.getFieldValue(field),
                today = this.today();
            if (Object.prototype.toString.call(val) == '[object Array]') {
                val.map(function (v, k) {
                    Object.prototype.toString.call(v) == '[object Date]' || (val[k] = new Date(v * 1000));
                });
            } else {
                Object.prototype.toString.call(val) == '[object Date]' || (val = new Date(v * 1000));
            }
        },
        today: function today() {
            var date = new Date();
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            m = m < 10 ? '0' + m : m;
            var d = date.getDate();
            d = d < 10 ? '0' + d : d;
            return y + '-' + m + '-' + d;
        },
        makeDatePicker: function makeDatePicker(rule) {
            var _this11 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            rule.props.type || (rule.props.type = 'date');
            this.stringToDate(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this11._bindInput(field, value);
            });
            return this.r.datePicker(t.get());
        },
        makeTimePicker: function makeTimePicker(rule) {
            var _this12 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            rule.props.type || (rule.props.type = 'time');
            this.stringToTime(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this12._bindInput(field, value);
            });
            return this.r.timePicker(t.get());
        },
        makeColorPicker: function makeColorPicker(rule) {
            var _this13 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            t.props(rule.props).props('value', this.getFieldValue(field)).ref(ref).on('input', function (value) {
                return _this13._bindInput(field, value);
            });
            return this.r.colorPicker(t.get());
        },
        makeUpload: function makeUpload(rule) {
            var _this14 = this;

            var t = new this.t(),
                field = rule.field,
                ref = this.metaRef(field);
            t.props(rule.props).props('value', this.getFieldValue(field));
            //上传文件之前的钩子，参数为上传的文件，若返回 false 或者 Promise 则停止上传
            t.props('before-upload', function () {
                if (rule.props['max-length'] && rule.props['max-length'] <= _this14.getFieldValue(field).length) {
                    api.message('最多可上传' + rule.props['max-length'] + '张图片');
                    return false;
                }
            });
            //文件上传时的钩子，返回字段为 event, file, fileList
            t.props('on-progress', function (event, file, fileList) {});
            //文件上传成功时的钩子，返回字段为 response, file, fileList
            t.props('on-success', function (response, file, fileList) {
                if (response.code == 200) {
                    api.message('success', file.name + '图片上传成功');
                    _this14.getFieldValue(field).push(response.data.url);
                } else {
                    api.message('error', file.name + '图片上传失败,' + response.msg);
                }
            });
            //点击已上传的文件链接时的钩子，返回字段为 file， 可以通过 file.response 拿到服务端返回数据
            t.props('on-preview', function (file) {});
            //文件列表移除文件时的钩子，返回字段为 file, fileList
            t.props('on-remove', function (file) {});
            //文件格式验证失败时的钩子，返回字段为 file, fileList
            t.props('on-format-error', function (file, fileList) {
                api.message('error', file.name + '格式不正确，请上传 jpg 或 png 格式的图片。');
            });
            //文件超出指定大小限制时的钩子，返回字段为 file, fileList
            t.props('on-exceeded-size', function (file, fileList) {
                api.message('error', file.name + '太大，不能超过 ' + rule.props['max-size'] + 'kb');
            });
            //文件上传失败时的钩子，返回字段为 error, file, fileList
            t.props('on-error', function (error, file, fileList) {
                api.message('error', file.name + '上传失败，' + error);
            });
            t.class('mp-upload', true);
            t.ref(ref);
            var data = t.get();
            return function () {
                var render = [];
                if (data.props['mp-show-upload-list'] == true) render.push(function () {
                    return data.props.value.map(function (img) {
                        return _this14.r.make('div', { class: { 'demo-upload-list': true } }, [_this14.r.make('img', { attrs: { src: img } }), _this14.r.make('div', { class: { 'demo-upload-list-cover': true } }, [_this14.r.icon({ props: { type: 'ios-eye-outline' }, nativeOn: { 'click': function click() {
                                    api.layer.open({
                                        type: 1,
                                        title: false,
                                        closeBtn: 0,
                                        shadeClose: true,
                                        content: '<img src="' + img + '" style="display: block;width: 100%;" />'
                                    });
                                } } }), _this14.r.icon({ props: { type: 'ios-trash-outline' }, nativeOn: { 'click': function click() {
                                    data.props.value.splice(data.props.value.indexOf(img), 1);
                                } } })])]);
                    });
                }());
                if (!rule.props['max-length'] || rule.props['max-length'] > _this14.getFieldValue(rule.field).length) render.push(function () {
                    return _this14.r.upload(data, function () {
                        return [_this14.r.make('div', { class: { 'mp-upload-btn': true } }, [_this14.r.icon({ props: { type: "camera", size: 20 } })])];
                    });
                }());
                return render;
            }();
        },
        makeSubmit: function makeSubmit() {
            var _this15 = this;

            var t = new this.t();
            t.props({ type: 'primary', 'html-type': 'submit', long: true, size: "large", loading: this.vm.formStatus.loading }).on('click', function () {
                _this15.vm.formStatus.loading = true;
                _this15.getFormRef().validate(function (valid) {
                    console.log(valid);
                });
            });
            return this.r.formItem({ class: { 'add-submit-item': true } }, [this.r.button(t.get(), function () {
                return [_this15.r.span('提交')];
            })]);
        },
        parse: function parse() {
            var _this16 = this;

            return this.makeForm(function () {
                var form = _this16.rules.filter(function (rule) {
                    return !!rule.field;
                }).map(function (rule) {
                    return _this16.makeFormItem(rule.field, rule.title, function () {
                        return _this16[rule.type].call(_this16, rule);
                    });
                });
                form.push(_this16.makeSubmit());
                return form;
            });
        },
        text: function text(rule) {
            return [this.makeInput(rule)];
        },
        radio: function radio(rule) {
            return [this.makeRadio(rule)];
        },
        checkbox: function checkbox(rule) {
            return [this.makeCheckBox(rule)];
        },
        select: function select(rule) {
            return [this.markSelect(rule)];
        },
        inputnumber: function inputnumber(rule) {
            return [this.makeInputNumber(rule)];
        },
        datepicker: function datepicker(rule) {
            return [this.makeDatePicker(rule)];
        },
        timepicker: function timepicker(rule) {
            return [this.makeTimePicker(rule)];
        },
        colorpicker: function colorpicker(rule) {
            return [this.makeColorPicker(rule)];
        },
        upload: function upload(rule) {
            return this.makeUpload(rule);
        }
    };
    return {
        install: formBuilderInstall
    };
});

//# sourceMappingURL=mpFormBuilder-compiled.js.map