(function (global, factory) {
    define && define.amd && define(factory);
    // global.$r = factory();
})(this, function () {
    var r = function r(h) {
        this.h = h;
    };

    var t = function t() {
        this.data = this._initData();
    };

    r.prototype = {
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
        datePicker: function datePicker(data, VNodeFn) {
            return this.make('Date-Picker', data, VNodeFn);
        },
        timePicker: function timePicker(data, VNodeFn) {
            return this.make('Time-Picker', data, VNodeFn);
        },
        colorPicker: function colorPicker(data, VNodeFn) {
            return this.make('Color-Picker', data, VNodeFn);
        },
        upload: function upload(data, VNodeFn) {
            return this.make('Upload', data, VNodeFn);
        },
        span: function span(data, VNodeFn) {
            if (typeof data == 'string') data = { domProps: { innerHTML: data } };
            return this.make('span', data, VNodeFn);
        },
        icon: function icon(data, VNodeFn) {
            return this.make('Icon', data, VNodeFn);
        },
        button: function button(data, VNodeFn) {
            return this.make('i-button', data, VNodeFn);
        },
        make: function make(nodeName, data, VNodeFn) {
            return this.h(nodeName, data, this.getVNode(VNodeFn));
        },
        more: function more() {
            var vNodeList = [];

            for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
                args[_key] = arguments[_key];
            }

            args.map(function (arg) {
                vNodeList.push(arg);
            });
            return vNodeList;
        },
        getVNode: function getVNode(VNode) {
            return typeof VNode == 'function' ? VNode() : VNode;
        },
        $t: function $t() {
            return t;
        }
    };

    t.prototype = {
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
        class: function _class(opt, status) {
            status !== undefined ? this.data.class[opt] = status : this.data.class = opt;
            return this;
        },
        style: function style(opt, status) {
            status !== undefined ? this.data.style[opt] = status : this.data.style = opt;
            return this;
        },
        attrs: function attrs(opt, value) {
            value !== undefined ? this.data.attrs[opt] = value : this.data.attrs = opt;
            return this;
        },
        props: function props(opt, value) {
            value !== undefined ? this.data.props[opt] = value : this.data.props = opt;
            return this;
        },
        domProps: function domProps(opt, value) {
            value !== undefined ? this.data.domProps[opt] = value : this.data.domProps = opt;
            return this;
        },
        on: function on(opt, call) {
            call !== undefined ? this.data.on[opt] = call : this.data.on = opt;
            return this;
        },
        nativeOn: function nativeOn(opt, call) {
            call !== undefined ? this.data.nativeOn[opt] = call : this.data.nativeOn = opt;
            return this;
        },
        directives: function directives(opt) {
            this.data.directives.push(opt);
            return this;
        },
        scopedSlots: function scopedSlots(opt, call) {
            call !== undefined ? this.data.scopedSlots[opt] = call : this.data.scopedSlots = opt;
            return this;
        },
        slot: function slot(value) {
            this.data.slot = value;
            return this;
        },
        key: function key(value) {
            this.data.key = value;
            return this;
        },
        ref: function ref(value) {
            this.data.ref = value;
            return this;
        },
        init: function init() {
            this.data = this._initData();
        },
        get: function get() {
            var data = this.data;
            this.init();
            return data;
        }
    };

    return r;
});

//# sourceMappingURL=mpBuilder-compiled.js.map