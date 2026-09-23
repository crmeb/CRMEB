<template>
	<view>
		<view v-for="(item, index) in dots" :key="index" :style="{
			position: 'fixed',
			borderRadius: '50%',
			left: item.left + 'px',
			top: item.top + 'px',
			display: item.show ? '' : 'none',
			width: size + 'px',
			height: size + 'px',
			background: color,
			zIndex: zIndex
		}">
			<image :src="item.src" mode="aspectFill" style="width: 100%;height: 100%;border-radius:inherit"
				v-if="item.src"></image>
		</view>
	</view>
</template>

<script setup>
	import { reactive } from 'vue';

	const props = defineProps({
		size: { // 尺寸：单位px
			type: Number,
			default: 20
		},
		color: {
			type: String,
			default: '#f5222d'
		},
		zIndex: {
			type: Number,
			default: 999
		},
		duration: {
			type: Number,
			default: 500
		}
	});

	const dots = reactive([
		/* { src: '', left: 0, top: 0, show: false } */
	]);

	function showBall({
		start,
		end,
		src
	}) {
		return new Promise(resolve => {
			let ball = dots.find(v => !v.show)
			if (!ball) {
				ball = reactive({
					src: '',
					left: 0,
					top: 0,
					show: false
				})
				dots.push(ball)
			}
			let t = props.duration,
				starX = start.x - props.size / 2,
				starY = start.y - props.size / 2,
				endX = 50 - props.size / 2,
				endY = 640 - props.size / 2,

				starT = Date.now()
			let Sx = endX - starX,
				Sy = endY - starY,
				Ax = -(2 * Sx / (t * t)) / 5, // 加速度
				Ay = Math.abs(Ax),
				Vox = Sx / t - (Ax * t) / 2, // 初速度
				Voy = Sy / t - (Ay * t) / 2


			const run = () => {
				const To = Date.now() - starT
				const x = starX + (Vox * To + Ax * To * To / 2),
					y = starY + (Voy * To + Ay * To * To / 2)
				ball.left = x
				ball.top = y
				if (To < t) {
					setTimeout(run)
				} else {
					ball.show = false
					resolve()
				}
			}
			ball.src = src
			ball.show = true
			run()
		})
	}

	// 供父组件通过 $refs.showBall(...) 调用（抛物线加购动画）
	defineExpose({
		showBall
	});
</script>

<style>

</style>
