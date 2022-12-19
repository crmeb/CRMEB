<template></template>
<!--<template>-->
<!--<Tooltip :content="tipContent" transfer>-->
<!--<div class="error-store">-->
<!--<Badge dot :count="lengthError?lengthError:0">-->
<!--<div @click="openErrorLoggerPage">-->
<!--<Icon :size="20" type="ios-locate-outline"/>-->
<!--</div>-->
<!--</Badge>-->
<!--</div>-->
<!--</Tooltip>-->
<!--</template>-->

<!--<script>-->
<!--import { mapGetters } from 'vuex'-->
<!--export default {-->
<!--name: 'ErrorStore',-->
<!--props: {-->
<!--count: {-->
<!--type: Number,-->
<!--default: 0-->
<!--},-->
<!--hasRead: {-->
<!--type: Boolean,-->
<!--default: false-->
<!--}-->
<!--},-->
<!--computed: {-->
<!--...mapGetters('error', [-->
<!--'length',-->
<!--'lengthError'-->
<!--]),-->
<!--tipContent () {-->
<!--if (!this.length) {-->
<!--return '没有日志或异常'-->
<!--} else {-->
<!--let text = `${this.length} 条日志`-->
<!--if (this.lengthError) text += ` | 包含 ${this.lengthError} 个异常`-->
<!--return text-->
<!--}-->
<!--}-->
<!--},-->
<!--methods: {-->
<!--openErrorLoggerPage () {-->
<!--this.$router.push('/admin/system/log')-->
<!--}-->
<!--}-->
<!--}-->
<!--</script>-->

<!--<style lang="less">-->
<!--.error-store:hover{-->
<!--color: #2D8cF0;-->
<!--}-->
<!--.error-store{-->
<!--margin-right: 21px;-->
<!--cursor: pointer;-->
<!--.ivu-badge-dot{-->
<!--top: 20px;-->
<!--}-->
<!--.ivu-btn.ivu-btn-text{-->
<!--padding: 5px 1px 6px;-->
<!--}-->
<!--}-->
<!--</style>-->
