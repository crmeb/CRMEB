<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">交易统计</span>
            </div>
        </div>
        <to-day></to-day>
        <transaction-from></transaction-from>
    </div>
</template>

<script>
    import toDay from './components/toDay'
    import transactionFrom from './components/transaction'
    export default {
        name: "index",
        components: {
            toDay,
            transactionFrom
        },
    }
</script>

<style scoped>

</style>