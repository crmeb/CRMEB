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

<script>
	export default {
		props: {
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
		},
		data() {
			return {
				dots: [
					/* { src: '', left: 0, top: 0, show: false } */
				]
			};
		},
		methods: {
			showBall({
				start,
				end,
				src
			}) {
				return new Promise(resolve => {
					let ball = this.dots.find(v => !v.show)
					if (!ball) {
						ball = {
							src: '',
							left: 0,
							top: 0,
							show: false
						}
						this.dots.push(ball)
					}
					let t = this.duration,
						starX = start.x - this.size / 2,
						starY = start.y - this.size / 2,
						endX = 50 - this.size / 2,
						endY = 640 - this.size / 2,

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
		}
	}
</script>

<style>

</style>
