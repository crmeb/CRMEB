<template>
	<view>
		<view v-if="mask" class="tui-drawer-mask" :class="{ 'tui-drawer-mask_show': visible }" :style="{ zIndex: maskZIndex }" 
			@tap="handleMaskClick" @touchmove.stop.prevent="moveHandle"></view>
		<view
			class="tui-drawer-container"
			:class="[`tui-drawer-container_${mode}`, visible ? `tui-drawer-${mode}__show` : '']"
			:style="{ zIndex: zIndex, backgroundColor: backgroundColor }"
			@touchmove.stop.prevent="moveHandle"
		>
			<slot></slot>
		</view>
	</view>
</template>

<script>
/**
 * 超过一屏时插槽使用scroll-view
 **/
export default {
	name: 'tuiDrawer',
	emits: ['close'],
	props: {
		visible: {
			type: Boolean,
			default: false
		},
		mask: {
			type: Boolean,
			default: true
		},
		maskClosable: {
			type: Boolean,
			default: true
		},
		// left right bottom top
		mode: {
			type: String,
			default: 'right'
		},
		//drawer z-index
		zIndex: {
			type: [Number, String],
			default: 990
		},
		//mask z-index
		maskZIndex: {
			type: [Number, String],
			default: 980
		},
		backgroundColor: {
			type: String,
			default: '#fff'
		}
	},
	methods: {
		moveHandle(){
			return false
		},
		handleMaskClick() {
			if (!this.maskClosable) {
				return;
			}
			this.$emit('close', {});
		}
	}
};
</script>

<style scoped>
.tui-drawer-mask {
	opacity: 0;
	visibility: hidden;
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.6);
	transition: all 0.3s ease-in-out;
}
.tui-drawer-mask_show {
	display: block;
	visibility: visible;
	opacity: 1;
}

.tui-drawer-container {
	position: fixed;
	left: 50%;
	height: 100.2%;
	top: 0;
	transform: translate3d(-50%, -50%, 0);
	transform-origin: center;
	transition: all 0.3s ease-in-out;
	opacity: 0;
	overflow-y: scroll;
	-webkit-overflow-scrolling: touch;
	-ms-touch-action: pan-y cross-slide-y;
	-ms-scroll-chaining: none;
	-ms-scroll-limit: 0 50 0 50;
}
.tui-drawer-container_left {
	left: 0;
	top: 50%;
	transform: translate3d(-100%, -50%, 0);
}

.tui-drawer-container_right {
	right: 0;
	top: 50%;
	left: auto;
	transform: translate3d(100%, -50%, 0);
}

.tui-drawer-container_bottom,
.tui-drawer-container_top {
	width: 100%;
	height: auto !important;
	min-height: 20rpx;
	left: 0;
	right: 0;
	transform-origin: center;
	transition: all 0.3s ease-in-out;
}
.tui-drawer-container_bottom {
	bottom: 0;
	top: auto;
	transform: translate3d(0, 100%, 0);
}
.tui-drawer-container_top {
	transform: translate3d(0, -100%, 0);
}
.tui-drawer-left__show,
.tui-drawer-right__show {
	opacity: 1;
	transform: translate3d(0, -50%, 0);
}
.tui-drawer-top__show,
.tui-drawer-bottom__show {
	opacity: 1;
	transform: translate3d(0, 0, 0);
}
</style>
