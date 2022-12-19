<template>
	<view>
		<view class="title">{{agreement.title}}</view>
		<view class="cont" v-html="agreement.content"></view>
	</view>
</template>

<script>
	import {
		memberCard
	} from '@/api/user.js';

	export default {
		data() {
			return {
				agreement: ''
			}
		},
		onLoad() {
			this.memberCard();
		},
		methods: {
			memberCard() {
				uni.showLoading({
					title: this.$t(`正在加载中`)
				});
				memberCard().then(res => {
					uni.hideLoading();
					const {
						member_explain
					} = res.data;
					this.agreement = member_explain;
				}).catch(err => {
					uni.hideLoading();
				});
			}
		}
	}
</script>

<style>
	page {
		background-color: #FFFFFF;
	}
</style>

<style scoped lang="scss">
	.title {
		padding-top: 60rpx;
		font-size: 30rpx;
		text-align: center;
	}

	.cont {
		padding: 50rpx 30rpx;
	    /deep/ img {
			max-width: 100% !important; 
		}
	}
	
	
</style>
