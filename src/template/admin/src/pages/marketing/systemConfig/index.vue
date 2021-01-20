<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            后台from表单
        </Card>
    </div>
</template>

<script>
    export default {
        name: 'systemConfig'
    }
</script>

<style scoped>

</style>
