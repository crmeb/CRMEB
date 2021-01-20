<template>
  <div class="CustomerList">
    <div
      class="item acea-row row-between-wrapper"
      v-for="item in list"
      :key="item.id"
      @click="goPage(item)"
    >
      <div class="pictrue"><img :src="item.avatar" /></div>
      <div class="text line1">{{ item.nickname }}</div>
    </div>
	 <home></home>
  </div>
</template>
<script>
import { serviceList } from "@/api/user";
import home from '@/components/home';

export default {
  name: "CustomerList",
  components:{
	  home
  },
  data() {
    return {
      list: [],
      productId: 0,
      orderId: ""
    };
  },
  methods: {
    getList() {
      serviceList().then(res => {
        this.list = res.data;
      });
    },
	goPage(item){
		uni.navigateTo({
			url:'/pages/customer_list/chat?uid='+item.uid+'&productId='+ this.productId+'&orderId='+this.orderId
		})
	}
  },
  onLoad(option) {
    this.getList();
    if (option.productId)
      this.productId = option.productId;
	if (option.orderId) {
		this.orderId = option.orderId
	} 
  }
};
</script>
<style scoped>
.CustomerList {
  margin-top:13rpx;
}
.CustomerList .item {
  height: 138rpx;
  border-bottom: 1px solid #eee;
  padding: 0 24rpx;
  background-color: #fff;
}
.CustomerList .item .pictrue {
  width: 90rpx;
  height: 90rpx;
  border-radius: 50%;
  border: 3rpx solid #fff;
  box-shadow: 0 0 1rpx 5rpx #f3f3f3;
}
.CustomerList .item .pictrue img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.CustomerList .item .text {
  width: 582rpx;
  font-size: 32rpx;
  color: #000;
}
</style>
<template>
  <div class="CustomerList">
    <div
      class="item acea-row row-between-wrapper"
      v-for="item in list"
      :key="item.id"
      @click="goPage(item)"
    >
      <div class="pictrue"><img :src="item.avatar" /></div>
      <div class="text line1">{{ item.nickname }}</div>
    </div>
	 <home></home>
  </div>
</template>
<script>
import { serviceList } from "@/api/user";
import home from '@/components/home';

export default {
  name: "CustomerList",
  components:{
	  home
  },
  data() {
    return {
      list: [],
      productId: 0,
      orderId: ""
    };
  },
  methods: {
    getList() {
      serviceList().then(res => {
        this.list = res.data;
      });
    },
	goPage(item){
		uni.navigateTo({
			url:'/pages/customer_list/chat?uid='+item.uid+'&productId='+ this.productId+'&orderId='+this.orderId
		})
	}
  },
  onLoad(option) {
    this.getList();
    if (option.productId)
      this.productId = option.productId;
	if (option.orderId) {
		this.orderId = option.orderId
	} 
  }
};
</script>
<style scoped>
.CustomerList {
  margin-top:13rpx;
}
.CustomerList .item {
  height: 138rpx;
  border-bottom: 1px solid #eee;
  padding: 0 24rpx;
  background-color: #fff;
}
.CustomerList .item .pictrue {
  width: 90rpx;
  height: 90rpx;
  border-radius: 50%;
  border: 3rpx solid #fff;
  box-shadow: 0 0 1rpx 5rpx #f3f3f3;
}
.CustomerList .item .pictrue img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.CustomerList .item .text {
  width: 582rpx;
  font-size: 32rpx;
  color: #000;
}
</style>
