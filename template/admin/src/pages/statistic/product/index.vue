<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">商品统计</span>
            </div>
        </div>
        <product-info></product-info>
        <product-ranking></product-ranking>
    </div>
</template>

<script>
    import productInfo from './components/productInfo'
    import productRanking from './components/productRanking'
    export default {
        name: "index",
        components: {
            productInfo,
            productRanking
        },
    }
</script>

<style scoped>

</style>