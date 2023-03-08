<template>
	<diy ref="diy" v-if="isDiy && loading"></diy>
	<visualization ref='vis' v-else-if="!isDiy && loading"></visualization>
</template>

<script>
	import diy from './diy'
	import visualization from './visualization'
	import Cache from '@/utils/cache';
	import {
		getShare,
		getVersion
	} from "@/api/public.js";
	let app = getApp();
	export default {
		data() {
			return {
				isDiy: uni.getStorageSync('is_diy'),
				shareInfo: {},
				loading: false,
			}
		},
		components: {
			diy,
			visualization
		},
		onLoad() {
			uni.hideTabBar()
			uni.$on('is_diy', (data) => {
				this.isDiy = data
			})
			this.setOpenShare();
		},
		onShow() {
			this.getVersion(0);
		},
		onHide() {
			// this.isDiy = -1
		},
		methods: {
			getVersion(name) {
				getVersion(name).then(res => {
					this.version = res.data.version
					this.isDiy = res.data.is_diy
					this.loading = true
					uni.setStorageSync('is_diy', res.data.is_diy)
					if (!uni.getStorageSync('DIY_VERSION') || res.data.version != uni.getStorageSync(
							'DIY_VERSION')) {
						if (uni.getStorageSync('DIY_VERSION')) {
							uni.setStorageSync('DIY_VERSION', res.data.version)
							if (this.isDiy) {
								this.$refs.diy.reconnect()
							} else {
								this.$refs.vis.reconnect()
							}
						}
						uni.setStorageSync('DIY_VERSION', res.data.version)
					} else {}
				}).catch(err => {
					this.loading = true
				})
			},
			// 微信分享；
			setOpenShare: function() {
				let that = this;
				getShare().then((res) => {
					let data = res.data;
					this.shareInfo = data;
					// #ifdef H5
					let url = location.href;
					if (this.$store.state.app.uid) {
						url =
							url.indexOf("?") === -1 ?
							url + "?spread=" + this.$store.state.app.uid :
							url + "&spread=" + this.$store.state.app.uid;
					}
					if (that.$wechat.isWeixin()) {
						let configAppMessage = {
							desc: data.synopsis,
							title: data.title,
							link: url,
							imgUrl: data.img,
						};
						that.$wechat.wechatEvevt(
							["updateAppMessageShareData", "updateTimelineShareData"],
							configAppMessage
						);
					}
					// #endif
				});
			},
		},
		onReachBottom: function() {
			if (this.isDiy) {
				this.$refs.diy.onsollBotton()
			}
		},
		// 滚动监听
		onPageScroll(e) {
			// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
			uni.$emit('scroll');
		},
		// #ifdef MP
		//发送给朋友
		onShareAppMessage(res) {
			// 此处的distSource为分享者的部分信息，需要传递给其他人
			let that = this;
			return {
				title: this.shareInfo.title,
				path: "/pages/index/index?spid=" + this.$store.state.app.uid || 0,
				imageUrl: this.shareInfo.img,
			};
		},
		//分享到朋友圈
		onShareTimeline() {
			return {
				title: this.shareInfo.title,
				query: {
					spid: this.$store.state.app.uid || 0
				},
				imageUrl: this.shareInfo.img,
			};
		},
		// #endif
	}
</script>

<style>

</style>
