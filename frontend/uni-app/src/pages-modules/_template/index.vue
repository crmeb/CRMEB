<!--
  pages-modules 插件模块模板：把本目录复制为 src/pages-modules/<插件标识>/（snake_case，
  与后端模块标识对齐，如 seckill 秒杀、combination 拼团、bargain 砍价、presell 预售），
  即自动注册为分包页面（无需改 src/pages.json），本页路由 = /pages-modules/<插件标识>/index。
  页面 = snake_case 子目录 + index.vue（lint 强制，语义后缀如 *_details/*_list/*_record）；
  局部组件放 components/（PascalCase），不会被注册为页面；
  需要自定义标题/导航样式时，在模块根新增 pages.json 片段。
  完整命名细则与营销插件对照表见 docs/命名规范.md §2.1。
  `_` 前缀目录会被扫描器排除，模板自身永不参与编译。
-->
<script setup>
import { ref } from 'vue';
import routes from './routes.js';

</script>

<template>
	<view class="tpl-page">
		<view class="tpl-card">
			<text class="tpl-title">pages-modules 业务模块模板</text>
			<text class="tpl-path">路由：{{ routes.index }}</text>
		</view>
	</view>
</template>

<style lang="scss" scoped>
.tpl-page {
	padding: 24rpx;
}

.tpl-card {
	padding: 32rpx 24rpx;
	border-radius: 16rpx;
	background-color: #fff;
	text-align: center;
}

.tpl-title {
	display: block;
	font-size: 32rpx;
	font-weight: 500;
}

.tpl-path {
	display: block;
	margin-top: 16rpx;
	font-size: 24rpx;
	color: #999;
}

.tpl-counter {
	margin-top: 32rpx;
	font-size: 28rpx;
	color: #333;
}
</style>
