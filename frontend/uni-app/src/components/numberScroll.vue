<template>
	<view class="number-box">
		<block v-for="(myIndex, index) in indexArr" :key="index">
			<swiper class="swiper" vertical="true" :current="myIndex" circular="true"
				v-bind:style="{color:color,width:myIndex == 10  ? '14rpx' : myIndex == 1 ? '22rpx' : width+'rpx',height:height+'rpx',lineHeight:fontSize+'rpx',fontSize:fontSize+'rpx',fontWeight: fontWeight}">
				<swiper-item>
					<view class="swiper-item">0</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">1</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">2</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">3</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">4</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">5</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">6</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">7</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">8</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">9</view>
				</swiper-item>
				<swiper-item>
					<view class="swiper-item">.</view>
				</swiper-item>
			</swiper>
		</block>
	</view>
</template>

<script setup>
	import { ref, watch, onMounted, nextTick } from 'vue';

	const props = defineProps({
		num: [String, Number],
		color: {
			type: String,
			default: '#000000'
		},
		width: {
			type: String,
			default: '30'
		},
		height: {
			type: String,
			default: '30'
		},
		fontSize: {
			type: String,
			default: '30'
		},
		fontWeight: {
			type: [String, Number],
			default: 500
		}
	});

	const indexArr = ref([]);

	// created：按初始 num 长度初始化占位数组
	let arr = new Array(props.num.toString().length);
	arr.fill(0);
	indexArr.value = arr;

	watch(() => props.num, function(val, oldVal) {
		// 处理新老数据长度不一样的情况
		let arr = Array.prototype.slice.apply(indexArr.value);
		let newLen = val.toString().length;
		let oldLen = oldVal.toString().length;
		if (newLen > oldLen) {
			for (let i = 0; i < newLen - oldLen; i++) {
				arr.push(0);
			}
			indexArr.value = arr;
		}
		if (newLen < oldLen) {
			for (let i = 0; i < oldLen - newLen; i++) {
				arr.pop();
			}
			indexArr.value = arr;
		}
		numChange(val);
	})

	onMounted(() => {
		//定时器作用：app显示数字滚动
		let _time = setTimeout(() => {
			numChange(props.num);
			clearTimeout(_time);
		}, 50);
	})

	/**
	 * 数字改变
	 * @value 数字
	 */
	function numChange(num) {
		nextTick(() => {
			let copyIndexArr = Array.prototype.slice.apply(indexArr.value);
			let _num = num.toString();
			for (let i = 0; i < _num.length; i++) {
				if (_num[i] === '.') {
					copyIndexArr[i] = 10;
				} else {
					copyIndexArr[i] = Number(_num[i]);
				}
			}
			indexArr.value = copyIndexArr;
		})
	}
</script>

<style lang="scss">
	.number-box {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
	}

	.swiper {
		position: relative;
		// 	line-height: 30upx;
		// 	width: 30upx;
		// 	height: 30upx;
		// 	font-size: 30upx;
		// 	background: red;
	}

	.swiper:after {
		content: '';
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
	}
</style>
