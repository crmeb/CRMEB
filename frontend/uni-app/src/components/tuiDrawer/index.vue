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
					<text :style="{ fontSize: px(item.fontsize) }">{{ $t(item.name) }}</text>
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

<script setup>
	import {
		ref,
		reactive,
		watch,
		onMounted,
		getCurrentInstance
	} from 'vue';
	import {
		useColorTheme
	} from '@/composables/useColorTheme';

	defineOptions({
		name: 'tuiSwipeAction'
	});

	const props = defineProps({
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
	});

	const emit = defineEmits(['click']);

	const {
		colorStyle
	} = useColorTheme();

	const instance = getCurrentInstance();

	// ---------- data ----------
	//start position
	const tStart = reactive({
		pageX: 0,
		pageY: 0
	});
	//限制滑动距离
	const limitMove = ref(0);
	//move position
	const position = ref({
		pageX: 0,
		pageY: 0
	});
	const isShowBtn = ref(false);
	const move = ref(false);

	// ---------- watch ----------
	watch(() => props.actions, (newValue, oldValue) => {
		updateButtonSize();
	});
	watch(() => props.open, (newValue) => {
		manualSwitch(newValue);
	});

	// ---------- mounted ----------
	onMounted(() => {
		updateButtonSize();
	});

	// ---------- methods ----------
	function swipeDirection(x1, x2, y1, y2) {
		return Math.abs(x1 - x2) >= Math.abs(y1 - y2) ? (x1 - x2 > 0 ? 'Left' : 'Right') : y1 - y2 > 0 ? 'Up' :
			'Down';
	}
	//阻止事件冒泡
	function loop() {}
	function updateButtonSize() {
		const actions = props.actions;
		if (actions.length > 0) {
			const query = uni.createSelectorQuery().in(instance?.proxy);
			let limitMovePosition = 0;
			actions.forEach(item => {
				limitMovePosition += item.width || 0;
			});
			limitMove.value = limitMovePosition;
		} else {
			limitMove.value = props.operateWidth;
		}
	}
	function handlerTouchstart(event) {
		if (props.forbid) return;
		let touches = event.touches
		if (touches && touches.length > 1) return;
		move.value = true;
		touches = touches ? event.touches[0] : {};
		if (!touches || (touches.pageX === undefined && touches.pageY === undefined)) {
			touches = {
				pageX: event.pageX,
				pageY: event.pageY
			};
		}
		if (touches) {
			for (let i in tStart) {
				if (touches[i]) {
					tStart[i] = touches[i];
				}
			}
		}
	}
	function swipper(touches) {
		const start = tStart;
		const spacing = {
			pageX: touches.pageX - start.pageX,
			pageY: touches.pageY - start.pageY
		};
		if (limitMove.value < Math.abs(spacing.pageX)) {
			spacing.pageX = -limitMove.value;
		}
		position.value = spacing;
	}
	function handlerTouchmove(event) {
		if (props.forbid || !move.value) return;
		const start = tStart;
		let touches = event.touches ? event.touches[0] : {};
		if (!touches || (touches.pageX === undefined && touches.pageY === undefined)) {
			touches = {
				pageX: event.pageX,
				pageY: event.pageY
			};
		}
		if (touches) {
			const direction = swipeDirection(start.pageX, touches.pageX, start.pageY, touches.pageY);
			if (direction === 'Left' && Math.abs(position.value.pageX) !== limitMove.value) {
				swipper(touches);
			}
		}
	}
	function handlerTouchend(event) {
		if (props.forbid || !move.value) return;
		move.value = false;
		const start = tStart;
		let touches = event.changedTouches ? event.changedTouches[0] : {};
		if (!touches || (touches.pageX === undefined && touches.pageY === undefined)) {
			touches = {
				pageX: event.pageX,
				pageY: event.pageY
			};
		}
		if (touches) {
			const direction = swipeDirection(start.pageX, touches.pageX, start.pageY, touches.pageY);
			const spacing = {
				pageX: touches.pageX - start.pageX,
				pageY: touches.pageY - start.pageY
			};
			if (Math.abs(spacing.pageX) >= 40 && direction === 'Left') {
				spacing.pageX = spacing.pageX < 0 ? -limitMove.value : limitMove.value;
				isShowBtn.value = true;
			} else {
				spacing.pageX = 0;
			}
			if (spacing.pageX == 0) {
				isShowBtn.value = false;
			}
			position.value = spacing;

		}
	}
	function handlerButton(event) {
		if (props.closable) {
			closeButtonGroup();
		}
		const dataset = event.currentTarget.dataset;
		emit('click', {
			index: Number(dataset.index),
			item: props.params
		});
	}
	function closeButtonGroup() {
		position.value = {
			pageX: 0,
			pageY: 0
		};
		isShowBtn.value = false;
	}
	//控制自定义按钮菜单
	function handlerParentButton(event) {
		if (props.closable) {
			closeButtonGroup();
		}
	}
	function manualSwitch(isOpen) {
		let x = 0;
		if (isOpen) {
			if (props.actions.length === 0) {
				x = props.operateWidth;
			} else {
				let width = 0;
				props.actions.forEach(item => {
					width += item.width;
				});
				x = width;
			}
		}
		position.value = {
			pageX: -x,
			pageY: 0
		};
	}
	function px(num) {
		return uni.upx2px(num) + 'px';
	}
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
