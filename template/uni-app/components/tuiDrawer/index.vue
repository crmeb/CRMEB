<template>
	<view class="tui-swipeout-wrap" :style="{ backgroundColor: backgroundColor }">
		<view class="tui-swipeout-item" :class="[isShowBtn ? 'swipe-action-show' : '']"
			:style="{ transform: 'translate(' + position.pageX + 'px,0)' }">
			<view class="tui-swipeout-content" @touchstart="handlerTouchstart" @touchmove="handlerTouchmove"
				@touchend="handlerTouchend" @mousedown="handlerTouchstart" @mousemove="handlerTouchmove"
				@mouseup="handlerTouchend">
				<slot name="content"></slot>
			</view>
			<view class="tui-swipeout-button-right-group" v-if="actions.length > 0" @touchend.stop="loop"
				:style="colorStyle">
				<view class="tui-swipeout-button-right-item" v-for="(item, index) in actions" :key="index"
					:style="{ backgroundColor: index == 0 ? 'var(--view-theme)' : '#ccc', color: index == 1 ? '#f2f2f2' : '#f2f2f2', width: item.width + 'px' }"
					:data-index="index" @tap="handlerButton">
					<image :src="item.icon" v-if="item.icon"
						:style="{ width: px(item.imgWidth), height: px(item.imgHeight) }"></image>
					<text :style="{ fontSize: px(item.fontsize) }">{{ item.name }}</text>
				</view>
			</view>
			<!--actions长度设置为0，可直接传按钮进来-->
			<view class="tui-swipeout-button-right-group" @touchend.stop="loop" @tap="handlerParentButton"
				v-if="actions.length === 0" :style="{ width: operateWidth + 'px', right: '-' + operateWidth + 'px' }">
				<slot name="button"></slot>
			</view>
		</view>
		<view v-if="isShowBtn && showMask" class="swipe-action_mask" @tap.stop="closeButtonGroup"
			@touchstart.stop.prevent="closeButtonGroup" />
	</view>
</template>

