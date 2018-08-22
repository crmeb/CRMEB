/*! form-create v1.2 | github https://github.com/xaboy/form-create | author xaboy */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else {
		var a = factory();
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(typeof self !== 'undefined' ? self : this, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 7);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = undefined;

var _util = __webpack_require__(1);

var makeFactory = function makeFactory(type, attrs) {
    return function $m(title, field) {
        var value = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';

        var rule = baseRule();
        rule.type = type;
        rule.title = title;
        rule.field = field;
        rule.value = value;
        return new make(rule, attrs);
    };
};

var baseRule = function baseRule() {
    return {
        props: {},
        event: {},
        validate: [],
        options: [],
        slot: {}
    };
};

var make = function make(rule, attrs) {
    var _this = this;

    this.rule = rule;
    attrs.forEach(function (attr) {
        _this[attr] = attrHandlers[attr];
    });
};

var attrHandlers = {};

var objAttrs = ['props', 'event', 'slot'];

objAttrs.forEach(function (attr) {
    attrHandlers[attr] = function (opt) {
        this.rule[attr] = Object.assign(this.rule[attr], opt);
        return this;
    };
});

var arrAttrs = ['validate', 'options'];

arrAttrs.forEach(function (attr) {
    attrHandlers[attr] = function (opt) {
        if (!(0, _util.isArray)(opt)) opt = [opt];
        this.rule[attr] = this.rule[attr].concat(opt);
        return this;
    };
});

make.prototype.getRule = function () {
    return this.rule;
};

exports.default = makeFactory;
exports.make = make;

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var concat = [].concat;

var assign = Object.assign;

var toString = Object.prototype.toString;

var throwIfMissing = function throwIfMissing() {
    var errorMsg = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'Missing parameter';
    throw new Error(errorMsg);
};

var isDate = function isDate(arg) {
    return toString.call(arg) === '[object Date]';
};

var isPlainObject = function isPlainObject(arg) {
    return toString.call(arg) === '[object Object]';
};

var isFunction = function isFunction(arg) {
    return toString.call(arg) === '[object Function]';
};

var isString = function isString(arg) {
    return toString.call(arg) === '[object String]';
};

var isArray = Array.isArray;

var isElement = function isElement(arg) {
    return (typeof arg === 'undefined' ? 'undefined' : _typeof(arg)) === 'object' && arg !== null && arg.nodeType === 1 && !isPlainObject(arg);
};

var deepExtend = function deepExtend(origin) {
    var target = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    var isArr = false;
    for (var key in target) {
        if (Object.prototype.hasOwnProperty.call(target, key)) {
            var clone = target[key];
            if ((isArr = isArray(clone)) || isPlainObject(clone)) {
                var nst = origin[key] === undefined;
                if (isArr) {
                    isArr = false;
                    nst && (origin[key] = []);
                } else {
                    nst && (origin[key] = {});
                }
                deepExtend(origin[key], clone);
            } else {
                origin[key] = clone;
            }
        }
    }
    return origin;
};

var uniqueId = function () {
    var id = 0;
    return function () {
        return id++;
    };
}();

exports.concat = concat;
exports.assign = assign;
exports.toString = toString;
exports.throwIfMissing = throwIfMissing;
exports.isPlainObject = isPlainObject;
exports.isDate = isDate;
exports.isFunction = isFunction;
exports.isString = isString;
exports.isArray = isArray;
exports.deepExtend = deepExtend;
exports.isElement = isElement;
exports.uniqueId = uniqueId;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.handlerFactory = undefined;

var _util = __webpack_require__(1);

var handlerFactory = function handlerFactory() {
    var prototypeExtend = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    var $h = function $h(vm, rule) {
        handler.call(this, vm, rule);
    };
    $h.prototype = Object.create(handler.prototype);
    Object.assign($h.prototype, prototypeExtend);
    $h.prototype.constructor = $h;
    return $h;
};

var handler = function handler(vm, _ref) {
    var field = _ref.field,
        type = _ref.type,
        _ref$title = _ref.title,
        title = _ref$title === undefined ? '' : _ref$title,
        _ref$options = _ref.options,
        options = _ref$options === undefined ? [] : _ref$options,
        _ref$props = _ref.props,
        props = _ref$props === undefined ? {} : _ref$props,
        _ref$validate = _ref.validate,
        validate = _ref$validate === undefined ? [] : _ref$validate,
        _ref$event = _ref.event,
        event = _ref$event === undefined ? {} : _ref$event,
        _ref$value = _ref.value,
        value = _ref$value === undefined ? '' : _ref$value,
        _ref$slot = _ref.slot,
        slot = _ref$slot === undefined ? {} : _ref$slot;

    field = field.toString();
    this.rule = {
        field: field, type: type, title: title, options: options, props: props, slot: slot,
        value: (0, _util.deepExtend)(Object.create(null), { value: value }).value,
        validate: (0, _util.isArray)(validate) ? validate : [validate],
        event: Object.keys(event).reduce(function (initial, eventName) {
            initial['on-' + eventName] = event[eventName];
            return initial;
        }, {})
    };
    this.vm = vm;
    this.unique = (0, _util.uniqueId)();
    this.refName = field + '' + this.unique;
    this.el = {};
    this.verify();
    this.handle();
};

handler.prototype = {
    handle: function handle() {
        this.changeParseValue(this.rule.value);
    },
    verify: function verify() {},
    getField: function getField() {
        return this.rule.field;
    },
    getValidate: function getValidate() {
        return this.rule.validate;
    },
    getValue: function getValue() {
        return this.parseValue;
    },
    changeValue: function changeValue(value) {
        this.rule.value = value;
        this.handle();
    },
    getRule: function getRule() {
        return this.rule;
    },
    getParseValue: function getParseValue() {
        return this.parseValue;
    },
    changeParseValue: function changeParseValue(parseValue) {
        var b = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

        if (b === true) this.vm.changeFormData(this.rule.field, parseValue);
        this.parseValue = parseValue;
    },
    mounted: function mounted() {
        this.el = this.vm.$refs[this.refName];
    }
};

exports.handlerFactory = handlerFactory;

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.renderFactory = undefined;

var _cvm = __webpack_require__(5);

var _cvm2 = _interopRequireDefault(_cvm);

var _props = __webpack_require__(6);

var _props2 = _interopRequireDefault(_props);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var renderFactory = function renderFactory(prototypeExtend) {
    var $r = function $r(vm, handler, options) {
        render.call(this, vm, handler, options);
    };
    $r.prototype = Object.create(render.prototype);
    Object.assign($r.prototype, prototypeExtend);
    $r.prototype.constructor = $r;
    return $r;
};

var render = function render(vm, handler) {
    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

    this.handler = handler;
    this.options = options;
    this.vm = vm;
    this.cvm = _cvm2.default.instance(vm.$createElement);
    this.event = handler.rule.event;
    this.init();
};

render.prototype = {
    props: _props2.default.instance(),
    init: function init() {},
    parse: function parse() {
        throw new Error('请实现parse方法');
    },
    inputProps: function inputProps() {
        var _this = this;

        var _handler = this.handler,
            refName = _handler.refName,
            unique = _handler.unique,
            _handler$rule = _handler.rule,
            props = _handler$rule.props,
            field = _handler$rule.field;

        return this.props.props(Object.assign(props, { model: 'formData.' + field, value: this.vm.formData[field], elementId: refName })).ref(refName).key('fip' + unique).on(this.event).on('input', function (value) {
            _this.vm.$emit('input', value);
            _this.vm.$set(_this.vm.formData, field, value);
        });
    }
};

exports.renderFactory = renderFactory;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.getMaker = exports.timeStampToDate = exports.getGlobalApi = exports.createHandler = exports.formCreateStyle = exports.getConfig = exports.getComponent = undefined;

var _util = __webpack_require__(1);

var _cascader = __webpack_require__(9);

var _cascader2 = _interopRequireDefault(_cascader);

var _checkbox = __webpack_require__(10);

var _checkbox2 = _interopRequireDefault(_checkbox);

var _colorPicker = __webpack_require__(11);

var _colorPicker2 = _interopRequireDefault(_colorPicker);

var _datePicker = __webpack_require__(12);

var _datePicker2 = _interopRequireDefault(_datePicker);

var _input = __webpack_require__(13);

var _input2 = _interopRequireDefault(_input);

var _inputNumber = __webpack_require__(14);

var _inputNumber2 = _interopRequireDefault(_inputNumber);

var _radio = __webpack_require__(15);

var _radio2 = _interopRequireDefault(_radio);

var _select = __webpack_require__(16);

var _select2 = _interopRequireDefault(_select);

var _switch = __webpack_require__(17);

var _switch2 = _interopRequireDefault(_switch);

var _timePicker = __webpack_require__(18);

var _timePicker2 = _interopRequireDefault(_timePicker);

var _hidden = __webpack_require__(19);

var _hidden2 = _interopRequireDefault(_hidden);

var _upload = __webpack_require__(20);

var _upload2 = _interopRequireDefault(_upload);

var _rate = __webpack_require__(21);

var _rate2 = _interopRequireDefault(_rate);

var _slider = __webpack_require__(22);

var _slider2 = _interopRequireDefault(_slider);

var _frame = __webpack_require__(23);

var _frame2 = _interopRequireDefault(_frame);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var componentList = {
    hidden: _hidden2.default,
    input: _input2.default,
    radio: _radio2.default,
    checkbox: _checkbox2.default,
    switch: _switch2.default,
    select: _select2.default,
    datepicker: _datePicker2.default,
    timepicker: _timePicker2.default,
    inputnumber: _inputNumber2.default,
    colorpicker: _colorPicker2.default,
    upload: _upload2.default,
    cascader: _cascader2.default,
    rate: _rate2.default,
    slider: _slider2.default,
    frame: _frame2.default
};

var getComponent = function getComponent(componentName) {
    if (componentList[componentName] === undefined) throw new Error(componentName + ' \u8868\u5355\u7C7B\u578B\u4E0D\u5B58\u5728');
    return componentList[componentName];
};

var getConfig = function getConfig(Vue) {
    var vm = new Vue();
    return {
        el: null,
        form: {
            inline: false,
            labelPosition: 'right',
            labelWidth: 125,
            showMessage: true,
            autocomplete: 'off'
        },
        upload: {
            beforeUpload: function beforeUpload() {},
            onProgress: function onProgress(event, file, fileList) {},
            onSuccess: function onSuccess(response, file, fileList) {},
            onError: function onError(error, file, fileList) {},
            onPreview: function onPreview(file) {},
            onRemove: function onRemove(file, fileList) {},
            onFormatError: function onFormatError(file, fileList) {},
            onExceededSize: function onExceededSize(file, fileList) {},
            handleIcon: 'ios-eye-outline',
            onHandle: function onHandle(src) {
                vm.$Modal.info({
                    title: "预览",
                    render: function render(h) {
                        return h('img', { attrs: { src: src }, style: "width: 100%" });
                    }
                });
            },
            allowRemove: true
        },
        onSubmit: function onSubmit(formData) {},
        submitBtn: {
            type: "primary",
            size: "large",
            shape: undefined,
            long: true,
            htmlType: "button",
            disabled: false,
            icon: "ios-upload",
            innerText: "提交",
            loading: false
        }
    };
};

var createHandler = function createHandler(vm, rule, createOptions) {
    var component = getComponent(rule.type),
        $h = new component.handler(vm, rule);
    $h.render = new component.render(vm, $h, createOptions);
    return $h;
};

var formCreateStyle = '.form-create{padding:25px;} .fc-files{display: inline-block;width: 58px;height: 58px;text-align: center;line-height: 60px;border: 1px solid transparent;border-radius: 4px;overflow: hidden;background: #fff;position: relative;box-shadow: 0 1px 1px rgba(0,0,0,.2);margin-right: 4px;box-sizing: border-box;}' + ' .fc-files>.ivu-icon{transform: translateY(20%);}' + '.fc-files img{width:100%;height:100%;display:block;}' + '.fc-upload .ivu-upload{display: inline-block;}' + '.fc-upload-btn i{font-size: 20px;}' + '.fc-upload .fc-upload-cover{ display: none; position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.6); }' + '.fc-upload .fc-upload-cover i{ color: #fff; font-size: 20px; cursor: pointer; margin: 0 2px; }' + '.fc-files:hover .fc-upload-cover{ display: block; }' + '.fc-upload .ivu-upload-list-file{ display: inline-block;float: left; }' + '.fc-upload .ivu-upload-list{ position: absolute;left: 0; }' + '.fc-upload-btn{ width: 58px;height: 58px;line-height: 58px;display:inline-block;background: #fff;border: 1px dashed #dddee1;border-radius: 4px;text-align: center;cursor: pointer;position: relative;overflow: hidden;transition: border-color .2s ease; } .fc-spin-icon-load{animation: ani-fc-spin 1s linear infinite;} @-webkit-keyframes ani-fc-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}50%{-webkit-transform:rotate(180deg);transform:rotate(180deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes ani-fc-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}50%{-webkit-transform:rotate(180deg);transform:rotate(180deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}';

var getGlobalApi = function getGlobalApi(fComponent) {
    return {
        formData: function formData() {
            var data = {};
            fComponent.fields().map(function (field) {
                field = field.toString();
                data[field] = fComponent.handlers[field].getValue();
            });
            return data;
        },
        getValue: function getValue(field) {
            field = field.toString();
            var handler = fComponent.handlers[field];
            if (handler === undefined) console.error(field + ' \u5B57\u6BB5\u4E0D\u5B58\u5728!');else {
                return handler.getValue();
            }
        },
        changeField: function changeField(field, value) {
            field = field.toString();
            var handler = fComponent.handlers[field];
            if (handler === undefined) console.error(field + ' \u5B57\u6BB5\u4E0D\u5B58\u5728!');else {
                if ((0, _util.isFunction)(value)) value(handler.getValue(), function change(changeValue) {
                    handler.changeValue(changeValue);
                });else handler.changeValue(value);
            }
        },
        removeField: function removeField(field) {
            field = field.toString();
            fComponent.removeField(field);
        },
        validate: function validate(successFn, errorFn) {
            fComponent.getFormRef().validate(function (valid) {
                valid === true ? successFn && successFn() : errorFn && errorFn();
            });
        },
        validateField: function validateField(field, callback) {
            field = field.toString();
            fComponent.getFormRef().validateField(field, callback);
        },
        resetFields: function resetFields() {
            fComponent.getFormRef().resetFields();
        },
        destroy: function destroy() {
            fComponent.vm.$el.remove();
            fComponent.vm.$destroy();
        },
        fields: function fields() {
            return fComponent.fields();
        },
        append: function append(rule, after) {
            fComponent.append(rule, after, false);
        },
        prepend: function prepend(rule, after) {
            fComponent.append(rule, after, true);
        },
        submit: function submit(successFn) {
            var _this = this;

            this.validate(function () {
                var formData = _this.formData();
                if ((0, _util.isFunction)(successFn)) successFn(formData);else fComponent.options.onSubmit && fComponent.options.onSubmit(formData);
            });
        },
        submitStatus: function submitStatus() {
            var _props = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

            var props = (0, _util.deepExtend)(Object.create(null), _props);
            fComponent.vm.changeButtonProps(props);
        },
        // vm:fComponent,
        btn: {
            loading: function loading() {
                fComponent.vm.changeButtonProps({ loading: true });
            },
            finish: function finish() {
                fComponent.vm.changeButtonProps({ loading: false });
            }
        },
        closeModal: function closeModal() {
            fComponent.vm.$Modal.remove();
        }
    };
};

var timeStampToDate = function timeStampToDate(timeStamp) {
    if ((0, _util.isDate)(timeStamp)) return timeStamp;else {
        var date = new Date(timeStamp);
        return date.toString() === 'Invalid Date' ? timeStamp : date;
    }
};

var getMaker = function getMaker() {
    var maker = Object.keys(componentList).reduce(function (initial, name) {
        initial[name] = componentList[name].make;
        return initial;
    }, {});
    maker.number = componentList.inputnumber.make;
    maker.time = componentList.timepicker.make;
    maker.date = componentList.datepicker.make;
    maker.color = componentList.colorpicker.make;
    return maker;
};

exports.getComponent = getComponent;
exports.getConfig = getConfig;
exports.formCreateStyle = formCreateStyle;
exports.createHandler = createHandler;
exports.getGlobalApi = getGlobalApi;
exports.timeStampToDate = timeStampToDate;
exports.getMaker = getMaker;

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _util = __webpack_require__(1);

var cvm = function cvm() {
    var createElement = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:createElement');

    this.$h = createElement;
};

var _instance = null;

var vm = null;

cvm.instance = function (createElement) {
    if (false === _instance instanceof cvm) _instance = new cvm(createElement);
    return _instance;
};

cvm.setVm = function ($vm) {
    vm = $vm;
};
cvm.clearVm = function () {
    vm = null;
};

cvm.prototype = {
    form: function form(data, VNodeFn) {
        return this.make('i-form', data, VNodeFn);
    },
    formItem: function formItem(data, VNodeFn) {
        return this.make('form-Item', data, VNodeFn);
    },
    input: function input(data, VNodeFn) {
        return this.make('i-input', data, VNodeFn);
    },
    inputNumber: function inputNumber(data, VNodeFn) {
        return this.make('Input-Number', data, VNodeFn);
    },
    radioGroup: function radioGroup(data, VNodeFn) {
        return this.make('Radio-Group', data, VNodeFn);
    },
    radio: function radio(data, VNodeFn) {
        return this.make('Radio', data, VNodeFn);
    },
    checkboxGroup: function checkboxGroup(data, VNodeFn) {
        return this.make('Checkbox-Group', data, VNodeFn);
    },
    checkbox: function checkbox(data, VNodeFn) {
        return this.make('Checkbox', data, VNodeFn);
    },
    select: function select(data, VNodeFn) {
        return this.make('i-select', data, VNodeFn);
    },
    option: function option(data, VNodeFn) {
        return this.make('i-option', data, VNodeFn);
    },
    switch: function _switch(data, VNodeFn) {
        return this.make('i-switch', data, VNodeFn);
    },
    datePicker: function datePicker(data, VNodeFn) {
        return this.make('Date-Picker', data, VNodeFn);
    },
    timePicker: function timePicker(data, VNodeFn) {
        return this.make('Time-Picker', data, VNodeFn);
    },
    colorPicker: function colorPicker(data, VNodeFn) {
        return this.make('Color-Picker', data, VNodeFn);
    },
    cascader: function cascader(data, VNodeFn) {
        return this.make('Cascader', data, VNodeFn);
    },
    upload: function upload(data, VNodeFn) {
        return this.make('Upload', data, VNodeFn);
    },
    rate: function rate(data, VNodeFn) {
        return this.make('Rate', data, VNodeFn);
    },
    slider: function slider(data, VNodeFn) {
        return this.make('Slider', data, VNodeFn);
    },
    span: function span(data, VNodeFn) {
        return this.make('span', data, VNodeFn);
    },
    icon: function icon(data, VNodeFn) {
        return this.make('Icon', data, VNodeFn);
    },
    button: function button(data, VNodeFn) {
        return this.make('i-button', data, VNodeFn);
    },
    progress: function progress(data, VNodeFn) {
        return this.make('i-progress', data, VNodeFn);
    },
    modal: function modal(data, VNodeFn) {
        return this.make('Modal', data, VNodeFn);
    },
    make: function make(nodeName, data, VNodeFn) {
        if ((0, _util.isString)(data)) data = { domProps: { innerHTML: data } };
        var Node = this.$h(nodeName, data, this.getVNode(VNodeFn));
        if (vm !== null) Node.context = vm;
        return Node;
    },
    getVNode: function getVNode(VNode) {
        return (0, _util.isFunction)(VNode) ? VNode() : VNode;
    }
};

exports.default = cvm;

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _util = __webpack_require__(1);

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

var props = function props() {
    this._data = this._initData();
    this._prev = null;
};

var _instance = null;

props.instance = function () {
    if (false === _instance instanceof props) _instance = new props();
    return _instance;
};

props.prototype = {
    _initData: function _initData() {
        return {
            class: {},
            style: {},
            attrs: {},
            props: {},
            domProps: {},
            on: {},
            nativeOn: {},
            directives: [],
            scopedSlots: {},
            slot: undefined,
            key: undefined,
            ref: undefined
        };
    },
    class: function _class() {
        var _this = this;

        var classList = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:classList');
        var status = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;

        if ((0, _util.isArray)(classList)) {
            classList.map(function (cls) {
                _this._data.class[cls.toString()] = true;
            });
        } else if ((0, _util.isPlainObject)(classList)) {
            this._data.class = (0, _util.assign)({}, this._data.class, classList);
        } else {
            this._data.class[classList.toString()] = status === undefined ? true : status;
        }
        return this;
    },
    style: function style() {
        var _style = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:style');

        var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;

        if ((0, _util.isPlainObject)(_style)) {
            this._data.style = (0, _util.assign)({}, this._data.style, _style);
        } else if (value !== undefined) {
            this._data.style[_style.toString()] = value;
        }
        return this;
    },
    attrs: function attrs() {
        var _attrs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:attrs');

        var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

        if ((0, _util.isPlainObject)(_attrs)) {
            this._data.attrs = (0, _util.assign)({}, this._data.attrs, _attrs);
        } else {
            this._data.attrs[_attrs.toString()] = value;
        }
        return this;
    },
    props: function props() {
        var _props = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:props');

        var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;

        if ((0, _util.isPlainObject)(_props)) {
            this._data.props = (0, _util.assign)({}, this._data.props, _props);
        } else {
            this._data.props[_props.toString()] = value;
        }
        return this;
    },
    domProps: function domProps() {
        var _domProps = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:domProps');

        var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;

        if ((0, _util.isPlainObject)(_domProps)) {
            this._data.domProps = (0, _util.assign)({}, this._data.domProps, _domProps);
        } else {
            this._data.domProps[_domProps.toString()] = value;
        }
        return this;
    },
    on: function on() {
        var onType = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:onType');
        var call = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};

        if ((0, _util.isPlainObject)(onType)) {
            this._data.on = (0, _util.assign)({}, this._data.on, onType);
        } else {
            this._data.on[onType.toString()] = call;
        }
        return this;
    },
    nativeOn: function nativeOn() {
        var onType = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:onType');
        var call = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};

        if ((0, _util.isPlainObject)(onType)) {
            this._data.nativeOn = (0, _util.assign)({}, this._data.nativeOn, onType);
        } else {
            this._data.nativeOn[onType.toString()] = call;
        }
        return this;
    },
    directives: function directives() {
        var _directives = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:directives');

        this._data.directives = _util.concat.call.apply(_util.concat, _toConsumableArray(this._data.directives).concat(_toConsumableArray(_directives)));
        return this;
    },
    scopedSlots: function scopedSlots() {
        var scopedSlot = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : (0, _util.throwIfMissing)('缺少参数:scopedSlot');
        var call = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};

        if ((0, _util.isPlainObject)(scopedSlot)) {
            this._data.scopedSlots = (0, _util.assign)({}, this._data.scopedSlots, scopedSlot);
        } else {
            this._data.scopedSlots[scopedSlot.toString()] = call;
        }
        return this;
    },
    slot: function slot(_slot) {
        this._data.slot = _slot;
        return this;
    },
    key: function key(_key) {
        this._data.key = _key;
        return this;
    },
    ref: function ref(_ref) {
        this._data.ref = _ref;
        return this;
    },
    init: function init() {
        this._data = this._initData();
    },
    get: function get() {
        this._prev = this._data;
        this.init();
        return this._prev;
    },
    getPrev: function getPrev() {
        return this._prev;
    }
};

