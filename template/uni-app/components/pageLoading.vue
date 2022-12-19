<template>
	<view class="load" v-if="status">
		{{$t(`正在加载中`)}}
	</view>
</template>

<script>
	export default{
		data(){
			return {
				status:false
			}
		},
		mounted() {
			this.status = uni.getStorageSync('loadStatus')
			uni.$once('loadClose',()=>{
				this.status = false
			})
		}
	}
</script>

<style lang="scss">
.load{
	z-index: 99999;
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: #fff;
}
</style>
