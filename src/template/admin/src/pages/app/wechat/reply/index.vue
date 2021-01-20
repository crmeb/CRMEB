<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt"></Card>
    </div>
</template>

<script>
    export default {
        name: 'index'
    }
</script>

<style scoped>

</style>
