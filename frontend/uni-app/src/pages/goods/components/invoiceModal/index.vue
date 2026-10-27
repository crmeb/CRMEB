<template>
	<view class="aleart" v-if="aleartStatus" :style="colorStyle">
		<!-- 顶部状态：图标半悬于弹窗外，文案居中 -->
		<view class="icon-top">
			<view
				class="icon-wrap"
				:class="invoiceData.is_invoice ? 'on' : 'off'"
			>
				<text class="iconfont icon-fapiao2"></text>
			</view>
			<view class="bill">
				{{ invoiceData.is_invoice ? $t(`已开票`) : $t(`未开票`) }}
			</view>
		</view>

		<view class="aleart-body">
			<view class="body-head">{{ $t(`发票信息`) }}</view>
			<view class="label">
				<view>{{ $t(`发票抬头`) }}</view>
				<view class="label-value">{{ invoiceData.name || invoiceData.title || '--' }}</view>
			</view>
			<view class="label">
				<view>{{ $t(`发票抬头类型`) }}</view>
				<view class="label-value">{{ invoiceData.header_type == 1 ? $t(`个人`) : $t(`企业`) }}</view>
			</view>
			<view class="label">
				<view>{{ $t(`发票类型`) }}</view>
				<view class="label-value">{{ invoiceData.type == 1 ? $t(`电子普通发票`) : $t(`电子专用发票`) }}</view>
			</view>
			<view class="label" v-if="invoiceData.duty_number || invoiceData.tax_number">
				<view>{{ $t(`企业税号`) }}</view>
				<view class="label-value">{{ invoiceData.duty_number || invoiceData.tax_number }}</view>
			</view>

			<view class="body-head">{{ $t(`联系信息`) }}</view>
			<view class="label">
				<view>{{ $t(`真实姓名`) }}</view>
				<view class="label-value">{{ invoiceData.name || invoiceData.title || '--' }}</view>
			</view>
			<view class="label">
				<view>{{ $t(`联系电话`) }}</view>
				<view class="label-value">{{ contactPhone || '--' }}</view>
			</view>
			<view class="label">
				<view>{{ $t(`联系邮箱`) }}</view>
				<view class="label-value">{{ invoiceData.email || '--' }}</view>
			</view>
			<view class="label">
				<view>{{ $t(`发票备注`) }}</view>
				<view class="label-value">{{ invoiceData.remark || '--' }}</view>
			</view>
		</view>
		<view class="btn" @click="close">{{ $t(`确认`) }}</view>
	</view>
</template>

<script setup>
import { computed } from 'vue';
import { useColorTheme } from '@/composables/useColorTheme';

const { colorStyle } = useColorTheme();

const props = defineProps({
	aleartStatus: {
		type: Boolean,
		default: false,
	},
	invoiceData: {
		type: Object,
		default: () => ({}),
	},
});

const emit = defineEmits(['close']);

/** 联系电话：个人 drawer_phone / 契约 phone / 企业 tell */
const contactPhone = computed(() => {
	const d = props.invoiceData || {};
	return d.drawer_phone || d.phone || d.tell || '';
});

function close() {
	emit('close');
}
</script>

<style lang="scss" scoped>
.aleart {
	width: 80%;
	position: fixed;
	left: 50%;
	top: 50%;
	transform: translate(-50%, -50%);
	z-index: 9999;
	background-color: #fff;
	padding: 0 30rpx 30rpx;
	border-radius: 12rpx;
	overflow: visible;
	/* 底部锯齿票根效果 */
	background-image: -webkit-gradient(
			linear,
			50% 0,
			0 100%,
			from(transparent),
			color-stop(0.5, transparent),
			color-stop(0.5, #999999),
			to(#999999)
		),
		-webkit-gradient(
			linear,
			50% 0,
			100% 100%,
			from(transparent),
			color-stop(0.5, transparent),
			color-stop(0.5, #999999),
			to(#999999)
		);
	background-size: 20rpx 10rpx;
	background-repeat: repeat-x;
	background-position: 0 100%;

	.icon-top {
		display: flex;
		flex-direction: column;
		align-items: center;
		margin-top: -48rpx;
		margin-bottom: 12rpx;

		.icon-wrap {
			width: 96rpx;
			height: 96rpx;
			border-radius: 50%;
			border: 4rpx solid #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			box-sizing: border-box;
			box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.08);

			.iconfont {
				font-size: 48rpx;
				color: #fff;
				line-height: 1;
			}

			&.on {
				background-color: var(--view-theme, #e93323);
			}

			&.off {
				background-color: #999;
			}
		}

		.bill {
			margin-top: 12rpx;
			font-size: 28rpx;
			color: #333;
			text-align: center;
			line-height: 1.2;
		}
	}

	.aleart-body {
		display: flex;
		flex-direction: column;
		padding: 20rpx 0 10rpx;

		.body-head {
			font-size: 30rpx;
			font-weight: bold;
			padding-bottom: 10rpx;
			border-bottom: 1px solid #eeeeee;
			margin: 16rpx 0 12rpx;
		}

		.label {
			width: 100%;
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			margin-bottom: 15rpx;
			color: #333333;
			font-size: 28rpx;
			gap: 24rpx;

			.label-value {
				color: #666666;
				text-align: right;
				flex: 1;
				word-break: break-all;
			}
		}
	}

	.btn {
		width: 100%;
		padding: 15rpx 0;
		color: #fff;
		background: var(--view-theme, #e93323);
		border-radius: 20px;
		text-align: center;
		margin-top: 10rpx;
		margin-bottom: 20rpx;
	}
}
</style>
