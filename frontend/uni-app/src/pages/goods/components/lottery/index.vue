<template>
	<view class="lottery_container">
		<view class="grid_wrap">
			<view class="lottery_wrap">
				<ul class="lottery_grid">
					<li v-for="(item, index) in prizeData" :class="{ active: current_index == index && index != 8 }"
						:key="index" @click="luck_draw" :data-index="index">
						<view :class="{in_line:index != 8 }" class="lottery-msg">
							<image v-if="index != 8" class="grid_img" mode='aspectFit' :src="item.image" alt="" />
							<text v-if="index !=8" class="name">
								{{ index == 8 ? $t(`抽奖`) : item.name }}
							</text>
							<image v-else class="lottery-click" src="../../static/lottery-click.png" mode="">
							</image>
						</view>
					</li>
				</ul>
			</view>
		</view>
	</view>

</template>

<script setup>
	import { ref } from 'vue';
	import LotteryDraw from './js/grids_lottery.js';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';

	const props = defineProps({
		prizeData: {
			type: Array,
			default: function() {
				return []
			}
		},
		lotteryNum: {
			type: [Number, String],
			default: 0
		}
	});

	const emit = defineEmits(['get_winingIndex', 'luck_draw_finish']);

	const current_index = ref(-1);
	const lotteryBtn = ref(true);

	function luck_draw(event) {
		if (Number(props.lotteryNum) <= 0) {
			return util.Tips({
				title: i18n.t(`剩余抽奖次数为0`)
			});
		} else if (lotteryBtn.value) {
			lotteryBtn.value = false
		} else {
			return
		}
		let index = event.currentTarget.dataset.index;
		if (index == 8) {
			// 点击抽奖之后知道获奖位置，修改父组件中lottery_draw_param的值
			emit('get_winingIndex', function(res) {
				let lottery_draw_param = res;
				let win = new LotteryDraw({
						domData: props.prizeData,
						...lottery_draw_param
					},
					function(index, count) {
						current_index.value = index;
						if (lottery_draw_param.winingIndex == index && lottery_draw_param.totalCount ==
							count) {
							lotteryBtn.value = true
							emit('luck_draw_finish', props.prizeData[index])
						}
					}
				);
			});

		}
	}
</script>

<style scoped lang="scss">
	@import './css/grids_lottery.css';

	.lottery-msg {
		width: 100%;
		height: 100%;
		padding: 0 4rpx;

		.name {}
	}

	.lottery-click {
		width: 100%;
		height: 100%;
	}
</style>
