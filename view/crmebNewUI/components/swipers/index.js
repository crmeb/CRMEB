// components/swiper/index.js
var app = getApp();
Component({
  properties: {
    imgUrls: {
      type: Object,
      value: []
    }
  },
  data: {
    circular: true,
    autoplay: true,
    interval: 3000,
    duration: 500,
    currentSwiper: 0
  },
  attached: function () {
    console.log(this.data.imgUrls);
  },
  methods: {
    swiperChange: function (e) {
      this.setData({
        currentSwiper: e.detail.current
      })
    },
  }
})