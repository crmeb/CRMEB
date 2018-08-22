(function (global, factory) {
    define && define.amd && define(factory());
})(this, function () {

    var FormBuilderName = 'form-builder';
    var props = {
        'label-width': 80
    };

    var formBuilderInstall = function formBuilderInstall(Vue, rules) {
        rules = formBuilder.handleRules(rules);
        Vue.component(FormBuilderName, {
            data: function data() {
                return {
                    formValidate: formBuilder.metaData(rules)
                };
            },
            render: function render(h) {
                window.__vm = this;
                var fb = new formBuilder(this, h, rules);
                window._fb = fb;
                return fb.makeForm();
            },
            watch: {},
            created: function created() {
                // formBuilder.createWatch(this,rules);
            }
        });
    };

    var formBuilder = function formBuilder(vm, h, rules) {
        this.vm = vm;
        this.h = h;
        this.rules = rules;
    };

    formBuilder.filterFailRule = function (rules) {
        return rules.filter(function (rule) {
            return !!rule.field;
        });
    };

    formBuilder.fields = function (rules) {
        var field = [];
        rules.map(function (rule) {
            field.push(rule.field);
        });
        return field;
    };
    formBuilder.metaData = function (rules) {
        var metaData = {};
        rules.map(function (rule) {
            metaData[rule.field] = rule.value;
        });
        return metaData;
    };
    formBuilder.metaRef = function (field) {
        return 'mp_' + field;
    };
    formBuilder.metaWatch = function (vm, field) {
        var _this = this;

        return vm.$watch('formValidate.' + field, function (n) {
            vm.$refs[_this.metaRef(field)].currentValue = n;
        });
    };
    formBuilder.createWatch = function (vm, rules) {
        var _this2 = this;

        this.fields(rules).map(function (field) {
            _this2.metaWatch(vm, field);
        });
    };

    formBuilder.handleRules = function (rules) {
        return this.filterFailRule(rules).map(function (rule) {
            rule.props || (rule.props = {});
            return rule;
        });
    };

    formBuilder.prototype = {
        onInput: function onInput(field, value) {
            console.log(value);
            this.vm.formValidate[field] = value;
            this.vm.$emit('input', value);
        },
        getFieldValue: function getFieldValue(field) {
            return this.vm.formValidate[field];
        },
        makeForm: function makeForm() {
            return this.h('i-form', {
                props: props
            }, this.parse());
        },
        makeFormItem: function makeFormItem(field, label, VNodeFn) {
            return this.h('form-Item', {
                props: {
                    'props': field,
                    'label': label || ''
                }
            }, VNodeFn());
        },
        makeInput: function makeInput(rule) {
            var _this3 = this;

            _vm = this.vm;
            rule.props.value = this.getFieldValue(rule.field);
            return this.h('i-input', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this3.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            });
        },
        makeInputNumber: function makeInputNumber(rule) {
            var _this4 = this;

            rule.props.value = parseFloat(this.getFieldValue(rule.field)) || 1;
            return this.h('Input-Number', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this4.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            });
        },
        makeRadioGroup: function makeRadioGroup(rule) {
            var _this5 = this;

            var VNodeFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};

            rule.props.value = this.getFieldValue(rule.field);
            return this.h('Radio-Group', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this5.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            }, VNodeFn());
        },
        makeRadio: function makeRadio(rule) {
            var _this6 = this;

            return this.makeRadioGroup(rule, function () {
                return rule.options.map(function (option) {
                    return _this6.h('Radio', {
                        props: option.props
                    });
                });
            });
        },
        makeCheckBoxGroup: function makeCheckBoxGroup(rule) {
            var _this7 = this;

            var VNodeFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};

            rule.props.value = this.getFieldValue(rule.field);
            return this.h('Checkbox-Group', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this7.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            }, VNodeFn());
        },
        makeCheckBox: function makeCheckBox(rule) {
            var _this8 = this;

            return this.makeCheckBoxGroup(rule, function () {
                return rule.options.map(function (checkbox) {
                    return _this8.h('Checkbox', {
                        props: checkbox.props
                    });
                });
            });
        },
        markSelectOptions: function markSelectOptions(options) {
            var _this9 = this;

            return options.map(function (option) {
                return _this9.h('i-option', {
                    props: option.props
                });
            });
        },
        markSelect: function markSelect(rule) {
            var _this10 = this;

            rule.props.value = this.getFieldValue(rule.field);
            return this.h('i-select', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this10.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            }, this.markSelectOptions(rule.options));
        },
        makeDatePicker: function makeDatePicker(rule) {
            var _this11 = this;

            rule.props.value = this.getFieldValue(rule.field);
            rule.props.type || (rule.props.type = 'date');
            return this.h('Date-Picker', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this11.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            });
        },
        makeTimePicker: function makeTimePicker(rule) {
            var _this12 = this;

            rule.props.value = this.getFieldValue(rule.field);
            rule.props.type || (rule.props.type = 'time');
            return this.h('Time-Picker', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this12.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            });
        },
        makeColorPicker: function makeColorPicker(rule) {
            var _this13 = this;

            rule.props.value = this.getFieldValue(rule.field);
            return this.h('Color-Picker', {
                props: rule.props,
                on: {
                    input: function input(value) {
                        return _this13.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            });
        },
        makeUpload: function makeUpload(rule) {
            var _this14 = this;

            rule.props.value = this.getFieldValue(rule.field);
            return this.h('Upload', {
                props: rule.props,
                attrs: {
                    style: 'display: inline-block;width:58px'
                },
                on: {
                    input: function input(value) {
                        return _this14.onInput(rule.field, value);
                    }
                },
                ref: formBuilder.metaRef(rule.field)
            }, [this.h('div', { style: { width: '58px', height: '58px', lineHeight: '58px' } }, [this.h('Icon', {
                props: {
                    type: "camera",
                    size: 20
                }
            })])]);
        },
        parse: function parse() {
            var _this15 = this;

            return this.rules.filter(function (rule) {
                return !!rule.field;
            }).map(function (rule) {
                return _this15.makeFormItem(rule.field, rule.title, function () {
                    return _this15[rule.type.toLowerCase()].call(_this15, rule);
                });
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
            return [this.makeUpload(rule)];
        }
    };
    return {
        install: formBuilderInstall
    };
});

//# sourceMappingURL=mpFormBuilder-bak-compiled.js.map