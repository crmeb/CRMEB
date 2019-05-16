var app = getApp();
Component({
  properties: {
    attribute: {
      type: Object,
      value:{}
    },
    attrList:{
      type: Object,
      value:[],
    },
    productAttr:{
      type: Object,
      value: [],
    },
    productSelect:{
      type: Object,
      value: {
        image: '',
        store_name: '',
        price: 0,
        unique: '',
        stock:0,
      }
    },
  },
  data: {
    attrValue:[],
    attrIndex:0,
  },
  attached: function () {
  },
  methods: {
    close: function () {
      this.triggerEvent('myevent', {'window': false});
    },
    CartNumDes:function(){
      this.triggerEvent('ChangeCartNum', false);
    },
    CartNumInt:function(){
      this.triggerEvent('ChangeCartNum', true);
    },
    tapAttr:function(e){
      //父级index
      var indexw = e.currentTarget.dataset.indexw;
      //子集index
      var indexn = e.currentTarget.dataset.indexn;
      //每次点击获得的属性
      var attr = this.data.productAttr[indexw].attr_value[indexn];
      //设置当前点击属性
      this.data.productAttr[indexw].checked = attr['attr'];
      this.setData({
        productAttr: this.data.productAttr,
      });
      var value = this.getCheckedValue().sort().join(',');
      this.triggerEvent('ChangeAttr',value);
    },
    getCheckedValue: function () {
      return this.data.productAttr.map(function (attr) {
        return attr.checked;
      });
    },
    ResetAttr:function(){
      for (var k in this.data.productAttr) this.data.productAttr[k].checked='';
      this.setData({ productAttr: this.data.productAttr});
    },
  }
})