exports.default = props;

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _formCreate = __webpack_require__(8);

var _formCreate2 = _interopRequireDefault(_formCreate);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

if (typeof window !== 'undefined') {
    window["formCreate"] = _formCreate2.default;
    if (window.Vue && (window.iview || window.iView)) {
        window.Vue.use(_formCreate2.default);
    }
}

module.exports.default = module.exports = _formCreate2.default;

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _util = __webpack_require__(1);

var _common = __webpack_require__(4);

var _form = __webpack_require__(24);

var _form2 = _interopRequireDefault(_form);

var _formCreateComponent = __webpack_require__(25);

var _formCreateComponent2 = _interopRequireDefault(_formCreateComponent);

var _make = __webpack_require__(0);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var version = '1.2.3';

var maker = (0, _common.getMaker)();

var formCreateStyleElId = 'form-create-style';

var formCreate = function formCreate(rules, options) {
    if (!this instanceof formCreate) throwIfMissing('formCreate is a constructor and should be called with the `new` keyword');
    this.rules = Array.isArray(rules) ? rules : [];
    this.handlers = {};
    this.fRender = {};
    this.formData = {};
    this.validate = {};
    this.fieldList = [];
    options.el = !options.el ? window.document.body : (0, _util.isElement)(options.el) ? options.el : document.querySelector(options.el);
    this.options = options;
};

