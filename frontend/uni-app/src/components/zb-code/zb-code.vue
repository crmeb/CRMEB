<template xlang="wxml" minapp="mpvue">
	<view class="zb-code">
		<!-- #ifndef MP-ALIPAY -->
		<canvas class="zb-code-canvas" :canvas-id="cid" :style="{width:cpSize+'px',height:cpSize+'px'}" />
		<!-- #endif -->
		<!-- #ifdef MP-ALIPAY -->
		<canvas :id="cid" :width="cpSize" :height="cpSize" class="zb-code-canvas" />
		<!-- #endif -->
		<image v-show="show" :src="result" :style="{width:cpSize+'px',height:cpSize+'px'}" />
	</view>
</template>

<script setup>
import i18n from '@/utils/lang.js';
import QRCode from "./qrcode.js"
import { ref, computed, watch, onMounted, getCurrentInstance } from 'vue'

let qrcode

defineOptions({ name: "zb-code" });

const instance = getCurrentInstance();

const props = defineProps({
	cid: {
		type: String,
		default: 'zb-code-canvas'
	},
	size: {
		type: Number,
		default: 200
	},
	unit: {
		type: String,
		default: 'upx'
	},
	show: {
		type: Boolean,
		default: true
	},
	val: {
		type: String,
		default: ''
	},
	background: {
		type: String,
		default: '#ffffff'
	},
	foreground: {
		type: String,
		default: '#000000'
	},
	pdground: {
		type: String,
		default: '#000000'
	},
	icon: {
		type: String,
		default: ''
	},
	iconSize: {
		type: Number,
		default: 40
	},
	lv: {
		type: Number,
		default: 3
	},
	onval: {
		type: Boolean,
		default: false
	},
	loadMake: {
		type: Boolean,
		default: false
	},
	usingComponents: {
		type: Boolean,
		default: true
	},
	showLoading: {
		type: Boolean,
		default: false
	},
	loadingText: {
		type: String,
		default: '二维码生成中'
	},
})

const result = ref('');

// 原代码在 size watch 中给未声明的 cSize 赋值（无效赋值），此处保留同名局部变量以维持原行为
let cSize = null;

function _makeCode() {
	if (!_empty(props.val)) {
		qrcode = new QRCode({
			context: instance.proxy, // 上下文环境
			canvasId: props.cid, // canvas-id
			usingComponents: props.usingComponents, // 是否是自定义组件
			showLoading: props.showLoading, // 是否显示loading
			loadingText: i18n.t(props.loadingText), // loading文字（props 存中文 key，使用时翻译）
			text: props.val, // 生成内容
			size: cpSize.value, // 二维码大小
			background: props.background, // 背景色
			foreground: props.foreground, // 前景色
			pdground: props.pdground, // 定位角点颜色
			correctLevel: props.lv, // 容错级别
			image: props.icon, // 二维码图标
			imageSize: props.iconSize,// 二维码图标大小
			cbResult: function (res) { // 生成二维码的回调
				_result(res)
			},
		});
	} else {
		uni.showToast({
			title: i18n.t(`二维码内容不能为空`),
			icon: 'none',
			duration: 2000
		});
	}
}
function _clearCode() {
	_result('')
	qrcode.clear()
}
function _saveCode() {
	if (result.value != "") {
		uni.saveImageToPhotosAlbum({
			filePath: result.value,
			success: function () {
				uni.showToast({
					title: i18n.t(`二维码保存成功`),
					icon: 'success',
					duration: 2000
				});
			}
		});
	}
}
const emit = defineEmits(['result']);
function _result(res) {
	result.value = res;
	emit('result', res)
}
function _empty(v) {
	let tp = typeof v,
		rt = false;
	if (tp == "number" && String(v) == "") {
		rt = true
	} else if (tp == "undefined") {
		rt = true
	} else if (tp == "object") {
		if (JSON.stringify(v) == "{}" || JSON.stringify(v) == "[]" || v == null) rt = true
	} else if (tp == "string") {
		if (v == "" || v == "undefined" || v == "null" || v == "{}" || v == "[]") rt = true
	} else if (tp == "function") {
		rt = false
	}
	return rt
}

watch(() => props.size, function (n, o) {
	if (n != o && !_empty(n)) {
		cSize = n
		if (!_empty(props.val)) {
			setTimeout(() => {
				_makeCode()
			}, 100);
		}
	}
})
watch(() => props.val, function (n, o) {
	if (props.onval) {
		if (n != o && !_empty(n)) {
			setTimeout(() => {
				_makeCode()
			}, 0);
		}
	}
})

const cpSize = computed(() => {
	if (props.unit == "upx") {
		return uni.upx2px(props.size)
	} else {
		return props.size
	}
})

onMounted(() => {
	if (props.loadMake) {
		if (!_empty(props.val)) {
			setTimeout(() => {
				_makeCode()
			}, 0);
		}
	}
})

// 保留原可通过 $refs 调用的方法
defineExpose({
	_makeCode,
	_clearCode,
	_saveCode
});
</script>
<style>
.zb-code {
  position: relative;
}
.zb-code-canvas {
  position: fixed;
  top: -99999upx;
  left: -99999upx;
  z-index: -99999;
}
</style>