<script>
	import colors from '@/mixins/color';
	export default {
		name: 'tuiSwipeAction',
		emits: ['click'],
		mixins: [colors],
		props: {
			// name: '删除',
			// color: '#fff',
			// fontsize: 32,//单位rpx
			// width: 80, //单位px
			// icon: 'like.png',//此处为图片地址
			// background: '#ed3f14'
			actions: {
				type: Array,
				default () {
					return [];
				}
			},
			//点击按钮时是否自动关闭
			closable: {
				type: Boolean,
				default: true
			},
			//设为false，可以滑动多行不关闭菜单
			showMask: {
				type: Boolean,
				default: true
			},
			operateWidth: {
				type: Number,
				default: 80
			},
			params: {
				type: Object,
				default () {
					return {};
				}
			},
			//禁止滑动
			forbid: {
				type: Boolean,
				default: false
			},
			//手动开关
			open: {
				type: Boolean,
				default: false
			},
			//背景色
			backgroundColor: {
				type: String,
				default: '#fff'
			}
		},
		watch: {
			actions(newValue, oldValue) {
				this.updateButtonSize();
			},
			open(newValue) {
				this.manualSwitch(newValue);
			}
		},
		data() {
			return {
				//start position
				tStart: {
					pageX: 0,
					pageY: 0
				},
				//限制滑动距离
				limitMove: 0,
				//move position
				position: {
					pageX: 0,
					pageY: 0
				},
				isShowBtn: false,
				move: false
			};
		},
		mounted() {
			this.updateButtonSize();
		},
		methods: {
			swipeDirection(x1, x2, y1, y2) {
				return Math.abs(x1 - x2) >= Math.abs(y1 - y2) ? (x1 - x2 > 0 ? 'Left' : 'Right') : y1 - y2 > 0 ? 'Up' :
					'Down';
			},
			//阻止事件冒泡
			loop() {},
			updateButtonSize() {
				const actions = this.actions;
				if (actions.length > 0) {
					const query = uni.createSelectorQuery().in(this);
					let limitMovePosition = 0;
					actions.forEach(item => {
						limitMovePosition += item.width || 0;
					});
					this.limitMove = limitMovePosition;
				} else {
					this.limitMove = this.operateWidth;
				}
			},
			handlerTouchstart(event) {
				if (this.forbid) return;
				let touches = event.touches
				if (touches && touches.length > 1) return;
				this.move = true;
				touches = touches ? event.touches[0] : {};
				if (!touches || (touches.pageX === undefined && touches.pageY === undefined)) {
					touches = {
						pageX: event.pageX,
						pageY: event.pageY
					};
				}
				const tStart = this.tStart;
				if (touches) {
					for (let i in tStart) {
						if (touches[i]) {
							tStart[i] = touches[i];
						}
					}
				}
			},
			swipper(touches) {
				const start = this.tStart;
				const spacing = {
					pageX: touches.pageX - start.pageX,
					pageY: touches.pageY - start.pageY
				};
				if (this.limitMove < Math.abs(spacing.pageX)) {
					spacing.pageX = -this.limitMove;
				}
				this.position = spacing;
			},
			handlerTouchmove(event) {
				if (this.forbid || !this.move) return;
				const start = this.tStart;
				let touches = event.touches ? event.touches[0] : {};
				if (!touches || (touches.pageX === undefined && touches.pageY === undefined)) {
					touches = {
						pageX: event.pageX,
						pageY: event.pageY
					};
				}
				if (touches) {
					const direction = this.swipeDirection(start.pageX, touches.pageX, start.pageY, touches.pageY);
					if (direction === 'Left' && Math.abs(this.position.pageX) !== this.limitMove) {
						this.swipper(touches);
					}
				}
			},
			handlerTouchend(event) {
				if (this.forbid || !this.move) return;
				this.move = false;
				const start = this.tStart;
				let touches = event.changedTouches ? event.changedTouches[0] : {};
				if (!touches || (touches.pageX === undefined && touches.pageY === undefined)) {
					touches = {
						pageX: event.pageX,
						pageY: event.pageY
					};
				}
				if (touches) {
					const direction = this.swipeDirection(start.pageX, touches.pageX, start.pageY, touches.pageY);
					const spacing = {
						pageX: touches.pageX - start.pageX,
						pageY: touches.pageY - start.pageY
					};
					if (Math.abs(spacing.pageX) >= 40 && direction === 'Left') {
						spacing.pageX = spacing.pageX < 0 ? -this.limitMove : this.limitMove;
						this.isShowBtn = true;
					} else {
						spacing.pageX = 0;
					}
					if (spacing.pageX == 0) {
						this.isShowBtn = false;
					}
					this.position = spacing;

				}
			},
			handlerButton(event) {
				if (this.closable) {
					this.closeButtonGroup();
				}
				const dataset = event.currentTarget.dataset;
				this.$emit('click', {
					index: Number(dataset.index),
					item: this.params
				});
			},
			closeButtonGroup() {
				this.position = {
					pageX: 0,
					pageY: 0
				};
				this.isShowBtn = false;
			},
			//控制自定义按钮菜单
			handlerParentButton(event) {
				if (this.closable) {
					this.closeButtonGroup();
				}
			},
			manualSwitch(isOpen) {
				let x = 0;
				if (isOpen) {
					if (this.actions.length === 0) {
						x = this.operateWidth;
					} else {
						let width = 0;
						this.actions.forEach(item => {
							width += item.width;
						});
						x = width;
					}
				}
				this.position = {
					pageX: -x,
					pageY: 0
				};
			},
			px(num) {
				return uni.upx2px(num) + 'px';
			}
		}
	};
</script>

<style scoped>
	.tui-swipeout-wrap {
		position: relative;
		overflow: hidden;
		/* margin-bottom: 24rpx; */

	}


	.swipe-action-show {
		position: relative;
		z-index: 998;
	}

	.tui-swipeout-item {
		width: 100%;
		/* padding: 15px 20px; */
		box-sizing: border-box;
		transition: transform 0.2s ease;
		font-size: 14px;
		/* cursor: pointer; */
	}

	/* .tui-swipeout-item :active {
    background-color: #fff !important;
  } */

	.tui-swipeout-content {
		white-space: nowrap;
		overflow: hidden;
	}

	.tui-swipeout-button-right-group {
		position: absolute;
		right: -100%;
		top: 0;
		height: 100%;
		z-index: 1;
		width: 100%;
	}

	.tui-swipeout-button-right-item {
		height: 100%;
		float: left;
		white-space: nowrap;
		box-sizing: border-box;
		display: flex;
		align-items: center;
		justify-content: center;
		text-align: center;
	}

	.swipe-action_mask {
		display: block;
		opacity: 0;
		position: fixed;
		z-index: 997;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}
</style>
