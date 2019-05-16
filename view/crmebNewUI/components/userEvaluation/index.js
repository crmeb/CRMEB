var app = getApp();
Component({
  properties: {
    reply:{
      type:Object,
      value:[],
    }
  },
  data: {
    
  },
  attached: function () {

  },
  methods: {
    getpreviewImage:function(e){
      var dataset=e.currentTarget.dataset;
      wx.previewImage({ 
        urls: this.data.reply[dataset.index].pics, 
        current: this.data.reply[dataset.index].pics[dataset.pic_index],
      });
    },
  }
})