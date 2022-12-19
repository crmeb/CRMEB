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

<script>
	import LotteryDraw from './js/grids_lottery.js';
	export default {
		data() {
			return {
				current_index: -1,
				lotteryBtn: true
			};
		},
		props: {
			prizeData: {
				type: Array,
				default: function() {
					return []
				}
			},
		},
		onLoad() {

		},

		methods: {
			luck_draw(event) {
				if (this.lotteryBtn) {
					this.lotteryBtn = false
				} else {
					return
				}
				let index = event.currentTarget.dataset.index;
				let that = this;
				if (index == 8) {
					// 点击抽奖之后知道获奖位置，修改父组件中lottery_draw_param的值
					this.$emit('get_winingIndex', function(res) {
						let lottery_draw_param = res;
						let win = new LotteryDraw({
								domData: that.prizeData,
								...lottery_draw_param
							},
							function(index, count) {
								that.current_index = index;
								if (lottery_draw_param.winingIndex == index && lottery_draw_param.totalCount ==
									count) {
									that.lotteryBtn = true
									that.$emit('luck_draw_finish', that.prizeData[index])
								}
							}
						);
					});

				}
			}
		}
	};
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
