<template>
	<view class="content">
		<jyf-parser :html="content" ref="article" :tag-style="tagStyle"></jyf-parser>
	</view>
</template>

<script>
	import parser from "@/components/jyf-parser/jyf-parser";
	import {
		getUserAgreement,
	} from '@/api/user.js';
	export default {
		components: {
			"jyf-parser": parser
		},
		data() {
			return {
				tagStyle: {
					img: 'width:100%;display:block;',
					table: 'width:100%',
					video: 'width:100%'
				},
				content: ``
			}
		},
		mounted() {
			getUserAgreement().then(res => {
				this.content = res.data.content
			}).catch(err => {
				that.$util.Tips({
					title: err.msg
				});
			})
		}
	}
</script>

<style scoped>
	page{
		background-color: #fff;
	}
	.content {
		padding: 40rpx 30rpx;
		line-height: 2;
	}
</style>