formCreate.createStyle = function () {
    if (document.getElementById(formCreateStyleElId) !== null) return;
    var style = document.createElement('style');
    style.id = formCreateStyleElId;
    style.innerText = _common.formCreateStyle;
    document.getElementsByTagName('head')[0].appendChild(style);
};

formCreate.install = function (Vue) {
    var globalOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    formCreate.createStyle();
    var options = (0, _util.deepExtend)((0, _util.deepExtend)(Object.create(null), (0, _common.getConfig)(Vue)), globalOptions);
    Vue.prototype.$formCreate = function (rules) {
        var _opt = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

        var opt = (0, _util.isElement)(_opt) ? { el: _opt } : _opt;
        var fComponent = new formCreate(rules, (0, _util.deepExtend)((0, _util.deepExtend)(Object.create(null), options), opt)),
            $vm = fComponent.create(Vue);
        return fComponent.fCreateApi;
    };
    Vue.prototype.$formCreate.version = version;
    Vue.prototype.$formCreate.maker = maker;
};

formCreate.prototype = {
    checkRule: function checkRule(rule) {
        rule.type = rule.type === undefined ? 'hidden' : rule.type.toLowerCase();
        if (!rule.field) rule.field = '';
        return rule;
    },
    setHandler: function setHandler(handler) {
        var field = handler.rule.field;
        this.handlers[field] = handler;
        this.formData[field] = handler.getParseValue();
        this.validate[field] = handler.getValidate();
    },
    init: function init(vm) {
        var _this = this;

        this.vm = vm;
        this.rules.forEach(function (rule, index) {
            if (rule instanceof _make.make) _this.rules[index] = rule.getRule();
        });
        this.rules.filter(function (rule) {
            return rule.field !== undefined;
        }).forEach(function (rule) {
            rule = _this.checkRule(rule);
            var handler = (0, _common.createHandler)(_this.vm, rule, _this.options);
            if (_this.fieldList.indexOf(handler.rule.field) === -1) {
                _this.setHandler(handler);
                _this.fieldList.push(handler.rule.field);
            } else {
                console.error(handler.rule.field + " \u5B57\u6BB5\u5DF2\u5B58\u5728");
            }
        });
        this.fCreateApi = (0, _common.getGlobalApi)(this);
        vm.$set(vm, 'formData', this.formData);
        vm.$set(vm, 'buttonProps', this.options.submitBtn);
        this.fRender = new _form2.default(this);
    },
    create: function create(Vue) {
        var $fCreate = Vue.extend(this.component()),
            $vm = new $fCreate().$mount();
        this.options.el.appendChild($vm.$el);
        return $vm;
    },
    component: function component() {
        return (0, _formCreateComponent2.default)(this);
    },
    append: function append(rule, after, pre) {
        if (rule instanceof _make.make) rule = rule.getRule();
        var _rule = (0, _util.deepExtend)(Object.create(null), this.checkRule(rule));
        var handler = (0, _common.createHandler)(this.vm, _rule, this.options);
        if (Object.keys(this.handlers).indexOf(handler.rule.field) !== -1) throw new Error(_rule.field + "\u5B57\u6BB5\u5DF2\u5B58\u5728");
        this.fRender.setRender(handler, after, pre);
        this.setHandler(handler);
        this.vm.setField(handler.rule.field, handler.getParseValue());
        this.addHandlerWatch(handler);
        this.vm.$nextTick(function () {
            handler.mounted();
        });
    },
    removeField: function removeField(field) {
        if (this.handlers[field] === undefined) throw new Error(field + "\u5B57\u6BB5\u4E0D\u5B58\u5728");
        this.vm.removeFormData(field);
        delete this.handlers[field];
        delete this.validate[field];
        this.fRender.removeRender(field);
        delete this.formData[field];
    },
    addHandlerWatch: function addHandlerWatch(handler) {
        var unWatch = this.vm.$watch("formData." + handler.rule.field, function (n, o) {
            if (handler !== undefined) handler.changeParseValue(n, false);else unWatch();
        });
    },
    getFormRef: function getFormRef() {
        return this.vm.$refs[this.fRender.refName];
    },
    fields: function fields() {
        return Object.keys(this.formData);
    }
};

