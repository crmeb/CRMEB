<template>
	<view>
	<view :style="{height:footHeight+'px'}"></view>
	<view>
		<view class="page-footer" id="target" :style="{'background-color':configData.bgColor.color[0].item}">
			<view class="foot-item" v-for="(item,index) in configData.menuList" :key="index" @click="goRouter(item)">
				<block v-if="item.link == activeRouter">
					<image :src="item.imgList[0]"></image>
					<view class="txt" :style="{color:configData.activeTxtColor.color[0].item}">{{item.name}}</view>
				</block>
				<block v-else>
					<image :src="item.imgList[1]"></image>
					<view class="txt" :style="{color:configData.txtColor.color[0].item}">{{item.name}}</view>
				</block>
			</view>
		</view>
	</view>
	</view>
</template>

<script>
	import {mapState} from "vuex"
	import { getNavigation } from '@/api/public.js'
	export default{
		name:'pageFooter',
		props:{
			curTitle:{
				type:String | Number
			}
		},
		computed:{
			...mapState({
				configData: state => state.app.pageFooter
			})
		},
		watch:{
			configData:{
				handler (nVal, oVal) {
					const query = uni.createSelectorQuery().in(this);
					this.newData = nVal
					this.$nextTick(()=>{
						query.select('#target').boundingClientRect(data => {
							this.footHeight = data.height+20
						}).exec();
					})
				},
				deep: true
			}
		},
		created(){
			getNavigation().then(res=>{
				uni.setStorageSync('pageFoot',res.data)
				this.$store.commit('FOOT_UPLOAD',res.data)
			})
		},
		mounted() {
			let that = this
			let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
			let curRoute = routes[routes.length - 1].route //获取当前页面路由
			this.activeRouter = '/'+curRoute
			this.newData = this.$store.state.app.pageFooter
			
			
			
			
			
			
		},
		data(){
			return {
				newData:{},
				activeRouter:'/',
				footHeight:0
			}
		},
		methods:{
			goRouter(item){
				if(item.name == this.curTitle){
					return
				}
				uni.reLaunch({
					url:item.link
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
.page-footer{
			position: fixed;
			bottom: 0;
			z-index: 30;
			display: flex;
			align-items: center;
			width: 100%;
			height: calc(98rpx+ constant(safe-area-inset-bottom));///兼容 IOS<11.2/
			height: calc(98rpx + env(safe-area-inset-bottom));///兼容 IOS>11.2/
			box-sizing: border-box;
			border-top: solid 1rpx #F3F3F3;
			background-color: #fff;
			box-shadow: 0px 0px 17rpx 1rpx rgba(206, 206, 206, 0.32);
			padding-bottom: constant(safe-area-inset-bottom);///兼容 IOS<11.2/
			padding-bottom: env(safe-area-inset-bottom);///兼容 IOS>11.2/
			.foot-item {
				flex: 1;
				display: flex;
				align-items: center;
				justify-content: center;
				flex-direction: column;
			}
	
			.foot-item image {
				height: 50rpx;
				width: 50rpx;
				text-align: center;
				margin: 0 auto;
			}
	
			.foot-item .txt {
				font-size: 24rpx;
				
	
				&.active {
				
				}
			}
}
</style>