exports.default = {
    install: formCreate.install,
    default: formCreate,
    maker: maker,
    version: version
};

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    verify: function verify() {
        if (!this.rule.props.data) this.rule.props.data = [];
        if (!(0, _util.isArray)(this.rule.value)) this.rule.value = [];
    },
    getValue: function getValue() {
        return this.el.value;
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.cascader(this.propsData)];
    }
});

var make = (0, _make2.default)('cascader', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler2 = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler2.handlerFactory)({
    handle: function handle() {
        var _this = this;

        var parseValue = [];
        if (false === (0, _util.isArray)(this.rule.value)) this.rule.value = [this.rule.value];
        this.rule.value.forEach(function (val) {
            _this.rule.options.forEach(function (option) {
                option.value === val && parseValue.push(option.label);
            });
        });
        this.changeParseValue(parseValue);
    },
    getValue: function getValue() {
        var _this2 = this;

        var parseValue = [];
        this.parseValue.forEach(function (value) {
            _this2.rule.options.forEach(function (option) {
                option.label === value && parseValue.push(option.value);
            });
        });
        parseValue = this.rule.options.length === 1 ? parseValue[0] === undefined ? '' : parseValue[0] : parseValue;
        return parseValue;
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        var _this3 = this;

        this.propsData = this.inputProps().get();
        var _handler = this.handler,
            unique = _handler.unique,
            options = _handler.rule.options;

        return [this.cvm.checkboxGroup(this.propsData, function () {
            return options.map(function (option, index) {
                return _this3.cvm.checkbox({ props: option, key: "copt" + index + unique });
            });
        })];
    }
});

var make = (0, _make2.default)('checkbox', ['options', 'props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.colorPicker(this.propsData)];
    }
});

var make = (0, _make2.default)('colorpicker', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _common = __webpack_require__(4);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    verify: function verify() {
        this.rule.props.type = !this.rule.props.type ? 'date' : this.rule.props.type;
    },
    handle: function handle() {
        var parseValue = this.rule.value;
        if (['daterange', 'datetimerange'].indexOf(this.rule.props.type) !== -1) {
            (0, _util.isArray)(parseValue) || (parseValue = ['', '']);
            parseValue = parseValue.map(function (time) {
                return !time ? '' : (0, _common.timeStampToDate)(time);
            });
        } else {
            (0, _util.isArray)(parseValue) && (parseValue = parseValue[0]);
            parseValue = !parseValue ? '' : (0, _common.timeStampToDate)(parseValue);
        }
        this.changeParseValue(parseValue);
    },
    getValue: function getValue() {
        return this.el.publicStringValue;
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.datePicker(this.propsData)];
    }
});

var make = (0, _make2.default)('datepicker', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.input(this.propsData)];
    }
});

var make = (0, _make2.default)('input', ['props', 'event', 'validate', 'slot']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    handle: function handle() {
        var parseValue = parseFloat(this.rule.value);
        if (Number.isNaN(parseValue)) parseValue = '';
        this.changeParseValue(parseValue);
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.inputNumber(this.propsData)];
    }
});

var make = (0, _make2.default)('inputnumber', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler2 = __webpack_require__(2);

var _render = __webpack_require__(3);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler2.handlerFactory)({
    handle: function handle() {
        var _this = this;

        var parseValue = '';
        this.rule.options.forEach(function (option) {
            option.value === _this.rule.value && (parseValue = option.label);
        });
        this.changeParseValue(parseValue);
    },
    getValue: function getValue() {
        var _this2 = this;

        var parseValue = '';
        this.rule.options.forEach(function (option) {
            option.label === _this2.parseValue && (parseValue = option.value);
        });
        return parseValue;
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        var _this3 = this;

        this.propsData = this.inputProps().get();
        var _handler = this.handler,
            unique = _handler.unique,
            options = _handler.rule.options;

        return [this.cvm.radioGroup(this.propsData, function () {
            return options.map(function (option, index) {
                return _this3.cvm.radio({ props: option, key: "ropt" + index + unique });
            });
        })];
    }
});

var make = (0, _make2.default)('radio', ['options', 'props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler2 = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler2.handlerFactory)({
    handle: function handle() {
        var isArr = (0, _util.isArray)(this.rule.value),
            parseValue = void 0;
        if (this.rule.props && this.rule.props.multiple === true) parseValue = isArr === true ? this.rule.value : [this.rule.value];else parseValue = isArr === true ? this.rule.value[0] : this.rule.value;
        this.changeParseValue(parseValue);
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        var _this = this;

        this.propsData = this.inputProps().get();
        var _handler = this.handler,
            unique = _handler.unique,
            options = _handler.rule.options;

        return [this.cvm.select(this.propsData, function () {
            return options.map(function (option, index) {
                return _this.cvm.option({ props: option, key: "sopt" + index + unique });
            });
        })];
    }
});

var make = (0, _make2.default)('select', ['options', 'props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    verify: function verify() {
        if (this.rule.slot === undefined) this.rule.slot = {};
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        var slot = this.handler.rule.slot;

        this.propsData = this.inputProps().scopedSlots({
            open: function open() {
                return slot.open;
            },
            close: function close() {
                return slot.close;
            }
        }).get();
        return [this.cvm.switch(this.propsData)];
    }
});

var make = (0, _make2.default)('switch', ['slot', 'props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _common = __webpack_require__(4);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    verify: function verify() {
        this.rule.props.type = !this.rule.props.type ? 'time' : this.rule.props.type;
    },
    handle: function handle() {
        var parseValue = this.rule.value;
        if ('timerange' === this.rule.props.type) {
            (0, _util.isArray)(parseValue) || (parseValue = ['', '']);
            parseValue = parseValue.map(function (time) {
                return !time ? '' : (0, _common.timeStampToDate)(time);
            });
        } else {
            (0, _util.isArray)(parseValue) && (parseValue = parseValue[0]);
            parseValue = !parseValue ? '' : (0, _common.timeStampToDate)(parseValue);
        }
        this.changeParseValue(parseValue);
    },
    getValue: function getValue() {
        return this.el.publicStringValue;
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.timePicker(this.propsData)];
    }
});

var make = (0, _make2.default)('timepicker', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        return [];
    }
});

var make = function () {
    var makeRule = (0, _make2.default)('hidden', []);
    return makeRule.bind(makeRule, '');
}();

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler2 = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

var handler = (0, _handler2.handlerFactory)({
    verify: function verify() {
        var props = this.rule.props;
        props.defaultFileList = [];
        props.showUploadList = false;
        props.uploadType = !props.uploadType ? 'file' : props.uploadType;
        if (props.uploadType === 'file' && props.handleIcon === undefined) props.handleIcon = false;
        this.parseValue = [];
    },
    handle: function handle() {
        var _this = this;

        var files = (0, _util.isArray)(this.rule.value) ? this.rule.value : !this.rule.value ? [] : [this.rule.value];
        this.parseValue.splice(0, this.parseValue.length);
        files.forEach(function (file) {
            return _this.push(file);
        });
        this.rule.props.defaultFileList = this.parseValue;
    },
    mounted: function mounted() {
        this.el = this.vm.$refs[this.refName];
        this.changeParseValue(this.el.fileList);
    },
    push: function push(file) {
        this.parseValue.push({
            url: file,
            name: this.getFileName(file)
        });
        this.changeParseValue(this.parseValue);
    },
    getValue: function getValue() {
        var files = this.parseValue.map(function (file) {
            return file.url;
        });
        return this.rule.props.maxLength <= 1 ? files[0] || '' : files;
    },
    changeParseValue: function changeParseValue(parseValue) {
        var b = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

        if (b === true) this.vm.changeFormData(this.rule.field, parseValue);
        this.parseValue = parseValue;
        this.el.fileList = parseValue;
    },
    getFileName: function getFileName(pic) {
        var res = pic.split('/'),
            file = res[res.length - 1],
            index = file.indexOf('.');
        return index === -1 ? file : file.substr(0, index);
    }
});

var propsEventType = ['beforeUpload', 'onProgress', 'onPreview', 'onRemove', 'onFormatError', 'onExceededSize', 'onError'];

var render = (0, _render.renderFactory)({
    init: function init() {
        var _this2 = this;

        this.uploadOptions = Object.assign(Object.create(null), this.options.upload, this.handler.rule.props);
        this.issetIcon = this.uploadOptions.allowRemove || this.uploadOptions.handleIcon;
        var events = propsEventType.reduce(function (initial, eventName) {
            initial[eventName] = function () {
                var _uploadOptions$eventN;

                for (var _len = arguments.length, arg = Array(_len), _key = 0; _key < _len; _key++) {
                    arg[_key] = arguments[_key];
                }

                if (_this2.uploadOptions[eventName]) return (_uploadOptions$eventN = _this2.uploadOptions[eventName]).call.apply(_uploadOptions$eventN, [null].concat(arg));
            };
            return initial;
        }, {});
        this.propsData = this.props.props(this.uploadOptions).props('onSuccess', function (response, file, fileList) {
            var url = _this2.uploadOptions.onSuccess.call(null, response, file, fileList);
            if (url) {
                file.url = url;
                file.name = _this2.handler.getFileName(url);
            }
            _this2.handler.changeParseValue(_this2.handler.el.fileList);
        }).props(events).ref(this.handler.refName).key("fip" + this.handler.unique).get();
    },
    parse: function parse() {
        var _this3 = this;

        var _handler = this.handler,
            rule = _handler.rule,
            unique = _handler.unique,
            value = this.vm.formData[rule.field],
            render = [].concat(_toConsumableArray(value.map(function (file, index) {
            if (file.status === undefined || file.status === 'finished') {
                return _this3.makeUploadView(file.url, "" + index + unique, index);
            } else if (file.showProgress) {
                return _this3.makeProgress(file, "" + index + unique);
            }
        })));

        render.push(this.makeUploadBtn(unique, !this.uploadOptions.maxLength || this.uploadOptions.maxLength > this.vm.formData[rule.field].length));
        return [this.cvm.make('div', { key: "div4" + unique, class: { 'fc-upload': true } }, render)];
    },
    makeUploadView: function makeUploadView(src, key, index) {
        var _this4 = this;

        return this.cvm.make('div', { key: "div1" + key, class: { 'fc-files': true } }, function () {
            var container = [];
            if (_this4.uploadOptions.uploadType === 'image') {
                container.push(_this4.cvm.make('img', { key: "img" + key, attrs: { src: src } }));
            } else {
                container.push(_this4.cvm.icon({ key: "file" + key, props: { type: "document-text", size: 40 } }));
            }
            if (_this4.issetIcon) container.push(_this4.makeIcons(src, key, index));
            return container;
        });
    },
    makeIcons: function makeIcons(src, key, index) {
        var _this5 = this;

        return this.cvm.make('div', { key: "div2" + key, class: { 'fc-upload-cover': true } }, function () {
            var icon = [];
            if (!!_this5.uploadOptions.handleIcon) icon.push(_this5.makeHandleIcon(src, key, index));
            if (_this5.uploadOptions.allowRemove === true) icon.push(_this5.makeRemoveIcon(src, key, index));
            return icon;
        });
    },
    makeProgress: function makeProgress(file, unique) {
        return this.cvm.make('div', { key: "div3" + unique, class: { 'fc-files': true } }, [this.cvm.progress({ key: "upp" + unique, props: { percent: file.percentage, hideInfo: true } })]);
    },
    makeUploadBtn: function makeUploadBtn(unique, isShow) {
        return this.cvm.upload(this.propsData, isShow === true ? [this.cvm.make('div', { key: "div5" + unique, class: { 'fc-upload-btn': true } }, [this.cvm.icon({ key: "upi" + unique, props: { type: "camera", size: 20 } })])] : []);
    },
    makeRemoveIcon: function makeRemoveIcon(src, key, index) {
        var _this6 = this;

        return this.cvm.icon({ key: "upri" + key + index, props: { type: 'ios-trash-outline' }, nativeOn: { 'click': function click() {
                    _this6.handler.el.fileList.splice(index, 1);
                } } });
    },
    makeHandleIcon: function makeHandleIcon(src, key, index) {
        var _this7 = this;

        return this.cvm.icon({ key: "uphi" + key + index, props: { type: this.uploadOptions.handleIcon.toString() }, nativeOn: { 'click': function click() {
                    _this7.uploadOptions.onHandle(src);
                } } });
    }
});

var make = (0, _make2.default)('upload', ['props', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    handle: function handle() {
        var parseValue = parseFloat(this.rule.value);
        if (Number.isNaN(parseValue)) parseValue = 0;
        this.changeParseValue(parseValue);
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.rate(this.propsData)];
    }
});

var make = (0, _make2.default)('rate', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    verify: function verify() {
        this.rule.props.min = this.rule.props.min === undefined ? 0 : parseFloat(this.rule.props.min);
    },
    handle: function handle() {
        var isArr = (0, _util.isArray)(this.rule.value),
            min = this.rule.props.min,
            parseValue = void 0;
        if (this.rule.props.range === true) {
            parseValue = isArr ? this.rule.value : [min, parseFloat(this.rule.value) || min];
        } else {
            parseValue = isArr ? parseFloat(this.rule.value[0]) || min : parseFloat(this.rule.value);
        }
        this.changeParseValue(parseValue);
    }
});

var render = (0, _render.renderFactory)({
    parse: function parse() {
        this.propsData = this.inputProps().get();
        return [this.cvm.slider(this.propsData)];
    }
});

var make = (0, _make2.default)('slider', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.make = exports.render = exports.handler = undefined;

var _handler = __webpack_require__(2);

var _render = __webpack_require__(3);

var _util = __webpack_require__(1);

var _make = __webpack_require__(0);

var _make2 = _interopRequireDefault(_make);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var handler = (0, _handler.handlerFactory)({
    verify: function verify() {
        var props = this.rule.props;
        if (!props.type) props.type = 'input';
        if (!props.icon) props.icon = 'folder';
        if (!props.width) props.width = '500px';
        if (!props.height) props.height = '370px';
        if (props.spin === undefined) props.spin = true;
        if (!props.title) props.title = '请选择' + this.rule.title;
        if (!props.maxLength) props.maxLength = 0;
        props.multiple = !props.maxLength || props.maxLength > 1;
        if (props.type === 'file' && props.handleIcon === undefined) props.handleIcon = false;else props.handleIcon = props.handleIcon === true || props.handleIcon === undefined ? 'ios-eye-outline' : props.handleIcon;
        if (props.allowRemove === undefined) props.allowRemove = true;
    },
    handle: function handle() {
        var parseValue = void 0,
            oldValue = this.rule.value,
            isArr = (0, _util.isArray)(oldValue);
        if (oldValue === '') parseValue = [];else if (!isArr) parseValue = [oldValue];else parseValue = oldValue;
        this.changeParseValue(parseValue);
    },
    getValue: function getValue() {
        return this.rule.props.multiple === true ? this.parseValue : this.parseValue[0] === undefined ? '' : this.parseValue[0];
    }
});

var render = (0, _render.renderFactory)({
    init: function init() {
        var _this = this;

        var field = this.handler.rule.field,
            b = false;
        this.vm.$watch("formData." + field, function () {
            b === true && _this.onChange();
            b = true;
        });
        this._props = this.handler.rule.props;
        this.issetIcon = this._props.handleIcon !== false || this._props.allowRemove === true;
    },
    parse: function parse() {
        var type = this._props.type,
            vNode = void 0;
        if (type === 'image') vNode = this.makeGroup(this.makeImage());else if (type === 'file') vNode = this.makeGroup(this.makeFile());else vNode = this.makeInput();
        return vNode;
    },
    makeInput: function makeInput(hidden) {
        var _this2 = this;

        var unique = this.handler.unique,
            props = this.inputProps().props({
            type: "text",
            value: this.handler.parseValue,
            icon: this._props.icon,
            readonly: true
        }).on('on-click', function () {
            _this2.showModel();
        }).key('ifit' + unique).style({ display: hidden === true ? 'none' : 'inline-block' }).get();
        return [this.cvm.input(props)];
    },
    makeGroup: function makeGroup(render) {
        var unique = this.handler.unique,
            field = this.handler.rule.field;
        return [this.cvm.make('div', { key: "ifgp1" + unique, class: { 'fc-upload': true }, ref: this.handler.refName, props: { value: this.vm.formData[field] } }, render), this.makeInput(true)];
    },
    makeImage: function makeImage() {
        var _this3 = this;

        var unique = this.handler.unique;
        var vNode = this.handler.parseValue.map(function (src, index) {
            return _this3.cvm.make('div', { key: "ifid1" + unique, class: { 'fc-files': true } }, [_this3.cvm.make('img', { key: "ifim" + unique, attrs: { src: src } }), _this3.makeIcons(src, unique, index)]);
        });
        vNode.push(this.makeBtn());
        return vNode;
    },
    makeFile: function makeFile() {
        var _this4 = this;

        var unique = this.handler.unique;
        var vNode = this.handler.parseValue.map(function (src, index) {
            return _this4.cvm.make('div', { key: "iffd2" + unique, class: { 'fc-files': true } }, [_this4.cvm.icon({ key: "iff" + unique, props: { type: "document-text", size: 40 } }), _this4.makeIcons(src, unique, index)]);
        });
        vNode.push(this.makeBtn());
        return vNode;
    },
    makeBtn: function makeBtn() {
        var _this5 = this;

        var props = this.handler.rule.props;
        if (props.maxLength > 0 && this.handler.parseValue.length >= props.maxLength) return;
        var unique = this.handler.unique;
        return this.cvm.make('div', { key: "ifbd3" + unique, class: { 'fc-upload-btn': true }, on: { click: function click() {
                    _this5.showModel();
                } } }, [this.cvm.icon({ key: "ifbi" + unique, props: { type: this._props.icon, size: 20 } })]);
    },
    makeSpin: function makeSpin() {
        if (true !== this._props.spin) return;
        var unique = this.handler.unique;
        return this.cvm.make('Spin', {
            props: { fix: true },
            key: 'ifsp' + unique,
            class: {
                'fc-spin': true
            }
        }, [this.cvm.icon({
            props: {
                type: 'load-c',
                size: 18
            },
            class: {
                'fc-spin-icon-load': true
            },
            key: 'ifspi' + unique
        }), this.cvm.make('div', {
            domProps: {
                innerHTML: '加载中...'
            },
            key: 'ifspd' + unique
        })]);
    },
    makeIcons: function makeIcons(src, key, index) {
        var _this6 = this;

        if (this.issetIcon === true) return this.cvm.make('div', { key: "ifis" + key, class: { 'fc-upload-cover': true } }, function () {
            var icon = [];
            if (_this6._props.handleIcon !== false) icon.push(_this6.makeHandleIcon(src, key, index));
            if (_this6._props.allowRemove === true) icon.push(_this6.makeRemoveIcon(src, key, index));
            return icon;
        });
    },
    makeRemoveIcon: function makeRemoveIcon(src, key, index) {
        var _this7 = this;

        return this.cvm.icon({ key: "ifri" + key + index, props: { type: 'ios-trash-outline' }, nativeOn: { 'click': function click() {
                    _this7.onRemove(src) !== false && _this7.handler.parseValue.splice(index, 1);
                } } });
    },
    makeHandleIcon: function makeHandleIcon(src, key, index) {
        var _this8 = this;

        var props = this._props;
        return this.cvm.icon({ key: "ifhi" + key + index, props: { type: props.handleIcon.toString() }, nativeOn: { 'click': function click() {
                    _this8.onHandle(src);
                } } });
    },
    onOpen: function onOpen() {
        var fn = this.handler.rule.event['on-open'];
        if (fn) return fn(this.handler.getValue());
    },
    onChange: function onChange() {
        var fn = this.handler.rule.event['on-change'];
        if (fn) return fn(this.handler.getValue());
    },
    onOk: function onOk() {
        var fn = this.handler.rule.event['on-ok'];
        if (fn) return fn(this.handler.getValue());
    },
    onRemove: function onRemove(src) {
        var fn = this.handler.rule.event['on-remove'];
        if (fn) return fn(src, this.handler.getValue());
    },
    onHandle: function onHandle(src) {
        var fn = this.handler.rule.event['on-handle'];
        if (fn) return fn(src);else this.defaultOnHandle(src);
    },
    defaultOnHandle: function defaultOnHandle(src) {
        this.vm.$Modal.info({
            title: "预览",
            render: function render(h) {
                return h('img', { attrs: { src: src }, style: "width: 100%" });
            }
        });
    },
    showModel: function showModel() {
        var _this9 = this;

        var isShow = false !== this.onOpen(),
            _props = this._props,
            width = _props.width,
            height = _props.height,
            src = _props.src,
            title = _props.title;

        isShow && this.vm.$Modal.info({
            title: title,
            render: function render() {
                return [_this9.makeSpin(), _this9.cvm.make('iframe', {
                    attrs: {
                        src: src
                    },
                    style: {
                        'height': height,
                        'border': "0 none",
                        'width': "100%"
                    },
                    on: {
                        'load': function load() {
                            _this9._props.spin === true && document.getElementsByClassName('fc-spin')[0].remove();
                        }
                    },
                    key: "ifmd" + (0, _util.uniqueId)()
                })];
            },
            onOk: function onOk() {
                _this9.onOk();
            },
            width: width
        });
    }
});

var make = (0, _make2.default)('frame', ['props', 'event', 'validate']);

var component = { handler: handler, render: render, make: make };

exports.default = component;
exports.handler = handler;
exports.render = render;
exports.make = make;

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _cvm = __webpack_require__(5);

var _cvm2 = _interopRequireDefault(_cvm);

var _props = __webpack_require__(6);

var _props2 = _interopRequireDefault(_props);

var _util = __webpack_require__(1);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var render = function render(_ref) {
    var vm = _ref.vm,
        options = _ref.options,
        fieldList = _ref.fieldList,
        handlers = _ref.handlers,
        formData = _ref.formData,
        validate = _ref.validate,
        fCreateApi = _ref.fCreateApi;

    this.vm = vm;
    this.options = options;
    this.handlers = handlers;
    this.renderSort = fieldList;
    this.renders = this.renderSort.reduce(function (initial, field) {
        initial[field] = handlers[field].render;
        return initial;
    }, {});
    this.form = {
        model: formData,
        rules: validate
    };
    this.fCreateApi = fCreateApi;
    this.cvm = _cvm2.default.instance(vm.$createElement);
    this.props = _props2.default.instance();
    this.unique = (0, _util.uniqueId)();
    this.refName = 'cForm' + this.unique;
};

render.prototype = {
    parse: function parse(vm) {
        var _this = this;

        _cvm2.default.setVm(vm);
        var unique = this.unique,
            propsData = this.props.props(Object.assign({}, this.options.form, this.form)).ref(this.refName).class('form-create', true).key(unique).get(),
            vn = this.renderSort.map(function (field) {
            var render = _this.renders[field],
                _render$handler = render.handler,
                key = _render$handler.key,
                type = _render$handler.rule.type;
            if (type !== 'hidden') return _this.makeFormItem(render.handler, render.parse(), 'fItem' + key + unique);
        });
        if (false !== this.options.submitBtn) vn.push(this.makeSubmitBtn(unique));
        return this.cvm.form(propsData, vn);
    },
    makeFormItem: function makeFormItem(_ref2, VNodeFn) {
        var rule = _ref2.rule,
            refName = _ref2.refName,
            unique = _ref2.unique;

        var propsData = this.props.props({
            prop: rule.field,
            label: rule.title,
            labelFor: refName,
            rules: rule.validate
        }).key(unique).get();
        return this.cvm.formItem(propsData, VNodeFn);
    },
    makeSubmitBtn: function makeSubmitBtn(unique) {
        var _this2 = this;

        return this.cvm.button({ key: 'fbtn' + unique, props: this.vm.buttonProps, on: { "click": function click() {
                    _this2.fCreateApi.submit();
                } } }, [this.cvm.span(this.options.submitBtn.innerText)]);
    },
    removeRender: function removeRender(field) {
        delete this.renders[field];
        this.renderSort.splice(this.renderSort.indexOf(field), 1);
    },
    setRender: function setRender(handler, after, pre) {
        this.renders[handler.rule.field] = handler.render;
        if (after !== undefined) this.changeSort(handler.rule.field, after, pre);
    },
    changeSort: function changeSort(field, after, pre) {
        var index = this.renderSort.indexOf(after.toString());
        if (index !== -1) this.renderSort.splice(pre === false ? index + 1 : index, 0, field);else if (!pre) this.renderSort.push(field);else this.renderSort.unshift(field);
    }
};

exports.default = render;

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
var formCreateName = 'form-create';

var formCreateComponent = function formCreateComponent(fComponent) {
    return {
        name: formCreateName,
        data: function data() {
            return {
                formData: {},
                buttonProps: {}
            };
        },

        render: function render() {
            return fComponent.fRender.parse(fComponent.vm);
        },
        created: function created() {
            fComponent.init(this);
        },

        methods: {
            changeFormData: function changeFormData(field, value) {
                this.$set(this.formData, field, value);
            },
            removeFormData: function removeFormData(field) {
                this.$delete(this.formData, field);
            },
            changeButtonProps: function changeButtonProps(props) {
                this.$set(this, 'buttonProps', Object.assign(this.buttonProps, props));
            },
            setField: function setField(field, value) {
                this.$set(this.formData, field, value);
            }
        },
        mounted: function mounted() {
            Object.keys(this.formData).map(function (field) {
                fComponent.addHandlerWatch(fComponent.handlers[field]);
                fComponent.handlers[field].mounted();
            });
        }
    };
};

exports.default = formCreateComponent;

/***/ })
/******/ ]);
});